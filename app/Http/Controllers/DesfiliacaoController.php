<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\FinalizarDesfiliacaoRequest;
use App\Http\Requests\StoreDesfiliacaoRequest;
use App\Mail\ConfirmacaoDesfiliacaoMail;
use App\Mail\NotificacaoDiretoriaDesfiliacaoMail;
use App\Mail\TermoDesfiliacaoTrabalhadorMail;
use App\Models\Desfiliacao;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DesfiliacaoController extends Controller
{
    /**
     * Exibe a página institucional com aviso de desativação do formulário público aberto.
     */
    public function index(): View
    {
        return view('desfiliacao.public-disabled');
    }

    /**
     * Bloqueia o preenchimento livre direto redirecionando/renderizando tela informativa.
     */
    public function create(): View
    {
        return view('desfiliacao.public-disabled');
    }

    /**
     * Salva o pedido inicial de desfiliação com status pendente e envia link assinado por e-mail.
     */
    public function store(StoreDesfiliacaoRequest $request): RedirectResponse
    {
        $dados = $request->validated();
        $dados['status'] = 'pendente';
        $dados['ip_solicitacao'] = $request->ip();
        $dados['aceite_lgpd_em'] = now();

        /** @var Desfiliacao $desfiliacao */
        $desfiliacao = Desfiliacao::create($dados);

        // Gera rota assinada com validade de 24 horas
        $linkConfirmacao = URL::temporarySignedRoute(
            'desfiliacao.confirmar',
            now()->addHours(24),
            ['id' => $desfiliacao->id]
        );

        // Dispara e-mail com link de confirmação (com fallback resiliente de log)
        try {
            Mail::to($desfiliacao->email)->send(new ConfirmacaoDesfiliacaoMail($desfiliacao, $linkConfirmacao));
        } catch (\Throwable $e) {
            Log::error('Erro ao enviar e-mail de confirmação de desfiliação: ' . $e->getMessage(), [
                'desfiliacao_id' => $desfiliacao->id,
                'email' => $desfiliacao->email,
            ]);
        }

        return redirect()
            ->route('desfiliacao.aguardando')
            ->with('email', $desfiliacao->email);
    }

    /**
     * Exibe a tela informativa de confirmação pendente enviada por e-mail.
     */
    public function aguardando(): View
    {
        return view('desfiliacao.aguardando');
    }

    /**
     * Exibe a tela de confirmação e captura da assinatura (protegida por assinatura temporária).
     */
    public function showConfirmacao(Request $request, int $id): View|RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return view('errors.custom', [
                'title' => 'Link Expirado ou Inválido',
                'message' => 'O link de confirmação acessado expirou (validade de 24h) ou é inválido. Por favor, realize uma nova solicitação no portal.',
                'actionUrl' => route('desfiliacao.create'),
                'actionText' => 'Fazer Nova Solicitação',
            ]);
        }

        /** @var Desfiliacao|null $desfiliacao */
        $desfiliacao = Desfiliacao::find($id);

        if (! $desfiliacao) {
            abort(404, 'Solicitação não encontrada.');
        }

        if ($desfiliacao->isAssinada()) {
            return redirect()
                ->route('desfiliacao.concluido', ['id' => $desfiliacao->id])
                ->with('info', 'Esta desfiliação já foi confirmada e assinada anteriormente.');
        }

        // Marca timestamp de verificação de clique no link do e-mail
        if (! $desfiliacao->email_verificado_em) {
            $desfiliacao->email_verificado_em = now();
            $desfiliacao->save();
        }

        // Gera a rota assinada para o POST de finalização
        $urlFinalizar = URL::temporarySignedRoute(
            'desfiliacao.finalizar',
            now()->addHours(24),
            ['id' => $desfiliacao->id]
        );

        return view('desfiliacao.confirmar', compact('desfiliacao', 'urlFinalizar'));
    }

    /**
     * Processa a assinatura eletrônica, emite o termo em PDF e notifica as partes.
     */
    public function finalizar(FinalizarDesfiliacaoRequest $request, int $id): View|RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return view('errors.custom', [
                'title' => 'Assinatura Expirada',
                'message' => 'A sessão de assinatura expirou. Por favor, acesse novamente o link enviado para o seu e-mail.',
                'actionUrl' => route('desfiliacao.create'),
                'actionText' => 'Início',
            ]);
        }

        /** @var Desfiliacao|null $desfiliacao */
        $desfiliacao = Desfiliacao::findOrFail($id);

        if ($desfiliacao->isAssinada()) {
            return redirect()->route('desfiliacao.concluido', ['id' => $desfiliacao->id]);
        }

        $desfiliacao->status = 'confirmada';
        $desfiliacao->confirmado_em = now();
        if (! $desfiliacao->email_verificado_em) {
            $desfiliacao->email_verificado_em = now();
        }
        $desfiliacao->ip_confirmacao = $request->ip();
        $desfiliacao->user_agent_confirmacao = $request->userAgent();
        $desfiliacao->assinatura_base64 = (string) $request->input('assinatura_base64');
        $desfiliacao->termo_hash = $desfiliacao->gerarHashUnico();

        // Geração do PDF Oficial via DomPDF com 'isRemoteEnabled' => true para renderizar a rubrica em Base64
        $pdf = Pdf::loadView('pdf.termo_desfiliacao', compact('desfiliacao'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'dpi' => 150,
                'defaultFont' => 'Helvetica',
            ]);

        $ano = date('Y');
        $relPath = "desfiliacoes/{$ano}/{$desfiliacao->termo_hash}.pdf";
        Storage::disk('local')->put($relPath, $pdf->output());

        $desfiliacao->pdf_path = $relPath;
        $desfiliacao->save();

        $pdfAbsPath = Storage::disk('local')->path($relPath);

        // 1. Notificação à Diretoria / RH do Sindicato com PDF anexo
        try {
            $emailDestinoDiretoria = config('mail.from.address', 'diretoria@sindicato.org.br');
            Mail::to($emailDestinoDiretoria)->send(new NotificacaoDiretoriaDesfiliacaoMail($desfiliacao, $pdfAbsPath));
        } catch (\Throwable $e) {
            Log::error('Erro ao notificar diretoria sobre desfiliação: ' . $e->getMessage(), [
                'desfiliacao_id' => $desfiliacao->id,
            ]);
        }

        // 2. Envio da cópia ao trabalhador com PDF anexo e alerta de protocolo no RH
        try {
            Mail::to($desfiliacao->email)->send(new TermoDesfiliacaoTrabalhadorMail($desfiliacao, $pdfAbsPath));
        } catch (\Throwable $e) {
            Log::error('Erro ao enviar termo assinado ao trabalhador: ' . $e->getMessage(), [
                'desfiliacao_id' => $desfiliacao->id,
                'email' => $desfiliacao->email,
            ]);
        }

        return redirect()->route('desfiliacao.concluido', ['id' => $desfiliacao->id]);
    }

    /**
     * Tela final de agradecimento e orientação de protocolo no RH com botão de download.
     */
    public function concluido(int $id): View
    {
        $desfiliacao = Desfiliacao::findOrFail($id);

        return view('desfiliacao.concluido', compact('desfiliacao'));
    }

    /**
     * Download do PDF gerado.
     */
    public function download(Request $request, int $id): BinaryFileResponse
    {
        $desfiliacao = Desfiliacao::findOrFail($id);

        if (! $desfiliacao->pdf_path || ! Storage::disk('local')->exists($desfiliacao->pdf_path)) {
            abort(404, 'Arquivo do termo não localizado.');
        }

        $filename = 'Termo_Desfiliacao_' . preg_replace('/\D/', '', $desfiliacao->cpf) . '.pdf';

        return response()->download(
            Storage::disk('local')->path($desfiliacao->pdf_path),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Consulta pública de autenticidade (por hash puro ou parâmetro na URL/QR Code).
     */
    public function validar(Request $request, ?string $hash = null): View
    {
        $hashBuscado = trim((string) ($hash ?: $request->query('hash', '')));
        $desfiliacao = null;

        if (! empty($hashBuscado)) {
            $desfiliacao = Desfiliacao::where('termo_hash', $hashBuscado)->first();
        }

        return view('desfiliacao.validar', compact('desfiliacao', 'hashBuscado'));
    }
}
