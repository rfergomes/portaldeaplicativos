@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fa-solid fa-laptop-code me-2 text-primary"></i>Controle de Equipamentos
            </h1>
            <p class="text-muted">Gerencie o inventário de hardware e dispositivos da empresa.</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('ativos.equipamentos.create') }}" class="btn btn-primary shadow-sm">
                <i class="fa-solid fa-plus me-2"></i>Novo Equipamento
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body">
            <form action="{{ route('ativos.equipamentos.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Identificador / Descrição</label>
                    <input type="text" name="identificador" class="form-control shadow-none" placeholder="Ex: NT-001..." value="{{ request('identificador') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" class="form-select shadow-none">
                        <option value="">Todos os Status</option>
                        <option value="disponivel" {{ request('status') == 'disponivel' ? 'selected' : '' }}>Disponível</option>
                        <option value="em_uso" {{ request('status') == 'em_uso' ? 'selected' : '' }}>Em Uso</option>
                        <option value="manutencao" {{ request('status') == 'manutencao' ? 'selected' : '' }}>Manutenção</option>
                        <option value="baixado" {{ request('status') == 'baixado' ? 'selected' : '' }}>Baixado</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-dark w-100">
                        <i class="fa-solid fa-magnifying-glass me-2"></i>Filtrar
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('ativos.equipamentos.index') }}" class="btn btn-outline-secondary w-100">
                        Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Equipamentos -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">ID / Identificador</th>
                            <th>Descrição</th>
                            <th>Modelo / Série</th>
                            <th>Status</th>
                            <th>Localização</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($equipamentos as $equipamento)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold text-primary">{{ $equipamento->identificador }}</span>
                                <div class="small text-muted">ID: #{{ $equipamento->id }}</div>
                            </td>
                            <td>
                                <div>{{ $equipamento->descricao }}</div>
                                <div class="small text-muted">{{ $equipamento->fabricante->nome ?? 'S/ Fabricante' }}</div>
                            </td>
                            <td>
                                <div class="badge bg-light text-dark border">{{ $equipamento->modelo ?? '-' }}</div>
                                <div class="small text-muted">SN: {{ $equipamento->numero_serie ?? '-' }}</div>
                            </td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'disponivel' => 'bg-success-subtle text-success border-success',
                                        'em_uso' => 'bg-primary-subtle text-primary border-primary',
                                        'manutencao' => 'bg-warning-subtle text-warning border-warning',
                                        'baixado' => 'bg-danger-subtle text-danger border-danger',
                                    ];
                                    $class = $statusClasses[$equipamento->status] ?? 'bg-secondary';
                                @endphp
                                <span class="badge border {{ $class }} text-uppercase" style="font-size: 0.7rem;">
                                    {{ str_replace('_', ' ', $equipamento->status) }}
                                </span>
                            </td>
                            <td>
                                <i class="fa-solid fa-location-dot text-muted me-1"></i>
                                <span class="small">{{ $equipamento->localizacao_atual }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm">
                                    <a href="{{ route('ativos.equipamentos.show', $equipamento) }}" class="btn btn-sm btn-white border" title="Detalhes">
                                        <i class="fa-solid fa-eye text-primary"></i>
                                    </a>
                                    <a href="{{ route('ativos.equipamentos.edit', $equipamento) }}" class="btn btn-sm btn-white border" title="Editar">
                                        <i class="fa-solid fa-pen-to-square text-dark"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-white border" title="Nova Movimentação" data-bs-toggle="modal" data-bs-target="#modalMovimentacao-{{ $equipamento->id }}">
                                        <i class="fa-solid fa-right-left text-success"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-inbox fa-3x mb-3 opacity-25"></i>
                                <p>Nenhum equipamento encontrado com os filtros aplicados.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($equipamentos->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $equipamentos->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
