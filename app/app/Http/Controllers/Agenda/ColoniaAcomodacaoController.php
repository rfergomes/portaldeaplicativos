<?php

namespace App\Http\Controllers\Agenda;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ColoniaAcomodacaoController extends Controller
{
    public function index(\App\Models\Colonia $colonia)
    {
        $acomodacoes = $colonia->acomodacoes()
            ->orderBy('tipo')
            ->orderByRaw('CAST(identificador AS UNSIGNED) ASC')
            ->orderBy('identificador')
            ->get();
        return view('agenda.acomodacoes.index', compact('colonia', 'acomodacoes'));
    }

    public function store(Request $request, \App\Models\Colonia $colonia)
    {
        $validated = $request->validate([
            'tipo' => 'nullable|string|max:255',
            'identificador' => 'required|string|max:255',
            'ativo' => 'boolean',
        ]);

        $colonia->acomodacoes()->create($validated);

        return redirect()->route('agenda.colonias.acomodacoes.index', $colonia)->with('success', 'Acomodação cadastrada com sucesso.');
    }

    public function update(Request $request, string $id)
    {
        $acomodacao = \App\Models\ColoniaAcomodacao::findOrFail($id);

        $validated = $request->validate([
            'tipo' => 'nullable|string|max:255',
            'identificador' => 'required|string|max:255',
            'ativo' => 'boolean',
        ]);

        $acomodacao->update($validated + ['ativo' => $request->has('ativo')]);

        return redirect()->route('agenda.colonias.acomodacoes.index', $acomodacao->colonia_id)->with('success', 'Acomodação atualizada com sucesso.');
    }

    public function destroy(string $id)
    {
        $acomodacao = \App\Models\ColoniaAcomodacao::findOrFail($id);
        $colonia_id = $acomodacao->colonia_id;
        $acomodacao->delete();

        return redirect()->route('agenda.colonias.acomodacoes.index', $colonia_id)->with('success', 'Acomodação removida com sucesso.');
    }
}
