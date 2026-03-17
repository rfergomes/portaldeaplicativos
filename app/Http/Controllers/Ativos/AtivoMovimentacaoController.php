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
        $query = \App\Models\AtivoMovimentacao::with(['equipamento', 'usuario.empresa', 'responsavel']);

        // Filtros
        if ($request->filled('equipamento_id')) {
            $query->where('equipamento_id', $request->equipamento_id);
        }

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('destino')) {
            $query->where('destino', 'like', '%' . $request->destino . '%');
        }

        if ($request->filled('responsavel_id')) {
            $query->where('responsavel_id', $request->responsavel_id);
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_movimentacao', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data_movimentacao', '<=', $request->data_fim);
        }

        $movimentacoes = $query->orderBy('data_movimentacao', 'desc')->paginate(20);

        // Dados para os filtros
        $equipamentos = \App\Models\AtivoEquipamento::select('id', 'descricao', 'modelo')->orderBy('descricao')->get();
        $usuarios = \App\Models\AtivoUsuario::select('id', 'nome')->orderBy('nome')->get();
        $operadores = \App\Models\User::select('id', 'name')->orderBy('name')->get();

        return view('ativos.movimentacoes.index', compact('movimentacoes', 'equipamentos', 'usuarios', 'operadores'));
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

            $cessaoId = null;
            if ($validated['tipo'] == 'cessao') {
                $ultimoId = \App\Models\AtivoCessao::max('id') ?? 0;
                $codigo = 'CSN' . str_pad($ultimoId + 1, 3, '0', STR_PAD_LEFT);
                
                $cessao = \App\Models\AtivoCessao::create([
                    'usuario_id' => $validated['usuario_id'],
                    'data_cessao' => now(),
                    'codigo_cessao' => $codigo,
                    'observacoes' => $validated['observacao'],
                ]);
                $cessaoId = $cessao->id;
            }

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
                'cessao_id' => $cessaoId,
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

            return redirect()->back()->with('success', 'Movimentação registrada com sucesso!')
                             ->with('cessao_id', $cessaoId);
        });
    }
}
