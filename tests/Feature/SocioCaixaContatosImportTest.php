<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\SocioCaixa;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SocioCaixaContatosImportTest extends TestCase
{
    private User $usuarioAutorizado;
    private User $usuarioSemPermissao;

    protected function setUp(): void
    {
        parent::setUp();

        $permImport = Permissao::firstOrCreate(
            ['chave' => 'socio_caixa.importar'],
            ['nome' => 'Importar Sócio Caixa', 'descricao' => 'Permite importar']
        );
        $perfil = Perfil::firstOrCreate(
            ['nome' => 'Operador Caixa Teste'],
            ['descricao' => 'Operador de Sócio Caixa']
        );
        $perfil->permissoes()->syncWithoutDetaching([$permImport->id]);

        $this->usuarioAutorizado = User::factory()->create([
            'email' => 'operador_import_' . uniqid() . '@teste.com',
        ]);
        $this->usuarioAutorizado->perfis()->attach($perfil);

        $this->usuarioSemPermissao = User::factory()->create([
            'email' => 'usuario_comum_' . uniqid() . '@teste.com',
        ]);
    }

    public function test_usuario_sem_permissao_nao_pode_importar_contatos(): void
    {
        $csvContent = "CÓDIGO,NOME,E-MAIL,TELEFONE MÓVEL\n30938.0,RODERLEI,roderleicarmo@gmail.com,(19) 99724-9687\n";
        $file = UploadedFile::fake()->createWithContent('contatos.csv', $csvContent);

        $response = $this->actingAs($this->usuarioSemPermissao)
            ->post(route('socios-caixa.import-contatos'), [
                'file' => $file,
            ]);

        $response->assertStatus(403);
    }

    public function test_importacao_contatos_atualiza_telefone_e_email_por_matricula(): void
    {
        // 1. Cria os registros de mensalidades dos sócios de teste
        $socio1 = SocioCaixa::create([
            'matricula' => '30938',
            'ano' => 2026,
            'mes' => 1,
            'nome' => 'RODERLEI INACIO DO CARMO',
            'tipo_socio' => 'COT',
            'valor' => 55.00,
            'pago' => false,
            'telefone' => null,
            'email' => null,
        ]);

        $socio2 = SocioCaixa::create([
            'matricula' => '49000',
            'ano' => 2026,
            'mes' => 1,
            'nome' => 'MARCIO MARQUES RAPADO',
            'tipo_socio' => 'COT',
            'valor' => 55.00,
            'pago' => false,
            'telefone' => null,
            'email' => null,
        ]);

        $socio3 = SocioCaixa::create([
            'matricula' => '25277',
            'ano' => 2026,
            'mes' => 1,
            'nome' => 'ALLAN DA SILVA REAL',
            'tipo_socio' => 'TERCEIRO FABRICA',
            'valor' => 55.00,
            'pago' => false,
            'telefone' => '(19) 3800-0000',
            'email' => null,
        ]);

        // 2. Prepara o conteúdo CSV exatamente no formato do arquivo real do usuário
        $csvContent = implode("\n", [
            'CÓDIGO,NOME,E-MAIL,TELEFONE MÓVEL',
            '30938.0,RODERLEI INACIO DO CARMO,roderleicarmo@gmail.com,(19) 99724-9687',
            '49000.0,MARCIO MARQUES RAPADO,m.marcio123@hotmail.com,(19) 98418-7006',
            '25277.0,ALLAN DA SILVA REAL,allavzico@yahoo.com.br,',
            ',,,',
            ',,,',
            ',TOTAL ===>>> 11.024,|   COM E-MAIL ====> 10.959   |,COM WHATSAPP ====>>> 11.008',
        ]);

        $file = UploadedFile::fake()->createWithContent('relatorio_contatos.csv', $csvContent);

        // 3. Executa a requisição de importação
        $response = $this->actingAs($this->usuarioAutorizado)
            ->from(route('socios-caixa.index'))
            ->post(route('socios-caixa.import-contatos'), [
                'file' => $file,
            ]);

        $response->assertRedirect(route('socios-caixa.index'));
        $response->assertSessionHas('success');

        // 4. Valida se os sócios foram atualizados no banco de dados
        $socio1->refresh();
        $this->assertEquals('(19) 99724-9687', $socio1->telefone);
        $this->assertEquals('roderleicarmo@gmail.com', $socio1->email);

        $socio2->refresh();
        $this->assertEquals('(19) 98418-7006', $socio2->telefone);
        $this->assertEquals('m.marcio123@hotmail.com', $socio2->email);

        // Sócio 3 só tinha e-mail no CSV; telefone pré-existente não deve ser sobrescrito com vazio
        $socio3->refresh();
        $this->assertEquals('allavzico@yahoo.com.br', $socio3->email);
        $this->assertEquals('(19) 3800-0000', $socio3->telefone);
    }
}
