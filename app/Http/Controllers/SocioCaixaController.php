<?php

namespace App\Http\Controllers;

use App\Models\SocioCaixa;
use App\Models\SocioCaixaOcorrencia;
use App\Imports\SocioCaixaImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SocioCaixaController extends Controller
{
    public function index(Request $request)
    {
        // Salva a URL atual na sessão para o botão voltar
        session(['socio_caixa_url' => request()->fullUrl()]);
        
        // 1. Definir padrões se for a primeira vez
        if (!$request->has('min_abertos')) {
            $request->merge(['min_abertos' => 2]);
        }

        $query = SocioCaixa::query();

        // Filtro por status de inativação Ábaco
        if ($request->has('ver_inativados')) {
            $query->where('inativado_abaco', true);
        } else {
            $query->where('inativado_abaco', false);
        }

        // Filtros encadeados (restaurados)
        if ($request->filled('ano')) {
            $query->where('ano', $request->ano);
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
            ->selectRaw('SUM(CASE WHEN (pago = 0 AND (postergado_ate IS NULL OR postergado_ate <= NOW())) THEN valor ELSE 0 END) as valor_aberto')
            ->selectRaw('COUNT(CASE WHEN (pago = 0 AND postergado_ate > NOW()) THEN 1 END) as total_postergados')
            ->selectRaw('MAX(inativado_abaco) as inativado_abaco')
            ->selectRaw('MIN(id) as id') 
            ->selectSub(\App\Models\SocioCaixaOcorrencia::whereColumn('matricula', 'socio_caixas.matricula')->where('mensagem', 'LIKE', '[WHATSAPP]%')->selectRaw('COUNT(*)'), 'qtde_contatos')
            ->groupBy('matricula', 'nome', 'tipo_socio');

        // Filtro de quantidade mínima em aberto
        $minAbertos = $request->input('min_abertos', 2);
        
        if ($request->has('ver_inativados')) {
            // Ao consultar inativados, listamos todos independentemente de filtro de abertos
            $query->havingRaw('COUNT(id) > 0');
        } elseif ($request->has('ver_postergados')) {
            $query->havingRaw('COUNT(CASE WHEN (pago = 0 AND postergado_ate > NOW()) THEN 1 END) > 0');
        } else {
            // Se min_abertos não foi informado, mantemos o padrão de mostrar quem tem pelo menos 1 (para ser um "Painel de Pendências")
            $threshold = $minAbertos > 0 ? $minAbertos : 1;
            $query->havingRaw('COUNT(CASE WHEN (pago = 0 AND (postergado_ate IS NULL OR postergado_ate <= NOW())) THEN 1 END) >= ?', [$threshold]);
        }

        $socios = $query->orderBy('nome')
                        ->paginate(20)
                        ->appends($request->all());
        
        $anos = SocioCaixa::select('ano')->distinct()->orderBy('ano', 'desc')->pluck('ano');
        $tipos = SocioCaixa::select('tipo_socio')->distinct()->whereNotNull('tipo_socio')->orderBy('tipo_socio')->pluck('tipo_socio');
        
        return view('socio_caixa.index', compact('socios', 'anos', 'tipos'));
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

    public function inativarAbaco(Request $request, SocioCaixa $socio)
    {
        $motivo = $request->input('motivo');

        // Atualiza todos os lançamentos desta matrícula para inativado
        SocioCaixa::where('matricula', $socio->matricula)->update([
            'inativado_abaco' => true,
            'inativado_abaco_em' => now(),
        ]);

        $userName = auth()->user()->nickname ?: auth()->user()->name;
        $obs = "[INATIVAÇÃO ERP ÁBACO] Associado inativado no ERP Ábaco pelo operador {$userName}.";
        if (!empty($motivo)) {
            $obs .= " Motivo: " . $motivo;
        }

        // Registrar na timeline de ocorrências
        SocioCaixaOcorrencia::create([
            'matricula' => $socio->matricula,
            'user_id' => auth()->id(),
            'mensagem' => $obs,
        ]);

        // Registrar no histórico de lançamentos do registro
        $socio->historico()->create([
            'user_id' => auth()->id(),
            'acao' => 'inativar_abaco',
            'observacao' => $obs,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Associado inativado no Ábaco com sucesso.'
        ]);
    }

    public function reativarAbaco(Request $request, SocioCaixa $socio)
    {
        $motivo = $request->input('motivo');

        // Reverte status para ativo em todos os lançamentos desta matrícula
        SocioCaixa::where('matricula', $socio->matricula)->update([
            'inativado_abaco' => false,
            'inativado_abaco_em' => null,
        ]);

        $userName = auth()->user()->nickname ?: auth()->user()->name;
        $obs = "[REATIVAÇÃO MANUAL] Associado reativado manualmente pelo operador {$userName}.";
        if (!empty($motivo)) {
            $obs .= " Motivo: " . $motivo;
        }

        // Registrar na timeline de ocorrências
        SocioCaixaOcorrencia::create([
            'matricula' => $socio->matricula,
            'user_id' => auth()->id(),
            'mensagem' => $obs,
        ]);

        // Registrar no histórico de lançamentos do registro
        $socio->historico()->create([
            'user_id' => auth()->id(),
            'acao' => 'reativar_abaco',
            'observacao' => $obs,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Associado reativado com sucesso.'
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file'
        ]);

        try {
            Excel::import(new \App\Imports\SocioCaixaImport, $request->file('file'));
            return redirect()->back()->with('success', 'Importação concluída com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro na importação: ' . $e->getMessage());
        }
    }

    public function storeOcorrencia(Request $request)
    {
        $request->validate([
            'matricula' => 'required|string',
            'mensagem' => 'required|string|min:3'
        ]);

        SocioCaixaOcorrencia::create([
            'matricula' => $request->matricula,
            'user_id' => auth()->id(),
            'mensagem' => $request->mensagem
        ]);

        return response()->json(['success' => true]);
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

        // Ocorrências/Atendimentos deste sócio
        $ocorrencias = SocioCaixaOcorrencia::where('matricula', $socio->matricula)
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        // Buscar empresa pelo cod_emp (campo empresa_erp na tabela empresas)
        $empresa = null;
        if ($socio->cod_emp) {
            $empresa = \App\Models\Empresa::where('empresa_erp', $socio->cod_emp)->first();
        }

        return view('socio_caixa.show', compact('socio', 'lancamentos', 'ocorrencias', 'empresa'));
    }

    public function updateTelefone(Request $request, SocioCaixa $socio)
    {
        $request->validate([
            'telefone' => 'required|string|min:10'
        ]);

        SocioCaixa::where('matricula', $socio->matricula)->update([
            'telefone' => $request->telefone
        ]);

        return response()->json(['success' => true]);
    }

    public function enviarWhatsapp(Request $request, SocioCaixa $socio)
    {
        if ($socio->inativado_abaco) {
            return response()->json(['success' => false, 'message' => 'Este associado está inativado no ERP Ábaco.'], 422);
        }

        if (empty($socio->telefone)) {
            return response()->json(['success' => false, 'message' => 'Telefone não cadastrado.'], 422);
        }

        $abertos = SocioCaixa::where('matricula', $socio->matricula)
            ->where('pago', false)
            ->where(function($q) {
                $q->whereNull('postergado_ate')
                  ->orWhere('postergado_ate', '<=', now());
            })
            ->orderBy('ano')
            ->orderBy('mes')
            ->get();

        if ($abertos->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Nenhuma mensalidade em aberto para notificar.'], 422);
        }

        $competencias = $abertos->map(function($item) {
            $comp = str_pad($item->mes, 2, '0', STR_PAD_LEFT) . '/' . $item->ano;
            if ($item->data_vencimento) {
                $comp .= ' (Venc: ' . $item->data_vencimento->format('d/m/Y') . ')';
            }
            return $comp;
        })->implode(', ');

        $userName = auth()->user()->nickname ?: auth()->user()->name;

        // Despachar Job para API Kwik
        \App\Jobs\SendKwikNotificationJob::dispatch(
            $socio->telefone,
            'aviso_mensalidade_caixa',
            [$socio->nome, $userName, $competencias],
            auth()->id()
        );

        // Registrar no histórico de ocorrências (Anotações / Atendimento)
        SocioCaixaOcorrencia::create([
            'matricula' => $socio->matricula,
            'user_id' => auth()->id(),
            'mensagem' => "[WHATSAPP] Aviso de mensalidade enviado para {$socio->telefone}. Competências: {$competencias}"
        ]);

        return response()->json(['success' => true]);
    }
}
