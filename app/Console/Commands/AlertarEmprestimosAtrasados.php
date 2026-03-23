<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AtivoMovimentacao;
use App\Jobs\SendKwikNotificationJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AlertarEmprestimosAtrasados extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kwik:alertar-atrasados';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispara notificações via Kwik para empréstimos de equipamentos em atraso';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hoje = Carbon::today();

        $equipamentosAtrasados = \App\Models\AtivoEquipamento::where('status', 'em_uso')
            ->where('tipo_uso', 'empréstimo')
            ->whereNotNull('data_devolucao_prevista')
            ->where('data_devolucao_prevista', '<', $hoje)
            ->get();

        if ($equipamentosAtrasados->isEmpty()) {
            $this->info("Nenhum empréstimo em atraso encontrado.");
            return;
        }

        $count = 0;

        foreach ($equipamentosAtrasados as $equip) {
            $ultimaMovimentacao = $equip->ultimaMovimentacao;

            if (!$ultimaMovimentacao || $ultimaMovimentacao->tipo !== 'emprestimo') {
                continue;
            }

            $usuario = \App\Models\AtivoUsuario::find($ultimaMovimentacao->usuario_id);

            if ($usuario && !empty($usuario->telefone)) {
                $nomeUsuario = explode(' ', trim($usuario->nome))[0];
                $descricaoEquip = $equip->descricao . ' (' . $equip->identificador . ')';
                $dataDevolucao = Carbon::parse($equip->data_devolucao_prevista);
                $diasAtraso = (int) $dataDevolucao->diffInDays($hoje);
                
                dispatch(new SendKwikNotificationJob(
                    $usuario->telefone,
                    'equipamento_atraso',
                    [$nomeUsuario, $descricaoEquip, $dataDevolucao->format('d/m/Y'), (string) $diasAtraso]
                ));

                $count++;
            }
        }

        $this->info("Foram enviadas $count notificações de atraso.");
        Log::info("Comando kwik:alertar-atrasados executado. $count notificações disparadas.");
    }
}
