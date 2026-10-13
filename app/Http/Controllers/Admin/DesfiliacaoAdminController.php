<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Desfiliacao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DesfiliacaoAdminController extends Controller
{
    public function index(Request $request): View
    {
        $query = Desfiliacao::query()->with('efetivadoPor');

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
            'confirmadas' => Desfiliacao::where('status', 'confirmada')->count(),
            'efetivadas' => Desfiliacao::where('status', 'efetivada')->count(),
        ];

        return view('admin.desfiliacoes.index', compact('desfiliacoes', 'totais'));
    }

    public function show(int $id): JsonResponse
    {
        $desfiliacao = Desfiliacao::with('efetivadoPor')->findOrFail($id);

        return response()->json([
            'id' => $desfiliacao->id,
            'nome_completo' => $desfiliacao->nome_completo,
            'cpf' => $desfiliacao->cpf_formatado,
            'matricula' => $desfiliacao->matricula ?: 'Não informado',
            'empresa' => $desfiliacao->empresa,
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
            'is_efetivada' => $desfiliacao->isEfetivada(),
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

        $desfiliacao->update([
            'status' => 'efetivada',
            'efetivado_por_user_id' => auth()->id(),
            'efetivado_em' => now(),
            'protocolo_abaco' => $request->input('protocolo_abaco'),
            'observacao_efetivacao' => $request->input('observacao_efetivacao'),
        ]);

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
                'desfiliacao' => $desfiliacao->fresh(['efetivadoPor']),
            ]);
        }

        return redirect()->route('admin.desfiliacoes.index')->with('success', $msg);
    }
}
