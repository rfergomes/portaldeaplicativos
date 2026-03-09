@extends('layouts.app')

@section('title', 'Dashboard - Portal de Aplicativos')

@push('styles')
    <style>
        .small-box .small-box-icon {
            position: absolute;
            right: 15px;
            top: 5px;
            z-index: 0;
            font-size: 60px;
            color: rgba(0, 0, 0, 0.12);
            transition: transform 0.3s ease;
        }

        .small-box:hover .small-box-icon {
            transform: scale(1.1);
        }

        .small-box .inner {
            z-index: 10;
            position: relative;
            padding: 10px 15px;
        }

        .metric-value {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0;
            white-space: nowrap;
        }

        .metric-label {
            font-size: 1rem;
            opacity: 0.9;
            font-weight: 400;
            margin-bottom: 5px;
        }

        .small-box-footer {
            font-size: 0.85rem;
            padding: 3px 0;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 0 0 0.375rem 0.375rem;
        }

        .small-box-footer:hover {
            background: rgba(0, 0, 0, 0.15);
        }

        .alert-card {
            border-left: 4px solid #dc3545;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
    </style>
@endpush

@section('content')
    <!-- KPI Row -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary text-white shadow-sm border-0">
                <div class="inner">
                    <h3 class="metric-value">{{ $totalEventosMes }}</h3>
                    <p class="metric-label">Eventos no Mês</p>
                </div>
                <div class="small-box-icon"><i class="fa-solid fa-calendar-star"></i></div>
                <a href="{{ route('eventos.index') }}"
                    class="small-box-footer link-light text-decoration-none d-block text-center">
                    Ver Eventos <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success text-white shadow-sm border-0">
                <div class="inner">
                    <h3 class="metric-value">{{ $totalEmpresas }}</h3>
                    <p class="metric-label">Empresas Cadastradas</p>
                </div>
                <div class="small-box-icon"><i class="fa-solid fa-building"></i></div>
                <a href="{{ url('/empresas') }}"
                    class="small-box-footer link-light text-decoration-none d-block text-center">
                    Ver Empresas <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning text-dark shadow-sm border-0">
                <div class="inner">
                    <h3 class="metric-value">{{ $totalProtocolosMes }}</h3>
                    <p class="metric-label">Protocolos no Mês</p>
                </div>
                <div class="small-box-icon"><i class="fa-solid fa-file-invoice"></i></div>
                <a href="{{ route('protocolos.index') }}"
                    class="small-box-footer link-dark text-decoration-none d-block text-center">
                    Ver Protocolos <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger text-white shadow-sm border-0">
                <div class="inner">
                    <h3 class="metric-value">{{ $reservasPendentes }}</h3>
                    <p class="metric-label">Reservas Pendentes</p>
                </div>
                <div class="small-box-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <a href="{{ route('agenda.reservas.index') }}"
                    class="small-box-footer link-light text-decoration-none d-block text-center">
                    Ver Reservas <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row g-4">
        <!-- Chart Column -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark">Fluxo de Protocolos (6 Meses)</h5>
                    <div class="card-tools ms-auto">
                        <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"><i
                                class="fa-solid fa-minus"></i></button>
                        <button type="button" class="btn btn-tool" data-lte-toggle="card-remove"><i
                                class="fa-solid fa-xmark"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="protocolChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts/Info Column -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-danger">Limites de Acertos Vencidos</h5>
                    <div class="card-tools ms-auto">
                        <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"><i
                                class="fa-solid fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($alertasVencidos as $reserva)
                            <div
                                class="list-group-item border-0 border-start border-4 border-danger mb-2 mx-2 rounded shadow-sm">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1 fw-bold">{{ $reserva->hospede->nome ?? 'Hóspede' }}</h6>
                                    <small class="text-danger fw-bold">Vencido!</small>
                                </div>
                                <p class="mb-1 small text-muted">{{ $reserva->colonia->nome }}
                                    ({{ $reserva->acomodacao->identificador ?? 'S/A' }})</p>
                                <small>Limite: {{ $reserva->periodo->data_limite_pagamento->format('d/m/Y') }}</small>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted">
                                <i class="fa-solid fa-check-circle fa-2x mb-2 text-success"></i>
                                <p class="mb-0 small">Nenhum limite de acerto vencido no momento.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lower Row: Charts & Tables -->
    <div class="row g-4 mt-2">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark">Reservas por Colônia</h5>
                    <div class="card-tools ms-auto">
                        <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"><i
                                class="fa-solid fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body overflow-hidden">
                    @if($reservasPorColonia->count() > 0)
                        <div class="chart-container">
                            <canvas id="reservationDonutChart"></canvas>
                        </div>
                    @else
                        <div class="p-5 text-center text-muted">
                            <i class="fa-solid fa-chart-pie fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">Aguardando dados de reservas para gerar o gráfico.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark">Protocolos Recentes</h5>
                    <div class="card-tools ms-auto">
                        <a href="{{ route('protocolos.index') }}" class="btn btn-sm btn-outline-primary me-2">Ver Todos</a>
                        <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse"><i
                                class="fa-solid fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 px-4">Tipo</th>
                                    <th class="border-0">Empresa</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0 text-end px-4">Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($protocolosRecentes as $prot)
                                    <tr>
                                        <td class="px-4">
                                            @if($prot->tipo)
                                                <span class="badge text-bg-{{ $prot->tipo->cor }} rounded-pill shadow-sm px-2">
                                                    <i class="{{ $prot->tipo->icone }} me-1"></i>{{ $prot->tipo->nome }}
                                                </span>
                                            @else
                                                <span class="badge text-bg-secondary rounded-pill shadow-sm px-2">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $prot->empresa->nome_curto ?? '—' }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'sucesso' => 'success',
                                                    'concluido' => 'success',
                                                    'enviado' => 'primary',
                                                    'pendente' => 'secondary',
                                                    'falha' => 'danger',
                                                ];
                                                $statusColor = $statusColors[$prot->status] ?? 'secondary';
                                                $statusLabel = $prot->status == 'concluido' ? 'Sucesso' : ucfirst($prot->status);
                                            @endphp
                                            <span class="badge bg-{{ $statusColor }} rounded-pill shadow-sm px-2">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td class="text-end px-4 small">{{ $prot->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Config Gráfico de Protocolos
            const protCtx = document.getElementById('protocolChart').getContext('2d');
            new Chart(protCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($protocolosGrafico->pluck('mes_ano')) !!},
                    datasets: [{
                        label: 'Protocolos Enviados',
                        data: {!! json_encode($protocolosGrafico->pluck('total')) !!},
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: '#2563eb'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { display: false } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // Config Gráfico de Reservas
            const resCtx = document.getElementById('reservationDonutChart').getContext('2d');
            new Chart(resCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($reservasPorColonia->pluck('nome')) !!},
                    datasets: [{
                        data: {!! json_encode($reservasPorColonia->pluck('total')) !!},
                        backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#6366f1'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                    },
                    cutout: '70%'
                }
            });
        });
    </script>
@endpush