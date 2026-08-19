<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\SocioFolha;
use App\Models\SocioFolhaHistorico;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    /**
     * Exibe a view de relatórios com histórico detalhado e ranking de desempenho.
     */
    public function index(Request $request)
    {
        $mes = $request->filled('mes') ? (int) $request->mes : (int) date('m');
        $ano = $request->filled('ano') ? (int) $request->ano : (int) date('Y');
        $userId = $request->filled('user_id') ? (int) $request->user_id : null;
        $acao = $request->filled('acao') ? (string) $request->acao : null;
        $dataInicio = $request->filled('data_inicio') ? (string) $request->data_inicio : null;
        $dataFim = $request->filled('data_fim') ? (string) $request->data_fim : null;
        $termoEmpresa = $request->filled('empresa') ? (string) $request->empresa : null;
        $activeTab = $request->get('tab', 'ranking');

        // 1. Consulta do Histórico de Alterações com Filtros
        $historicoQuery = SocioFolhaHistorico::with(['user', 'socioFolha.empresa', 'socioFolha.regiao'])
            ->orderBy('created_at', 'desc');

        if ($userId) {
            $historicoQuery->where('user_id', $userId);
        }

        if ($acao) {
            $historicoQuery->where('acao', $acao);
        }

        if ($dataInicio) {
            $historicoQuery->whereDate('created_at', '>=', $dataInicio);
        }

        if ($dataFim) {
            $historicoQuery->whereDate('created_at', '<=', $dataFim);
        }

        if ($request->filled('mes') || $request->filled('ano')) {
            $historicoQuery->whereHas('socioFolha', function ($sf) use ($request) {
                if ($request->filled('mes')) {
                    $sf->where('mes', (int) $request->mes);
                }
                if ($request->filled('ano')) {
                    $sf->where('ano', (int) $request->ano);
                }
            });
        }

        if ($termoEmpresa) {
            $historicoQuery->whereHas('socioFolha.empresa', function ($emp) use ($termoEmpresa) {
                $emp->where('razao_social', 'like', "%{$termoEmpresa}%")
                    ->orWhere('cnpj', 'like', "%{$termoEmpresa}%")
                    ->orWhere('empresa_erp', 'like', "%{$termoEmpresa}%");
            });
        }

        $historicos = $historicoQuery->paginate(25)->appends($request->all());

        // 2. Consulta e Agregação do Ranking de Produtividade
        $rankingData = $this->calcularRanking($mes, $ano, $dataInicio, $dataFim, $userId);

        // 3. Listas para filtros auxiliares
        $users = User::orderBy('name', 'asc')->get(['id', 'name', 'username']);
        $acoesDisponiveis = [
            'marcou_baixa_ok' => 'Marcou Baixa ERP (OK)',
            'desmarcou_baixa' => 'Desmarcou Baixa ERP',
            'marcou_lista_ok' => 'Marcou Lista Recebida (OK)',
            'desmarcou_lista' => 'Desmarcou Lista Recebida',
            'marcou_pago' => 'Marcou como PAGO',
            'desmarcou_pago' => 'Desmarcou PAGO',
        ];

        $anosDisponiveis = SocioFolha::distinct()
            ->whereNotNull('ano')
            ->orderBy('ano', 'desc')
            ->pluck('ano')
            ->toArray();

        if (empty($anosDisponiveis)) {
            $anosDisponiveis = [(int) date('Y')];
        }

        return view('admin.relatorios.index', [
            'historicos' => $historicos,
            'ranking' => $rankingData['ranking'],
            'totais' => $rankingData['totais'],
            'destaque' => $rankingData['destaque'],
            'users' => $users,
            'acoesDisponiveis' => $acoesDisponiveis,
            'anosDisponiveis' => $anosDisponiveis,
            'mes' => $mes,
            'ano' => $ano,
            'userId' => $userId,
            'acao' => $acao,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'termoEmpresa' => $termoEmpresa,
            'activeTab' => $activeTab,
        ]);
    }

    /**
     * Exporta o relatório de ranking de desempenho consolidado em formato PDF.
     */
    public function exportPdf(Request $request)
    {
        $mes = $request->filled('mes') ? (int) $request->mes : (int) date('m');
        $ano = $request->filled('ano') ? (int) $request->ano : (int) date('Y');
        $userId = $request->filled('user_id') ? (int) $request->user_id : null;
        $dataInicio = $request->filled('data_inicio') ? (string) $request->data_inicio : null;
        $dataFim = $request->filled('data_fim') ? (string) $request->data_fim : null;

        $rankingData = $this->calcularRanking($mes, $ano, $dataInicio, $dataFim, $userId);

        $logoPath = public_path('img/logo.jpg');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/jpeg;base64,' . base64_encode($logoData);
        }

        $pdf = Pdf::loadView('admin.relatorios.pdf_ranking', [
            'ranking' => $rankingData['ranking'],
            'totais' => $rankingData['totais'],
            'destaque' => $rankingData['destaque'],
            'mes' => $mes,
            'ano' => $ano,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'userId' => $userId,
            'usuarioFiltro' => $userId ? User::find($userId) : null,
            'logoBase64' => $logoBase64,
            'dataEmissao' => Carbon::now()->format('d/m/Y H:i:s'),
        ])->setPaper('a4', 'portrait');

        $nomeArquivo = sprintf('relatorio_desempenho_usuarios_%02d_%04d.pdf', $mes, $ano);

        return $pdf->stream($nomeArquivo);
    }

    /**
     * Realiza o cálculo agregado de produtividade e ranking de operadores.
     */
    private function calcularRanking(
        ?int $mes,
        ?int $ano,
        ?string $dataInicio,
        ?string $dataFim,
        ?int $userId = null
    ): array {
        $query = SocioFolhaHistorico::with('user')
            ->select(
                'user_id',
                DB::raw("COUNT(CASE WHEN acao = 'marcou_baixa_ok' THEN 1 END) as total_baixas_ok"),
                DB::raw("COUNT(CASE WHEN acao = 'desmarcou_baixa' THEN 1 END) as total_desmarcou_baixa"),
                DB::raw("COUNT(CASE WHEN acao = 'marcou_lista_ok' THEN 1 END) as total_listas_ok"),
                DB::raw("COUNT(CASE WHEN acao = 'desmarcou_lista' THEN 1 END) as total_desmarcou_lista"),
                DB::raw("COUNT(CASE WHEN acao = 'marcou_pago' THEN 1 END) as total_pagos"),
                DB::raw("COUNT(*) as total_acoes")
            )
            ->whereNotNull('user_id');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($dataInicio) {
            $query->whereDate('created_at', '>=', $dataInicio);
        }

        if ($dataFim) {
            $query->whereDate('created_at', '<=', $dataFim);
        }

        if (!$dataInicio && !$dataFim) {
            if ($mes) {
                $query->whereMonth('created_at', $mes);
            }
            if ($ano) {
                $query->whereYear('created_at', $ano);
            }
        }

        $rawRanking = $query->groupBy('user_id')
            ->orderByDesc('total_baixas_ok')
            ->orderByDesc('total_acoes')
            ->get();

        $ranking = [];
        $totalGeralBaixas = 0;
        $totalGeralListas = 0;
        $totalGeralPagos = 0;
        $totalGeralAcoes = 0;
        $posicao = 1;

        foreach ($rawRanking as $item) {
            if (!$item->user) {
                continue;
            }

            $baixasOk = (int) $item->total_baixas_ok;
            $listasOk = (int) $item->total_listas_ok;
            $pagos = (int) $item->total_pagos;
            $acoes = (int) $item->total_acoes;

            $totalGeralBaixas += $baixasOk;
            $totalGeralListas += $listasOk;
            $totalGeralPagos += $pagos;
            $totalGeralAcoes += $acoes;

            $ranking[] = [
                'posicao' => $posicao++,
                'user_id' => $item->user_id,
                'nome' => $item->user->name,
                'username' => $item->user->username,
                'email' => $item->user->email,
                'total_baixas_ok' => $baixasOk,
                'total_listas_ok' => $listasOk,
                'total_pagos' => $pagos,
                'total_acoes' => $acoes,
                'percentual_baixas' => 0, // calculado após somatório
            ];
        }

        // Calcula percentual individual sobre o total de baixas
        foreach ($ranking as &$userRank) {
            $userRank['percentual_baixas'] = $totalGeralBaixas > 0
                ? round(($userRank['total_baixas_ok'] / $totalGeralBaixas) * 100, 1)
                : 0.0;
        }
        unset($userRank);

        $destaque = !empty($ranking) ? $ranking[0] : null;

        return [
            'ranking' => $ranking,
            'destaque' => $destaque,
            'totais' => [
                'total_baixas_ok' => $totalGeralBaixas,
                'total_listas_ok' => $totalGeralListas,
                'total_pagos' => $totalGeralPagos,
                'total_acoes' => $totalGeralAcoes,
                'total_operadores' => count($ranking),
            ],
        ];
    }
}
