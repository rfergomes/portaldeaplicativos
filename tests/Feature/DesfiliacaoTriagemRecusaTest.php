<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\RecusaDesfiliacaoMail;
use App\Models\Desfiliacao;
use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DesfiliacaoTriagemRecusaTest extends TestCase
{
    private User $operador;
    private User $semPermissao;

    protected function setUp(): void
    {
        parent::setUp();

        $permRecusar = Permissao::firstOrCreate(
            ['chave' => 'desfiliacoes.recusar'],
            ['nome' => 'Recusar Desfiliacao', 'descricao' => 'Permite recusar']
        );
        $permVisualizar = Permissao::firstOrCreate(
            ['chave' => 'desfiliacoes.visualizar'],
            ['nome' => 'Visualizar Desfiliacao', 'descricao' => 'Permite visualizar']
        );
        $permRecepcionar = Permissao::firstOrCreate(
            ['chave' => 'desfiliacoes.recepcionar'],
            ['nome' => 'Recepcionar Desfiliacao', 'descricao' => 'Permite recepcionar']
        );

        $perfil = Perfil::firstOrCreate(
            ['nome' => 'Operador Triagem Teste'],
            ['descricao' => 'Operador de Triagem']
        );
        $perfil->permissoes()->syncWithoutDetaching([$permRecusar->id, $permVisualizar->id, $permRecepcionar->id]);

        $this->operador = User::factory()->create();
        $this->operador->perfis()->attach($perfil);

        $this->semPermissao = User::factory()->create();
    }

    public function test_operador_pode_recusar_solicitacao_com_justificativa_obrigatoria(): void
    {
        Mail::fake();

        $desfiliacao = Desfiliacao::create([
            'nome_completo' => 'Maria Silva',
            'cpf' => '22233344455',
            'empresa' => 'Indústria Delta',
            'email' => 'maria.silva@teste.com',
            'telefone' => '19988887777',
            'status' => 'confirmada',
        ]);

        $justificativa = 'Após consulta no ERP Ábaco, constatamos divergência de matrícula e empresa informada.';

        $response = $this->actingAs($this->operador)->post(route('admin.desfiliacoes.recusar', $desfiliacao->id), [
            'motivo_recusa' => $justificativa,
        ]);

        $response->assertRedirect(route('admin.desfiliacoes.index'));

        $desfiliacao->refresh();
        $this->assertEquals('recusada', $desfiliacao->status);
        $this->assertEquals($this->operador->id, $desfiliacao->recusado_por_user_id);
        $this->assertNotNull($desfiliacao->recusado_em);
        $this->assertEquals($justificativa, $desfiliacao->motivo_recusa);

        Mail::assertSent(RecusaDesfiliacaoMail::class, function ($mail) use ($desfiliacao, $justificativa) {
            return $mail->hasTo($desfiliacao->email) && $mail->motivoRecusa === $justificativa;
        });
    }

    public function test_recusa_sem_justificativa_suficiente_falha_na_validacao(): void
    {
        $desfiliacao = Desfiliacao::create([
            'nome_completo' => 'José Santos',
            'cpf' => '33344455566',
            'empresa' => 'Empresa Alfa',
            'email' => 'jose@teste.com',
            'telefone' => '19977776666',
            'status' => 'confirmada',
        ]);

        $response = $this->actingAs($this->operador)->post(route('admin.desfiliacoes.recusar', $desfiliacao->id), [
            'motivo_recusa' => 'curto', // Menos de 10 caracteres
        ]);

        $response->assertSessionHasErrors('motivo_recusa');
        $this->assertEquals('confirmada', $desfiliacao->fresh()->status);
    }

    public function test_usuario_sem_permissao_nao_pode_recusar_solicitacao(): void
    {
        $desfiliacao = Desfiliacao::create([
            'nome_completo' => 'Ana Paula',
            'cpf' => '44455566677',
            'empresa' => 'Empresa Beta',
            'email' => 'ana@teste.com',
            'telefone' => '19966665555',
            'status' => 'confirmada',
        ]);

        $response = $this->actingAs($this->semPermissao)->post(route('admin.desfiliacoes.recusar', $desfiliacao->id), [
            'motivo_recusa' => 'Justificativa válida com mais de dez caracteres.',
        ]);

        $response->assertForbidden();
    }
}
