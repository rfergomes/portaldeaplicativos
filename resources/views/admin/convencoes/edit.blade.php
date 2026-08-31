@extends('layouts.app')

@section('title', 'Editar Convenção Coletiva')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col-sm-6">
            <h1 class="h4 mb-0 text-gray-800 fw-bold">
                <i class="fa-solid fa-pen-to-square text-primary me-2"></i>Editar Convenção Coletiva
            </h1>
            <p class="text-muted small mb-0">{{ $convencao->titulo }}</p>
        </div>
        <div class="col-sm-6 text-end">
            <a href="{{ route('admin.convencoes.show', $convencao) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Ver Detalhes
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="m-0 fw-bold text-primary">Alterar Dados da Convenção</h6>
                </div>
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm mb-4">
                            <ul class="mb-0 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.convencoes.update', $convencao) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-secondary">Título da Convenção <span class="text-danger">*</span></label>
                                <input type="text" name="titulo" class="form-control @error('titulo') is-invalid @enderror" 
                                    value="{{ old('titulo', $convencao->titulo) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-secondary">Categoria Representada <span class="text-danger">*</span></label>
                                <select name="categoria" class="form-select @error('categoria') is-invalid @enderror" required>
                                    <option value="QUIMICA" {{ old('categoria', $convencao->categoria) === 'QUIMICA' ? 'selected' : '' }}>Química (Dissídio Nov)</option>
                                    <option value="FARMACEUTICA" {{ old('categoria', $convencao->categoria) === 'FARMACEUTICA' ? 'selected' : '' }}>Farmacêutica (Dissídio Abr)</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-secondary">Início da Vigência <span class="text-danger">*</span></label>
                                <input type="date" name="vigencia_inicio" class="form-control @error('vigencia_inicio') is-invalid @enderror" 
                                    value="{{ old('vigencia_inicio', $convencao->vigencia_inicio ? $convencao->vigencia_inicio->format('Y-m-d') : '') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-secondary">Término da Vigência <span class="text-danger">*</span></label>
                                <input type="date" name="vigencia_fim" class="form-control @error('vigencia_fim') is-invalid @enderror" 
                                    value="{{ old('vigencia_fim', $convencao->vigencia_fim ? $convencao->vigencia_fim->format('Y-m-d') : '') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-secondary">Data-Base (Mês) <span class="text-danger">*</span></label>
                                <input type="text" name="data_base" class="form-control @error('data_base') is-invalid @enderror" 
                                    value="{{ old('data_base', $convencao->data_base) }}" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-secondary">Abrangência Territorial e Econômica</label>
                                <textarea name="abrangencia" rows="3" class="form-control @error('abrangencia') is-invalid @enderror">{{ old('abrangencia', $convencao->abrangencia) }}</textarea>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch pt-2">
                                    <input class="form-check-input" type="checkbox" name="ativo" id="ativoCheck" value="1" {{ old('ativo', $convencao->ativo) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark" for="ativoCheck">
                                        Convenção Ativa no Sistema
                                    </label>
                                    <div class="form-text text-muted small">
                                        Convenções ativas são utilizadas como referência para fundamentação de lembretes e regras sindicais.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="text-end">
                            <a href="{{ route('admin.convencoes.show', $convencao) }}" class="btn btn-light rounded-pill px-4 me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
