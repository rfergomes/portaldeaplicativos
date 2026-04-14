@extends('layouts.app')

@section('title', 'Detalhes do Sócio: ' . $socio->nome)

@section('content')
<div class="container-fluid">
    <div class="row g-4">
        <!-- Perfil do Sócio -->
        <div class="col-md-4">
            <div class="card card-outline card-primary shadow-sm h-100 border-0">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="mx-auto bg-primary text-white d-flex align-items-center justify-content-center rounded-circle mb-3 shadow-sm" style="width: 80px; height: 80px; font-size: 2.5rem;">
                            {{ strtoupper(substr($socio->nome, 0, 1)) }}
                        </div>
                        <h4 class="fw-bold mb-0 text-truncate">{{ $socio->nome }}</h4>
                        <span class="badge bg-secondary rounded-pill">MATRÍCULA: {{ $socio->matricula }}</span>
                    </div>
                    
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span class="text-muted"><i class="fas fa-building me-2"></i>Empresa/Tipo:</span>
                            <span class="fw-bold">{{ $socio->tipo_socio ?: 'Não informado' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span class="text-muted"><i class="fas fa-calendar-alt me-2"></i>Lançamentos:</span>
                            <span class="badge bg-light text-dark border">{{ $lancamentos->count() }} meses</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3 p-3 bg-light rounded border border-danger-subtle">
                            <span class="text-muted fw-bold">TOTAL EM ABERTO:</span>
                            <span class="text-danger fw-bold fs-4">R$ {{ number_format($lancamentos->where('pago', false)->sum('valor'), 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-4">
                    <a href="{{ route('socios-caixa.index') }}" class="btn btn-outline-secondary w-100 rounded-pill">
                        <i class="fas fa-arrow-left me-2"></i> Voltar à Lista Geral
                    </a>
                </div>
            </div>
        </div>

        <!-- Todos os Lançamentos -->
        <div class="col-md-8">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header border-0 bg-white d-flex justify-content-between align-items-center pt-3">
                    <h3 class="card-title fw-bold m-0"><i class="fas fa-history me-2 text-primary"></i>Histórico de Mensalidades</h3>
                    <div class="card-tools">
                        <span class="badge bg-success-subtle text-success border border-success">{{ $lancamentos->where('pago', true)->count() }} Pagos</span>
                        <span class="badge bg-danger-subtle text-danger border border-danger ms-1">{{ $lancamentos->where('pago', false)->count() }} Abertos</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 premium-table">
                            <thead>
                                <tr class="table-light">
                                    <th class="ps-3 border-0">Ref (Mês/Ano)</th>
                                    <th class="border-0">Valor</th>
                                    <th class="text-center border-0">Pago?</th>
                                    <th class="border-0">Última Baixa</th>
                                    <th class="text-end pe-3 border-0">Auditoria</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lancamentos as $item)
                                <tr>
                                    <td class="ps-3 fw-bold">{{ str_pad($item->mes, 2, '0', STR_PAD_LEFT) }}/{{ $item->ano }}</td>
                                    <td><span class="text-secondary fw-semibold">R$ {{ number_format($item->valor, 2, ',', '.') }}</span></td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input payment-toggle cursor-pointer" type="checkbox" 
                                                   data-id="{{ $item->id }}"
                                                   data-ref="{{ str_pad($item->mes, 2, '0', STR_PAD_LEFT) }}/{{ $item->ano }}"
                                                   data-paid="{{ $item->pago ? 'true' : 'false' }}"
                                                   style="width: 2.8em; height: 1.4em;"
                                                   {{ $item->pago ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td>
                                        @if($item->usuarioBaixa)
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; font-size: 0.7rem;">
                                                    {{ strtoupper(substr($item->usuarioBaixa->name, 0, 1)) }}
                                                </div>
                                                <div style="line-height: 1;">
                                                    <small class="fw-bold d-block">{{ $item->usuarioBaixa->nickname ?: $item->usuarioBaixa->name }}</small>
                                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $item->data_pagamento ? $item->data_pagamento->format('d/m/Y H:i') : 'Data N/D' }}</small>
                                                </div>
                                            </div>
                                        @else
                                            <span class="badge bg-light text-muted fw-normal border">Aguardando...</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <button type="button" class="btn btn-sm btn-light border rounded-pill" data-bs-toggle="collapse" data-bs-target="#hist-{{ $item->id }}" aria-expanded="false">
                                            <i class="fas fa-eye me-1"></i> <small>LOGS</small>
                                        </button>
                                    </td>
                                </tr>
                                <tr class="collapse" id="hist-{{ $item->id }}">
                                    <td colspan="5" class="bg-gray-100 p-0">
                                        <div class="p-3 border-start border-primary border-4 ms-3 my-2 shadow-sm rounded bg-white">
                                            <h6 class="fw-bold small mb-2 text-primary text-uppercase"><i class="fas fa-fingerprint me-2"></i>Trilha de Auditoria</h6>
                                            @forelse($item->historico as $log)
                                                <div class="d-flex justify-content-between align-items-start mb-2 pb-2 {{ !$loop->last ? 'border-bottom border-dashed' : '' }}">
                                                    <div>
                                                        <span class="badge {{ $log->acao == 'baixa' ? 'bg-success' : 'bg-danger' }} p-1 px-2 small me-2">{{ strtoupper($log->acao) }}</span>
                                                        <span class="small text-dark">{{ $log->observacao ?: 'Sem observação registrada.' }}</span>
                                                    </div>
                                                    <div class="text-end">
                                                        <small class="d-block text-dark fw-bold" style="font-size: 0.7rem;">{{ $log->user->name }}</small>
                                                        <small class="text-muted" style="font-size: 0.65rem;">{{ $log->created_at->format('d/m/Y H:i') }}</small>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="text-muted small italic">Nenhuma movimentação manual registrada no sistema.</div>
                                            @endforelse
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Observação -->
<div class="modal fade" id="modalObservacao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h4 class="modal-title fw-bold" id="modalTitle">Título</h4>
                <button type="button" class="btn-close" data-bs-modal="modal" onclick="fecharModal()"></button>
            </div>
            <div class="modal-body p-4">
                <p id="modalDescription" class="mb-4 text-muted fs-6">Descrição...</p>
                <div class="form-group">
                    <label class="small fw-bold mb-2 text-uppercase text-secondary">Observação do Lançamento</label>
                    <textarea id="obsInput" class="form-control border-gray-300" rows="3" placeholder="Ex: Recebido em dinheiro, pix, motivo do estorno..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" onclick="fecharModal()">Cancelar</button>
                <button type="button" id="btnConfirmar" class="btn btn-primary rounded-pill px-4 fw-bold">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gray-100 { background-color: #f8f9fa; }
.border-dashed { border-style: dashed !important; }
.cursor-pointer { cursor: pointer; }
.form-check-input:checked { background-color: #198754; border-color: #198754; }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
let currentToggle = null;
let currentId = null;
let modalObj = null;

document.addEventListener('DOMContentLoaded', function() {
    modalObj = new bootstrap.Modal(document.getElementById('modalObservacao'), { backdrop: 'static', keyboard: false });
    const btnConfirmar = document.getElementById('btnConfirmar');
    const obsInput = document.getElementById('obsInput');
    const modalTitle = document.getElementById('modalTitle');
    const modalDescription = document.getElementById('modalDescription');

    document.querySelectorAll('.payment-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();

            currentToggle = this;
            currentId = this.dataset.id;

            // Lê o estado REAL via data-paid (imune ao comportamento do browser)
            const isCurrentlyPaid = this.dataset.paid === 'true';
            const willBePaid = !isCurrentlyPaid;

            // Restaura o visual para o estado real
            this.checked = isCurrentlyPaid;

            obsInput.value = '';

            if (willBePaid) {
                // Switch cinza → confirmar pagamento
                modalTitle.textContent = "Confirmar Recebimento";
                modalTitle.className = "modal-title fw-bold text-success";
                modalDescription.innerHTML = `Você está registrando a <strong>BAIXA</strong> do pagamento ref. <strong>${this.dataset.ref}</strong>.`;
                btnConfirmar.className = "btn btn-success rounded-pill px-4 fw-bold";
            } else {
                // Switch verde → estornar
                modalTitle.textContent = "Estornar Pagamento";
                modalTitle.className = "modal-title fw-bold text-danger";
                modalDescription.innerHTML = `Você está realizando o <strong>ESTORNO</strong> da referência <strong>${this.dataset.ref}</strong>. O sócio voltará a ficar inadimplente.`;
                btnConfirmar.className = "btn btn-danger rounded-pill px-4 fw-bold";
            }

            modalObj.show();
        });
    });

    btnConfirmar.addEventListener('click', function() {
        const obs = obsInput.value;
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processando...';

        axios.patch(`/socio-caixa/${currentId}/toggle-payment`, {
            observacao: obs
        }, {
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(response => {
            if (response.data.success) {
                // Agora sim chaveia visualmente antes de dar o reload (opcional)
                currentToggle.checked = !currentToggle.checked;
                modalObj.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: 'O registro foi atualizado.',
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload(); 
                });
            }
        })
        .catch(error => {
            Swal.fire('Erro', 'Não foi possível atualizar o status.', 'error');
            btn.disabled = false;
            btn.innerHTML = 'Confirmar';
        });
    });
});

function fecharModal() {
    modalObj.hide();
};
</script>
@endpush
@endsection
