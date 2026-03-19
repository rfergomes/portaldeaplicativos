@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Editar Aquisição #AQ_{{ $aquisicao->id }}
            </h1>
            <p class="text-muted m-0">Altere os dados fiscais e de origem desta compra.</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('ativos.aquisicoes.index') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar
            </a>
        </div>
    </div>

    <form action="{{ route('ativos.aquisicoes.update', $aquisicao->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom text-primary fw-bold">
                <i class="fa-solid fa-barcode me-2"></i>Dados do Cabeçalho da Nota
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-3">
                        <label for="numero_nf" class="text-muted small fw-bold mb-1">Número da Nota</label>
                        <input type="text" name="numero_nf" class="form-control" id="numero_nf" value="{{ old('numero_nf', $aquisicao->numero_nf) }}">
                    </div>
                    <div class="col-md-6">
                        <label for="chave_acesso" class="text-muted small fw-bold mb-1">Chave de Acesso</label>
                        <input type="text" name="chave_acesso" class="form-control font-monospace" id="chave_acesso" value="{{ old('chave_acesso', $aquisicao->chave_acesso) }}" maxlength="44">
                    </div>
                    <div class="col-md-3">
                        <label for="data_aquisicao" class="text-muted small fw-bold mb-1">Data da Compra *</label>
                        <input type="date" name="data_aquisicao" class="form-control" id="data_aquisicao" value="{{ old('data_aquisicao', $aquisicao->data_aquisicao ? $aquisicao->data_aquisicao->format('Y-m-d') : '') }}" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="fornecedor_id" class="text-muted small fw-bold mb-1">Fornecedor Emissor</label>
                        <select name="fornecedor_id" id="fornecedor_id" class="form-select">
                            <option value="">Sem fornecedor / Origem Vária</option>
                            @foreach($fornecedores as $forn)
                                <option value="{{ $forn->id }}" {{ old('fornecedor_id', $aquisicao->fornecedor_id) == $forn->id ? 'selected' : '' }}>{{ $forn->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="marketplace_id" class="text-muted small fw-bold mb-1">Marketplace / Plataforma</label>
                        <select name="marketplace_id" id="marketplace_id" class="form-select">
                            <option value="">(Nenhum / Compra Direta)</option>
                            @foreach($marketplaces as $mkt)
                                <option value="{{ $mkt->id }}" {{ old('marketplace_id', $aquisicao->marketplace_id) == $mkt->id ? 'selected' : '' }}>{{ $mkt->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="valor_frete" class="text-muted small fw-bold mb-1">Custo de Frete (R$)</label>
                        <input type="number" step="0.01" name="valor_frete" class="form-control" id="valor_frete" value="{{ old('valor_frete', $aquisicao->valor_frete) }}">
                    </div>
                    
                    <div class="col-md-4">
                        <label for="valor_total" class="text-muted small fw-bold mb-1">Valor Total da Nota (R$)</label>
                        <input type="number" step="0.01" name="valor_total" class="form-control" id="valor_total" value="{{ old('valor_total', $aquisicao->valor_total) }}">
                    </div>

                    <div class="col-md-12">
                        <label for="observacao" class="text-muted small fw-bold mb-1">Observações / Lincagem</label>
                        <textarea name="observacao" class="form-control" id="observacao" style="height: 60px">{{ old('observacao', $aquisicao->observacao) }}</textarea>
                    </div>
                </div>
            </div>
            
            <div class="card-footer bg-light p-3 text-end">
                <i class="fa-solid fa-circle-info text-info me-2 small"></i><span class="small text-muted me-3">Nota: Alterar o fornecedor, marketplace ou data aplicará essas mudanças em todos os equipamentos vinculados a esta NF automaticamente.</span>
                <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                    <i class="fa-solid fa-save me-2"></i>Salvar Cabeçalho
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4 bg-light">
            <div class="card-body p-4 text-center text-muted">
                <i class="fa-solid fa-laptop text-secondary fs-4 mb-2"></i>
                <h6 class="fw-bold mb-0">Esta NFE possui {{ $aquisicao->equipamentos()->count() }} item(s).</h6>
                <small>Para modificar os dados específicos de uma máquina ou patrimônio, vá até o menu <b>Inventário de Equipamentos</b> e edite o equipamento individualmente.</small>
            </div>
        </div>
    </form>
</div>
@endsection
