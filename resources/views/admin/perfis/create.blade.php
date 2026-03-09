@extends('layouts.app')

@section('title', 'Configurar Perfil')

@section('content')
<div class="container-fluid">
    <form action="{{ isset($perfil) ? route('perfis.update', $perfil) : route('perfis.store') }}" method="POST">
        @csrf
        @if(isset($perfil)) @method('PUT') @endif

        <div class="row">
            <!-- Dados do Perfil -->
            <div class="col-md-4">
                <div class="card card-outline card-primary shadow-sm">
                    <div class="card-header"><h3 class="card-title fw-bold">Dados do Perfil</h3></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome</label>
                            <input type="text" name="nome" class="form-control" value="{{ old('nome', $perfil->nome ?? '') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descrição</label>
                            <textarea name="descricao" class="form-control" rows="3">{{ old('descricao', $perfil->descricao ?? '') }}</textarea>
                        </div>
                        <hr>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary rounded-pill shadow-sm">
                                <i class="fa-solid fa-save me-1"></i> {{ isset($perfil) ? 'Salvar Alterações' : 'Criar Perfil' }}
                            </button>
                            <a href="{{ route('perfis.index') }}" class="btn btn-link btn-sm text-muted mt-2">Cancelar</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Permissões -->
            <div class="col-md-8">
                <div class="card card-outline card-success shadow-sm">
                    <div class="card-header d-flex justify-content-between">
                        <h3 class="card-title fw-bold">Permissões de Acesso</h3>
                        <div class="ms-auto small">
                            <a href="#" onclick="$('.perm-check').prop('checked', true); return false;" class="text-success me-2 text-decoration-none">Marcar Todas</a>
                            <a href="#" onclick="$('.perm-check').prop('checked', false); return false;" class="text-danger text-decoration-none">Desmarcar Todas</a>
                        </div>
                    </div>
                    <div class="card-body p-0" style="max-height: 70vh; overflow-y: auto;">
                        <div class="accordion accordion-flush" id="permsAccordion">
                            @foreach($permissoes as $grupo => $lista)
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed py-2 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $grupo }}">
                                            <i class="fa-solid fa-folder-open me-2 text-secondary"></i> Módulo: {{ ucfirst(str_replace('_', ' ', $grupo)) }}
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $grupo }}" class="accordion-collapse collapse" data-bs-parent="#permsAccordion">
                                        <div class="accordion-body py-1">
                                            <div class="row">
                                                @foreach($lista as $perm)
                                                    <div class="col-md-6 py-2 border-bottom">
                                                        <div class="form-check form-switch m-0">
                                                            <input class="form-check-input perm-check" type="checkbox" name="permissoes[]" 
                                                                   value="{{ $perm->id }}" id="perm{{ $perm->id }}"
                                                                   {{ in_array($perm->id, $perfilPermissoes ?? []) ? 'checked' : '' }}>
                                                            <label class="form-check-label ms-2" for="perm{{ $perm->id }}">
                                                                <span class="fw-semibold d-block">{{ $perm->nome }}</span>
                                                                <small class="text-muted">{{ $perm->descricao }}</small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
