@extends('layouts.app')

@section('title', 'Estações de Trabalho')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Inventário por Estações de Trabalho</h4>
            <p class="text-muted small mb-0">Gerencie os postos de trabalho e seus ativos vinculados por departamento.</p>
        </div>
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNovaEstacao">
            <i class="fa-solid fa-plus me-1"></i> Nova Estação
        </button>
    </div>

    <!-- Cards Informativos -->
    <div class="row g-3 mb-4">
        <!-- Card 1 -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="text-xs fw-bold text-primary text-uppercase">Total de Estações</div>
                        <i class="fa-solid fa-desktop text-primary opacity-50 mt-1" style="font-size: 1.2rem;"></i>
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalEstacoes }}</div>
                </div>
            </div>
        </div>
        <!-- Card 2 -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="text-xs fw-bold text-success text-uppercase">Estações Livres</div>
                        <i class="fa-solid fa-chair text-success opacity-50 mt-1" style="font-size: 1.2rem;"></i>
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $estacoesLivres }}</div>
                </div>
            </div>
        </div>
        <!-- Card 3 -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="text-xs fw-bold text-warning text-uppercase">Equipamentos Fixados</div>
                        <i class="fa-solid fa-plug circle text-warning opacity-50 mt-1" style="font-size: 1.2rem;"></i>
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $equipamentosAlocados }}</div>
                </div>
            </div>
        </div>
        <!-- Card 4 -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="text-xs fw-bold text-info text-uppercase">Setores Mapeados</div>
                        <i class="fa-solid fa-sitemap text-info opacity-50 mt-1" style="font-size: 1.2rem;"></i>
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalDepartamentos }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm border-0 mb-4 bg-white">
        <div class="card-body p-3">
            <form action="{{ route('ativos.estacoes.index') }}" method="GET" class="row align-items-end g-3" id="formFiltros">
                <div class="col-md-5">
                    <label class="form-label text-muted small fw-bold mb-1">Buscar Estação</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fa-solid fa-search text-muted"></i></span>
                        <input type="text" name="busca" class="form-control" placeholder="Nome, apelido ou descrição..." value="{{ request('busca') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-bold mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <option value="ativa" {{ request('status') == 'ativa' ? 'selected' : '' }}>Ativa (Com Equipamentos)</option>
                        <option value="vazia" {{ request('status') == 'vazia' ? 'selected' : '' }}>Vazia (Livre)</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill fw-bold">
                        <i class="fa-solid fa-filter me-1"></i>Filtrar
                    </button>
                    @if(request('busca') || request('status'))
                    <a href="{{ route('ativos.estacoes.index') }}" class="btn btn-sm btn-light border" title="Limpar Filtros"><i class="fa-solid fa-eraser"></i></a>
                    @endif
                    <button type="submit" formaction="{{ route('ativos.estacoes.pdf') }}" formtarget="_blank" class="btn btn-sm btn-danger flex-fill fw-bold" title="Gerar PDF com os filtros aplicados">
                        <i class="fa-solid fa-file-pdf me-1"></i>Gerar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Container Agrupado por Departamentos -->
    <div class="row">
        @forelse($departamentos as $depto)
        <div class="col-12 mb-5">
            <div class="d-flex align-items-center mb-3">
                <h5 class="h5 fw-bold mb-0 text-primary">
                    <i class="fa-solid fa-sitemap me-2"></i>{{ $depto->nome }}
                </h5>
                <span class="badge bg-secondary rounded-pill small ms-3 shadow-sm py-1 px-3">{{ $depto->estacoes->count() }} Estações</span>
                <hr class="flex-grow-1 ms-3 text-muted opacity-25">
            </div>

            @if($depto->estacoes->count() > 0)
            <div class="row g-4">
                @foreach($depto->estacoes as $estacao)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0 border-top border-4 {{ $estacao->equipamentos->count() > 0 ? 'border-primary' : 'border-warning' }} hover-shadow transition-all">
                        <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                            <span class="badge {{ $estacao->equipamentos->count() > 0 ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }}">
                                {{ $estacao->equipamentos->count() > 0 ? 'Ativa' : 'Vazia' }}
                            </span>
                            <span class="text-muted small fw-bold px-2 py-1 bg-light rounded"><i class="fa-solid fa-desktop me-1 text-secondary"></i> {{ $estacao->equipamentos->count() }} Equip.</span>
                        </div>
                        <div class="card-body pt-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 52px; height: 52px;">
                                        <i class="fa-solid {{ $estacao->equipamentos->count() > 0 ? 'fa-user-tie text-primary' : 'fa-chair text-secondary opacity-50' }} fs-4"></i>
                                    </div>
                                </div>
                                <div class="overflow-hidden">
                                    <h6 class="mb-1 fw-bold text-dark text-truncate" title="{{ $estacao->nome }}">{{ $estacao->nome }}</h6>
                                    <p class="text-muted small mb-0 text-truncate" title="{{ $estacao->descricao ?: 'Sem descrição' }}">{{ $estacao->descricao ?: 'Sem descrição' }}</p>
                                </div>
                            </div>
                            
                            <div class="bg-light p-2 rounded border border-light">
                                <div class="text-xs fw-bold text-muted text-uppercase mb-2"><i class="fa-solid fa-plug circle me-1"></i>Inventário</div>
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse($estacao->equipamentos as $equipa)
                                        <span class="badge bg-white text-dark border shadow-sm" title="{{ $equipa->modelo }}">
                                            <i class="fa-solid fa-laptop me-1 text-primary"></i>{{ $equipa->descricao }}
                                        </span>
                                    @empty
                                        <span class="text-muted small fst-italic">Nenhum ativo associado</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white py-3 border-top-0 d-flex justify-content-end gap-2">
                            <button class="btn btn-sm btn-light border fw-bold text-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEditEstacao{{ $estacao->id }}">
                                <i class="fa-solid fa-edit me-1"></i>Editar
                            </button>
                            <form action="{{ route('ativos.estacoes.destroy', $estacao->id) }}" method="POST" class="d-inline m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light border fw-bold text-danger shadow-sm" onclick="return confirm('Excluir esta estação permanentemente?')">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Editar Estacao embutido -->
                <div class="modal fade" id="modalEditEstacao{{ $estacao->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('ativos.estacoes.update', $estacao->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-edit text-primary me-2"></i>Editar Estação</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-muted">Apelido / Nome</label>
                                        <input type="text" name="nome" class="form-control" value="{{ $estacao->nome }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-muted">Descrição / Observações</label>
                                        <textarea name="descricao" class="form-control" rows="3">{{ $estacao->descricao }}</textarea>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light border-top-0">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Revogar</button>
                                    <button type="submit" class="btn btn-primary fw-bold"><i class="fa-solid fa-check me-1"></i>Atualizar Dados</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <!-- Teve casos no controlador que só filtra depto preenchido, mas garanto o empty state por precaução -->
            <div class="text-center py-4 bg-light rounded border text-muted">
                <i class="fa-solid fa-chair fs-2 mb-2 opacity-50"></i>
                <p class="mb-0">Nenhuma estação de trabalho cadastrada sob os atuais filtros.</p>
            </div>
            @endif
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info text-center border-0 shadow-sm py-5">
                <i class="fa-solid fa-magnifying-glass fs-1 mb-3 opacity-50"></i>
                <h5 class="fw-bold">Nenhum resultado</h5>
                <p class="mb-0">Não localizamos estações de trabalho. Ajuste os filtros ou cadastre um novo posto.</p>
            </div>
        </div>
        @endforelse
    </div>
</div>
<style>
.hover-shadow:hover {
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    transform: translateY(-2px);
}
.transition-all {
    transition: all .2s ease-in-out;
}
</style>

<!-- Modal Nova Estacao -->
<div class="modal fade" id="modalNovaEstacao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('ativos.estacoes.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nova Estação de Trabalho</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Departamento</label>
                        <select name="departamento_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            @foreach($departamentos as $depto)
                                <option value="{{ $depto->id }}">{{ $depto->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Apelido / Nome</label>
                        <input type="text" name="nome" class="form-control" placeholder="Ex: Estação Rodrigo, Recepção Central" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição / Local específico</label>
                        <textarea name="descricao" class="form-control" rows="2" placeholder="Ex: Mesa ao lado da janela"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar Estação</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
