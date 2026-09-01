<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConvencaoColetiva;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConvencaoColetivaController extends Controller
{
    public function index(Request $request): View
    {
        $query = ConvencaoColetiva::withCount(['clausulas', 'aditivos']);

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }

        if ($request->filled('status')) {
            $query->where('ativo', $request->status === 'ativo');
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('data_base', 'like', "%{$search}%")
                  ->orWhere('abrangencia', 'like', "%{$search}%");
            });
        }

        $convencoes = $query->orderBy('vigencia_inicio', 'desc')->paginate(15)->withQueryString();

        return view('admin.convencoes.index', compact('convencoes'));
    }

    public function create(): View
    {
        return view('admin.convencoes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'categoria' => 'required|string|in:QUIMICA,FARMACEUTICA',
            'vigencia_inicio' => 'required|date',
            'vigencia_fim' => 'required|date|after_or_equal:vigencia_inicio',
            'data_base' => 'required|string|max:50',
            'abrangencia' => 'nullable|string',
            'arquivo_pdf' => 'nullable|file|mimes:pdf|max:25600',
            'ativo' => 'nullable|boolean',
        ], [
            'titulo.required' => 'O título da convenção é obrigatório.',
            'categoria.required' => 'A categoria é obrigatória.',
            'vigencia_inicio.required' => 'A data de início da vigência é obrigatória.',
            'vigencia_fim.required' => 'A data final da vigência é obrigatória.',
            'vigencia_fim.after_or_equal' => 'A data final deve ser igual ou posterior à data de início.',
            'data_base.required' => 'A data-base é obrigatória.',
            'arquivo_pdf.mimes' => 'O arquivo da convenção deve ser obrigatoriamente um documento em formato PDF.',
            'arquivo_pdf.max' => 'O arquivo PDF não pode ultrapassar o tamanho máximo de 25MB.',
        ]);

        $validated['ativo'] = $request->has('ativo');

        if ($request->hasFile('arquivo_pdf')) {
            $file = $request->file('arquivo_pdf');
            $path = $file->store('convencoes', 'public');
            $validated['arquivo_pdf'] = $path;
            $validated['arquivo_nome_original'] = $file->getClientOriginalName();
            $validated['arquivo_tamanho'] = $file->getSize();
        }

        $convencao = ConvencaoColetiva::create($validated);

        return redirect()->route('admin.convencoes.show', $convencao)
            ->with('success', 'Convenção Coletiva cadastrada com sucesso!');
    }

    public function show(ConvencaoColetiva $convencao): View
    {
        $convencao->load([
            'clausulas' => function ($q) {
                $q->with('termoAditivo')->orderBy('ordem')->orderBy('numero');
            },
            'aditivos' => function ($q) {
                $q->withCount('clausulas')->orderBy('vigencia_inicio', 'desc');
            }
        ]);

        return view('admin.convencoes.show', compact('convencao'));
    }

    public function edit(ConvencaoColetiva $convencao): View
    {
        return view('admin.convencoes.edit', compact('convencao'));
    }

    public function update(Request $request, ConvencaoColetiva $convencao): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'categoria' => 'required|string|in:QUIMICA,FARMACEUTICA',
            'vigencia_inicio' => 'required|date',
            'vigencia_fim' => 'required|date|after_or_equal:vigencia_inicio',
            'data_base' => 'required|string|max:50',
            'abrangencia' => 'nullable|string',
            'arquivo_pdf' => 'nullable|file|mimes:pdf|max:25600',
            'ativo' => 'nullable|boolean',
        ], [
            'titulo.required' => 'O título da convenção é obrigatório.',
            'categoria.required' => 'A categoria é obrigatória.',
            'vigencia_inicio.required' => 'A data de início da vigência é obrigatória.',
            'vigencia_fim.required' => 'A data final da vigência é obrigatória.',
            'vigencia_fim.after_or_equal' => 'A data final deve ser igual ou posterior à data de início.',
            'data_base.required' => 'A data-base é obrigatória.',
            'arquivo_pdf.mimes' => 'O arquivo da convenção deve ser obrigatoriamente um documento em formato PDF.',
            'arquivo_pdf.max' => 'O arquivo PDF não pode ultrapassar o tamanho máximo de 25MB.',
        ]);

        $validated['ativo'] = $request->has('ativo');

        if ($request->hasFile('arquivo_pdf')) {
            // Remove o arquivo anterior se existir
            if ($convencao->arquivo_pdf && Storage::disk('public')->exists($convencao->arquivo_pdf)) {
                Storage::disk('public')->delete($convencao->arquivo_pdf);
            }

            $file = $request->file('arquivo_pdf');
            $path = $file->store('convencoes', 'public');
            $validated['arquivo_pdf'] = $path;
            $validated['arquivo_nome_original'] = $file->getClientOriginalName();
            $validated['arquivo_tamanho'] = $file->getSize();
        }

        $convencao->update($validated);

        return redirect()->route('admin.convencoes.show', $convencao)
            ->with('success', 'Convenção Coletiva atualizada com sucesso!');
    }

    public function destroy(ConvencaoColetiva $convencao): RedirectResponse
    {
        if ($convencao->arquivo_pdf && Storage::disk('public')->exists($convencao->arquivo_pdf)) {
            Storage::disk('public')->delete($convencao->arquivo_pdf);
        }

        $convencao->delete();

        return redirect()->route('admin.convencoes.index')
            ->with('success', 'Convenção Coletiva excluída com sucesso!');
    }

    public function downloadPdf(ConvencaoColetiva $convencao): StreamedResponse|RedirectResponse
    {
        if (!$convencao->arquivo_pdf || !Storage::disk('public')->exists($convencao->arquivo_pdf)) {
            return redirect()->back()->with('error', 'O arquivo PDF desta convenção não foi encontrado no servidor.');
        }

        $nomeDownload = $convencao->arquivo_nome_original ?: "Convencao_{$convencao->categoria}_{$convencao->vigencia_inicio?->format('Y')}.pdf";

        return Storage::disk('public')->download($convencao->arquivo_pdf, $nomeDownload);
    }

    public function removerPdf(ConvencaoColetiva $convencao): RedirectResponse
    {
        if ($convencao->arquivo_pdf && Storage::disk('public')->exists($convencao->arquivo_pdf)) {
            Storage::disk('public')->delete($convencao->arquivo_pdf);
        }

        $convencao->update([
            'arquivo_pdf' => null,
            'arquivo_nome_original' => null,
            'arquivo_tamanho' => null,
        ]);

        return redirect()->route('admin.convencoes.show', $convencao)
            ->with('success', 'Arquivo PDF removido com sucesso!');
    }
}
