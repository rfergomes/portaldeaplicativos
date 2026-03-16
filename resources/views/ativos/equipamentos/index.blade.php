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
        @can('ativos.criar')
        <div class="col-md-4 text-end">
            <a href="{{ route('ativos.equipamentos.create') }}" class="btn btn-primary shadow-sm">
                <i class="fa-solid fa-plus me-2"></i>Novo Equipamento
            </a>
        </div>
        @endcan
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
                            <th class="ps-4">ID</th>
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
                                <span class="badge text-bg-light border shadow-sm px-2">#EQP_{{ $equipamento->id }}</span>
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
                                <span class="small">
                                    @if($equipamento->status === 'em_uso' && $equipamento->ultimaMovimentacao && $equipamento->ultimaMovimentacao->usuario)
                                        {{ $equipamento->ultimaMovimentacao->usuario->nome }}
                                    @else
                                        {{ $equipamento->localizacao_atual }}
                                    @endif
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm">
                                    <a href="{{ route('ativos.equipamentos.show', $equipamento) }}" class="btn btn-sm btn-white border" title="Detalhes">
                                        <i class="fa-solid fa-eye text-primary"></i>
                                    </a>
                                    @can('ativos.editar')
                                    <a href="{{ route('ativos.equipamentos.edit', $equipamento) }}" class="btn btn-sm btn-white border" title="Editar">
                                        <i class="fa-solid fa-pen-to-square text-dark"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-white border" title="Nova Movimentação" data-bs-toggle="modal" data-bs-target="#modalMovimentacao-{{ $equipamento->id }}">
                                        <i class="fa-solid fa-right-left text-success"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Nova Movimentação -->
                        <div class="modal fade" id="modalMovimentacao-{{ $equipamento->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg">
                                    <form action="{{ route('ativos.movimentacoes.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="equipamento_id" value="{{ $equipamento->id }}">
                                        <div class="modal-header bg-success text-white border-0 py-3">
                                            <h5 class="modal-title fw-bold">
                                                <i class="fa-solid fa-right-left me-2"></i>Mover: #{{ $equipamento->id }}
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                <div class="col-md-12">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Tipo de Ação</label>
                                                    <select name="tipo" class="form-select bg-light border-0 shadow-none select-tipo-movimento" required>
                                                        <option value="">Selecione...</option>
                                                        <option value="cessao" {{ $equipamento->status !== 'disponivel' ? 'disabled' : '' }}>Cessão de Uso (Longo Prazo)</option>
                                                        <option value="emprestimo" {{ $equipamento->status !== 'disponivel' ? 'disabled' : '' }}>Empréstimo (Curto Prazo)</option>
                                                        <option value="devolucao" {{ $equipamento->status === 'disponivel' ? 'disabled' : '' }}>Devolução ao Estoque</option>
                                                        <option value="manutencao" {{ $equipamento->status !== 'disponivel' ? 'disabled' : '' }}>Enviar para Manutenção</option>
                                                        <option value="transferencia" {{ $equipamento->status !== 'disponivel' ? 'disabled' : '' }}>Transferência Interna</option>
                                                    </select>
                                                </div>
    
                                                <div class="col-md-12 field-usuario" style="display:none;">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Cessionário / Responsável</label>
                                                    <select name="usuario_id" class="form-select bg-light border-0 shadow-none">
                                                        <option value="">Selecione a pessoa...</option>
                                                        @foreach(\App\Models\AtivoUsuario::where('ativo', true)->orderBy('nome')->get() as $u)
                                                            <option value="{{ $u->id }}">{{ $u->nome }} ({{ $u->empresa->razao_social ?? 'S/ Empresa' }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>
    
                                                <div class="col-md-12 field-devolu-prev" style="display:none;">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Previsão de Devolução</label>
                                                    <input type="date" name="data_previsao_devolucao" class="form-control bg-light border-0 shadow-none">
                                                </div>
    
                                                <div class="col-md-12 field-destino" style="display:none;">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Destino / Localização</label>
                                                    <input type="text" name="destino" class="form-control bg-light border-0 shadow-none" placeholder="Ex: Sala 02, CPD, Filial...">
                                                </div>
    
                                                <div class="col-md-12">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Observações / Motivo</label>
                                                    <textarea name="observacao" class="form-control bg-light border-0 shadow-none" rows="3" placeholder="Detalhes adicionais..."></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light border-0 py-3">
                                            <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-success px-4 shadow-sm fw-bold">
                                                <i class="fa-solid fa-check me-2"></i>Confirmar Registro
                                            </button>
                                        </div>
                                    </form>
                                </div>
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
    @if(session('success'))
        @if(session('cessao_id'))
            Swal.fire({
                title: 'Sucesso!',
                text: "{{ session('success') }}",
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-file-pdf me-1"></i> Gerar Termo de Cessão',
                cancelButtonText: 'Fechar',
                confirmButtonColor: '#0d6efd'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open("{{ route('ativos.cessoes.pdf', session('cessao_id')) }}", '_blank');
                }
            });
        @else
            Swal.fire('Sucesso!', "{{ session('success') }}", 'success');
        @endif
    @endif
});
</script>
@endsection
