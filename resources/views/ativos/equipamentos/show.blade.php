@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('ativos.equipamentos.index') }}" class="btn btn-white border shadow-sm me-3">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="flex-grow-1">
            <h1 class="h3 mb-0 text-gray-800">Detalhes do Ativo: {{ $equipamento->identificador }}</h1>
            <p class="text-muted small mb-0">{{ $equipamento->descricao }}</p>
        </div>
        <div class="text-end">
            <a href="{{ route('ativos.equipamentos.edit', $equipamento) }}" class="btn btn-dark shadow-sm">
                <i class="fa-solid fa-edit me-2"></i>Editar Equipamento
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Coluna da Esquerda: Infos Técnicas -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-circle-info me-2 text-primary"></i>Status Atual</h5>
                </div>
                <div class="card-body pt-0">
                    <div class="text-center py-4 bg-light rounded-3 mb-4">
                        @php
                            $statusClasses = [
                                'disponivel' => 'text-success',
                                'em_uso' => 'text-primary',
                                'manutencao' => 'text-warning',
                                'baixado' => 'text-danger',
                            ];
                            $color = $statusClasses[$equipamento->status] ?? 'text-secondary';
                        @endphp
                        <h2 class="fw-bold {{ $color }} mb-1 text-uppercase">{{ str_replace('_', ' ', $equipamento->status) }}</h2>
                        <div class="small text-muted fw-bold">LOCALIZAÇÃO: {{ $equipamento->localizacao_atual }}</div>
                    </div>

                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Fabricante</span>
                            <span class="fw-bold">{{ $equipamento->fabricante->nome ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Modelo</span>
                            <span class="fw-bold">{{ $equipamento->modelo ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Nº de Série</span>
                            <span class="fw-bold text-break">{{ $equipamento->numero_serie ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Fornecedor</span>
                            <span class="fw-bold text-end">{{ $equipamento->fornecedor->nome ?? '-' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-receipt me-2 text-primary"></i>Aquisição</h5>
                </div>
                <div class="card-body pt-0">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Data Compra</span>
                            <span class="fw-bold">{{ $equipamento->data_compra ? $equipamento->data_compra->format('d/m/Y') : '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Valor Unitário</span>
                            <span class="fw-bold">R$ {{ number_format($equipamento->valor_item, 2, ',', '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Nota Fiscal</span>
                            <span class="fw-bold">{{ $equipamento->valor_nota ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Garantia</span>
                            <span class="fw-bold">{{ $equipamento->garantia_meses ? $equipamento->garantia_meses . ' meses' : '-' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Coluna da Direita: Histórico e Ações -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Histórico de Movimentações</h5>
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalNovaMovimentacao">
                        <i class="fa-solid fa-plus me-1"></i>Registrar Ação
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Data</th>
                                    <th>Ação</th>
                                    <th>Pessoa Responsável</th>
                                    <th>Destino / Obs</th>
                                    <th class="text-end pe-4">Registrado por</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($equipamento->movimentacoes as $mov)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ $mov->data_movimentacao->format('d/m/Y') }}</div>
                                        <div class="x-small text-muted">{{ $mov->data_movimentacao->format('H:i') }} h</div>
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
                                            $badgeClass = $tipoClasses[$mov->tipo] ?? 'bg-light';
                                        @endphp
                                        <span class="badge {{ $badgeClass }} text-uppercase">{{ $mov->tipo }}</span>
                                    </td>
                                    <td>{{ $mov->usuario->nome ?? '-' }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $mov->destino }}</div>
                                        <div class="x-small text-muted text-truncate" style="max-width: 200px;">{{ $mov->observacao }}</div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="fw-bold">{{ $mov->responsavel->name }}</div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Ainda não há movimentações registradas para este ativo.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova Movimentação -->
<div class="modal fade" id="modalNovaMovimentacao" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('ativos.movimentacoes.store') }}" method="POST" class="modal-content text-start">
            @csrf
            <input type="hidden" name="equipamento_id" value="{{ $equipamento->id }}">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Registrar Movimentação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label small fw-bold">Tipo de Ação</label>
                        <select name="tipo" class="form-select shadow-none" required id="selectTipoMovimento">
                            <option value="">Selecione...</option>
                            <option value="cessao">Cessão de Uso (Longo Prazo)</option>
                            <option value="emprestimo">Empréstimo (Curto Prazo)</option>
                            <option value="devolucao" {{ $equipamento->status == 'em_uso' ? '' : 'disabled' }}>Devolução ao Estoque</option>
                            <option value="manutencao">Enviar para Manutenção</option>
                            <option value="transferencia">Transferência Interna</option>
                        </select>
                    </div>

                    <div class="col-md-12 field-usuario" style="display:none;">
                        <label class="form-label small fw-bold">Cessionário / Responsável</label>
                        <select name="usuario_id" class="form-select shadow-none">
                            <option value="">Selecione a pessoa...</option>
                            @foreach(\App\Models\AtivoUsuario::where('ativo', true)->orderBy('nome')->get() as $u)
                                <option value="{{ $u->id }}">{{ $u->nome }} ({{ $u->empresa->razao_social ?? 'S/ Empresa' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12 field-devolu-prev" style="display:none;">
                        <label class="form-label small fw-bold">Previsão de Devolução</label>
                        <input type="date" name="data_previsao_devolucao" class="form-control shadow-none">
                    </div>

                    <div class="col-md-12 field-destino" style="display:none;">
                        <label class="form-label small fw-bold">Destino / Localização</label>
                        <input type="text" name="destino" class="form-control shadow-none" placeholder="Ex: Laboratório X, Prédio B...">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label small fw-bold">Observações / Motivo</label>
                        <textarea name="observacao" class="form-control shadow-none" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary px-4">Confirmar Registro</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectTipo = document.getElementById('selectTipoMovimento');
    const fieldUsuario = document.querySelector('.field-usuario');
    const fieldDevolu = document.querySelector('.field-devolu-prev');
    const fieldDestino = document.querySelector('.field-destino');

    selectTipo.addEventListener('change', function() {
        const val = this.value;
        
        fieldUsuario.style.display = (val === 'cessao' || val === 'emprestimo') ? 'block' : 'none';
        fieldDevolu.style.display = (val === 'emprestimo') ? 'block' : 'none';
        fieldDestino.style.display = (val === 'transferencia' || val === 'manutencao') ? 'block' : 'none';
    });
});
</script>
@endsection
