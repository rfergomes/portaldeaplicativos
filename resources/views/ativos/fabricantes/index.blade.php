@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fa-solid fa-industry me-2 text-primary"></i>Fabricantes
            </h1>
            <p class="text-muted">Gerencie os fabricantes de seus equipamentos.</p>
        </div>
        <div class="col-md-4 text-end">
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNovoFabricante">
                <i class="fa-solid fa-plus me-2"></i>Novo Fabricante
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
                            <th>Site</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fabricantes as $fab)
                        <tr>
                            <td class="ps-4"><span class="badge text-bg-light border shadow-sm px-2">#FAB_{{ $fab->id }}</span></td>
                            <td class="fw-bold">{{ $fab->nome }}</td>
                            <td>
                                @if($fab->site)
                                    <a href="{{ $fab->site }}" target="_blank" class="text-decoration-none small">
                                        <i class="fa-solid fa-link me-1"></i>Visitar Site
                                    </a>
                                @else
                                    <span class="text-muted small">Não informado</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $fab->ativo ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                    {{ $fab->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-white border" data-bs-toggle="modal" data-bs-target="#modalEditFab-{{ $fab->id }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('ativos.fabricantes.destroy', $fab->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-white border text-danger" onclick="return confirm('Excluir este fabricante?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Nenhum fabricante cadastrado.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modais de Edição (Fora da tabela) -->
@foreach($fabricantes as $fab)
<div class="modal fade" id="modalEditFab-{{ $fab->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('ativos.fabricantes.update', $fab->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-pen-to-square me-2"></i>Editar Fabricante
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-floating mb-3">
                        <input type="text" name="nome" class="form-control bg-white shadow-none" id="edit-fab-nome-{{ $fab->id }}" value="{{ $fab->nome }}" placeholder="Nome do Fabricante" required>
                        <label for="edit-fab-nome-{{ $fab->id }}" class="text-muted small fw-bold text-uppercase">Nome do Fabricante</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="url" name="site" class="form-control bg-white shadow-none" id="edit-fab-site-{{ $fab->id }}" value="{{ $fab->site }}" placeholder="Site (URL)">
                        <label for="edit-fab-site-{{ $fab->id }}" class="text-muted small fw-bold text-uppercase">Site (URL)</label>
                    </div>
                    <div class="bg-light p-3 rounded-3 border">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" name="ativo" value="1" id="edit-fab-ativo-{{ $fab->id }}" {{ $fab->ativo ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-secondary" for="edit-fab-ativo-{{ $fab->id }}">Fabricante Ativo no Sistema</label>
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
<div class="modal fade" id="modalNovoFabricante" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('ativos.fabricantes.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white border-0 py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-plus me-2"></i>Novo Fabricante
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="form-floating mb-3">
                        <input type="text" name="nome" class="form-control bg-white shadow-none" id="new-nome" placeholder="Nome do Fabricante" required>
                        <label for="new-nome" class="text-muted small fw-bold text-uppercase">Nome do Fabricante</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="url" name="site" class="form-control bg-white shadow-none" id="new-site" placeholder="Site (opcional)">
                        <label for="new-site" class="text-muted small fw-bold text-uppercase">Site (opcional)</label>
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
                        <i class="fa-solid fa-plus me-2"></i>Criar Fabricante
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
