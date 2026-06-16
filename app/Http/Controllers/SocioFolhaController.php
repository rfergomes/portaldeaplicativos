<?php

namespace App\Http\Controllers;

use App\Models\SocioFolha;
use App\Imports\SocioFolhaImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class SocioFolhaController extends Controller
{
    public function index(Request $request)
    {
        // Se nenhum filtro principal for passado, assumimos o ano atual como padrão inicial
        if (!$request->has('ano') && !$request->has('regiao_id') && !$request->has('empresa_id')) {
            $request->merge(['ano' => date('Y')]);
        }

        $query = SocioFolha::with(['empresa', 'regiao'])
            ->join('empresas', 'socios_folha.empresa_id', '=', 'empresas.id')
            ->select('socios_folha.*');

        if ($request->filled('regiao_id')) {
            $query->where('socios_folha.regiao_id', $request->regiao_id);
        }

        if ($request->filled('empresa_id')) {
            $query->where('socios_folha.empresa_id', $request->empresa_id);
        }

        if ($request->filled('ano')) {
            $query->where('socios_folha.ano', $request->ano);
        }

        if ($request->filled('mes')) {
            $query->where('socios_folha.mes', $request->mes);
        }

        // Clone da query para calcular totais
        $queryTotais = clone $query;
        $totalPagoCount = (clone $queryTotais)->where('socios_folha.situacao', 'PAGO')->count();
        $totalPagoValor = (clone $queryTotais)->where('socios_folha.situacao', 'PAGO')->sum('socios_folha.valor_mensalidade');
        
        $totalPendenteCount = (clone $queryTotais)->where('socios_folha.situacao', '!=', 'PAGO')->count();
        $totalPendenteValor = (clone $queryTotais)->where('socios_folha.situacao', '!=', 'PAGO')->sum('socios_folha.valor_mensalidade');

        $sociosFolha = $query->orderBy('socios_folha.ano', 'desc')
                             ->orderBy('socios_folha.mes', 'desc')
                             ->orderBy('empresas.razao_social', 'asc')
                             ->paginate(20)
                             ->appends($request->all());

        $regioes = \App\Models\Regiao::where('ativo', true)->orderBy('nome')->get();
        $anos = SocioFolha::select('ano')->distinct()->orderBy('ano', 'desc')->pluck('ano');
        $meses = SocioFolha::select('mes')->distinct()->orderBy('mes', 'asc')->pluck('mes');

        // Carrega todas as empresas que possuem registros em Sócio Folha
        $empresas = \App\Models\Empresa::whereHas('socioFolha')
            ->orderBy('razao_social')
            ->get(['id', 'razao_social']);

        return view('socio_folha.index', compact('sociosFolha', 'regioes', 'anos', 'meses', 'empresas', 'totalPagoCount', 'totalPagoValor', 'totalPendenteCount', 'totalPendenteValor'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx,csv'
        ]);

        try {
            Excel::import(new SocioFolhaImport, $request->file('file'));
            return redirect()->back()->with('success', 'Importação concluída com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro na importação: ' . $e->getMessage());
        }
    }

    public function toggleSituacao(Request $request, SocioFolha $socio)
    {
        $request->validate([
            'valor_pago' => 'nullable|numeric'
        ]);

        if ($socio->situacao === 'PAGO') {
            return response()->json(['success' => false, 'message' => 'Lançamento já está pago e não pode ser revertido.']);
        }

        $socio->update([
            'situacao' => 'PAGO',
            'valor_pago' => $request->valor_pago,
        ]);

        $socio->historico()->create([
            'user_id' => auth()->id(),
            'acao' => 'marcou_pago',
            'detalhes' => 'Marcou a situação como PAGO. Valor: ' . $request->valor_pago
        ]);

        return response()->json([
            'success' => true,
            'situacao' => 'PAGO'
        ]);
    }

    public function toggleLista(Request $request, SocioFolha $socio)
    {
        $novaData = $socio->data_lista ? null : now();

        $socio->update([
            'data_lista' => $novaData
        ]);

        $acaoDesc = $novaData ? 'marcou_lista_ok' : 'desmarcou_lista';

        $socio->historico()->create([
            'user_id' => auth()->id(),
            'acao' => $acaoDesc,
            'detalhes' => $novaData ? 'Marcou o recebimento da Lista como OK.' : 'Desmarcou o recebimento da Lista.'
        ]);

        return response()->json([
            'success' => true,
            'data_lista' => $novaData ? $novaData->format('d/m/Y H:i') : null
        ]);
    }

    public function toggleBaixa(Request $request, SocioFolha $socio)
    {
        if (!$socio->data_lista) {
            return response()->json(['success' => false, 'message' => 'Não é possível dar baixa sem antes confirmar o recebimento da Lista.']);
        }

        $novaData = $socio->data_baixa ? null : now();

        $socio->update([
            'data_baixa' => $novaData
        ]);

        $acaoDesc = $novaData ? 'marcou_baixa_ok' : 'desmarcou_baixa';

        $socio->historico()->create([
            'user_id' => auth()->id(),
            'acao' => $acaoDesc,
            'detalhes' => $novaData ? 'Marcou a Baixa ERP como OK.' : 'Desmarcou a Baixa ERP.'
        ]);

        return response()->json([
            'success' => true,
            'data_baixa' => $novaData ? $novaData->format('d/m/Y H:i') : null
        ]);
    }

    public function getEmpresasPorRegiao($regiao_id)
    {
        $query = \App\Models\Empresa::whereHas('socioFolha')
                                     ->where('ativo', true)
                                     ->orderBy('razao_social');

        if ($regiao_id !== 'all') {
            $query->where('regiao_id', $regiao_id);
        }

        $empresas = $query->get(['id', 'razao_social', 'empresa_erp']);
        return response()->json($empresas);
    }

    public function getEmailHistorico(SocioFolha $socio)
    {
        $historico = $socio->emailHistoricos()
            ->with('cliente')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($historico);
    }

    public function exportPendentesPdf(Request $request)
    {
        $query = SocioFolha::with(['empresa', 'regiao'])
            ->join('empresas', 'socios_folha.empresa_id', '=', 'empresas.id')
            ->select('socios_folha.*')
            ->where('socios_folha.situacao', '!=', 'PAGO');

        if ($request->filled('regiao_id')) {
            $query->where('socios_folha.regiao_id', $request->regiao_id);
        }
        if ($request->filled('empresa_id')) {
            $query->where('socios_folha.empresa_id', $request->empresa_id);
        }
        if ($request->filled('ano')) {
            $query->where('socios_folha.ano', $request->ano);
        }
        if ($request->filled('mes')) {
            $query->where('socios_folha.mes', $request->mes);
        }

        $pendentes = $query->orderBy('empresas.razao_social', 'asc')
                           ->orderBy('socios_folha.ano', 'asc')
                           ->orderBy('socios_folha.mes', 'asc')
                           ->get();

        $pdf = Pdf::loadView('socio_folha.pdf_pendentes', compact('pendentes', 'request'))
                  ->setPaper('a4', 'landscape');
                  
        return $pdf->stream('relatorio_pendentes_folha.pdf');
    }

    public function exportEmpresaDebitosPdf(Request $request, $empresa_id)
    {
        $empresa = \App\Models\Empresa::findOrFail($empresa_id);
        
        $debitos = SocioFolha::with('regiao')
            ->where('empresa_id', $empresa_id)
            ->where('situacao', '!=', 'PAGO')
            ->orderBy('ano', 'asc')
            ->orderBy('mes', 'asc')
            ->get();

        $pdf = Pdf::loadView('socio_folha.pdf_empresa', compact('empresa', 'debitos'))
                  ->setPaper('a4', 'portrait');
                  
        return $pdf->stream('debitos_empresa_' . $empresa_id . '.pdf');
    }
}
