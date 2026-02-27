<?php

namespace Tests\Feature\Eventos;

use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventosScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_eventos_e_carregado(): void
    {
        $user = User::factory()->create();
        Evento::factory()->create(['nome' => 'Evento Teste']);

        $response = $this->actingAs($user)->get('/eventos');

        $response->assertStatus(200);
        $response->assertSee('Controle de Eventos', false);
        $response->assertSee('Evento Teste', false);
    }
}

