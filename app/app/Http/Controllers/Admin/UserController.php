<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Perfil;
use App\Models\TokenDepto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('perfis')->orderBy('name')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $perfis = Perfil::orderBy('nome')->get();
        $tokenDeptos = TokenDepto::orderBy('departamento')->get();
        return view('admin.users.create', compact('perfis', 'tokenDeptos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'token_depto_id' => ['nullable', 'exists:token_deptos,id'],
            'perfis' => ['nullable', 'array'],
            'perfis.*' => ['exists:perfis,id']
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => strtolower(explode('@', $data['email'])[0]), // Default username
            'password' => Hash::make($data['password']),
            'token_depto_id' => $data['token_depto_id'] ?? null,
        ]);

        if (!empty($data['perfis'])) {
            $user->perfis()->sync($data['perfis']);
        }

        return redirect()->route('users.index')
            ->with('success', 'Usuário criado com sucesso.');
    }

    public function edit(User $user)
    {
        $perfis = Perfil::orderBy('nome')->get();
        $tokenDeptos = TokenDepto::orderBy('departamento')->get();
        return view('admin.users.edit', compact('user', 'perfis', 'tokenDeptos'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'token_depto_id' => ['nullable', 'exists:token_deptos,id'],
            'perfis' => ['nullable', 'array'],
            'perfis.*' => ['exists:perfis,id']
        ]);

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'token_depto_id' => $data['token_depto_id'] ?? null,
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        if (isset($data['perfis'])) {
            $user->perfis()->sync($data['perfis']);
        } else {
            $user->perfis()->detach();
        }

        return redirect()->route('users.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Você não pode excluir a si mesmo.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuário excluído com sucesso.');
    }
}
