<?php

namespace App\Http\Controllers\Cadastro;

use App\Http\Controllers\Controller;
use App\Models\Regiao;
use Illuminate\Http\Request;

class RegiaoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $regioes = Regiao::when($search, function ($query, $search) {
            return $query->where('nome', 'like', "%{$search}%")
                ->orWhere('area_adm', 'like', "%{$search}%");
        })
            ->orderBy('nome')
            ->paginate(15);

        return view('regioes.index', compact('regioes', 'search'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255|unique:regioes,nome',
            'area_adm' => 'nullable|string|max:20',
        ]);

        $data['nome'] = mb_strtoupper($data['nome'], 'UTF-8');

        Regiao::create($data);

        return redirect()->route('regioes.index')->with('success', 'Região cadastrada com sucesso!');
    }

    public function update(Request $request, Regiao $regiao)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255|unique:regioes,nome,' . $regiao->id,
            'area_adm' => 'nullable|string|max:20',
        ]);

        $data['nome'] = mb_strtoupper($data['nome'], 'UTF-8');

        $regiao->update($data);

        return redirect()->route('regioes.index')->with('success', 'Região atualizada com sucesso!');
    }

    public function destroy(Regiao $regiao)
    {
        if ($regiao->empresas()->count() > 0) {
            return redirect()->route('regioes.index')->with('error', 'Esta região não pode ser excluída pois possui empresas vinculadas.');
        }

        $regiao->delete();

        return redirect()->route('regioes.index')->with('success', 'Região excluída com sucesso!');
    }
}
