<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Desfiliacao;
use Tests\TestCase;

class DesfiliacaoArOnlineWebhookTest extends TestCase
{
    public function test_webhook_aronline_atualiza_status_da_empresa_para_entregue_e_lido(): void
    {
        $idExterno = 'AR-WEBHOOK-TEST-' . uniqid();

        $desfiliacao = Desfiliacao::create([
            'nome_completo' => 'Trabalhador Webhook Teste',
            'cpf' => '11144477735',
            'empresa' => 'Indústria Webhook S/A',
            'empresa_email' => 'rh@webhook.com.br',
            'email' => 'trabalhador@webhook.com.br',
            'telefone' => '19988887777',
            'status' => 'recepcionada',
            'empresa_aronline_id' => $idExterno,
            'empresa_envio_status' => 'enviado',
            'empresa_notificada_em' => now(),
            'termo_hash' => hash('sha256', $idExterno),
        ]);

        // 1. Simula webhook do AR-Online reportando 'Entregue'
        $payloadEntregue = [
            'notificationID' => $idExterno,
            'description' => 'Entregue no servidor de destino',
            'metadata' => [
                'webhookVersion' => 'v1',
            ],
        ];

        $response1 = $this->postJson('/api/webhooks/aronline', $payloadEntregue);
        $response1->assertStatus(200);

        $desfiliacao->refresh();
        $this->assertEquals('entregue', $desfiliacao->empresa_envio_status);
        $this->assertStringContainsString('Entregue no servidor de destino', $desfiliacao->empresa_aronline_resposta);

        // 2. Simula webhook do AR-Online reportando 'Aberto/Lido'
        $payloadLido = [
            'notificationID' => $idExterno,
            'status' => 'Lido pelo destinatário',
            'metadata' => [
                'webhookVersion' => 'v2',
            ],
        ];

        $response2 = $this->postJson('/api/webhooks/aronline', $payloadLido);
        $response2->assertStatus(200);

        $desfiliacao->refresh();
        $this->assertEquals('lido', $desfiliacao->empresa_envio_status);
        $this->assertStringContainsString('Lido pelo destinatário', $desfiliacao->empresa_aronline_resposta);

        $desfiliacao->delete();
    }
}
