@extends('layouts.app')

@section('title', 'Convenções Coletivas')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-sm-6">
            <h1 class="h4 mb-0 text-gray-800 fw-bold">
                <i class="fa-solid fa-file-contract text-primary me-2"></i>Convenções Coletivas
            </h1>
            <p class="text-muted small mb-0">Gestão das convenções de trabalho das categorias Química e Farmacêutica</p>
        </div>
        <div class="col-sm-6 text-end">
            <a href="{{ route('admin.convencoes.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-plus me-1"></i> Nova Convenção
            </a>
        </div>
    </div>

    <!-- Filtros e Busca -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.convencoes.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Buscar por título, data-base..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="categoria" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Todas as Categorias</option>
                        <option value="QUIMICA" {{ request('categoria') === 'QUIMICA' ? 'selected' : '' }}>Química (Data-Base Nov)</option>
                        <option value="FARMACEUTICA" {{ request('categoria') === 'FARMACEUTICA' ? 'selected' : '' }}>Farmacêutica (Data-Base Abr)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Todos os Status</option>
                        <option value="ativo" {{ request('status') === 'ativo' ? 'selected' : '' }}>Somente Ativas</option>
                        <option value="inativo" {{ request('status') === 'inativo' ? 'selected' : '' }}>Somente Inativas</option>
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    @if(request()->hasAny(['search', 'categoria', 'status']))
                        <a href="{{ route('admin.convencoes.index') }}" class="btn btn-light btn-sm rounded-pill px-3 border">
                            <i class="fa-solid fa-xmark me-1"></i> Limpar
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Convenções -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 premium-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Convenção / Título</th>
                            <th class="text-center">Categoria</th>
                            <th class="text-center">Vigência</th>
                            <th class="text-center">Data-Base</th>
                            <th class="text-center">Cláusulas</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($convencoes as $convencao)
                            <tr>
                                <td class="ps-4">
                                    <a href="{{ route('admin.convencoes.show', $convencao) }}" class="fw-bold text-decoration-none text-dark d-block">
                                        {{ $convencao->titulo }}
                                    </a>
                                    @if($convencao->abrangencia)
                                        <small class="text-muted text-truncate d-block" style="max-width: 350px;" title="{{ $convencao->abrangencia }}">
                                            {{ Str::limit($convencao->abrangencia, 70) }}
                                        </small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($convencao->categoria === 'QUIMICA')
                                        <span class="badge bg-primary-subtle text-primary border border-primary px-2 py-1">
                                            <i class="fa-solid fa-flask me-1"></i> Química
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                            <i class="fa-solid fa-pills me-1"></i> Farmacêutica
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center small">
                                    <i class="fa-regular fa-calendar-days text-muted me-1"></i>
                                    {{ $convencao->vigencia_inicio ? $convencao->vigencia_inicio->format('d/m/Y') : '-' }} a {{ $convencao->vigencia_fim ? $convencao->vigencia_fim->format('d/m/Y') : '-' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">
                                        {{ $convencao->data_base }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill bg-info text-dark px-2">
                                        {{ $convencao->clausulas_count }} cláusula(s)
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($convencao->ativo)
                                        <span class="badge bg-success rounded-pill px-2">Ativa</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill px-2">Inativa</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.convencoes.show', $convencao) }}" class="btn btn-outline-primary" title="Ver Detalhes e Cláusulas">
                                            <i class="fa-solid fa-list-check"></i>
                                        </a>
                                        <a href="{{ route('admin.convencoes.edit', $convencao) }}" class="btn btn-outline-secondary" title="Editar">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete({{ $convencao->id }})" title="Excluir">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-form-{{ $convencao->id }}" action="{{ route('admin.convencoes.destroy', $convencao) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <div class="mb-2"><i class="fa-solid fa-file-circle-xmark fa-3x opacity-25"></i></div>
                                    Nenhuma convenção coletiva cadastrada para os filtros selecionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($convencoes->hasPages())
            <div class="card-footer bg-light border-0 py-2">
                {{ $convencoes->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Excluir Convenção?',
            text: "Todas as cláusulas vinculadas a esta convenção também serão removidas permanentemente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endpush
@endsection
