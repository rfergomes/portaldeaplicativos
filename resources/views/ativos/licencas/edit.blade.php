@extends('layouts.app')

@section('title', 'Editar Licença')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title fw-bold mb-0">Editar Licença: {{ $licenca->nome }}</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('ativos.licencas.update', $licenca->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold small">Nome do Software</label>
                                <input type="text" name="nome" class="form-control" value="{{ $licenca->nome }}" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold small">Chave / Licença (Serial)</label>
                                <input type="text" name="chave" class="form-control" value="{{ $licenca->chave }}" placeholder="XXXXX-XXXXX-XXXXX-XXXXX">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Fabricante</label>
                                <select name="fabricante_id" class="form-select">
                                    <option value="">Selecione...</option>
                                    @foreach($fabricantes as $fab)
                                        <option value="{{ $fab->id }}" {{ $licenca->fabricante_id == $fab->id ? 'selected' : '' }}>{{ $fab->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Tipo de Licença</label>
                                <select name="tipo_licenca" class="form-select" required>
                                    <option value="assinatura" {{ $licenca->tipo_licenca == 'assinatura' ? 'selected' : '' }}>Assinatura / Renovável</option>
                                    <option value="vitalicia" {{ $licenca->tipo_licenca == 'vitalicia' ? 'selected' : '' }}>Vitalícia / Permanente</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Data de Expiração (Opcional)</label>
                                <input type="date" name="data_validade" class="form-control" value="{{ $licenca->data_validade ? $licenca->data_validade->format('Y-m-d') : '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Quantidade de Seats (Ativações)</label>
                                <input type="number" name="quantidade_seats" class="form-control" value="{{ $licenca->quantidade_seats }}" min="1" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold small">Observações</label>
                                <textarea name="observacao" class="form-control" rows="3">{{ $licenca->observacao }}</textarea>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-top text-end">
                            <a href="{{ route('ativos.licencas.index') }}" class="btn btn-light me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4">Atualizar Licença</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
