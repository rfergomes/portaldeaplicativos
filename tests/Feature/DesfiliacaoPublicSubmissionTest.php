<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Desfiliacao;
use App\Models\DesfiliacaoLink;
use App\Models\User;
use Tests\TestCase;

class DesfiliacaoPublicSubmissionTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_acesso_a_rota_publica_aberta_exibe_aviso_de_desativacao(): void
    {
        $response = $this->get(route('desfiliacao.index'));

        $response->assertOk();
        $response->assertSee('Atendimento Individualizado de Desfiliação');
        $response->assertSee('Preenchimento Público Desativado');
    }

    public function test_acesso_com_token_inexistente_renderiza_tela_de_link_invalido(): void
    {
        $response = $this->get(route('desfiliacao.solicitar.token', 'token-falso-inexistente'));

        $response->assertOk();
        $response->assertSee('Link Exclusivo Não Disponível');
    }

    public function test_acesso_com_token_expirado_bloqueia_e_informa_expiracao(): void
    {
        $link = DesfiliacaoLink::create([
            'token' => DesfiliacaoLink::gerarToken(),
            'user_id' => $this->user->id,
            'horas_validade' => 24,
            'expira_em' => now()->subHour(), // Já expirou
            'utilizado' => false,
        ]);

        $response = $this->get(route('desfiliacao.solicitar.token', $link->token));

        $response->assertOk();
        $response->assertSee('Este link expirou');
    }

    public function test_acesso_com_token_valido_carrega_select2_de_empresas(): void
    {
        \App\Models\Empresa::updateOrCreate(
            ['cnpj' => '12345678000199'],
            ['razao_social' => 'Empresa Select2 Teste S/A', 'ativo' => true]
        );

        $link = DesfiliacaoLink::create([
            'token' => DesfiliacaoLink::gerarToken(),
            'user_id' => $this->user->id,
            'horas_validade' => 24,
            'expira_em' => now()->addHours(24),
            'utilizado' => false,
        ]);

        $response = $this->get(route('desfiliacao.solicitar.token', $link->token));

        $response->assertOk();
        $response->assertSee('select2');
        $response->assertSee('Empresa Select2 Teste S/A');
        $response->assertSee('select name="empresa" id="empresa"', false);
    }

    public function test_submissao_unificada_valida_consome_token_e_cria_desfiliacao(): void
    {
        $link = DesfiliacaoLink::create([
            'token' => DesfiliacaoLink::gerarToken(),
            'user_id' => $this->user->id,
            'destinatario_nome' => 'Carlos Eduardo',
            'destinatario_contato' => '19987654321',
            'horas_validade' => 48,
            'expira_em' => now()->addHours(48),
            'utilizado' => false,
        ]);

        $payload = [
            'nome_completo' => 'Carlos Eduardo Ferreira',
            'cpf' => '11144477735', // CPF matematicamente válido
            'empresa' => 'Indústria Química Teste S/A',
            'email' => 'carlos.ferreira@teste.com',
            'telefone' => '(19) 98765-4321',
            'motivo' => 'Decisão pessoal',
            'aceite_lgpd' => '1',
            'assinatura_base64' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ];

        $response = $this->post(route('desfiliacao.solicitar.submit', $link->token), $payload);

        $desfiliacao = Desfiliacao::where('desfiliacao_link_id', $link->id)->first();
        $this->assertNotNull($desfiliacao);

        $response->assertRedirect(route('desfiliacao.solicitada.sucesso', $desfiliacao->id));

        $this->assertEquals('confirmada', $desfiliacao->status);
        $this->assertEquals($link->id, $desfiliacao->desfiliacao_link_id);
        $this->assertNotNull($desfiliacao->aceite_lgpd_em);
        $this->assertNotNull($desfiliacao->termo_hash);

        // Verifica que o link foi atomicamente invalidado para novos acessos
        $link->refresh();
        $this->assertTrue($link->utilizado);
        $this->assertNotNull($link->utilizado_em);
        $this->assertEquals($desfiliacao->id, $link->desfiliacao_id);

        // Tentativa de reutilizar o mesmo link é bloqueada
        $responseReuso = $this->get(route('desfiliacao.solicitar.token', $link->token));
        $responseReuso->assertSee('já foi utilizado');
    }
}
