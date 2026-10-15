<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\DespacharTermoDesfiliacaoAction;
use App\Domain\Protocolos\Contracts\ArOnlineClient;
use App\Domain\Protocolos\DTOs\ArOnlineSendPayload;
use App\Mail\TermoDesfiliacaoTrabalhadorMail;
use App\Models\Desfiliacao;
use App\Models\EmpresaContato;
use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class DesfiliacaoDespachoEmailTest extends TestCase
{
    private User $operador;

    protected function setUp(): void
    {
        parent::setUp();

        $permRecepcao = Permissao::firstOrCreate(
            ['chave' => 'desfiliacoes.recepcionar'],
            ['nome' => 'Recepcionar Desfiliacao', 'descricao' => 'Permite recepcionar']
        );
        $perfil = Perfil::firstOrCreate(
            ['nome' => 'Operador Despacho Teste'],
            ['descricao' => 'Operador de Despacho']
        );
        $perfil->permissoes()->syncWithoutDetaching([$permRecepcao->id]);

        $this->operador = User::factory()->create();
        $this->operador->perfis()->attach($perfil);

        EmpresaContato::truncate();
    }

    public function test_despacho_automatico_com_empresa_cadastrada_envia_via_aronline(): void
    {
        Mail::fake();

        // Cadastra empresa e contato corporativo na tabela clientes
        $empresa = \App\Models\Empresa::firstOrCreate(
            ['cnpj' => '22.333.444/0001-55'],
            [
                'razao_social' => 'Química Integrada do Brasil S/A',
                'ativo' => true,
            ]
        );
        $empresa->update(['razao_social' => 'Química Integrada do Brasil S/A', 'ativo' => true]);

        \App\Models\Cliente::firstOrCreate(
            ['empresa_id' => $empresa->id, 'email' => 'rh@quimicaintegrada.com.br'],
            [
                'nome' => 'Recursos Humanos',
                'ativo' => true,
                'email_valido' => true,
            ]
        )->update(['ativo' => true, 'email_valido' => true]);

        $desfiliacao = Desfiliacao::create([
            'nome_completo' => 'Renato Barros',
            'cpf' => '55566677788',
            'empresa' => 'Química Integrada do Brasil S/A',
            'email' => 'renato.barros@teste.com',
            'telefone' => '19955554444',
            'status' => 'confirmada',
        ]);

        // Mock da API AR-Online
        $arOnlineMock = Mockery::mock(ArOnlineClient::class);
        $arOnlineMock->shouldReceive('send')
            ->once()
            ->withArgs(function (ArOnlineSendPayload $payload) use ($desfiliacao) {
                return $payload->emailTo === 'rh@quimicaintegrada.com.br' &&
                       $payload->customId === 'desfiliacao-' . $desfiliacao->id;
            })
            ->andReturn('AR-EXTERNO-999888');

        $this->app->instance(ArOnlineClient::class, $arOnlineMock);

        /** @var DespacharTermoDesfiliacaoAction $action */
        $action = $this->app->make(DespacharTermoDesfiliacaoAction::class);
        $resultado = $action->execute($desfiliacao);

        $this->assertTrue($resultado['trabalhador']);
        $this->assertTrue($resultado['empresa']);
        $this->assertEquals('AR-EXTERNO-999888', $resultado['empresa_aronline_id']);

        $desfiliacao->refresh();
        $this->assertEquals('enviado', $desfiliacao->empresa_envio_status);
        $this->assertEquals('AR-EXTERNO-999888', $desfiliacao->empresa_aronline_id);
        $this->assertNotNull($desfiliacao->empresa_notificada_em);

        Mail::assertSent(TermoDesfiliacaoTrabalhadorMail::class, function ($mail) use ($desfiliacao) {
            return $mail->hasTo($desfiliacao->email);
        });
    }

    public function test_despacho_com_empresa_sem_contato_marca_pendencia_para_operador(): void
    {
        Mail::fake();

        $desfiliacao = Desfiliacao::create([
            'nome_completo' => 'Juliana Costa',
            'cpf' => '66677788899',
            'empresa' => 'Fábrica Nova Sem Cadastro Ltda',
            'email' => 'juliana@teste.com',
            'telefone' => '19944443333',
            'status' => 'confirmada',
        ]);

        /** @var DespacharTermoDesfiliacaoAction $action */
        $action = $this->app->make(DespacharTermoDesfiliacaoAction::class);
        $resultado = $action->execute($desfiliacao);

        $this->assertTrue($resultado['trabalhador']);
        $this->assertFalse($resultado['empresa']);
        $this->assertEquals('pendente_contato', $resultado['empresa_status']);

        $desfiliacao->refresh();
        $this->assertEquals('pendente_contato', $desfiliacao->empresa_envio_status);
        $this->assertNull($desfiliacao->empresa_notificada_em);
    }

    public function test_operador_pode_informar_email_rh_e_disparar_via_aronline(): void
    {
        $empresa = \App\Models\Empresa::firstOrCreate(
            ['cnpj' => '33.444.555/0001-66'],
            [
                'razao_social' => 'Metalúrgica Gama',
                'ativo' => true,
            ]
        );
        $empresa->update(['razao_social' => 'Metalúrgica Gama', 'ativo' => true]);

        // Garante que não existem contatos prévios
        \App\Models\Cliente::where('empresa_id', $empresa->id)->delete();

        $desfiliacao = Desfiliacao::create([
            'nome_completo' => 'Marcos Dias',
            'cpf' => '77788899900',
            'empresa' => 'Metalúrgica Gama',
            'email' => 'marcos@teste.com',
            'telefone' => '19933332222',
            'status' => 'recepcionada',
            'empresa_envio_status' => 'pendente_contato',
        ]);

        $arOnlineMock = Mockery::mock(ArOnlineClient::class);
        $arOnlineMock->shouldReceive('send')
            ->once()
            ->andReturn('AR-MANUAL-12345');

        $this->app->instance(ArOnlineClient::class, $arOnlineMock);

        $response = $this->actingAs($this->operador)
            ->from(route('admin.desfiliacoes.index'))
            ->post(route('admin.desfiliacoes.notificar_empresa', $desfiliacao->id), [
                'email_rh' => 'rh@metalurgicagama.com.br',
                'salvar_contato' => '1',
            ]);

        $response->assertRedirect(route('admin.desfiliacoes.index'));

        $desfiliacao->refresh();
        $this->assertEquals('enviado', $desfiliacao->empresa_envio_status);
        $this->assertEquals('AR-MANUAL-12345', $desfiliacao->empresa_aronline_id);
        $this->assertEquals('rh@metalurgicagama.com.br', $desfiliacao->empresa_email);

        // Verifica que o contato foi salvo na tabela clientes e não em empresa_contatos
        $this->assertDatabaseHas('clientes', [
            'empresa_id' => $empresa->id,
            'email' => 'rh@metalurgicagama.com.br',
            'ativo' => 1,
            'email_valido' => 1,
        ]);

        $this->assertDatabaseMissing('empresa_contatos', [
            'email_rh' => 'rh@metalurgicagama.com.br',
        ]);
    }

    public function test_recepcao_utiliza_contatos_da_tabela_clientes_e_nao_grava_em_empresa_contatos(): void
    {
        Mail::fake();

        $cnpjTeste = '04.728.464/0001-70';
        $razaoTeste = 'COT CENTRO ORGANIZ DOS TRABALHADORES';

        // 1. Cadastra empresa na tabela oficial empresas
        $empresa = \App\Models\Empresa::firstOrCreate(
            ['cnpj' => $cnpjTeste],
            [
                'razao_social' => $razaoTeste,
                'nome_fantasia' => 'COT X',
                'telefone' => '(19) 3735-4903',
                'cidade' => 'CAMPINAS',
                'estado' => 'SP',
                'categoria' => 'QUIMICAS',
                'ativo' => true,
            ]
        );
        $empresa->update(['razao_social' => $razaoTeste, 'ativo' => true]);

        // 2. Cadastra contato corporativo vinculado na tabela clientes
        \App\Models\Cliente::firstOrCreate(
            ['empresa_id' => $empresa->id, 'email' => 'rizeldaquimicps@terra.com.br'],
            [
                'nome' => 'Rizelda RH',
                'ativo' => true,
                'email_valido' => true,
            ]
        )->update(['ativo' => true, 'email_valido' => true]);

        $desfiliacao = Desfiliacao::create([
            'nome_completo' => 'MARA CISLEY DA SILVA',
            'cpf' => '12345678901',
            'empresa' => $razaoTeste,
            'email' => 'mara@teste.com',
            'telefone' => '19988887777',
            'status' => 'confirmada',
        ]);

        // Mock do AR-Online para aceitar envio
        $arOnlineMock = Mockery::mock(ArOnlineClient::class);
        $arOnlineMock->shouldReceive('send')
            ->once()
            ->withArgs(function (ArOnlineSendPayload $payload) {
                return $payload->emailTo === 'rizeldaquimicps@terra.com.br';
            })
            ->andReturn('AR-SINCRONIZADO-777');

        $this->app->instance(ArOnlineClient::class, $arOnlineMock);

        // Operador dá o aceite / recepção formal
        $response = $this->actingAs($this->operador)
            ->post(route('admin.desfiliacoes.recepcionar', $desfiliacao->id));

        $response->assertRedirect(route('admin.desfiliacoes.index'));

        // Verifica que NENHUM contato foi gravado na tabela empresa_contatos
        $this->assertDatabaseMissing('empresa_contatos', [
            'razao_social' => $razaoTeste,
        ]);

        // Verifica que a desfiliação recebeu o e-mail da tabela clientes e foi notificada
        $desfiliacao->refresh();
        $this->assertEquals('recepcionada', $desfiliacao->status);
        $this->assertEquals('rizeldaquimicps@terra.com.br', $desfiliacao->empresa_email);
        $this->assertEquals('enviado', $desfiliacao->empresa_envio_status);
        $this->assertEquals('AR-SINCRONIZADO-777', $desfiliacao->empresa_aronline_id);
    }
}

