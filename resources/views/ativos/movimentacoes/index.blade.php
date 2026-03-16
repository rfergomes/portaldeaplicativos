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
                                <a href="{{ route('ativos.equipamentos.show', $mov->equipamento_id) }}" class="text-decoration-none fw-bold">
                                    {{ $mov->equipamento->identificador }}
                                </a>
                                <div class="x-small text-muted">{{ $mov->equipamento->descricao }}</div>
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
                                    <div class="x-small text-muted">{{ $mov->usuario->empresa->razao_social ?? 'S/ Empresa' }}</div>
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
                                <div class="small fw-bold">{{ $mov->responsavel->name }}</div>
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
            {{ $movimentacoes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
