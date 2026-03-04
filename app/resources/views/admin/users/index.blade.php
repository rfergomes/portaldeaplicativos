@extends('layouts.app')

@section('title', 'Gestão de Usuários')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title fw-bold m-0"><i class="fa-solid fa-users me-2"></i>Usuários do Sistema</h3>
                        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm rounded-pill shadow-sm ms-auto">
                            <i class="fa-solid fa-plus me-1"></i> Novo Usuário
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>E-mail</th>
                                        <th>Perfis</th>
                                        <th>Token AR-Online</th>
                                        <th>Último Acesso</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($users as $user)
                                        <tr>
                                            <td class="fw-semibold">{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @foreach ($user->perfis as $perfil)
                                                    <span
                                                        class="badge bg-info text-dark rounded-pill shadow-sm px-2">{{ $perfil->nome }}</span>
                                                @endforeach
                                            </td>
                                            <td>
                                                @if($user->tokenDepto)
                                                    <span class="badge bg-success rounded-pill px-2"><i
                                                            class="fa-solid fa-check me-1"></i>{{ $user->tokenDepto->departamento }}</span>
                                                @else
                                                    <span class="badge bg-secondary rounded-pill px-2"><i
                                                            class="fa-solid fa-xmark me-1"></i>Pendente</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($user->last_login_at)
                                                    <span class="text-muted small">
                                                        <i class="fa-solid fa-clock me-1"></i>
                                                        {{ $user->last_login_at->format('d/m/Y H:i') }}
                                                    </span>
                                                @else
                                                    <span class="text-muted small">Nunca</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('users.edit', $user) }}"
                                                    class="btn btn-sm btn-outline-primary rounded-circle" title="Editar">
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>
                                                <form action="{{ route('users.destroy', $user) }}" method="POST"
                                                    id="delete-form-{{ $user->id }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-circle"
                                                        title="Excluir" {{ auth()->id() === $user->id ? 'disabled' : '' }}
                                                        onclick="confirmDelete('delete-form-{{ $user->id }}', 'Tem certeza que deseja excluir este usuário?')">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                Nenhum usuário encontrado.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($users->hasPages())
                        <div class="card-footer border-top">
                            {{ $users->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection