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
        $permissoesData = [
            [
                'chave' => 'desfiliacoes.visualizar',
                'nome' => 'Visualizar Desfiliações',
                'descricao' => 'Permite visualizar o painel, histórico e detalhes probatórios de desfiliações sindicais',
            ],
            [
                'chave' => 'desfiliacoes.recepcionar',
                'nome' => 'Recepcionar / Dar Ciência',
                'descricao' => 'Permite recepcionar formalmente a carta de desfiliação com emissão de carimbo institucional',
            ],
            [
                'chave' => 'desfiliacoes.baixar_abaco',
                'nome' => 'Baixar no ERP Ábaco (Efetivar)',
                'descricao' => 'Permite registrar providências de baixa do sócio no ERP Ábaco e efetivar a desfiliação',
            ],
        ];

        $permissoesIds = [];

        foreach ($permissoesData as $item) {
            $permissao = Permissao::firstOrCreate(
                ['chave' => $item['chave']],
                ['nome' => $item['nome'], 'descricao' => $item['descricao']]
            );

            $permissoesIds[] = $permissao->id;
        }

        // Vincular ao perfil Administrador
        $admin = Perfil::where('nome', 'Administrador')->first();
        if ($admin) {
            $admin->permissoes()->syncWithoutDetaching($permissoesIds);
        }

        // Vincular ao perfil Operador se existir
        $operador = Perfil::where('nome', 'Operador')->first();
        if ($operador) {
            $operador->permissoes()->syncWithoutDetaching($permissoesIds);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permissao::whereIn('chave', [
            'desfiliacoes.visualizar',
            'desfiliacoes.recepcionar',
            'desfiliacoes.baixar_abaco',
        ])->delete();
    }
};
