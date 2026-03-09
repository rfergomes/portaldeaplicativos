<?php

namespace App\Http\Controllers\Agenda;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AgendaHospedeController extends Controller
{
    public function index(Request $request)
    {
        $hospedes = \App\Models\AgendaHospede::with('empresa')->orderBy('nome')->paginate(20);
        $empresas = \App\Models\Empresa::where('ativo', true)->orderBy('razao_social')->get(['id', 'razao_social', 'nome_fantasia']);

        return view('agenda.hospedes.index', compact('hospedes', 'empresas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'empresa_id' => 'nullable|exists:empresas,id',
            'associado' => 'boolean',
        ]);

        \App\Models\AgendaHospede::create($validated);

        return redirect()->route('agenda.hospedes.index')->with('success', 'Hóspede cadastrado com sucesso.');
    }

    public function update(Request $request, string $id)
    {
        $hospede = \App\Models\AgendaHospede::findOrFail($id);

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'empresa_id' => 'nullable|exists:empresas,id',
            'associado' => 'boolean',
        ]);

        $hospede->update($validated + ['associado' => $request->has('associado')]);

        return redirect()->route('agenda.hospedes.index')->with('success', 'Hóspede atualizado com sucesso.');
    }

    public function destroy(string $id)
    {
        $hospede = \App\Models\AgendaHospede::findOrFail($id);
        $hospede->delete();

        return redirect()->route('agenda.hospedes.index')->with('success', 'Hóspede removido com sucesso.');
    }
}
