<?php

namespace App\Http\Controllers\Cadastro;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'empresa_id' => 'required|exists:empresas,id',
            'tipo_cliente_id' => 'required|exists:tipos_clientes,id',
            'nome' => 'required|string|max:255',
            'documento' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|size:2',
        ]);

        // Padronização para Caixa Alta
        $data['nome'] = mb_strtoupper($data['nome']);
        if (isset($data['cidade']))
            $data['cidade'] = mb_strtoupper($data['cidade']);
        if (isset($data['estado']))
            $data['estado'] = mb_strtoupper($data['estado']);

        Cliente::create($data);

        return redirect()->route('empresas.show', $data['empresa_id'])->with('success', 'Contato cadastrado com sucesso!');
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'tipo_cliente_id' => 'required|exists:tipos_clientes,id',
            'nome' => 'required|string|max:255',
            'documento' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:20',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|size:2',
        ]);

        // Padronização para Caixa Alta
        $data['nome'] = mb_strtoupper($data['nome']);
        if (isset($data['cidade']))
            $data['cidade'] = mb_strtoupper($data['cidade']);
        if (isset($data['estado']))
            $data['estado'] = mb_strtoupper($data['estado']);

        $cliente->update($data);

        return redirect()->route('empresas.show', $cliente->empresa_id)->with('success', 'Contato atualizado com sucesso!');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->back()->with('success', 'Contato excluído com sucesso!');
    }
}
