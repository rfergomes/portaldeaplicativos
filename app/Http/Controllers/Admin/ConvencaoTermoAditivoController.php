<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConvencaoColetiva;
use App\Models\ConvencaoTermoAditivo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConvencaoTermoAditivoController extends Controller
{
    public function store(Request $request, ConvencaoColetiva $convencao): RedirectResponse
    {
        $validated = $request->validate([
            'numero_termo' => 'required|string|max:50',
            'titulo' => 'required|string|max:255',
            'tipo' => 'required|string|max:50',
            'data_assinatura' => 'nullable|date',
            'vigencia_inicio' => 'required|date',
            'vigencia_fim' => 'required|date|after_or_equal:vigencia_inicio',
            'descricao' => 'nullable|string',
            'arquivo_pdf' => 'nullable|file|mimes:pdf|max:25600',
            'ativo' => 'nullable|boolean',
        ], [
            'numero_termo.required' => 'O número de identificação do termo aditivo é obrigatório.',
            'titulo.required' => 'O título do termo aditivo é obrigatório.',
            'tipo.required' => 'O tipo do aditivo é obrigatório.',
            'vigencia_inicio.required' => 'A data inicial da vigência é obrigatória.',
            'vigencia_fim.required' => 'A data final da vigência é obrigatória.',
            'vigencia_fim.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
            'arquivo_pdf.mimes' => 'O arquivo anexo do termo aditivo deve ser um documento em formato PDF.',
            'arquivo_pdf.max' => 'O arquivo PDF não pode ultrapassar 25MB.',
        ]);

        $validated['ativo'] = $request->has('ativo');

        if ($request->hasFile('arquivo_pdf')) {
            $file = $request->file('arquivo_pdf');
            $path = $file->store('convencoes/aditivos', 'public');
            $validated['arquivo_pdf'] = $path;
            $validated['arquivo_nome_original'] = $file->getClientOriginalName();
            $validated['arquivo_tamanho'] = $file->getSize();
        }

        $convencao->aditivos()->create($validated);

        return redirect()->route('admin.convencoes.show', $convencao)
            ->with('success', 'Termo Aditivo cadastrado com sucesso!');
    }

    public function update(Request $request, ConvencaoColetiva $convencao, ConvencaoTermoAditivo $aditivo): RedirectResponse
    {
        $this->validarPertencimento($convencao, $aditivo);

        $validated = $request->validate([
            'numero_termo' => 'required|string|max:50',
            'titulo' => 'required|string|max:255',
            'tipo' => 'required|string|max:50',
            'data_assinatura' => 'nullable|date',
            'vigencia_inicio' => 'required|date',
            'vigencia_fim' => 'required|date|after_or_equal:vigencia_inicio',
            'descricao' => 'nullable|string',
            'arquivo_pdf' => 'nullable|file|mimes:pdf|max:25600',
            'ativo' => 'nullable|boolean',
        ], [
            'numero_termo.required' => 'O número de identificação do termo aditivo é obrigatório.',
            'titulo.required' => 'O título do termo aditivo é obrigatório.',
            'tipo.required' => 'O tipo do aditivo é obrigatório.',
            'vigencia_inicio.required' => 'A data inicial da vigência é obrigatória.',
            'vigencia_fim.required' => 'A data final da vigência é obrigatória.',
            'vigencia_fim.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
            'arquivo_pdf.mimes' => 'O arquivo anexo do termo aditivo deve ser um documento em formato PDF.',
            'arquivo_pdf.max' => 'O arquivo PDF não pode ultrapassar 25MB.',
        ]);

        $validated['ativo'] = $request->has('ativo');

        if ($request->hasFile('arquivo_pdf')) {
            if ($aditivo->arquivo_pdf && Storage::disk('public')->exists($aditivo->arquivo_pdf)) {
                Storage::disk('public')->delete($aditivo->arquivo_pdf);
            }

            $file = $request->file('arquivo_pdf');
            $path = $file->store('convencoes/aditivos', 'public');
            $validated['arquivo_pdf'] = $path;
            $validated['arquivo_nome_original'] = $file->getClientOriginalName();
            $validated['arquivo_tamanho'] = $file->getSize();
        }

        $aditivo->update($validated);

        return redirect()->route('admin.convencoes.show', $convencao)
            ->with('success', 'Termo Aditivo atualizado com sucesso!');
    }

    public function destroy(ConvencaoColetiva $convencao, ConvencaoTermoAditivo $aditivo): RedirectResponse
    {
        $this->validarPertencimento($convencao, $aditivo);

        if ($aditivo->arquivo_pdf && Storage::disk('public')->exists($aditivo->arquivo_pdf)) {
            Storage::disk('public')->delete($aditivo->arquivo_pdf);
        }

        $aditivo->delete();

        return redirect()->route('admin.convencoes.show', $convencao)
            ->with('success', 'Termo Aditivo excluído com sucesso!');
    }

    public function downloadPdf(ConvencaoColetiva $convencao, ConvencaoTermoAditivo $aditivo): StreamedResponse|RedirectResponse
    {
        $this->validarPertencimento($convencao, $aditivo);

        if (!$aditivo->arquivo_pdf || !Storage::disk('public')->exists($aditivo->arquivo_pdf)) {
            return redirect()->back()->with('error', 'O arquivo PDF deste termo aditivo não foi localizado no servidor.');
        }

        $nomeDownload = $aditivo->arquivo_nome_original ?: "Termo_Aditivo_{$aditivo->numero_termo}.pdf";

        return Storage::disk('public')->download($aditivo->arquivo_pdf, $nomeDownload);
    }

    private function validarPertencimento(ConvencaoColetiva $convencao, ConvencaoTermoAditivo $aditivo): void
    {
        abort_if($aditivo->convencao_coletiva_id !== $convencao->id, 404, 'O termo aditivo não pertence à convenção informada.');
    }
}
