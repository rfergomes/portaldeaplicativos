<?php

namespace App\Http\Controllers\Eventos;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\LoteConvite;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function store(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'quantidade_total' => 'required|integer|min:1',
        ]);

        $evento->lotes()->create([
            'nome' => mb_strtoupper($data['nome']),
            'quantidade_total' => $data['quantidade_total'],
            'quantidade_disponivel' => $data['quantidade_total'],
        ]);

        return redirect()->back()->with('status', 'Lote criado com sucesso.');
    }

    public function update(Request $request, LoteConvite $lote)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'quantidade_total' => 'required|integer|min:1',
        ]);

        // Ajusta quantidade disponível baseado na nova quantidade total
        $diferenca = $data['quantidade_total'] - $lote->quantidade_total;
        $novaDisponivel = $lote->quantidade_disponivel + $diferenca;

        if ($novaDisponivel < 0) {
            return redirect()->back()->withErrors(['quantidade_total' => 'A nova quantidade total é inferior à quantidade já utilizada.']);
        }

        $lote->update([
            'nome' => mb_strtoupper($data['nome']),
            'quantidade_total' => $data['quantidade_total'],
            'quantidade_disponivel' => $novaDisponivel,
        ]);

        return redirect()->back()->with('status', 'Lote atualizado com sucesso.');
    }

    public function destroy(LoteConvite $lote)
    {
        if ($lote->convites()->count() > 0) {
            return redirect()->back()->withErrors(['lote' => 'Não é possível excluir um lote que já possui convites vinculados.']);
        }

        $lote->delete();
        return redirect()->back()->with('status', 'Lote excluído com sucesso.');
    }
}
