@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fa-solid fa-file-invoice-dollar me-2 text-primary"></i>Nova Entrada de Estoque
            </h1>
            <p class="text-muted m-0">Preencha os dados da nota ou recibo e adicione os equipamentos recebidos.</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('ativos.aquisicoes.index') }}" class="btn btn-outline-secondary shadow-sm">
                <i class="fa-solid fa-arrow-left me-2"></i>Voltar
            </a>
        </div>
    </div>

    <form action="{{ route('ativos.aquisicoes.store') }}" method="POST" id="form-aquisicao" enctype="multipart/form-data">
        @csrf
        
        <!-- Bloco 1: Dados Gerais da Compra da Nota -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="m-0 fw-bold text-primary"><i class="fa-solid fa-barcode me-2"></i>1. Cabeçalho Principal</h5>
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
                                <option value="">Sem fornecedor / Origem Vária</option>
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
                                <option value="">Foi comprado diretamente do fornecedor</option>
                                @foreach($marketplaces as $mkt)
                                    <option value="{{ $mkt->id }}" {{ old('marketplace_id') == $mkt->id ? 'selected' : '' }}>{{ $mkt->nome }}</option>
                                @endforeach
                            </select>
                            <label for="marketplace_id" class="text-muted small fw-bold">Comprado através de Plataforma/Marketplace?</label>
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
                            <label for="valor_total" class="text-muted small fw-bold">Valor Total da Aquisição (R$)</label>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-floating">
                            <textarea name="observacao" class="form-control" id="observacao" style="height: 60px" placeholder="Obs">{{ old('observacao') }}</textarea>
                            <label for="observacao" class="text-muted small fw-bold">Observações gerais sobre a compra / link do comprovante</label>
                        </div>
                    </div>
                    <div class="col-md-12 mt-3">
                        <label class="text-muted small fw-bold mb-1"><i class="fa-solid fa-paperclip me-1"></i>Anexos (DANFE em PDF, Imagens, Recibos...)</label>
                        <input type="file" name="anexos[]" class="form-control bg-light" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
                        <div class="form-text small">Você pode selecionar vários arquivos segurando a tecla CTRL. Máx: 5MB por arquivo.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bloco 2: Múltiplos Itens (Equipamentos) -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="m-0 fw-bold text-success"><i class="fa-solid fa-boxes-stacked me-2"></i>2. Produtos / Equipamentos Adquiridos</h5>
                <button type="button" class="btn btn-sm btn-success" id="btn-add-item">
                    <i class="fa-solid fa-plus me-1"></i>Adicionar Item de NF
                </button>
            </div>
            <div class="card-body p-4 bg-light" id="items-container">
                <!-- O primeiro item é renderizado por padrão -->
                <div class="item-row p-4 bg-white border rounded mb-3 shadow-sm position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3 btn-remove-item" aria-label="Remover" style="display: none;"></button>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" name="itens[0][descricao]" class="form-control border-success-subtle" placeholder="Ex: Monitor Dell 24" required>
                                <label class="text-success fw-bold small">Descrição do Equipamento *</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-floating">
                                <input type="text" name="itens[0][modelo]" class="form-control" placeholder="Modelo">
                                <label class="text-muted small fw-bold">Modelo (Opc.)</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating">
                                <select name="itens[0][fabricante_id]" class="form-select">
                                    <option value="">(Desconhecido)</option>
                                    @foreach($fabricantes as $fab)
                                        <option value="{{ $fab->id }}">{{ $fab->nome }}</option>
                                    @endforeach
                                </select>
                                <label class="text-muted small fw-bold">Fabricante</label>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-floating">
                                <input type="number" name="itens[0][quantidade]" class="form-control bg-light" min="1" value="1" required>
                                <label class="text-primary fw-bold small">QTD *</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-floating">
                                <input type="number" step="0.01" name="itens[0][valor_unitario]" class="form-control text-end font-monospace" placeholder="0.00" required>
                                <label class="text-success fw-bold small">R$ Unitário *</label>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Fim Primeiro Item -->
            </div>
        </div>

        <div class="text-end mb-5">
            <button type="submit" class="btn btn-primary btn-lg shadow px-5">
                <i class="fa-solid fa-check me-2"></i>Finalizar e Gerar Opcionais no Estoque
            </button>
        </div>
    </form>
</div>

<!-- Template Oculto para Injeção via JS -->
<template id="item-template">
    <div class="item-row p-4 bg-white border rounded mb-3 shadow-sm position-relative item-anin">
        <button type="button" class="btn-close position-absolute top-0 end-0 m-3 btn-remove-item" aria-label="Remover"></button>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="form-floating">
                    <input type="text" name="itens[__INDEX__][descricao]" class="form-control border-success-subtle" placeholder="Ex: Monitor Dell 24" required disabled>
                    <label class="text-success fw-bold small">Descrição do Equipamento *</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-floating">
                    <input type="text" name="itens[__INDEX__][modelo]" class="form-control" placeholder="Modelo" disabled>
                    <label class="text-muted small fw-bold">Modelo (Opc.)</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating">
                    <select name="itens[__INDEX__][fabricante_id]" class="form-select" disabled>
                        <option value="">(Desconhecido)</option>
                        @foreach($fabricantes as $fab)
                            <option value="{{ $fab->id }}">{{ $fab->nome }}</option>
                        @endforeach
                    </select>
                    <label class="text-muted small fw-bold">Fabricante</label>
                </div>
            </div>
            <div class="col-md-1">
                <div class="form-floating">
                    <input type="number" name="itens[__INDEX__][quantidade]" class="form-control bg-light" min="1" value="1" required disabled>
                    <label class="text-primary fw-bold small">QTD *</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-floating">
                    <input type="number" step="0.01" name="itens[__INDEX__][valor_unitario]" class="form-control text-end font-monospace" placeholder="0.00" required disabled>
                    <label class="text-success fw-bold small">R$ Unitário *</label>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
    .form-floating > .form-select {
        height: 58px !important;
        padding-top: 1.625rem !important;
        padding-bottom: 0.625rem !important;
    }
    .item-anin {
        animation: fadeIn 0.3s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnAdd = document.getElementById('btn-add-item');
        const container = document.getElementById('items-container');
        const template = document.getElementById('item-template').innerHTML;
        let itemIndex = 1; // Já temos o [0] criado por padrão

        btnAdd.addEventListener('click', function() {
            // Substitui os prefixos __INDEX__ pelo índice real
            let newItemHtml = template.replace(/__INDEX__/g, itemIndex);
            
            // Remove o atributo 'disabled' que colocamos no template para evitar submit por erro
            newItemHtml = newItemHtml.replace(/disabled/g, '');
            
            // Cria um Node Wrapper para o HTML injetado
            const div = document.createElement('div');
            div.innerHTML = newItemHtml;
            const newItemNode = div.firstElementChild;
            
            // Adiciona evento de remoção
            newItemNode.querySelector('.btn-remove-item').addEventListener('click', function() {
                newItemNode.remove();
                updateRemoveButtons();
            });

            container.appendChild(newItemNode);
            itemIndex++;
            updateRemoveButtons();
        });

        function updateRemoveButtons() {
            const rows = container.querySelectorAll('.item-row');
            // Só mostra o botão de fechar se houver mais de um item
            rows.forEach((row, idx) => {
                const btnClose = row.querySelector('.btn-remove-item');
                if (rows.length > 1) {
                    btnClose.style.display = 'block';
                } else {
                    btnClose.style.display = 'none';
                }
            });
        }
    });
</script>
@endsection
