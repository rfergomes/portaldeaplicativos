<?php

namespace App\Http\Controllers\Ativos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AtivoEquipamento;
use App\Models\AtivoAquisicao;
use App\Models\AtivoFornecedor;
use App\Models\AtivoMarketplace;
use App\Models\AtivoAnexo;
use App\Models\AtivoFabricante;
use Illuminate\Support\Facades\DB;

class AtivoAquisicaoController extends Controller
{
    public function index(Request $request)
    {
        $query = AtivoAquisicao::with(['fornecedor', 'marketplace'])->withCount('equipamentos');

        // Filtros
        if ($request->filled('numero_nf')) {
            $query->where('numero_nf', 'like', '%' . $request->numero_nf . '%');
        }

        if ($request->filled('fornecedor_id')) {
            $query->where('fornecedor_id', $request->fornecedor_id);
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_aquisicao', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data_aquisicao', '<=', $request->data_fim);
        }

        $aquisicoes = $query->orderBy('data_aquisicao', 'desc')->paginate(20)->appends($request->all());
        $fornecedores = AtivoFornecedor::orderBy('nome')->get();

        // Estatísticas
        $totalNFs = AtivoAquisicao::count();
        $totalInvestido = AtivoAquisicao::sum('valor_total');
        $totalEquipamentos = AtivoEquipamento::whereNotNull('aquisicao_id')->count();
        $investidoAno = AtivoAquisicao::whereYear('data_aquisicao', date('Y'))->sum('valor_total');

