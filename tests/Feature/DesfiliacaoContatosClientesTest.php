<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Protocolos\Contracts\ArOnlineClient;
use App\Domain\Protocolos\DTOs\ArOnlineSendPayload;
use App\Models\Cliente;
use App\Models\Desfiliacao;
use App\Models\Empresa;
use App\Models\EmpresaContato;
use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class DesfiliacaoContatosClientesTest extends TestCase
{
    private User $operador;
    private Empresa $empresa;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $perm = Permissao::firstOrCreate(
            ['chave' => 'desfiliacoes.visualizar'],
            ['nome' => 'Visualizar Desfiliações', 'descricao' => 'Permite triagem']
        );
        $permRecepcao = Permissao::firstOrCreate(
            ['chave' => 'desfiliacoes.recepcionar'],
            ['nome' => 'Recepcionar Desfiliações', 'descricao' => 'Permite recepção']
        );

        $perfil = Perfil::firstOrCreate(
            ['nome' => 'Operador Contatos Teste'],
            ['descricao' => 'Operador']
        );
        $perfil->permissoes()->syncWithoutDetaching([$perm->id, $permRecepcao->id]);

        $this->operador = User::factory()->create([
            'email' => 'operador_contatos_' . uniqid() . '@teste.com',
        ]);
        $this->operador->perfis()->attach($perfil);

        $cnpjAleatorio = sprintf("%02d.%03d.%03d/0001-%02d", rand(10, 99), rand(100, 999), rand(100, 999), rand(10, 99));

        $this->empresa = Empresa::create([
            'razao_social' => 'Empresa Teste Clientes S/A ' . uniqid(),
            'nome_fantasia' => 'Teste Clientes',
            'cnpj' => $cnpjAleatorio,
            'ativo' => true,
        ]);
    }

    public function test_despacho_automatico_utiliza_contatos_validos_da_tabela_clientes(): void
    {
        // 1. Cria contatos na tabela clientes vinculados à empresa
        $contatoAtivo = Cliente::create([
            'empresa_id' => $this->empresa->id,
            'nome' => 'Coordenador RH',
            'email' => 'rh.valido@empresateste.com',
            'ativo' => true,
            'email_valido' => true,
        ]);

        $contatoInativo = Cliente::create([
            'empresa_id' => $this->empresa->id,
            'nome' => 'Ex-Funcionário',
            'email' => 'inativo@empresateste.com',
            'ativo' => false,
            'email_valido' => true,
        ]);

        $contatoBounce = Cliente::create([
            'empresa_id' => $this->empresa->id,
            'nome' => 'Com Bounce',
            'email' => 'bounce@empresateste.com',
            'ativo' => true,
            'email_valido' => false,
            'email_bounce_code' => '550',
        ]);

        $desfiliacao = Desfiliacao::create([
            'nome_completo' => 'Trabalhador Teste',
            'cpf' => '11144477735',
            'empresa' => $this->empresa->razao_social,
            'email' => 'trabalhador@teste.com',
            'telefone' => '19988776655',
            'status' => 'confirmada',
            'aceite_lgpd_em' => now(),
            'assinatura_base64' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        // Simula mock de envio AR-Online esperando o e-mail válido da tabela clientes
        $arOnlineMock = Mockery::mock(ArOnlineClient::class);
        $arOnlineMock->shouldReceive('setToken')->byDefault();
        $arOnlineMock->shouldReceive('send')
            ->once()
            ->withArgs(function (ArOnlineSendPayload $payload) {
                return $payload->emailTo === 'rh.valido@empresateste.com';
            })
            ->andReturn('AR-CLIENTE-TEST-001');

        $this->app->instance(ArOnlineClient::class, $arOnlineMock);

        $contagemEmpresaContatosAntes = EmpresaContato::count();

        // 2. Operador recepciona a carta, deflagrando o despacho
        $response = $this->actingAs($this->operador)
            ->post(route('admin.desfiliacoes.recepcionar', $desfiliacao->id), [
                'observacao_recepcao' => 'Homologado com contatos da tabela clientes',
            ]);

        $response->assertRedirect();
        $desfiliacao->refresh();

        // Verifica que o envio foi feito para o e-mail do contato da tabela clientes
        $this->assertEquals('enviado', $desfiliacao->empresa_envio_status);
        $this->assertEquals('rh.valido@empresateste.com', $desfiliacao->empresa_email);
        $this->assertEquals('AR-CLIENTE-TEST-001', $desfiliacao->empresa_aronline_id);

        // Verifica que nenhuma gravação ocorreu na tabela empresa_contatos
        $this->assertEquals($contagemEmpresaContatosAntes, EmpresaContato::count());
    }

    public function test_operador_informa_email_manual_e_contato_e_gravado_em_clientes(): void
    {
        // Empresa sem nenhum contato cadastrado
        $desfiliacao = Desfiliacao::create([
            'nome_completo' => 'Trabalhador Sem Contato',
            'cpf' => '11144477735',
            'empresa' => $this->empresa->razao_social,
            'email' => 'trabalhador2@teste.com',
            'telefone' => '19988776655',
            'status' => 'recepcionada',
            'aceite_lgpd_em' => now(),
            'assinatura_base64' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $arOnlineMock = Mockery::mock(ArOnlineClient::class);
        $arOnlineMock->shouldReceive('setToken')->byDefault();
        $arOnlineMock->shouldReceive('send')
            ->once()
            ->withArgs(function (ArOnlineSendPayload $payload) {
                return $payload->emailTo === 'novo.rh@empresateste.com';
            })
            ->andReturn('AR-MANUAL-001');

        $this->app->instance(ArOnlineClient::class, $arOnlineMock);

        $contagemEmpresaContatosAntes = EmpresaContato::count();

        // Operador dispara despacho manual informando e-mail de RH
        $response = $this->actingAs($this->operador)
            ->post(route('admin.desfiliacoes.despachar-empresa', $desfiliacao->id), [
                'empresa_email' => 'novo.rh@empresateste.com',
            ]);

        $response->assertRedirect();

        // Confirma que o novo contato foi persistido na tabela clientes associado à empresa
        $this->assertDatabaseHas('clientes', [
            'empresa_id' => $this->empresa->id,
            'email' => 'novo.rh@empresateste.com',
            'ativo' => true,
            'email_valido' => true,
        ]);

        // Confirma que nenhuma gravação ocorreu em empresa_contatos
        $this->assertEquals($contagemEmpresaContatosAntes, EmpresaContato::count());
    }
}
