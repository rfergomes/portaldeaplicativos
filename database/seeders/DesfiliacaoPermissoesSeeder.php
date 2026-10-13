<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Perfil;
use App\Models\Permissao;
use Illuminate\Database\Seeder;

class DesfiliacaoPermissoesSeeder extends Seeder
{
    public function run(): void
    {
        $acoes = ['Visualizar', 'Baixar', 'Gerenciar'];
        $permissoesIds = [];

        foreach ($acoes as $acao) {
            $chave = 'desfiliacoes.' . strtolower($acao);
            $nome = $acao . ' Desfiliações';

            $permissao = Permissao::firstOrCreate(
                ['chave' => $chave],
                ['nome' => $nome, 'descricao' => "Permite $acao no módulo de Gestão de Desfiliação Sindical"]
            );

            $permissoesIds[] = $permissao->id;
        }

        // Adicionar permissões ao perfil Administrador
        $admin = Perfil::where('nome', 'Administrador')->first();
        if ($admin) {
            $admin->permissoes()->syncWithoutDetaching($permissoesIds);
        }

        // Adicionar permissão de visualização e download ao Operador
        $operador = Perfil::where('nome', 'Operador')->first();
        if ($operador) {
            $visualizarPerm = Permissao::whereIn('chave', ['desfiliacoes.visualizar', 'desfiliacoes.baixar'])->pluck('id')->toArray();
            if (!empty($visualizarPerm)) {
                $operador->permissoes()->syncWithoutDetaching($visualizarPerm);
            }
        }
    }
}
