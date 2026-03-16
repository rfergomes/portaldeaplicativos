@extends('layouts.app')

@section('title', 'Acesso Negado')

@section('content')
<div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 70vh;">
    <div class="text-center p-5 rounded-4 shadow-lg bg-white border border-danger border-opacity-25" style="max-width: 600px;">
        <div class="mb-4">
            <i class="fa-solid fa-shield-halved text-danger" style="font-size: 8rem; opacity: 0.15;"></i>
            <div class="position-absolute start-50 translate-middle-x" style="margin-top: -5rem;">
                <i class="fa-solid fa-lock text-danger fa-4x shadow-sm p-3 bg-white rounded-circle border border-danger border-3"></i>
            </div>
        </div>
        
        <h1 class="display-1 fw-bold text-danger mb-0">403</h1>
        <h2 class="fw-bold text-dark mb-4">Ação Não Autorizada</h2>
        
        <p class="text-muted fs-5 mb-5">
            Desculpe, mas sua conta não possui as permissões necessárias para realizar esta operação ou acessar este recurso.
        </p>
        
        <div class="d-flex gap-3 justify-content-center">
            <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg px-4 rounded-pill fw-bold">
                <i class="fa-solid fa-arrow-left me-2"></i> Voltar
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg px-4 rounded-pill fw-bold shadow">
                <i class="fa-solid fa-house me-2"></i> Ir para a Home
            </a>
        </div>
        
        <div class="mt-5 pt-4 border-top">
            <small class="text-muted">
                Se você acredita que isso é um erro, entre em contato com o administrador do sistema.
            </small>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    body {
        background-color: #f8fafc !important;
    }
    [data-bs-theme="dark"] .bg-white {
        background-color: var(--bs-secondary-bg) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
    [data-bs-theme="dark"] .text-dark {
        color: #f8fafc !important;
    }
</style>
@endpush
