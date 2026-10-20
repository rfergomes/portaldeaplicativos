<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendKwikNotificationJob;
use App\Models\DesfiliacaoLink;
use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\User;
use App\Models\WhatsappTemplate;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DesfiliacaoLinkKwikTest extends TestCase
{
    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $permissao = Permissao::firstOrCreate(
            ['chave' => 'desfiliacoes.links.gerar'],
            ['nome' => 'Gerenciar Links', 'descricao' => 'Permite gerar links']
        );

        $perfil = Perfil::firstOrCreate(
            ['nome' => 'Operador KWIK Teste'],
            ['descricao' => 'Operador']
        );
        $perfil->permissoes()->syncWithoutDetaching([$permissao->id]);

        $this->adminUser = User::factory()->create(['email' => 'admin_kwik_' . uniqid() . '@example.com']);
        $this->adminUser->perfis()->attach($perfil);
    }

    public function test_geracao_de_link_com_canal_kwik_dispara_job_com_template_selecionado(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->adminUser)->post(route('admin.desfiliacao.links.store'), [
            'tipo_link' => 'individual',
            'destinatario_nome' => 'Maria Silva',
            'destinatario_contato' => '19988776655',
            'destinatario_email' => 'maria@teste.com',
            'canal_envio' => 'kwik',
            'kwik_template' => 'atendimento_inicial',
            'horas_validade' => 48,
        ]);

        $response->assertRedirect(route('admin.desfiliacao.links.index'));

        Queue::assertPushed(SendKwikNotificationJob::class, function ($job) {
            return $job->telefone === '19988776655'
                && $job->template === 'atendimento_inicial'
                && count($job->bodyArgs) === 3;
        });
    }

    public function test_disparo_avulso_via_rota_enviar_kwik(): void
    {
        Queue::fake();

        $link = DesfiliacaoLink::create([
            'token' => DesfiliacaoLink::gerarToken(),
            'user_id' => $this->adminUser->id,
            'tipo_link' => 'individual',
            'limite_usos' => 1,
            'usos_atuais' => 0,
            'destinatario_nome' => 'Roberto Carlos',
            'destinatario_contato' => '19977665544',
            'destinatario_email' => 'roberto@teste.com',
            'horas_validade' => 24,
            'expira_em' => now()->addHours(24),
        ]);

        $response = $this->actingAs($this->adminUser)->post(
            route('admin.desfiliacao.links.enviar_kwik', $link->id),
            ['template_nome' => 'portal_verao_cefol']
        );

        $response->assertRedirect();
        $response->assertSessionHas('status');

        Queue::assertPushed(SendKwikNotificationJob::class, function ($job) {
            return $job->telefone === '19977665544'
                && $job->template === 'portal_verao_cefol'
                && $job->bodyArgs[0] === 'Roberto Carlos';
        });
    }

    public function test_disparo_kwik_falha_amigavelmente_se_contato_estiver_vazio(): void
    {
        Queue::fake();

        $link = DesfiliacaoLink::create([
            'token' => DesfiliacaoLink::gerarToken(),
            'user_id' => $this->adminUser->id,
            'tipo_link' => 'individual',
            'limite_usos' => 1,
            'usos_atuais' => 0,
            'destinatario_contato' => null,
            'horas_validade' => 24,
            'expira_em' => now()->addHours(24),
        ]);

        $response = $this->actingAs($this->adminUser)->post(
            route('admin.desfiliacao.links.enviar_kwik', $link->id),
            ['template_nome' => 'atendimento_inicial']
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
        Queue::assertNothingPushed();
    }
}
