<?php

namespace App\Http\Controllers\Agenda;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AgendaPeriodoController extends Controller
{
    public function index(Request $request)
    {
        $periodos = \App\Models\AgendaPeriodo::orderBy('data_inicial', 'desc')->paginate(15);

        return view('agenda.periodos.index', compact('periodos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'descricao' => 'required|string|max:255',
            'data_inicial' => 'required|date',
            'data_final' => 'required|date|after_or_equal:data_inicial',
            'data_limite' => 'nullable|date',
            'data_sorteio' => 'nullable|date',
            'data_limite_pagamento' => 'nullable|date',
            'ativo' => 'boolean',
        ]);

        \App\Models\AgendaPeriodo::create($validated);

        return redirect()->route('agenda.periodos.index')->with('success', 'Período cadastrado com sucesso.');
    }

    public function update(Request $request, string $id)
    {
        $periodo = \App\Models\AgendaPeriodo::findOrFail($id);

        $validated = $request->validate([
            'descricao' => 'required|string|max:255',
            'data_inicial' => 'required|date',
            'data_final' => 'required|date|after_or_equal:data_inicial',
            'data_limite' => 'nullable|date',
            'data_sorteio' => 'nullable|date',
            'data_limite_pagamento' => 'nullable|date',
            'ativo' => 'boolean',
        ]);

        $periodo->update($validated + ['ativo' => $request->has('ativo')]);

        return redirect()->route('agenda.periodos.index')->with('success', 'Período atualizado com sucesso.');
    }

    public function destroy(string $id)
    {
        $periodo = \App\Models\AgendaPeriodo::findOrFail($id);
        $periodo->delete();

        return redirect()->route('agenda.periodos.index')->with('success', 'Período removido com sucesso.');
    }

    public function gerarSemanas(Request $request)
    {
        $validated = $request->validate([
            'mes_ano' => 'required|date_format:Y-m',
        ]);

        $carbonDate = \Carbon\Carbon::createFromFormat('Y-m', $validated['mes_ano'])->startOfMonth();

        // Pega o nome do mês e ano para a descrição
        $mesNome = ucfirst($carbonDate->translatedFormat('F/Y'));

        $quintas = [];
        $currentDate = $carbonDate->copy();

        // Encontra todas as quintas-feiras do mês fornecido
        while ($currentDate->month == $carbonDate->month) {
            if ($currentDate->dayOfWeek == \Carbon\Carbon::THURSDAY) {
                $quintas[] = $currentDate->copy();
            }
            $currentDate->addDay();
        }

        $count = 0;
        foreach ($quintas as $index => $quinta) {
            $terca = $quinta->copy()->next(\Carbon\Carbon::TUESDAY);

            $numeroSemana = $index + 1;
            $descricao = "{$numeroSemana}ª Semana - {$mesNome}";

            // Verifica se um período com essa mesma data já existe para não duplicar agressivamente
            $existe = \App\Models\AgendaPeriodo::where('data_inicial', $quinta->format('Y-m-d'))
                ->where('data_final', $terca->format('Y-m-d'))
                ->exists();

            if (!$existe) {
                // A data limite pode ser útil padrão na terça-feira (2 dias antes do início)
                $dataLimite = $quinta->copy()->subDays(2)->format('Y-m-d');

                \App\Models\AgendaPeriodo::create([
                    'descricao' => $descricao,
                    'data_inicial' => $quinta->format('Y-m-d'),
                    'data_final' => $terca->format('Y-m-d'),
                    'data_limite' => $dataLimite,
                    'ativo' => true,
                ]);
                $count++;
            }
        }

        if ($count > 0) {
            return redirect()->route('agenda.periodos.index')->with('success', "{$count} período(s) gerado(s) com sucesso para {$mesNome}!");
        } else {
            return redirect()->route('agenda.periodos.index')->with('warning', "Nenhum novo período gerado. As semanas já existem ou houve um problema.");
        }
    }
}
