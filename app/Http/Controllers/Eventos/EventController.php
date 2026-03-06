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
        $eventosAbertos = Evento::with(['convites', 'convidados'])->where('encerrado', false)->orderByDesc('data_inicio')->get();
        $eventosEncerrados = Evento::with(['convites', 'convidados'])->where('encerrado', true)->orderByDesc('data_inicio')->get();

        $allEvents = $eventosAbertos->concat($eventosEncerrados);

        $totalEventos = $allEvents->count();
        $totalConvites = $allEvents->sum(fn($e) => $e->convites->count());
        $totalConvidados = $allEvents->sum(fn($e) => $e->convidados->count());

        // Unifica a lógica de arrecadação baseando-se no valor dos convidados carregados
        $totalArrecadado = $allEvents->sum(fn($e) => $e->convidados->sum('valor'));

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

    public function edit(Evento $evento): View
    {
        return view('eventos.edit', compact('evento'));
    }

    public function update(Request $request, Evento $evento): RedirectResponse
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'data_inicio' => ['nullable', 'date'],
            'local' => ['nullable', 'string', 'max:255'],
            'valor_inteira' => ['nullable', 'numeric', 'min:0'],
        ]);

        $evento->update([
            'nome' => $data['nome'],
            'data_inicio' => $data['data_inicio'] ?? null,
            'local' => $data['local'] ?? null,
            'valor_inteira' => $data['valor_inteira'] ?? 0,
            'valor_meia' => ($data['valor_inteira'] ?? 0) / 2,
        ]);

        return redirect()->route('eventos.index')
            ->with('status', 'Evento atualizado com sucesso.');
    }

    public function toggleStatus(Evento $evento): RedirectResponse
    {
        $evento->update([
            'encerrado' => !$evento->encerrado
        ]);

        $status = $evento->encerrado ? 'encerrado' : 'reaberto';

        return redirect()->route('eventos.index')
            ->with('status', "Evento {$status} com sucesso.");
    }
}
