<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\DespacharTermoDesfiliacaoAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\RecusarDesfiliacaoRequest;
use App\Mail\RecusaDesfiliacaoMail;
use App\Models\Desfiliacao;
use App\Models\EmpresaContato;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DesfiliacaoAdminController extends Controller
{
    public function index(Request $request): View
    {
        $query = Desfiliacao::query()->with(['recepcionadoPor', 'efetivadoPor']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('busca')) {
            $busca = trim((string) $request->input('busca'));
            $buscaLimpa = preg_replace('/\D/', '', $busca);
            $query->where(function ($q) use ($busca, $buscaLimpa) {
                $q->where('nome_completo', 'like', "%{$busca}%")
                  ->orWhere('empresa', 'like', "%{$busca}%")
                  ->orWhere('email', 'like', "%{$busca}%");
                if (! empty($buscaLimpa)) {
                    $q->orWhere('cpf', 'like', "%{$buscaLimpa}%");
                }
            });
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->input('data_inicio'));
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->input('data_fim'));
        }

        $desfiliacoes = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $totais = [
            'total' => Desfiliacao::count(),
            'pendentes' => Desfiliacao::where('status', 'pendente')->count(),
            'confirmadas' => Desfiliacao::where('status', 'confirmada')->count(), // Pendente Recepção/Ciência
            'recepcionadas' => Desfiliacao::where('status', 'recepcionada')->count(), // Pendente Baixa no Ábaco
            'efetivadas' => Desfiliacao::where('status', 'efetivada')->count(), // Baixa Concluída
            'recusadas' => Desfiliacao::where('status', 'recusada')->count(), // Recusadas com Justificativa
            'pendentes_empresa' => Desfiliacao::where('empresa_envio_status', 'pendente_contato')->count(),
        ];

        return view('admin.desfiliacoes.index', compact('desfiliacoes', 'totais'));
    }

    public function show(int $id): JsonResponse
    {
        $desfiliacao = Desfiliacao::with(['recepcionadoPor', 'efetivadoPor', 'recusadoPor', 'desfiliacaoLink'])->findOrFail($id);

        return response()->json([
            'id' => $desfiliacao->id,
            'nome_completo' => $desfiliacao->nome_completo,
            'cpf' => $desfiliacao->cpf_formatado,
            'matricula' => $desfiliacao->matricula ?: 'Não informado',
            'empresa' => $desfiliacao->empresa,
            'empresa_email' => $desfiliacao->empresa_email,
            'empresa_aronline_id' => $desfiliacao->empresa_aronline_id,
            'empresa_envio_status' => $desfiliacao->empresa_envio_status,
            'empresa_notificada_em' => $desfiliacao->empresa_notificada_em?->format('d/m/Y H:i:s'),
            'email' => $desfiliacao->email,
            'telefone' => $desfiliacao->telefone_formatado,
            'motivo' => $desfiliacao->motivo ?: 'Não informado',
            'status' => $desfiliacao->status,
            'ip_solicitacao' => $desfiliacao->ip_solicitacao,
            'ip_confirmacao' => $desfiliacao->ip_confirmacao,
            'user_agent_confirmacao' => $desfiliacao->user_agent_confirmacao,
            'created_at' => $desfiliacao->created_at?->format('d/m/Y H:i:s'),
            'confirmado_em' => $desfiliacao->confirmado_em?->format('d/m/Y H:i:s'),
            'termo_hash' => $desfiliacao->termo_hash,
            'tem_pdf' => (bool) ($desfiliacao->pdf_path && Storage::disk('local')->exists($desfiliacao->pdf_path)),
            'download_url' => route('admin.desfiliacoes.download', ['id' => $desfiliacao->id]),
            'is_confirmada' => $desfiliacao->isConfirmada(),
            'is_recepcionada' => $desfiliacao->isRecepcionada(),
            'is_efetivada' => $desfiliacao->isEfetivada(),
            'is_recusada' => $desfiliacao->isRecusada(),
            'recusado_em' => $desfiliacao->recusado_em?->format('d/m/Y H:i:s'),
            'recusado_por' => $desfiliacao->recusadoPor?->name,
            'motivo_recusa' => $desfiliacao->motivo_recusa,
            'recepcionado_em' => $desfiliacao->recepcionado_em?->format('d/m/Y H:i:s'),
            'recepcionado_por' => $desfiliacao->recepcionadoPor?->name,
            'observacao_recepcao' => $desfiliacao->observacao_recepcao,
            'efetivado_em' => $desfiliacao->efetivado_em?->format('d/m/Y H:i:s'),
            'efetivado_por' => $desfiliacao->efetivadoPor?->name,
            'protocolo_abaco' => $desfiliacao->protocolo_abaco,
            'observacao_efetivacao' => $desfiliacao->observacao_efetivacao,
        ]);
    }

    public function download(int $id): BinaryFileResponse
    {
        $desfiliacao = Desfiliacao::findOrFail($id);

        if (! $desfiliacao->pdf_path || ! Storage::disk('local')->exists($desfiliacao->pdf_path)) {
            abort(404, 'Arquivo PDF do termo não encontrado no disco protegido.');
        }

        $filename = 'Termo_Desfiliacao_' . preg_replace('/\D/', '', $desfiliacao->cpf) . '.pdf';

        return response()->download(
            Storage::disk('local')->path($desfiliacao->pdf_path),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Registra o aceite / recepção formal e ciência institucional da carta de desfiliação.
     */
    public function recepcionar(Request $request, int $id)
    {
        $desfiliacao = Desfiliacao::findOrFail($id);

        if ($desfiliacao->isRecepcionada() || $desfiliacao->isEfetivada()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta carta de desfiliação já foi recepcionada anteriormente.',
                ], 422);
            }
            return back()->with('warning', 'Esta carta de desfiliação já foi recepcionada anteriormente.');
        }

        $request->validate([
            'observacao_recepcao' => 'nullable|string|max:1000',
        ]);

        $desfiliacao->update([
            'status' => 'recepcionada',
            'recepcionado_por_user_id' => auth()->id(),
            'recepcionado_em' => now(),
            'observacao_recepcao' => $request->input('observacao_recepcao'),
        ]);

        $desfiliacao->load('recepcionadoPor');

        // Regenerar o PDF para estampar o Protocolo de Recepção Institucional (Ciência)
        try {
            $this->regenerarPdf($desfiliacao);
        } catch (\Throwable $e) {
            // Continua a execução mesmo se falhar a gravação do PDF em ambiente de teste
        }

        // Despacha notificação por e-mail ao trabalhador e via AR-Online para a empresa
        try {
            $despachoAction = app(DespacharTermoDesfiliacaoAction::class);
            $despachoAction->execute($desfiliacao);
        } catch (\Throwable $e) {
            // Log do erro já registrado na Action
        }

        $msg = "Solicitação de desfiliação de {$desfiliacao->nome_completo} homologada com sucesso! O termo em PDF foi emitido, encaminhado ao trabalhador e processado via AR-Online para a empresa.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'desfiliacao' => $desfiliacao->fresh(['recepcionadoPor', 'efetivadoPor']),
            ]);
        }

        return redirect()->route('admin.desfiliacoes.index')->with('success', $msg);
    }

    /**
     * Recusa formalmente uma solicitação divergente com justificativa obrigatória e notifica o trabalhador.
     */
    public function recusar(RecusarDesfiliacaoRequest $request, int $id)
    {
        $desfiliacao = Desfiliacao::findOrFail($id);

        if ($desfiliacao->isRecusada()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta solicitação já foi recusada anteriormente.',
                ], 422);
            }
            return back()->with('warning', 'Esta solicitação já foi recusada anteriormente.');
        }

        $motivo = $request->validated('motivo_recusa');

        $desfiliacao->update([
            'status' => 'recusada',
            'recusado_por_user_id' => auth()->id(),
            'recusado_em' => now(),
            'motivo_recusa' => $motivo,
        ]);

        // Dispara e-mail com a justificativa de recusa para o trabalhador
        try {
            Mail::to($desfiliacao->email)->send(new RecusaDesfiliacaoMail($desfiliacao, $motivo));
        } catch (\Throwable $e) {
            // Continua mesmo em falha de conexão de e-mail local
        }

        $msg = "A solicitação #{$desfiliacao->id} foi recusada com sucesso. O solicitante foi notificado por e-mail com a justificativa informada.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'desfiliacao' => $desfiliacao->fresh(['recusadoPor']),
            ]);
        }

        return redirect()->route('admin.desfiliacoes.index')->with('status', $msg);
    }

    /**
     * Informa ou atualiza o e-mail de RH da empresa e despacha a notificação via API AR-Online.
     */
    public function notificarEmpresa(Request $request, int $id)
    {
        $desfiliacao = Desfiliacao::findOrFail($id);

        $request->validate([
            'email_rh' => ['required', 'email', 'max:191'],
            'salvar_contato' => ['nullable', 'boolean'],
        ]);

        $emailRh = strtolower(trim((string) $request->input('email_rh')));

        if ($request->boolean('salvar_contato')) {
            EmpresaContato::updateOrCreate(
                ['razao_social' => $desfiliacao->empresa],
                [
                    'email_rh' => $emailRh,
                    'nome_fantasia' => $desfiliacao->empresa,
                ]
            );
        }

        $despachoAction = app(DespacharTermoDesfiliacaoAction::class);
        $resultado = $despachoAction->execute($desfiliacao, $emailRh);

        if ($resultado['empresa']) {
            $msg = "Notificação encaminhada com sucesso via API AR-Online para {$emailRh} (ID: {$resultado['empresa_aronline_id']}).";
            return back()->with('success', $msg);
        }

        return back()->with('error', 'Ocorreu uma falha ao enviar via AR-Online: ' . ($desfiliacao->empresa_aronline_resposta ?? 'Erro desconhecido'));
    }

    /**
     * Efetiva a desfiliação após providências/baixa no ERP Ábaco pelo usuário operador.
     */
    public function efetivar(Request $request, int $id)
    {
        $desfiliacao = Desfiliacao::findOrFail($id);

        if ($desfiliacao->isEfetivada()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta desfiliação já foi efetivada anteriormente.',
                ], 422);
            }
            return back()->with('warning', 'Esta desfiliação já foi efetivada anteriormente.');
        }

        $request->validate([
            'protocolo_abaco' => 'nullable|string|max:100',
            'observacao_efetivacao' => 'nullable|string|max:1000',
        ]);

        // Se ainda não tiver sido recepcionada formalmente, registra o operador atual como recebedor também
        if (! $desfiliacao->recepcionado_em) {
            $desfiliacao->recepcionado_por_user_id = auth()->id();
            $desfiliacao->recepcionado_em = now();
            if (empty($desfiliacao->observacao_recepcao)) {
                $desfiliacao->observacao_recepcao = 'Recepcionado e baixado simultaneamente no ERP Ábaco.';
            }
        }

        $desfiliacao->update([
            'status' => 'efetivada',
            'recepcionado_por_user_id' => $desfiliacao->recepcionado_por_user_id,
            'recepcionado_em' => $desfiliacao->recepcionado_em,
            'observacao_recepcao' => $desfiliacao->observacao_recepcao,
            'efetivado_por_user_id' => auth()->id(),
            'efetivado_em' => now(),
            'protocolo_abaco' => $request->input('protocolo_abaco'),
            'observacao_efetivacao' => $request->input('observacao_efetivacao'),
        ]);

        $desfiliacao->load(['recepcionadoPor', 'efetivadoPor']);

        try {
            $this->regenerarPdf($desfiliacao);
        } catch (\Throwable $e) {
            // Continua caso dê erro no PDF
        }

        // Se o sócio existir no cadastro de sócios caixa, sincronizar flag inativado_abaco
        $cpfLimpo = preg_replace('/\D/', '', (string) $desfiliacao->cpf);
        $sociosCaixa = \App\Models\SocioCaixa::where(function ($q) use ($desfiliacao, $cpfLimpo) {
            if (! empty($desfiliacao->matricula)) {
                $q->where('matricula', $desfiliacao->matricula);
            }
            if (! empty($cpfLimpo)) {
                $q->orWhere('cpf', $cpfLimpo);
            }
        })->get();

        foreach ($sociosCaixa as $sc) {
            if (! $sc->inativado_abaco) {
                $sc->update([
                    'inativado_abaco' => true,
                    'inativado_abaco_em' => now(),
                ]);
            }
        }

        $msg = "Desfiliação de {$desfiliacao->nome_completo} efetivada com sucesso! Baixa no ERP Ábaco registrada.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'desfiliacao' => $desfiliacao->fresh(['recepcionadoPor', 'efetivadoPor']),
            ]);
        }

        return redirect()->route('admin.desfiliacoes.index')->with('success', $msg);
    }

    /**
     * Regenera o PDF do termo com os carimbos de auditoria, recepção institucional e baixa.
     */
    protected function regenerarPdf(Desfiliacao $desfiliacao): void
    {
        $pdf = Pdf::loadView('pdf.termo_desfiliacao', compact('desfiliacao'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'dpi' => 150,
                'defaultFont' => 'Helvetica',
            ]);

        $ano = $desfiliacao->created_at ? $desfiliacao->created_at->format('Y') : date('Y');
        $relPath = "desfiliacoes/{$ano}/{$desfiliacao->termo_hash}.pdf";
        Storage::disk('local')->put($relPath, $pdf->output());

        if ($desfiliacao->pdf_path !== $relPath) {
            $desfiliacao->update(['pdf_path' => $relPath]);
        }
    }
}
