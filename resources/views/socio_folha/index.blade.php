@extends('layouts.app')

@section('title', 'Arrecadação - Sócio Folha')

@section('content')
    <div class="container-fluid">
        <div class="row g-4">
            <!-- Import Card -->
            <div class="col-md-4">
                <div class="card card-outline card-primary shadow-sm h-100 border-0">
                    <div class="card-header border-0 pb-0">
                        <h3 class="card-title fw-bold text-primary"><i class="fas fa-file-import me-2"></i>Importar Sócio
                            Folha</h3>
                    </div>
                    <form id="importForm" action="{{ route('socios-folha.import') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="bg-light p-3 rounded mb-3 border border-primary-subtle">
                                <small class="text-muted d-block mb-3">Selecione o arquivo Excel de mensalidades PGE
                                    DEB.</small>
                                <div class="input-group">
                                    <input type="file" name="file" id="file" class="form-control" required
                                        accept=".xls,.xlsx,.csv">
                                    <button type="submit" id="btnImportar" class="btn btn-primary px-4 fw-bold"><i
                                            class="fas fa-upload me-2"></i>Upload</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="col-md-8">
                <div class="card card-outline card-secondary shadow-sm h-100 border-0">
                    <div class="card-header border-0 pb-0">
                        <h3 class="card-title fw-bold"><i class="fas fa-filter me-2"></i>Filtros Avançados</h3>
                    </div>
                    <form action="{{ route('socios-folha.index') }}" method="GET" id="filterForm">
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label class="small fw-bold text-secondary">Região</label>
                                    <select name="regiao_id" id="regiaoSelect" class="form-select form-select-sm"
                                        onchange="loadEmpresas(); this.form.submit()">
                                        <option value="">TODOS</option>
                                        @foreach($regioes as $regiao)
                                            <option value="{{ $regiao->id }}" {{ request('regiao_id') == $regiao->id ? 'selected' : '' }}>{{ $regiao->nome }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="small fw-bold text-secondary">Empresa</label>
                                    <select name="empresa_id" id="empresaSelect" class="form-select form-select-sm"
                                        onchange="this.form.submit()">
                                        <option value="">TODOS</option>
                                        @if(!request('regiao_id'))
                                            @foreach($empresas as $empresa)
                                                <option value="{{ $empresa->id }}" {{ request('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                                    {{ $empresa->razao_social }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="small fw-bold text-secondary">Ano</label>
                                    <select name="ano" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="">TODOS</option>
                                        @foreach($anos as $ano)
                                            <option value="{{ $ano }}" {{ request('ano') == $ano ? 'selected' : '' }}>{{ $ano }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="small fw-bold text-secondary">Mês</label>
                                    <select name="mes" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="">TODOS</option>
                                        @foreach($meses as $mes)
                                            @php
                                                $mesNome = \Carbon\Carbon::create()->month($mes)->locale('pt_BR')->monthName;
                                            @endphp
                                            <option value="{{ $mes }}" {{ request('mes') == $mes ? 'selected' : '' }}>
                                                {{ str_pad($mes, 2, '0', STR_PAD_LEFT) }} - {{ ucfirst($mesNome) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success mt-4 border-0 shadow-sm alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger mt-4 border-0 shadow-sm alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Table Card -->
        <div class="card mt-4 shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 premium-table">
                        <thead>
                            <tr class="table-light">
                                <th class="ps-3 border-0">Região</th>
                                <th class="border-0">Empresa</th>
                                <th class="border-0 text-center">Ref (Mês/Ano)</th>
                                <th class="border-0 text-center">Vencimento</th>
                                <th class="border-0 text-end">Valor (R$)</th>
                                <th class="border-0 text-center">Situação</th>
                                <th class="border-0 text-center">Lista (Recebida)</th>
                                <th class="border-0 text-center">Baixa (Ábaco)</th>
                                <th class="border-0 text-center pe-3">Lembretes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sociosFolha as $socio)
                                <tr>
                                    <td class="ps-3"><span
                                            class="small fw-bold text-muted">{{ $socio->regiao->nome ?? 'N/A' }}</span></td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $socio->empresa->razao_social ?? 'N/A' }}</div>
                                        <div class="small text-muted">
                                            {{ $socio->empresa->cnpj ?? $socio->empresa->empresa_erp }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-secondary-subtle text-secondary border border-secondary">{{ str_pad($socio->mes, 2, '0', STR_PAD_LEFT) }}/{{ $socio->ano }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="small">{{ $socio->data_vencimento ? $socio->data_vencimento->format('d/m/Y') : 'N/A' }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-dark">
                                            {{ number_format($socio->valor_mensalidade, 2, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($socio->situacao === 'PAGO')
                                            <span
                                                class="badge rounded-pill bg-success-subtle text-success px-3 border border-success">PAGO</span>
                                        @else
                                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold btn-situacao"
                                                data-id="{{ $socio->id }}" data-valor="{{ $socio->valor_mensalidade }}">
                                                ABERTO
                                            </button>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input switch-lista cursor-pointer" type="checkbox"
                                                data-id="{{ $socio->id }}" {{ $socio->data_lista ? 'checked' : '' }}
                                                title="{{ $socio->data_lista ? 'Confirmado em ' . $socio->data_lista->format('d/m/Y H:i') : 'Pendente' }}">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input switch-baixa cursor-pointer" type="checkbox"
                                                data-id="{{ $socio->id }}" {{ $socio->data_baixa ? 'checked' : '' }}
                                                title="{{ $socio->data_baixa ? 'Confirmado em ' . $socio->data_baixa->format('d/m/Y H:i') : 'Pendente' }}">
                                        </div>
                                    </td>
                                    <td class="text-center pe-3">
                                        <button class="btn btn-sm btn-link text-primary btn-historico p-0"
                                            data-id="{{ $socio->id }}" title="Histórico de Envios">
                                            <i class="fas fa-envelope-open-text fa-lg"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="text-muted opacity-50">
                                            <i class="fas fa-folder-open fa-3x mb-3"></i>
                                            <p class="mb-0 fs-5">Nenhum lançamento importado.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($sociosFolha->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    <div class="d-flex justify-content-center">
                        {{ $sociosFolha->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Pagamento -->
    <div class="modal fade" id="modalPagamento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-success">Confirmar Pagamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">Informe o valor exato pago pela empresa.</p>
                    <div class="form-group">
                        <label class="small fw-bold text-secondary mb-1">Valor Pago (R$)</label>
                        <input type="number" step="0.01" class="form-control" id="valorPagoInput">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnConfirmarPagamento"
                        class="btn btn-success rounded-pill px-4 fw-bold">Confirmar</button>
                </div>
    </div>

    <!-- Modal Histórico de Envios -->
    <div class="modal fade" id="modalHistorico" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-primary"><i class="fas fa-envelope-open-text me-2"></i>Histórico de E-mails Enviados</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="table-responsive">
                        <table class="table align-middle premium-table">
                            <thead>
                                <tr class="table-light">
                                    <th>Contato</th>
                                    <th>Destinatário</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Enviado Em</th>
                                    <th>Ações/Detalhes</th>
                                </tr>
                            </thead>
                            <tbody id="historicoTableBody">
                                <!-- Preenchido via AJAX -->
                            </tbody>
                        </table>
                    </div>
                    <div id="historicoEmptyState" class="text-center py-4 d-none">
                        <span class="text-muted"><i class="fas fa-info-circle me-1"></i>Nenhum e-mail enviado para este lançamento.</span>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .cursor-pointer {
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: #198754;
            border-color: #198754;
        }

        .premium-table thead th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
        }
    </style>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
        <script>
            let currentSocioId = null;
            let modalPagamentoObj = null;

            function loadEmpresas() {
                const regiaoId = document.getElementById('regiaoSelect').value;
                const empresaSelect = document.getElementById('empresaSelect');
                const selectedEmpresa = '{{ request("empresa_id") }}';

                const apiId = regiaoId || 'all';

                axios.get(`/socio-folha/empresas-por-regiao/${apiId}`)
                    .then(res => {
                        const empresas = res.data;
                        empresaSelect.innerHTML = '<option value="">TODOS</option>';
                        empresas.forEach(emp => {
                            const option = document.createElement('option');
                            option.value = emp.id;
                            option.textContent = emp.razao_social;
                            if (selectedEmpresa == emp.id) option.selected = true;
                            empresaSelect.appendChild(option);
                        });
                    });
            }

            document.addEventListener('DOMContentLoaded', function () {
                // Load dependants
                loadEmpresas();

                modalPagamentoObj = new bootstrap.Modal(document.getElementById('modalPagamento'));

                // Spinner para Importação
                const importForm = document.getElementById('importForm');
                const btnImportar = document.getElementById('btnImportar');
                if (importForm) {
                    importForm.addEventListener('submit', function () {
                        btnImportar.disabled = true;
                        btnImportar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
                    });
                }

                // Toggle Situação (ABERTO -> PAGO)
                document.querySelectorAll('.btn-situacao').forEach(btn => {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        currentSocioId = this.dataset.id;
                        document.getElementById('valorPagoInput').value = parseFloat(this.dataset.valor).toFixed(2);
                        modalPagamentoObj.show();
                    });
                });

                document.getElementById('btnConfirmarPagamento').addEventListener('click', function () {
                    const valorPago = document.getElementById('valorPagoInput').value;
                    const btn = this;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>...';

                    axios.patch(`/socio-folha/${currentSocioId}/toggle-situacao`, {
                        valor_pago: valorPago
                    }, {
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    }).then(res => {
                        if (res.data.success) {
                            window.location.reload();
                        } else {
                            Swal.fire('Aviso', res.data.message, 'warning');
                            modalPagamentoObj.hide();
                            btn.disabled = false;
                            btn.innerHTML = 'Confirmar';
                        }
                    }).catch(err => {
                        Swal.fire('Erro', 'Falha ao atualizar situação.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = 'Confirmar';
                    });
                });

                // Toggle Lista
                document.querySelectorAll('.switch-lista').forEach(toggle => {
                    toggle.addEventListener('change', function () {
                        const id = this.dataset.id;
                        const originalState = !this.checked;

                        axios.patch(`/socio-folha/${id}/toggle-lista`, {}, {
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        }).then(res => {
                            if (res.data.success) {
                                Toast.fire({
                                    icon: 'success',
                                    title: 'Status da Lista atualizado!'
                                });
                            }
                        }).catch(err => {
                            this.checked = originalState; // revert
                            Swal.fire('Erro', 'Falha ao atualizar status.', 'error');
                        });
                    });
                });

                // Toggle Baixa
                document.querySelectorAll('.switch-baixa').forEach(toggle => {
                    toggle.addEventListener('change', function () {
                        const id = this.dataset.id;
                        const isChecked = this.checked;
                        const originalState = !isChecked;

                        axios.patch(`/socio-folha/${id}/toggle-baixa`, {}, {
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        }).then(res => {
                            if (res.data.success) {
                                Toast.fire({
                                    icon: 'success',
                                    title: 'Baixa no ERP atualizada!'
                                });
                            } else {
                                this.checked = originalState; // revert
                                Swal.fire('Atenção', res.data.message, 'warning');
                            }
                        }).catch(err => {
                            this.checked = originalState; // revert
                            Swal.fire('Erro', 'Falha ao atualizar baixa.', 'error');
                        });
                    });
                });

                // Modal de Histórico
                const modalHistoricoObj = new bootstrap.Modal(document.getElementById('modalHistorico'));
                const historicoTableBody = document.getElementById('historicoTableBody');
                const historicoEmptyState = document.getElementById('historicoEmptyState');

                document.querySelectorAll('.btn-historico').forEach(btn => {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        const socioId = this.dataset.id;
                        
                        historicoTableBody.innerHTML = '<tr><td colspan="6" class="text-center"><span class="spinner-border spinner-border-sm me-2"></span>Carregando...</td></tr>';
                        historicoEmptyState.classList.add('d-none');
                        modalHistoricoObj.show();

                        axios.get(`/socio-folha/${socioId}/email-historico`)
                            .then(res => {
                                const emails = res.data;
                                historicoTableBody.innerHTML = '';
                                
                                if (emails.length === 0) {
                                    historicoEmptyState.classList.remove('d-none');
                                    return;
                                }

                                emails.forEach(email => {
                                    const tr = document.createElement('tr');
                                    
                                    const contatoName = email.cliente ? email.cliente.nome : 'Contato Removido';
                                    
                                    let statusBadge = '';
                                    if (email.status === 'ENVIADO') {
                                        statusBadge = '<span class="badge bg-secondary-subtle text-secondary border border-secondary">Enviado</span>';
                                    } else if (email.status === 'ABERTO') {
                                        const openedTime = email.opened_at ? new Date(email.opened_at).toLocaleString('pt-BR') : '';
                                        statusBadge = `<span class="badge bg-success-subtle text-success border border-success" title="Aberto em: ${openedTime}">Aberto</span>`;
                                    } else if (email.status === 'BOUNCE') {
                                        statusBadge = '<span class="badge bg-danger-subtle text-danger border border-danger">Bounce</span>';
                                    }

                                    let tipoEnvioLabel = '';
                                    if (email.tipo_envio === '10_dias') tipoEnvioLabel = '10 Dias';
                                    else if (email.tipo_envio === '5_dias') tipoEnvioLabel = '5 Dias';
                                    else if (email.tipo_envio === '1_dia') tipoEnvioLabel = '1 Dia';
                                    else tipoEnvioLabel = email.tipo_envio;

                                    const sentDate = new Date(email.created_at).toLocaleString('pt-BR');

                                    let actionContent = '';
                                    if (email.status === 'BOUNCE') {
                                        const bounceMsg = (email.bounce_description || 'Erro desconhecido').replace(/"/g, '&quot;');
                                        actionContent = `<button class="btn btn-sm btn-outline-danger btn-bounce-detail" data-code="${email.bounce_code}" data-desc="${bounceMsg}"><i class="fas fa-exclamation-circle"></i> Ver Erro</button>`;
                                    } else if (email.status === 'ABERTO' && email.opened_at) {
                                        const openedTime = new Date(email.opened_at).toLocaleString('pt-BR');
                                        actionContent = `<small class="text-success"><i class="fas fa-check-double me-1"></i>Aberto em ${openedTime}</small>`;
                                    } else {
                                        actionContent = '<small class="text-muted">-</small>';
                                    }

                                    tr.innerHTML = `
                                        <td><strong>${contatoName}</strong></td>
                                        <td><code>${email.email_destinatario}</code></td>
                                        <td class="text-center"><span class="badge bg-light text-dark border">${tipoEnvioLabel}</span></td>
                                        <td class="text-center">${statusBadge}</td>
                                        <td class="text-center"><small>${sentDate}</small></td>
                                        <td>${actionContent}</td>
                                    `;
                                    historicoTableBody.appendChild(tr);
                                });

                                document.querySelectorAll('.btn-bounce-detail').forEach(bounceBtn => {
                                    bounceBtn.addEventListener('click', function() {
                                        const code = this.dataset.code || 'N/A';
                                        const desc = this.dataset.desc;
                                        Swal.fire({
                                            title: `Detalhes da Falha (Erro ${code})`,
                                            text: desc,
                                            icon: 'error',
                                            confirmButtonText: 'Fechar',
                                            confirmButtonColor: '#e53e3e'
                                        });
                                    });
                                });
                            })
                            .catch(err => {
                                historicoTableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Erro ao carregar histórico.</td></tr>';
                            });
                    });
                });
            });
        </script>
    @endpush
@endsection