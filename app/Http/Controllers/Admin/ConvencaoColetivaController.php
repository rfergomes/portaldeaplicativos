<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConvencaoColetiva;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConvencaoColetivaController extends Controller
{
    public function index(Request $request): View
    {
        $query = ConvencaoColetiva::withCount('clausulas');

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
            'ativo' => 'nullable|boolean',
        ], [
            'titulo.required' => 'O título da convenção é obrigatório.',
            'categoria.required' => 'A categoria é obrigatória.',
            'vigencia_inicio.required' => 'A data de início da vigência é obrigatória.',
            'vigencia_fim.required' => 'A data final da vigência é obrigatória.',
            'vigencia_fim.after_or_equal' => 'A data final deve ser igual ou posterior à data de início.',
            'data_base.required' => 'A data-base é obrigatória.',
        ]);

        $validated['ativo'] = $request->has('ativo');

        $convencao = ConvencaoColetiva::create($validated);

        return redirect()->route('admin.convencoes.show', $convencao)
            ->with('success', 'Convenção Coletiva cadastrada com sucesso!');
    }

    public function show(ConvencaoColetiva $convencao): View
    {
        $convencao->load(['clausulas' => function ($q) {
            $q->orderBy('ordem')->orderBy('numero');
        }]);

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
            'ativo' => 'nullable|boolean',
        ], [
            'titulo.required' => 'O título da convenção é obrigatório.',
            'categoria.required' => 'A categoria é obrigatória.',
            'vigencia_inicio.required' => 'A data de início da vigência é obrigatória.',
            'vigencia_fim.required' => 'A data final da vigência é obrigatória.',
            'vigencia_fim.after_or_equal' => 'A data final deve ser igual ou posterior à data de início.',
            'data_base.required' => 'A data-base é obrigatória.',
        ]);

        $validated['ativo'] = $request->has('ativo');

        $convencao->update($validated);

        return redirect()->route('admin.convencoes.show', $convencao)
            ->with('success', 'Convenção Coletiva atualizada com sucesso!');
    }

    public function destroy(ConvencaoColetiva $convencao): RedirectResponse
    {
        $convencao->delete();

        return redirect()->route('admin.convencoes.index')
            ->with('success', 'Convenção Coletiva excluída com sucesso!');
    }
}
