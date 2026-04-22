<?php

namespace App\Http\Controllers;

use App\Models\SocioFolha;
use App\Imports\SocioFolhaImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

    public function index(Request $request)
    {
        $query = SocioFolha::with(['empresa', 'regiao']);

        if ($request->filled('regiao_id')) {
            $query->where('regiao_id', $request->regiao_id);
        }

        if ($request->filled('empresa_id')) {
            $query->where('empresa_id', $request->empresa_id);
        }

        if ($request->filled('ano')) {
            $query->where('ano', $request->ano);
        }

        if ($request->filled('mes')) {
            $query->where('mes', $request->mes);
        }

        $sociosFolha = $query->orderBy('ano', 'desc')
                             ->orderBy('mes', 'desc')
                             ->orderBy('data_vencimento', 'asc')
                             ->paginate(20)
                             ->appends($request->all());

        $regioes = \App\Models\Regiao::where('ativo', true)->orderBy('nome')->get();
        $anos = SocioFolha::select('ano')->distinct()->orderBy('ano', 'desc')->pluck('ano');
        $meses = SocioFolha::select('mes')->distinct()->orderBy('mes', 'asc')->pluck('mes');

        return view('socio_folha.index', compact('sociosFolha', 'regioes', 'anos', 'meses'));
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
        $empresas = \App\Models\Empresa::where('regiao_id', $regiao_id)
                                       ->where('ativo', true)
                                       ->orderBy('razao_social')
                                       ->get(['id', 'razao_social', 'empresa_erp']);
        return response()->json($empresas);
    }
}
