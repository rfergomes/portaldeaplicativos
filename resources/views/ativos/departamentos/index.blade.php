@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fa-solid fa-sitemap me-2 text-primary"></i>Departamentos
            </h1>
            <p class="text-muted">Gerencie os departamentos vinculados aos ativos.</p>
        </div>
        <div class="col-md-4 text-end">
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNovoDepto">
                <i class="fa-solid fa-plus me-2"></i>Novo Departamento
            </button>
        </div>
    </div>

    <!-- Tabela -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" style="width: 80px;">ID</th>
                            <th>Nome</th>
                            <th class="text-center">Usuários</th>
                            <th class="text-center">Equipamentos</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departamentos as $depto)
                        <tr>
                            <td class="ps-4">#{{ $depto->id }}</td>
                            <td class="fw-bold">{{ $depto->nome }}</td>
                            <td class="text-center">
                                <span class="badge bg-light text-primary border fw-bold">
                                    <i class="fa-solid fa-users me-1"></i>{{ $depto->usuarios_count }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border fw-bold">
                                    <i class="fa-solid fa-laptop me-1"></i>{{ $depto->equipamentos_count }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $depto->ativo ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                    {{ $depto->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-white border" data-bs-toggle="modal" data-bs-target="#modalEditDepto-{{ $depto->id }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('ativos.departamentos.destroy', $depto->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-white border text-danger" onclick="return confirm('Excluir este departamento?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">Nenhum departamento cadastrado.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modais de Edição (Fora da tabela) -->
@foreach($departamentos as $depto)
<div class="modal fade" id="modalEditDepto-{{ $depto->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('ativos.departamentos.update', $depto->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-pen-to-square me-2"></i>Editar Departamento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-floating mb-4">
                        <input type="text" name="nome" class="form-control bg-white shadow-none" id="edit-depto-nome-{{ $depto->id }}" value="{{ $depto->nome }}" placeholder="Nome do Departamento" required>
                        <label for="edit-depto-nome-{{ $depto->id }}" class="text-muted small fw-bold text-uppercase">Nome do Departamento</label>
                    </div>
                    <div class="bg-light p-3 rounded-3 border">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" name="ativo" value="1" id="edit-depto-ativo-{{ $depto->id }}" {{ $depto->ativo ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-secondary" for="edit-depto-ativo-{{ $depto->id }}">Departamento Ativo no Sistema</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                        <i class="fa-solid fa-check me-2"></i>Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Modal Novo -->
<div class="modal fade" id="modalNovoDepto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('ativos.departamentos.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white border-0 py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-plus me-2"></i>Novo Departamento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-floating mb-4">
                        <input type="text" name="nome" class="form-control bg-white shadow-none" id="new-nome" placeholder="Nome do Departamento" required>
                        <label for="new-nome" class="text-muted small fw-bold text-uppercase">Nome do Departamento</label>
                    </div>
                    <div class="bg-light p-3 rounded-3 border">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" name="ativo" value="1" id="new-ativo" checked>
                            <label class="form-check-label fw-bold text-secondary" for="new-ativo">Ativo no Sistema</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                        <i class="fa-solid fa-plus me-2"></i>Criar Departamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
