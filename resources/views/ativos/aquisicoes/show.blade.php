@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fa-solid fa-file-invoice me-2 text-primary"></i>Detalhes da Aquisição #AQ_{{ $aquisicao->id }}
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('ativos.aquisicoes.index') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Detalhes da Compra -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="m-0 fw-bold text-secondary"><i class="fa-solid fa-circle-info me-2"></i>Inf. da Compra/Nota</h5>
                </div>
                <div class="card-body p-4">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-bold">Data:</span>
                            <span class="fw-bold text-dark">{{ $aquisicao->data_aquisicao?->format('d/m/Y') ?? '-' }}</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-bold">Fornecedor Emissor:</span>
                            <span class="text-dark">{{ $aquisicao->fornecedor->nome ?? 'Não informado' }}</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-bold">Marketplace / Plataforma:</span>
                            <span class="text-dark">{{ $aquisicao->marketplace->nome ?? '-' }}</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-bold">Número da Nota:</span>
                            <span class="badge bg-secondary-subtle text-secondary">{{ $aquisicao->numero_nf ?? 'Sem Nota' }}</span>
                        </li>
                        <li class="list-group-item px-0">
                            <div class="text-muted fw-bold mb-1">Chave de Acesso:</div>
                            <div class="font-monospace small bg-light p-2 border rounded text-break">{{ $aquisicao->chave_acesso ?? 'Não informada' }}</div>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-bold">Valor do Frete (R$):</span>
                            <span class="text-dark">{{ $aquisicao->valor_frete ? 'R$ ' . number_format($aquisicao->valor_frete, 2, ',', '.') : '-' }}</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <span class="text-muted fw-bold">Valor Total (R$):</span>
                            <span class="text-success fw-bold">{{ $aquisicao->valor_total ? 'R$ ' . number_format($aquisicao->valor_total, 2, ',', '.') : '-' }}</span>
                        </li>
                    </ul>
                    @if($aquisicao->observacao)
                        <div class="mt-4 p-3 bg-light rounded border">
                            <span class="text-muted fw-bold d-block mb-1">Observações:</span>
                            <p class="small m-0 text-dark">{{ $aquisicao->observacao }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Itens / Equipamentos Gerados -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="m-0 fw-bold text-primary"><i class="fa-solid fa-boxes-stacked me-2"></i>Equipamentos Gerados Automaticamente ({{ $aquisicao->equipamentos->count() }})</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Código (ID)</th>
                                    <th>Descrição / Modelo</th>
                                    <th>Fabricante</th>
                                    <th>Valor Unit.</th>
                                    <th>Status Atual</th>
                                    <th class="text-end pe-4">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($aquisicao->equipamentos as $equip)
                                <tr>
                                    <td class="ps-4"><span class="badge text-bg-light border shadow-sm px-2">#EQ_{{ $equip->id }}</span></td>
                                    <td>
                                        <div class="fw-bold">{{ $equip->descricao }}</div>
                                        <div class="small text-muted">{{ $equip->modelo ?? '-' }}</div>
                                    </td>
                                    <td>{{ $equip->fabricante->nome ?? '-' }}</td>
                                    <td class="font-monospace text-success">R$ {{ number_format($equip->valor_item, 2, ',', '.') }}</td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success border rounded-pill px-2 py-1">
                                            {{ ucfirst($equip->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('ativos.equipamentos.show', $equip->id) }}" class="btn btn-sm btn-white border" title="Acessar Ficha do Equipamento">
                                            <i class="fa-solid fa-up-right-from-square"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light py-3 text-muted small">
                    <i class="fa-solid fa-info-circle me-1"></i> Esses equipamentos já estão listados no Inventário Geral. Qualquer edição neles (ex: colocar Número de Série), deve ser feita através do módulo "Meu Patrimônio > Equipamentos".
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
