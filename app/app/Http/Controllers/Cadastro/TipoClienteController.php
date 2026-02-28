<?php

namespace App\Http\Controllers\Cadastro;

use App\Http\Controllers\Controller;
use App\Models\TipoCliente;
use Illuminate\Http\Request;

class TipoClienteController extends Controller
{
    public function index()
    {
        $tipos = TipoCliente::orderBy('nome')->get();
        return view('cadastros.tipos_clientes.index', compact('tipos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255', 'unique:tipos_clientes,nome'],
            'descricao' => ['nullable', 'string'],
        ]);

        $data['nome'] = mb_strtoupper($data['nome']);
        $data['ativo'] = true;

        TipoCliente::create($data);

        return redirect()->route('tipos_clientes.index')
            ->with('success', 'Tipo de contato/cargo adicionado com sucesso.');
    }

    public function update(Request $request, TipoCliente $tipos_cliente)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255', 'unique:tipos_clientes,nome,' . $tipos_cliente->id],
            'descricao' => ['nullable', 'string'],
        ]);

        $data['nome'] = mb_strtoupper($data['nome']);

        $tipos_cliente->update($data);

        return redirect()->route('tipos_clientes.index')
            ->with('success', 'Tipo de contato atualizado.');
    }

    public function destroy(TipoCliente $tipos_cliente)
    {
        if ($tipos_cliente->clientes()->count() > 0) {
            return back()->with('error', 'Não é possível excluir: existem contatos usando este tipo.');
        }

        $tipos_cliente->delete();

        return redirect()->route('tipos_clientes.index')
            ->with('success', 'Tipo de contato excluído.');
    }
}
