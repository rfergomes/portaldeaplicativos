@extends('layouts.app')

@section('title', 'Licenças de Software')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Gestão de Licenças</h4>
            <p class="text-muted small mb-0">Controle de chaves, seats e vencimentos de softwares corporativos.</p>
        </div>
        <div>
            <a href="{{ route('ativos.licencas.create_aquisicao') }}" class="btn btn-success shadow-sm me-2">
                <i class="fa-solid fa-file-invoice-dollar me-2"></i>Entrada por Nota (Itens)
            </a>
            <a href="{{ route('ativos.licencas.create') }}" class="btn btn-primary shadow-sm">
                <i class="fa-solid fa-plus me-1"></i> Nova Licença Solo
            </a>
        </div>
    </div>

    <!-- Cards Informativos -->
    <div class="row g-3 mb-4">
        <!-- Card 1 -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="text-xs fw-bold text-primary text-uppercase">Licenças Adquiridas</div>
                        <i class="fa-solid fa-key text-primary opacity-50 mt-1" style="font-size: 1.2rem;"></i>
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalLicencas }}</div>
                </div>
            </div>
        </div>
        <!-- Card 2 -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="text-xs fw-bold text-warning text-uppercase">Expirando (30 dias) / Vencidas</div>
                        <i class="fa-solid fa-bell text-warning opacity-50 mt-1" style="font-size: 1.2rem;"></i>
                    </div>
                    <div class="h5 mb-0 fw-bold {{ $licencasExpirando > 0 ? 'text-danger' : 'text-gray-800' }}">{{ $licencasExpirando }}</div>
                </div>
            </div>
        </div>
        <!-- Card 3 -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="text-xs fw-bold text-info text-uppercase">Utilização Total (Seats)</div>
                        <i class="fa-solid fa-users text-info opacity-50 mt-1" style="font-size: 1.2rem;"></i>
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $seatsEmUso }} / <span class="text-muted">{{ $totalSeats }}</span></div>
                </div>
            </div>
        </div>
        <!-- Card 4 -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="text-xs fw-bold text-success text-uppercase">Custo Total Atual</div>
                        <i class="fa-solid fa-dollar-sign text-success opacity-50 mt-1" style="font-size: 1.2rem;"></i>
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">R$ {{ number_format($custoSoftware, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('ativos.licencas.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0 shadow-none" placeholder="Buscar por software ou chave..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="fabricante_id" class="form-select shadow-none">
                        <option value="">Todos os Fabricantes</option>
                        @foreach($fabricantes as $fab)
                            <option value="{{ $fab->id }}" {{ request('fabricante_id') == $fab->id ? 'selected' : '' }}>{{ $fab->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="tipo_licenca" class="form-select shadow-none">
                        <option value="">Todos os Tipos</option>
                        <option value="vitalicia" {{ request('tipo_licenca') === 'vitalicia' ? 'selected' : '' }}>Vitalícia</option>
                        <option value="assinatura" {{ request('tipo_licenca') === 'assinatura' ? 'selected' : '' }}>Assinatura</option>
                    </select>
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary shadow-sm"><i class="fa-solid fa-filter me-2"></i>Filtrar</button>
                </div>
                @if(request()->anyFilled(['search', 'fabricante_id', 'tipo_licenca']))
                <div class="col-md-auto">
                    <a href="{{ route('ativos.licencas.index') }}" class="btn btn-light text-muted"><i class="fa-solid fa-xmark me-2"></i>Limpar</a>
                </div>
                @endif
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr style="font-size: 0.75rem;" class="text-muted text-uppercase">
                            <th class="ps-4">Software</th>
                            <th>Fabricante / Fornecedor</th>
                            <th>Tipo</th>
                            <th>NF / Compra</th>
                            <th>Expiração / Status</th>
                            <th>Utilização (Seats)</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($licencas as $licenca)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $licenca->nome }}</div>
                                <div class="small text-muted font-monospace">{{ $licenca->chave ?: 'Chave sob consulta' }}</div>
                            </td>
                            <td>
                                <div class="text-dark">{{ $licenca->fabricante->nome ?? 'N/D' }}</div>
                                <div class="small text-muted">{{ $licenca->fornecedor->nome ?? 'Sem fornecedor' }}</div>
                            </td>
                            <td>
                                @if($licenca->tipo_licenca == 'vitalicia')
                                    <span class="badge bg-info-subtle text-info">Vitalícia</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Assinatura</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-dark">{{ $licenca->numero_nf ?: 'S/NF' }}</div>
                                <div class="small text-muted">{{ $licenca->data_aquisicao ? $licenca->data_aquisicao->format('d/m/Y') : '-' }}</div>
                            </td>
                            <td>
                                @if($licenca->data_validade)
                                    @php $vencida = $licenca->data_validade < now(); @endphp
                                    <div class="small {{ $vencida ? 'text-danger fw-bold' : '' }}">
                                        {{ $licenca->data_validade->format('d/m/Y') }}
                                    </div>
                                    @if($vencida)
                                        <span class="badge bg-danger rounded-pill x-small">Vencida</span>
                                    @endif
                                @else
                                    <span class="text-muted small">Sem expiração</span>
                                @endif
                            </td>
                            <td>
                                <div class="progress mb-1" style="height: 6px; width: 100px;">
                                    @php $percent = ($licenca->equipamentos_count / $licenca->quantidade_seats) * 100; @endphp
                                    <div class="progress-bar {{ $percent > 90 ? 'bg-danger' : 'bg-primary' }}" 
                                         role="progressbar" 
                                         style="width: {{ min($percent, 100) }}%"></div>
                                </div>
                                <small class="text-muted">{{ $licenca->equipamentos_count }} / {{ $licenca->quantidade_seats }} usado(s)</small>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('ativos.licencas.edit', $licenca->id) }}" class="btn btn-link text-primary p-0 me-2">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    <form action="{{ route('ativos.licencas.destroy', $licenca->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0" onclick="return confirm('Excluir esta licença?')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Nenhuma licença cadastrada.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($licencas->hasPages())
                <div class="card-footer bg-white border-0 py-3 border-top">
                    {{ $licencas->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
