<?php

namespace App\Http\Controllers\Eventos;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $eventosAbertos = Evento::where('encerrado', false)->orderByDesc('data_inicio')->get();
        $eventosEncerrados = Evento::where('encerrado', true)->orderByDesc('data_inicio')->get();

        $totalEventos = Evento::count();
        $totalConvites = Evento::withCount('convites')->get()->sum('convites_count');
        $totalConvidados = Evento::withCount('convidados')->get()->sum('convidados_count');
        $totalArrecadado = Evento::with('vendas')->get()->flatMap->vendas->sum('valor_venda');

        return view('eventos.index', [
            'eventosAbertos' => $eventosAbertos,
            'eventosEncerrados' => $eventosEncerrados,
            'totalEventos' => $totalEventos,
            'totalConvites' => $totalConvites,
            'totalConvidados' => $totalConvidados,
            'totalArrecadado' => $totalArrecadado,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'data' => ['nullable', 'date'],
            'local' => ['nullable', 'string', 'max:255'],
            'valor_inteira' => ['nullable', 'numeric', 'min:0'],
        ]);

        Evento::create([
            'nome' => $data['nome'],
            'data_inicio' => $data['data'] ?? null,
            'local' => $data['local'] ?? null,
            'valor_inteira' => $data['valor_inteira'] ?? 0,
            'valor_meia' => ($data['valor_inteira'] ?? 0) / 2,
        ]);

        return redirect()->route('eventos.index')
            ->with('status', 'Evento criado com sucesso.');
    }
}

