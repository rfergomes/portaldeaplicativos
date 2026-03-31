<?php

namespace App\Http\Controllers\Ativos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AtivoUsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\AtivoUsuario::with(['empresa', 'departamento']);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->search . '%')
                  ->orWhere('cpf', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('ativo', $request->status);
        }

        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->empresa_id);
        }

        if ($request->filled('departamento_id')) {
            $query->where('departamento_id', $request->departamento_id);
        }

        $usuarios = $query->orderBy('nome')->paginate(15);
        $empresas = \App\Models\Empresa::where('ativo', true)
            ->orderBy('razao_social')
            ->get();
        $departamentos = \App\Models\AtivoDepartamento::where('ativo', true)->orderBy('nome')->get();
        
        // Estatísticas
        $totalCessionarios = \App\Models\AtivoUsuario::count();
        $empresasAtendidas = \App\Models\AtivoUsuario::whereNotNull('empresa_id')->distinct('empresa_id')->count('empresa_id');
        $comEquipamento = \App\Models\AtivoUsuario::whereHas('movimentacoes', function($q) {
            $q->whereIn('tipo', ['cessao', 'emprestimo']);
        })->count();
        $ativosNaEmpresa = \App\Models\AtivoUsuario::where('ativo', true)->count();

        return view('ativos.usuarios.index', compact('usuarios', 'empresas', 'departamentos', 'totalCessionarios', 'empresasAtendidas', 'comEquipamento', 'ativosNaEmpresa'));
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
