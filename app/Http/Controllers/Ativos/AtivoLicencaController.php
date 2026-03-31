<?php

namespace App\Http\Controllers\Ativos;

use App\Http\Controllers\Controller;
use App\Models\AtivoLicenca;
use App\Models\AtivoFabricante;
use Illuminate\Http\Request;

class AtivoLicencaController extends Controller
{
    public function index(Request $request)
    {
        $query = AtivoLicenca::with(['fabricante', 'fornecedor', 'aquisicao'])
            ->withCount('equipamentos');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->search . '%')
                  ->orWhere('chave', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('fabricante_id')) {
            $query->where('fabricante_id', $request->fabricante_id);
        }

        if ($request->filled('tipo_licenca')) {
            $query->where('tipo_licenca', $request->tipo_licenca);
        }

        $licencas = $query->orderBy('nome')->paginate(15);
        $fabricantes = AtivoFabricante::where('ativo', true)->orderBy('nome')->get();
            
        // Estatísticas
        $totalLicencas = AtivoLicenca::count();
        $licencasExpirando = AtivoLicenca::whereNotNull('data_validade')
            ->whereDate('data_validade', '<=', now()->addDays(30))
            ->count();
        $custoSoftware = AtivoLicenca::sum('valor_total');
        $totalSeats = AtivoLicenca::sum('quantidade_seats');
        $seatsEmUso = \Illuminate\Support\Facades\DB::table('ativo_licenca_equipamento')->count();

