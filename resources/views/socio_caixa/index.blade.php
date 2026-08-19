@extends('layouts.app')

@section('title', 'Acompanhamento Sócio Caixa')

@section('content')
<div class="container-fluid">
    <div class="row g-4">
        <!-- Import Card -->
        @if(auth()->user()->temPermissao('socio_caixa.importar'))
            <div class="col-md-5">
                <div class="card card-outline card-primary shadow-sm h-100 border-0">
                    <div class="card-header border-0 pb-0">
                        <h3 class="card-title fw-bold text-primary"><i class="fas fa-file-import me-2"></i>Importar Planilha</h3>
                    </div>
                    <form id="importForm" action="{{ route('socios-caixa.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="bg-light p-3 rounded mb-3 border border-primary-subtle">
                                <small class="text-muted d-block mb-3">O sistema sincroniza automaticamente registros baseados em Matrícula, Ano e Mês (Upsert).</small>
                                <div class="input-group">
                                    <input type="file" name="file" id="file" class="form-control" required>
                                    <button type="submit" id="btnImportar" class="btn btn-primary px-4 fw-bold"><i class="fas fa-upload me-2"></i>Importar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Filters Card -->
        <div class="col-md-{{ auth()->user()->temPermissao('socio_caixa.importar') ? '7' : '12' }}">
            <div class="card card-outline card-secondary shadow-sm h-100 border-0">
                <div class="card-header border-0 pb-0">
                    <h3 class="card-title fw-bold"><i class="fas fa-filter me-2"></i>Filtros Avançados</h3>
                </div>
                <form action="{{ route('socios-caixa.index') }}" method="GET">
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-2">
                                <label class="small fw-bold text-secondary">Ano</label>
                                <select name="ano" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">Todos</option>
                                    @foreach($anos as $ano)
                                        <option value="{{ $ano }}" {{ request('ano') == $ano ? 'selected' : '' }}>{{ $ano }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small fw-bold text-secondary">Tipo de Sócio</label>
                                <select name="tipo" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">Todos</option>
                                    @foreach($tipos as $t)
                                        <option value="{{ $t }}" {{ request('tipo') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small fw-bold text-secondary">Min. em Aberto</label>
                                <select name="min_abertos" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="0">Qualquer</option>
                                    @for($i=1; $i<=12; $i++)
                                        <option value="{{ $i }}" {{ request('min_abertos') == $i ? 'selected' : '' }}>{{ $i }}+ meses</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="small fw-bold text-secondary">Busca Rápida (Nome ou Matrícula)</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="nome" class="form-control" placeholder="Digite o nome..." value="{{ request('nome') }}">
                                    <button type="submit" class="btn btn-secondary pe-3 ps-3"><i class="fas fa-search me-1"></i> Buscar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 pt-0 d-flex flex-wrap gap-3">
                        <div class="form-check form-switch d-inline-block">
                            <input class="form-check-input" type="checkbox" name="ver_postergados" id="verPostergados" value="1" {{ request('ver_postergados') ? 'checked' : '' }} onchange="this.form.submit()">
                            <label class="form-check-label small fw-bold text-muted" for="verPostergados">Exibir somente postergados (Snooze)</label>
                        </div>
                        <div class="form-check form-switch d-inline-block">
                            <input class="form-check-input" type="checkbox" name="ver_inativados" id="verInativados" value="1" {{ request('ver_inativados') ? 'checked' : '' }} onchange="this.form.submit()">
                            <label class="form-check-label small fw-bold text-danger" for="verInativados">
                                <i class="fas fa-user-slash me-1"></i>Exibir Inativados no Ábaco
                            </label>
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
                            <th class="ps-3 border-0">Matrícula</th>
                            <th class="border-0">Nome do Sócio</th>
                            <th class="border-0 text-center">Tipo</th>
                            <th class="border-0 text-center">Quant. Pagas</th>
                            <th class="border-0 text-center">Em Aberto</th>
                            <th class="border-0 text-center">Valor Total</th>
                            <th class="border-0 text-center">Postergadas</th>
                            <th class="border-0 text-center">Contatos</th>
                            <th class="text-end pe-3 border-0">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($socios as $socio)
                        <tr>
                            <td class="ps-3"><span class="badge bg-secondary-subtle text-secondary border border-secondary">{{ $socio->matricula }}</span></td>
                            <td>
                                <span class="fw-bold text-dark">{{ $socio->nome }}</span>
                                @if($socio->inativado_abaco)
                                    <span class="badge bg-danger-subtle text-danger border border-danger ms-1" style="font-size: 0.7rem;"><i class="fas fa-user-slash me-1"></i>Inativado Ábaco</span>
                                @endif
                            </td>
                            <td class="text-center"><span class="small text-muted text-uppercase">{{ $socio->tipo_socio }}</span></td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-success-subtle text-success px-3 border border-success">
                                    {{ $socio->total_pagos }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill {{ $socio->total_abertos > 0 ? 'bg-danger-subtle text-danger border border-danger' : 'bg-light text-muted border' }} px-3">
                                     {{ $socio->total_abertos }}
                                 </span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold {{ $socio->valor_aberto > 0 ? 'text-danger' : 'text-muted' }}">
                                    R$ {{ number_format($socio->valor_aberto, 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill {{ $socio->total_postergados > 0 ? 'bg-warning-subtle text-warning border border-warning' : 'bg-light text-muted border' }} px-3">
                                    {{ $socio->total_postergados }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill {{ $socio->qtde_contatos > 0 ? 'bg-info-subtle text-info border border-info' : 'bg-light text-muted border' }} px-3">
                                    <i class="fab fa-whatsapp me-1"></i>{{ $socio->qtde_contatos ?? 0 }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group">
                                    <a href="{{ route('socios-caixa.show', $socio->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Gerenciar Lançamentos">
                                        <i class="fas fa-list-check me-1"></i> Detalhes
                                    </a>
                                    @if(auth()->user()->temPermissao('socio_caixa.gerenciar'))
                                        @if(!$socio->inativado_abaco)
                                            <button type="button" class="btn btn-sm btn-outline-warning ms-1 rounded-pill btn-postpone" 
                                                    data-id="{{ $socio->id }}" 
                                                    data-nome="{{ $socio->nome }}"
                                                    title="Postergar todos os débitos">
                                                <i class="fas fa-clock"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger ms-1 rounded-pill btn-inativar-abaco" 
                                                    data-id="{{ $socio->id }}" 
                                                    data-nome="{{ $socio->nome }}"
                                                    title="Marcar como Inativado no ERP Ábaco">
                                                <i class="fas fa-user-slash"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-success ms-1 rounded-pill btn-reativar-abaco" 
                                                    data-id="{{ $socio->id }}" 
                                                    data-nome="{{ $socio->nome }}"
                                                    title="Reativar Associado">
                                                <i class="fas fa-user-check me-1"></i> Reativar
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted opacity-50">
                                    <i class="fas fa-search fa-3x mb-3"></i>
                                    <p class="mb-0 fs-5">Nenhum registro encontrado.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($socios->hasPages())
        <div class="card-footer bg-white border-top py-3">
            <div class="d-flex justify-content-center">
                {{ $socios->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modal de Observação -->
<div class="modal fade" id="modalObservacao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h4 class="modal-title fw-bold" id="modalTitle">Título</h4>
                <button type="button" class="btn-close" onclick="fecharModal()"></button>
            </div>
            <div class="modal-body p-4">
                <p id="modalDescription" class="mb-4 text-muted fs-6">Descrição...</p>
                <div class="form-group text-start">
                    <label class="small fw-bold mb-2 text-uppercase text-secondary">Observações da Operação</label>
                    <textarea id="obsInput" class="form-control border-gray-300" rows="3" placeholder="Insira detalhes..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" onclick="fecharModal()">Cancelar</button>
                <button type="button" id="btnConfirmar" class="btn btn-primary rounded-pill px-4 fw-bold">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Inativação Ábaco -->
<div class="modal fade" id="modalInativarAbaco" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h4 class="modal-title fw-bold text-danger"><i class="fas fa-user-slash me-2"></i>Inativar no ERP Ábaco</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-3 text-muted">
                    Confirma que o associado <strong id="inativarNome"></strong> foi inativado/desligado no <strong>ERP Ábaco</strong>?
                </p>
                <div class="alert alert-warning border-0 small py-2">
                    <i class="fas fa-info-circle me-1"></i> Este associado não será mais exibido na lista ativa de cobranças. Caso ele conste em uma nova importação de planilha do ERP, será reativado automaticamente.
                </div>
                <div class="form-group">
                    <label class="small fw-bold text-secondary text-uppercase mb-2">Motivo / Observações</label>
                    <textarea id="inativarMotivo" class="form-control" rows="2" placeholder="Opcional..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarInativar" class="btn btn-danger rounded-pill px-4 fw-bold">Confirmar Inativação</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Reativação Ábaco -->
<div class="modal fade" id="modalReativarAbaco" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h4 class="modal-title fw-bold text-success"><i class="fas fa-user-check me-2"></i>Reativar Associado</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-3 text-muted">
                    Deseja reativar o associado <strong id="reativarNome"></strong>? Ele voltará para a lista regular de cobranças.
                </p>
                <div class="form-group">
                    <label class="small fw-bold text-secondary text-uppercase mb-2">Motivo / Observações</label>
                    <textarea id="reativarMotivo" class="form-control" rows="2" placeholder="Opcional..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarReativar" class="btn btn-success rounded-pill px-4 fw-bold">Reativar Agora</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Postergação (Snooze) -->
<div class="modal fade" id="modalPostpone" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h4 class="modal-title fw-bold text-warning">Postergar Conferência</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-3 text-muted">A conferência de <strong id="postponeNome"></strong> sairá da lista temporariamente.</p>
                
                <div class="mb-3">
                    <label class="small fw-bold text-secondary text-uppercase mb-2">Postergar para:</label>
                    <div class="row g-2">
                        <div class="col-6"><button type="button" class="btn btn-outline-secondary btn-sm w-100 quick-postpone" data-days="1">Amanhã</button></div>
                        <div class="col-6"><button type="button" class="btn btn-outline-secondary btn-sm w-100 quick-postpone" data-days="3">Em 3 dias</button></div>
                        <div class="col-6"><button type="button" class="btn btn-outline-secondary btn-sm w-100 quick-postpone" data-days="7">Próx. Semana</button></div>
                        <div class="col-6"><button type="button" class="btn btn-outline-secondary btn-sm w-100 quick-postpone" data-days="30">Próx. Mês</button></div>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="small fw-bold text-secondary text-uppercase mb-2">Data Personalizada</label>
                    <input type="date" id="postponeDate" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}" value="{{ date('Y-m-d', strtotime('+1 day')) }}">
                </div>

                <div class="form-group">
                    <label class="small fw-bold text-secondary text-uppercase mb-2">Motivo</label>
                    <textarea id="postponeMotivo" class="form-control" rows="2" placeholder="Opcional..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarPostpone" class="btn btn-warning rounded-pill px-4 fw-bold">Postergar Agora</button>
            </div>
        </div>
    </div>
</div>

<style>
.cursor-pointer { cursor: pointer; }
.form-check-input:checked { background-color: #198754; border-color: #198754; }
.premium-table thead th {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 700;
}
.quick-postpone.active { background-color: #ffc107; color: #000; border-color: #ffc107; }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
let currentToggle = null;
let currentId = null;
let modalObj = null;
let modalPostpone = null;

function fecharModal() {
    // Não precisa reverter o switch - e.preventDefault() impediu qualquer mudança
    if (modalObj) modalObj.hide();
}

document.addEventListener('DOMContentLoaded', function() {
    modalObj = new bootstrap.Modal(document.getElementById('modalObservacao'), { backdrop: 'static', keyboard: false });
    modalPostpone = new bootstrap.Modal(document.getElementById('modalPostpone'));
    
    const btnConfirmar = document.getElementById('btnConfirmar');
    const obsInput = document.getElementById('obsInput');
    const modalTitle = document.getElementById('modalTitle');
    const modalDescription = document.getElementById('modalDescription');

    // Spinner para Importação
    const importForm = document.getElementById('importForm');
    const btnImportar = document.getElementById('btnImportar');
    if (importForm) {
        importForm.addEventListener('submit', function() {
            btnImportar.disabled = true;
            btnImportar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processando...';
        });
    }

    // Toggle Pagamento
    document.querySelectorAll('.payment-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            // Bloqueia o toggle padrão do browser
            e.preventDefault();

            currentToggle = this;
            currentId = this.dataset.id;

            // Lê o estado REAL do servidor via data-paid (imune ao comportamento do browser)
            const isCurrentlyPaid = this.dataset.paid === 'true';
            const willBePaid = !isCurrentlyPaid;

            // Restaura o visual para o estado real (caso o browser tenha alterado)
            this.checked = isCurrentlyPaid;

            obsInput.value = '';

            if (willBePaid) {
                // Switch está OFF (cinza) → usuário quer confirmar pagamento
                modalTitle.textContent = "Confirmar Pagamento";
                modalTitle.className = "modal-title fw-bold text-success";
                modalDescription.innerHTML = `Confirmar recebimento da referência <strong>${this.dataset.ref}</strong>?`;
                btnConfirmar.className = "btn btn-success rounded-pill px-4 fw-bold";
            } else {
                // Switch está ON (verde) → usuário quer estornar
                modalTitle.textContent = "Estornar Lançamento";
                modalTitle.className = "modal-title fw-bold text-danger";
                modalDescription.innerHTML = `Deseja realmente estornar a referência <strong>${this.dataset.ref}</strong>?`;
                btnConfirmar.className = "btn btn-danger rounded-pill px-4 fw-bold";
            }

            modalObj.show();
        });
    });

    btnConfirmar.addEventListener('click', function() {
        const obs = obsInput.value;
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>...';

        axios.patch(`/socio-caixa/${currentId}/toggle-payment`, {
            observacao: obs
        }, {
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(response => {
            if (response.data.success) {
                currentToggle.checked = !currentToggle.checked;
                modalObj.hide();
                window.location.reload(); 
            }
        })
        .catch(error => {
            Swal.fire('Erro', 'Operação falhou.', 'error');
            btn.disabled = false;
            btn.innerHTML = 'Confirmar';
        });
    });

    // Postergação (Postpone)
    document.querySelectorAll('.btn-postpone').forEach(btn => {
        btn.addEventListener('click', function() {
            currentId = this.dataset.id;
            document.getElementById('postponeNome').textContent = this.dataset.nome;
            modalPostpone.show();
        });
    });

    document.querySelectorAll('.quick-postpone').forEach(btn => {
        btn.addEventListener('click', function() {
            const days = parseInt(this.dataset.days);
            const date = new Date();
            date.setDate(date.getDate() + days);
            document.getElementById('postponeDate').value = date.toISOString().split('T')[0];
            
            document.querySelectorAll('.quick-postpone').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    document.getElementById('btnConfirmarPostpone').addEventListener('click', function() {
        const date = document.getElementById('postponeDate').value;
        const motivo = document.getElementById('postponeMotivo').value;
        const btn = this;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Postergando...';

        axios.patch(`/socio-caixa/${currentId}/postpone`, {
            postergado_ate: date + ' 23:59:59',
            motivo: motivo
        }, {
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(response => {
            if (response.data.success) {
                modalPostpone.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Postergado!',
                    text: 'O registro sairá da lista principal até a data selecionada.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            }
        })
        .catch(error => {
            Swal.fire('Erro', 'Não foi possível postergar.', 'error');
            btn.disabled = false;
            btn.innerHTML = 'Postergar Agora';
        });
    });

    // Inativação ERP Ábaco
    const modalInativarObj = new bootstrap.Modal(document.getElementById('modalInativarAbaco'));
    document.querySelectorAll('.btn-inativar-abaco').forEach(btn => {
        btn.addEventListener('click', function() {
            currentId = this.dataset.id;
            document.getElementById('inativarNome').textContent = this.dataset.nome;
            document.getElementById('inativarMotivo').value = '';
            modalInativarObj.show();
        });
    });

    document.getElementById('btnConfirmarInativar').addEventListener('click', function() {
        const motivo = document.getElementById('inativarMotivo').value;
        const btn = this;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Inativando...';

        axios.patch(`/socio-caixa/${currentId}/inativar-abaco`, {
            motivo: motivo
        }, {
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(response => {
            if (response.data.success) {
                modalInativarObj.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Inativado no Ábaco!',
                    text: 'Associado inativado com sucesso.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            }
        })
        .catch(error => {
            const msg = error.response?.data?.message || 'Não foi possível inativar o associado.';
            Swal.fire('Erro', msg, 'error');
            btn.disabled = false;
            btn.innerHTML = 'Confirmar Inativação';
        });
    });

    // Reativação de Associado
    const modalReativarObj = new bootstrap.Modal(document.getElementById('modalReativarAbaco'));
    document.querySelectorAll('.btn-reativar-abaco').forEach(btn => {
        btn.addEventListener('click', function() {
            currentId = this.dataset.id;
            document.getElementById('reativarNome').textContent = this.dataset.nome;
            document.getElementById('reativarMotivo').value = '';
            modalReativarObj.show();
        });
    });

    document.getElementById('btnConfirmarReativar').addEventListener('click', function() {
        const motivo = document.getElementById('reativarMotivo').value;
        const btn = this;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Reativando...';

        axios.patch(`/socio-caixa/${currentId}/reativar-abaco`, {
            motivo: motivo
        }, {
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(response => {
            if (response.data.success) {
                modalReativarObj.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Reativado!',
                    text: 'Associado reativado com sucesso.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            }
        })
        .catch(error => {
            const msg = error.response?.data?.message || 'Não foi possível reativar o associado.';
            Swal.fire('Erro', msg, 'error');
            btn.disabled = false;
            btn.innerHTML = 'Reativar Agora';
        });
    });
});
</script>
@endpush
@endsection
