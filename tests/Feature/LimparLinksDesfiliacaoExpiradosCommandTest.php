<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Desfiliacao;
use App\Models\DesfiliacaoLink;
use App\Models\User;
use Tests\TestCase;

class LimparLinksDesfiliacaoExpiradosCommandTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_comando_em_modo_dry_run_nao_exclui_registros(): void
    {
        // Cria link expirado há mais de 35 dias sem desfiliação
        $linkOrfao = DesfiliacaoLink::create([
            'token' => DesfiliacaoLink::gerarToken(),
            'user_id' => $this->user->id,
            'expira_em' => now()->subDays(35),
            'horas_validade' => 24,
            'limite_usos' => 1,
            'usos_atuais' => 0,
            'utilizado' => false,
        ]);

        $this->artisan('desfiliacao:limpar-links-expirados --dias=30 --dry-run')
            ->expectsOutputToContain('[SIMULAÇÃO]')
            ->assertSuccessful();

        $this->assertDatabaseHas('desfiliacao_links', ['id' => $linkOrfao->id]);
    }

    public function test_comando_exclui_links_orfaos_expirados_e_preserva_links_com_desfiliacao(): void
    {
        // 1. Link órfão expirado há 40 dias (DEVE ser excluído)
        $linkOrfao = DesfiliacaoLink::create([
            'token' => DesfiliacaoLink::gerarToken(),
            'user_id' => $this->user->id,
            'expira_em' => now()->subDays(40),
            'horas_validade' => 24,
            'limite_usos' => 1,
            'usos_atuais' => 0,
            'utilizado' => false,
        ]);

        // 2. Link expirado recente há 5 dias (DEVE ser preservado pois corte é 30 dias)
        $linkRecente = DesfiliacaoLink::create([
            'token' => DesfiliacaoLink::gerarToken(),
            'user_id' => $this->user->id,
            'expira_em' => now()->subDays(5),
            'horas_validade' => 24,
            'limite_usos' => 1,
            'usos_atuais' => 0,
            'utilizado' => false,
        ]);

        // 3. Link expirado há 40 dias COM desfiliação formalizada vinculada (NUNCA PODE SER EXCLUÍDO)
        $linkComDesfiliacao = DesfiliacaoLink::create([
            'token' => DesfiliacaoLink::gerarToken(),
            'user_id' => $this->user->id,
            'expira_em' => now()->subDays(40),
            'horas_validade' => 24,
            'limite_usos' => 1,
            'usos_atuais' => 1,
            'utilizado' => true,
        ]);

        $desfiliacao = Desfiliacao::create([
            'desfiliacao_link_id' => $linkComDesfiliacao->id,
            'nome_completo' => 'Trabalhador Teste Probatório',
            'cpf' => '99988877766',
            'empresa' => 'Empresa Teste Auditoria',
            'email' => 'teste@auditoria.com',
            'telefone' => '19988776655',
            'status' => 'confirmada',
        ]);

        $linkComDesfiliacao->update(['desfiliacao_id' => $desfiliacao->id]);

        $this->artisan('desfiliacao:limpar-links-expirados --dias=30')
            ->expectsOutputToContain('foram excluídos com sucesso')
            ->assertSuccessful();

        // Verifica que o órfão antigo foi deletado
        $this->assertDatabaseMissing('desfiliacao_links', ['id' => $linkOrfao->id]);

        // Verifica que o recente e o que tem desfiliação foram estritamente preservados
        $this->assertDatabaseHas('desfiliacao_links', ['id' => $linkRecente->id]);
        $this->assertDatabaseHas('desfiliacao_links', ['id' => $linkComDesfiliacao->id]);
        $this->assertDatabaseHas('desfiliacoes', ['id' => $desfiliacao->id]);
    }
}
