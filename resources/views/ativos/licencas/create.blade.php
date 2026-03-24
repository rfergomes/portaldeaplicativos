@extends('layouts.app')

@section('title', 'Nova Licença')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title fw-bold mb-0">Cadastro de Licença de Software</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('ativos.licencas.store') }}" method="POST">
                        @csrf
                        
                        {{-- Mensagens de Erro --}}
                        @if ($errors->any())
                            <div class="alert alert-danger mb-4">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <h6 class="fw-bold mb-3 text-primary">Informações do Software</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label class="form-label fw-bold small">Nome do Software</label>
                                <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome') }}" placeholder="Ex: Microsoft 365 Business, Adobe Creative Cloud" required>
                                @error('nome') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Fabricante</label>
                                <select name="fabricante_id" class="form-select @error('fabricante_id') is-invalid @enderror">
                                    <option value="">Selecione...</option>
                                    @foreach($fabricantes as $fab)
                                        <option value="{{ $fab->id }}" {{ old('fabricante_id') == $fab->id ? 'selected' : '' }}>{{ $fab->nome }}</option>
                                    @endforeach
                                </select>
                                @error('fabricante_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold small">Chave / Licença (Serial)</label>
                                <input type="text" name="chave" class="form-control @error('chave') is-invalid @enderror" value="{{ old('chave') }}" placeholder="XXXXX-XXXXX-XXXXX-XXXXX">
                                @error('chave') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Tipo de Licença</label>
                                <select name="tipo_licenca" class="form-select @error('tipo_licenca') is-invalid @enderror" required>
                                    <option value="assinatura" {{ old('tipo_licenca') == 'assinatura' ? 'selected' : '' }}>Assinatura / Renovável</option>
                                    <option value="vitalicia" {{ old('tipo_licenca') == 'vitalicia' ? 'selected' : '' }}>Vitalícia / Permanente</option>
                                </select>
                                @error('tipo_licenca') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Data de Expiração (Opcional)</label>
                                <input type="date" name="data_validade" class="form-control @error('data_validade') is-invalid @enderror" value="{{ old('data_validade') }}">
                                @error('data_validade') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Quantidade de Seats (Ativações)</label>
                                <input type="number" name="quantidade_seats" class="form-control @error('quantidade_seats') is-invalid @enderror" value="{{ old('quantidade_seats', 1) }}" min="1" required>
                                @error('quantidade_seats') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3 text-primary">Informações de Aquisição (NF)</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Fornecedor</label>
                                <select name="fornecedor_id" class="form-select @error('fornecedor_id') is-invalid @enderror">
                                    <option value="">Selecione o fornecedor...</option>
                                    @foreach($fornecedores as $fornecedor)
                                        <option value="{{ $fornecedor->id }}" {{ old('fornecedor_id') == $fornecedor->id ? 'selected' : '' }}>{{ $fornecedor->nome }}</option>
                                    @endforeach
                                </select>
                                @error('fornecedor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Marketplace (Opcional)</label>
                                <select name="marketplace_id" class="form-select @error('marketplace_id') is-invalid @enderror">
                                    <option value="">Selecione...</option>
                                    @foreach($marketplaces as $mkt)
                                        <option value="{{ $mkt->id }}" {{ old('marketplace_id') == $mkt->id ? 'selected' : '' }}>{{ $mkt->nome }}</option>
                                    @endforeach
                                </select>
                                @error('marketplace_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Número da NF</label>
                                <input type="text" name="numero_nf" class="form-control @error('numero_nf') is-invalid @enderror" value="{{ old('numero_nf') }}" placeholder="Ex: 123456">
                                @error('numero_nf') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold small">Chave de Acesso (NF-e)</label>
                                <input type="text" name="chave_acesso" class="form-control @error('chave_acesso') is-invalid @enderror" value="{{ old('chave_acesso') }}" placeholder="44 dígitos">
                                @error('chave_acesso') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Data da Compra</label>
                                <input type="date" name="data_aquisicao" class="form-control @error('data_aquisicao') is-invalid @enderror" value="{{ old('data_aquisicao') }}">
                                @error('data_aquisicao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Valor Total (R$)</label>
                                <input type="number" step="0.01" name="valor_total" class="form-control @error('valor_total') is-invalid @enderror" value="{{ old('valor_total') }}" placeholder="0,00">
                                @error('valor_total') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Valor do Frete (R$)</label>
                                <input type="number" step="0.01" name="valor_frete" class="form-control @error('valor_frete') is-invalid @enderror" value="{{ old('valor_frete') }}" placeholder="0,00">
                                @error('valor_frete') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3 text-primary">Outras Informações</h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold small">Observações</label>
                                <textarea name="observacao" class="form-control @error('observacao') is-invalid @enderror" rows="3">{{ old('observacao') }}</textarea>
                                @error('observacao') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top text-end">
                            <a href="{{ route('ativos.licencas.index') }}" class="btn btn-light me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="bi bi-save me-1"></i> Salvar Licença
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
