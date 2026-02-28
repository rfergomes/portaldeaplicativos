<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegiaoSeeder extends Seeder
{
    public function run(): void
    {
        $regioes = [
            ['nome' => 'CAMPINAS', 'area_adm' => '1'],
            ['nome' => 'HORTOLÂNDIA', 'area_adm' => '2'],
            ['nome' => 'PAULÍNIA', 'area_adm' => '3'],
            ['nome' => 'VALINHOS', 'area_adm' => '4'],
            ['nome' => 'SUMARÉ', 'area_adm' => '5'],
            ['nome' => 'APOSENTADOS', 'area_adm' => '6'],
            ['nome' => 'NÃO DEFINIDO', 'area_adm' => '0'],
        ];

        foreach ($regioes as $regiao) {
            DB::table('regioes')->updateOrInsert(
                ['nome' => $regiao['nome']],
                ['area_adm' => $regiao['area_adm'], 'ativo' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
