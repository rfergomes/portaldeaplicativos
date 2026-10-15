<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Protocolos\Contracts\ArOnlineClient;
use App\Mail\TermoDesfiliacaoTrabalhadorMail;
use App\Models\Desfiliacao;
use App\Models\DesfiliacaoLink;
use App\Models\EmpresaContato;
use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class DesfiliacaoFluxoCompletoTest extends TestCase
{
    private User $operador;

    protected function setUp(): void
    {
        parent::setUp();

        // Configura permissões
        $permissoes = [
            'desfiliacoes.visualizar',
            'desfiliacoes.links.gerar',
            'desfiliacoes.recepcionar',
            'desfiliacoes.recusar',
            'desfiliacoes.baixar_abaco',
        ];

        $permissoesIds = [];
        foreach ($permissoes as $p) {
            $perm = Permissao::firstOrCreate(
                ['chave' => $p],
                ['nome' => $p, 'descricao' => $p]
            );
            $permissoesIds[] = $perm->id;
        }

        $perfil = Perfil::firstOrCreate(['nome' => 'Operador Completo'], ['descricao' => 'Operador']);
        $perfil->permissoes()->syncWithoutDetaching($permissoesIds);

        $this->operador = User::factory()->create();
        $this->operador->perfis()->attach($perfil);
    }

    public function test_fluxo_completo_de_desfiliacao_com_link_exclusivo_e_despacho_aronline(): void
    {
        Mail::fake();

        // 0. Cadastra empresa e contato corporativo na tabela clientes
        $empresa = \App\Models\Empresa::firstOrCreate(
            ['cnpj' => '11.222.333/0001-44'],
            [
                'razao_social' => 'Petroquímica Paulínia S/A',
                'ativo' => true,
            ]
        );
        $empresa->update(['razao_social' => 'Petroquímica Paulínia S/A', 'ativo' => true]);

        \App\Models\Cliente::firstOrCreate(
            ['empresa_id' => $empresa->id, 'email' => 'dp@petroquimica.com.br'],
            [
                'nome' => 'Departamento Pessoal',
                'ativo' => true,
                'email_valido' => true,
            ]
        )->update(['ativo' => true, 'email_valido' => true]);

        // Mock do AR-Online para validade jurídica
        $arOnlineMock = Mockery::mock(ArOnlineClient::class);
        $arOnlineMock->shouldReceive('send')
            ->once()
            ->andReturn('AR-INTEGRACAO-PONTAPONTA-777');
        $this->app->instance(ArOnlineClient::class, $arOnlineMock);

        // 1. Operador gera Link Exclusivo (US1)
        $resGerar = $this->actingAs($this->operador)->post(route('admin.desfiliacao.links.store'), [
            'destinatario_nome' => 'Guilherme Mendes',
            'destinatario_contato' => '19988771122',
            'canal_envio' => 'whatsapp',
            'horas_validade' => 48,
        ]);
        $resGerar->assertRedirect(route('admin.desfiliacao.links.index'));

        $link = DesfiliacaoLink::latest()->first();
        $this->assertNotNull($link);
        $this->assertTrue($link->isValido());

        // 2. Trabalhador tenta acessar rota sem token -> Bloqueado com tela informativa (US2)
        $resPublica = $this->get(route('desfiliacao.index'));
        $resPublica->assertOk();
        $resPublica->assertSee('Preenchimento Público Desativado');

        // 3. Trabalhador acessa com o Link Exclusivo e submete formulário unificado com rubrica (US2)
        $resForm = $this->get(route('desfiliacao.solicitar.token', $link->token));
        $resForm->assertOk();
        $resForm->assertSee('Requerimento de Desfiliação Sindical');

        $resSubmit = $this->post(route('desfiliacao.solicitar.submit', $link->token), [
            'nome_completo' => 'Guilherme Mendes',
            'cpf' => '11144477735',
            'empresa' => 'Petroquímica Paulínia S/A',
            'email' => 'guilherme.mendes@teste.com',
            'telefone' => '(19) 98877-1122',
            'motivo' => 'Mudança de setor',
            'aceite_lgpd' => '1',
            'assinatura_base64' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $desfiliacao = Desfiliacao::where('desfiliacao_link_id', $link->id)->latest('id')->first();
        $this->assertNotNull($desfiliacao);
        $resSubmit->assertRedirect(route('desfiliacao.solicitada.sucesso', $desfiliacao->id));

        // Link agora está utilizado e não pode ser reutilizado
        $link->refresh();
        $this->assertTrue($link->utilizado);
        $resReuso = $this->get(route('desfiliacao.solicitar.token', $link->token));
        $resReuso->assertSee('já foi utilizado');

        // 4. Operador confere dados no ERP Ábaco e homologa o Aceite Formal (US3 + US4)
        $this->assertEquals('confirmada', $desfiliacao->status);

        $resAceitar = $this->actingAs($this->operador)->post(route('admin.desfiliacoes.recepcionar', $desfiliacao->id), [
            'observacao_recepcao' => 'Conferido no ERP Ábaco. Matrícula sindical validada.',
        ]);
        $resAceitar->assertRedirect(route('admin.desfiliacoes.index'));

        $desfiliacao->refresh();
        $this->assertEquals('recepcionada', $desfiliacao->status);
        $this->assertEquals($this->operador->id, $desfiliacao->recepcionado_por_user_id);
        $this->assertNotNull($desfiliacao->recepcionado_em);

        // 5. Verifica despacho automático via AR-Online para empresa e e-mail para o trabalhador (US4)
        $this->assertEquals('enviado', $desfiliacao->empresa_envio_status);
        $this->assertEquals('AR-INTEGRACAO-PONTAPONTA-777', $desfiliacao->empresa_aronline_id);
        $this->assertEquals('dp@petroquimica.com.br', $desfiliacao->empresa_email);
        $this->assertNotNull($desfiliacao->empresa_notificada_em);

        Mail::assertSent(TermoDesfiliacaoTrabalhadorMail::class, function ($mail) use ($desfiliacao) {
            return $mail->hasTo($desfiliacao->email);
        });

        // 6. Consulta Pública de Autenticidade comprova a validade do documento emitido
        $resAutenticidade = $this->get(route('desfiliacao.validar', ['hash' => $desfiliacao->termo_hash]));
        $resAutenticidade->assertOk();
        $resAutenticidade->assertSee('DOCUMENTO AUTÊNTICO E ÍNTEGRO');
        $resAutenticidade->assertSee('Guilherme Mendes');
    }
}
