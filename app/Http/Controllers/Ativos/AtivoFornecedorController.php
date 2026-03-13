<?php

namespace App\Http\Controllers\Ativos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AtivoFornecedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fornecedores = \App\Models\AtivoFornecedor::orderBy('nome')->get();
        return view('ativos.fornecedores.index', compact('fornecedores'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'contato' => 'nullable|string|max:255',
            'endereco' => 'nullable|string',
            'ativo' => 'boolean',
        ]);

        \App\Models\AtivoFornecedor::create($validated);

        return redirect()->route('ativos.fornecedores.index')->with('success', 'Fornecedor criado com sucesso!');
    }

    public function update(Request $request, string $id)
    {
        $fornecedor = \App\Models\AtivoFornecedor::findOrFail($id);
        
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cnpj' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'contato' => 'nullable|string|max:255',
            'endereco' => 'nullable|string',
            'ativo' => 'boolean',
        ]);

        $fornecedor->update($validated);

        return redirect()->route('ativos.fornecedores.index')->with('success', 'Fornecedor atualizado com sucesso!');
    }

    public function destroy(string $id)
    {
        $fornecedor = \App\Models\AtivoFornecedor::findOrFail($id);
        
        if ($fornecedor->equipamentos()->exists()) {
            return redirect()->back()->with('error', 'Não é possível excluir um fornecedor que possui equipamentos vinculados.');
        }

        $fornecedor->delete();

        return redirect()->route('ativos.fornecedores.index')->with('success', 'Fornecedor excluído com sucesso!');
    }
}
