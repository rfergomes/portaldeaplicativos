<?php

namespace App\Http\Controllers\Ativos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AtivoMarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\AtivoMarketplace::query();

        if ($request->filled('search')) {
            $query->where('nome', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('ativo', $request->status);
        }

        $marketplaces = $query->orderBy('nome')->paginate(15);

        return view('ativos.marketplaces.index', compact('marketplaces'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'site' => 'nullable|url|max:255',
            'ativo' => 'boolean',
        ]);

        \App\Models\AtivoMarketplace::create($validated);

        return redirect()->route('ativos.marketplaces.index')->with('success', 'Marketplace cadastrado com sucesso!');
    }

    public function update(Request $request, string $id)
    {
        $marketplace = \App\Models\AtivoMarketplace::findOrFail($id);
        
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'site' => 'nullable|url|max:255',
            'ativo' => 'boolean',
        ]);

        $marketplace->update($validated);

        return redirect()->route('ativos.marketplaces.index')->with('success', 'Marketplace atualizado com sucesso!');
    }

    public function destroy(string $id)
    {
        $marketplace = \App\Models\AtivoMarketplace::findOrFail($id);
        
        // Futura checagem de exclusão de notas e equipamentos vinculados
        // if ($marketplace->aquisicoes()->exists() || $marketplace->equipamentos()->exists()) {
        //     return redirect()->back()->with('error', 'Este marketplace possui histórico e não pode ser excluído.');
        // }

        $marketplace->delete();

        return redirect()->route('ativos.marketplaces.index')->with('success', 'Marketplace excluído com sucesso!');
    }
}
