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
                    <h1 class="h3 mb-0 text-gray-800">Editar Equipamento</h1>
                    <p class="text-muted small mb-0">Atualize os dados técnicos e de aquisição do ativo.</p>
                </div>
            </div>

            <form action="{{ route('ativos.equipamentos.update', $equipamento->id) }}" method="POST" class="card shadow-sm border-0">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <h5 class="card-title mb-4 pb-2 border-bottom text-primary">Informações Básicas</h5>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Descrição Curta</label>
                            <input type="text" name="descricao" class="form-control shadow-none" value="{{ old('descricao', $equipamento->descricao) }}" placeholder="Ex: Notebook Dell Latitude 3420" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Fabricante</label>
                            <select name="fabricante_id" class="form-select shadow-none">
                                <option value="">Selecione o Fabricante</option>
                                @foreach($fabricantes as $fab)
                                    <option value="{{ $fab->id }}" {{ old('fabricante_id', $equipamento->fabricante_id) == $fab->id ? 'selected' : '' }}>{{ $fab->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Modelo</label>
                            <input type="text" name="modelo" class="form-control shadow-none" value="{{ old('modelo', $equipamento->modelo) }}" placeholder="Ex: i5 11th Gen, 16GB RAM">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Número de Série (S/N)</label>
                            <input type="text" name="numero_serie" class="form-control shadow-none" value="{{ old('numero_serie', $equipamento->numero_serie) }}" placeholder="Serial Number do fabricante">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Status</label>
                            <select name="status" class="form-select shadow-none" required>
                                <option value="disponivel" {{ old('status', $equipamento->status) == 'disponivel' ? 'selected' : '' }}>Disponível</option>
                                <option value="em_uso" {{ old('status', $equipamento->status) == 'em_uso' ? 'selected' : '' }}>Em Uso</option>
                                <option value="manutencao" {{ old('status', $equipamento->status) == 'manutencao' ? 'selected' : '' }}>Manutenção</option>
                                <option value="baixado" {{ old('status', $equipamento->status) == 'baixado' ? 'selected' : '' }}>Baixado</option>
                            </select>
                        </div>
                    </div>

                    <h5 class="card-title mt-5 mb-4 pb-2 border-bottom text-primary">Localização / Estação de Trabalho</h5>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Estação de Trabalho</label>
                            <select name="estacao_id" class="form-select shadow-none">
                                <option value="">Sem estação definida (Em estoque)</option>
                                @foreach($departamentos as $depto)
                                    <optgroup label="{{ $depto->nome }}">
                                        @foreach($depto->estacoes as $esta)
                                            <option value="{{ $esta->id }}" {{ old('estacao_id', $equipamento->estacao_id) == $esta->id ? 'selected' : '' }}>{{ $esta->nome }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <div class="form-text small">Selecione para qual estação de trabalho este item está alocado.</div>
                        </div>
                    </div>

                    <h5 class="card-title mt-5 mb-4 pb-2 border-bottom text-primary">Dados de Aquisição</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Fornecedor</label>
                            <select name="fornecedor_id" class="form-select shadow-none">
                                <option value="">Selecione o Fornecedor</option>
                                @foreach($fornecedores as $forn)
                                    <option value="{{ $forn->id }}" {{ old('fornecedor_id', $equipamento->fornecedor_id) == $forn->id ? 'selected' : '' }}>{{ $forn->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Data da Compra</label>
                            <input type="date" name="data_compra" class="form-control shadow-none" value="{{ old('data_compra', $equipamento->data_compra ? $equipamento->data_compra->format('Y-m-d') : '') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Garantia (Meses)</label>
                            <input type="number" name="garantia_meses" class="form-control shadow-none" value="{{ old('garantia_meses', $equipamento->garantia_meses) }}" placeholder="0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Custo Unitário (R$)</label>
                            <input type="number" step="0.01" name="valor_item" class="form-control shadow-none" value="{{ old('valor_item', $equipamento->valor_item) }}" placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Número da Nota Fiscal</label>
                            <input type="text" name="valor_nota" class="form-control shadow-none" value="{{ old('valor_nota', $equipamento->valor_nota) }}" placeholder="Nº NF-e">
                        </div>
                    </div>

                    <h5 class="card-title mt-5 mb-4 pb-2 border-bottom text-primary">Acessórios e Observações</h5>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Acessórios Inclusos</label>
                            <input type="text" name="acessorios" class="form-control shadow-none" value="{{ old('acessorios', $equipamento->acessorios) }}" placeholder="Ex: Mouse, Teclado, Fonte de Energia, Maleta">
                            <div class="form-text small">Especifique os acessórios que vêm junto com este equipamento.</div>
                        </div>
                        <div class="col-md-12 mt-3">
                            <label class="form-label small fw-bold">Observações Adicionais</label>
                            <textarea name="observacao" class="form-control shadow-none" rows="3" placeholder="Detalhes adicionais, histórico de compras, avarias prévias...">{{ old('observacao', $equipamento->observacao) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-3 text-end">
                    <a href="{{ route('ativos.equipamentos.index') }}" class="btn btn-light border me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Atualizar Equipamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
