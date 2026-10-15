<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Desfiliacao;
use App\Models\DesfiliacaoLink;
use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\User;
use Tests\TestCase;

class DesfiliacaoLinkMultiploTest extends TestCase
{
    private User $representante;

    protected function setUp(): void
    {
        parent::setUp();

        $permProprios = Permissao::firstOrCreate(
            ['chave' => 'desfiliacoes.links.gerar.proprios'],
            ['nome' => 'Gerar Links Proprios', 'descricao' => 'Permite gerar links rapidos']
        );

        $perfil = Perfil::firstOrCreate(
            ['nome' => 'Representante Cota Teste'],
            ['descricao' => 'Representante']
        );
        $perfil->permissoes()->syncWithoutDetaching([$permProprios->id]);

        $this->representante = User::factory()->create([
            'email' => 'rep_cota_' . uniqid() . '@teste.com',
        ]);
        $this->representante->perfis()->attach($perfil);
    }

    public function test_operador_pode_gerar_link_compartilhado_com_cota_multi_uso(): void
    {
        $response = $this->actingAs($this->representante)
            ->post(route('representante.desfiliacao.links.store'), [
                'tipo_link' => 'compartilhado',
                'limite_usos' => 5,
                'horas_validade' => 24,
            ]);

        $response->assertRedirect(route('representante.desfiliacao.links'));

        $this->assertDatabaseHas('desfiliacao_links', [
            'user_id' => $this->representante->id,
            'tipo_link' => 'compartilhado',
            'limite_usos' => 5,
            'usos_atuais' => 0,
            'utilizado' => false,
        ]);
    }

    private function gerarCpfValido(): string
    {
        $n = [rand(1, 9)];
        for ($i = 1; $i < 9; $i++) {
            $n[] = rand(0, 9);
        }

        // Primeiro dígito verificador
        $d1 = 0;
        for ($i = 0; $i < 9; $i++) {
            $d1 += $n[$i] * (10 - $i);
        }
        $d1 = ((10 * $d1) % 11) % 10;
        $n[] = $d1;

        // Segundo dígito verificador
        $d2 = 0;
        for ($i = 0; $i < 10; $i++) {
            $d2 += $n[$i] * (11 - $i);
        }
        $d2 = ((10 * $d2) % 11) % 10;
        $n[] = $d2;

        return implode('', $n);
    }

    public function test_link_compartilhado_permite_multiplas_utilizacoes_e_bloqueia_apos_atingir_cota(): void
    {
        // 1. Cria link compartilhado com cota de 2 utilizações
        $link = DesfiliacaoLink::create([
            'token' => DesfiliacaoLink::gerarToken(),
            'user_id' => $this->representante->id,
            'tipo_link' => 'compartilhado',
            'limite_usos' => 2,
            'usos_atuais' => 0,
            'expira_em' => now()->addHours(24),
            'horas_validade' => 24,
            'utilizado' => false,
        ]);

        $this->assertTrue($link->isValido());
        $this->assertEquals(2, $link->vagas_restantes);

        // 2. Primeira submissão com sucesso
        $resp1 = $this->post(route('desfiliacao.solicitar.submit', $link->token), [
            'nome_completo' => 'Primeiro Solicitante',
            'cpf' => $this->gerarCpfValido(),
            'empresa' => 'Empresa Alfa Ltda',
            'email' => 'primeiro@teste.com',
            'telefone' => '19911112222',
            'motivo' => 'Motivo teste 1',
            'aceite_lgpd' => '1',
            'assinatura_base64' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $resp1->assertSessionHasNoErrors();
        $resp1->assertRedirect();
        $link->refresh();
        $this->assertEquals(1, $link->usos_atuais);
        $this->assertEquals(1, $link->vagas_restantes);
        $this->assertFalse($link->utilizado);
        $this->assertTrue($link->isValido());

        // 3. Segunda submissão (atinge o limite da cota)
        $resp2 = $this->post(route('desfiliacao.solicitar.submit', $link->token), [
            'nome_completo' => 'Segundo Solicitante',
            'cpf' => $this->gerarCpfValido(),
            'empresa' => 'Empresa Alfa Ltda',
            'email' => 'segundo@teste.com',
            'telefone' => '19922223333',
            'motivo' => 'Motivo teste 2',
            'aceite_lgpd' => '1',
            'assinatura_base64' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $resp2->assertSessionHasNoErrors();
        $resp2->assertRedirect();
        $link->refresh();
        $this->assertEquals(2, $link->usos_atuais);
        $this->assertEquals(0, $link->vagas_restantes);
        $this->assertTrue($link->utilizado);
        $this->assertFalse($link->isValido());
        $this->assertTrue($link->isEsgotado());

        // 4. Terceira tentativa de acesso ao formulário público deve exibir tela de link esgotado
        $respAcesso = $this->get(route('desfiliacao.solicitar.token', $link->token));
        $respAcesso->assertOk();
        $respAcesso->assertSee('atingiu o limite máximo de 2 utilizações permitidas');

        // 5. Tentativa de submissão excedente é bloqueada
        $resp3 = $this->post(route('desfiliacao.solicitar.submit', $link->token), [
            'nome_completo' => 'Terceiro Solicitante Excedente',
            'cpf' => $this->gerarCpfValido(),
            'empresa' => 'Empresa Alfa Ltda',
            'email' => 'terceiro@teste.com',
            'telefone' => '19933334444',
            'aceite_lgpd' => '1',
            'assinatura_base64' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $resp3->assertRedirect(route('desfiliacao.solicitar.token', $link->token));
        $resp3->assertSessionHas('error');

        // Confirma que foram criadas exatamente 2 desfiliações para este link
        $this->assertCount(2, Desfiliacao::where('desfiliacao_link_id', $link->id)->get());
    }

    public function test_operador_pode_gerar_link_sem_identificacao_nominal(): void
    {
        $response = $this->actingAs($this->representante)
            ->post(route('representante.desfiliacao.links.store'), [
                'tipo_link' => 'individual',
                'destinatario_nome' => null,
                'destinatario_contato' => null,
                'horas_validade' => 48,
            ]);

        $response->assertRedirect(route('representante.desfiliacao.links'));

        $this->assertDatabaseHas('desfiliacao_links', [
            'user_id' => $this->representante->id,
            'destinatario_nome' => null,
            'destinatario_contato' => null,
            'limite_usos' => 1,
        ]);
    }

    public function test_operador_pode_gerar_lote_de_links_individuais(): void
    {
        $response = $this->actingAs($this->representante)
            ->post(route('representante.desfiliacao.links.store'), [
                'tipo_link' => 'lote',
                'quantidade' => 4,
                'horas_validade' => 24,
            ]);

        $response->assertRedirect(route('representante.desfiliacao.links'));
        $response->assertSessionHas('links_lote');

        $this->assertCount(4, DesfiliacaoLink::where('user_id', $this->representante->id)
            ->where('tipo_link', 'lote')
            ->get());
    }
}
