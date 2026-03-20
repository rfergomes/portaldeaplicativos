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
                        <div class="small text-muted fw-bold">
                            @if($equipamento->estacao)
                                <i class="fa-solid fa-desktop me-1"></i>ESTAÇÃO: {{ $equipamento->estacao->nome }}
                            @else
                                <i class="fa-solid fa-location-dot me-1"></i>LOCAL: {{ $equipamento->localizacao_atual }}
                            @endif
                        </div>
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

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-key me-2 text-primary"></i>Licenças de Software</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalVincularLicenca">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
                <div class="card-body pt-0">
                    <div class="list-group list-group-flush">
                        @forelse($equipamento->licencas as $lic)
                            <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold small">{{ $lic->nome }}</div>
                                    <div class="x-small text-muted">{{ $lic->chave ?? 'S/ Chave' }}</div>
                                </div>
                                <form action="{{ route('ativos.licencas.desvincular', [$lic->id, $equipamento->id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link text-danger p-0" onclick="return confirm('Desvincular licença?')">
                                        <i class="fa-solid fa-unlink small"></i>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="text-center py-3 text-muted x-small">Nenhuma licença vinculada.</div>
                        @endforelse
                    </div>
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
<div class="modal fade" id="modalNovaMovimentacao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('ativos.movimentacoes.store') }}" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <input type="hidden" name="equipamento_id" value="{{ $equipamento->id }}">
            <div class="modal-header bg-success text-white border-0 py-3">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-right-left me-2"></i>Registrar Movimentação
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-12">
                        <div class="form-floating">
                            <select name="tipo" class="form-select border-0 bg-light shadow-none" id="selectTipoMovimento" required>
                                <option value="">Selecione...</option>
                                <option value="cessao">Cessão de Uso (Longo Prazo)</option>
                                <option value="emprestimo">Empréstimo (Curto Prazo)</option>
                                <option value="devolucao" {{ $equipamento->status == 'em_uso' ? '' : 'disabled' }}>Devolução ao Estoque</option>
                                <option value="manutencao">Enviar para Manutenção</option>
                                <option value="transferencia">Transferência Interna</option>
                            </select>
                            <label for="selectTipoMovimento" class="text-muted small fw-bold text-uppercase">Tipo de Ação</label>
                        </div>
                    </div>

                    <div class="col-md-12 field-usuario" style="display:none;">
                        <div class="form-floating">
                            <select name="usuario_id" class="form-select border-0 bg-light shadow-none" id="new-mov-usuario">
                                <option value="">Selecione a pessoa...</option>
                                @foreach(\App\Models\AtivoUsuario::where('ativo', true)->orderBy('nome')->get() as $u)
                                    <option value="{{ $u->id }}">{{ $u->nome }} ({{ $u->empresa->razao_social ?? 'S/ Empresa' }})</option>
                                @endforeach
                            </select>
                            <label for="new-mov-usuario" class="text-muted small fw-bold text-uppercase">Cessionário / Responsável</label>
                        </div>
                    </div>

                    <div class="col-md-12 field-devolu-prev" style="display:none;">
                        <div class="form-floating">
                            <input type="date" name="data_previsao_devolucao" class="form-control border-0 bg-light shadow-none" id="new-mov-data">
                            <label for="new-mov-data" class="text-muted small fw-bold text-uppercase">Previsão de Devolução</label>
                        </div>
                    </div>

                    <div class="col-md-12 field-destino" style="display:none;">
                        <div class="form-floating">
                            <input type="text" name="destino" class="form-control border-0 bg-light shadow-none" id="new-mov-destino" placeholder="Destino / Localização">
                            <label for="new-mov-destino" class="text-muted small fw-bold text-uppercase">Destino / Localização</label>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-floating">
                            <textarea name="observacao" class="form-control border-0 bg-light shadow-none" id="new-mov-obs" style="height: 100px" placeholder="Observações"></textarea>
                            <label for="new-mov-obs" class="text-muted small fw-bold text-uppercase">Observações / Motivo</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                    <i class="fa-solid fa-check me-2"></i>Confirmar Registro
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Vincular Licença -->
<div class="modal fade" id="modalVincularLicenca" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('ativos.licencas.vincular', $equipamento->id) }}" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Vincular Licença de Software</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Selecione o Software / Licença</label>
                    <select name="licenca_id" class="form-select" required>
                        <option value="">Selecione...</option>
                        @foreach(\App\Models\AtivoLicenca::orderBy('nome')->get() as $lic)
                            <option value="{{ $lic->id }}">{{ $lic->nome }} ({{ $lic->chave }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary px-4 fw-bold">Vincular Agora</button>
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
