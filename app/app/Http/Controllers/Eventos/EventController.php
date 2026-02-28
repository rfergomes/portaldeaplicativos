<?php

namespace App\Http\Controllers\Eventos;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

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

    public function show(Evento $evento): View
    {
        $evento->load(['convites.convidados']);

        $empresas = \App\Models\Empresa::orderBy('nome_curto')->orderBy('razao_social')->get();

        // Resumo de estatísticas para o cabeçalho
        $totalConvites = $evento->convites->count();
        $totalConvidados = $evento->convidados()->count();
        $totalArrecadado = $evento->convites->sum(function ($convite) {
            return $convite->convidados->sum('valor');
        });

        return view('eventos.show', [
            'evento' => $evento,
            'empresas' => $empresas,
            'totalConvites' => $totalConvites,
            'totalConvidados' => $totalConvidados,
            'totalArrecadado' => $totalArrecadado,
        ]);
    }

    public function report(Request $request, Evento $evento)
    {
        $semValor = $request->query('sem_valor') == '1';
        $evento->load(['convites.convidados']);

        $totalGeral = $evento->convites->sum(function ($convite) {
            return $convite->convidados->sum('valor');
        });

        $pdf = Pdf::loadView('eventos.relatorio-pdf', [
            'evento' => $evento,
            'totalGeral' => $totalGeral,
            'semValor' => $semValor
        ]);

        return $pdf->stream('relatorio-' . $evento->id . '.pdf');
    }
}