        return view('ativos.aquisicoes.index', compact('aquisicoes', 'fornecedores', 'totalNFs', 'totalInvestido', 'totalEquipamentos', 'investidoAno'));
    }

    public function create()
    {
        $fornecedores = AtivoFornecedor::where('ativo', true)->orderBy('nome')->get();
        $marketplaces = AtivoMarketplace::where('ativo', true)->orderBy('nome')->get();
        $fabricantes = AtivoFabricante::where('ativo', true)->orderBy('nome')->get();

        return view('ativos.aquisicoes.create', compact('fornecedores', 'marketplaces', 'fabricantes'));
    }

    public function store(Request $request)
    {
        if ($request->has('chave_acesso') && $request->chave_acesso !== null) {
            $request->merge(['chave_acesso' => preg_replace('/[^0-9]/', '', $request->chave_acesso)]);
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
            'itens.*.descricao' => 'required|string|max:255',
            'itens.*.modelo' => 'nullable|string|max:255',
            'itens.*.fabricante_id' => 'nullable|exists:ativo_fabricantes,id',
            'itens.*.quantidade' => 'required|integer|min:1',
            'itens.*.valor_unitario' => 'required|numeric|min:0',
            'itens.*.numeros_serie' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Cria a Aquisição
            $aquisicao = AtivoAquisicao::create([
                'numero_nf' => $validated['numero_nf'] ?? null,
                'chave_acesso' => $validated['chave_acesso'] ?? null,
                'data_aquisicao' => $validated['data_aquisicao'],
                'fornecedor_id' => $validated['fornecedor_id'] ?? null,
                'marketplace_id' => $validated['marketplace_id'] ?? null,
                'valor_frete' => $validated['valor_frete'] ?? null,
                'valor_total' => $validated['valor_total'] ?? null,
                'observacao' => $validated['observacao'] ?? null,
            ]);

            // Percorre os Itens e Cadastra os Equipamentos um por vez
            foreach ($validated['itens'] as $item) {
                // Processa números de série se fornecidos
                $seriaisStr = $item['numeros_serie'] ?? '';
                $seriais = preg_split('/[\n,]+/', $seriaisStr, -1, PREG_SPLIT_NO_EMPTY);
                $seriais = array_map('trim', $seriais);

                for ($i = 0; $i < $item['quantidade']; $i++) {
                    AtivoEquipamento::create([
                        'descricao' => $item['descricao'],
                        'modelo' => $item['modelo'] ?? null,
                        'fabricante_id' => $item['fabricante_id'] ?? null,
                        'valor_item' => $item['valor_unitario'],
                        'status' => 'disponivel', // Por padrão entram como disponíveis
                        'fornecedor_id' => $aquisicao->fornecedor_id,
                        'aquisicao_id' => $aquisicao->id,
                        'marketplace_id' => $aquisicao->marketplace_id,
                        'data_compra' => $aquisicao->data_aquisicao,
                        'valor_nota' => $aquisicao->numero_nf,
                        'numero_serie' => $seriais[$i] ?? null,
                    ]);
                }
            }

            if ($request->hasFile('anexos')) {
                foreach ($request->file('anexos') as $file) {
                    $path = $file->store('ativos/anexos', 'public');
                    AtivoAnexo::create([
                        'aquisicao_id' => $aquisicao->id,
                        'caminho' => $path,
                        'nome_original' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'tamanho' => $file->getSize(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('ativos.aquisicoes.index')->with('success', 'Aquisição e equipamentos registrados com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erro ao salvar a aquisição: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $aquisicao = AtivoAquisicao::with(['fornecedor', 'marketplace', 'equipamentos.fabricante', 'anexos'])->findOrFail($id);
        return view('ativos.aquisicoes.show', compact('aquisicao'));
    }

    public function edit($id)
    {
        $aquisicao = AtivoAquisicao::findOrFail($id);
        $fornecedores = AtivoFornecedor::where('ativo', true)->orderBy('nome')->get();
        $marketplaces = AtivoMarketplace::where('ativo', true)->orderBy('nome')->get();

        return view('ativos.aquisicoes.edit', compact('aquisicao', 'fornecedores', 'marketplaces'));
    }

    public function update(Request $request, $id)
    {
        $aquisicao = AtivoAquisicao::findOrFail($id);

        if ($request->has('chave_acesso') && $request->chave_acesso !== null) {
            $request->merge(['chave_acesso' => preg_replace('/[^0-9]/', '', $request->chave_acesso)]);
        }

        $validated = $request->validate([
            'numero_nf' => 'nullable|string|max:255',
            'chave_acesso' => 'nullable|string|max:255',
            'data_aquisicao' => 'required|date',
            'fornecedor_id' => 'nullable|exists:ativo_fornecedores,id',
            'marketplace_id' => 'nullable|exists:ativo_marketplaces,id',
            'valor_frete' => 'nullable|numeric|min:0',
            'valor_total' => 'nullable|numeric|min:0',
            'observacao' => 'nullable|string',
        ]);

        $aquisicao->update([
            'numero_nf' => $validated['numero_nf'] ?? null,
            'chave_acesso' => $validated['chave_acesso'] ?? null,
            'data_aquisicao' => $validated['data_aquisicao'],
            'fornecedor_id' => $validated['fornecedor_id'] ?? null,
            'marketplace_id' => $validated['marketplace_id'] ?? null,
            'valor_frete' => $validated['valor_frete'] ?? null,
            'valor_total' => $validated['valor_total'] ?? null,
            'observacao' => $validated['observacao'] ?? null,
        ]);

        // Sincroniza os equipamentos filhos se houver mudança nesses dados herdeiros
        if ($aquisicao->wasChanged(['fornecedor_id', 'marketplace_id', 'data_aquisicao', 'numero_nf'])) {
            $aquisicao->equipamentos()->update([
                'fornecedor_id' => $aquisicao->fornecedor_id,
                'marketplace_id' => $aquisicao->marketplace_id,
                'data_compra' => $aquisicao->data_aquisicao,
                'valor_nota' => $aquisicao->numero_nf,
            ]);
        }

        return redirect()->route('ativos.aquisicoes.show', $aquisicao->id)->with('success', 'Cabeçalho da aquisição atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $aquisicao = AtivoAquisicao::findOrFail($id);
        
        // Verifica se algum equipamento dessa aquisição já sofreu movimentação
        $temMovimentacao = $aquisicao->equipamentos()->whereHas('movimentacoes')->exists();
        
        if ($temMovimentacao) {
            return back()->with('error', 'Não é possível excluir esta aquisição, pois alguns equipamentos associados a ela já estão em uso (possuem histórico de movimentação).');
        }

        DB::beginTransaction();
        try {
            // Exclui os equipamentos
            $aquisicao->equipamentos()->delete();
            // Exclui a aquisição
            $aquisicao->delete();
            
            DB::commit();
            
            return redirect()->route('ativos.aquisicoes.index')->with('success', 'Aquisição e equipamentos excluídos com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao excluir: ' . $e->getMessage());
        }
    }

    public function uploadAnexo(Request $request, AtivoAquisicao $aquisicao)
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240', // 10MB
        ]);

        $file = $request->file('arquivo');
        $path = $file->store('ativos/anexos', 'public');

        $aquisicao->anexos()->create([
            'caminho' => $path,
            'nome_original' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'tamanho' => $file->getSize(),
        ]);

        return redirect()->back()->with('success', 'Documento anexado à NF com sucesso!');
    }
}
