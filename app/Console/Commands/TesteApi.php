<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Protocolos\Contracts\ArOnlineClient;

class TesteApi extends Command
{
    protected $signature = 'teste:api';
    public function handle(ArOnlineClient $client)
    {
        $ids = [
            '996114ee-b1c7-4d8b-95f1-15ad4ae5191b',
            '3818572f-81f2-466f-b79c-fdf29d69275a',
            '815f0d85-9d99-4ef5-acd7-a3af38fab218'
        ];

        $token = config('services.ar_online.token');

        if (!$token) {
            $this->error("Sem token no arquivo .env");
            return;
        }

        $client->setToken($token);

        foreach ($ids as $id) {
            $this->info("Consultando ID: {$id}");

            try {
                $statusData = $client->getFullStatus($id);
                $this->line(json_encode($statusData, JSON_PRETTY_PRINT));
            } catch (\Exception $e) {
                // Log the exact response body to understand the failure
                $this->error("Erro detalhado: " . $e->getMessage());
            }
            $this->line("-------------------------------------------------------------------");
        }
    }
}
