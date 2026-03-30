<?php

namespace App\Http\Controllers\Ativos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AtivoEquipamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\AtivoEquipamento::with(['fabricante', 'fornecedor', 'ultimaMovimentacao.usuario']);

        // Filtros
        if ($request->filled('identificador')) {
            $search = $request->identificador;
            $query->where(function($q) use ($search) {
                $q->where('id', $search)
                  ->orWhere('descricao', 'like', "%{$search}%")
                  ->orWhere('modelo', 'like', "%{$search}%")
                  ->orWhere('numero_serie', 'like', "%{$search}%")
                  ->orWhere('valor_nota', 'like', "%{$search}%")
                  ->orWhereHas('movimentacoes', function($mq) use ($search) {
                      $mq->where('tipo', 'cessao')
                         ->whereHas('usuario', function($uq) use ($search) {
                             $uq->where('nome', 'like', "%{$search}%");
                         });
                  });
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $equipamentos = $query->orderBy('id', 'asc')->paginate(15)->appends($request->all());
        
        return view('ativos.equipamentos.index', compact('equipamentos'));
    }

    public function create()
    {
        $fabricantes = \App\Models\AtivoFabricante::where('ativo', true)->orderBy('nome')->get();
        $fornecedores = \App\Models\AtivoFornecedor::where('ativo', true)->orderBy('nome')->get();
        $departamentos = \App\Models\AtivoDepartamento::with('estacoes')->where('ativo', true)->orderBy('nome')->get();
        return view('ativos.equipamentos.create', compact('fabricantes', 'fornecedores', 'departamentos'));
    }

    public function store(Request $request)
    {
        // Sanitize numeric inputs
        if ($request->has('valor_item') && $request->valor_item !== null) {
            $request->merge(['valor_item' => str_replace(',', '.', $request->valor_item)]);
        }
        if ($request->has('valor_residual') && $request->valor_residual !== null) {
            $request->merge(['valor_residual' => str_replace(',', '.', $request->valor_residual)]);
        }

        $validated = $request->validate([
            'identificador' => 'nullable|string|max:50',
            'descricao' => 'required|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'numero_serie' => 'nullable|string|max:255',
            'fabricante_id' => 'nullable|exists:ativo_fabricantes,id',
            'fornecedor_id' => 'nullable|exists:ativo_fornecedores,id',
            'estacao_id' => 'nullable|exists:ativo_estacoes,id',
            'data_compra' => 'nullable|date',
            'valor_item' => 'nullable|numeric',
            'valor_nota' => 'nullable|string|max:255',
            'garantia_meses' => 'nullable|integer',
            'acessorios' => 'nullable|string',
            'observacao' => 'nullable|string',
            'is_depreciavel' => 'boolean',
            'valor_residual' => 'nullable|numeric|min:0',
            'vida_util_meses' => 'nullable|integer|min:0',
            'metodo_depreciacao' => 'nullable|string|max:50',
            'categoria_depreciacao' => 'nullable|string|max:50',
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
        $departamentos = \App\Models\AtivoDepartamento::with('estacoes')->where('ativo', true)->orderBy('nome')->get();
        return view('ativos.equipamentos.edit', compact('equipamento', 'fabricantes', 'fornecedores', 'departamentos'));
    }

    public function update(Request $request, string $id)
    {
        $equipamento = \App\Models\AtivoEquipamento::findOrFail($id);

        // Sanitize numeric inputs
        if ($request->has('valor_item') && $request->valor_item !== null) {
            $request->merge(['valor_item' => str_replace(',', '.', $request->valor_item)]);
        }
        if ($request->has('valor_residual') && $request->valor_residual !== null) {
            $request->merge(['valor_residual' => str_replace(',', '.', $request->valor_residual)]);
        }

        $validated = $request->validate([
            'identificador' => 'nullable|string|max:50',
            'descricao' => 'required|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'numero_serie' => 'nullable|string|max:255',
            'fabricante_id' => 'nullable|exists:ativo_fabricantes,id',
            'fornecedor_id' => 'nullable|exists:ativo_fornecedores,id',
            'estacao_id' => 'nullable|exists:ativo_estacoes,id',
            'data_compra' => 'nullable|date',
            'valor_item' => 'nullable|numeric',
            'valor_nota' => 'nullable|string|max:255',
            'garantia_meses' => 'nullable|integer',
            'status' => 'required|in:disponivel,em_uso,manutencao,baixado',
            'acessorios' => 'nullable|string',
            'observacao' => 'nullable|string',
            'is_depreciavel' => 'boolean',
            'valor_residual' => 'nullable|numeric|min:0',
            'vida_util_meses' => 'nullable|integer|min:0',
            'metodo_depreciacao' => 'nullable|string|max:50',
            'categoria_depreciacao' => 'nullable|string|max:50',
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

    public function gerarInventarioPdf()
    {
        $equipamentos = \App\Models\AtivoEquipamento::with('aquisicao')
            ->where('status', 'disponivel')
            ->get()
            ->groupBy(function($item) {
                return $item->aquisicao->numero_nf ?? $item->valor_nota ?? 'SEM NOTA FISCAL';
            });

        $pdf = Pdf::loadView('ativos.equipamentos.pdf_inventario', compact('equipamentos'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('inventario_equipamentos_' . now()->format('d_m_Y') . '.pdf');
    }

    public function pdfBaixa(string $id)
    {
        $equipamento = \App\Models\AtivoEquipamento::with(['ultimaMovimentacao', 'aquisicao', 'fabricante'])->findOrFail($id);

        if ($equipamento->status !== 'baixado' || !$equipamento->ultimaMovimentacao || $equipamento->ultimaMovimentacao->tipo !== 'baixa') {
            return redirect()->back()->with('error', 'O equipamento selecionado não possui um registro de baixa válido.');
        }

        $movimentacao = $equipamento->ultimaMovimentacao;

        $pdf = Pdf::loadView('ativos.equipamentos.pdf_baixa', compact('equipamento', 'movimentacao'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("termo_baixa_equipamento_{$equipamento->id}.pdf");
    }
}
