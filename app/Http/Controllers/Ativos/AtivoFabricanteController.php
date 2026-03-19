<?php

namespace App\Http\Controllers\Ativos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AtivoFabricanteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fabricantes = \App\Models\AtivoFabricante::withCount('equipamentos')->orderBy('nome')->get();
        return view('ativos.fabricantes.index', compact('fabricantes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'site' => 'nullable|url|max:255',
            'ativo' => 'boolean',
        ]);

        \App\Models\AtivoFabricante::create($validated);

        return redirect()->route('ativos.fabricantes.index')->with('success', 'Fabricante criado com sucesso!');
    }

    public function update(Request $request, string $id)
    {
        $fabricante = \App\Models\AtivoFabricante::findOrFail($id);
        
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'site' => 'nullable|url|max:255',
            'ativo' => 'boolean',
        ]);

        $fabricante->update($validated);

        return redirect()->route('ativos.fabricantes.index')->with('success', 'Fabricante atualizado com sucesso!');
    }

    public function destroy(string $id)
    {
        $fabricante = \App\Models\AtivoFabricante::findOrFail($id);
        
        if ($fabricante->equipamentos()->exists()) {
            return redirect()->back()->with('error', 'Não é possível excluir um fabricante que possui equipamentos vinculados.');
        }

        $fabricante->delete();

        return redirect()->route('ativos.fabricantes.index')->with('success', 'Fabricante excluído com sucesso!');
    }
}