        return view('ativos.licencas.index', compact('licencas', 'fabricantes', 'totalLicencas', 'licencasExpirando', 'custoSoftware', 'totalSeats', 'seatsEmUso'));
    }

    public function createAquisicao()
    {
        $fabricantes = AtivoFabricante::where('ativo', true)->orderBy('nome')->get();
        $fornecedores = \App\Models\AtivoFornecedor::where('ativo', true)->orderBy('nome')->get();
        $marketplaces = \App\Models\AtivoMarketplace::where('ativo', true)->orderBy('nome')->get();
        return view('ativos.licencas.create_aquisicao', compact('fabricantes', 'fornecedores', 'marketplaces'));
    }

    public function storeAquisicao(Request $request)
    {
        if ($request->has('chave_acesso') && $request->chave_acesso !== null) {
            $request->merge(['chave_acesso' => preg_replace('/[^0-9]/', '', $request->chave_acesso)]);
        }

        // Sanitize numeric inputs (replace , with .) and handle empty strings
        if ($request->has('valor_frete')) {
            $val = $request->valor_frete !== null ? str_replace(',', '.', $request->valor_frete) : null;
            $request->merge(['valor_frete' => ($val === '' ? null : $val)]);
        }
        if ($request->has('valor_total')) {
            $val = $request->valor_total !== null ? str_replace(',', '.', $request->valor_total) : null;
            $request->merge(['valor_total' => ($val === '' ? null : $val)]);
        }
        
        if ($request->has('itens')) {
            $itens = $request->itens;
            foreach ($itens as $i => $item) {
                if (isset($item['valor_unitario'])) {
                    $val = str_replace(',', '.', $item['valor_unitario']);
                    $itens[$i]['valor_unitario'] = ($val === '' ? null : $val);
                }
                if (isset($item['tipo_licenca'])) {
                    $itens[$i]['tipo_licenca'] = $item['tipo_licenca'] !== null ? strtolower($item['tipo_licenca']) : null;
                }
            }
            $request->merge(['itens' => $itens]);
        }

        $validated = $request->validate([
            // Cabeçalho
            'numero_nf' => 'nullable|string|max:255',
            'chave_acesso' => 'nullable|string|max:255',
            'data_aquisicao' => 'required|date',
            'fornecedor_id' => 'nullable|exists:ativo_fornecedores,id',
            'marketplace_id' => 'nullable|exists:ativo_marketplaces,id',
            'valor_frete' => 'nullable|numeric|min:0',
            'valor_total' => 'nullable|numeric|min:0',
            'observacao' => 'nullable|string',
            'anexos.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',

            // Itens Array
            'itens' => 'required|array|min:1',
            'itens.*.nome' => 'required|string|max:255',
            'itens.*.chave' => 'nullable|string|max:255',
            'itens.*.fabricante_id' => 'nullable|exists:ativo_fabricantes,id',
            'itens.*.tipo_licenca' => 'required|in:vitalicia,assinatura',
            'itens.*.data_validade' => 'nullable|date',
            'itens.*.quantidade_seats' => 'required|integer|min:1',
            'itens.*.valor_unitario' => 'required|numeric|min:0',
            'itens.*.observacao' => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            // Cria a Aquisição de Ativo (Genericamente usada para Equipamentos e agora Licenças)
            $aquisicao = \App\Models\AtivoAquisicao::create([
                'numero_nf' => $validated['numero_nf'] ?? null,
                'chave_acesso' => $validated['chave_acesso'] ?? null,
                'data_aquisicao' => $validated['data_aquisicao'],
                'fornecedor_id' => $validated['fornecedor_id'] ?? null,
                'marketplace_id' => $validated['marketplace_id'] ?? null,
                'valor_frete' => $validated['valor_frete'] ?? null,
                'valor_total' => $validated['valor_total'] ?? null,
                'observacao' => $validated['observacao'] ?? null,
            ]);

            // Percorre os Itens e Cadastra as Licenças
            foreach ($validated['itens'] as $item) {
                AtivoLicenca::create([
                    'aquisicao_id' => $aquisicao->id,
                    'nome' => $item['nome'],
                    'chave' => $item['chave'] ?? null,
                    'fabricante_id' => $item['fabricante_id'] ?? null,
                    'tipo_licenca' => $item['tipo_licenca'],
                    'data_validade' => $item['data_validade'] ?? null,
                    'quantidade_seats' => $item['quantidade_seats'],
                    'observacao' => $item['observacao'] ?? null,
                    // Dados denormalizados da aquisição para manter compatibilidade com views atuais
                    'fornecedor_id' => $aquisicao->fornecedor_id,
                    'marketplace_id' => $aquisicao->marketplace_id,
                    'numero_nf' => $aquisicao->numero_nf,
                    'chave_acesso' => $aquisicao->chave_acesso,
                    'data_aquisicao' => $aquisicao->data_aquisicao,
                    'valor_total' => $item['valor_unitario'] * $item['quantidade_seats'], // Valor específico desta licença
                ]);
            }

            // Anexos
            if ($request->hasFile('anexos')) {
                foreach ($request->file('anexos') as $file) {
                    $path = $file->store('ativos/anexos', 'public');
                    \App\Models\AtivoAnexo::create([
                        'aquisicao_id' => $aquisicao->id,
                        'caminho' => $path,
                        'nome_original' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'tamanho' => $file->getSize(),
                    ]);
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('ativos.licencas.index')->with('success', 'Aquisição de licenças registrada com sucesso!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withInput()->with('error', 'Erro ao salvar a aquisição: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $fabricantes = AtivoFabricante::where('ativo', true)->orderBy('nome')->get();
        $fornecedores = \App\Models\AtivoFornecedor::where('ativo', true)->orderBy('nome')->get();
        $marketplaces = \App\Models\AtivoMarketplace::where('ativo', true)->orderBy('nome')->get();
        return view('ativos.licencas.create', compact('fabricantes', 'fornecedores', 'marketplaces'));
    }

    public function store(Request $request)
    {
        if ($request->has('chave_acesso') && $request->chave_acesso !== null) {
            $request->merge(['chave_acesso' => preg_replace('/[^0-9]/', '', $request->chave_acesso)]);
        }

        // Sanitize numeric inputs (replace , with .) and handle empty strings
        if ($request->has('valor_frete')) {
            $val = $request->valor_frete !== null ? str_replace(',', '.', $request->valor_frete) : null;
            $request->merge(['valor_frete' => ($val === '' ? null : $val)]);
        }
        if ($request->has('valor_total')) {
            $val = $request->valor_total !== null ? str_replace(',', '.', $request->valor_total) : null;
            $request->merge(['valor_total' => ($val === '' ? null : $val)]);
        }
        if ($request->has('tipo_licenca')) {
            $request->merge(['tipo_licenca' => $request->tipo_licenca !== null ? strtolower($request->tipo_licenca) : null]);
        }

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'chave' => 'nullable|string|max:255',
            'tipo_licenca' => 'required|in:vitalicia,assinatura',
            'data_validade' => 'nullable|date',
            'fabricante_id' => 'nullable|exists:ativo_fabricantes,id',
            'fornecedor_id' => 'nullable|exists:ativo_fornecedores,id',
            'marketplace_id' => 'nullable|exists:ativo_marketplaces,id',
            'numero_nf' => 'nullable|string|max:255',
            'chave_acesso' => 'nullable|string|max:255',
            'data_aquisicao' => 'nullable|date',
            'valor_total' => 'nullable|numeric|min:0',
            'valor_frete' => 'nullable|numeric|min:0',
            'quantidade_seats' => 'required|integer|min:1',
            'observacao' => 'nullable|string',
        ]);

        AtivoLicenca::create($validated);

        return redirect()->route('ativos.licencas.index')->with('success', 'Licença cadastrada com sucesso!');
    }

    public function edit(AtivoLicenca $licenca)
    {
        $fabricantes = AtivoFabricante::where('ativo', true)->orderBy('nome')->get();
        $fornecedores = \App\Models\AtivoFornecedor::where('ativo', true)->orderBy('nome')->get();
        $marketplaces = \App\Models\AtivoMarketplace::where('ativo', true)->orderBy('nome')->get();
        return view('ativos.licencas.edit', compact('licenca', 'fabricantes', 'fornecedores', 'marketplaces'));
    }

    public function update(Request $request, AtivoLicenca $licenca)
    {
        if ($request->has('chave_acesso') && $request->chave_acesso !== null) {
            $request->merge(['chave_acesso' => preg_replace('/[^0-9]/', '', $request->chave_acesso)]);
        }

        // Sanitize numeric inputs (replace , with .) and handle empty strings
        if ($request->has('valor_frete')) {
            $val = $request->valor_frete !== null ? str_replace(',', '.', $request->valor_frete) : null;
            $request->merge(['valor_frete' => ($val === '' ? null : $val)]);
        }
        if ($request->has('valor_total')) {
            $val = $request->valor_total !== null ? str_replace(',', '.', $request->valor_total) : null;
            $request->merge(['valor_total' => ($val === '' ? null : $val)]);
        }
        if ($request->has('tipo_licenca')) {
            $request->merge(['tipo_licenca' => $request->tipo_licenca !== null ? strtolower($request->tipo_licenca) : null]);
        }

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'chave' => 'nullable|string|max:255',
            'tipo_licenca' => 'required|in:vitalicia,assinatura',
            'data_validade' => 'nullable|date',
            'fabricante_id' => 'nullable|exists:ativo_fabricantes,id',
            'fornecedor_id' => 'nullable|exists:ativo_fornecedores,id',
            'marketplace_id' => 'nullable|exists:ativo_marketplaces,id',
            'numero_nf' => 'nullable|string|max:255',
            'chave_acesso' => 'nullable|string|max:255',
            'data_aquisicao' => 'nullable|date',
            'valor_total' => 'nullable|numeric|min:0',
            'valor_frete' => 'nullable|numeric|min:0',
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
