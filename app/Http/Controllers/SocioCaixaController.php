<?php

namespace App\Http\Controllers;

use App\Models\SocioCaixa;
use App\Imports\SocioCaixaImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SocioCaixaController extends Controller
{
    public function index(Request $request)
    {
        $query = SocioCaixa::query();

        // Filtro de Status (Default: Em Aberto - pago=0)
        if (!$request->has('pago')) {
            $request->merge(['pago' => '0']);
        }

        if ($request->filled('pago') && $request->pago !== 'todos') {
            $query->where('pago', $request->pago == '1');
        }

        // Filtro de Postergados
        // Default: Esconde os que estão com data futura
        if ($request->pago == '0' && !$request->has('ver_postergados')) {
            $query->where(function($q) {
                $q->whereNull('postergado_ate')
                  ->orWhere('postergado_ate', '<=', now());
            });
        } elseif ($request->has('ver_postergados')) {
            $query->whereNotNull('postergado_ate')->where('postergado_ate', '>', now());
        }

        // Filtros encadeados (simplificados)
        if ($request->filled('ano')) {
            $query->where('ano', $request->ano);
        }
        if ($request->filled('mes')) {
            $query->where('mes', $request->mes);
        }
        if ($request->filled('matricula')) {
            $query->where('matricula', 'like', '%' . $request->matricula . '%');
        }
        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . $request->nome . '%');
        }
        if ($request->filled('tipo')) {
            $query->where('tipo_socio', $request->tipo);
        }

        $query->select('matricula', 'nome', 'tipo_socio')
            ->selectRaw('COUNT(CASE WHEN pago = 1 THEN 1 END) as total_pagos')
            ->selectRaw('COUNT(CASE WHEN (pago = 0 AND (postergado_ate IS NULL OR postergado_ate <= NOW())) THEN 1 END) as total_abertos')
            ->selectRaw('COUNT(CASE WHEN (pago = 0 AND postergado_ate > NOW()) THEN 1 END) as total_postergados')
            // Pegamos o ID de qualquer um dos registros para manter compatibilidade com rotas se necessário,
            // mas o ideal é usar a matrícula
            ->selectRaw('MIN(id) as id') 
            ->groupBy('matricula', 'nome', 'tipo_socio');

        $socios = $query->orderBy('nome')
                        ->paginate(20)
                        ->appends($request->all());
        
        $anos = SocioCaixa::select('ano')->distinct()->orderBy('ano', 'desc')->pluck('ano');
        $tipos = SocioCaixa::select('tipo_socio')->distinct()->whereNotNull('tipo_socio')->orderBy('tipo_socio')->pluck('tipo_socio');
        
        // Months for the selected year or all
        $mesQuery = SocioCaixa::select('mes')->distinct();
        if ($request->filled('ano')) {
            $mesQuery->where('ano', $request->ano);
        }
        $meses = $mesQuery->orderBy('mes')->pluck('mes');

        return view('socio_caixa.index', compact('socios', 'anos', 'meses', 'tipos'));
    }

    public function dashboard(Request $request)
    {
        // --- Info Cards ---
        $totalLancamentos  = SocioCaixa::count();
        $totalPagos        = SocioCaixa::where('pago', true)->count();
        $totalAbertos      = SocioCaixa::where('pago', false)->count();
        $totalPostergados  = SocioCaixa::whereNotNull('postergado_ate')->where('postergado_ate', '>', now())->count();

        // --- Ranking de Operadores (quem mais deu baixa) ---
        $rankingOperadores = \DB::table('socio_caixa_historicos')
            ->join('users', 'users.id', '=', 'socio_caixa_historicos.user_id')
            ->where('socio_caixa_historicos.acao', 'baixa')
            ->select('users.name', \DB::raw('count(*) as total'))
            ->groupBy('users.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // --- Movimentações por Ação (para gráfico de rosca) ---
        $movimentacoesPorAcao = \DB::table('socio_caixa_historicos')
            ->select('acao', \DB::raw('count(*) as total'))
            ->groupBy('acao')
            ->get()
            ->pluck('total', 'acao');

        // --- Pagamentos por Mês (para gráfico de barras) ---
        $pagosPorMes = SocioCaixa::where('pago', true)
            ->select('mes', \DB::raw('count(*) as total'))
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total', 'mes');

        // --- Abertos por Tipo de Sócio ---
        $abertosPorTipo = SocioCaixa::where('pago', false)
            ->whereNotNull('tipo_socio')
            ->select('tipo_socio', \DB::raw('count(*) as total'))
            ->groupBy('tipo_socio')
            ->orderByDesc('total')
            ->get();

        // --- Últimas Movimentações ---
        $ultimasMovimentacoes = \App\Models\SocioCaixaHistorico::with(['socio', 'user'])
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        return view('socio_caixa.dashboard', compact(
            'totalLancamentos', 'totalPagos', 'totalAbertos', 'totalPostergados',
            'rankingOperadores', 'movimentacoesPorAcao', 'pagosPorMes', 'abertosPorTipo',
            'ultimasMovimentacoes'
        ));
    }

    public function postpone(Request $request, SocioCaixa $socio)
    {
        $request->validate([
            'postergado_ate' => 'required|date|after:now',
            'motivo' => 'nullable|string'
        ]);

        // Atualiza todos os lançamentos em aberto desta matrícula
        SocioCaixa::where('matricula', $socio->matricula)
            ->where('pago', false)
            ->update([
                'postergado_ate' => $request->postergado_ate,
                'motivo_postergacao' => $request->motivo
            ]);

        // Registra o histórico no registro "pai" (ou no que foi clicado)
        $socio->historico()->create([
            'user_id' => auth()->id(),
            'acao' => 'postergar_coletivo',
            'observacao' => "Postergação COLETIVA aplicada a todos os débitos até " . \Carbon\Carbon::parse($request->postergado_ate)->format('d/m/Y') . ". Motivo: " . ($request->motivo ?: 'N/D')
        ]);

        return response()->json(['success' => true]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file'
        ]);

        try {
            Excel::import(new SocioCaixaImport, $request->file('file'));
            return redirect()->back()->with('success', 'Importação concluída com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro na importação: ' . $e->getMessage());
        }
    }

    public function togglePayment(Request $request, SocioCaixa $socio)
    {
        $oldStatus = $socio->pago;
        $newStatus = !$oldStatus;
        $acao = $newStatus ? 'baixa' : 'estorno';

        $socio->update([
            'pago' => $newStatus,
            'data_pagamento' => $newStatus ? now() : null,
            'user_id' => auth()->id(),
            'observacao' => $request->observacao
        ]);

        // Gravar histórico
        $socio->historico()->create([
            'user_id' => auth()->id(),
            'acao' => $acao,
            'observacao' => $request->observacao
        ]);

        return response()->json([
            'success' => true, 
            'pago' => $socio->pago,
            'data_pagamento' => $socio->data_pagamento ? $socio->data_pagamento->format('d/m/Y H:i') : null,
            'usuario' => auth()->user()->nickname ?: auth()->user()->name
        ]);
    }

    public function show(SocioCaixa $socio)
    {
        // Todos os lançamentos deste sócio pela matrícula
        $lancamentos = SocioCaixa::where('matricula', $socio->matricula)
            ->with(['usuarioBaixa', 'historico.user'])
            ->orderBy('ano', 'desc')
            ->orderBy('mes', 'desc')
            ->get();

        return view('socio_caixa.show', compact('socio', 'lancamentos'));
    }
}
