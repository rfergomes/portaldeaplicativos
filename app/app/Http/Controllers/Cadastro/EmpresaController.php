<?php

namespace App\Http\Controllers\Cadastro;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmpresaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $regiao_id = $request->get('regiao_id');

        $empresas = Empresa::with('regiao')
            ->when($search, function ($query, $search) {
                return $query->where('razao_social', 'like', "%{$search}%")
                    ->orWhere('nome_fantasia', 'like', "%{$search}%")
                    ->orWhere('cnpj', 'like', "%{$search}%")
                    ->orWhere('empresa_erp', 'like', "%{$search}%");
            })
            ->when($regiao_id, function ($query, $regiao_id) {
                return $query->where('regiao_id', $regiao_id);
            })
            ->orderBy('razao_social')
            ->paginate(15);

        $regioes = \App\Models\Regiao::where('ativo', true)->orderBy('nome')->get();

        return view('empresas.index', compact('empresas', 'search', 'regioes', 'regiao_id'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'regiao_id' => 'required|exists:regioes,id',
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'nome_curto' => 'nullable|string|max:255',
            'cnpj' => 'required|string|max:18|unique:empresas,cnpj',
            'empresa_erp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|size:2',
            'categoria' => 'nullable|string|max:255',
        ]);

        // Padronização para Caixa Alta
        $data['razao_social'] = mb_strtoupper($data['razao_social']);
        if ($data['nome_fantasia'])
            $data['nome_fantasia'] = mb_strtoupper($data['nome_fantasia']);
        if (!empty($data['nome_curto']))
            $data['nome_curto'] = mb_strtoupper($data['nome_curto']);
        if ($data['cidade'])
            $data['cidade'] = mb_strtoupper($data['cidade']);
        if ($data['estado'])
            $data['estado'] = mb_strtoupper($data['estado']);
        if ($data['categoria'])
            $data['categoria'] = mb_strtoupper($data['categoria']);

        Empresa::create($data);

        return redirect()->route('empresas.index')->with('success', 'Empresa cadastrada com sucesso!');
    }

    public function update(Request $request, Empresa $empresa)
    {
        $data = $request->validate([
            'regiao_id' => 'required|exists:regioes,id',
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'nome_curto' => 'nullable|string|max:255',
            'cnpj' => 'required|string|max:18|unique:empresas,cnpj,' . $empresa->id,
            'empresa_erp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|size:2',
            'categoria' => 'nullable|string|max:255',
        ]);

        // Padronização para Caixa Alta
        $data['razao_social'] = mb_strtoupper($data['razao_social']);
        if (isset($data['nome_fantasia']))
            $data['nome_fantasia'] = mb_strtoupper($data['nome_fantasia']);
        if (isset($data['nome_curto']))
            $data['nome_curto'] = mb_strtoupper($data['nome_curto']);
        if (isset($data['cidade']))
            $data['cidade'] = mb_strtoupper($data['cidade']);
        if (isset($data['estado']))
            $data['estado'] = mb_strtoupper($data['estado']);
        if (isset($data['categoria']))
            $data['categoria'] = mb_strtoupper($data['categoria']);

        $empresa->update($data);

        return redirect()->route('empresas.index')->with('success', 'Empresa atualizada com sucesso!');
    }

    public function show(Empresa $empresa)
    {
        $empresa->load(['clientes.tipo']);
        $tiposClientes = \App\Models\TipoCliente::where('ativo', true)->orderBy('nome')->get();
        return view('empresas.show', compact('empresa', 'tiposClientes'));
    }

    public function destroy(Empresa $empresa)
    {
        $empresa->delete();
        return redirect()->route('empresas.index')->with('success', 'Empresa excluída com sucesso!');
    }
}
