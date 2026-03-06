@extends('layouts.app')

@section('title', 'Troca de Senha Obrigatória')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header text-center py-4">
                        <h4 class="fw-bold mb-0 text-primary">Segurança da Conta</h4>
                        <p class="text-muted small mb-0">Para sua segurança, é necessário alterar sua senha temporária.</p>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('password.change.update') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold small">Nova Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i
                                            class="fa-solid fa-lock text-muted"></i></span>
                                    <input type="password" name="password" id="password"
                                        class="form-control @error('password') is-invalid @enderror" required
                                        autocomplete="new-password" placeholder="Digite sua nova senha">
                                </div>
                                @error('password')
                                    <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                                @enderror
                                <div class="form-text small">Use pelo menos 8 caracteres com letras e números.</div>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label fw-bold small">Confirmar Nova
                                    Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i
                                            class="fa-solid fa-check text-muted"></i></span>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-control" required autocomplete="new-password"
                                        placeholder="Confirme sua nova senha">
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary rounded-pill py-2 fw-bold">
                                    <i class="fa-solid fa-save me-2"></i>Salvar Nova Senha
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer bg-light text-center py-3">
                        <small class="text-muted">Após alterar a senha, você terá acesso total ao sistema.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection