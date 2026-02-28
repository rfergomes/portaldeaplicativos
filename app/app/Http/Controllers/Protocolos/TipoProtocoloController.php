<?php

namespace App\Http\Controllers\Protocolos;

use App\Http\Controllers\Controller;
use App\Models\TipoProtocolo;
use Illuminate\Http\Request;

class TipoProtocoloController extends Controller
{
    public function index()
    {
        $tipos = TipoProtocolo::orderBy('nome')->get();

        return view('protocolos.tipos.index', compact('tipos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:100', 'unique:tipo_protocolos,nome'],
            'icone' => ['nullable', 'string', 'max:100'],
            'cor' => ['nullable', 'string', 'max:50'],
        ]);

        TipoProtocolo::create([
            'nome' => strtoupper($data['nome']),
            'icone' => $data['icone'] ?? 'fa-solid fa-file',
            'cor' => $data['cor'] ?? 'primary',
        ]);

        return redirect()->route('protocolos.tipos.index')
            ->with('success', 'Tipo de protocolo criado com sucesso.');
    }

    public function update(Request $request, TipoProtocolo $tipo)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:100', 'unique:tipo_protocolos,nome,' . $tipo->id],
            'icone' => ['nullable', 'string', 'max:100'],
            'cor' => ['nullable', 'string', 'max:50'],
        ]);

        $tipo->update([
            'nome' => strtoupper($data['nome']),
            'icone' => $data['icone'] ?? $tipo->icone,
            'cor' => $data['cor'] ?? $tipo->cor,
        ]);

        return redirect()->route('protocolos.tipos.index')
            ->with('success', 'Tipo de protocolo atualizado.');
    }

    public function destroy(TipoProtocolo $tipo)
    {
        if ($tipo->protocolos()->count()) {
            return back()->with('error', 'Não é possível excluir: há protocolos vinculados a este tipo.');
        }

        $tipo->delete();

        return redirect()->route('protocolos.tipos.index')
            ->with('success', 'Tipo de protocolo excluído.');
    }
}
