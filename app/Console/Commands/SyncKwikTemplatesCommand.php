<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Whatsapp\Services\KwikTemplateSyncService;
use App\Models\WhatsappTemplate;
use Illuminate\Console\Command;

class SyncKwikTemplatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kwik:sync-templates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza os templates de WhatsApp homologados na Meta a partir da API da Kwik';

    /**
     * Execute the console command.
     */
    public function handle(KwikTemplateSyncService $service): int
    {
        $this->info('Iniciando sincronização de templates com a API Kwik...');

        $result = $service->sincronizar();

        if (!empty($result->erros)) {
            foreach ($result->erros as $erro) {
                $this->error("Erro: {$erro}");
            }
            return Command::FAILURE;
        }

        $this->info($result->resumoMensagem());

        $templates = WhatsappTemplate::orderBy('nome')->get(['nome', 'status_meta', 'categoria', 'ativo', 'sincronizado_em']);

        if ($templates->isNotEmpty()) {
            $this->table(
                ['Nome', 'Status Meta', 'Categoria', 'Ativo para Disparo', 'Última Sincronização'],
                $templates->map(fn ($t) => [
                    $t->nome,
                    $t->status_meta ?? 'N/D',
                    $t->categoria ?? 'N/D',
                    $t->ativo ? 'Sim' : 'Não',
                    $t->sincronizado_em?->format('d/m/Y H:i:s') ?? 'N/D',
                ])
            );
        }

        return Command::SUCCESS;
    }
}
