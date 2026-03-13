<?php

namespace App\Http\Controllers\Ativos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AtivoEquipamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\AtivoEquipamento::with(['fabricante', 'fornecedor']);

        // Filtros
        if ($request->filled('identificador')) {
            $query->where('identificador', 'like', '%' . $request->identificador . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $equipamentos = $query->orderBy('identificador')->paginate(15);
        
        return view('ativos.equipamentos.index', compact('equipamentos'));
    }

    public function create()
    {
        $fabricantes = \App\Models\AtivoFabricante::where('ativo', true)->orderBy('nome')->get();
        $fornecedores = \App\Models\AtivoFornecedor::where('ativo', true)->orderBy('nome')->get();
        return view('ativos.equipamentos.create', compact('fabricantes', 'fornecedores'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'identificador' => 'required|string|unique:ativo_equipamentos,identificador|max:50',
            'descricao' => 'required|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'numero_serie' => 'nullable|string|max:255',
            'fabricante_id' => 'nullable|exists:ativo_fabricantes,id',
            'fornecedor_id' => 'nullable|exists:ativo_fornecedores,id',
            'data_compra' => 'nullable|date',
            'valor_item' => 'nullable|numeric',
            'valor_nota' => 'nullable|string|max:255',
            'garantia_meses' => 'nullable|integer',
            'observacao' => 'nullable|string',
        ]);

        \App\Models\AtivoEquipamento::create($validated);

        return redirect()->route('ativos.equipamentos.index')->with('success', 'Equipamento cadastrado com sucesso!');
    }

    public function show(string $id)
    {
        $equipamento = \App\Models\AtivoEquipamento::with(['fabricante', 'fornecedor', 'movimentacoes.usuario', 'movimentacoes.responsavel', 'anexos'])->findOrFail($id);
        return view('ativos.equipamentos.show', compact('equipamento'));
    }

    public function edit(string $id)
    {
        $equipamento = \App\Models\AtivoEquipamento::findOrFail($id);
        $fabricantes = \App\Models\AtivoFabricante::where('ativo', true)->orderBy('nome')->get();
        $fornecedores = \App\Models\AtivoFornecedor::where('ativo', true)->orderBy('nome')->get();
        return view('ativos.equipamentos.edit', compact('equipamento', 'fabricantes', 'fornecedores'));
    }

    public function update(Request $request, string $id)
    {
        $equipamento = \App\Models\AtivoEquipamento::findOrFail($id);

        $validated = $request->validate([
            'identificador' => 'required|string|unique:ativo_equipamentos,identificador,' . $id . '|max:50',
            'descricao' => 'required|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'numero_serie' => 'nullable|string|max:255',
            'fabricante_id' => 'nullable|exists:ativo_fabricantes,id',
            'fornecedor_id' => 'nullable|exists:ativo_fornecedores,id',
            'data_compra' => 'nullable|date',
            'valor_item' => 'nullable|numeric',
            'valor_nota' => 'nullable|string|max:255',
            'garantia_meses' => 'nullable|integer',
            'status' => 'required|in:disponivel,em_uso,manutencao,baixado',
            'observacao' => 'nullable|string',
        ]);

        $equipamento->update($validated);

        return redirect()->route('ativos.equipamentos.index')->with('success', 'Equipamento atualizado com sucesso!');
    }

    public function destroy(string $id)
    {
        $equipamento = \App\Models\AtivoEquipamento::findOrFail($id);
        
        if ($equipamento->movimentacoes()->exists()) {
             return redirect()->back()->with('error', 'Não é possível excluir um equipamento que possui histórico de movimentações. Considere mudar o status para "Baixado".');
        }

        $equipamento->delete();

        return redirect()->route('ativos.equipamentos.index')->with('success', 'Equipamento excluído com sucesso!');
    }
}
