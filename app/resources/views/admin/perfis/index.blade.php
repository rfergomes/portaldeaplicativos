@extends('layouts.app')

@section('title', 'Gestão de Perfis')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title fw-bold m-0"><i class="fa-solid fa-shield-halved me-2"></i>Perfis e Acessos
                        </h3>
                        <div class="ms-auto">
                            <a href="{{ route('users.index') }}"
                                class="btn btn-outline-secondary btn-sm rounded-pill px-3 me-2">
                                <i class="fa-solid fa-users me-1"></i> Usuários
                            </a>
                            <a href="{{ route('perfis.create') }}"
                                class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                                <i class="fa-solid fa-plus me-1"></i> Novo Perfil
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nome do Perfil</th>
                                        <th>Descrição</th>
                                        <th class="text-center">Qtd. Permissões</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($perfis as $perfil)
                                        <tr>
                                            <td class="fw-bold text-primary">{{ $perfil->nome }}</td>
                                            <td>{{ $perfil->descricao ?? '-' }}</td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-info text-dark rounded-pill">{{ $perfil->permissoes_count }}</span>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('perfis.edit', $perfil) }}"
                                                    class="btn btn-sm btn-outline-primary rounded-circle"
                                                    title="Configurar Permissões">
                                                    <i class="fa-solid fa-user-lock"></i>
                                                </a>
                                                @if($perfil->nome !== 'Administrador')
                                                    <form action="{{ route('perfis.destroy', $perfil) }}" method="POST"
                                                        class="d-inline" onsubmit="return confirm('Excluir este perfil?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle"
                                                            title="Excluir">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4">Nenhum perfil cadastrado.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection