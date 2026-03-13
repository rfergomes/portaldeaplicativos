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
                            <th>Status</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departamentos as $depto)
                        <tr>
                            <td class="ps-4">#{{ $depto->id }}</td>
                            <td class="fw-bold">{{ $depto->nome }}</td>
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

                        <!-- Modal Edit -->
                        <div class="modal fade" id="modalEditDepto-{{ $depto->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('ativos.departamentos.update', $depto->id) }}" method="POST" class="modal-content">
                                    @csrf @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Editar Departamento</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">Nome</label>
                                            <input type="text" name="nome" class="form-control" value="{{ $depto->nome }}" required>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="ativo" value="1" {{ $depto->ativo ? 'checked' : '' }}>
                                            <label class="form-check-label">Departamento Ativo</label>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                                    </div>
                                </form>
                            </div>
                        </div>
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

<!-- Modal Novo -->
<div class="modal fade" id="modalNovoDepto" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('ativos.departamentos.store') }}" method="POST" class="modal-content text-start">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Novo Departamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nome do Departamento</label>
                    <input type="text" name="nome" class="form-control shadow-none" placeholder="Ex: TI, RH..." required>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="ativo" value="1" checked>
                    <label class="form-check-label">Ativo</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Criar Departamento</button>
            </div>
        </form>
    </div>
</div>
@endsection
