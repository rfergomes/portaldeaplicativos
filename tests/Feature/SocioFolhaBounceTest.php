<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\TipoCliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class SocioFolhaBounceTest extends TestCase
{
    use RefreshDatabase;

    private $tipoCliente;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Grant all gate permissions automatically
        Gate::before(function () {
            return true;
        });

        // 2. Authenticate a user to pass auth middleware
        $this->user = User::factory()->create([
            'force_password_change' => false,
        ]);
        $this->actingAs($this->user);

        // 3. Create standard TipoCliente required by controller validation
        $this->tipoCliente = TipoCliente::create([
            'nome' => 'TEST TIPO',
            'ativo' => true,
        ]);
    }

    public function test_webhook_bounce_marks_corresponding_clients_as_invalid(): void
    {
        // 1. Create a client with a valid email
        $empresa = Empresa::create([
            'razao_social' => 'TEST COMPANY LTDA',
            'cnpj' => '12.345.678/0001-90',
            'ativo' => true,
        ]);

        $cliente = Cliente::create([
            'empresa_id' => $empresa->id,
            'tipo_cliente_id' => $this->tipoCliente->id,
            'nome' => 'TEST USER',
            'email' => 'test_bounce@example.com',
            'ativo' => true,
            'email_valido' => true,
        ]);

        // 2. Simulate the SMTP Webhook sending a bounce request
        $response = $this->postJson('/api/webhooks/smtp/bounces', [
            'to' => 'test_bounce@example.com',
            'bounce_code' => '5.1.1',
            'bounce_description' => 'O endereco de email especificado nao existe',
            'x-smtplw' => '999', // dummy ID
        ]);

        $response->assertStatus(200);

        // 3. Assert the client email is now invalid and contains the bounce details
        $cliente->refresh();
        $this->assertFalse($cliente->email_valido);
        $this->assertEquals('5.1.1', $cliente->email_bounce_code);
        $this->assertEquals('O endereco de email especificado nao existe', $cliente->email_bounce_description);
    }

    public function test_updating_client_email_resets_bounce_status(): void
    {
        $empresa = Empresa::create([
            'razao_social' => 'TEST COMPANY LTDA',
            'cnpj' => '12.345.678/0001-90',
            'ativo' => true,
        ]);

        $cliente = Cliente::create([
            'empresa_id' => $empresa->id,
            'tipo_cliente_id' => $this->tipoCliente->id,
            'nome' => 'TEST USER',
            'email' => 'old_email@example.com',
            'email_valido' => false,
            'email_bounce_code' => '5.1.1',
            'email_bounce_description' => 'Bounce error',
            'ativo' => true,
        ]);

        // When updating the email, it should auto-reactivate
        $response = $this->put(route('clientes.update', $cliente), [
            'tipo_cliente_id' => $this->tipoCliente->id,
            'nome' => 'TEST USER',
            'email' => 'new_email@example.com',
            'email_valido' => '0', // even if form says invalid, email changed should override it
        ]);

        $response->assertRedirect(route('empresas.show', $empresa->id));

        $cliente->refresh();
        $this->assertTrue($cliente->email_valido);
        $this->assertNull($cliente->email_bounce_code);
        $this->assertNull($cliente->email_bounce_description);
    }

    public function test_manually_reactivating_email_clears_bounce_status(): void
    {
        $empresa = Empresa::create([
            'razao_social' => 'TEST COMPANY LTDA',
            'cnpj' => '12.345.678/0001-90',
            'ativo' => true,
        ]);

        $cliente = Cliente::create([
            'empresa_id' => $empresa->id,
            'tipo_cliente_id' => $this->tipoCliente->id,
            'nome' => 'TEST USER',
            'email' => 'same_email@example.com',
            'email_valido' => false,
            'email_bounce_code' => '5.1.1',
            'email_bounce_description' => 'Bounce error',
            'ativo' => true,
        ]);

        // When sending email_valido = 1 without changing the email, it should reactivate it
        $response = $this->put(route('clientes.update', $cliente), [
            'tipo_cliente_id' => $this->tipoCliente->id,
            'nome' => 'TEST USER',
            'email' => 'same_email@example.com',
            'email_valido' => '1',
        ]);

        $response->assertRedirect(route('empresas.show', $empresa->id));

        $cliente->refresh();
        $this->assertTrue($cliente->email_valido);
        $this->assertNull($cliente->email_bounce_code);
        $this->assertNull($cliente->email_bounce_description);
    }
}
