<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AtivoEquipamento;
use App\Models\AtivoAquisicao;
use Illuminate\Support\Facades\DB;

class MigrarLegadoEquipamentos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ativos:migrar-legado';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra equipamentos antigos (sem Aquisição vinculada), agrupando-os por data, fornecedor e valor nota, gerando automaticamente a Entrada (Nota Fiscal).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando migração de equipamentos legados...');

        // Busca equipamentos órfãos (criados antes do módulo de aquisições)
        $equipamentosOrfaos = AtivoEquipamento::whereNull('aquisicao_id')->get();

        if ($equipamentosOrfaos->isEmpty()) {
            $this->info('Nenhum equipamento legado foi encontrado. Tudo já está migrado!');
            return;
        }

        $this->info('Encontrados ' . $equipamentosOrfaos->count() . ' equipamentos sem Aquisição vinculada.');

        // Agrupa os equipamentos por: data_compra, fornecedor_id e valor_nota
        // Equipamentos com a mesma data de compra e mesmo fornecedor (e mesmo valor listado na nota) provavelmente vieram da mesma NF.
        $grupos = $equipamentosOrfaos->groupBy(function ($item) {
            $data = $item->data_compra ? $item->data_compra->format('Y-m-d') : 'SEM_DATA';
            $forn = $item->fornecedor_id ?? 'SEM_FORNECEDOR';
            $valorStr = $item->valor_nota ? strval(floatval($item->valor_nota)) : 'SEM_VALOR';
            return "{$data}_{$forn}_{$valorStr}";
        });

        $this->info('Os equipamentos foram organizados em ' . $grupos->count() . ' grupos de aquisições (notas).');
        
        $bar = $this->output->createProgressBar($grupos->count());
        $bar->start();

        DB::beginTransaction();

        try {
            foreach ($grupos as $chaveGrupo => $itensNoGrupo) {
                $primeiro = $itensNoGrupo->first();

                // Cria a NFE / Aquisição Mãe
                $aquisicao = AtivoAquisicao::create([
                    // Como não tínhamos um campo número NF antes, deixamos null ou colocamos aviso
                    'numero_nf' => null, 
                    'data_aquisicao' => $primeiro->data_compra ?? now(),
                    'fornecedor_id' => $primeiro->fornecedor_id,
                    'valor_total' => $primeiro->valor_nota,
                    'observacao' => 'Aquisição (Nota Fiscal) agrupada e gerada automaticamente pelo script de migração do legado.',
                ]);

                // Atualiza todos os equipamentos deste grupo para pertencerem a esta Aquisição
                foreach ($itensNoGrupo as $equip) {
                    $equip->update([
                        'aquisicao_id' => $aquisicao->id
                    ]);
                }

                $bar->advance();
            }

            DB::commit();
            $bar->finish();
            $this->newLine();
            $this->info('Migração concluída com sucesso! Todos os equipamentos foram agrupados em Notas Fiscais/Aquisições.');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Ocorreu um erro ao migrar: ' . $e->getMessage());
        }
    }
}
