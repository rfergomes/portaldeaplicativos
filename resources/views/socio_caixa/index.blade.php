@extends('layouts.app')

@section('title', 'Acompanhamento Sócio Caixa')

@section('content')
<div class="container-fluid">
    <div class="row g-4">
        <!-- Import Card -->
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

        <!-- Filters Card -->
        <div class="col-md-7">
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
                            <div class="col-md-2">
                                <label class="small fw-bold text-secondary">Mês</label>
                                <select name="mes" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">Todos</option>
                                    @foreach($meses as $mes)
                                        <option value="{{ $mes }}" {{ request('mes') == $mes ? 'selected' : '' }}>
                                            {{ str_pad($mes, 2, '0', STR_PAD_LEFT) }}
                                        </option>
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
                                <label class="small fw-bold text-secondary">Status Pagto.</label>
                                <select name="pago" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="todos" {{ request('pago') == 'todos' ? 'selected' : '' }}>Todos</option>
                                    <option value="0" {{ request('pago') == '0' ? 'selected' : '' }}>Em Aberto</option>
                                    <option value="1" {{ request('pago') == '1' ? 'selected' : '' }}>Pago</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small fw-bold text-secondary">Busca Rápida</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="nome" class="form-control" placeholder="Nome..." value="{{ request('nome') }}">
                                    <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 pt-0">
                        <div class="form-check form-switch d-inline-block">
                            <input class="form-check-input" type="checkbox" name="ver_postergados" id="verPostergados" value="1" {{ request('ver_postergados') ? 'checked' : '' }} onchange="this.form.submit()">
                            <label class="form-check-label small fw-bold text-muted" for="verPostergados">Exibir somente postergados (Snooze)</label>
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
                            <th class="border-0">Tipo</th>
                            <th class="border-0 text-center">Referência</th>
                            <th class="border-0">Valor</th>
                            <th class="text-center border-0">Pago?</th>
                            <th class="border-0">Operador / Status</th>
                            <th class="text-end pe-3 border-0">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($socios as $socio)
                        <tr class="{{ $socio->postergado_ate && $socio->postergado_ate > now() ? 'opacity-75 bg-light' : '' }}">
                            <td class="ps-3"><span class="badge bg-secondary-subtle text-secondary border border-secondary">{{ $socio->matricula }}</span></td>
                            <td>
                                <span class="fw-bold text-dark">{{ $socio->nome }}</span>
                                @if($socio->postergado_ate && $socio->postergado_ate > now())
                                    <div class="small text-warning fw-bold">
                                        <i class="fas fa-clock me-1"></i> Postergado até {{ $socio->postergado_ate->format('d/m') }}
                                    </div>
                                @endif
                            </td>
                            <td><span class="small text-muted text-uppercase">{{ $socio->tipo_socio }}</span></td>
                            <td class="text-center">{{ str_pad($socio->mes, 2, '0', STR_PAD_LEFT) }}/{{ $socio->ano }}</td>
                            <td><span class="fw-semibold">R$ {{ number_format($socio->valor, 2, ',', '.') }}</span></td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input payment-toggle cursor-pointer" type="checkbox" 
                                           data-id="{{ $socio->id }}"
                                           data-ref="{{ str_pad($socio->mes, 2, '0', STR_PAD_LEFT) }}/{{ $socio->ano }}"
                                           data-paid="{{ $socio->pago ? 'true' : 'false' }}"
                                           style="width: 2.5em; height: 1.25em;"
                                           {{ $socio->pago ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td>
                                <div id="pagto-{{ $socio->id }}" class="small">
                                    @if($socio->pago)
                                        <span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i> PAGO</span>
                                        @if($socio->usuarioBaixa)
                                            <div class="text-muted" style="font-size:0.7rem;">Por: {{ $socio->usuarioBaixa->nickname ?: $socio->usuarioBaixa->name }}</div>
                                        @endif
                                    @else
                                        <span class="text-danger fw-bold"><i class="fas fa-times-circle me-1"></i> EM ABERTO</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group">
                                    <a href="{{ route('socios-caixa.show', $socio->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Visualizar Lançamentos">
                                        <i class="fas fa-external-link-alt me-1"></i> Detalhes
                                    </a>
                                    @if(!$socio->pago)
                                    <button type="button" class="btn btn-sm btn-outline-warning ms-1 rounded-pill btn-postpone" 
                                            data-id="{{ $socio->id }}" 
                                            data-nome="{{ $socio->nome }}"
                                            title="Postergar Conferência">
                                        <i class="fas fa-clock"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
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
});
</script>
@endpush
@endsection
