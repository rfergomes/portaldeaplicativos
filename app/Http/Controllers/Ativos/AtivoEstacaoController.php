<?php

namespace App\Http\Controllers\Ativos;

use App\Http\Controllers\Controller;
use App\Models\AtivoEstacao;
use App\Models\AtivoDepartamento;
use Illuminate\Http\Request;

class AtivoEstacaoController extends Controller
{
    public function index()
    {
        $departamentos = AtivoDepartamento::with(['estacoes.equipamentos'])
            ->where('ativo', true)
            ->get();
            
        // Estatísticas
        $totalEstacoes = AtivoEstacao::count();
        $estacoesLivres = AtivoEstacao::doesntHave('equipamentos')->count();
        $totalDepartamentos = AtivoDepartamento::count();
        $equipamentosAlocados = \App\Models\AtivoEquipamento::whereNotNull('estacao_id')->count();

        return view('ativos.estacoes.index', compact('departamentos', 'totalEstacoes', 'estacoesLivres', 'totalDepartamentos', 'equipamentosAlocados'));
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
}
