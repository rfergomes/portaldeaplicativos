<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Demanda;
use App\Models\DemandaChecklist;
use App\Models\Perfil;
use App\Jobs\SendKwikNotificationJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DemandaTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $adminPerfil;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Seed profiles and permissions
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        // 2. Create user
        $this->user = User::factory()->create([
            'force_password_change' => false,
        ]);

        $this->adminPerfil = Perfil::where('nome', 'Administrador')->first();
        $this->user->perfis()->attach($this->adminPerfil->id);

        $this->actingAs($this->user);
    }

    public function test_can_view_demanda_index()
    {
        $demanda = Demanda::create([
            'titulo' => 'Test Demand',
            'descricao' => 'This is a test description',
            'prioridade' => 'media',
            'criador_id' => $this->user->id,
            'tipo_responsavel' => 'usuario',
            'responsavel_usuario_id' => $this->user->id,
            'status' => 'aberta',
        ]);

        $response = $this->get(route('demandas.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Demand');
    }

    public function test_can_create_internal_demanda()
    {
        $assignee = User::factory()->create();

        $response = $this->post(route('demandas.store'), [
            'titulo' => 'Repair AC Unit',
            'descricao' => 'AC unit is leaking in the office.',
            'prioridade' => 'alta',
            'tipo_responsavel' => 'usuario',
            'responsavel_usuario_id' => $assignee->id,
            'checklist_items' => ['Turn off AC', 'Fix leak', 'Turn on AC']
        ]);

        $response->assertRedirect(route('demandas.index'));
        $this->assertDatabaseHas('demandas', [
            'titulo' => 'Repair AC Unit',
            'tipo_responsavel' => 'usuario',
            'responsavel_usuario_id' => $assignee->id,
            'status' => 'aberta',
        ]);

        $demanda = Demanda::where('titulo', 'Repair AC Unit')->first();
        $this->assertCount(3, $demanda->checklists);
        $this->assertDatabaseHas('demanda_checklists', [
            'demanda_id' => $demanda->id,
            'item' => 'Fix leak',
            'concluido' => false,
        ]);
    }

    public function test_creating_external_demanda_dispatches_whatsapp_job()
    {
        Queue::fake();

        $response = $this->post(route('demandas.store'), [
            'titulo' => 'Fix Gate Lock',
            'descricao' => 'Repair the external gate keylock.',
            'prioridade' => 'urgente',
            'tipo_responsavel' => 'externo',
            'responsavel_nome' => 'John Locksmith',
            'responsavel_telefone' => '(11) 99999-8888',
        ]);

        $response->assertRedirect(route('demandas.index'));
        
        $this->assertDatabaseHas('demandas', [
            'titulo' => 'Fix Gate Lock',
            'tipo_responsavel' => 'externo',
            'responsavel_nome' => 'John Locksmith',
            'responsavel_telefone' => '11999998888',
            'status' => 'aguardando',
        ]);

        Queue::assertPushed(SendKwikNotificationJob::class, function ($job) {
            return $job->telefone === '11999998888' && $job->template === 'nova_demanda_externa';
        });
    }

    public function test_can_toggle_checklist_item_via_ajax()
    {
        $demanda = Demanda::create([
            'titulo' => 'Test Checklist',
            'descricao' => 'Details',
            'criador_id' => $this->user->id,
            'tipo_responsavel' => 'usuario',
            'responsavel_usuario_id' => $this->user->id,
            'status' => 'aberta',
        ]);

        $item = $demanda->checklists()->create([
            'item' => 'First task',
            'concluido' => false
        ]);

        $response = $this->postJson(route('demandas.checklists.toggle', [$demanda->id, $item->id]), [
            'concluido' => true
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'progresso' => 100]);

        $item->refresh();
        $this->assertTrue($item->concluido);
    }

    public function test_can_register_devolutiva()
    {
        $demanda = Demanda::create([
            'titulo' => 'Test Devolutiva',
            'descricao' => 'Details',
            'criador_id' => $this->user->id,
            'tipo_responsavel' => 'usuario',
            'responsavel_usuario_id' => $this->user->id,
            'status' => 'aberta',
        ]);

        $response = $this->post(route('demandas.devolutiva', $demanda->id), [
            'status' => 'executada',
            'motivo_devolutiva' => 'Finished successfully.'
        ]);

        $response->assertRedirect(route('demandas.show', $demanda->id));

        $demanda->refresh();
        $this->assertEquals('executada', $demanda->status);
        $this->assertEquals('Finished successfully.', $demanda->motivo_devolutiva);
        $this->assertNotNull($demanda->devolutiva_em);
    }

    public function test_can_forward_demanda_to_another_responsible()
    {
        $demanda = Demanda::create([
            'titulo' => 'Test Forwarding',
            'descricao' => 'Details',
            'criador_id' => $this->user->id,
            'tipo_responsavel' => 'usuario',
            'responsavel_usuario_id' => $this->user->id,
            'status' => 'aberta',
        ]);

        $newAssignee = User::factory()->create();

        $response = $this->post(route('demandas.reencaminhar', $demanda->id), [
            'tipo_responsavel' => 'usuario',
            'responsavel_usuario_id' => $newAssignee->id,
            'motivo_reencaminhamento' => 'Not my expertise.',
        ]);

        $response->assertRedirect(route('demandas.show', $demanda->id));

        $demanda->refresh();
        $this->assertEquals('usuario', $demanda->tipo_responsavel);
        $this->assertEquals($newAssignee->id, $demanda->responsavel_usuario_id);
        
        $this->assertDatabaseHas('demanda_historicos', [
            'demanda_id' => $demanda->id,
            'acao' => 'encaminhada',
        ]);
    }
}
