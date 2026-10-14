<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\ConfirmacaoDesfiliacaoMail;
use App\Mail\NotificacaoDiretoriaDesfiliacaoMail;
use App\Mail\TermoDesfiliacaoTrabalhadorMail;
use App\Models\Desfiliacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class DesfiliacaoTest extends TestCase
{
    // Usamos transactions para isolar os testes sem apagar a base
    protected function tearDown(): void
    {
        Desfiliacao::where('cpf', '11144477735')
            ->orWhere('cpf', '111.444.777-35')
            ->delete();

        parent::tearDown();
    }

    public function test_exibe_formulario_publico_de_solicitacao(): void
    {
        $response = $this->get(route('desfiliacao.create'));

        $response->assertStatus(200);
        $response->assertSee('Requerimento de Desfiliação Sindical');
        $response->assertSee('Nome Completo do Trabalhador');
        $response->assertDontSee('Matrícula Funcional');
        $response->assertDontSee('name="matricula"', false);
    }

    public function test_rota_raiz_desfiliacao_exibe_diretamente_formulario(): void
    {
        $response = $this->get('/desfiliacao');

        $response->assertStatus(200);
        $response->assertSee('Requerimento de Desfiliação Sindical');
    }

    public function test_rota_desfilizacao_redireciona_para_desfiliacao(): void
    {
        $response = $this->get('/desfilizacao');

        $response->assertRedirect('/desfiliacao');
    }

    public function test_valida_campos_obrigatorios_ao_solicitar(): void
    {
        $response = $this->post(route('desfiliacao.store'), []);

        $response->assertSessionHasErrors(['nome_completo', 'cpf', 'empresa', 'email', 'telefone']);
    }

    public function test_valida_cpf_invalido(): void
    {
        $response = $this->post(route('desfiliacao.store'), [
            'nome_completo' => 'Trabalhador Teste',
            'cpf' => '111.222.333-44', // CPF inválido
            'empresa' => 'Empresa Teste',
            'email' => 'teste@email.com',
            'telefone' => '(19) 99999-9999',
        ]);

        $response->assertSessionHasErrors('cpf');
    }

    public function test_cria_solicitacao_pendente_e_envia_email_com_link_assinado(): void
    {
        Mail::fake();

        $dados = [
            'nome_completo' => 'Carlos Eduardo Ferreira',
            'cpf' => '111.444.777-35', // CPF matematicamente válido
            'matricula' => '12345',
            'empresa' => 'Indústria Teste S/A',
            'email' => 'carlos.ferreira.teste@example.com',
            'telefone' => '(19) 98765-4321',
            'motivo' => 'Decisão pessoal',
        ];

        $response = $this->post(route('desfiliacao.store'), $dados);

        $response->assertRedirect(route('desfiliacao.aguardando'));
        $response->assertSessionHas('email', 'carlos.ferreira.teste@example.com');

        $this->assertDatabaseHas('desfiliacoes', [
            'cpf' => '111.444.777-35',
            'email' => 'carlos.ferreira.teste@example.com',
            'status' => 'pendente',
        ]);

        Mail::assertSent(ConfirmacaoDesfiliacaoMail::class, function ($mail) {
            return $mail->hasTo('carlos.ferreira.teste@example.com');
        });
    }

    public function test_impede_solicitacao_duplicada_para_mesmo_cpf_nas_ultimas_24h(): void
    {
        Desfiliacao::create([
            'nome_completo' => 'Carlos Eduardo Ferreira',
            'cpf' => '111.444.777-35',
            'empresa' => 'Indústria Teste S/A',
            'email' => 'carlos.ferreira.teste@example.com',
            'telefone' => '(19) 98765-4321',
            'status' => 'pendente',
        ]);

        $response = $this->post(route('desfiliacao.store'), [
            'nome_completo' => 'Outro Nome Mesmo CPF',
            'cpf' => '111.444.777-35',
            'empresa' => 'Outra Empresa',
            'email' => 'outro@email.com',
            'telefone' => '(19) 91111-2222',
        ]);

        $response->assertSessionHasErrors('cpf');
    }

    public function test_acesso_a_tela_de_confirmacao_com_link_assinado_valido(): void
    {
        $desfiliacao = Desfiliacao::create([
            'nome_completo' => 'Carlos Eduardo Ferreira',
            'cpf' => '111.444.777-35',
            'empresa' => 'Indústria Teste S/A',
            'email' => 'carlos.ferreira.teste@example.com',
            'telefone' => '(19) 98765-4321',
            'status' => 'pendente',
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'desfiliacao.confirmar',
            now()->addHours(24),
            ['id' => $desfiliacao->id]
        );

        $response = $this->get($signedUrl);

        $response->assertStatus(200);
        $response->assertSee('Assinatura Eletrônica do Requerimento');
        $response->assertSee('Carlos Eduardo Ferreira');
        $response->assertDontSee('Matrícula Funcional');
    }

    public function test_rejeita_acesso_a_tela_de_confirmacao_com_link_invalido(): void
    {
        $desfiliacao = Desfiliacao::create([
            'nome_completo' => 'Carlos Eduardo Ferreira',
            'cpf' => '111.444.777-35',
            'empresa' => 'Indústria Teste S/A',
            'email' => 'carlos.ferreira.teste@example.com',
            'telefone' => '(19) 98765-4321',
            'status' => 'pendente',
        ]);

        // URL sem assinatura válida
        $urlSemAssinatura = route('desfiliacao.confirmar', ['id' => $desfiliacao->id]);

        $response = $this->get($urlSemAssinatura);

        $response->assertStatus(403);
    }

    public function test_finaliza_desfiliacao_e_gera_termo_e_hash_e_emails(): void
    {
        Mail::fake();
        Storage::fake('local');

        $desfiliacao = Desfiliacao::create([
            'nome_completo' => 'Carlos Eduardo Ferreira',
            'cpf' => '111.444.777-35',
            'empresa' => 'Indústria Teste S/A',
            'email' => 'carlos.ferreira.teste@example.com',
            'telefone' => '(19) 98765-4321',
            'status' => 'pendente',
        ]);

        $urlFinalizar = URL::temporarySignedRoute(
            'desfiliacao.finalizar',
            now()->addHours(24),
            ['id' => $desfiliacao->id]
        );

        // String base64 simulada de imagem PNG de assinatura
        $base64Assinatura = 'data:image/png;base64,' . base64_encode(str_repeat('x', 600));

        $response = $this->post($urlFinalizar, [
            'assinatura_base64' => $base64Assinatura,
            'aceite_termos' => '1',
        ]);

        $response->assertRedirect(route('desfiliacao.concluido', ['id' => $desfiliacao->id]));

        $desfiliacao->refresh();
        $this->assertEquals('confirmada', $desfiliacao->status);
        $this->assertNotNull($desfiliacao->termo_hash);
        $this->assertNotNull($desfiliacao->pdf_path);
        $this->assertNotNull($desfiliacao->confirmado_em);

        Storage::disk('local')->assertExists($desfiliacao->pdf_path);

        $htmlPdf = view('pdf.termo_desfiliacao', ['desfiliacao' => $desfiliacao])->render();
        $this->assertStringNotContainsString('matrícula funcional', mb_strtolower($htmlPdf));

        Mail::assertSent(NotificacaoDiretoriaDesfiliacaoMail::class);
        Mail::assertSent(TermoDesfiliacaoTrabalhadorMail::class);
    }

    public function test_consulta_publica_de_autenticidade_por_hash(): void
    {
        $hashExemplo = hash('sha256', 'teste-integridade');

        $desfiliacao = Desfiliacao::create([
            'nome_completo' => 'Carlos Eduardo Ferreira',
            'cpf' => '111.444.777-35',
            'empresa' => 'Indústria Teste S/A',
            'email' => 'carlos.ferreira.teste@example.com',
            'telefone' => '(19) 98765-4321',
            'status' => 'confirmada',
            'confirmado_em' => now(),
            'termo_hash' => $hashExemplo,
        ]);

        // Status: confirmada (Pendente de Recepção)
        $response = $this->get(route('desfiliacao.validar', ['hash' => $hashExemplo]));
        $response->assertStatus(200);
        $response->assertSee('DOCUMENTO AUTÊNTICO E ÍNTEGRO');
        $response->assertSee('Carlos Eduardo Ferreira');
        $response->assertSee('Aguardando Ciência Interna');

        // Status: recepcionada (2ª Etapa: Baixa no Ábaco)
        $desfiliacao->update([
            'status' => 'recepcionada',
            'recepcionado_em' => now(),
        ]);
        $responseRecepcionada = $this->get(route('desfiliacao.validar', ['hash' => $hashExemplo]));
        $responseRecepcionada->assertStatus(200);
        $responseRecepcionada->assertSee('DOCUMENTO AUTÊNTICO E ÍNTEGRO');
        $responseRecepcionada->assertSee('Recepcionada pelo Sindicato');

        // Status: efetivada (Baixa concluída no ERP Ábaco)
        $desfiliacao->update([
            'status' => 'efetivada',
            'efetivado_em' => now(),
            'protocolo_abaco' => 'ABACO-12345',
        ]);
        $responseEfetivada = $this->get(route('desfiliacao.validar', ['hash' => $hashExemplo]));
        $responseEfetivada->assertStatus(200);
        $responseEfetivada->assertSee('DOCUMENTO AUTÊNTICO E ÍNTEGRO');
        $responseEfetivada->assertSee('Desfiliação Efetivada');
        $responseEfetivada->assertSee('ABACO-12345');
    }

    public function test_consulta_publica_de_autenticidade_com_hash_inexistente(): void
    {
        $response = $this->get(route('desfiliacao.validar', ['hash' => 'hash-inexistente-12345']));

        $response->assertStatus(200);
        $response->assertSee('DOCUMENTO NÃO ENCONTRADO OU INVÁLIDO');
    }

    protected function criarUsuarioAdmin(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $perfil = \App\Models\Perfil::firstOrCreate(
            ['nome' => 'Administrador'],
            ['descricao' => 'Administrador Geral']
        );
        $user->perfis()->syncWithoutDetaching([$perfil->id]);
        return $user;
    }

    public function test_painel_administrativo_lista_desfiliacoes_para_usuario_autenticado(): void
    {
        $user = $this->criarUsuarioAdmin();

        $desfiliacao = Desfiliacao::create([
            'nome_completo' => 'Carlos Eduardo Ferreira',
            'cpf' => '111.444.777-35',
            'empresa' => 'Indústria Teste S/A',
            'email' => 'carlos.ferreira.teste@example.com',
            'telefone' => '(19) 98765-4321',
            'status' => 'confirmada',
            'confirmado_em' => now(),
            'termo_hash' => hash('sha256', 'teste-admin'),
        ]);

        $response = $this->actingAs($user)->get(route('admin.desfiliacoes.index'));

        $response->assertStatus(200);
        $response->assertSee('Gestão de Desfiliações Sindicais');
        $response->assertSee('Carlos Eduardo Ferreira');
        $response->assertSee('Indústria Teste S/A');

        $user->delete();
    }

    public function test_api_detalhes_de_auditoria_para_modal_administrativo(): void
    {
        $user = $this->criarUsuarioAdmin();

        $desfiliacao = Desfiliacao::create([
            'nome_completo' => 'Carlos Eduardo Ferreira',
            'cpf' => '111.444.777-35',
            'empresa' => 'Indústria Teste S/A',
            'email' => 'carlos.ferreira.teste@example.com',
            'telefone' => '(19) 98765-4321',
            'status' => 'confirmada',
            'confirmado_em' => now(),
            'termo_hash' => hash('sha256', 'teste-modal'),
            'ip_solicitacao' => '192.168.1.100',
            'ip_confirmacao' => '192.168.1.101',
        ]);

        $response = $this->actingAs($user)->get(route('admin.desfiliacoes.show', ['id' => $desfiliacao->id]));

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $desfiliacao->id,
            'nome_completo' => 'Carlos Eduardo Ferreira',
            'empresa' => 'Indústria Teste S/A',
            'ip_solicitacao' => '192.168.1.100',
            'ip_confirmacao' => '192.168.1.101',
        ]);

        $user->delete();
    }

    public function test_operador_pode_recepcionar_carta_de_desfiliacao_e_estampar_protocolo(): void
    {
        $user = $this->criarUsuarioAdmin(['name' => 'Operador Recepção']);

        $desfiliacao = Desfiliacao::create([
            'nome_completo' => 'Carlos Eduardo Ferreira',
            'cpf' => '111.444.777-35',
            'empresa' => 'Indústria Teste S/A',
            'email' => 'carlos.ferreira.teste@example.com',
            'telefone' => '(19) 98765-4321',
            'status' => 'confirmada',
            'confirmado_em' => now(),
            'termo_hash' => hash('sha256', 'teste-recepcao'),
        ]);

        $response = $this->actingAs($user)->post(route('admin.desfiliacoes.recepcionar', ['id' => $desfiliacao->id]), [
            'observacao_recepcao' => 'Carta conferida e recebida no setor de arrecadação.',
        ]);

        $response->assertRedirect(route('admin.desfiliacoes.index'));
        $response->assertSessionHas('success');

        $desfiliacao->refresh();

        $this->assertTrue($desfiliacao->isRecepcionada());
        $this->assertSame('recepcionada', $desfiliacao->status);
        $this->assertSame($user->id, $desfiliacao->recepcionado_por_user_id);
        $this->assertNotNull($desfiliacao->recepcionado_em);
        $this->assertSame('Carta conferida e recebida no setor de arrecadação.', $desfiliacao->observacao_recepcao);

        $user->delete();
    }

    public function test_operador_pode_efetivar_desfiliacao_e_registrar_baixa_no_erp_abaco(): void
    {
        $user = $this->criarUsuarioAdmin(['name' => 'Operador Arrecadação']);

        $desfiliacao = Desfiliacao::create([
            'nome_completo' => 'Carlos Eduardo Ferreira',
            'cpf' => '111.444.777-35',
            'empresa' => 'Indústria Teste S/A',
            'email' => 'carlos.ferreira.teste@example.com',
            'telefone' => '(19) 98765-4321',
            'status' => 'recepcionada',
            'confirmado_em' => now(),
            'recepcionado_em' => now(),
            'recepcionado_por_user_id' => $user->id,
            'termo_hash' => hash('sha256', 'teste-efetivar'),
        ]);

        $response = $this->actingAs($user)->post(route('admin.desfiliacoes.efetivar', ['id' => $desfiliacao->id]), [
            'protocolo_abaco' => 'ABACO-2026-99',
            'observacao_efetivacao' => 'Baixa realizada com sucesso no cadastro ERP Ábaco.',
        ]);

        $response->assertRedirect(route('admin.desfiliacoes.index'));
        $response->assertSessionHas('success');

        $desfiliacao->refresh();

        $this->assertTrue($desfiliacao->isEfetivada());
        $this->assertSame('efetivada', $desfiliacao->status);
        $this->assertSame($user->id, $desfiliacao->efetivado_por_user_id);
        $this->assertNotNull($desfiliacao->efetivado_em);
        $this->assertSame('ABACO-2026-99', $desfiliacao->protocolo_abaco);
        $this->assertSame('Baixa realizada com sucesso no cadastro ERP Ábaco.', $desfiliacao->observacao_efetivacao);

        $user->delete();
    }

    public function test_view_composer_carrega_alertas_de_desfiliacao_e_modal_proativo(): void
    {
        $user = $this->criarUsuarioAdmin(['name' => 'Admin Notificacao']);

        $desfiliacao = Desfiliacao::create([
            'nome_completo' => 'Carlos Eduardo Ferreira',
            'cpf' => '111.444.777-35',
            'empresa' => 'Indústria Teste S/A',
            'email' => 'carlos.ferreira.teste@example.com',
            'telefone' => '(19) 98765-4321',
            'status' => 'confirmada',
            'confirmado_em' => now(),
            'termo_hash' => hash('sha256', 'teste-composer'),
        ]);

        $response = $this->actingAs($user)->get(route('admin.desfiliacoes.index'));

        $response->assertStatus(200);
        $response->assertSee('Nova Carta (Pendente Recepção)');
        $response->assertSee('modalAlertaDesfiliacoes');
        $response->assertSee('Carlos Eduardo Ferreira');
        $response->assertSee('1ª Etapa: Pendente Recepção');

        $user->delete();
    }

    public function test_usuario_sem_permissao_recebe_403_e_com_permissao_acessa(): void
    {
        $user = User::factory()->create(['name' => 'Usuario Sem Permissao']);

        // Sem permissão: deve receber 403
        $response = $this->actingAs($user)->get(route('admin.desfiliacoes.index'));
        $response->assertStatus(403);

        // Atribuir permissão desfiliacoes.visualizar
        $permissao = \App\Models\Permissao::firstOrCreate(
            ['chave' => 'desfiliacoes.visualizar'],
            ['nome' => 'Visualizar Desfiliações', 'descricao' => 'Permite visualizar']
        );
        $perfil = \App\Models\Perfil::create(['nome' => 'Perfil Teste Desfiliacao', 'ativo' => true]);
        $perfil->permissoes()->attach($permissao->id);
        $user->perfis()->attach($perfil->id);

        // Com permissão: deve receber 200
        $response = $this->actingAs($user)->get(route('admin.desfiliacoes.index'));
        $response->assertStatus(200);

        $user->delete();
        $perfil->delete();
    }
}
