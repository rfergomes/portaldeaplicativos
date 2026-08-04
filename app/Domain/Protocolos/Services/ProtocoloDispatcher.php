<?php

namespace App\Domain\Protocolos\Services;

use App\Models\Protocolo;
use App\Models\ProtocoloDestinatario;
use App\Models\ProtocoloEnvio;
use App\Domain\Protocolos\Contracts\ArOnlineClient;
use App\Domain\Protocolos\DTOs\ArOnlineSendPayload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ProtocoloDispatcher
{
    public function __construct(
        private readonly ArOnlineHttpClient $httpClient
    ) {
    }

    /**
     * Envia o protocolo para TODOS os destinatários (e-mail e, se válido, WhatsApp).
     * Retorna o número de envios realizados com sucesso.
     */
    public function dispatch(Protocolo $protocolo): int
    {
        $token = $this->resolveToken($protocolo);
        $this->httpClient->setToken($token);

        $enviados = 0;

        foreach ($protocolo->destinatarios as $destinatario) {
            $enviados += $this->enviarParaDestinatario($protocolo, $destinatario, $token);
        }

        // Atualiza status geral do protocolo
        if ($enviados > 0) {
            $protocolo->atualizarStatusGeral();
        } else {
            $protocolo->update(['status' => 'falha']);
        }

        return $enviados;
    }

    private function enviarParaDestinatario(Protocolo $protocolo, ProtocoloDestinatario $destinatario, ?string $token): int
    {
        $enviados = 0;

        // ---- Verificação de Bounce Prévia ----
        $cliente = $destinatario->cliente_id
            ? \App\Models\Cliente::find($destinatario->cliente_id)
            : \App\Models\Cliente::where('email', $destinatario->email)->first();

        if ($cliente && $cliente->temBounce()) {
            $bounceInfo = $cliente->email_bounce_code ? " (Código: {$cliente->email_bounce_code})" : "";
            Log::warning("ProtocoloDispatcher: Envio cancelado para {$destinatario->email} devido a bounce prévio{$bounceInfo}");

            ProtocoloEnvio::create([
                'protocolo_id' => $protocolo->id,
                'destinatario_id' => $destinatario->id,
                'canal' => 'email',
                'status' => 'falha',
                'ultima_resposta' => "Envio cancelado: E-mail marcado como bounce prévio{$bounceInfo}",
                'token_usado' => $token,
            ]);

            return 0;
        }

        // ---- Envio por E-mail ----
        try {
            $whatsappPayload = null;

            // TODO: API da AR-Online está com bug ao receber array de whatsapp/sms em nulo/branco
            // Adiciona whatsapp ao payload se o número for válido
            // if ($destinatario->isCelularValido()) {
            //     $whatsappPayload = [
            //         'number' => $destinatario->telefoneSanitizado(),
            //     ];
            // }

            $attachmentsPayload = null;
            if ($protocolo->anexos && $protocolo->anexos->isNotEmpty()) {
                $attachmentsPayload = [];
                foreach ($protocolo->anexos as $anexo) {
                    $content = \Illuminate\Support\Facades\Storage::disk('local')->get($anexo->caminho_armazenado);
                    if ($content !== null) {
                        $attachmentsPayload[] = [
                            'name' => $anexo->nome_original,
                            'base64' => base64_encode($content)
                        ];
                    }
                }
            }

            $assuntoMsg = $protocolo->assunto;
            $corpoMsg = $protocolo->corpo;

            $empresaNome = $destinatario->empresa ? $destinatario->empresa->razao_social : ($protocolo->empresa ? $protocolo->empresa->razao_social : '');

            $replaceVars = [
                '{nome_contato}' => $destinatario->nome,
                '{empresa}' => $empresaNome,
                '{whatsapp}' => $destinatario->telefone ?? '',
                '{email}' => $destinatario->email,
            ];

            $assuntoMsg = strtr($assuntoMsg, $replaceVars);
            $corpoMsg = strtr($corpoMsg, $replaceVars);

            $payload = new ArOnlineSendPayload(
                nameTo: $destinatario->nome,
                subject: $assuntoMsg,
                contentHtml: $corpoMsg,
                emailTo: $destinatario->email,
                attachments: $attachmentsPayload,
                customId: (string) $protocolo->id,
                // whatsapp: $whatsappPayload, // Disabled due to API bug
            );

            $idEmail = $this->httpClient->send($payload);

            ProtocoloEnvio::create([
                'protocolo_id' => $protocolo->id,
                'destinatario_id' => $destinatario->id,
                'canal' => 'email',
                'id_email_externo' => $idEmail,
                'status' => 'enviado',
                'enviado_em' => now(),
                'token_usado' => $token,
            ]);

            $enviados++;
        } catch (\Throwable $e) {
            Log::error("ProtocoloDispatcher: falha ao enviar para {$destinatario->email}", [
                'protocolo_id' => $protocolo->id,
                'destinatario_id' => $destinatario->id,
                'erro' => $e->getMessage(),
            ]);

            ProtocoloEnvio::create([
                'protocolo_id' => $protocolo->id,
                'destinatario_id' => $destinatario->id,
                'canal' => 'email',
                'status' => 'falha',
                'ultima_resposta' => $e->getMessage(),
                'token_usado' => $token,
            ]);
        }

        return $enviados;
    }

    /**
     * Executa o reenvio individual de um envio especifico com falha.
     */
    public function reenviarEnvio(ProtocoloEnvio $envio): bool
    {
        if (!$envio->podeSerReenviado()) {
            return false;
        }

        $protocolo = $envio->protocolo;
        $destinatario = $envio->destinatario;

        if (!$protocolo || !$destinatario) {
            return false;
        }

        $token = $this->resolveToken($protocolo);
        $this->httpClient->setToken($token);

        $envio->increment('tentativas');
        $numeroTentativa = $envio->tentativas;

        // Verificação de Bounce Prévia
        $cliente = $destinatario->cliente_id
            ? \App\Models\Cliente::find($destinatario->cliente_id)
            : \App\Models\Cliente::where('email', $destinatario->email)->first();

        if ($cliente && $cliente->temBounce()) {
            $bounceInfo = $cliente->email_bounce_code ? " (Código: {$cliente->email_bounce_code})" : "";
            $msgErro = "Envio cancelado: E-mail marcado como bounce prévio{$bounceInfo}";

            $envio->update([
                'status' => 'falha',
                'bloqueado_reenvio' => true,
                'ultima_resposta' => $msgErro,
            ]);

            \App\Models\ProtocoloEnvioTentativa::create([
                'protocolo_envio_id' => $envio->id,
                'numero_tentativa' => $numeroTentativa,
                'status_resultado' => 'bounce_cancelado',
                'resposta_api' => $msgErro,
                'executado_por_user_id' => Auth::id(),
            ]);

            return false;
        }

        try {
            $attachmentsPayload = null;
            if ($protocolo->anexos && $protocolo->anexos->isNotEmpty()) {
                $attachmentsPayload = [];
                foreach ($protocolo->anexos as $anexo) {
                    $content = \Illuminate\Support\Facades\Storage::disk('local')->get($anexo->caminho_armazenado);
                    if ($content !== null) {
                        $attachmentsPayload[] = [
                            'name' => $anexo->nome_original,
                            'base64' => base64_encode($content)
                        ];
                    }
                }
            }

            $empresaNome = $destinatario->empresa ? $destinatario->empresa->razao_social : ($protocolo->empresa ? $protocolo->empresa->razao_social : '');
            $replaceVars = [
                '{nome_contato}' => $destinatario->nome,
                '{empresa}' => $empresaNome,
                '{whatsapp}' => $destinatario->telefone ?? '',
                '{email}' => $destinatario->email,
            ];

            $assuntoMsg = strtr($protocolo->assunto, $replaceVars);
            $corpoMsg = strtr($protocolo->corpo, $replaceVars);

            $payload = new ArOnlineSendPayload(
                nameTo: $destinatario->nome,
                subject: $assuntoMsg,
                contentHtml: $corpoMsg,
                emailTo: $destinatario->email,
                attachments: $attachmentsPayload,
                customId: (string) $protocolo->id,
            );

            $idEmail = $this->httpClient->send($payload);

            $envio->update([
                'status' => 'enviado',
                'id_email_externo' => $idEmail,
                'enviado_em' => now(),
                'token_usado' => $token,
                'ultima_resposta' => 'Reenviado com sucesso.',
            ]);

            \App\Models\ProtocoloEnvioTentativa::create([
                'protocolo_envio_id' => $envio->id,
                'numero_tentativa' => $numeroTentativa,
                'status_resultado' => 'sucesso',
                'resposta_api' => "ID E-mail: {$idEmail}",
                'executado_por_user_id' => Auth::id(),
            ]);

            $protocolo->atualizarStatusGeral();

            return true;
        } catch (\Throwable $e) {
            Log::error("ProtocoloDispatcher: falha no reenvio para {$destinatario->email}", [
                'protocolo_id' => $protocolo->id,
                'destinatario_id' => $destinatario->id,
                'envio_id' => $envio->id,
                'erro' => $e->getMessage(),
            ]);

            $envio->update([
                'status' => 'falha',
                'ultima_resposta' => $e->getMessage(),
            ]);

            \App\Models\ProtocoloEnvioTentativa::create([
                'protocolo_envio_id' => $envio->id,
                'numero_tentativa' => $numeroTentativa,
                'status_resultado' => 'falha',
                'resposta_api' => $e->getMessage(),
                'executado_por_user_id' => Auth::id(),
            ]);

            return false;
        }
    }

    /**
     * Executa o reenvio em lote para todos os envios com falha de um protocolo.
     */
    public function reenviarFalhasDoProtocolo(Protocolo $protocolo): int
    {
        $enviosComFalha = $protocolo->enviosComFalha()->get();
        $reenviados = 0;

        foreach ($enviosComFalha as $envio) {
            if ($this->reenviarEnvio($envio)) {
                $reenviados++;
            }
        }

        return $reenviados;
    }

    /**
     * Determina o token AR-Online a usar:
     * 1. Token do usuário que criou o protocolo
     * 2. Token do usuário autenticado no momento
     * 3. Token do .env como fallback
     */
    private function resolveToken(Protocolo $protocolo): ?string
    {
        // Prioridade 1: O Token do Usuário Rementente (dono do protocolo)
        // Se a FK `token_depto_id` estiver cheia, ele pucha o token do Departamento.
        $tokenUsuario = $protocolo->usuario?->tokenDepto?->token;

        if ($tokenUsuario) {
            return $tokenUsuario;
        }

        // Prioridade 2: O Token configurado no Perfil de quem está logado (Fallback de segurança)
        $tokenAutenticado = Auth::user()?->tokenDepto?->token;

        if ($tokenAutenticado) {
            return $tokenAutenticado;
        }

        // Fallback: token do .env
        return config('services.ar_online.token');
    }
}
