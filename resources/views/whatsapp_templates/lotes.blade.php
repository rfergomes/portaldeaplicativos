@extends('layouts.app')

@section('title', 'Histórico de Lotes - WhatsApp')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold">
                <i class="fa-solid fa-paper-plane text-primary me-2"></i>Histórico de Lotes - WhatsApp
            </h1>
            <p class="text-muted mb-0">Rastreabilidade, status de envio e auditoria de mensagens em massa</p>
        </div>
        <div>
            <a href="{{ route('whatsapp-templates.index') }}" class="btn btn-outline-secondary">
                <i class="fa-brands fa-whatsapp text-success me-1"></i>Gerenciar Templates
            </a>
        </div>
    </div>

    <!-- Filtros de Lotes -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('whatsapp-lotes.index') }}" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <select name="modulo" class="form-select">
                        <option value="">Todos os Módulos</option>
                        <option value="caixa" {{ request('modulo') === 'caixa' ? 'selected' : '' }}>Sócio Caixa</option>
                        <option value="folha" {{ request('modulo') === 'folha' ? 'selected' : '' }}>Sócio Folha</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Todos os Status</option>
                        <option value="concluido" {{ request('status') === 'concluido' ? 'selected' : '' }}>Concluído</option>
                        <option value="processando" {{ request('status') === 'processando' ? 'selected' : '' }}>Processando</option>
                        <option value="pendente" {{ request('status') === 'pendente' ? 'selected' : '' }}>Pendente</option>
                        <option value="erro_parcial" {{ request('status') === 'erro_parcial' ? 'selected' : '' }}>Com Falhas</option>
                        <option value="cancelado" {{ request('status') === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter me-1"></i>Filtrar</button>
                    <a href="{{ route('whatsapp-lotes.index') }}" class="btn btn-light border"><i class="fas fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Lotes -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 80px;"># Lote</th>
                            <th style="width: 140px;">Data / Hora</th>
                            <th style="width: 120px;">Módulo</th>
                            <th>Template</th>
                            <th>Operador</th>
                            <th class="text-center" style="width: 130px;">Destinatários</th>
                            <th style="width: 180px;">Progresso</th>
                            <th class="text-center" style="width: 130px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lotes as $lote)
                            <tr>
                                <td class="ps-3 fw-bold text-dark font-monospace">#{{ $lote->id }}</td>
                                <td>
                                    <span class="d-block text-dark fw-semibold">{{ $lote->created_at->format('d/m/Y') }}</span>
                                    <small class="text-muted">{{ $lote->created_at->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    @if($lote->modulo === 'caixa')
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                            <i class="fas fa-cash-register me-1"></i>Caixa
                                        </span>
                                    @else
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1">
                                            <i class="fas fa-file-invoice-dollar me-1"></i>Folha
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">{{ $lote->template?->nome ?? 'N/D' }}</span>
                                    <small class="d-block text-muted text-truncate" style="max-width: 280px;">{{ $lote->template?->descricao }}</small>
                                </td>
                                <td>
                                    <span class="text-secondary small">{{ $lote->user?->name ?? 'Sistema' }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold">{{ $lote->total_destinatarios }}</div>
                                    <small class="text-muted">
                                        <span class="text-success">{{ $lote->total_enviados }} env.</span>
                                        @if($lote->total_falhas > 0)
                                            / <span class="text-danger">{{ $lote->total_falhas }} err.</span>
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    @php $pct = $lote->percentualConcluido(); @endphp
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar {{ $lote->status === 'erro_parcial' ? 'bg-warning' : ($lote->status === 'cancelado' ? 'bg-danger' : 'bg-success') }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $pct }}%;" 
                                                 aria-valuenow="{{ $pct }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100"></div>
                                        </div>
                                        <small class="text-muted fw-bold">{{ $pct }}%</small>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @switch($lote->status)
                                        @case('concluido')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="fas fa-check-circle me-1"></i>Concluído
                                            </span>
                                            @break
                                        @case('processando')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                                                <span class="spinner-border spinner-border-sm me-1" style="width: 0.8rem; height: 0.8rem;"></span>Processando
                                            </span>
                                            @break
                                        @case('pendente')
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">
                                                <i class="fas fa-clock me-1"></i>Pendente
                                            </span>
                                            @break
                                        @case('erro_parcial')
                                            <span class="badge bg-warning-subtle text-danger border border-danger-subtle px-2 py-1">
                                                <i class="fas fa-exclamation-triangle me-1"></i>Com Falhas
                                            </span>
                                            @break
                                        @case('cancelado')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                <i class="fas fa-ban me-1"></i>Cancelado
                                            </span>
                                            @break
                                    @endswitch
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-paper-plane fs-1 text-muted d-block mb-2"></i>
                                    Nenhum lote de WhatsApp registrado ainda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($lotes->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $lotes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
