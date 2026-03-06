<?php

namespace App\Http\Controllers\Agenda;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ColoniaController extends Controller
{
    public function index()
    {
        $colonias = \App\Models\Colonia::withCount('acomodacoes')->get();
        return view('agenda.colonias.index', compact('colonias'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:500',
            'capacidade_total' => 'required|integer|min:0',
            'ativo' => 'boolean',
        ]);

        \App\Models\Colonia::create($validated);

        return redirect()->route('agenda.colonias.index')->with('success', 'Colônia criada com sucesso.');
    }

    public function update(Request $request, string $id)
    {
        $colonia = \App\Models\Colonia::findOrFail($id);

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:500',
            'capacidade_total' => 'required|integer|min:0',
            'ativo' => 'boolean',
        ]);

        $colonia->update($validated + ['ativo' => $request->has('ativo')]);

        return redirect()->route('agenda.colonias.index')->with('success', 'Colônia atualizada com sucesso.');
    }

    public function destroy(string $id)
    {
        $colonia = \App\Models\Colonia::findOrFail($id);
        $colonia->delete();

        return redirect()->route('agenda.colonias.index')->with('success', 'Colônia removida com sucesso.');
    }
}
