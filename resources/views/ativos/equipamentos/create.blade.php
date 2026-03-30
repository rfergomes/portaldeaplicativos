@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('ativos.equipamentos.index') }}" class="btn btn-white border shadow-sm me-3">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Novo Equipamento</h1>
                    <p class="text-muted small mb-0">Preencha os dados técnicos e de aquisição do ativo.</p>
                </div>
            </div>

            <form action="{{ route('ativos.equipamentos.store') }}" method="POST" class="card shadow-sm border-0">
                @csrf
                <div class="card-body">
                    
                    <div class="row g-3">
                        <h5 class="card-title mt-5 pb-2 border-bottom bg-secondary text-white rounded p-2">Informações Básicas</h5>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Descrição Curta</label>
                            <input type="text" name="descricao" class="form-control shadow-none" placeholder="Ex: Notebook Dell Latitude 3420" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Fabricante</label>
                            <select name="fabricante_id" class="form-select shadow-none">
                                <option value="">Selecione o Fabricante</option>
                                @foreach($fabricantes as $fab)
                                    <option value="{{ $fab->id }}">{{ $fab->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Modelo</label>
                            <input type="text" name="modelo" class="form-control shadow-none" placeholder="Ex: i5 11th Gen, 16GB RAM">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Número de Série (S/N)</label>
                            <input type="text" name="numero_serie" class="form-control shadow-none" placeholder="Serial Number do fabricante">
                        </div>
                    </div>

                    
                    <div class="row g-3">
                        <h5 class="card-title mt-5 pb-2 border-bottom bg-secondary text-white rounded p-2">Localização / Estação de Trabalho</h5>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Estação de Trabalho</label>
                            <select name="estacao_id" class="form-select shadow-none">
                                <option value="">Sem estação definida (Em estoque)</option>
                                @foreach($departamentos as $depto)
                                    <optgroup label="{{ $depto->nome }}">
                                        @foreach($depto->estacoes as $esta)
                                            <option value="{{ $esta->id }}">{{ $esta->nome }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <div class="form-text small">Selecione para qual estação de trabalho este item será alocado.</div>
                        </div>
                    </div>

                    
                    <div class="row g-3">
                        <h5 class="card-title mt-5 pb-2 border-bottom bg-secondary text-white rounded p-2">Dados de Aquisição</h5>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Fornecedor</label>
                            <select name="fornecedor_id" class="form-select shadow-none">
                                <option value="">Selecione o Fornecedor</option>
                                @foreach($fornecedores as $forn)
                                    <option value="{{ $forn->id }}">{{ $forn->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Data da Compra</label>
                            <input type="date" name="data_compra" class="form-control shadow-none">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Garantia (Meses)</label>
                            <input type="number" name="garantia_meses" class="form-control shadow-none" placeholder="0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Custo Unitário (R$)</label>
                            <input type="number" step="0.01" name="valor_item" class="form-control shadow-none" placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Número da Nota Fiscal</label>
                            <input type="text" name="valor_nota" class="form-control shadow-none" placeholder="Nº NF-e">
                        </div>
                    </div>

                    
                    <div class="row g-3">
                        <h5 class="card-title mt-5 pb-2 border-bottom bg-secondary text-white rounded p-2">Acessórios e Observações</h5>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Acessórios Inclusos</label>
                            <input type="text" name="acessorios" class="form-control shadow-none" placeholder="Ex: Mouse, Teclado, Fonte de Energia, Maleta">
                            <div class="form-text small">Especifique os acessórios que vêm junto com este equipamento.</div>
                        </div>
                        <div class="col-md-12 mt-3">
                            <label class="form-label small fw-bold">Observações Adicionais</label>
                            <textarea name="observacao" class="form-control shadow-none" rows="3" placeholder="Detalhes adicionais, histórico de compras, avarias prévias..."></textarea>
                        </div>
                    </div>

                    
                    <div class="row g-3">
                        <h5 class="card-title mt-5 pb-2 border-bottom bg-secondary text-white rounded p-2">Cálculo de Depreciação (Contábil)</h5>
                        <div class="col-md-12 mb-2">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_depreciavel" id="is_depreciavel" value="1" checked>
                                <label class="form-check-label fw-bold small" for="is_depreciavel">Este item está sujeito a depreciação?</label>
                            </div>
                        </div>

                        <div id="depreciation_fields" class="row g-3 m-0 p-0">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Categoria</label>
                                <select name="categoria_depreciacao" id="categoria_depreciacao" class="form-select shadow-none">
                                    <option value="">Selecione a Categoria</option>
                                    <option value="informatica">Informática (Padrão 5 anos)</option>
                                    <option value="telefonia">Telefonia (Padrão 10 anos)</option>
                                    <option value="outros">Outros / Personalizado</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Vida Útil (Meses)</label>
                                <input type="number" name="vida_util_meses" id="vida_util_meses" class="form-control shadow-none" value="60" placeholder="60">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Valor Residual (R$)</label>
                                <input type="number" step="0.01" name="valor_residual" class="form-control shadow-none" value="1.00" placeholder="1.00">
                                <div class="form-text small">Valor mínimo após depreciação total.</div>
                            </div>
                            <input type="hidden" name="metodo_depreciacao" value="linear">
                        </div>
                    </div>
                </div>

                @push('scripts')
                <script>
                    document.getElementById('is_depreciavel').addEventListener('change', function() {
                        const fields = document.getElementById('depreciation_fields');
                        fields.style.opacity = this.checked ? '1' : '0.5';
                        fields.querySelectorAll('input, select').forEach(el => el.disabled = !this.checked);
                    });

                    document.getElementById('categoria_depreciacao').addEventListener('change', function() {
                        const lifeInput = document.getElementById('vida_util_meses');
                        if (this.value === 'informatica') {
                            lifeInput.value = 60;
                        } else if (this.value === 'telefonia') {
                            lifeInput.value = 120;
                        }
                    });
                </script>
                @endpush
                <div class="card-footer bg-light py-3 text-end">
                    <a href="{{ route('ativos.equipamentos.index') }}" class="btn btn-light border me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Salvar Equipamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
