<?php

namespace Tests\Feature\Protocolos;

use App\Domain\Protocolos\Services\ArOnlineHttpClient;
use App\Models\Empresa;
use App\Models\Protocolo;
use App\Models\ProtocoloDestinatario;
use App\Models\ProtocoloEnvio;
use App\Models\ProtocoloEnvioTentativa;
use App\Models\TipoProtocolo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Mockery\MockInterface;
use Tests\TestCase;

class ProtocoloReenvioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Gate::before(fn () => true);
    }

    public function test_reenvio_individual_com_falha(): void
    {
        $user = User::factory()->create();
        $tipo = TipoProtocolo::create([
            'nome' => 'Ofício Teste',
            'slug' => 'oficio-teste',
            'ativo' => true,
        ]);

        $protocolo = Protocolo::create([
            'tipo_protocolo_id' => $tipo->id,
            'user_id' => $user->id,
            'assunto' => 'Teste Reenvio Individual',
            'corpo' => 'Corpo do protocolo',
            'canal' => 'email',
            'tipo_escopo' => 'individual',
            'status' => 'falha',
        ]);

        $destinatario = ProtocoloDestinatario::create([
            'protocolo_id' => $protocolo->id,
            'nome' => 'João da Silva',
            'email' => 'joao@empresa.com.br',
        ]);

        $envio = ProtocoloEnvio::create([
            'protocolo_id' => $protocolo->id,
            'destinatario_id' => $destinatario->id,
            'canal' => 'email',
            'status' => 'falha',
            'tentativas' => 1,
            'ultima_resposta' => 'Connection timeout',
        ]);

        $this->mock(ArOnlineHttpClient::class, function (MockInterface $mock) {
            $mock->shouldReceive('setToken')->andReturnSelf();
            $mock->shouldReceive('send')->once()->andReturn('MSG_EXT_999');
        });

        $response = $this->actingAs($user)
            ->post("/protocolos/{$protocolo->id}/envios/{$envio->id}/reenviar");

        $response->assertRedirect();

        $envio->refresh();
        $this->assertEquals('enviado', $envio->status);
        $this->assertEquals(2, $envio->tentativas);
        $this->assertEquals('MSG_EXT_999', $envio->id_email_externo);

        $this->assertDatabaseHas('protocolo_envio_tentativas', [
            'protocolo_envio_id' => $envio->id,
            'numero_tentativa' => 2,
            'status_resultado' => 'sucesso',
        ]);
    }

    public function test_reenvio_em_lote_com_falha(): void
    {
        $user = User::factory()->create();
        $tipo = TipoProtocolo::create([
            'nome' => 'Ofício Coletivo',
            'slug' => 'oficio-coletivo',
            'ativo' => true,
        ]);

        $protocolo = Protocolo::create([
            'tipo_protocolo_id' => $tipo->id,
            'user_id' => $user->id,
            'assunto' => 'Teste Reenvio em Lote',
            'corpo' => 'Corpo em lote',
            'canal' => 'email',
            'tipo_escopo' => 'coletivo',
            'status' => 'falha',
        ]);

        $dest1 = ProtocoloDestinatario::create(['protocolo_id' => $protocolo->id, 'nome' => 'Dest 1', 'email' => 'd1@test.com']);
        $dest2 = ProtocoloDestinatario::create(['protocolo_id' => $protocolo->id, 'nome' => 'Dest 2', 'email' => 'd2@test.com']);

        $envio1 = ProtocoloEnvio::create(['protocolo_id' => $protocolo->id, 'destinatario_id' => $dest1->id, 'canal' => 'email', 'status' => 'falha', 'tentativas' => 1]);
        $envio2 = ProtocoloEnvio::create(['protocolo_id' => $protocolo->id, 'destinatario_id' => $dest2->id, 'canal' => 'email', 'status' => 'falha', 'tentativas' => 1]);

        $this->mock(ArOnlineHttpClient::class, function (MockInterface $mock) {
            $mock->shouldReceive('setToken')->andReturnSelf();
            $mock->shouldReceive('send')->twice()->andReturn('MSG_EXT_OK');
        });

        $response = $this->actingAs($user)
            ->post("/protocolos/{$protocolo->id}/reenviar-falhas");

        $response->assertRedirect();

        $this->assertEquals('enviado', $envio1->fresh()->status);
        $this->assertEquals('enviado', $envio2->fresh()->status);
    }

    public function test_nao_permite_reenvio_de_mensagem_entregue(): void
    {
        $user = User::factory()->create();
        $tipo = TipoProtocolo::create(['nome' => 'Tipo Teste', 'slug' => 'tipo-teste', 'ativo' => true]);
        $protocolo = Protocolo::create(['tipo_protocolo_id' => $tipo->id, 'user_id' => $user->id, 'assunto' => 'A', 'corpo' => 'C', 'canal' => 'email', 'tipo_escopo' => 'individual', 'status' => 'sucesso']);
        $dest = ProtocoloDestinatario::create(['protocolo_id' => $protocolo->id, 'nome' => 'X', 'email' => 'x@test.com']);

        $envio = ProtocoloEnvio::create([
            'protocolo_id' => $protocolo->id,
            'destinatario_id' => $dest->id,
            'canal' => 'email',
            'status' => 'entregue',
            'tentativas' => 1,
        ]);

        $response = $this->actingAs($user)
            ->post("/protocolos/{$protocolo->id}/envios/{$envio->id}/reenviar");

        $response->assertRedirect();
        $this->assertEquals('entregue', $envio->fresh()->status);
        $this->assertEquals(1, $envio->fresh()->tentativas);
    }

    public function test_atualizacao_de_destinatario_pre_reenvio(): void
    {
        $user = User::factory()->create();
        $tipo = TipoProtocolo::create(['nome' => 'Tipo Teste', 'slug' => 'tipo-teste', 'ativo' => true]);
        $protocolo = Protocolo::create(['tipo_protocolo_id' => $tipo->id, 'user_id' => $user->id, 'assunto' => 'A', 'corpo' => 'C', 'canal' => 'email', 'tipo_escopo' => 'individual', 'status' => 'falha']);
        $dest = ProtocoloDestinatario::create(['protocolo_id' => $protocolo->id, 'nome' => 'Maria', 'email' => 'errado@test.com']);

        $response = $this->actingAs($user)
            ->put("/protocolos/{$protocolo->id}/destinatarios/{$dest->id}", [
                'nome' => 'Maria Silva',
                'email' => 'correto@test.com',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('protocolo_destinatarios', [
            'id' => $dest->id,
            'email' => 'correto@test.com',
            'nome' => 'Maria Silva',
        ]);
    }

    public function test_tolerancia_defensiva_para_schema(): void
    {
        $user = User::factory()->create();
        $tipo = TipoProtocolo::create(['nome' => 'Tipo Teste', 'slug' => 'tipo-teste', 'ativo' => true]);
        $protocolo = Protocolo::create(['tipo_protocolo_id' => $tipo->id, 'user_id' => $user->id, 'assunto' => 'A', 'corpo' => 'C', 'canal' => 'email', 'tipo_escopo' => 'individual', 'status' => 'sucesso']);

        $response = $this->actingAs($user)->get("/protocolos/{$protocolo->id}");

        $response->assertStatus(200);
        $this->assertFalse($protocolo->temEnviosComFalha());
    }
}

