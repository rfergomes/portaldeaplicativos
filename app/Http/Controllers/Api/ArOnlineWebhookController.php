<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ArOnlineNotification;
use App\Models\Cliente;
use App\Models\Desfiliacao;
use App\Models\DesfiliacaoEmpresaEnvio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ArOnlineWebhookController extends Controller
{
    /**
     * Handle incoming webhooks from AR Online.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        
        Log::info('AR Online Webhook Received:', $payload);

        $notificationId = $payload['notificationID'] ?? null;
        $statusDescription = null;

        if ($notificationId) {
            if (isset($payload['metadata']['webhookVersion']) && $payload['metadata']['webhookVersion'] === 'v2') {
                $statusDescription = $payload['status'] ?? 'Unknown';
            } else {
                $statusDescription = $payload['description'] ?? 'Unknown';
            }

            try {
                ArOnlineNotification::updateOrCreate(
                    ['notification_id' => $notificationId],
                    [
                        'status' => $statusDescription,
                        'payload' => $payload,
                    ]
                );
                Log::info("AR Online Notification updated: ID $notificationId to status $statusDescription");
            } catch (\Throwable $e) {
                Log::warning("AR Online Webhook: Erro ao registrar ArOnlineNotification para ID $notificationId: " . $e->getMessage());
            }

            // Atualização automática em tempo real do módulo de Desfiliação
            try {
                $statusLower = mb_strtolower(trim((string)$statusDescription));
                $novoStatusEmpresa = null;

                if (str_contains($statusLower, 'lido') || str_contains($statusLower, 'abert') || str_contains($statusLower, 'read') || str_contains($statusLower, 'opened')) {
                    $novoStatusEmpresa = 'lido';
                } elseif (str_contains($statusLower, 'entregue') || str_contains($statusLower, 'deliver')) {
                    $novoStatusEmpresa = 'entregue';
                } elseif (str_contains($statusLower, 'falha') || str_contains($statusLower, 'bounce') || str_contains($statusLower, 'drop') || str_contains($statusLower, 'erro') || str_contains($statusLower, 'fail') || str_contains($statusLower, 'rejeit') || str_contains($statusLower, 'block') || str_contains($statusLower, 'inval')) {
                    $novoStatusEmpresa = 'falha';
                } elseif (str_contains($statusLower, 'enviado') || str_contains($statusLower, 'sent') || str_contains($statusLower, 'process')) {
                    $novoStatusEmpresa = 'enviado';
                }

                $horaFormatada = now()->format('d/m/Y H:i:s');

                // 1. Busca por envio individual na tabela desfiliacao_empresa_envios
                $envioEmpresa = DesfiliacaoEmpresaEnvio::where('aronline_id', $notificationId)->first();

                if ($envioEmpresa) {
                    if ($novoStatusEmpresa) {
                        $envioEmpresa->status = $novoStatusEmpresa;
                        if ($novoStatusEmpresa === 'entregue' && !$envioEmpresa->entregue_em) {
                            $envioEmpresa->entregue_em = now();
                        } elseif ($novoStatusEmpresa === 'lido') {
                            $envioEmpresa->lido_em = $envioEmpresa->lido_em ?? now();
                            $envioEmpresa->entregue_em = $envioEmpresa->entregue_em ?? now();
                        }
                    }
                    $envioEmpresa->resposta_webhook = "Webhook ({$horaFormatada}): {$statusDescription}";
                    $envioEmpresa->save();

                    // Se for falha por bounce ou rejeição, marca o contato como bounce para nunca mais enviar
                    if ($novoStatusEmpresa === 'falha' && (str_contains($statusLower, 'bounce') || str_contains($statusLower, 'drop') || str_contains($statusLower, 'rejeit') || str_contains($statusLower, 'inval'))) {
                        Cliente::where('email', $envioEmpresa->email)->update([
                            'email_valido' => false,
                            'email_bounce_code' => 'AR_ONLINE_BOUNCE',
                            'email_bounce_description' => $statusDescription,
                        ]);
                        Log::info("Webhook AR-Online: Contato {$envioEmpresa->email} marcado com bounce.");
                    }

                    // Sincroniza e consolida o status da desfiliação
                    $desfiliacao = $envioEmpresa->desfiliacao;
                    if ($desfiliacao) {
                        $desfiliacao->consolidarStatusEnvioEmpresa();
                        $desfiliacao->update([
                            'empresa_aronline_resposta' => "Status AR-Online atualizado ({$horaFormatada}): Destinatário {$envioEmpresa->email} - {$statusDescription}",
                        ]);
                        Log::info("Webhook AR-Online: Envio ID #{$envioEmpresa->id} e Desfiliação #{$desfiliacao->id} consolidados com sucesso (Status: {$envioEmpresa->status}).");
                    }
                } else {
                    // 2. Fallback para desfiliações com registro legado em empresa_aronline_id
                    $desfiliacao = Desfiliacao::where('empresa_aronline_id', $notificationId)
                        ->orWhere('empresa_aronline_id', 'LIKE', "%{$notificationId}%")
                        ->first();

                    if ($desfiliacao) {
                        $updates = [
                            'empresa_aronline_resposta' => "Status AR-Online atualizado via Webhook ({$horaFormatada}): {$statusDescription}",
                        ];

                        if ($novoStatusEmpresa) {
                            $updates['empresa_envio_status'] = $novoStatusEmpresa;
                        }

                        $desfiliacao->update($updates);
                        Log::info("Webhook AR-Online: Desfiliação legada #{$desfiliacao->id} atualizada com sucesso para status '{$desfiliacao->empresa_envio_status}' (evento: {$statusDescription}).");
                    }
                }
            } catch (\Throwable $e) {
                Log::error("Webhook AR-Online: Erro ao sincronizar desfiliação para notificationID {$notificationId}: " . $e->getMessage());
            }
        } else {
            Log::warning('AR Online Webhook: notificationID missing in payload.', $payload);
        }

        return response()->json(['message' => 'Webhook received successfully'], 200);
    }
}
