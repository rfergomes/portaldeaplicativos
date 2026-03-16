@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fa-solid fa-users-gear me-2 text-primary"></i>Cessionários / Locatários
            </h1>
            <p class="text-muted">Pessoas autorizadas a retirar e utilizar equipamentos da empresa.</p>
        </div>
        <div class="col-md-4 text-end">
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNovoUsuario">
                <i class="fa-solid fa-user-plus me-2"></i>Novo Cessionário
            </button>
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
                            <th>Empresa</th>
                            <th>Departamento</th>
                            <th>Contato</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $usuario)
                        <tr>
                            <td class="ps-4">
                                <span class="badge text-bg-light border shadow-sm px-2">#USR_{{ $usuario->id }}</span>
                            </td>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $usuario->nome }}</div>
                            </td>
                            <td>{{ $usuario->empresa->razao_social ?? 'S/ Empresa' }}</td>
                            <td>{{ $usuario->departamento->nome ?? 'S/ Depto' }}</td>
                            <td>
                                <div class="small"><i class="fa-regular fa-envelope me-1"></i>{{ $usuario->email ?? '-' }}</div>
                                <div class="small"><i class="fa-solid fa-phone me-1"></i>{{ $usuario->telefone ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $usuario->ativo ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                    {{ $usuario->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-white border" data-bs-toggle="modal" data-bs-target="#modalEditUser-{{ $usuario->id }}">
                                    <i class="fa-solid fa-user-pen"></i>
                                </button>
                                <form action="{{ route('ativos.usuarios.destroy', $usuario->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-white border text-danger" onclick="return confirm('Excluir este cessionário?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Nenhum cessionário cadastrado.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modais de Edição (Fora da tabela para evitar bugs de layout) -->
@foreach($usuarios as $usuario)
<div class="modal fade" id="modalEditUser-{{ $usuario->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('ativos.usuarios.update', $usuario->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-user-pen me-2"></i>Editar Cessionário
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-12">
                            <div class="form-floating">
                                <input type="text" name="nome" class="form-control bg-white shadow-none" id="edit-nome-{{ $usuario->id }}" value="{{ $usuario->nome }}" placeholder="Nome Completo" required>
                                <label for="edit-nome-{{ $usuario->id }}" class="text-muted small fw-bold text-uppercase">Nome Completo</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" name="email" class="form-control bg-white shadow-none" id="edit-email-{{ $usuario->id }}" value="{{ $usuario->email }}" placeholder="E-mail">
                                <label for="edit-email-{{ $usuario->id }}" class="text-muted small fw-bold text-uppercase">E-mail</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="telefone" class="form-control bg-white shadow-none" id="edit-tel-{{ $usuario->id }}" value="{{ $usuario->telefone }}" placeholder="Telefone">
                                <label for="edit-tel-{{ $usuario->id }}" class="text-muted small fw-bold text-uppercase">Telefone</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select name="empresa_id" class="form-select bg-white shadow-none" id="edit-emp-{{ $usuario->id }}">
                                    <option value="">Selecione uma Empresa</option>
                                    @foreach($empresas as $emp)
                                        <option value="{{ $emp->id }}" {{ $usuario->empresa_id == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->razao_social }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="edit-emp-{{ $usuario->id }}" class="text-muted small fw-bold text-uppercase">Empresa</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select name="departamento_id" class="form-select bg-white shadow-none" id="edit-depto-{{ $usuario->id }}">
                                    <option value="">Selecione o Departamento</option>
                                    @foreach($departamentos as $depto)
                                        <option value="{{ $depto->id }}" {{ $usuario->departamento_id == $depto->id ? 'selected' : '' }}>
                                            {{ $depto->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="edit-depto-{{ $usuario->id }}" class="text-muted small fw-bold text-uppercase">Departamento</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="bg-light p-3 rounded-3 border">
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" name="ativo" value="1" id="edit-ativo-{{ $usuario->id }}" {{ $usuario->ativo ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-secondary" for="edit-ativo-{{ $usuario->id }}">Cessionário Ativo no Sistema</label>
                                </div>
                            </div>
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
<div class="modal fade" id="modalNovoUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('ativos.usuarios.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white border-0 py-3">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-user-plus me-2"></i>Novo Cessionário
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-12">
                            <div class="form-floating">
                                <input type="text" name="nome" class="form-control bg-white shadow-none" id="new-nome" placeholder="Nome Completo" required>
                                <label for="new-nome" class="text-muted small fw-bold text-uppercase">Nome Completo</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" name="email" class="form-control bg-white shadow-none" id="new-email" placeholder="E-mail">
                                <label for="new-email" class="text-muted small fw-bold text-uppercase">E-mail</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="telefone" class="form-control bg-white shadow-none" id="new-tel" placeholder="Telefone">
                                <label for="new-tel" class="text-muted small fw-bold text-uppercase">Telefone</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select name="empresa_id" class="form-select bg-white shadow-none" id="new-emp">
                                    <option value="">Selecione uma Empresa</option>
                                    @foreach($empresas as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->razao_social }}</option>
                                    @endforeach
                                </select>
                                <label for="new-emp" class="text-muted small fw-bold text-uppercase">Empresa</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select name="departamento_id" class="form-select bg-white shadow-none" id="new-depto">
                                    <option value="">Selecione o Departamento</option>
                                    @foreach($departamentos as $depto)
                                        <option value="{{ $depto->id }}">{{ $depto->nome }}</option>
                                    @endforeach
                                </select>
                                <label for="new-depto" class="text-muted small fw-bold text-uppercase">Departamento</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                        <i class="fa-solid fa-plus me-2"></i>Criar Cessionário
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
