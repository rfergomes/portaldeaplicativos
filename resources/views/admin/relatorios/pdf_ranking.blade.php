<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Desempenho de Usuários - Sócio Folha</title>
    <style>
        @page {
            margin: 25px 25px 35px 25px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #2d3748;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-logo {
            width: 130px;
            text-align: left;
            vertical-align: middle;
        }
        .header-logo img {
            max-width: 120px;
            max-height: 45px;
        }
        .header-info {
            text-align: right;
            vertical-align: middle;
        }
        .report-title {
            font-size: 14pt;
            font-weight: bold;
            color: #0d6efd;
            margin: 0 0 3px 0;
            text-transform: uppercase;
        }
        .report-subtitle {
            font-size: 8.5pt;
            color: #718096;
            margin: 0;
        }
        
        /* Summary Grid / Cards */
        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 12px;
        }
        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 8px;
            text-align: center;
            vertical-align: middle;
        }
        .summary-card.highlight {
            background-color: #fef9c3;
            border-color: #fde047;
        }
        .summary-label {
            font-size: 7.5pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .summary-value {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
        }
        .summary-value.primary { color: #0d6efd; }
        .summary-value.success { color: #16a34a; }
        .summary-value.warning { color: #d97706; }

        /* Filter Box */
        .filter-box {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 8pt;
            margin-bottom: 14px;
            color: #334155;
        }

        /* Ranking Table */
        .ranking-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 8.5pt;
        }
        .ranking-table th {
            background-color: #e2e8f0;
            color: #1e293b;
            border: 1px solid #cbd5e1;
            padding: 6px 6px;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
            font-size: 7.5pt;
        }
        .ranking-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            vertical-align: middle;
        }
        .ranking-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .ranking-table tr.top-1 {
            background-color: #fefce8;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7.5pt;
            font-weight: bold;
        }
        .badge-pos-1 { background-color: #eab308; color: #ffffff; }
        .badge-pos-2 { background-color: #94a3b8; color: #ffffff; }
        .badge-pos-3 { background-color: #cd7f32; color: #ffffff; }
        .badge-pos-other { background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .badge-baixas { background-color: #0d6efd; color: #ffffff; }
        .badge-total { background-color: #334155; color: #ffffff; }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 20px;
            text-align: center;
            font-size: 7.5pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
        .page-number:before {
            content: "Página " counter(page);
        }
    </style>
</head>
<body>

    <!-- Header com Logotipo e Título -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" alt="Logotipo">
                @else
                    <span style="font-weight: bold; color: #0d6efd; font-size: 14pt;">PORTAL</span>
                @endif
            </td>
            <td class="header-info">
                <h1 class="report-title">Relatório de Desempenho de Usuários</h1>
                <div class="report-subtitle">Módulo Sócio Folha &bull; Ranking Mensal de Produtividade</div>
            </td>
        </tr>
    </table>

    <!-- Cards de Totais e Destaque -->
    <table class="summary-table">
        <tr>
            <td class="summary-card" style="width: 25%;">
                <div class="summary-label">Total Baixas ERP (OK)</div>
                <div class="summary-value primary">{{ number_format($totais['total_baixas_ok'], 0, ',', '.') }}</div>
            </td>
            <td class="summary-card" style="width: 25%;">
                <div class="summary-label">Listas Recebidas (OK)</div>
                <div class="summary-value success">{{ number_format($totais['total_listas_ok'], 0, ',', '.') }}</div>
            </td>
            <td class="summary-card" style="width: 25%;">
                <div class="summary-label">Total de Ações Apuradas</div>
                <div class="summary-value">{{ number_format($totais['total_acoes'], 0, ',', '.') }}</div>
            </td>
            <td class="summary-card highlight" style="width: 25%;">
                <div class="summary-label">🥇 Destaque do Mês</div>
                <div class="summary-value warning" style="font-size: 10.5pt; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                    {{ $destaque['nome'] ?? 'Nenhum' }}
                </div>
                <div style="font-size: 7.5pt; color: #854d0e;">
                    @if($destaque)
                        {{ $destaque['total_baixas_ok'] }} Baixas ({{ $destaque['percentual_baixas'] }}%)
                    @else
                        Sem registros
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- Barra de Filtros Aplicados -->
    <div class="filter-box">
        <strong>Filtros aplicados:</strong>
        @if($dataInicio || $dataFim)
            Período: <strong>{{ $dataInicio ? \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') : 'Início' }} até {{ $dataFim ? \Carbon\Carbon::parse($dataFim)->format('d/m/Y') : 'Atual' }}</strong> |
        @else
            @php
                $meses = [
                    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
                ];
            @endphp
            Mês de Apuração: <strong>{{ $meses[$mes] ?? $mes }}/{{ $ano }}</strong> |
        @endif
        @if($usuarioFiltro)
            Operador: <strong>{{ $usuarioFiltro->name }} ({{ $usuarioFiltro->username }})</strong> |
        @else
            Operadores: <strong>Todos</strong> |
        @endif
        Emissão: <strong>{{ $dataEmissao }}</strong>
    </div>

    <!-- Tabela de Ranking -->
    <table class="ranking-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 45px;">Rank</th>
                <th>Nome do Operador</th>
                <th style="width: 110px;">Usuário / Login</th>
                <th class="text-center" style="width: 95px;">Baixas ERP (OK)</th>
                <th class="text-center" style="width: 75px;">Listas OK</th>
                <th class="text-center" style="width: 65px;">Pagos</th>
                <th class="text-center" style="width: 80px;">Total Ações</th>
                <th class="text-center" style="width: 75px;">% Baixas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ranking as $item)
                <tr class="{{ $item['posicao'] === 1 ? 'top-1' : '' }}">
                    <td class="text-center">
                        @if($item['posicao'] === 1)
                            <span class="badge badge-pos-1">1º</span>
                        @elseif($item['posicao'] === 2)
                            <span class="badge badge-pos-2">2º</span>
                        @elseif($item['posicao'] === 3)
                            <span class="badge badge-pos-3">3º</span>
                        @else
                            <span class="badge badge-pos-other">{{ $item['posicao'] }}º</span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $item['nome'] }}</strong>
                    </td>
                    <td style="color: #64748b;">
                        {{ $item['username'] }}
                    </td>
                    <td class="text-center">
                        <span class="badge badge-baixas">{{ number_format($item['total_baixas_ok'], 0, ',', '.') }}</span>
                    </td>
                    <td class="text-center">
                        {{ number_format($item['total_listas_ok'], 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        {{ number_format($item['total_pagos'], 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        <span class="badge badge-total">{{ number_format($item['total_acoes'], 0, ',', '.') }}</span>
                    </td>
                    <td class="text-center" style="font-weight: bold; color: #16a34a;">
                        {{ $item['percentual_baixas'] }}%
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 25px; color: #64748b;">
                        Nenhum registro de ação encontrado para o período especificado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Rodapé Fixo -->
    <div class="footer">
        Portal de Aplicativos &bull; Documento Oficial de Desempenho &bull; Gerado em {{ $dataEmissao }} &bull; <span class="page-number"></span>
    </div>

</body>
</html>
