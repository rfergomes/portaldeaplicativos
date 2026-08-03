<?php

namespace Tests\Feature\Protocolos;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProtocoloCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_tela_novo_protocolo_requer_auth(): void
    {
        $response = $this->get('/protocolos/create');
        $response->assertRedirect('/login');
    }

    public function test_usuario_autenticado_acessa_formulario(): void
    {
        \Illuminate\Support\Facades\Gate::before(fn () => true);

        $user = User::factory()->create();
        Empresa::factory()->create();

        $response = $this->actingAs($user)->get('/protocolos/create');

        $response->assertStatus(200);
        $response->assertSee('Novo Protocolo', false);
    }
}

