@if(isset($macroData))
<!-- FASE 1: VISÃO MACRO CEO -->
<h5 class="mb-3 fw-bold text-primary border-bottom pb-2 mt-4"><i class="fa-solid fa-chart-line me-2"></i>Visão Macro Financeira e Operacional</h5>
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success text-white shadow-sm border-0">
            <div class="inner">
                <h3 class="metric-value">R$ {{ number_format($macroData['receitas']['total'], 2, ',', '.') }}</h3>
                <p class="metric-label">Receitas Recebidas (Mês)</p>
            </div>
            <div class="small-box-icon"><i class="fa-solid fa-sack-dollar"></i></div>
            <div class="small-box-footer d-flex justify-content-around">
                <span>Caixa: R$ {{ number_format($macroData['receitas']['caixa'], 0, ',', '.') }}</span>
                <span>Folha: R$ {{ number_format($macroData['receitas']['folha'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info text-white shadow-sm border-0">
            <div class="inner">
                <h3 class="metric-value">{{ $macroData['vazao_demandas']['saldo'] > 0 ? '+' : '' }}{{ $macroData['vazao_demandas']['saldo'] }}</h3>
                <p class="metric-label">Saldo de Demandas (Semana)</p>
            </div>
            <div class="small-box-icon"><i class="fa-solid fa-scale-balanced"></i></div>
            <div class="small-box-footer d-flex justify-content-around">
                <span>Criadas: {{ $macroData['vazao_demandas']['criadas'] }}</span>
                <span>Resolvidas: {{ $macroData['vazao_demandas']['resolvidas'] }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                <h5 class="card-title mb-0 fw-bold text-dark">Tendência de Volume Geral (12 Meses)</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 250px;">
                    <canvas id="tendenciaMacroChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                <h5 class="card-title mb-0 fw-bold text-dark">Carga por Módulo (Hoje)</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 250px;">
                    <canvas id="distribuicaoMacroChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if(isset($produtividadeData) || isset($burndownData))
<!-- FASE 2: PRODUTIVIDADE E ENTREGAS -->
<h5 class="mb-3 fw-bold text-secondary border-bottom pb-2 mt-4"><i class="fa-solid fa-users-gear me-2"></i>Produtividade e Entregas</h5>

<div class="row g-4 mb-4">
    @if(isset($burndownData))
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                <h5 class="card-title mb-0 fw-bold text-dark">Seu Burn-down (Hoje)</h5>
            </div>
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="display-4 fw-bold text-primary mb-2">{{ $burndownData['entregue'] }} / {{ $burndownData['demandado'] }}</div>
                <p class="text-muted">Tarefas Entregues hoje</p>
                
                <div class="progress mt-3" style="height: 20px;">
                    @php
                        $pct = $burndownData['demandado'] > 0 ? ($burndownData['entregue'] / $burndownData['demandado']) * 100 : 0;
                    @endphp
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">{{ round($pct) }}%</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($produtividadeData) && count($produtividadeData['extrato']) > 0)
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                <h5 class="card-title mb-0 fw-bold text-dark">Ranking de Entregas (Mês)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 px-4">Usuário</th>
                                <th class="border-0 text-center">Demandas Resolvidas</th>
                                <th class="border-0 text-center">Protocolos Enviados</th>
                                <th class="border-0 text-end px-4">Total Entregas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($produtividadeData['extrato'], 0, 5) as $idx => $prod)
                                <tr>
                                    <td class="px-4 fw-bold">
                                        @if($idx == 0) 🥇 @elseif($idx == 1) 🥈 @elseif($idx == 2) 🥉 @else <span class="ms-4"></span> @endif
                                        {{ $prod['nome'] }}
                                    </td>
                                    <td class="text-center text-primary fw-bold">{{ $prod['demandas_resolvidas'] }}</td>
                                    <td class="text-center text-info fw-bold">{{ $prod['protocolos_enviados'] }}</td>
                                    <td class="text-end px-4 fw-bold fs-5 text-success">{{ $prod['total_entregas'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(isset($macroData))
            // Gráfico Tendência Macro
            const tendCtx = document.getElementById('tendenciaMacroChart').getContext('2d');
            new Chart(tendCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($macroData['tendencia']['labels']) !!},
                    datasets: [{
                        label: 'Volume de Serviço',
                        data: {!! json_encode($macroData['tendencia']['data']) !!},
                        backgroundColor: '#6366f1',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } }
                }
            });

            // Gráfico Distribuição Macro
            const distCtx = document.getElementById('distribuicaoMacroChart').getContext('2d');
            new Chart(distCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($macroData['distribuicao']['labels']) !!},
                    datasets: [{
                        data: {!! json_encode($macroData['distribuicao']['data']) !!},
                        backgroundColor: ['#ef4444', '#f59e0b', '#10b981'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    cutout: '60%'
                }
            });
        @endif
    });
</script>
@endpush
