<?php

use App\Models\Perfil;
use App\Models\Permissao;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $acoes = ['Visualizar', 'Criar', 'Editar', 'Excluir', 'Gerenciar'];
        $permissoesIds = [];

        foreach ($acoes as $acao) {
            $chave = 'convencoes.' . strtolower($acao);
            $nome = $acao . ' Convenções';

            $permissao = Permissao::firstOrCreate(
                ['chave' => $chave],
                ['nome' => $nome, 'descricao' => "Permite $acao no módulo de Convenções Coletivas e Cláusulas"]
            );

            $permissoesIds[] = $permissao->id;
        }

        // Adicionar permissões ao perfil Administrador
        $admin = Perfil::where('nome', 'Administrador')->first();
        if ($admin) {
            $admin->permissoes()->syncWithoutDetaching($permissoesIds);
        }

        // Adicionar permissão de visualização ao Operador
        $operador = Perfil::where('nome', 'Operador')->first();
        if ($operador) {
            $visualizarPerm = Permissao::where('chave', 'convencoes.visualizar')->first();
            if ($visualizarPerm) {
                $operador->permissoes()->syncWithoutDetaching([$visualizarPerm->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não remove para não afetar perfis existentes
    }
};
