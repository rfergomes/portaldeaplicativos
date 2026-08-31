@extends('layouts.app')

@section('title', 'Nova Convenção Coletiva')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col-sm-6">
            <h1 class="h4 mb-0 text-gray-800 fw-bold">
                <i class="fa-solid fa-plus-circle text-primary me-2"></i>Nova Convenção Coletiva
            </h1>
            <p class="text-muted small mb-0">Cadastre o instrumento coletivo e configure suas vigências e data-base</p>
        </div>
        <div class="col-sm-6 text-end">
            <a href="{{ route('admin.convencoes.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Voltar
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="m-0 fw-bold text-primary">Dados da Convenção Coletiva de Trabalho</h6>
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

                    <form action="{{ route('admin.convencoes.store') }}" method="POST">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-secondary">Título da Convenção <span class="text-danger">*</span></label>
                                <input type="text" name="titulo" class="form-control @error('titulo') is-invalid @enderror" 
                                    value="{{ old('titulo') }}" placeholder="Ex: Convenção Coletiva de Trabalho 2025/2027" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-secondary">Categoria Representada <span class="text-danger">*</span></label>
                                <select name="categoria" id="categoriaSelect" class="form-select @error('categoria') is-invalid @enderror" required onchange="ajustarDataBasePadrao()">
                                    <option value="QUIMICA" {{ old('categoria') === 'QUIMICA' ? 'selected' : '' }}>Química (Dissídio Nov)</option>
                                    <option value="FARMACEUTICA" {{ old('categoria') === 'FARMACEUTICA' ? 'selected' : '' }}>Farmacêutica (Dissídio Abr)</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-secondary">Início da Vigência <span class="text-danger">*</span></label>
                                <input type="date" name="vigencia_inicio" class="form-control @error('vigencia_inicio') is-invalid @enderror" 
                                    value="{{ old('vigencia_inicio') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-secondary">Término da Vigência <span class="text-danger">*</span></label>
                                <input type="date" name="vigencia_fim" class="form-control @error('vigencia_fim') is-invalid @enderror" 
                                    value="{{ old('vigencia_fim') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-secondary">Data-Base (Mês) <span class="text-danger">*</span></label>
                                <input type="text" name="data_base" id="dataBaseInput" class="form-control @error('data_base') is-invalid @enderror" 
                                    value="{{ old('data_base', 'Novembro') }}" placeholder="Ex: Novembro ou Abril" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-secondary">Abrangência Territorial e Econômica</label>
                                <textarea name="abrangencia" rows="3" class="form-control @error('abrangencia') is-invalid @enderror" 
                                    placeholder="Descreva as categorias profissionais/econômicas e os municípios abrangidos...">{{ old('abrangencia') }}</textarea>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch pt-2">
                                    <input class="form-check-input" type="checkbox" name="ativo" id="ativoCheck" value="1" {{ old('ativo', '1') ? 'checked' : '' }}>
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
                            <a href="{{ route('admin.convencoes.index') }}" class="btn btn-light rounded-pill px-4 me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Salvar Convenção
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function ajustarDataBasePadrao() {
        const cat = document.getElementById('categoriaSelect').value;
        const dbInput = document.getElementById('dataBaseInput');
        if (cat === 'QUIMICA' && !dbInput.value || dbInput.value === 'Abril') {
            dbInput.value = 'Novembro';
        } else if (cat === 'FARMACEUTICA' && !dbInput.value || dbInput.value === 'Novembro') {
            dbInput.value = 'Abril';
        }
    }
</script>
@endpush
@endsection
