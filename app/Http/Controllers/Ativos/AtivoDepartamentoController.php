<?php

namespace App\Http\Controllers\Ativos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AtivoDepartamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departamentos = \App\Models\AtivoDepartamento::withCount('usuarios')
            ->orderBy('nome')
            ->get()
            ->map(function ($depto) {
                // Conta equipamentos que estão "em_uso" e pertencem a usuários deste departamento
                $depto->equipamentos_count = \App\Models\AtivoEquipamento::where('status', 'em_uso')
                    ->whereHas('ultimaMovimentacao', function ($q) use ($depto) {
                        $q->whereHas('usuario', function ($qu) use ($depto) {
                            $qu->where('departamento_id', $depto->id);
                        });
                    })->count();
                return $depto;
            });

        return view('ativos.departamentos.index', compact('departamentos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'ativo' => 'boolean',
        ]);

        \App\Models\AtivoDepartamento::create($validated);

        return redirect()->route('ativos.departamentos.index')->with('success', 'Departamento criado com sucesso!');
    }

    public function update(Request $request, string $id)
    {
        $departamento = \App\Models\AtivoDepartamento::findOrFail($id);
        
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'ativo' => 'boolean',
        ]);

        $departamento->update($validated);

        return redirect()->route('ativos.departamentos.index')->with('success', 'Departamento atualizado com sucesso!');
    }

    public function destroy(string $id)
    {
        $departamento = \App\Models\AtivoDepartamento::findOrFail($id);
        
        if ($departamento->usuarios()->exists()) {
            return redirect()->back()->with('error', 'Não é possível excluir um departamento que possui usuários vinculados.');
        }

        $departamento->delete();

        return redirect()->route('ativos.departamentos.index')->with('success', 'Departamento excluído com sucesso!');
    }
}
