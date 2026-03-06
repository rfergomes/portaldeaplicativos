<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Perfil;
use App\Models\Permissao;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    /**
     * Listar perfis.
     */
    public function index()
    {
        $perfis = Perfil::withCount('permissoes')->orderBy('nome')->get();
        return view('admin.perfis.index', compact('perfis'));
    }

    /**
     * Mostrar formulário de criação.
     */
    public function create()
    {
        $permissoes = Permissao::orderBy('chave')->get()->groupBy(function ($perm) {
            return explode('.', $perm->chave)[0];
        });
        return view('admin.perfis.create', compact('permissoes'));
    }

    /**
     * Salvar novo perfil.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255', 'unique:perfis'],
            'descricao' => ['nullable', 'string', 'max:500'],
            'permissoes' => ['nullable', 'array'],
            'permissoes.*' => ['exists:permissoes,id'],
        ]);

        $perfil = Perfil::create([
            'nome' => $data['nome'],
            'descricao' => $data['descricao'],
            'ativo' => true
        ]);

        if (!empty($data['permissoes'])) {
            $perfil->permissoes()->sync($data['permissoes']);
        }

        return redirect()->route('perfis.index')->with('success', 'Perfil criado com sucesso!');
    }

    /**
     * Mostrar formulário de edição.
     */
    public function edit(Perfil $perfil)
    {
        $permissoes = Permissao::orderBy('chave')->get()->groupBy(function ($perm) {
            $parts = explode('.', $perm->chave);
            return count($parts) > 1 ? $parts[0] : 'geral';
        });

        $perfilPermissoes = $perfil->permissoes->pluck('id')->toArray();

        return view('admin.perfis.edit', compact('perfil', 'permissoes', 'perfilPermissoes'));
    }

    /**
     * Atualizar perfil e permissões.
     */
    public function update(Request $request, Perfil $perfil)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255', 'unique:perfis,nome,' . $perfil->id],
            'descricao' => ['nullable', 'string', 'max:500'],
            'permissoes' => ['nullable', 'array'],
            'permissoes.*' => ['exists:permissoes,id'],
        ]);

        $perfil->update([
            'nome' => $data['nome'],
            'descricao' => $data['descricao'],
        ]);

        $perfil->permissoes()->sync($data['permissoes'] ?? []);

        return redirect()->route('perfis.index')->with('success', 'Perfil atualizado com sucesso!');
    }

    /**
     * Remover perfil.
     */
    public function destroy(Perfil $perfil)
    {
        if ($perfil->nome === 'Administrador') {
            return back()->with('error', 'O perfil Administrador não pode ser excluído.');
        }

        $perfil->delete();
        return redirect()->route('perfis.index')->with('success', 'Perfil excluído com sucesso!');
    }
}
