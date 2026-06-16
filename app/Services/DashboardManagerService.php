<?php

namespace App\Services;

use App\Models\SocioCaixa;
use App\Models\SocioFolha;
use App\Models\Demanda;
use App\Models\Protocolo;
use Carbon\Carbon;

class DashboardManagerService
{
    /**
     * Calcula o total de receitas do mês atual (Caixa + Folha)
     */
    public function getTermometroReceitasMesAtual()
    {
        $mesAtual = Carbon::now()->month;
        $anoAtual = Carbon::now()->year;

        $receitaCaixa = SocioCaixa::where('ano', $anoAtual)
            ->where('mes', $mesAtual)
            ->where('pago', true)
            ->sum('valor');

        $receitaFolha = SocioFolha::where('ano', $anoAtual)
            ->where('mes', $mesAtual)
            ->sum('valor_pago');

        return [
            'caixa' => $receitaCaixa,
            'folha' => $receitaFolha,
            'total' => $receitaCaixa + $receitaFolha
        ];
    }

    /**
     * Calcula a vazão de demandas na semana atual (criadas vs fechadas)
     */
    public function getVazaoDemandasSemana()
    {
        $inicioSemana = Carbon::now()->startOfWeek();
        $fimSemana = Carbon::now()->endOfWeek();

        $criadas = Demanda::whereBetween('created_at', [$inicioSemana, $fimSemana])->count();
        
        $resolvidas = Demanda::where('status', Demanda::STATUS_EXECUTADA)
            ->whereBetween('updated_at', [$inicioSemana, $fimSemana])
            ->count();

        return [
            'criadas' => $criadas,
            'resolvidas' => $resolvidas,
            'saldo' => $resolvidas - $criadas
        ];
    }

    /**
     * Retorna os dados para o gráfico de distribuição de carga por módulo (hoje)
     */
    public function getDistribuicaoCargaHoje()
    {
        $hoje = Carbon::today();

        $countDemandas = Demanda::whereDate('created_at', $hoje)->count();
        $countProtocolos = Protocolo::whereDate('created_at', $hoje)->count();
        $countCaixa = SocioCaixa::whereDate('data_pagamento', $hoje)->count();
        // Pode ser expandido para Ativos, Eventos, etc.

        return [
            'labels' => ['Demandas', 'Protocolos', 'Financeiro (Caixa)'],
            'data' => [$countDemandas, $countProtocolos, $countCaixa]
        ];
    }

    /**
     * Gráfico de Tendência Consolidado (Últimos 12 meses)
     * Simples aproximação do volume de trabalho gerado
     */
    public function getTendenciaVolume12Meses()
    {
        $dados = [];
        $labels = [];

        for ($i = 11; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);
            $labels[] = $mes->format('M/Y');

            $volDemandas = Demanda::whereYear('created_at', $mes->year)
                ->whereMonth('created_at', $mes->month)->count();
            
            $volProtocolos = Protocolo::whereYear('created_at', $mes->year)
                ->whereMonth('created_at', $mes->month)->count();

            $dados[] = $volDemandas + $volProtocolos; // Soma agregada do volume de serviço
        }

        return [
            'labels' => $labels,
            'data' => $dados
        ];
    }
}
