<?php

namespace App\Http\Controllers\Ativos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AtivoUsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuarios = \App\Models\AtivoUsuario::with(['empresa', 'departamento'])->orderBy('nome')->get();
        $empresas = \App\Models\Empresa::where('ativo', true)
            ->where('categoria', 'PARCEIRO')
            ->orderBy('razao_social')
            ->get();
        $departamentos = \App\Models\AtivoDepartamento::where('ativo', true)->orderBy('nome')->get();
        
        return view('ativos.usuarios.index', compact('usuarios', 'empresas', 'departamentos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|max:18',
            'endereco' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'empresa_id' => 'nullable|exists:empresas,id',
            'departamento_id' => 'nullable|exists:ativo_departamentos,id',
            'ativo' => 'boolean',
        ]);

        \App\Models\AtivoUsuario::create($validated);

        return redirect()->route('ativos.usuarios.index')->with('success', 'Usuário de ativos criado com sucesso!');
    }

    public function update(Request $request, string $id)
    {
        $usuario = \App\Models\AtivoUsuario::findOrFail($id);
        
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|max:18',
            'endereco' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'empresa_id' => 'nullable|exists:empresas,id',
            'departamento_id' => 'nullable|exists:ativo_departamentos,id',
            'ativo' => 'boolean',
        ]);

        $usuario->update($validated);

        return redirect()->route('ativos.usuarios.index')->with('success', 'Usuário de ativos atualizado com sucesso!');
    }

    public function destroy(string $id)
    {
        $usuario = \App\Models\AtivoUsuario::findOrFail($id);
        
        if ($usuario->movimentacoes()->exists()) {
            return redirect()->back()->with('error', 'Não é possível excluir um usuário que possui histórico de movimentações.');
        }

        $usuario->delete();

        return redirect()->route('ativos.usuarios.index')->with('success', 'Usuário de ativos excluído com sucesso!');
    }
}
