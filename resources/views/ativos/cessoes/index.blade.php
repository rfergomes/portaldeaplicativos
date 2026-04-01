@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fa-solid fa-file-signature me-2 text-primary"></i>Gerenciar Cessões
            </h1>
            <p class="text-muted">Gestão de termos de cessão, múltiplos itens e documentos assinados.</p>
        </div>
        <div class="col-md-6 text-end">
             <button type="button" class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#modalNovaCessaoNotaFiscal">
                <i class="fa-solid fa-file-invoice me-1"></i> Cessão por NF
            </button>
             <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovaCessaoMultipla">
                <i class="fa-solid fa-plus me-1"></i> Nova Cessão Múltipla
            </button>
        </div>
    </div>

    <!-- Cards Informativos -->
    <div class="row g-3 mb-4">
        <!-- Card 1 -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="text-xs fw-bold text-primary text-uppercase">Total de Termos</div>
                        <i class="fa-solid fa-file-signature text-primary opacity-50 mt-1" style="font-size: 1.2rem;"></i>
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalTermos }}</div>
                </div>
            </div>
        </div>
        <!-- Card 2 -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="text-xs fw-bold text-success text-uppercase">Itens Cedidos (Ativos)</div>
                        <i class="fa-solid fa-laptop text-success opacity-50 mt-1" style="font-size: 1.2rem;"></i>
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $itensCedidos }}</div>
                </div>
            </div>
        </div>
        <!-- Card 3 -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="text-xs fw-bold text-info text-uppercase">Cessionários Atendidos</div>
                        <i class="fa-solid fa-users text-info opacity-50 mt-1" style="font-size: 1.2rem;"></i>
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $cessionariosUnicos }}</div>
                </div>
            </div>
        </div>
        <!-- Card 4 -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="text-xs fw-bold text-danger text-uppercase">Devoluções Atrasadas</div>
                        <i class="fa-solid fa-clock-rotate-left text-danger opacity-50 mt-1" style="font-size: 1.2rem;"></i>
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $devolucoesAtrasadas }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('ativos.cessoes.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">ID da Cessão</label>
                    <input type="text" name="search" class="form-control" placeholder="Ex: CSN001" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Filtrar por Cessionário</label>
                    <select name="usuario_id" class="form-select select2">
                        <option value="">Todos</option>
                        @foreach($usuarios as $user)
                            <option value="{{ $user->id }}" {{ request('usuario_id') == $user->id ? 'selected' : '' }}>{{ $user->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Data de Início</label>
                    <input type="date" name="data_inicio" class="form-control" value="{{ request('data_inicio') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Data de Fim</label>
                    <input type="date" name="data_fim" class="form-control" value="{{ request('data_fim') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary">Buscar</button>
                        <a href="{{ route('ativos.cessoes.index') }}" class="btn btn-secondary">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Listagem -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">ID da Cessão</th>
                            <th>Data</th>
                            <th>Cessionário</th>
                            <th class="text-center">Termo Gerado</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($cessoes->count() > 0)
                        @foreach($cessoes as $cessao)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold">{{ $cessao->codigo_cessao }}</span>
                                <div class="x-small text-muted">{{ $cessao->movimentacoes->count() }} item(ns)</div>
                            </td>
                            <td>
                                <div>{{ $cessao->data_cessao->format('d/m/Y') }}</div>
                                <div class="small text-muted">{{ $cessao->data_cessao->format('H:i:s') }}</div>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $cessao->usuario->nome }}</div>
                                <div class="small text-muted">{{ $cessao->usuario->empresa->razao_social ?? 'S/ Empresa' }}</div>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('ativos.cessoes.pdf', $cessao->id) }}" target="_blank" class="btn btn-sm {{ $cessao->termo_pdf_path ? 'btn-outline-danger' : 'btn-outline-secondary' }}" title="{{ $cessao->termo_pdf_path ? 'Visualizar PDF' : 'Gerar PDF' }}">
                                    <i class="fa-solid fa-file-pdf"></i> {{ $cessao->termo_pdf_path ? '' : 'Gerar' }}
                                </a>
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalDetalhes{{ $cessao->id }}">
                                    <i class="fa-solid fa-eye me-1"></i> Ver Detalhes
                                </button>
                            </td>
                        </tr>

                        @endforeach
                        @else
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Nenhuma cessão registrada.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @if($cessoes->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $cessoes->links() }}
        </div>
        @endif
    </div>
