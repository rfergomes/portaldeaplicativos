<?php

namespace App\Http\Controllers\Eventos;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Convite;
use App\Models\Convidado;
use App\Models\LoteConvite;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function store(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'nome_responsavel' => 'required|string|max:255',
            'documento' => 'nullable|string|max:20',
            'placa' => 'nullable|string|max:20',
            'empresa' => 'nullable|string|max:255',
            'tipo' => 'required|in:inteira,meia',
            'lote_id' => 'nullable|exists:lotes_convite,id',
        ]);

        if ($data['lote_id']) {
            $lote = LoteConvite::find($data['lote_id']);
            if ($lote->quantidade_disponivel <= 0) {
                return redirect()->back()->withErrors(['lote_id' => 'Este lote está esgotado.'])->withInput();
            }
            $lote->decrement('quantidade_disponivel');
        }

        $valor = $data['tipo'] === 'inteira' ? $evento->valor_inteira : $evento->valor_meia;

        $convite = $evento->convites()->create([
            'nome_responsavel' => mb_strtoupper($data['nome_responsavel']),
            'placa' => mb_strtoupper($data['placa']),
            'empresa' => mb_strtoupper($data['empresa']),
            'tipo' => $data['tipo'],
            'valor' => $valor,
            'lote_id' => $data['lote_id'] ?? null,
            'codigo' => Str::upper(Str::random(10)),
        ]);

        // Adiciona o responsável como o primeiro convidado automaticamente
        $convite->convidados()->create([
            'nome' => mb_strtoupper($data['nome_responsavel']),
            'documento' => $data['documento'] ?? null,
            'empresa' => mb_strtoupper($data['empresa']),
            'valor' => $convite->valor,
        ]);

        return redirect()->back()->with('status', 'Convite criado com sucesso.');
    }

    public function update(Request $request, Convite $convite)
    {
        $data = $request->validate([
            'nome_responsavel' => 'required|string|max:255',
            'placa' => 'nullable|string|max:20',
            'empresa' => 'nullable|string|max:255',
            'tipo' => 'required|in:inteira,meia',
            'lote_id' => 'nullable|exists:lotes_convite,id',
        ]);

        $loteOriginalId = $convite->lote_id;
        $loteNovoId = $data['lote_id'];

        if ($loteOriginalId != $loteNovoId) {
            // Devolve disponibilidade do lote antigo
            if ($loteOriginalId) {
                LoteConvite::where('id', $loteOriginalId)->increment('quantidade_disponivel');
            }

            // Reserva disponibilidade no lote novo
            if ($loteNovoId) {
                $loteNovo = LoteConvite::find($loteNovoId);
                if ($loteNovo->quantidade_disponivel <= 0) {
                    // Reverte o incremento se falhar (opcional, mas seguro)
                    if ($loteOriginalId) {
                        LoteConvite::where('id', $loteOriginalId)->decrement('quantidade_disponivel');
                    }
                    return redirect()->back()->withErrors(['lote_id' => 'Este lote está esgotado.'])->withInput();
                }
                $loteNovo->decrement('quantidade_disponivel');
            }
        }

        $evento = $convite->evento;
        $valor = $data['tipo'] === 'inteira' ? $evento->valor_inteira : $evento->valor_meia;

        $convite->update([
            'nome_responsavel' => mb_strtoupper($data['nome_responsavel']),
            'placa' => mb_strtoupper($data['placa']),
            'empresa' => mb_strtoupper($data['empresa']),
            'tipo' => $data['tipo'],
            'valor' => $valor,
            'lote_id' => $data['lote_id'] ?? null,
        ]);

        return redirect()->back()->with('status', 'Convite atualizado com sucesso.');
    }

    public function destroy(Convite $convite)
    {
        if ($convite->lote_id) {
            LoteConvite::where('id', $convite->lote_id)->increment('quantidade_disponivel');
        }
        $convite->delete();
        return redirect()->back()->with('status', 'Convite e convidados excluídos com sucesso.');
    }

    public function storeConvidado(Request $request, Convite $convite)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'documento' => 'nullable|string|max:20',
            'empresa' => 'nullable|string|max:255',
            'valor' => 'required|numeric|min:0',
        ]);

        $convite->convidados()->create([
            'nome' => mb_strtoupper($data['nome']),
            'documento' => $data['documento'],
            'empresa' => mb_strtoupper($data['empresa']),
            'valor' => $data['valor'],
        ]);

        return response()->json(['success' => true]);
    }

    public function getConvidados(Convite $convite)
    {
        return response()->json($convite->convidados);
    }

    public function destroyConvidado(Convidado $convidado)
    {
        $convidado->delete();
        return response()->json(['success' => true]);
    }

    public function updateConvidado(Request $request, Convidado $convidado)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'documento' => 'nullable|string|max:20',
            'empresa' => 'nullable|string|max:255',
            'valor' => 'required|numeric|min:0',
        ]);

        $convidado->update([
            'nome' => mb_strtoupper($data['nome']),
            'documento' => $data['documento'],
            'empresa' => mb_strtoupper($data['empresa']),
            'valor' => $data['valor'],
        ]);

        return response()->json(['success' => true]);
    }
}
