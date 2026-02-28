@extends('layouts.app')

@section('title', 'Editar Usuário')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title fw-bold m-0"><i class="fa-solid fa-user-pen me-2"></i>Editar Usuário:
                        {{ $user->name }}
                    </h3>
                </div>
                <form action="{{ route('users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nome Completo</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $user->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">E-mail</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email) }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Nova Senha <small>(Deixe em branco para
                                        manter)</small></label>
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror" minlength="8">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted">Confirmar Nova Senha</label>
                                <input type="password" name="password_confirmation" class="form-control" minlength="8">
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="fw-bold border-bottom pb-2">Segurança e Permissões</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Perfis de Acesso</label>
                                @php $userPerfis = $user->perfis->pluck('id')->toArray(); @endphp
                                <select name="perfis[]" class="form-select select2" multiple
                                    data-placeholder="Selecione os perfis">
                                    @foreach($perfis as $perfil)
                                        <option value="{{ $perfil->id }}" {{ in_array($perfil->id, old('perfis', $userPerfis)) ? 'selected' : '' }}>
                                            {{ $perfil->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Define quais menus e ações o usuário terá acesso.</small>
                                @error('perfis')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Unidade / Token AR-Online</label>
                                <select name="token_depto_id"
                                    class="form-select @error('token_depto_id') is-invalid @enderror">
                                    <option value="">-- SEM INTEGRAÇÃO DE AR --</option>
                                    @foreach($tokenDeptos as $depto)
                                        <option value="{{ $depto->id }}" {{ old('token_depto_id', $user->token_depto_id) == $depto->id ? 'selected' : '' }}>
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
                                class="fa-solid fa-save me-2"></i>Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection