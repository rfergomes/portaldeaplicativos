<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConvencaoClausula;
use App\Models\ConvencaoColetiva;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ConvencaoClausulaController extends Controller
{
    public function store(Request $request, ConvencaoColetiva $convencao): RedirectResponse
    {
        $validated = $request->validate([
            'numero' => 'required|string|max:50',
            'titulo' => 'required|string|max:255',
            'categoria_clausula' => 'required|string|max:50',
            'texto' => 'required|string',
            'vigencia_inicio' => 'nullable|date',
            'vigencia_fim' => 'nullable|date|after_or_equal:vigencia_inicio',
            'dispara_lembrete_lista_nominal' => 'nullable|boolean',
            'ordem' => 'nullable|integer',
            'ativo' => 'nullable|boolean',
        ], [
            'numero.required' => 'O número da cláusula é obrigatório.',
            'titulo.required' => 'O título da cláusula é obrigatório.',
            'categoria_clausula.required' => 'A categoria da cláusula é obrigatória.',
            'texto.required' => 'O texto/teor da cláusula é obrigatório.',
        ]);

        $disparaLembrete = $request->has('dispara_lembrete_lista_nominal');
        $validated['dispara_lembrete_lista_nominal'] = $disparaLembrete;
        $validated['ativo'] = $request->has('ativo');
        $validated['ordem'] = (int) ($validated['ordem'] ?? 0);

        // Se marcou para disparar lembrete, desmarca as outras cláusulas desta convenção
        if ($disparaLembrete) {
            $convencao->clausulas()->update(['dispara_lembrete_lista_nominal' => false]);
        }

        $convencao->clausulas()->create($validated);

        return redirect()->route('admin.convencoes.show', $convencao)
            ->with('success', 'Cláusula adicionada com sucesso!');
    }

    public function update(Request $request, ConvencaoColetiva $convencao, ConvencaoClausula $clausula): RedirectResponse
    {
        $this->validarPertencimento($convencao, $clausula);

        $validated = $request->validate([
            'numero' => 'required|string|max:50',
            'titulo' => 'required|string|max:255',
            'categoria_clausula' => 'required|string|max:50',
            'texto' => 'required|string',
            'vigencia_inicio' => 'nullable|date',
            'vigencia_fim' => 'nullable|date|after_or_equal:vigencia_inicio',
            'dispara_lembrete_lista_nominal' => 'nullable|boolean',
            'ordem' => 'nullable|integer',
            'ativo' => 'nullable|boolean',
        ], [
            'numero.required' => 'O número da cláusula é obrigatório.',
            'titulo.required' => 'O título da cláusula é obrigatório.',
            'categoria_clausula.required' => 'A categoria da cláusula é obrigatória.',
            'texto.required' => 'O texto/teor da cláusula é obrigatório.',
        ]);

        $disparaLembrete = $request->has('dispara_lembrete_lista_nominal');
        $validated['dispara_lembrete_lista_nominal'] = $disparaLembrete;
        $validated['ativo'] = $request->has('ativo');
        $validated['ordem'] = (int) ($validated['ordem'] ?? 0);

        if ($disparaLembrete) {
            $convencao->clausulas()->where('id', '!=', $clausula->id)->update(['dispara_lembrete_lista_nominal' => false]);
        }

        $clausula->update($validated);

        return redirect()->route('admin.convencoes.show', $convencao)
            ->with('success', 'Cláusula atualizada com sucesso!');
    }

    public function destroy(ConvencaoColetiva $convencao, ConvencaoClausula $clausula): RedirectResponse
    {
        $this->validarPertencimento($convencao, $clausula);

        $clausula->delete();

        return redirect()->route('admin.convencoes.show', $convencao)
            ->with('success', 'Cláusula removida com sucesso!');
    }

    public function toggleLembrete(ConvencaoColetiva $convencao, ConvencaoClausula $clausula): JsonResponse
    {
        $this->validarPertencimento($convencao, $clausula);

        $novoEstado = !$clausula->dispara_lembrete_lista_nominal;

        if ($novoEstado) {
            // Desmarca todas as outras cláusulas da convenção
            $convencao->clausulas()->update(['dispara_lembrete_lista_nominal' => false]);
        }

        $clausula->update(['dispara_lembrete_lista_nominal' => $novoEstado]);

        return response()->json([
            'success' => true,
            'dispara_lembrete' => $novoEstado,
            'message' => $novoEstado 
                ? "Cláusula {$clausula->numero} definida como regra ativa de cobrança de lista nominal."
                : "Gatilho de cobrança desativado para a Cláusula {$clausula->numero}."
        ]);
    }

    private function validarPertencimento(ConvencaoColetiva $convencao, ConvencaoClausula $clausula): void
    {
        abort_if($clausula->convencao_coletiva_id !== $convencao->id, 404, 'Cláusula não pertence à convenção informada.');
    }
}
