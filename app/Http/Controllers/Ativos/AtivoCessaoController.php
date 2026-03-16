<?php

namespace App\Http\Controllers\Ativos;

use App\Http\Controllers\Controller;
use App\Models\AtivoCessao;
use App\Models\AtivoEquipamento;
use App\Models\AtivoMovimentacao;
use App\Models\AtivoUsuario;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AtivoCessaoController extends Controller
{
    public function index(Request $request)
    {
        $query = AtivoCessao::with(['usuario', 'movimentacoes.equipamento']);

        if ($request->filled('search')) {
            $query->where('codigo_cessao', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('usuario_id')) {
            $query->where('usuario_id', $request->usuario_id);
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_cessao', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data_cessao', '<=', $request->data_fim);
        }

        $cessoes = $query->orderBy('data_cessao', 'desc')->paginate(15);
        $usuarios = AtivoUsuario::orderBy('nome')->get();

        return view('ativos.cessoes.index', compact('cessoes', 'usuarios'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'usuario_id' => 'required|exists:ativo_usuarios,id',
            'equipamentos' => 'required|array',
            'equipamentos.*' => 'exists:ativo_equipamentos,id',
            'data_previsao_devolucao' => 'nullable|date',
            'observacoes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $usuario = AtivoUsuario::findOrFail($validated['usuario_id']);
            
            // Gerar código de cessão (CSN + ID sequencial aproximado)
            $ultimoId = AtivoCessao::max('id') ?? 0;
            $codigo = 'CSN' . str_pad($ultimoId + 1, 3, '0', STR_PAD_LEFT);

            $cessao = AtivoCessao::create([
                'usuario_id' => $usuario->id,
                'data_cessao' => now(),
                'codigo_cessao' => $codigo,
                'observacoes' => $validated['observacoes'],
            ]);

            foreach ($validated['equipamentos'] as $equipId) {
                $equipamento = AtivoEquipamento::lockForUpdate()->findOrFail($equipId);
                
                $origem = $equipamento->localizacao_atual;

                // Registrar Movimentação vinculada à Cessão
                AtivoMovimentacao::create([
                    'equipamento_id' => $equipamento->id,
                    'usuario_id' => $usuario->id,
                    'tipo' => 'cessao',
                    'data_movimentacao' => now(),
                    'data_previsao_devolucao' => $validated['data_previsao_devolucao'],
                    'responsavel_id' => auth()->id(),
                    'cessao_id' => $cessao->id,
                    'origem' => $origem,
                    'destino' => $usuario->nome,
                    'observacao' => $validated['observacoes'],
                ]);

                // Atualizar Equipamento
                $equipamento->update([
                    'status' => 'em_uso',
                    'tipo_uso' => 'cessão',
                    'localizacao_atual' => $usuario->nome,
                    'data_devolucao_prevista' => $validated['data_previsao_devolucao'],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cessão registrada com sucesso!',
                'cessao_id' => $cessao->id
            ]);
        });
    }

    public function generatePdf(AtivoCessao $cessao)
    {
        $cessao->load(['usuario', 'movimentacoes.equipamento.fabricante']);
        
        $pdf = Pdf::loadView('ativos.cessoes.pdf_termo', compact('cessao'));
        
        $filename = 'termo_cessao_' . $cessao->codigo_cessao . '.pdf';
        
        // Armazenar PDF para consultas futuras
        $path = 'ativos/cessoes/' . $filename;
        Storage::disk('public')->put($path, $pdf->output());
        
        $cessao->update(['termo_pdf_path' => $path]);

        return $pdf->stream($filename);
    }

    public function uploadAnexo(Request $request, AtivoCessao $cessao)
    {
        $request->validate([
            'arquivo' => 'required|file|max:10240', // 10MB
        ]);

        $file = $request->file('arquivo');
        $path = $file->store('ativos/anexos', 'public');

        $cessao->anexos()->create([
            'equipamento_id' => $cessao->movimentacoes->first()->equipamento_id ?? null,
            'cessao_id' => $cessao->id,
            'caminho' => $path,
            'nome_original' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'tamanho' => $file->getSize(),
        ]);

        return redirect()->back()->with('success', 'Documento anexado com sucesso!');
    }

    public function destroyAnexo(\App\Models\AtivoAnexo $anexo)
    {
        Storage::disk('public')->delete($anexo->caminho);
        $anexo->delete();
        return redirect()->back()->with('success', 'Anexo excluído com sucesso!');
    }
}
