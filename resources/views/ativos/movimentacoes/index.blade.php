@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fa-solid fa-right-left me-2 text-primary"></i>Histórico Geral de Movimentações
            </h1>
            <p class="text-muted">Acompanhe todas as entradas, saídas e transferências de ativos do sistema!</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('ativos.movimentacoes.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Equipamento</label>
                    <select name="equipamento_id" class="form-select form-select-sm select2">
                        <option value="">Todos</option>
                        @foreach($equipamentos as $eqp)
                            <option value="{{ $eqp->id }}" {{ request('equipamento_id') == $eqp->id ? 'selected' : '' }}>
                                [#EQP_{{ $eqp->id }}] {{ $eqp->descricao }} ({{ $eqp->modelo }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Cessionário</label>
                    <select name="usuario_id" class="form-select form-select-sm select2">
                        <option value="">Todos</option>
                        @foreach($usuarios as $user)
                            <option value="{{ $user->id }}" {{ request('usuario_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Tipo</label>
                    <select name="tipo" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="cessao" {{ request('tipo') == 'cessao' ? 'selected' : '' }}>Cessão</option>
                        <option value="emprestimo" {{ request('tipo') == 'emprestimo' ? 'selected' : '' }}>Empréstimo</option>
                        <option value="devolucao" {{ request('tipo') == 'devolucao' ? 'selected' : '' }}>Devolução</option>
                        <option value="manutencao" {{ request('tipo') == 'manutencao' ? 'selected' : '' }}>Manutenção</option>
                        <option value="transferencia" {{ request('tipo') == 'transferencia' ? 'selected' : '' }}>Transferência</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Destino / Local</label>
                    <input type="text" name="destino" class="form-control form-control-sm" value="{{ request('destino') }}" placeholder="Ex: Escritório">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Operador</label>
                    <select name="responsavel_id" class="form-select form-select-sm select2">
                        <option value="">Todos</option>
                        @foreach($operadores as $op)
                            <option value="{{ $op->id }}" {{ request('responsavel_id') == $op->id ? 'selected' : '' }}>
                                {{ $op->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Período</label>
                    <div class="input-group input-group-sm">
                        <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                        <span class="input-group-text">até</span>
                        <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim') }}">
                    </div>
                </div>
                <div class="col-md-9 d-flex align-items-end justify-content-end gap-2">
                    <a href="{{ route('ativos.movimentacoes.index') }}" class="btn btn-sm btn-light border">
                        <i class="fa-solid fa-eraser me-1"></i>Limpar
                    </a>
                    <button type="submit" class="btn btn-sm btn-primary px-4">
                        <i class="fa-solid fa-magnifying-glass me-1"></i>Filtrar
                    </button>
                    <button type="submit" name="export" value="pdf" class="btn btn-sm btn-outline-danger">
                        <i class="fa-solid fa-file-pdf me-1"></i>Exportar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Data / Hora</th>
                            <th>Equipamento</th>
                            <th>Tipo</th>
                            <th>Cessionário / Locatário</th>
                            <th>Destino / Local</th>
                            <th class="text-end pe-4">Operador por</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimentacoes as $mov)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold">{{ $mov->data_movimentacao->format('d/m/Y') }}</div>
                                <div class="small text-muted">{{ $mov->data_movimentacao->format('H:i') }}</div>
                            </td>
                            <td>
                                @if($mov->equipamento)
                                    <a href="{{ route('ativos.equipamentos.show', $mov->equipamento->id) }}" class="text-decoration-none fw-bold">
                                        <span class="badge text-bg-light border shadow-sm px-2">#EQP_{{ $mov->equipamento->id }}</span>
                                    </a>
                                    <div class="x-small text-muted">{{ $mov->equipamento->descricao }}</div>
                                @else
                                    <span class="text-muted small">Equipamento Removido</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $tipoClasses = [
                                        'cessao' => 'bg-primary text-white',
                                        'emprestimo' => 'bg-info text-dark',
                                        'devolucao' => 'bg-success text-white',
                                        'manutencao' => 'bg-warning text-dark',
                                        'transferencia' => 'bg-secondary text-white',
                                    ];
                                    $badgeClass = $tipoClasses[$mov->tipo] ?? 'bg-light text-dark';
                                @endphp
                                <span class="badge {{ $badgeClass }} text-uppercase" style="font-size: 0.65rem;">
                                    {{ $mov->tipo }}
                                </span>
                            </td>
                            <td>
                                @if($mov->usuario)
                                    <div>{{ $mov->usuario->nome }}</div>
                                    <div class="x-small text-muted">{{ optional($mov->usuario->empresa)->razao_social ?? 'S/ Empresa' }}</div>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td>
                                <div class="small fw-bold">{{ $mov->destino ?? '-' }}</div>
                                @if($mov->observacao)
                                    <div class="x-small text-muted text-truncate" style="max-width: 150px;" title="{{ $mov->observacao }}">
                                        {{ $mov->observacao }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="small fw-bold">{{ optional($mov->responsavel)->name ?? 'Sistema' }}</div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Nenhuma movimentação registrada no sistema.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($movimentacoes->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $movimentacoes->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
