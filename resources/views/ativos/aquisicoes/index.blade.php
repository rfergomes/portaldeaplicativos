@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fa-solid fa-file-invoice-dollar me-2 text-primary"></i>Entrada de Equipamentos
            </h1>
            <p class="text-muted">Registro de compras de ativos e cadastro em lote a partir de notas fiscais ou transferências físicas.</p>
        </div>
        @can('ativos.criar')
        <div class="col-md-4 text-end">
            <a href="{{ route('ativos.aquisicoes.create') }}" class="btn btn-primary shadow-sm">
                <i class="fa-solid fa-plus me-2"></i>Nova Entrada (Aquisição)
            </a>
        </div>
        @endcan
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">ID / Data</th>
                            <th>Origem</th>
                            <th>Nº Doc. fiscal</th>
                            <th>Chave Acesso</th>
                            <th>Equipamentos (Und.)</th>
                            <th>Valor Total</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($aquisicoes as $aq)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold">#AQ_{{ $aq->id }}</div>
                                <div class="small text-muted">{{ $aq->data_aquisicao?->format('d/m/Y') ?? 'S/ Data' }}</div>
                            </td>
                            <td>
                                <div><i class="fa-solid fa-truck-field text-muted me-1"></i> {{ $aq->fornecedor->nome ?? 'S/ Fornecedor' }}</div>
                                @if($aq->marketplace)
                                    <div class="small mt-1"><i class="fa-solid fa-store text-muted me-1"></i> Via {{ $aq->marketplace->nome }}</div>
                                @endif
                            </td>
                            <td>
                                @if($aq->numero_nf)
                                    <span class="badge bg-secondary-subtle text-secondary border">{{ $aq->numero_nf }}</span>
                                @else
                                    <span class="text-muted small">Sem Nota</span>
                                @endif
                            </td>
                            <td>
                                @if($aq->chave_acesso)
                                    <div class="small font-monospace text-truncate" style="max-width: 150px;" title="{{ $aq->chave_acesso }}">{{ $aq->chave_acesso }}</div>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary rounded-pill px-3">{{ $aq->equipamentos_count }} itens</span>
                            </td>
                            <td>
                                @if($aq->valor_total)
                                    R$ {{ number_format($aq->valor_total, 2, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('ativos.aquisicoes.show', $aq->id) }}" class="btn btn-sm btn-white border text-primary" title="Visualizar Detalhes">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                @can('ativos.excluir')
                                <form action="{{ route('ativos.aquisicoes.destroy', $aq->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-white border text-danger" onclick="return confirm('ATENÇÃO: Isso excluirá permanentemente a entrada E TODOS {{ $aq->equipamentos_count }} equipamentos vinculados a ela se não houver histórico de cessão. Continuar?')" title="Excluir Entrada e Itens">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Nenhuma aquisição registrada até o momento.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
