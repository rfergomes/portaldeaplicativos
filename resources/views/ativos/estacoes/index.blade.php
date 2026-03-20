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

    <div class="row">
        @foreach($departamentos as $depto)
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title h6 fw-bold mb-0 text-primary">
                        <i class="fa-solid fa-sitemap me-2"></i>{{ $depto->nome }}
                    </h5>
                    <span class="badge bg-secondary rounded-pill small">{{ $depto->estacoes->count() }} Estações</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr style="font-size: 0.75rem;" class="text-muted text-uppercase">
                                    <th class="ps-4">Apelido / Nome do Posto</th>
                                    <th>Equipamentos Vinculados</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($depto->estacoes->count() > 0)
                                @foreach($depto->estacoes as $estacao)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $estacao->nome }}</div>
                                        <div class="small text-muted">{{ $estacao->descricao ?: 'Sem descrição' }}</div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @forelse($estacao->equipamentos as $equipa)
                                                <span class="badge text-bg-light border" title="{{ $equipa->modelo }}">
                                                    <i class="fa-solid fa-laptop me-1 small"></i>{{ $equipa->descricao }}
                                                </span>
                                            @empty
                                                <span class="text-muted small italic">Nenhum equipamento vinculado</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td>
                                        @if($estacao->equipamentos->count() > 0)
                                            <span class="badge bg-success-subtle text-success">Ativa</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning">Vazia</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-link text-primary p-0 me-2" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalEditEstacao{{ $estacao->id }}">
                                                <i class="fa-solid fa-edit"></i>
                                            </button>
                                            <form action="{{ route('ativos.estacoes.destroy', $estacao->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link text-danger p-0" onclick="return confirm('Excluir esta estação?')">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Editar Estacao -->
                                <div class="modal fade" id="modalEditEstacao{{ $estacao->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('ativos.estacoes.update', $estacao->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Editar Estação</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Apelido / Nome</label>
                                                        <input type="text" name="nome" class="form-control" value="{{ $estacao->nome }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Descrição / Observações</label>
                                                        <textarea name="descricao" class="form-control" rows="3">{{ $estacao->descricao }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @else
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">Nenhuma estação cadastrada para este departamento.</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

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
