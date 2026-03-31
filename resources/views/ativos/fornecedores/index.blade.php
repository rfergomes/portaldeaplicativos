@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fa-solid fa-truck-field me-2 text-primary"></i>Fornecedores
                </h1>
                <p class="text-muted">Gestão de empresas fornecedoras de equipamentos e serviços.</p>
            </div>
            @can('ativos.criar')
                <div class="col-md-4 text-end">
                    <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal"
                        data-bs-target="#modalNovoFornecedor">
                        <i class="fa-solid fa-plus me-2"></i>Novo Fornecedor
                    </button>
                </div>
            @endcan
        </div>

        <!-- Filtros -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body">
                <form action="{{ route('ativos.fornecedores.index') }}" method="GET" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label small fw-bold">Razão Social / Nome</label>
                        <input type="text" name="nome" class="form-control shadow-none" placeholder="Ex: Dell, Kalunga..."
                            value="{{ request('nome') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">CNPJ</label>
                        <input type="text" name="cnpj" class="form-control shadow-none"
                            placeholder="Apenas números ou formatado..." value="{{ request('cnpj') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-dark w-100">
                            <i class="fa-solid fa-magnifying-glass me-2"></i>Filtrar
                        </button>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <a href="{{ route('ativos.fornecedores.index') }}" class="btn btn-outline-secondary w-100">
                            Limpar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th class="ps-4">Nome</th>
                                <th>CNPJ / Contato</th>
                                <th>E-mail</th>
                                <th>Telefone</th>
                                <th>Equip. / Compras</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fornecedores as $forn)
                                <tr>
                                    <td class="ps-4"><span
                                            class="badge text-bg-light border shadow-sm px-2">#FOR_{{ $forn->id }}</span></td>
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ $forn->nome }}</div>
                                        <div class="small text-muted">{{ $forn->contato ?? 'Sem contato' }}</div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-primary"
                                                style="width: {{ $forn->equipamentos_count }}%"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>{{ $forn->cnpj ?? '-' }}</div>
                                    </td>
                                    <td>{{ $forn->email ?? '-' }}</td>
                                    <td>{{ $forn->telefone ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-1">
                                            <i class="fa-solid fa-boxes-stacked me-1"></i> {{ $forn->equipamentos_count }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge {{ $forn->ativo ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                            {{ $forn->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        @can('ativos.editar')
                                            <button type="button" class="btn btn-sm btn-white border" data-bs-toggle="modal"
                                                data-bs-target="#modalEditForn-{{ $forn->id }}">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                        @endcan
                                        @can('ativos.excluir')
                                            <form action="{{ route('ativos.fornecedores.destroy', $forn->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-white border text-danger"
                                                    onclick="return confirm('Excluir este fornecedor?')">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-inbox fa-3x mb-3 opacity-25"></i>
                                        <p>Nenhum fornecedor encontrado.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($fornecedores->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $fornecedores->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modais de Edição (Fora da tabela) -->
    @foreach($fornecedores as $forn)
        <div class="modal fade" id="modalEditForn-{{ $forn->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg">
                    <form action="{{ route('ativos.fornecedores.update', $forn->id) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="modal-header bg-primary text-white border-0 py-3">
                            <h5 class="modal-title fw-bold">
                                <i class="fa-solid fa-pen-to-square me-2"></i>Editar Fornecedor
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-4">
                                <div class="col-md-8">
                                    <div class="form-floating">
                                        <input type="text" name="nome" class="form-control bg-white shadow-none"
                                            id="edit-forn-nome-{{ $forn->id }}" value="{{ $forn->nome }}"
                                            placeholder="Razão Social" required>
                                        <label for="edit-forn-nome-{{ $forn->id }}"
                                            class="text-muted small fw-bold text-uppercase">Razão Social / Nome</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="text" name="cnpj" class="form-control bg-white shadow-none"
                                            id="edit-forn-cnpj-{{ $forn->id }}" value="{{ $forn->cnpj }}" placeholder="CNPJ">
                                        <label for="edit-forn-cnpj-{{ $forn->id }}"
                                            class="text-muted small fw-bold text-uppercase">CNPJ</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="email" name="email" class="form-control bg-white shadow-none"
                                            id="edit-forn-email-{{ $forn->id }}" value="{{ $forn->email }}"
                                            placeholder="E-mail">
                                        <label for="edit-forn-email-{{ $forn->id }}"
                                            class="text-muted small fw-bold text-uppercase">E-mail</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" name="telefone" class="form-control bg-white shadow-none"
                                            id="edit-forn-tel-{{ $forn->id }}" value="{{ $forn->telefone }}"
                                            placeholder="Telefone">
                                        <label for="edit-forn-tel-{{ $forn->id }}"
                                            class="text-muted small fw-bold text-uppercase">Telefone</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-floating">
                                        <input type="text" name="contato" class="form-control bg-white shadow-none"
                                            id="edit-forn-contato-{{ $forn->id }}" value="{{ $forn->contato }}"
                                            placeholder="Nome do Contato">
                                        <label for="edit-forn-contato-{{ $forn->id }}"
                                            class="text-muted small fw-bold text-uppercase">Nome do Contato /
                                            Representante</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-floating">
                                        <textarea name="endereco" class="form-control bg-white shadow-none"
                                            id="edit-forn-end-{{ $forn->id }}" style="height: 80px"
                                            placeholder="Endereço">{{ $forn->endereco }}</textarea>
                                        <label for="edit-forn-end-{{ $forn->id }}"
                                            class="text-muted small fw-bold text-uppercase">Endereço Completo</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="bg-light p-3 rounded-3 border">
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" name="ativo" value="1"
                                                id="edit-forn-ativo-{{ $forn->id }}" {{ $forn->ativo ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold text-secondary"
                                                for="edit-forn-ativo-{{ $forn->id }}">Fornecedor Ativo no Sistema</label>
                                        </div>
                                    </div>
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
    <div class="modal fade" id="modalNovoFornecedor" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <form action="{{ route('ativos.fornecedores.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success text-white border-0 py-3">
                        <h5 class="modal-title fw-bold">
                            <i class="fa-solid fa-plus me-2"></i>Novo Fornecedor
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <div class="col-md-8">
                                <div class="form-floating">
                                    <input type="text" name="nome" class="form-control bg-white shadow-none" id="new-nome"
                                        placeholder="Razão Social" required>
                                    <label for="new-nome" class="text-muted small fw-bold text-uppercase">Razão Social /
                                        Nome</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" name="cnpj" class="form-control bg-white shadow-none" id="new-cnpj"
                                        placeholder="CNPJ">
                                    <label for="new-cnpj" class="text-muted small fw-bold text-uppercase">CNPJ</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" name="email" class="form-control bg-white shadow-none"
                                        id="new-email" placeholder="E-mail">
                                    <label for="new-email" class="text-muted small fw-bold text-uppercase">E-mail</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="telefone" class="form-control bg-white shadow-none"
                                        id="new-tel" placeholder="Telefone">
                                    <label for="new-tel" class="text-muted small fw-bold text-uppercase">Telefone</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="text" name="contato" class="form-control bg-white shadow-none"
                                        id="new-contato" placeholder="Nome do Contato">
                                    <label for="new-contato" class="text-muted small fw-bold text-uppercase">Nome do Contato
                                        / Representante</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <textarea name="endereco" class="form-control bg-white shadow-none" id="new-end"
                                        style="height: 80px" placeholder="Endereço"></textarea>
                                    <label for="new-end" class="text-muted small fw-bold text-uppercase">Endereço
                                        Completo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-3">
                        <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                            <i class="fa-solid fa-plus me-2"></i>Criar Fornecedor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection