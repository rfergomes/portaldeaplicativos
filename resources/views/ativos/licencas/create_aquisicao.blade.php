@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fa-solid fa-file-invoice-dollar me-2 text-primary"></i>Nova Aquisição de Licenças
            </h1>
            <p class="text-muted m-0">Registre a nota fiscal e adicione todas as licenças de software contidas nela.</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('ativos.licencas.index') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar
            </a>
        </div>
    </div>

    <form action="{{ route('ativos.licencas.store_aquisicao') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <!-- Bloco 1: Dados da Nota Fiscal -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="m-0 fw-bold text-primary"><i class="fa-solid fa-barcode me-2"></i>1. Cabeçalho da Nota Fiscal</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="form-floating">
                            <input type="text" name="numero_nf" class="form-control" id="numero_nf" placeholder="NF-e" value="{{ old('numero_nf') }}">
                            <label for="numero_nf" class="text-muted small fw-bold">Número da Nota (Opcional)</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" name="chave_acesso" class="form-control font-monospace" id="chave_acesso" placeholder="Chave de Acesso" value="{{ old('chave_acesso') }}" maxlength="44">
                            <label for="chave_acesso" class="text-muted small fw-bold">Chave de Acesso da DANFE (Opcional)</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating">
                            <input type="date" name="data_aquisicao" class="form-control" id="data_aquisicao" value="{{ old('data_aquisicao', date('Y-m-d')) }}" required>
                            <label for="data_aquisicao" class="text-muted small fw-bold">Data da Aquisição *</label>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-floating">
                            <select name="fornecedor_id" id="fornecedor_id" class="form-select">
                                <option value="">Selecione o Fornecedor...</option>
                                @foreach($fornecedores as $forn)
                                    <option value="{{ $forn->id }}" {{ old('fornecedor_id') == $forn->id ? 'selected' : '' }}>{{ $forn->nome }}</option>
                                @endforeach
                            </select>
                            <label for="fornecedor_id" class="text-muted small fw-bold">Fornecedor Emissor</label>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-floating">
                            <select name="marketplace_id" id="marketplace_id" class="form-select">
                                <option value="">Compra Direta / Outro</option>
                                @foreach($marketplaces as $mkt)
                                    <option value="{{ $mkt->id }}" {{ old('marketplace_id') == $mkt->id ? 'selected' : '' }}>{{ $mkt->nome }}</option>
                                @endforeach
                            </select>
                            <label for="marketplace_id" class="text-muted small fw-bold">Marketplace (Opcional)</label>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-floating">
                            <input type="number" step="0.01" name="valor_frete" class="form-control" id="valor_frete" placeholder="Frete" value="{{ old('valor_frete') }}">
                            <label for="valor_frete" class="text-muted small fw-bold">Custo de Frete (R$)</label>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-floating">
                            <input type="number" step="0.01" name="valor_total" class="form-control" id="valor_total" placeholder="Total" value="{{ old('valor_total') }}">
                            <label for="valor_total" class="text-muted small fw-bold">Valor Total da NF (R$)</label>
                        </div>
                    </div>

                    <div class="col-md-12 mt-3">
                        <label class="text-muted small fw-bold mb-1"><i class="fa-solid fa-paperclip me-1"></i>Anexos (PDF da Nota, Comprovantes...)</label>
                        <input type="file" name="anexos[]" class="form-control bg-light" multiple accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
            </div>
        </div>

        <!-- Bloco 2: Itens (Licenças) -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="m-0 fw-bold text-success"><i class="fa-solid fa-key me-2"></i>2. Licenças Adquiridas</h5>
                <button type="button" class="btn btn-sm btn-success shadow-sm" id="btn-add-item">
                    <i class="fa-solid fa-plus me-1"></i>Adicionar Licença
                </button>
            </div>
            <div class="card-body p-4 bg-light" id="items-container">
                <!-- Item 0 -->
                <div class="item-row p-4 bg-white border rounded mb-3 shadow-sm position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3 btn-remove-item" style="display: none;"></button>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <div class="form-floating">
                                <input type="text" name="itens[0][nome]" class="form-control border-success-subtle fw-bold" placeholder="Nome do Software" required>
                                <label class="text-success small fw-bold">Nome do Software / Licença *</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <select name="itens[0][fabricante_id]" class="form-select">
                                    <option value="">Selecione...</option>
                                    @foreach($fabricantes as $fab)
                                        <option value="{{ $fab->id }}">{{ $fab->nome }}</option>
                                    @endforeach
                                </select>
                                <label class="text-muted small fw-bold">Fabricante</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating">
                                <select name="itens[0][tipo_licenca]" class="form-select" required>
                                    <option value="assinatura">Assinatura / Renovável</option>
                                    <option value="vitalicia">Vitalícia / Permanente</option>
                                </select>
                                <label class="text-muted small fw-bold">Tipo de Licença *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="itens[0][chave]" class="form-control font-monospace" placeholder="Serial Key">
                                <label class="text-muted small fw-bold">Chave / Serial / Código de Ativação</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-floating">
                                <input type="number" name="itens[0][quantidade_seats]" class="form-control" min="1" value="1" required>
                                <label class="text-primary small fw-bold">Seats (Ativações) *</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-floating">
                                <input type="number" step="0.01" name="itens[0][valor_unitario]" class="form-control text-end" placeholder="0.00" required>
                                <label class="text-success small fw-bold">R$ Unitário *</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-floating">
                                <input type="date" name="itens[0][data_validade]" class="form-control">
                                <label class="text-muted small fw-bold">Vencimento (Opc.)</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mb-5">
            <button type="submit" class="btn btn-primary btn-lg shadow px-5">
                <i class="fa-solid fa-cloud-arrow-up me-1"></i> Processar Aquisição de Licenças
            </button>
        </div>
    </form>
