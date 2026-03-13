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
                            <td class="ps-4">#{{ $fab->id }}</td>
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

                        <!-- Modal Edit -->
                        <div class="modal fade" id="modalEditFab-{{ $fab->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('ativos.fabricantes.update', $fab->id) }}" method="POST" class="modal-content text-start">
                                    @csrf @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Editar Fabricante</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Nome</label>
                                            <input type="text" name="nome" class="form-control shadow-none" value="{{ $fab->nome }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Site (URL)</label>
                                            <input type="url" name="site" class="form-control shadow-none" value="{{ $fab->site }}" placeholder="https://...">
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="ativo" value="1" {{ $fab->ativo ? 'checked' : '' }}>
                                            <label class="form-check-label">Fabricante Ativo</label>
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
                            <td colspan="5" class="text-center py-5 text-muted">Nenhum fabricante cadastrado.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo -->
<div class="modal fade" id="modalNovoFabricante" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('ativos.fabricantes.store') }}" method="POST" class="modal-content text-start">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Novo Fabricante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nome do Fabricante</label>
                    <input type="text" name="nome" class="form-control shadow-none" placeholder="Ex: Dell, Samsung..." required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Site (opcional)</label>
                    <input type="url" name="site" class="form-control shadow-none" placeholder="https://...">
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="ativo" value="1" checked>
                    <label class="form-check-label">Ativo</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Criar Fabricante</button>
            </div>
        </form>
    </div>
</div>
@endsection
