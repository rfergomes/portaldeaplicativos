<?php

namespace Database\Seeders;

use App\Models\TipoProtocolo;
use Illuminate\Database\Seeder;

class TipoProtocoloSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nome' => 'PROTOCOLO', 'icone' => 'fa-solid fa-file-contract', 'cor' => 'primary'],
            ['nome' => 'OFÍCIO', 'icone' => 'fa-solid fa-file-pen', 'cor' => 'info'],
            ['nome' => 'E-MAIL ESPORÁDICO', 'icone' => 'fa-solid fa-envelope', 'cor' => 'secondary'],
            ['nome' => 'NOTIFICAÇÃO EXTRAJUD.', 'icone' => 'fa-solid fa-scale-balanced', 'cor' => 'warning'],
            ['nome' => 'COMUNICADO INTERNO', 'icone' => 'fa-solid fa-bullhorn', 'cor' => 'success'],
        ];

        foreach ($tipos as $tipo) {
            TipoProtocolo::firstOrCreate(['nome' => $tipo['nome']], $tipo);
        }
    }
}
