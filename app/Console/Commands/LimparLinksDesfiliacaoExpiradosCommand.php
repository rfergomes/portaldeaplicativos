<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\DesfiliacaoLink;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LimparLinksDesfiliacaoExpiradosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'desfiliacao:limpar-links-expirados 
                            {--dias=30 : Quantidade de dias após expiração ou cancelamento para purgar} 
                            {--dry-run : Apenas simula a quantidade sem excluir os registros}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expurga links de desfiliação expirados ou cancelados que não possuem relacionamento com desfiliações formalizadas';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dias = max(1, (int) $this->option('dias'));
        $isDryRun = (bool) $this->option('dry-run');
        $dataCorte = now()->subDays($dias);

        $this->info("Iniciando varredura de links órfãos de desfiliação...");
        $this->info("Critério de corte: links expirados ou cancelados anteriores a {$dataCorte->format('d/m/Y H:i')} (mais de {$dias} dias).");

        $query = DesfiliacaoLink::query()
            ->where(function ($q) use ($dataCorte) {
                $q->where('expira_em', '<', $dataCorte)
                  ->orWhere('cancelado_em', '<', $dataCorte);
            })
            ->whereDoesntHave('desfiliacoes')
            ->whereNull('desfiliacao_id');

        $total = $query->count();

        if ($total === 0) {
            $this->info("Nenhum link órfão encontrado para os critérios informados.");
            return Command::SUCCESS;
        }

        if ($isDryRun) {
            $this->warn("[SIMULAÇÃO] {$total} links órfãos seriam excluídos (0 desfiliações afetadas).");
            $this->info("Nenhum registro foi alterado no banco de dados.");
            return Command::SUCCESS;
        }

        $excluidos = $query->delete();

        Log::info("Limpeza de Links de Desfiliação: {$excluidos} links órfãos purgados com sucesso (corte: {$dias} dias).");
        $this->info("Sucesso: {$excluidos} links órfãos foram excluídos com sucesso. Espaço de índices liberado.");

        return Command::SUCCESS;
    }
}
