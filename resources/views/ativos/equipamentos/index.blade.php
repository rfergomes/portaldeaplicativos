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

                        <!-- Modal Nova Movimentação -->
                        <div class="modal fade" id="modalMovimentacao-{{ $equipamento->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="{{ route('ativos.movimentacoes.store') }}" method="POST" class="modal-content border-0 shadow-lg">
                                    @csrf
                                    <input type="hidden" name="equipamento_id" value="{{ $equipamento->id }}">
                                    <div class="modal-header bg-success text-white border-0 py-3">
                                        <h5 class="modal-title fw-bold">
                                            <i class="fa-solid fa-right-left me-2"></i>Mover: {{ $equipamento->identificador }}
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row g-4">
                                            <div class="col-md-12">
                                                <div class="form-floating">
                                                    <select name="tipo" class="form-select border-0 bg-light shadow-none select-tipo-movimento" required>
                                                        <option value="">Selecione...</option>
                                                        <option value="cessao">Cessão de Uso (Longo Prazo)</option>
                                                        <option value="emprestimo">Empréstimo (Curto Prazo)</option>
                                                        <option value="devolucao" {{ $equipamento->status == 'em_uso' ? '' : 'disabled' }}>Devolução ao Estoque</option>
                                                        <option value="manutencao">Enviar para Manutenção</option>
                                                        <option value="transferencia">Transferência Interna</option>
                                                    </select>
                                                    <label class="text-muted small fw-bold text-uppercase">Tipo de Ação</label>
                                                </div>
                                            </div>

                                            <div class="col-md-12 field-usuario" style="display:none;">
                                                <div class="form-floating">
                                                    <select name="usuario_id" class="form-select border-0 bg-light shadow-none">
                                                        <option value="">Selecione a pessoa...</option>
                                                        @foreach(\App\Models\AtivoUsuario::where('ativo', true)->orderBy('nome')->get() as $u)
                                                            <option value="{{ $u->id }}">{{ $u->nome }} ({{ $u->empresa->razao_social ?? 'S/ Empresa' }})</option>
                                                        @endforeach
                                                    </select>
                                                    <label class="text-muted small fw-bold text-uppercase">Cessionário / Responsável</label>
                                                </div>
                                            </div>

                                            <div class="col-md-12 field-devolu-prev" style="display:none;">
                                                <div class="form-floating">
                                                    <input type="date" name="data_previsao_devolucao" class="form-control border-0 bg-light shadow-none">
                                                    <label class="text-muted small fw-bold text-uppercase">Previsão de Devolução</label>
                                                </div>
                                            </div>

                                            <div class="col-md-12 field-destino" style="display:none;">
                                                <div class="form-floating">
                                                    <input type="text" name="destino" class="form-control border-0 bg-light shadow-none" placeholder="Destino / Localização">
                                                    <label class="text-muted small fw-bold text-uppercase">Destino / Localização</label>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-floating">
                                                    <textarea name="observacao" class="form-control border-0 bg-light shadow-none" style="height: 100px" placeholder="Observações"></textarea>
                                                    <label class="text-muted small fw-bold text-uppercase">Observações / Motivo</label>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectTipos = document.querySelectorAll('.select-tipo-movimento');
    
    selectTipos.forEach(select => {
        select.addEventListener('change', function() {
            const val = this.value;
            const modalBody = this.closest('.modal-body');
            
            const fieldUsuario = modalBody.querySelector('.field-usuario');
            const fieldDevolu = modalBody.querySelector('.field-devolu-prev');
            const fieldDestino = modalBody.querySelector('.field-destino');
            
            fieldUsuario.style.display = (val === 'cessao' || val === 'emprestimo') ? 'block' : 'none';
            fieldDevolu.style.display = (val === 'emprestimo') ? 'block' : 'none';
            fieldDestino.style.display = (val === 'transferencia' || val === 'manutencao') ? 'block' : 'none';
        });
    });
});
</script>
@endsection
