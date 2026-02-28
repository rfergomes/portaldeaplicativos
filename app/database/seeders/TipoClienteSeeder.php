<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoClienteSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nome' => 'DIRETOR'],
            ['nome' => 'RECURSOS HUMANOS (RH)'],
            ['nome' => 'FINANCEIRO'],
            ['nome' => 'OPERACIONAL'],
            ['nome' => 'COMPRAS'],
            ['nome' => 'OUTROS'],
        ];

        foreach ($tipos as $tipo) {
            DB::table('tipos_clientes')->updateOrInsert(
                ['nome' => $tipo['nome']],
                ['ativo' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
