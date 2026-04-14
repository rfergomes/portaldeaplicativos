@extends('layouts.app')

@section('title', 'Estatísticas · Mensalidades Sócio Caixa')

@section('content')
<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-bar me-2 text-primary"></i>Dashboard de Mensalidades</h2>
            <small class="text-muted">Visão geral e análise de desempenho do módulo Sócio Caixa</small>
        </div>
        <a href="{{ route('socios-caixa.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-list-ul me-2"></i>Ver Lista
        </a>
    </div>

    <!-- Info Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 text-white-50 small fw-bold text-uppercase">Total de Lançamentos</p>
                            <h2 class="fw-bold mb-0">{{ number_format($totalLancamentos) }}</h2>
                        </div>
                        <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="fas fa-database fa-lg"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top border-white border-opacity-25">
                        <small class="text-white-50">Registros importados no sistema</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 text-white-50 small fw-bold text-uppercase">Pagamentos Confirmados</p>
                            <h2 class="fw-bold mb-0">{{ number_format($totalPagos) }}</h2>
                        </div>
                        <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top border-white border-opacity-25">
                        @if($totalLancamentos > 0)
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 me-2" style="height:4px; background:rgba(255,255,255,0.3)">
                                <div class="progress-bar bg-white" style="width: {{ round($totalPagos/$totalLancamentos*100) }}%"></div>
                            </div>
                            <small class="text-white fw-bold">{{ round($totalPagos/$totalLancamentos*100) }}%</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 text-white-50 small fw-bold text-uppercase">Em Aberto</p>
                            <h2 class="fw-bold mb-0">{{ number_format($totalAbertos) }}</h2>
                        </div>
                        <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="fas fa-exclamation-circle fa-lg"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top border-white border-opacity-25">
                        @if($totalLancamentos > 0)
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 me-2" style="height:4px; background:rgba(255,255,255,0.3)">
                                <div class="progress-bar bg-white" style="width: {{ round($totalAbertos/$totalLancamentos*100) }}%"></div>
                            </div>
                            <small class="text-white fw-bold">{{ round($totalAbertos/$totalLancamentos*100) }}%</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);">
                <div class="card-body text-dark p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 small fw-bold text-uppercase opacity-75">Postergados (Snooze)</p>
                            <h2 class="fw-bold mb-0">{{ number_format($totalPostergados) }}</h2>
                        </div>
                        <div class="rounded-circle bg-dark bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-top border-dark border-opacity-10">
                        <small class="opacity-75">Aguardando retorno à lista</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row g-3 mb-4">

        <!-- Pagamentos por Mês -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Pagamentos Confirmados por Mês</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <canvas id="chartPagosMes" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Donut - Movimentações por Ação -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Ações Registradas</h5>
                </div>
                <div class="card-body px-4 pb-4 d-flex align-items-center justify-content-center">
                    <canvas id="chartAcoes" style="max-height: 250px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row g-3 mb-4">

        <!-- Ranking Operadores -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0"><i class="fas fa-trophy me-2 text-warning"></i>Ranking de Operadores</h5>
                    <small class="text-muted">Quem mais confirmou baixas</small>
                </div>
                <div class="card-body px-4 pb-4">
                    @forelse($rankingOperadores as $index => $op)
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3 fw-bold text-center" style="width:28px;">
                            @if($index === 0) <span class="text-warning fs-5">🥇</span>
                            @elseif($index === 1) <span class="fs-5">🥈</span>
                            @elseif($index === 2) <span class="fs-5">🥉</span>
                            @else <span class="text-muted small fw-bold">{{ $index + 1 }}°</span>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-bold">{{ $op->name }}</span>
                                <span class="text-muted">{{ $op->total }} baixas</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success rounded-pill" 
                                     style="width: {{ $rankingOperadores->max('total') > 0 ? round($op->total / $rankingOperadores->max('total') * 100) : 0 }}%">
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-5 opacity-50">
                        <i class="fas fa-user-slash fa-2x mb-2"></i>
                        <p class="mb-0">Nenhuma baixa registrada ainda.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Abertos por Tipo -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0"><i class="fas fa-chart-bar me-2 text-danger"></i>Em Aberto por Tipo de Sócio</h5>
                    <small class="text-muted">Distribuição dos inadimplentes por categoria</small>
                </div>
                <div class="card-body px-4 pb-4">
                    <canvas id="chartAbertosTipo" height="120"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimas Movimentações -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="fw-bold mb-0"><i class="fas fa-history me-2 text-primary"></i>Últimas Movimentações</h5>
            <small class="text-muted">As 15 ações mais recentes do sistema</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 border-0 py-3">Quando</th>
                            <th class="border-0">Operador</th>
                            <th class="border-0">Sócio</th>
                            <th class="border-0">Ref.</th>
                            <th class="border-0">Ação</th>
                            <th class="border-0 pe-4">Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ultimasMovimentacoes as $mov)
                        <tr>
                            <td class="ps-4">
                                <span class="text-muted" title="{{ $mov->created_at }}">{{ $mov->created_at->diffForHumans() }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width:26px;height:26px;font-size:0.7rem;">
                                        {{ strtoupper(substr($mov->user->name ?? '?', 0, 1)) }}
                                    </div>
                                    <span class="fw-semibold">{{ $mov->user->nickname ?? $mov->user->name ?? 'N/D' }}</span>
                                </div>
                            </td>
                            <td class="fw-bold text-truncate" style="max-width:160px;">{{ $mov->socio->nome ?? 'N/D' }}</td>
                            <td class="text-muted">
                                @if($mov->socio)
                                    {{ str_pad($mov->socio->mes, 2, '0', STR_PAD_LEFT) }}/{{ $mov->socio->ano }}
                                @endif
                            </td>
                            <td>
                                @php
                                    $badge = match($mov->acao) {
                                        'baixa' => 'bg-success',
                                        'estorno' => 'bg-danger',
                                        'postergar' => 'bg-warning text-dark',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $badge }} rounded-pill px-3">{{ strtoupper($mov->acao) }}</span>
                            </td>
                            <td class="pe-4 text-muted text-truncate" style="max-width:200px;">{{ $mov->observacao ?: '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">Nenhuma movimentação registrada.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
    Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
    Chart.defaults.color = '#6c757d';

    const mesesNomes = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
    const pagosPorMes = @json($pagosPorMes);
    
    // --- Gráfico: Pagamentos por Mês ---
    const ctxMes = document.getElementById('chartPagosMes').getContext('2d');
    new Chart(ctxMes, {
        type: 'bar',
        data: {
            labels: Object.keys(pagosPorMes).map(m => mesesNomes[parseInt(m)-1] ?? 'M'+m),
            datasets: [{
                label: 'Confirmados',
                data: Object.values(pagosPorMes),
                backgroundColor: 'rgba(102, 126, 234, 0.7)',
                borderColor: 'rgba(102, 126, 234, 1)',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });

    // --- Gráfico: Ações (Donut) ---
    const movAcoes = @json($movimentacoesPorAcao);
    const ctxAcoes = document.getElementById('chartAcoes').getContext('2d');
    new Chart(ctxAcoes, {
        type: 'doughnut',
        data: {
            labels: Object.keys(movAcoes).map(a => a.charAt(0).toUpperCase() + a.slice(1)),
            datasets: [{
                data: Object.values(movAcoes),
                backgroundColor: ['#11998e', '#f5576c', '#f7971e', '#667eea'],
                borderWidth: 3,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: {
                legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true } }
            }
        }
    });

    // --- Gráfico: Abertos por Tipo (Horizontal Bar) ---
    const abertosTipo = @json($abertosPorTipo);
    const ctxTipo = document.getElementById('chartAbertosTipo').getContext('2d');
    new Chart(ctxTipo, {
        type: 'bar',
        data: {
            labels: abertosTipo.map(t => t.tipo_socio),
            datasets: [{
                label: 'Em Aberto',
                data: abertosTipo.map(t => t.total),
                backgroundColor: 'rgba(245, 87, 108, 0.7)',
                borderColor: 'rgba(245, 87, 108, 1)',
                borderWidth: 2,
                borderRadius: 4,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { stepSize: 1 } },
                y: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });
</script>
@endpush
@endsection
