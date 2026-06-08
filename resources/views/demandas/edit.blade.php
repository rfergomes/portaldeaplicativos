@extends('layouts.app')

@section('title', 'Editar Demanda')

@push('styles')
    <style>
        .form-card {
            border-radius: 12px;
            border: none;
        }
        .btn-premium {
            background: linear-gradient(135deg, #033c5a 0%, #0b72a6 100%);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-premium:hover {
            background: linear-gradient(135deg, #0b72a6 0%, #033c5a 100%);
            color: #ffffff;
        }
    </style>
@endpush

@section('content')
    <div class="mb-4">
        <a href="{{ route('demandas.show', $demanda) }}" class="text-decoration-none text-muted">
            <i class="fa-solid fa-arrow-left me-1"></i> Voltar aos Detalhes
        </a>
        <h1 class="h3 mb-0 text-slate-800 fw-bold mt-2">Editar Demanda</h1>
    </div>

    <div class="row">
        <div class="col-lg-9 col-12 mx-auto">
            <form method="POST" action="{{ route('demandas.update', $demanda) }}" class="card form-card shadow-sm">
                @csrf
                @method('PUT')
                <div class="card-body p-4">
                    <!-- Título -->
                    <div class="mb-3">
                        <label for="titulo" class="form-label fw-bold text-secondary">Título da Demanda <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('titulo') is-invalid @enderror" id="titulo" name="titulo" value="{{ old('titulo', $demanda->titulo) }}" required>
                        @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Descrição -->
                    <div class="mb-4">
                        <label for="descricao" class="form-label fw-bold text-secondary">Descrição Detalhada <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('descricao') is-invalid @enderror" id="descricao" name="descricao" rows="6" required>{{ old('descricao', $demanda->descricao) }}</textarea>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <!-- Prazo -->
                        <div class="col-md-6 col-12">
                            <label for="prazo" class="form-label fw-bold text-secondary">Prazo Limite</label>
                            <input type="datetime-local" class="form-control @error('prazo') is-invalid @enderror" id="prazo" name="prazo" value="{{ old('prazo', $demanda->prazo ? $demanda->prazo->format('Y-m-d\TH:i') : '') }}">
                            @error('prazo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Prioridade -->
                        <div class="col-md-6 col-12">
                            <label for="prioridade" class="form-label fw-bold text-secondary">Prioridade <span class="text-danger">*</span></label>
                            <select class="form-select @error('prioridade') is-invalid @enderror" id="prioridade" name="prioridade" required>
                                <option value="baixa" {{ old('prioridade', $demanda->prioridade) == 'baixa' ? 'selected' : '' }}>Baixa</option>
                                <option value="media" {{ old('prioridade', $demanda->prioridade) == 'media' ? 'selected' : '' }}>Média</option>
                                <option value="alta" {{ old('prioridade', $demanda->prioridade) == 'alta' ? 'selected' : '' }}>Alta</option>
                                <option value="urgente" {{ old('prioridade', $demanda->prioridade) == 'urgente' ? 'selected' : '' }}>Urgente</option>
                            </select>
                            @error('prioridade')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="divider border-bottom mb-4"></div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('demandas.show', $demanda) }}" class="btn btn-light px-4">Cancelar</a>
                        <button type="submit" class="btn btn-premium px-5 py-2 shadow-sm">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Salvar Alterações
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
