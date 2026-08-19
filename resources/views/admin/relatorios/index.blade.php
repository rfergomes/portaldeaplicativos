@extends('layouts.app')

@section('title', 'Relatórios e Desempenho de Usuários')

@section('content')
<div class="container-fluid py-3">

    <!-- Header / Título e Ações Rápidas -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 fw-bold text-primary mb-1">
                <i class="fa-solid fa-chart-line me-2"></i>Relatórios & Desempenho
            </h2>
            <p class="text-muted small mb-0">
                Auditoria de alterações e ranking mensal de produtividade por usuário.
            </p>
        </div>
        <div class="mt-2 mt-md-0 d-flex gap-2">
            <a href="{{ route('admin.relatorios.pdf', request()->all()) }}" target="_blank" class="btn btn-danger btn-sm shadow-sm rounded-pill px-3">
                <i class="fa-solid fa-file-pdf me-1"></i> Exportar Ranking em PDF
            </a>
        </div>
    </div>

    <!-- Filtros Globais -->
    <div class="card card-outline card-primary shadow-sm mb-4 border-0">
        <div class="card-header bg-white py-3">
            <h5 class="card-title fw-bold text-dark m-0 fs-6">
                <i class="fa-solid fa-filter text-primary me-2"></i>Filtros de Apuração
            </h5>
        </div>
        <div class="card-body bg-light-subtle">
            <form method="GET" action="{{ route('admin.relatorios.index') }}" class="row g-3 align-items-end" id="filter-form">
                <input type="hidden" name="tab" id="active-tab-input" value="{{ $activeTab }}">

                <!-- Usuário -->
                <div class="col-md-3">
                    <label for="user_id" class="form-label small fw-semibold text-muted">Usuário Responsável</label>
                    <select name="user_id" id="user_id" class="form-select form-select-sm shadow-sm">
                        <option value="">-- Todos os Usuários --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->username }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Mês de Apuração -->
                <div class="col-md-2">
                    <label for="mes" class="form-label small fw-semibold text-muted">Mês</label>
                    <select name="mes" id="mes" class="form-select form-select-sm shadow-sm">
                        @php
                            $meses = [
                                1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                                5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                                9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
                            ];
                        @endphp
                        @foreach($meses as $num => $nomeMes)
                            <option value="{{ $num }}" {{ $mes == $num ? 'selected' : '' }}>
                                {{ $nomeMes }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Ano -->
                <div class="col-md-2">
                    <label for="ano" class="form-label small fw-semibold text-muted">Ano</label>
                    <select name="ano" id="ano" class="form-select form-select-sm shadow-sm">
                        @foreach($anosDisponiveis as $a)
                            <option value="{{ $a }}" {{ $ano == $a ? 'selected' : '' }}>
                                {{ $a }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tipo de Ação (Aplica no Histórico) -->
                <div class="col-md-2">
                    <label for="acao" class="form-label small fw-semibold text-muted">Tipo de Ação</label>
                    <select name="acao" id="acao" class="form-select form-select-sm shadow-sm">
                        <option value="">-- Todas as Ações --</option>
                        @foreach($acoesDisponiveis as $chaveAcao => $rotuloAcao)
                            <option value="{{ $chaveAcao }}" {{ $acao == $chaveAcao ? 'selected' : '' }}>
                                {{ $rotuloAcao }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Busca Empresa -->
                <div class="col-md-3">
                    <label for="empresa" class="form-label small fw-semibold text-muted">Empresa / CNPJ</label>
                    <input type="text" name="empresa" id="empresa" class="form-control form-select-sm shadow-sm" placeholder="Nome ou CNPJ..." value="{{ $termoEmpresa }}">
                </div>

                <!-- Data Início / Fim Opcionais -->
                <div class="col-md-3">
                    <label for="data_inicio" class="form-label small fw-semibold text-muted">Data Inicial (Opcional)</label>
                    <input type="date" name="data_inicio" id="data_inicio" class="form-control form-select-sm shadow-sm" value="{{ $dataInicio }}">
                </div>

                <div class="col-md-3">
                    <label for="data_fim" class="form-label small fw-semibold text-muted">Data Final (Opcional)</label>
                    <input type="date" name="data_fim" id="data_fim" class="form-control form-select-sm shadow-sm" value="{{ $dataFim }}">
                </div>

                <!-- Botões de Ação -->
                <div class="col-md-6 d-flex gap-2 justify-content-md-end">
                    <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.relatorios.index') }}" class="btn btn-secondary btn-sm px-3 shadow-sm">
                        <i class="fa-solid fa-rotate-left me-1"></i> Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Navegação por Abas: Ranking de Produtividade vs Histórico Detalhado -->
    <ul class="nav nav-pills mb-4 nav-justified bg-white p-2 rounded shadow-sm border" id="relatoriosTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link py-2 {{ $activeTab === 'ranking' ? 'active fw-bold' : 'text-muted' }}" 
                    id="ranking-tab" 
                    data-bs-toggle="pill" 
                    data-bs-target="#ranking-content" 
                    type="button" 
                    role="tab" 
                    onclick="document.getElementById('active-tab-input').value='ranking'">
                <i class="fa-solid fa-trophy me-2 text-warning"></i> Ranking de Desempenho (Mensal)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link py-2 {{ $activeTab === 'historico' ? 'active fw-bold' : 'text-muted' }}" 
                    id="historico-tab" 
                    data-bs-toggle="pill" 
                    data-bs-target="#historico-content" 
                    type="button" 
                    role="tab"
                    onclick="document.getElementById('active-tab-input').value='historico'">
                <i class="fa-solid fa-clock-rotate-left me-2 text-info"></i> Histórico Geral de Alterações
            </button>
        </li>
    </ul>

    <div class="tab-content" id="relatoriosTabContent">

        <!-- ABA 1: RANKING DE DESEMPENHO -->
        <div class="tab-pane fade {{ $activeTab === 'ranking' ? 'show active' : '' }}" id="ranking-content" role="tabpanel">
            
            <!-- Cards de Métricas e Destaque -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-primary text-white h-100">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 text-uppercase small mb-1 fw-bold">Total Baixas ERP</h6>
                                    <h3 class="fw-bold mb-0">{{ number_format($totais['total_baixas_ok'], 0, ',', '.') }}</h3>
                                </div>
                                <div class="fs-1 text-white-50">
                                    <i class="fa-solid fa-check-double"></i>
                                </div>
                            </div>
                            <small class="text-white-50">Ação marcou_baixa_ok apurada</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-success text-white h-100">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 text-uppercase small mb-1 fw-bold">Listas Recebidas</h6>
                                    <h3 class="fw-bold mb-0">{{ number_format($totais['total_listas_ok'], 0, ',', '.') }}</h3>
                                </div>
                                <div class="fs-1 text-white-50">
                                    <i class="fa-solid fa-list-check"></i>
                                </div>
                            </div>
                            <small class="text-white-50">Ação marcou_lista_ok</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-info text-white h-100">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-white-50 text-uppercase small mb-1 fw-bold">Total Geral de Ações</h6>
                                    <h3 class="fw-bold mb-0">{{ number_format($totais['total_acoes'], 0, ',', '.') }}</h3>
                                </div>
                                <div class="fs-1 text-white-50">
                                    <i class="fa-solid fa-bolt"></i>
                                </div>
                            </div>
                            <small class="text-white-50">Volume de operações efetuadas</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0 bg-warning text-dark h-100">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-dark-50 text-uppercase small mb-1 fw-bold">🥇 Destaque do Mês</h6>
                                    <h5 class="fw-bold mb-0 text-truncate" style="max-width: 160px;" title="{{ $destaque['nome'] ?? 'Nenhum' }}">
                                        {{ $destaque['nome'] ?? 'Sem ações' }}
                                    </h5>
                                </div>
                                <div class="fs-1 text-dark-50">
                                    <i class="fa-solid fa-award text-warning-emphasis"></i>
                                </div>
                            </div>
                            <small class="text-dark-50">
                                @if($destaque)
                                    {{ $destaque['total_baixas_ok'] }} Baixas ERP ({{ $destaque['percentual_baixas'] }}%)
                                @else
                                    Nenhum registro apurado
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Ranking -->
            <div class="card card-outline card-primary shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold text-dark m-0 fs-6">
                        <i class="fa-solid fa-ranking-star text-warning me-2"></i>Classificação Geral dos Operadores
                    </h5>
                    <span class="badge bg-light text-dark border px-2 py-1">
                        Período: {{ $meses[$mes] ?? $mes }}/{{ $ano }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 80px;">Posição</th>
                                    <th>Operador / Usuário</th>
                                    <th class="text-center">Baixas ERP (OK) 🎯</th>
                                    <th class="text-center">Listas OK</th>
                                    <th class="text-center">Pagos</th>
                                    <th class="text-center">Total de Ações</th>
                                    <th style="width: 200px;">Participação nas Baixas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ranking as $rank)
                                    <tr class="{{ $rank['posicao'] === 1 ? 'table-warning-subtle' : '' }}">
                                        <td class="text-center">
                                            @if($rank['posicao'] === 1)
                                                <span class="badge bg-warning text-dark rounded-circle p-2 fs-6 shadow-sm" title="1º Lugar">
                                                    🥇 1º
                                                </span>
                                            @elseif($rank['posicao'] === 2)
                                                <span class="badge bg-secondary text-white rounded-circle p-2 fs-6 shadow-sm" title="2º Lugar">
                                                    🥈 2º
                                                </span>
                                            @elseif($rank['posicao'] === 3)
                                                <span class="badge bg-danger text-white rounded-circle p-2 fs-6 shadow-sm" style="background-color: #cd7f32 !important;" title="3º Lugar">
                                                    🥉 3º
                                                </span>
                                            @else
                                                <span class="badge bg-light text-dark border rounded-pill px-2 py-1">
                                                    {{ $rank['posicao'] }}º
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $rank['nome'] }}</div>
                                            <div class="text-muted small"><i class="fa-solid fa-user me-1"></i>{{ $rank['username'] }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill shadow-sm">
                                                {{ number_format($rank['total_baixas_ok'], 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="text-center fw-semibold text-secondary">
                                            {{ number_format($rank['total_listas_ok'], 0, ',', '.') }}
                                        </td>
                                        <td class="text-center fw-semibold text-secondary">
                                            {{ number_format($rank['total_pagos'], 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-dark rounded-pill px-2 py-1">
                                                {{ number_format($rank['total_acoes'], 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 8px;">
                                                    <div class="progress-bar bg-success" role="progressbar" 
                                                         style="width: {{ $rank['percentual_baixas'] }}%;" 
                                                         aria-valuenow="{{ $rank['percentual_baixas'] }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100"></div>
                                                </div>
                                                <span class="small fw-bold text-muted" style="min-width: 45px;">
                                                    {{ $rank['percentual_baixas'] }}%
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-circle-info fs-2 text-secondary mb-3 d-block"></i>
                                            <h6 class="fw-semibold">Nenhuma ação registrada para o período selecionado.</h6>
                                            <p class="small mb-0">Ajuste os filtros de Mês/Ano ou datas para consultar outros períodos.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ABA 2: HISTÓRICO GERAL DE ALTERAÇÕES -->
        <div class="tab-pane fade {{ $activeTab === 'historico' ? 'show active' : '' }}" id="historico-content" role="tabpanel">
            <div class="card card-outline card-primary shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold text-dark m-0 fs-6">
                        <i class="fa-solid fa-list-ul text-primary me-2"></i>Log de Auditoria de Sócio Folha
                    </h5>
                    <span class="badge bg-secondary rounded-pill px-2 py-1">
                        {{ $historicos->total() }} registros encontrados
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 160px;">Data / Hora</th>
                                    <th>Usuário Responsável</th>
                                    <th>Ação Realizada</th>
                                    <th>Empresa</th>
                                    <th>Região</th>
                                    <th class="text-center">Competência</th>
                                    <th>Detalhes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historicos as $h)
                                    <tr>
                                        <td class="text-nowrap small">
                                            <i class="fa-regular fa-clock text-muted me-1"></i>
                                            {{ $h->created_at ? $h->created_at->format('d/m/Y, H:i:s') : 'N/A' }}
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $h->user->name ?? 'Sistema/Desconhecido' }}</div>
                                            <div class="text-muted small">{{ $h->user->username ?? '' }}</div>
                                        </td>
                                        <td>
                                            @php
                                                $badgeClass = match($h->acao) {
                                                    'marcou_baixa_ok' => 'bg-success',
                                                    'desmarcou_baixa' => 'bg-danger',
                                                    'marcou_lista_ok' => 'bg-primary',
                                                    'desmarcou_lista' => 'bg-warning text-dark',
                                                    'marcou_pago' => 'bg-info text-dark',
                                                    default => 'bg-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }} rounded-pill px-2 py-1 shadow-sm">
                                                {{ $acoesDisponiveis[$h->acao] ?? $h->acao }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($h->socioFolha && $h->socioFolha->empresa)
                                                <div class="fw-semibold text-dark text-truncate" style="max-width: 220px;" title="{{ $h->socioFolha->empresa->razao_social }}">
                                                    {{ $h->socioFolha->empresa->razao_social }}
                                                </div>
                                                <div class="text-muted small">
                                                    CNPJ: {{ $h->socioFolha->empresa->cnpj ?? $h->socioFolha->empresa->empresa_erp ?? 'N/A' }}
                                                </div>
                                            @else
                                                <span class="text-muted small">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ $h->socioFolha->regiao->nome ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="text-center small fw-semibold">
                                            @if($h->socioFolha)
                                                {{ str_pad((string)$h->socioFolha->mes, 2, '0', STR_PAD_LEFT) }}/{{ $h->socioFolha->ano }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="small text-muted">
                                            {{ $h->detalhes ?? '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-folder-open fs-2 text-secondary mb-3 d-block"></i>
                                            <h6 class="fw-semibold">Nenhum registro de histórico encontrado.</h6>
                                            <p class="small mb-0">Tente ajustar os critérios de pesquisa nos filtros acima.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($historicos->hasPages())
                    <div class="card-footer bg-white d-flex justify-content-center py-3">
                        {{ $historicos->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
