<?php

namespace App\Console\Commands;

use App\Models\AgendaPeriodo;
use Illuminate\Console\Command;
use Carbon\Carbon;

class InativarPeriodosVencidos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agenda:inativar-periodos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inativa automaticamente os períodos da agenda que já venceram (data_final < hoje)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hoje = Carbon::today();

        $periodos = AgendaPeriodo::where('data_final', '<', $hoje)
            ->where('ativo', true)
            ->get();

        $total = $periodos->count();

        if ($total > 0) {
            foreach ($periodos as $periodo) {
                $periodo->update(['ativo' => false]);
                $this->info("Período ID #{$periodo->id} ('{$periodo->descricao}') inativado.");
            }
            $this->info("Processo concluído: {$total} período(s) inativado(s).");
        } else {
            $this->info('Nenhum período vencido encontrado para inativar.');
        }

        return Command::SUCCESS;
    }
}
