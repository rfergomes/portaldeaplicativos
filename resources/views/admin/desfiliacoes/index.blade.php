@extends('layouts.app')

@section('title', 'Gestão de Desfiliações Sindicais')

@section('content')
<div class="container-fluid">
    <!-- Header e Métricas -->
    <div class="row mb-3">
        <div class="col-md-4 mb-2">
            <div class="card card-outline card-primary shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 me-3">
                        <i class="fa-solid fa-file-signature fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Total de Solicitações</div>
                        <div class="fs-4 fw-bold text-dark">{{ $totais['total'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card card-outline card-warning shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 me-3">
                        <i class="fa-solid fa-clock-rotate-left fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Pendentes de Assinatura</div>
                        <div class="fs-4 fw-bold text-dark">{{ $totais['pendentes'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-2">
            <div class="card card-outline card-success shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 me-3">
                        <i class="fa-solid fa-circle-check fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Confirmadas e Assinadas</div>
                        <div class="fs-4 fw-bold text-dark">{{ $totais['confirmadas'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de Busca -->
    <div class="card card-outline card-secondary shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.desfiliacoes.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Buscar por Nome, CPF, Empresa ou E-mail</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="busca" class="form-control" value="{{ request('busca') }}" placeholder="Digite para filtrar...">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="pendente" {{ request('status') === 'pendente' ? 'selected' : '' }}>Pendente</option>
                        <option value="confirmada" {{ request('status') === 'confirmada' ? 'selected' : '' }}>Confirmada</option>
                        <option value="expirada" {{ request('status') === 'expirada' ? 'selected' : '' }}>Expirada</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control form-control-sm" value="{{ request('data_inicio') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control form-control-sm" value="{{ request('data_fim') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100 shadow-sm">
                        <i class="fa-solid fa-filter me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.desfiliacoes.index') }}" class="btn btn-outline-secondary btn-sm" title="Limpar">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Solicitações -->
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title fw-bold m-0">
                <i class="fa-solid fa-list-check me-2"></i>Registros de Desfiliação
            </h3>
            <span class="badge bg-secondary ms-auto">{{ $desfiliacoes->total() }} registros</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Data</th>
                            <th>Trabalhador</th>
                            <th>CPF</th>
                            <th>Empresa</th>
                            <th>Status</th>
                            <th>Hash / Validação</th>
                            <th class="text-center" style="width: 130px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($desfiliacoes as $item)
                            <tr>
                                <td class="fw-bold text-muted">#{{ str_pad((string)$item->id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <div class="fw-semibold small">{{ $item->created_at->format('d/m/Y') }}</div>
                                    <div class="text-muted text-xs">{{ $item->created_at->format('H:i') }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $item->nome_completo }}</div>
                                    <div class="text-muted text-xs"><i class="fa-regular fa-envelope me-1"></i>{{ $item->email }}</div>
                                </td>
                                <td class="font-monospace small">{{ $item->cpf_formatado }}</td>
                                <td class="small">{{ $item->empresa }}</td>
                                <td>
                                    @if ($item->status === 'confirmada')
                                        <span class="badge bg-success rounded-pill px-2">
                                            <i class="fa-solid fa-check me-1"></i>Confirmada
                                        </span>
                                    @elseif ($item->status === 'pendente')
                                        <span class="badge bg-warning text-dark rounded-pill px-2">
                                            <i class="fa-solid fa-clock me-1"></i>Pendente
                                        </span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill px-2">{{ ucfirst($item->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($item->termo_hash)
                                        <span class="font-monospace text-xs text-primary" title="{{ $item->termo_hash }}">
                                            {{ substr($item->termo_hash, 0, 12) }}...
                                        </span>
                                        <a href="{{ route('desfiliacao.validar', ['hash' => $item->termo_hash]) }}" target="_blank" class="ms-1 text-muted" title="Verificar na página pública">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                    @else
                                        <span class="text-muted text-xs">Aguardando assinatura</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-info btn-detalhes" data-id="{{ $item->id }}" title="Ver Detalhes de Auditoria">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        @if ($item->isConfirmada() && $item->pdf_path)
                                            <a href="{{ route('admin.desfiliacoes.download', ['id' => $item->id]) }}" class="btn btn-outline-danger" title="Baixar Termo em PDF">
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-inbox fa-2x mb-2 d-block"></i>
                                    Nenhum registro de desfiliação encontrado com os filtros aplicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($desfiliacoes->hasPages())
            <div class="card-footer bg-white border-top">
                {{ $desfiliacoes->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal de Detalhes de Auditoria -->
<div class="modal fade" id="modalAuditoria" tabindex="-1" aria-labelledby="modalAuditoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fs-6 fw-bold" id="modalAuditoriaLabel">
                    <i class="fa-solid fa-shield-halved me-2"></i>Trilha de Auditoria e Metadados Probatórios
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div id="modalLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="small text-muted mt-2">Carregando dados da solicitação...</div>
                </div>
                <div id="modalContent" style="display: none;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Nome Completo</label>
                            <div class="fw-bold" id="detNome"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small fw-bold">CPF</label>
                            <div class="font-monospace" id="detCpf"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small fw-bold">Matrícula</label>
                            <div id="detMatricula"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Empresa Empregadora</label>
                            <div id="detEmpresa"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">E-mail</label>
                            <div id="detEmail"></div>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Motivo Declarado</label>
                            <div class="p-2 bg-light rounded border text-muted small" id="detMotivo"></div>
                        </div>
                        <div class="col-12"><hr class="my-1"></div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">IP de Solicitação</label>
                            <div class="font-monospace small" id="detIpSol"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">IP de Confirmação</label>
                            <div class="font-monospace small" id="detIpConf"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Data/Hora da Solicitação</label>
                            <div class="small" id="detDataCriacao"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Data/Hora da Assinatura</label>
                            <div class="small" id="detDataConf"></div>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-bold">User-Agent do Navegador (Dispositivo)</label>
                            <div class="p-2 bg-light rounded border font-monospace text-xs text-muted" id="detUserAgent"></div>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-bold">Hash SHA-256 de Autenticidade</label>
                            <div class="p-2 bg-light rounded border font-monospace text-xs text-primary break-all" id="detHash"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <a href="#" id="detBtnPdf" class="btn btn-danger btn-sm shadow-sm" style="display: none;" target="_blank">
                    <i class="fa-solid fa-file-pdf me-1"></i> Baixar PDF Oficial
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('modalAuditoria');
    const modal = new bootstrap.Modal(modalEl);
    const modalLoading = document.getElementById('modalLoading');
    const modalContent = document.getElementById('modalContent');

    document.querySelectorAll('.btn-detalhes').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            modalLoading.style.display = 'block';
            modalContent.style.display = 'none';
            modal.show();

            fetch(`/admin/desfiliacoes/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('detNome').innerText = data.nome_completo;
                    document.getElementById('detCpf').innerText = data.cpf;
                    document.getElementById('detMatricula').innerText = data.matricula;
                    document.getElementById('detEmpresa').innerText = data.empresa;
                    document.getElementById('detEmail').innerText = data.email;
                    document.getElementById('detMotivo').innerText = data.motivo;
                    document.getElementById('detIpSol').innerText = data.ip_solicitacao || 'Não registrado';
                    document.getElementById('detIpConf').innerText = data.ip_confirmacao || 'Não registrado';
                    document.getElementById('detDataCriacao').innerText = data.created_at || '-';
                    document.getElementById('detDataConf').innerText = data.confirmado_em || 'Pendente de confirmação';
                    document.getElementById('detUserAgent').innerText = data.user_agent_confirmacao || 'N/A';
                    document.getElementById('detHash').innerText = data.termo_hash || 'Aguardando finalização';

                    const btnPdf = document.getElementById('detBtnPdf');
                    if (data.tem_pdf) {
                        btnPdf.href = data.download_url;
                        btnPdf.style.display = 'inline-block';
                    } else {
                        btnPdf.style.display = 'none';
                    }

                    modalLoading.style.display = 'none';
                    modalContent.style.display = 'block';
                })
                .catch(err => {
                    alert('Erro ao carregar os dados de auditoria.');
                    modal.hide();
                });
        });
    });
});
</script>
@endsection
