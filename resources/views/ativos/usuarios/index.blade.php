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
                                <div class="fw-bold text-dark">{{ $usuario->nome }}</div>
                                <div class="small text-muted">ID: #{{ $usuario->id }}</div>
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

                        <!-- Modal Edit -->
                        <div class="modal fade" id="modalEditUser-{{ $usuario->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <form action="{{ route('ativos.usuarios.update', $usuario->id) }}" method="POST" class="modal-content text-start">
                                    @csrf @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Editar Cessionário</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-md-12">
                                                <label class="form-label small fw-bold">Nome Completo</label>
                                                <input type="text" name="nome" class="form-control shadow-none" value="{{ $usuario->nome }}" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">E-mail</label>
                                                <input type="email" name="email" class="form-control shadow-none" value="{{ $usuario->email }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Telefone</label>
                                                <input type="text" name="telefone" class="form-control shadow-none" value="{{ $usuario->telefone }}">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Empresa</label>
                                                <select name="empresa_id" class="form-select shadow-none">
                                                    <option value="">Selecione uma Empresa</option>
                                                    @foreach($empresas as $emp)
                                                        <option value="{{ $emp->id }}" {{ $usuario->empresa_id == $emp->id ? 'selected' : '' }}>
                                                            {{ $emp->razao_social }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="form-text small">Use para filtrar por Química/Farmacêutica.</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold">Departamento</label>
                                                <select name="departamento_id" class="form-select shadow-none">
                                                    <option value="">Selecione o Departamento</option>
                                                    @foreach($departamentos as $depto)
                                                        <option value="{{ $depto->id }}" {{ $usuario->departamento_id == $depto->id ? 'selected' : '' }}>
                                                            {{ $depto->nome }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="ativo" value="1" {{ $usuario->ativo ? 'checked' : '' }}>
                                                    <label class="form-check-label">Usuário Ativo</label>
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
                            <td colspan="6" class="text-center py-5 text-muted">Nenhum cessionário cadastrado.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo -->
<div class="modal fade" id="modalNovoUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('ativos.usuarios.store') }}" method="POST" class="modal-content text-start">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Novo Cessionário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label small fw-bold">Nome Completo</label>
                        <input type="text" name="nome" class="form-control shadow-none" placeholder="Nome do cessionário" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">E-mail</label>
                        <input type="email" name="email" class="form-control shadow-none" placeholder="email@exemplo.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Telefone</label>
                        <input type="text" name="telefone" class="form-control shadow-none" placeholder="(00) 00000-0000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Empresa</label>
                        <select name="empresa_id" class="form-select shadow-none">
                            <option value="">Selecione uma Empresa</option>
                            @foreach($empresas as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->razao_social }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Departamento</label>
                        <select name="departamento_id" class="form-select shadow-none">
                            <option value="">Selecione o Departamento</option>
                            @foreach($departamentos as $depto)
                                <option value="{{ $depto->id }}">{{ $depto->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Criar Cessionário</button>
            </div>
        </form>
    </div>
</div>
@endsection
