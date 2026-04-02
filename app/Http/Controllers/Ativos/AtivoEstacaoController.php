<?php

namespace App\Http\Controllers\Ativos;

use App\Http\Controllers\Controller;
use App\Models\AtivoEstacao;
use App\Models\AtivoDepartamento;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AtivoEstacaoController extends Controller
{
    public function index(Request $request)
    {
        $busca = $request->input('busca');
        $status = $request->input('status');

        $departamentosQuery = AtivoDepartamento::with(['estacoes' => function($q) use ($busca, $status) {
            if ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                  ->orWhere('descricao', 'like', "%{$busca}%");
            }
            if ($status === 'vazia') {
                $q->doesntHave('equipamentos');
            } elseif ($status === 'ativa') {
                $q->has('equipamentos');
            }
            $q->orderBy('nome', 'asc');
        }, 'estacoes.equipamentos'])->where('ativo', true);

        // Se houver filtro, mostrar apenas departamentos onde tenham estações
        if ($busca || $status) {
            $departamentosQuery->whereHas('estacoes', function($q) use ($busca, $status) {
                if ($busca) {
                    $q->where(function($sq) use ($busca) {
                        $sq->where('nome', 'like', "%{$busca}%")
                           ->orWhere('descricao', 'like', "%{$busca}%");
                    });
                }
                if ($status === 'vazia') {
                    $q->doesntHave('equipamentos');
                } elseif ($status === 'ativa') {
                    $q->has('equipamentos');
                }
            });
        }

        $departamentos = $departamentosQuery->orderBy('nome')->get();
            
        // Estatísticas Globais
        $totalEstacoes = AtivoEstacao::count();
        $estacoesLivres = AtivoEstacao::doesntHave('equipamentos')->count();
        $totalDepartamentos = AtivoDepartamento::count();
        $equipamentosAlocados = \App\Models\AtivoEquipamento::whereNotNull('estacao_id')->count();

        // Se for request vazio de todos os departamentos (sem filtro). Mantemos original view, 
        // mas as estações já virão ordenadas no ->with() em "asc" nome.

        return view('ativos.estacoes.index', compact('departamentos', 'totalEstacoes', 'estacoesLivres', 'totalDepartamentos', 'equipamentosAlocados', 'busca', 'status'));
    }

    public function gerarPdf(Request $request)
    {
        $busca = $request->input('busca');
        $status = $request->input('status');

        $departamentosQuery = AtivoDepartamento::with(['estacoes' => function($q) use ($busca, $status) {
            if ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                  ->orWhere('descricao', 'like', "%{$busca}%");
            }
            if ($status === 'vazia') {
                $q->doesntHave('equipamentos');
            } elseif ($status === 'ativa') {
                $q->has('equipamentos');
            }
            $q->orderBy('nome', 'asc');
        }, 'estacoes.equipamentos'])->where('ativo', true);

        if ($busca || $status) {
            $departamentosQuery->whereHas('estacoes', function($q) use ($busca, $status) {
                if ($busca) {
                    $q->where(function($sq) use ($busca) {
                        $sq->where('nome', 'like', "%{$busca}%")
                           ->orWhere('descricao', 'like', "%{$busca}%");
                    });
                }
                if ($status === 'vazia') {
                    $q->doesntHave('equipamentos');
                } elseif ($status === 'ativa') {
                    $q->has('equipamentos');
                }
            });
        }

        $departamentos = $departamentosQuery->orderBy('nome')->get();

        $pdf = Pdf::loadView('ativos.estacoes.pdf', compact('departamentos', 'busca', 'status'))
                  ->setPaper('a4', 'portrait');

        return $pdf->stream('relatorio_estacoes_de_trabalho_' . now()->format('Ymd_H_i') . '.pdf');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'departamento_id' => 'required|exists:ativo_departamentos,id',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
        ]);

        AtivoEstacao::create($validated);

        return redirect()->back()->with('success', 'Estação de Trabalho criada com sucesso!');
    }

    public function update(Request $request, AtivoEstacao $estacao)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
        ]);

        $estacao->update($validated);

        return redirect()->back()->with('success', 'Estação de Trabalho atualizada com sucesso!');
    }

    public function destroy(AtivoEstacao $estacao)
    {
        if ($estacao->equipamentos()->exists()) {
            return redirect()->back()->with('error', 'Não é possível excluir uma estação que possui equipamentos vinculados.');
        }

        $estacao->delete();
        return redirect()->back()->with('success', 'Estação de Trabalho excluída com sucesso!');
    }

    public function apiGetEstacoes(Request $request)
    {
        $query = AtivoEstacao::query();
        if ($request->filled('departamento_id')) {
            $query->where('departamento_id', $request->departamento_id);
        }
        return response()->json($query->orderBy('nome')->get(['id', 'nome']));
    }
}
