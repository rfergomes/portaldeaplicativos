@extends('layouts.app')

@section('title', 'Meu Perfil')

@section('content')
    <div class="container-fluid">
        <div class="row g-4">
            <!-- Coluna de Dados Pessoais -->
            <div class="col-md-6">
                <div class="card card-outline card-primary shadow-sm h-100">
                    <div class="card-header">
                        <h3 class="card-title fw-bold"><i class="fa-solid fa-user-edit me-2"></i>Dados Pessoais</h3>
                    </div>
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Nome</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $user->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">E-mail</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email) }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-0">
                                <label class="form-label fw-bold small">Usuário (Username)</label>
                                <input type="text" class="form-control bg-light" value="{{ $user->username }}" readonly
                                    disabled>
                                <small class="text-muted">O nome de usuário não pode ser alterado.</small>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                <i class="fa-solid fa-save me-2"></i>Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Coluna de Troca de Senha -->
            <div class="col-md-6">
                <div class="card card-outline card-warning shadow-sm h-100">
                    <div class="card-header">
                        <h3 class="card-title fw-bold"><i class="fa-solid fa-key me-2"></i>Alterar Senha</h3>
                    </div>
                    <form action="{{ route('profile.password.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Senha Atual</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i
                                            class="fa-solid fa-lock-open text-muted small"></i></span>
                                    <input type="password" name="current_password"
                                        class="form-control @error('current_password') is-invalid @enderror" required>
                                </div>
                                @error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Nova Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i
                                            class="fa-solid fa-lock text-muted small"></i></span>
                                    <input type="password" name="password"
                                        class="form-control @error('password') is-invalid @enderror" required>
                                </div>
                                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-0">
                                <label class="form-label fw-bold small">Confirmar Nova Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i
                                            class="fa-solid fa-check text-muted small"></i></span>
                                    <input type="password" name="password_confirmation" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 d-flex justify-content-end">
                            <button type="submit" class="btn btn-warning rounded-pill px-4 shadow-sm fw-bold">
                                <i class="fa-solid fa-shield-halved me-2"></i>Atualizar Senha
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection