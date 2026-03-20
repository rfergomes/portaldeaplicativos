<?php

namespace App\Http\Controllers\Ativos;

use App\Http\Controllers\Controller;
use App\Models\AtivoLicenca;
use App\Models\AtivoFabricante;
use Illuminate\Http\Request;

class AtivoLicencaController extends Controller
{
    public function index()
    {
        $licencas = AtivoLicenca::with('fabricante')
            ->withCount('equipamentos')
            ->orderBy('nome')
            ->paginate(15);
            
        return view('ativos.licencas.index', compact('licencas'));
    }

    public function create()
    {
        $fabricantes = AtivoFabricante::where('ativo', true)->orderBy('nome')->get();
        return view('ativos.licencas.create', compact('fabricantes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'chave' => 'nullable|string|max:255',
            'tipo_licenca' => 'required|in:vitalicia,assinatura',
            'data_validade' => 'nullable|date',
            'fabricante_id' => 'nullable|exists:ativo_fabricantes,id',
            'quantidade_seats' => 'required|integer|min:1',
            'observacao' => 'nullable|string',
        ]);

        AtivoLicenca::create($validated);

        return redirect()->route('ativos.licencas.index')->with('success', 'Licença cadastrada com sucesso!');
    }

    public function edit(AtivoLicenca $licenca)
    {
        $fabricantes = AtivoFabricante::where('ativo', true)->orderBy('nome')->get();
        return view('ativos.licencas.edit', compact('licenca', 'fabricantes'));
    }

    public function update(Request $request, AtivoLicenca $licenca)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'chave' => 'nullable|string|max:255',
            'tipo_licenca' => 'required|in:vitalicia,assinatura',
            'data_validade' => 'nullable|date',
            'fabricante_id' => 'nullable|exists:ativo_fabricantes,id',
            'quantidade_seats' => 'required|integer|min:1',
            'observacao' => 'nullable|string',
        ]);

        $licenca->update($validated);

        return redirect()->route('ativos.licencas.index')->with('success', 'Licença atualizada com sucesso!');
    }

    public function destroy(string $id)
    {
        $licenca = AtivoLicenca::findOrFail($id);
        
        if ($licenca->equipamentos()->exists()) {
            return redirect()->back()->with('error', 'Não é possível excluir uma licença que está vinculada a equipamentos. Desvincule-os primeiro.');
        }

        $licenca->delete();

        return redirect()->route('ativos.licencas.index')->with('success', 'Licença excluída com sucesso!');
    }

    public function vincularEquipamento(Request $request, $equipamentoId)
    {
        $request->validate([
            'licenca_id' => 'required|exists:ativo_licencas,id'
        ]);

        $equipamento = \App\Models\AtivoEquipamento::findOrFail($equipamentoId);
        $licenca = AtivoLicenca::findOrFail($request->licenca_id);

        // Verifica se já está vinculado
        if ($equipamento->licencas()->where('ativo_licenca_id', $licenca->id)->exists()) {
            return redirect()->back()->with('error', 'Esta licença já está vinculada a este equipamento.');
        }

        // Verifica seats
        if ($licenca->equipamentos()->count() >= $licenca->quantidade_seats) {
            return redirect()->back()->with('error', 'Limite de ativações (seats) atingido para esta licença.');
        }

        $equipamento->licencas()->attach($licenca->id);

        return redirect()->back()->with('success', 'Licença vinculada com sucesso!');
    }

    public function desvincularEquipamento($licencaId, $equipamentoId)
    {
        $equipamento = \App\Models\AtivoEquipamento::findOrFail($equipamentoId);
        $equipamento->licencas()->detach($licencaId);

        return redirect()->back()->with('success', 'Licença desvinculada com sucesso!');
    }
}
