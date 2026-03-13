<?php

namespace App\Http\Controllers\Ativos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AtivoMovimentacaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $movimentacoes = \App\Models\AtivoMovimentacao::with(['equipamento', 'usuario', 'responsavel'])
            ->orderBy('data_movimentacao', 'desc')
            ->paginate(20);
            
        return view('ativos.movimentacoes.index', compact('movimentacoes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipamento_id' => 'required|exists:ativo_equipamentos,id',
            'usuario_id' => 'nullable|exists:ativo_usuarios,id',
            'tipo' => 'required|in:cessao,emprestimo,devolucao,manutencao,transferencia',
            'data_previsao_devolucao' => 'nullable|date',
            'origem' => 'nullable|string|max:255',
            'destino' => 'nullable|string|max:255',
            'observacao' => 'nullable|string',
        ]);

        return \DB::transaction(function () use ($validated) {
            $equipamento = \App\Models\AtivoEquipamento::lockForUpdate()->findOrFail($validated['equipamento_id']);
            
            $origem = $equipamento->localizacao_atual;
            
            // Lógica de atualização conforme o tipo
            $status = $equipamento->status;
            $tipoUso = $equipamento->tipo_uso;
            $localizacao = $equipamento->localizacao_atual;

            switch ($validated['tipo']) {
                case 'cessao':
                case 'emprestimo':
                    $status = 'em_uso';
                    $tipoUso = ($validated['tipo'] == 'cessao') ? 'cessão' : 'empréstimo';
                    $localizacao = \App\Models\AtivoUsuario::find($validated['usuario_id'])->nome ?? 'Desconhecido';
                    break;
                case 'devolucao':
                    $status = 'disponivel';
                    $tipoUso = null;
                    $localizacao = 'Estoque';
                    break;
                case 'manutencao':
                    $status = 'manutencao';
                    $tipoUso = null;
                    $localizacao = 'Manutenção';
                    break;
                case 'transferencia':
                    $localizacao = $validated['destino'] ?? $localizacao;
                    break;
            }

            // Registrar Movimentação
            \App\Models\AtivoMovimentacao::create([
                'equipamento_id' => $equipamento->id,
                'usuario_id' => $validated['usuario_id'],
                'tipo' => $validated['tipo'],
                'data_movimentacao' => now(),
                'data_previsao_devolucao' => $validated['data_previsao_devolucao'],
                'responsavel_id' => auth()->id(),
                'origem' => $origem,
                'destino' => $localizacao,
                'observacao' => $validated['observacao'],
            ]);

            // Atualizar Equipamento
            $equipamento->update([
                'status' => $status,
                'tipo_uso' => $tipoUso,
                'localizacao_atual' => $localizacao,
                'data_devolucao_prevista' => $validated['data_previsao_devolucao'],
            ]);

            return redirect()->back()->with('success', 'Movimentação registrada com sucesso!');
        });
    }
}