</div>

<!-- Template para JS -->
<template id="item-template">
    <div class="item-row p-4 bg-white border rounded mb-3 shadow-sm position-relative">
        <button type="button" class="btn-close position-absolute top-0 end-0 m-3 btn-remove-item"></button>
        <div class="row g-3">
            <div class="col-md-5">
                <div class="form-floating">
                    <input type="text" name="itens[__INDEX__][nome]" class="form-control border-success-subtle fw-bold" placeholder="Nome do Software" required>
                    <label class="text-success small fw-bold">Nome do Software / Licença *</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-floating">
                    <select name="itens[__INDEX__][fabricante_id]" class="form-select">
                        <option value="">Selecione...</option>
                        @foreach($fabricantes as $fab)
                            <option value="{{ $fab->id }}">{{ $fab->nome }}</option>
                        @endforeach
                    </select>
                    <label class="text-muted small fw-bold">Fabricante</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating">
                    <select name="itens[__INDEX__][tipo_licenca]" class="form-select" required>
                        <option value="assinatura">Assinatura / Renovável</option>
                        <option value="vitalicia">Vitalícia / Permanente</option>
                    </select>
                    <label class="text-muted small fw-bold">Tipo de Licença *</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-floating">
                    <input type="text" name="itens[__INDEX__][chave]" class="form-control font-monospace" placeholder="Serial Key">
                    <label class="text-muted small fw-bold">Chave / Serial / Código de Ativação</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-floating">
                    <input type="number" name="itens[__INDEX__][quantidade_seats]" class="form-control" min="1" value="1" required>
                    <label class="text-primary small fw-bold">Seats (Ativações) *</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-floating">
                    <input type="number" step="0.01" name="itens[__INDEX__][valor_unitario]" class="form-control text-end" placeholder="0.00" required>
                    <label class="text-success small fw-bold">R$ Unitário *</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-floating">
                    <input type="date" name="itens[__INDEX__][data_validade]" class="form-control">
                    <label class="text-muted small fw-bold">Vencimento (Opc.)</label>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnAdd = document.getElementById('btn-add-item');
        const container = document.getElementById('items-container');
        const template = document.getElementById('item-template').innerHTML;
        let itemIndex = 1;

        btnAdd.addEventListener('click', function() {
            let newItemHtml = template.replace(/__INDEX__/g, itemIndex);
            const div = document.createElement('div');
            div.innerHTML = newItemHtml;
            const newNode = div.firstElementChild;
            
            newNode.querySelector('.btn-remove-item').addEventListener('click', () => {
                newNode.remove();
                updateButtons();
            });

            container.appendChild(newNode);
            itemIndex++;
            updateButtons();
        });

        function updateButtons() {
            const rows = container.querySelectorAll('.item-row');
            rows.forEach(row => {
                row.querySelector('.btn-remove-item').style.display = rows.length > 1 ? 'block' : 'none';
            });
        }
    });
</script>
@endsection
