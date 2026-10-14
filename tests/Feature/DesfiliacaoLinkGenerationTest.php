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

        $this->adminUser = User::factory()->create();
        $this->adminUser->perfis()->attach($perfil);

        $this->commonUser = User::factory()->create();
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