</div>

@foreach($cessoes as $cessao)
<!-- Modal de Detalhes -->
<div class="modal fade" id="modalDetalhes{{ $cessao->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 shadow-sm bg-light">
                <h5 class="modal-title fw-bold">Detalhes da Cessão: {{ $cessao->codigo_cessao }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-4">
                    <div class="col-md-6 border-start border-4 border-primary ps-3">
                        <div class="small fw-bold text-muted text-uppercase mb-1">Cessionário</div>
                        <div class="h6 mb-0 fw-bold">{{ $cessao->usuario->nome }}</div>
                        <div class="small text-muted">{{ $cessao->usuario->empresa->razao_social ?? 'S/ Empresa' }}</div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="small fw-bold text-muted text-uppercase mb-1">Data da Operação</div>
                        <div class="h6 mb-0">{{ $cessao->data_cessao->format('d/m/Y H:i:s') }}</div>
                    </div>
                </div>

                <!-- Itens da Cessão -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="mb-0 fw-bold text-primary">
                            <i class="fa-solid fa-box-open me-2"></i>Itens Vinculados
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr style="font-size: 0.7rem;" class="text-muted text-uppercase">
                                        <th class="ps-3">Patrimônio / ID</th>
                                        <th>Descrição</th>
                                        <th class="pe-3">Marca/Modelo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cessao->movimentacoes as $mov)
                                    <tr>
                                        <td class="ps-3"><span class="badge text-bg-light border">#{{ optional($mov->equipamento)->id }}</span></td>
                                        <td class="small fw-bold">{{ optional($mov->equipamento)->descricao ?? 'Equipamento Removido' }}</td>
                                        <td class="small text-muted pe-3">
                                            {{ optional(optional($mov->equipamento)->fabricante)->nome ?? '-' }} / {{ optional($mov->equipamento)->modelo ?? '-' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h6 class="mb-0 fw-bold">
                            <i class="fa-solid fa-paperclip me-2 text-primary"></i>Anexos / Documentos
                        </h6>
                    </div>
                    <div class="card-body pt-0">
                        <ul class="list-group list-group-flush">
                            @forelse($cessao->anexos as $anexo)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div class="d-flex align-items-center">
                                        <i class="fa-solid fa-file-pdf text-danger me-2 shadow-sm"></i>
                                        <small class="fw-bold">{{ $anexo->nome_original }}</small>
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('ativos.anexos.download', [$anexo->id, Str::slug(pathinfo($anexo->nome_original, PATHINFO_FILENAME)) . '.' . pathinfo($anexo->nome_original, PATHINFO_EXTENSION)]) }}" target="_blank" class="btn btn-link text-primary p-0 me-3" title="Baixar/Visualizar">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <form action="{{ route('ativos.anexos.destroy', $anexo->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger p-0" onclick="return confirm('Excluir anexo?')">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-center py-4 text-muted border-0">
                                    <i class="fa-solid fa-folder-open d-block mb-2 opacity-50 h4"></i>
                                    Nenhum anexo disponível.
                                </li>
                            @endforelse
                        </ul>

                        <form action="{{ route('ativos.cessoes.anexos.store', $cessao->id) }}" method="POST" enctype="multipart/form-data" class="mt-4 p-3 bg-light rounded-3 border border-dashed">
                            @csrf
                            <label class="form-label small fw-bold text-muted text-uppercase mb-2">Enviar Novo Documento</label>
                            <div class="input-group input-group-sm">
                                <input type="file" name="arquivo" class="form-control" required>
                                <button class="btn btn-primary px-3 fw-bold" type="submit">
                                    <i class="fa-solid fa-upload me-1"></i> Anexar
                                </button>
                            </div>
                            <div class="form-text x-small text-muted mt-2">Formatos aceitos: PDF, JPG, PNG (máx 5mb).</div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
@endforeach

<!-- Modal Nova Cessão Múltipla -->
<div class="modal fade" id="modalNovaCessaoMultipla" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Cessão Múltipla: Selecionar Ativos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNovaCessaoMultipla">
                <div class="modal-body pt-4">
                    <!-- Step 1: Selecionar Ativos -->
                    <div id="step1">
                        <p class="text-muted small mb-3">Selecione os ativos com status "Disponível" que deseja emprestar.</p>
                        
                        <div class="mb-3">
                            <input type="text" id="filtroTabelaMultipla" class="form-control form-control-sm" placeholder="Buscar por ID, Descrição ou Modelo...">
                        </div>

                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-sm table-hover align-middle">
                                <thead class="bg-light sticky-top">
                                    <tr>
                                        <th style="width: 40px;">
                                            <input type="checkbox" class="form-check-input" id="checkAll">
                                        </th>
                                        <th>ID do Ativo</th>
                                        <th>Descrição</th>
                                        <th>Modelo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $disponiveis = \App\Models\AtivoEquipamento::where('status', 'disponivel')->orderBy('id', 'desc')->get();
                                    @endphp
                                    @forelse($disponiveis as $equip)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="equipamentos[]" value="{{ $equip->id }}" class="form-check-input equip-check">
                                        </td>
                                        <td><span class="badge text-bg-light border">#{{ $equip->id }}</span></td>
                                        <td>{{ $equip->descricao }}</td>
                                        <td>{{ $equip->modelo ?? '-' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Nenhum equipamento disponível no momento.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Step 2: Informar Destino -->
                    <div id="step2" style="display: none;">
                        <h6 class="fw-bold mb-4">Informar Destino</h6>
                        
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Cessionário (Destino)</label>
                                <select id="usuario_id_multiplo" class="form-select select2-modal" required>
                                    <option value="">Selecione um destino...</option>
                                    @foreach($usuarios as $user)
                                        <option value="{{ $user->id }}">{{ $user->nome }} ({{ $user->empresa->razao_social ?? 'S/ Empresa' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Data de Devolução Prevista (Opcional)</label>
                                <input type="date" id="data_previsao_multiplo" class="form-control">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Observações</label>
                                <textarea id="observacoes_multiplo" class="form-control" rows="3" placeholder="Notas adicionais sobre a cessão..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnNext">Avançar</button>
                    <button type="button" class="btn btn-primary" id="btnPrev" style="display: none;">Voltar</button>
                    <button type="submit" class="btn btn-success" id="btnSubmit" style="display: none;">Confirmar e Gerar Termo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Nova Cessão por Nota Fiscal -->
<div class="modal fade" id="modalNovaCessaoNotaFiscal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="titleModalNF">Cessão por NF: Selecionar Notas Fiscais</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNovaCessaoNotaFiscal">
                <div class="modal-body pt-4">
                    <!-- Step 1: Selecionar NFs -->
                    <div id="step1NF">
                        <p class="text-muted small mb-3">Selecione as notas fiscais contendo os equipamentos que deseja ceder.</p>
                        
                        <div class="mb-3">
                            <input type="text" id="filtroTabelaNF" class="form-control form-control-sm" placeholder="Buscar por Número da NF, Fornecedor...">
                        </div>

                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-sm table-hover align-middle">
                                <thead class="bg-light sticky-top">
                                    <tr>
                                        <th style="width: 40px;">
                                            <input type="checkbox" class="form-check-input" id="checkAllNF">
                                        </th>
                                        <th>Número NF</th>
                                        <th>Fornecedor</th>
                                        <th>Itens Disponíveis</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($aquisicoesDisponiveis ?? [] as $aq)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="nfs[]" value="{{ $aq->id }}" class="form-check-input nf-check">
                                        </td>
                                        <td class="fw-bold">{{ $aq->numero_nf ?? 'S/ Nota (ID: '.$aq->id.')' }}</td>
                                        <td>{{ $aq->fornecedor->nome ?? '-' }}</td>
                                        <td><span class="badge bg-success rounded-pill px-3">{{ $aq->equipamentos_count }} disponíveis</span></td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Nenhuma nota fiscal com equipamentos disponíveis.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Step 2: Informar Destino -->
                    <div id="step2NF" style="display: none;">
                        <h6 class="fw-bold mb-3 text-primary">Resumo: <span id="resumoItensNF">0</span> itens selecionados</h6>
                        
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Cessionário (Destino)</label>
                                <select id="usuario_id_nf" class="form-select select2-nf" required>
                                    <option value="">Selecione um destino...</option>
                                    @foreach($usuarios as $user)
                                        <option value="{{ $user->id }}">{{ $user->nome }} ({{ $user->empresa->razao_social ?? 'S/ Empresa' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Data de Devolução Prevista (Opcional)</label>
                                <input type="date" id="data_previsao_nf" class="form-control">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Observações</label>
                                <textarea id="observacoes_nf" class="form-control" rows="3" placeholder="Notas adicionais sobre a cessão..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnNextNF">Avançar</button>
                    <button type="button" class="btn btn-primary" id="btnPrevNF" style="display: none;">Voltar</button>
                    <button type="submit" class="btn btn-success" id="btnSubmitNF" style="display: none;">Confirmar Cessão Lote</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .x-small { font-size: 0.75rem; }
    .select2-container { width: 100% !important; }
</style>

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('body')
    });

    // Modal behavior handled on step transition

    // Filtros de Tabela Modais
    $('#filtroTabelaMultipla').on('keyup', function() {
        let value = $(this).val().toLowerCase();
        $('#step1 tbody tr').filter(function() {
            if ($(this).find('td').length === 1) return;
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    $('#filtroTabelaNF').on('keyup', function() {
        let value = $(this).val().toLowerCase();
        $('#step1NF tbody tr').filter(function() {
            if ($(this).find('td').length === 1) return;
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    $('#checkAll').on('change', function() {
        $('#step1 tbody tr:visible .equip-check').prop('checked', this.checked);
    });

    let currentStep = 1;

    $('#btnNext').on('click', function() {
        if ($('.equip-check:checked').length === 0) {
            Swal.fire('Aviso', 'Selecione pelo menos um equipamento.', 'warning');
            return;
        }
        $('#step1').hide();
        $('#step2').show();
        $('#btnNext').hide();
        $('#btnPrev').show();
        $('#btnSubmit').show();
        $('.modal-title').text('Cessão Múltipla: Informar Destino');

        // Initialize select2 only when step2 is visible to prevent duplicate/empty broken renders
        if (!$('.select2-modal').hasClass('select2-hidden-accessible')) {
            $('.select2-modal').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalNovaCessaoMultipla'),
                width: '100%'
            });
        }
    });

    $('#btnPrev').on('click', function() {
        $('#step2').hide();
        $('#step1').show();
        $('#btnNext').show();
        $('#btnPrev').hide();
        $('#btnSubmit').hide();
        $('.modal-title').text('Cessão Múltipla: Selecionar Ativos');
    });

    $('#formNovaCessaoMultipla').on('submit', function(e) {
        e.preventDefault();
        
        const data = {
            usuario_id: $('#usuario_id_multiplo').val(),
            data_previsao_devolucao: $('#data_previsao_multiplo').val(),
            observacoes: $('#observacoes_multiplo').val(),
            equipamentos: $('.equip-check:checked').map(function() { return this.value; }).get(),
            _token: '{{ csrf_token() }}'
        };

        if (!data.usuario_id) {
            Swal.fire('Erro', 'Selecione um cessionário.', 'error');
            return;
        }

        Swal.fire({
            title: 'Processando...',
            text: 'Registrando cessão e gerando termo.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: "{{ route('ativos.cessoes.store') }}",
            type: "POST",
            data: data,
            success: function(response) {
                Swal.fire('Sucesso!', response.message, 'success').then(() => {
                    // Abrir o PDF em nova aba
                    window.open("{{ url('ativos/cessoes') }}/" + response.cessao_id + "/pdf", '_blank');
                    location.reload();
                });
            },
            error: function(xhr) {
                Swal.fire('Erro', 'Não foi possível processar a requisição.', 'error');
            }
        });
    });

    // --- NOVA CESSÃO POR NOTA FISCAL (AJAX) ---

    $('#checkAllNF').on('change', function() {
        $('#step1NF tbody tr:visible .nf-check').prop('checked', this.checked);
    });

    let currentEquipamentosNF = [];

    $('#btnNextNF').on('click', function() {
        const selectedNFs = $('.nf-check:checked').map(function() { return this.value; }).get();
        if (selectedNFs.length === 0) {
            Swal.fire('Aviso', 'Selecione pelo menos uma nota fiscal.', 'warning');
            return;
        }

        // Fazer requisição AJAX para pegar os equipamentos disponíveis dessas NFs
        const btnNext = $(this);
        btnNext.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Buscando...');

        $.ajax({
            url: "{{ route('ativos.aquisicoes.equipamentos_disponiveis') }}",
            type: "GET",
            data: { nfs: selectedNFs },
            success: function(response) {
                btnNext.prop('disabled', false).text('Avançar');
                
                if(response.success && response.equipamentos.length > 0) {
                    currentEquipamentosNF = response.equipamentos.map(eq => eq.id);
                    $('#resumoItensNF').text(response.equipamentos.length);

                    $('#step1NF').hide();
                    $('#step2NF').show();
                    $('#btnNextNF').hide();
                    $('#btnPrevNF').show();
                    $('#btnSubmitNF').show();
                    $('#titleModalNF').text('Cessão por NF: Validar Destino');

                    if (!$('.select2-nf').hasClass('select2-hidden-accessible')) {
                        $('.select2-nf').select2({
                            theme: 'bootstrap-5',
                            dropdownParent: $('#modalNovaCessaoNotaFiscal'),
                            width: '100%'
                        });
                    }
                } else {
                    Swal.fire('Aviso', 'Nenhum equipamento disponível encontrado para as NFs selecionadas.', 'warning');
                }
            },
            error: function(xhr) {
                btnNext.prop('disabled', false).text('Avançar');
                Swal.fire('Erro', 'Não foi possível buscar equipamentos.', 'error');
            }
        });
    });

    $('#btnPrevNF').on('click', function() {
        $('#step2NF').hide();
        $('#step1NF').show();
        $('#btnNextNF').show();
        $('#btnPrevNF').hide();
        $('#btnSubmitNF').hide();
        $('#titleModalNF').text('Cessão por NF: Selecionar Notas Fiscais');
    });

    $('#formNovaCessaoNotaFiscal').on('submit', function(e) {
        e.preventDefault();
        
        const data = {
            usuario_id: $('#usuario_id_nf').val(),
            data_previsao_devolucao: $('#data_previsao_nf').val(),
            observacoes: $('#observacoes_nf').val(),
            equipamentos: currentEquipamentosNF, // Arrays do AJAX
            _token: '{{ csrf_token() }}'
        };

        if (!data.usuario_id) {
            Swal.fire('Erro', 'Selecione um cessionário.', 'error');
            return;
        }

        Swal.fire({
            title: 'Processando Lote...',
            text: 'Registrando termio único para os itens das NFs.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: "{{ route('ativos.cessoes.store') }}",
            type: "POST",
            data: data,
            success: function(response) {
                Swal.fire('Sucesso!', response.message, 'success').then(() => {
                    window.open("{{ url('ativos/cessoes') }}/" + response.cessao_id + "/pdf", '_blank');
                    location.reload();
                });
            },
            error: function(xhr) {
                Swal.fire('Erro', xhr.responseJSON?.message || 'Não foi possível processar a requisição.', 'error');
            }
        });
    });

});
</script>
@endpush
@endsection
