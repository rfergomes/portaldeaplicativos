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
        <div class="col-md-4 text-end">
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNovoFornecedor">
                <i class="fa-solid fa-plus me-2"></i>Novo Fornecedor
            </button>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Nome</th>
                            <th>CNPJ / Contato</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fornecedores as $forn)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold">{{ $forn->nome }}</div>
                                <div class="small text-muted">{{ $forn->contato ?? 'Sem contato' }}</div>
                            </td>
                            <td>
                                <div>{{ $forn->cnpj ?? '-' }}</div>
                            </td>
                            <td>{{ $forn->email ?? '-' }}</td>
                            <td>{{ $forn->telefone ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $forn->ativo ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                    {{ $forn->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-white border" data-bs-toggle="modal" data-bs-target="#modalEditForn-{{ $forn->id }}">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('ativos.fornecedores.destroy', $forn->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-white border text-danger" onclick="return confirm('Excluir este fornecedor?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="modalEditForn-{{ $forn->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <form action="{{ route('ativos.fornecedores.update', $forn->id) }}" method="POST" class="modal-content text-start">
                                    @csrf @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Editar Fornecedor</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-8">
                                                <label class="form-label small fw-bold">Razão Social / Nome</label>
                                                <input type="text" name="nome" class="form-control" value="{{ $forn->nome }}" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small fw-bold">CNPJ</label>
                                                <input type="text" name="cnpj" class="form-control" value="{{ $forn->cnpj }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">E-mail</label>
                                                <input type="email" name="email" class="form-control" value="{{ $forn->email }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Telefone</label>
                                                <input type="text" name="telefone" class="form-control" value="{{ $forn->telefone }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Nome do Contato</label>
                                                <input type="text" name="contato" class="form-control" value="{{ $forn->contato }}">
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label small fw-bold">Endereço</label>
                                                <textarea name="endereco" class="form-control" rows="2">{{ $forn->endereco }}</textarea>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="ativo" value="1" {{ $forn->ativo ? 'checked' : '' }}>
                                                    <label class="form-check-label">Fornecedor Ativo</label>
                                                </div>
                                            </div>
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
                            <td colspan="6" class="text-center py-5 text-muted">Nenhum fornecedor cadastrado.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo -->
<div class="modal fade" id="modalNovoFornecedor" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('ativos.fornecedores.store') }}" method="POST" class="modal-content text-start">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Novo Fornecedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label small fw-bold">Razão Social / Nome</label>
                        <input type="text" name="nome" class="form-control shadow-none" placeholder="Ex: Dell Tecnologia LTDA" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">CNPJ</label>
                        <input type="text" name="cnpj" class="form-control shadow-none" placeholder="00.000.000/0000-00">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">E-mail</label>
                        <input type="email" name="email" class="form-control shadow-none" placeholder="contato@fornecedor.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Telefone</label>
                        <input type="text" name="telefone" class="form-control shadow-none" placeholder="(00) 0000-0000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Nome do Contato</label>
                        <input type="text" name="contato" class="form-control shadow-none" placeholder="Nome do representante">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold">Endereço</label>
                        <textarea name="endereco" class="form-control shadow-none" rows="2" placeholder="Rua, número, bairro, cidade..."></textarea>
                    </div>
                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="ativo" value="1" checked>
                            <label class="form-check-label">Ativo</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Criar Fornecedor</button>
            </div>
        </form>
    </div>
</div>
@endsection
