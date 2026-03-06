<?php

namespace App\Http\Controllers\Agenda;

use App\Http\Controllers\Controller;
use App\Models\Agenda\AgendaReservaHistorico;
use App\Models\AgendaPeriodo;
use App\Models\Colonia;
use Illuminate\Http\Request;

class AgendaHistoricoController extends Controller
{
    /**
     * Lista o histórico de exclusões com filtros.
     */
    public function index(Request $request)
    {
        $colonias = Colonia::orderBy('nome')->get();
        $periodos = AgendaPeriodo::orderBy('data_inicial', 'desc')->get();

        $query = AgendaReservaHistorico::query()->latest();

        if ($request->filled('colonia_id')) {
            $query->where('colonia_id', $request->colonia_id);
        }

        if ($request->filled('periodo_id')) {
            $query->where('periodo_id', $request->periodo_id);
        }

        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function ($q) use ($busca) {
                $q->where('hospede_nome', 'like', "%{$busca}%")
                    ->orWhere('motivo', 'like', "%{$busca}%")
                    ->orWhere('bloqueio_nota', 'like', "%{$busca}%");
            });
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        $historicos = $query->paginate(25)->withQueryString();

        return view('agenda.historico.index', compact(
            'historicos',
            'colonias',
            'periodos'
        ));
    }
}
