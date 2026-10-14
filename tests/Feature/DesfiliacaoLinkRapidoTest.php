<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\DesfiliacaoLink;
use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\User;
use Tests\TestCase;

class DesfiliacaoLinkRapidoTest extends TestCase
{
    private User $representante;
    private User $outroRepresentante;
    private User $usuarioSemPermissao;

    protected function setUp(): void
    {
        parent::setUp();

        $permProprios = Permissao::firstOrCreate(
            ['chave' => 'desfiliacoes.links.gerar.proprios'],
            ['nome' => 'Gerar Links Proprios', 'descricao' => 'Permite gerar links rapidos']
        );

        $perfil = Perfil::firstOrCreate(
            ['nome' => 'Representante Teste'],
            ['descricao' => 'Representante']
        );
        $perfil->permissoes()->syncWithoutDetaching([$permProprios->id]);

        $this->representante = User::factory()->create();
        $this->representante->perfis()->attach($perfil);

        $this->outroRepresentante = User::factory()->create();
        $this->outroRepresentante->perfis()->attach($perfil);

        $this->usuarioSemPermissao = User::factory()->create();
    }

    public function test_representante_pode_acessar_portal_rapido_e_emitir_link(): void
    {
        $response = $this->actingAs($this->representante)->get(route('representante.desfiliacao.links'));
        $response->assertOk();
        $response->assertSee('Emissão Rápida de Link Exclusivo');

        $responsePost = $this->actingAs($this->representante)->post(route('representante.desfiliacao.links.store'), [
            'destinatario_nome' => 'Fábio Alencar',
            'destinatario_contato' => '19988776655',
            'horas_validade' => 48,
        ]);

        $responsePost->assertRedirect(route('representante.desfiliacao.links'));
        $responsePost->assertSessionHas('whatsapp_url');

        $this->assertDatabaseHas('desfiliacao_links', [
            'user_id' => $this->representante->id,
            'destinatario_nome' => 'Fábio Alencar',
            'destinatario_contato' => '19988776655',
            'canal_envio' => 'whatsapp',
        ]);
    }

    public function test_representante_visualiza_apenas_seus_proprios_links_emitidos(): void
    {
        $linkProprio = DesfiliacaoLink::create([
            'token' => DesfiliacaoLink::gerarToken(),
            'user_id' => $this->representante->id,
            'destinatario_nome' => 'Trabalhador do Representante 1',
            'horas_validade' => 24,
            'expira_em' => now()->addHours(24),
        ]);

        $linkOutro = DesfiliacaoLink::create([
            'token' => DesfiliacaoLink::gerarToken(),
            'user_id' => $this->outroRepresentante->id,
            'destinatario_nome' => 'Trabalhador de Outro Dirigente',
            'horas_validade' => 24,
            'expira_em' => now()->addHours(24),
        ]);

        $response = $this->actingAs($this->representante)->get(route('representante.desfiliacao.links'));
        $response->assertOk();
        $response->assertSee('Trabalhador do Representante 1');
        $response->assertDontSee('Trabalhador de Outro Dirigente');
    }

    public function test_usuario_sem_permissao_tem_acesso_bloqueado_ao_portal_do_representante(): void
    {
        $response = $this->actingAs($this->usuarioSemPermissao)->get(route('representante.desfiliacao.links'));
        $response->assertForbidden();
    }
}
