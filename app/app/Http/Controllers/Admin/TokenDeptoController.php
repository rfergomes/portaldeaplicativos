<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TokenDepto;
use Illuminate\Http\Request;

class TokenDeptoController extends Controller
{
    public function index()
    {
        $tokens = TokenDepto::orderBy('departamento')->get();
        return view('admin.token_deptos.index', compact('tokens'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'departamento' => ['required', 'string', 'max:255', 'unique:token_deptos,departamento'],
            'email' => ['required', 'email', 'max:255'],
            'token' => ['required', 'string'],
        ]);

        $data['departamento'] = mb_strtoupper($data['departamento']);

        TokenDepto::create($data);

        return redirect()->route('token-deptos.index')
            ->with('success', 'Token de API cadastrado com sucesso para o departamento.');
    }

    public function update(Request $request, TokenDepto $token_depto)
    {
        $data = $request->validate([
            'departamento' => ['required', 'string', 'max:255', 'unique:token_deptos,departamento,' . $token_depto->id],
            'email' => ['required', 'email', 'max:255'],
            'token' => ['nullable', 'string'],
        ]);

        $data['departamento'] = mb_strtoupper($data['departamento']);

        if (empty($data['token'])) {
            unset($data['token']); // Prevent overwriting with null if left blank
        }

        $token_depto->update($data);

        return redirect()->route('token-deptos.index')
            ->with('success', 'Configurações de Token atualizadas.');
    }

    public function destroy(TokenDepto $token_depto)
    {
        if ($token_depto->users()->count() > 0) {
            return back()->with('error', 'Não é possível excluir: há usuários logados vinculados a este token.');
        }

        $token_depto->delete();

        return redirect()->route('token-deptos.index')
            ->with('success', 'Departamento e Token excluídos.');
    }
}
