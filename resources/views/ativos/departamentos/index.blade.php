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
            @can('ativos.criar')
                <div class="col-md-4 text-end">
                    <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal"
                        data-bs-target="#modalNovoDepto">
                        <i class="fa-solid fa-plus me-2"></i>Novo Departamento
                    </button>
                </div>
            @endcan
        </div>

        <!-- Filtros -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-3">
                <form action="{{ route('ativos.departamentos.index') }}" method="GET" class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i
                                    class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0 shadow-none"
                                placeholder="Buscar por nome..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select shadow-none">
                            <option value="">Todos os Status</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Ativo</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inativo</option>
                        </select>
                    </div>
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-primary shadow-sm"><i
                                class="fa-solid fa-filter me-2"></i>Filtrar</button>
                    </div>
                    @if(request()->anyFilled(['search', 'status']))
                        <div class="col-md-auto">
                            <a href="{{ route('ativos.departamentos.index') }}" class="btn btn-light text-muted"><i
                                    class="fa-solid fa-xmark me-2"></i>Limpar</a>
                        </div>
                    @endif
                </form>
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
                                    <td class="ps-4"><span
                                            class="badge text-bg-light border shadow-sm px-2">#DPT_{{ $depto->id ?? '-' }}</span>
                                    </td>
                                    <td class="fw-bold">{{ $depto->nome }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-primary border fw-bold">
                                            <i class="fa-solid fa-users me-1"></i>{{ $depto->usuarios_count }}
                                            <div class="progress progress-sm mt-1">
                                                <div class="progress-bar bg-primary"
                                                    style="width: {{ $depto->usuarios_count }}%"></div>
                                            </div>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border fw-bold">
                                            <i class="fa-solid fa-laptop me-1"></i>{{ $depto->equipamentos_count }}
                                            <div class="progress progress-sm mt-1">
                                                <div class="progress-bar bg-primary"
                                                    style="width: {{ $depto->equipamentos_count }}%"></div>
                                            </div>
                                        </span>

                                    </td>
                                    <td>
                                        <span
                                            class="badge {{ $depto->ativo ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                            {{ $depto->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        @can('ativos.editar')
                                            <button type="button" class="btn btn-sm btn-white border" data-bs-toggle="modal"
                                                data-bs-target="#modalEditDepto-{{ $depto->id }}">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                        @endcan
                                        @can('ativos.excluir')
                                            <form action="{{ route('ativos.departamentos.destroy', $depto->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-white border text-danger"
                                                    onclick="return confirm('Excluir este departamento?')">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
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

                @if($departamentos->hasPages())
                    <div class="px-4 py-3 border-top">
                        {{ $departamentos->appends(request()->query())->links() }}
                    </div>
                @endif
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
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="form-floating mb-4">
                                <input type="text" name="nome" class="form-control bg-white shadow-none"
                                    id="edit-depto-nome-{{ $depto->id }}" value="{{ $depto->nome }}"
                                    placeholder="Nome do Departamento" required>
                                <label for="edit-depto-nome-{{ $depto->id }}"
                                    class="text-muted small fw-bold text-uppercase">Nome do Departamento</label>
                            </div>
                            <div class="bg-light p-3 rounded-3 border">
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" name="ativo" value="1"
                                        id="edit-depto-ativo-{{ $depto->id }}" {{ $depto->ativo ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-secondary"
                                        for="edit-depto-ativo-{{ $depto->id }}">Departamento Ativo no Sistema</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-0 py-3">
                            <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none"
                                data-bs-dismiss="modal">Cancelar</button>
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
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="form-floating mb-4">
                            <input type="text" name="nome" class="form-control bg-white shadow-none" id="new-nome"
                                placeholder="Nome do Departamento" required>
                            <label for="new-nome" class="text-muted small fw-bold text-uppercase">Nome do
                                Departamento</label>
                        </div>
                        <div class="bg-light p-3 rounded-3 border">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" name="ativo" value="1" id="new-ativo"
                                    checked>
                                <label class="form-check-label fw-bold text-secondary" for="new-ativo">Ativo no
                                    Sistema</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3">
                        <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                            <i class="fa-solid fa-plus me-2"></i>Criar Departamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection