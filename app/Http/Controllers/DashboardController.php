<?php

namespace App\Http\Controllers;

use App\Models\AgendaReserva;
use App\Models\Empresa;
use App\Models\Evento;
use App\Models\Protocolo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        // Totais (KPIs)
        $totalEventosMes = Evento::whereMonth('data_inicio', $now->month)
            ->whereYear('data_inicio', $now->year)
            ->count();
        $totalEmpresas = Empresa::where('ativo', true)->count();
        $totalProtocolosMes = Protocolo::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        // Reservas Pendentes (Aguardando confirmação ou pagamento próximo do vencimento)
        $reservasPendentes = AgendaReserva::where('status', 'reservado')
            ->whereHas('periodo', function ($query) use ($now) {
                // Considera data_limite_pagamento se existir, senao usa data_limite
                $query->whereRaw('COALESCE(data_limite_pagamento, data_limite) >= ?', [$now->copy()->subDays(2)]);
            })->count();

        // Dados para Gráfico de Protocolos (Últimos 6 meses preenchidos)
        $protocolosGrafico = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $mesAno = $date->format('m/Y');

            $total = Protocolo::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();

            $protocolosGrafico->push((object) [
                'total' => $total,
                'mes_ano' => $mesAno
            ]);
        }

        // Dados para Gráfico de Reservas por Colônia
        $reservasPorColonia = AgendaReserva::select('colonias.nome', DB::raw('count(agenda_reservas.id) as total'))
            ->join('colonias', 'agenda_reservas.colonia_id', '=', 'colonias.id')
            ->groupBy('colonias.nome')
            ->get();

        // Alertas: Reservas com pagamento vencido
        $alertasVencidos = AgendaReserva::with(['hospede', 'periodo', 'colonia'])
            ->where('status', 'reservado')
            ->whereHas('periodo', function ($query) use ($now) {
                // Vencido significa que a data limite (ou pagamento) já passou pelo início do dia de hoje (meia-noite)
                $query->whereRaw('COALESCE(data_limite_pagamento, data_limite) < ?', [$now->startOfDay()])
                      ->whereMonth(DB::raw('COALESCE(data_limite_pagamento, data_limite)'), $now->month)
                      ->whereYear(DB::raw('COALESCE(data_limite_pagamento, data_limite)'), $now->year);
            })
            ->limit(5)
            ->get();

        // Tabelas Informativas
        $protocolosRecentes = Protocolo::with(['empresa', 'tipo'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $eventosFuturos = Evento::where('data_inicio', '>=', $now)
            ->orderBy('data_inicio', 'asc')
            ->limit(5)
            ->get();

        // KPIs Sócio Folha
        $totalMensalidadesMesAtual = \App\Models\SocioFolha::whereMonth('data_vencimento', $now->month)
            ->whereYear('data_vencimento', $now->year)
            ->count();

        $empresasComLancamentos = \App\Models\SocioFolha::whereMonth('data_vencimento', $now->month)
            ->whereYear('data_vencimento', $now->year)
            ->distinct('empresa_id')
            ->count('empresa_id');

        $listasRecebidas = \App\Models\SocioFolha::whereMonth('data_vencimento', $now->month)
            ->whereYear('data_vencimento', $now->year)
            ->whereNotNull('data_lista')
            ->count();

        $gargaloAbaco = \App\Models\SocioFolha::whereMonth('data_vencimento', $now->month)
            ->whereYear('data_vencimento', $now->year)
            ->whereNotNull('data_lista')
            ->whereNull('data_baixa')
            ->count();

        $inadimplencia = \App\Models\SocioFolha::whereMonth('data_vencimento', $now->month)
            ->whereYear('data_vencimento', $now->year)
            ->where('situacao', 'ABERTO')
            ->where('data_vencimento', '<', $now->startOfDay())
            ->count();

        $kpisFolha = (object) [
            'total_lancamentos' => $totalMensalidadesMesAtual,
            'empresas_cobertas' => $empresasComLancamentos,
            'perc_listas_recebidas' => $totalMensalidadesMesAtual > 0 ? round(($listasRecebidas / $totalMensalidadesMesAtual) * 100, 1) : 0,
            'gargalo_abaco' => $totalMensalidadesMesAtual > 0 ? round(($gargaloAbaco / $totalMensalidadesMesAtual) * 100, 1) : 0,
            'taxa_inadimplencia' => $totalMensalidadesMesAtual > 0 ? round(($inadimplencia / $totalMensalidadesMesAtual) * 100, 1) : 0,
        ];

        // Demandas Alertas
        $novasDemandasCount = 0;
        $demandasExpirando = collect();
        $userId = auth()->id();
        $novasDemandasCount = \App\Models\Demanda::where('status', \App\Models\Demanda::STATUS_ABERTA)
            ->where('tipo_responsavel', 'usuario')
            ->where('responsavel_usuario_id', $userId)
            ->where('lida_pelo_responsavel', false)
            ->count();

        $demandasExpirando = \App\Models\Demanda::whereIn('status', [\App\Models\Demanda::STATUS_ABERTA, \App\Models\Demanda::STATUS_AGUARDANDO])
            ->whereNotNull('prazo')
            ->where('prazo', '>=', now())
            ->where('prazo', '<=', now()->addHours(24))
            ->where(function($q) use ($userId) {
                $q->where('criador_id', $userId)
                  ->orWhere(function($sub) use ($userId) {
                      $sub->where('tipo_responsavel', 'usuario')
                          ->where('responsavel_usuario_id', $userId);
                  });
            })
            ->get();

        return view('dashboard', compact(
            'totalEventosMes',
            'totalEmpresas',
            'totalProtocolosMes',
            'reservasPendentes',
            'protocolosGrafico',
            'reservasPorColonia',
            'alertasVencidos',
            'protocolosRecentes',
            'eventosFuturos',
            'kpisFolha',
            'novasDemandasCount',
            'demandasExpirando'
        ));
    }
}
