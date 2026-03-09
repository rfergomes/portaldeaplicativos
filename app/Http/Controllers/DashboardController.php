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
                $query->where('data_limite_pagamento', '>=', $now->copy()->subDays(2));
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
                $query->where('data_limite_pagamento', '<', $now);
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

        return view('dashboard', compact(
            'totalEventosMes',
            'totalEmpresas',
            'totalProtocolosMes',
            'reservasPendentes',
            'protocolosGrafico',
            'reservasPorColonia',
            'alertasVencidos',
            'protocolosRecentes',
            'eventosFuturos'
        ));
    }
}
