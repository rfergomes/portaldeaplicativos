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
        $protocolo->update([
            'status' => $enviados > 0 ? 'enviado' : 'falha',
        ]);

        return $enviados;
    }

    private function enviarParaDestinatario(Protocolo $protocolo, ProtocoloDestinatario $destinatario, ?string $token): int
    {
        $enviados = 0;

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

            $payload = new ArOnlineSendPayload(
                nameTo: $destinatario->nome,
                subject: $protocolo->assunto,
                contentHtml: $protocolo->corpo,
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
