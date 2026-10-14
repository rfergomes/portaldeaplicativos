<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SubmitDesfiliacaoExclusivaRequest;
use App\Models\Desfiliacao;
use App\Models\DesfiliacaoLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DesfiliacaoPublicLinkController extends Controller
{
    /**
     * Exibe o formulário unificado caso o token seja válido.
     */
    public function show(string $token): View
    {
        $link = DesfiliacaoLink::where('token', $token)->first();

        if (!$link || $link->isCancelado()) {
            return view('desfiliacoes.public.link-invalido', [
                'motivo' => 'O link informado não foi encontrado ou foi cancelado pela entidade sindical.',
            ]);
        }

        if ($link->utilizado) {
            return view('desfiliacoes.public.link-invalido', [
                'motivo' => 'Este link exclusivo já foi utilizado em ' . ($link->utilizado_em ? $link->utilizado_em->format('d/m/Y \à\s H:i') : '') . ' e não permite novo envio.',
            ]);
        }

        if ($link->isExpirado()) {
            return view('desfiliacoes.public.link-invalido', [
                'motivo' => 'Este link expirou em ' . $link->expira_em->format('d/m/Y \à\s H:i') . '. Por favor, solicite um novo link de atendimento junto ao sindicato.',
            ]);
        }

        return view('desfiliacoes.public.solicitar-exclusivo', [
            'link' => $link,
            'token' => $token,
        ]);
    }

    /**
     * Processa a submissão unificada (dados cadastrais + aceite LGPD + rubrica manuscrita).
     */
    public function store(SubmitDesfiliacaoExclusivaRequest $request, string $token): RedirectResponse
    {
        return DB::transaction(function () use ($request, $token) {
            // Lock pessimista para garantir uso único atômico
            $link = DesfiliacaoLink::where('token', $token)->lockForUpdate()->first();

            if (!$link || !$link->isValido()) {
                return redirect()->route('desfiliacao.solicitar.token', $token)
                    ->with('error', 'Este link não é mais válido ou acabou de ser utilizado.');
            }

            $validated = $request->validated();

            $desfiliacao = Desfiliacao::create([
                'desfiliacao_link_id' => $link->id,
                'nome_completo' => trim($validated['nome_completo']),
                'cpf' => preg_replace('/\D/', '', $validated['cpf']),
                'empresa' => trim($validated['empresa']),
                'email' => strtolower(trim($validated['email'])),
                'telefone' => preg_replace('/\D/', '', $validated['telefone']),
                'motivo' => $validated['motivo'] ?? null,
                'status' => 'confirmada', // Status confirmada = aguardando recepção/triagem do operador
                'ip_solicitacao' => $request->ip(),
                'ip_confirmacao' => $request->ip(),
                'user_agent_confirmacao' => $request->userAgent(),
                'aceite_lgpd_em' => now(),
                'email_verificado_em' => now(),
                'confirmado_em' => now(),
                'assinatura_base64' => $validated['assinatura_base64'],
            ]);

            // Gera hash inicial
            $desfiliacao->termo_hash = $desfiliacao->gerarHashUnico();
            $desfiliacao->save();

            // Invalida atomicamente o link exclusivo
            $link->update([
                'utilizado' => true,
                'utilizado_em' => now(),
                'desfiliacao_id' => $desfiliacao->id,
            ]);

            return redirect()->route('desfiliacao.solicitada.sucesso', $desfiliacao->id);
        });
    }

    /**
     * Tela informativa de sucesso após envio do trabalhador.
     */
    public function sucesso(int $id): View
    {
        $desfiliacao = Desfiliacao::findOrFail($id);

        return view('desfiliacoes.public.solicitacao-enviada', compact('desfiliacao'));
    }
}
