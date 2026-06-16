<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permissao;
use App\Models\Perfil;

class DashboardPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissoes = [
            [
                'chave' => 'dashboard.ceo.view',
                'nome' => 'Visualizar Dashboard CEO',
                'descricao' => 'Acesso total ao Dashboard Macro e Financeiro'
            ],
            [
                'chave' => 'dashboard.manager.view',
                'nome' => 'Visualizar Dashboard Gerencial',
                'descricao' => 'Acesso aos indicadores de produtividade da equipe'
            ],
        ];

        foreach ($permissoes as $permData) {
            $permissao = Permissao::firstOrCreate(
                ['chave' => $permData['chave']],
                ['nome' => $permData['nome'], 'descricao' => $permData['descricao']]
            );

            // Se for CEO view, adicionar ao Administrador
            if ($permData['chave'] === 'dashboard.ceo.view') {
                $perfilAdmin = Perfil::where('nome', 'Administrador')->first();
                if ($perfilAdmin && !$perfilAdmin->permissoes->contains($permissao->id)) {
                    $perfilAdmin->permissoes()->attach($permissao->id);
                }
            }
        }
    }
}
