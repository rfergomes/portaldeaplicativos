<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\DesfiliacaoLink;
use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesfiliacaoLinkGenerationTest extends TestCase
{
    private User $adminUser;
    private User $commonUser;

    protected function setUp(): void
    {
        parent::setUp();

        $permissao = Permissao::firstOrCreate(
            ['chave' => 'desfiliacoes.links.gerar'],
            ['nome' => 'Gerenciar Links', 'descricao' => 'Permite gerar links']
        );

        $perfil = Perfil::firstOrCreate(
            ['nome' => 'Operador Teste'],
            ['descricao' => 'Operador']
        );
        $perfil->permissoes()->syncWithoutDetaching([$permissao->id]);

        $this->adminUser = User::factory()->create(['email' => 'admin_' . uniqid() . '@example.com']);
        $this->adminUser->perfis()->attach($perfil);

        $this->commonUser = User::factory()->create(['email' => 'common_' . uniqid() . '@example.com']);
    }

    public function test_usuario_com_permissao_pode_gerar_link_exclusivo(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.desfiliacao.links.store'), [
            'destinatario_nome' => 'João da Silva',
            'destinatario_contato' => '19999999999',
            'canal_envio' => 'whatsapp',
            'horas_validade' => 48,
        ]);

        $response->assertRedirect(route('admin.desfiliacao.links.index'));

        $this->assertDatabaseHas('desfiliacao_links', [
            'user_id' => $this->adminUser->id,
            'destinatario_nome' => 'João da Silva',
            'destinatario_contato' => '19999999999',
            'canal_envio' => 'whatsapp',
            'horas_validade' => 48,
            'utilizado' => false,
        ]);

        $link = DesfiliacaoLink::latest()->first();
        $this->assertNotNull($link);
        $this->assertEquals(64, strlen($link->token));
        $this->assertTrue($link->isValido());
        $this->assertFalse($link->isExpirado());
        $this->assertStringContainsString('/desfiliacao/solicitar/' . $link->token, $link->url);
        $this->assertStringContainsString('wa.me/5519999999999', $link->whatsapp_url);
    }

    public function test_usuario_sem_permissao_nao_pode_gerar_link(): void
    {
        $response = $this->actingAs($this->commonUser)->post(route('admin.desfiliacao.links.store'), [
            'horas_validade' => 48,
        ]);

        $response->assertForbidden();
    }

    public function test_link_individual_gera_estritamente_com_cota_um_mesmo_com_parametro_limite_usos_presente(): void
    {
        // Simula o envio do formulário HTML onde limite_usos=5 vinha presente no payload
        $response = $this->actingAs($this->adminUser)->post(route('admin.desfiliacao.links.store'), [
            'tipo_link' => 'individual',
            'quantidade' => 1,
            'limite_usos' => 5, // Valor oculto no form anterior
            'destinatario_nome' => 'Carlos Eduardo',
            'destinatario_contato' => '19987654321',
            'destinatario_email' => 'carlos@empresa.com.br',
            'canal_envio' => 'whatsapp',
            'horas_validade' => 48,
        ]);

        $response->assertRedirect(route('admin.desfiliacao.links.index'));

        $this->assertDatabaseHas('desfiliacao_links', [
            'user_id' => $this->adminUser->id,
            'destinatario_nome' => 'Carlos Eduardo',
            'destinatario_email' => 'carlos@empresa.com.br',
            'tipo_link' => 'individual',
            'limite_usos' => 1,
        ]);

        $link = DesfiliacaoLink::where('user_id', $this->adminUser->id)->latest('id')->first();
        $this->assertNotNull($link);
        $this->assertEquals('individual', $link->tipo_link);
        $this->assertEquals(1, $link->limite_usos, 'Link individual deve ter cota estrita de 1 uso.');
        $this->assertEquals(0, $link->usos_atuais);
        $this->assertEquals('carlos@empresa.com.br', $link->destinatario_email);
        $this->assertFalse($link->isCompartilhado());
    }

    public function test_operador_pode_cancelar_link_nao_utilizado(): void
    {
        $link = DesfiliacaoLink::create([
            'token' => DesfiliacaoLink::gerarToken(),
            'user_id' => $this->adminUser->id,
            'horas_validade' => 24,
            'expira_em' => now()->addHours(24),
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.desfiliacao.links.cancelar', $link->id));

        $response->assertRedirect();
        $this->assertNotNull($link->fresh()->cancelado_em);
        $this->assertFalse($link->fresh()->isValido());
    }
}
