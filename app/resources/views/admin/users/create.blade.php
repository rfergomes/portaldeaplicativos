@extends('layouts.app')

@section('title', 'Novo Usuário')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title fw-bold m-0"><i class="fa-solid fa-user-plus me-2"></i>Cadastrar Usuário</h3>
                </div>
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nome Completo</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">E-mail</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Senha</label>
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror" required minlength="8">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Confirmar Senha</label>
                                <input type="password" name="password_confirmation" class="form-control" required
                                    minlength="8">
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="fw-bold border-bottom pb-2">Segurança e Permissões</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Perfis de Acesso</label>
                                <select name="perfis[]" class="form-select select2" multiple
                                    data-placeholder="Selecione os perfis">
                                    @foreach($perfis as $perfil)
                                        <option value="{{ $perfil->id }}" {{ in_array($perfil->id, old('perfis', [])) ? 'selected' : '' }}>
                                            {{ $perfil->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Define quais menus e ações o usuário terá acesso.</small>
                                @error('perfis')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Unidade / Token AR-Online</label>
                                <select name="token_depto_id" class="form-select @error('token_depto_id') is-invalid @enderror">
                                    <option value="">-- SEM INTEGRAÇÃO DE AR --</option>
                                    @foreach($tokenDeptos as $depto)
                                        <option value="{{ $depto->id }}" {{ old('token_depto_id') == $depto->id ? 'selected' : '' }}>
                                            {{ $depto->departamento }} ({{ $depto->email }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Selecione o departamento para autenticar Envios.</small>
                                @error('token_depto_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-end gap-2">
                        <a href="{{ route('users.index') }}"
                            class="btn btn-outline-secondary rounded-pill px-4">Cancelar</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><i
                                class="fa-solid fa-save me-2"></i>Salvar Usuário</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection