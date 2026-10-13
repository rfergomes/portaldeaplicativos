@extends('layouts.app')

@section('title', 'Gestão de Desfiliações Sindicais')

@section('content')
<div class="container-fluid">
    <!-- Header e Métricas -->
    <div class="row mb-3">
        <div class="col-md-3 col-sm-6 mb-2">
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
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card card-outline card-secondary shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary p-3 me-3">
                        <i class="fa-solid fa-hourglass-half fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Pendentes de Assinatura</div>
                        <div class="fs-4 fw-bold text-dark">{{ $totais['pendentes'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card card-outline card-warning shadow-sm h-100 border-warning">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-25 text-warning-emphasis p-3 me-3">
                        <i class="fa-solid fa-clock-rotate-left fa-2x text-warning"></i>
                    </div>
                    <div>
                        <div class="text-warning-emphasis small text-uppercase fw-bold">
                            Aguardando Baixa Ábaco
                            @if($totais['confirmadas'] > 0)
                                <span class="badge bg-danger rounded-pill ms-1 animate-pulse">Ação necessária</span>
                            @endif
                        </div>
                        <div class="fs-4 fw-bold text-dark">{{ $totais['confirmadas'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="card card-outline card-success shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 me-3">
                        <i class="fa-solid fa-check-double fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Efetivadas (Baixa no Ábaco)</div>
                        <div class="fs-4 fw-bold text-success">{{ $totais['efetivadas'] }}</div>
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
                        <option value="">Todos os status</option>
                        <option value="confirmada" {{ request('status') === 'confirmada' ? 'selected' : '' }}>Aguardando Baixa Ábaco</option>
                        <option value="efetivada" {{ request('status') === 'efetivada' ? 'selected' : '' }}>Efetivada (Baixa Concluída)</option>
                        <option value="pendente" {{ request('status') === 'pendente' ? 'selected' : '' }}>Pendente de Assinatura</option>
                        <option value="expirada" {{ request('status') === 'expirada' ? 'selected' : '' }}>Expirada</option>
                        <option value="cancelada" {{ request('status') === 'cancelada' ? 'selected' : '' }}>Cancelada</option>
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
                    <a href="{{ route('admin.desfiliacoes.index') }}" class="btn btn-outline-secondary btn-sm" title="Limpar Filtros">
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
                            <th>Status / Providências</th>
                            <th>Hash / Validação</th>
                            <th class="text-center" style="width: 170px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($desfiliacoes as $item)
                            <tr class="{{ $item->isConfirmada() ? 'table-warning bg-opacity-25' : '' }}">
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
                                        <span class="badge bg-warning text-dark rounded-pill px-2" title="Assinado pelo trabalhador. Aguardando baixa no ERP Ábaco.">
                                            <i class="fa-solid fa-clock-rotate-left me-1"></i>Aguardando Baixa Ábaco
                                        </span>
                                    @elseif ($item->status === 'efetivada')
                                        <span class="badge bg-success rounded-pill px-2" title="Baixa realizada no ERP Ábaco">
                                            <i class="fa-solid fa-check-double me-1"></i>Efetivada (Ábaco)
                                        </span>
                                        <div class="text-xs text-muted mt-1">
                                            <i class="fa-regular fa-user me-1"></i>{{ $item->efetivadoPor?->nickname ?: ($item->efetivadoPor?->name ?? 'Operador') }}
                                            <span class="text-muted">({{ $item->efetivado_em?->format('d/m/Y H:i') }})</span>
                                        </div>
                                        @if($item->protocolo_abaco)
                                            <div class="text-xs font-monospace text-primary">
                                                <i class="fa-solid fa-hashtag me-1"></i>{{ $item->protocolo_abaco }}
                                            </div>
                                        @endif
                                    @elseif ($item->status === 'pendente')
                                        <span class="badge bg-secondary rounded-pill px-2">
                                            <i class="fa-solid fa-hourglass-start me-1"></i>Pendente Assinatura
                                        </span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill px-2">{{ ucfirst($item->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($item->termo_hash)
                                        <span class="font-monospace text-xs text-primary" title="{{ $item->termo_hash }}">
                                            {{ substr($item->termo_hash, 0, 10) }}...
                                        </span>
                                        <a href="{{ route('desfiliacao.validar', ['hash' => $item->termo_hash]) }}" target="_blank" class="ms-1 text-muted" title="Verificar autenticidade pública">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                    @else
                                        <span class="text-muted text-xs">Aguardando assinatura</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        @if ($item->isConfirmada())
                                            <button type="button" class="btn btn-warning btn-sm btn-abrir-efetivar text-dark fw-semibold" 
                                                data-id="{{ $item->id }}" 
                                                data-nome="{{ $item->nome_completo }}" 
                                                data-cpf="{{ $item->cpf_formatado }}" 
                                                data-empresa="{{ $item->empresa }}" 
                                                data-matricula="{{ $item->matricula ?: 'Não informada' }}" 
                                                title="Confirmar Baixa no ERP Ábaco">
                                                <i class="fa-solid fa-clipboard-check me-1"></i>Dar Baixa
                                            </button>
                                        @endif

                                        <button type="button" class="btn btn-outline-info btn-detalhes" data-id="{{ $item->id }}" title="Ver Detalhes e Trilha Probatória">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        @if (($item->isConfirmada() || $item->isEfetivada()) && $item->pdf_path)
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

<!-- Modal para Confirmar Baixa no ERP Ábaco (Efetivação) -->
<div class="modal fade" id="modalEfetivar" tabindex="-1" aria-labelledby="modalEfetivarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow border-warning">
            <form id="formEfetivar" method="POST" action="">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fs-6 fw-bold" id="modalEfetivarLabel">
                        <i class="fa-solid fa-clipboard-check me-2"></i>Confirmar Baixa no ERP Ábaco
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2 small mb-3">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        Esta ação altera o status da solicitação para <strong>Efetivada</strong>, registrando seu usuário e horário como comprovação de que o sócio foi baixado no sistema ERP Ábaco.
                    </div>

                    <div class="card bg-light mb-3">
                        <div class="card-body p-2 small">
                            <div><strong>Sócio:</strong> <span id="efetivarNome"></span></div>
                            <div><strong>CPF:</strong> <span id="efetivarCpf" class="font-monospace"></span></div>
                            <div><strong>Empresa:</strong> <span id="efetivarEmpresa"></span></div>
                            <div><strong>Matrícula:</strong> <span id="efetivarMatricula"></span></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="inputProtocoloAbaco" class="form-label small fw-bold">Nº de Protocolo / Código no Ábaco <span class="text-muted fw-normal">(Opcional)</span></label>
                        <input type="text" name="protocolo_abaco" id="inputProtocoloAbaco" class="form-control form-control-sm" placeholder="Ex: PROTO-2026-098 ou Código de Baixa">
                        <div class="form-text text-xs">Identificador ou número do chamado registrado no ERP Ábaco.</div>
                    </div>

                    <div class="mb-2">
                        <label for="inputObservacao" class="form-label small fw-bold">Observações da Efetivação <span class="text-muted fw-normal">(Opcional)</span></label>
                        <textarea name="observacao_efetivacao" id="inputObservacao" rows="3" class="form-control form-control-sm" placeholder="Ex: Sócio baixado no módulo de arrecadação do ERP Ábaco pelo operador."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-sm shadow-sm">
                        <i class="fa-solid fa-check-double me-1"></i> Confirmar Baixa no Ábaco
                    </button>
                </div>
            </form>
        </div>
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
                    <!-- Bloco de Status de Efetivação / Baixa ERP Ábaco -->
                    <div id="blocoEfetivada" class="alert alert-success d-flex align-items-center mb-3" style="display: none;">
                        <i class="fa-solid fa-circle-check fs-3 me-3 text-success"></i>
                        <div class="small">
                            <strong class="d-block text-success-emphasis">Baixa Realizada no ERP Ábaco (Efetivada)</strong>
                            <div><strong>Operador Responsável:</strong> <span id="detEfetivadoPor"></span></div>
                            <div><strong>Data/Hora da Baixa:</strong> <span id="detDataEfetivacao"></span></div>
                            <div id="detLinhaProtocolo"><strong>Protocolo Ábaco:</strong> <span id="detProtocoloAbaco" class="font-monospace"></span></div>
                            <div id="detLinhaObs"><strong>Observação:</strong> <span id="detObsEfetivacao"></span></div>
                        </div>
                    </div>

                    <div id="blocoPendenteBaixa" class="alert alert-warning d-flex align-items-center justify-content-between mb-3" style="display: none;">
                        <div class="d-flex align-items-center small">
                            <i class="fa-solid fa-clock-rotate-left fs-3 me-3 text-warning"></i>
                            <div>
                                <strong class="d-block text-warning-emphasis">Aguardando Baixa no ERP Ábaco</strong>
                                <span>O trabalhador já confirmou e assinou o termo. É necessário realizar a baixa no sistema Ábaco.</span>
                            </div>
                        </div>
                        <button type="button" id="btnDarBaixaModal" class="btn btn-warning btn-sm text-dark fw-bold ms-3">
                            <i class="fa-solid fa-clipboard-check me-1"></i>Dar Baixa Agora
                        </button>
                    </div>

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
    const modalAuditoriaEl = document.getElementById('modalAuditoria');
    const modalAuditoria = new bootstrap.Modal(modalAuditoriaEl);
    const modalLoading = document.getElementById('modalLoading');
    const modalContent = document.getElementById('modalContent');

    const modalEfetivarEl = document.getElementById('modalEfetivar');
    const modalEfetivar = new bootstrap.Modal(modalEfetivarEl);
    const formEfetivar = document.getElementById('formEfetivar');

    let currentItemData = null;

    // Abertura do modal de Efetivação / Baixa ERP Ábaco
    function abrirModalEfetivar(id, nome, cpf, empresa, matricula) {
        formEfetivar.action = `/admin/desfiliacoes/${id}/efetivar`;
        document.getElementById('efetivarNome').innerText = nome;
        document.getElementById('efetivarCpf').innerText = cpf;
        document.getElementById('efetivarEmpresa').innerText = empresa;
        document.getElementById('efetivarMatricula').innerText = matricula;
        document.getElementById('inputProtocoloAbaco').value = '';
        document.getElementById('inputObservacao').value = '';
        modalEfetivar.show();
    }

    document.querySelectorAll('.btn-abrir-efetivar').forEach(btn => {
        btn.addEventListener('click', function () {
            abrirModalEfetivar(
                this.dataset.id,
                this.dataset.nome,
                this.dataset.cpf,
                this.dataset.empresa,
                this.dataset.matricula
            );
        });
    });

    // Botão de Dar Baixa dentro do Modal de Auditoria
    document.getElementById('btnDarBaixaModal').addEventListener('click', function() {
        if (currentItemData) {
            modalAuditoria.hide();
            abrirModalEfetivar(
                currentItemData.id,
                currentItemData.nome_completo,
                currentItemData.cpf,
                currentItemData.empresa,
                currentItemData.matricula
            );
        }
    });

    // Detalhes de Auditoria
    document.querySelectorAll('.btn-detalhes').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            modalLoading.style.display = 'block';
            modalContent.style.display = 'none';
            modalAuditoria.show();

            fetch(`/admin/desfiliacoes/${id}`)
                .then(res => res.json())
                .then(data => {
                    currentItemData = data;
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

                    // Tratamento do bloco de status de baixa ERP Ábaco
                    const blocoEfetivada = document.getElementById('blocoEfetivada');
                    const blocoPendenteBaixa = document.getElementById('blocoPendenteBaixa');

                    if (data.is_efetivada) {
                        blocoEfetivada.style.display = 'flex';
                        blocoPendenteBaixa.style.display = 'none';
                        document.getElementById('detEfetivadoPor').innerText = data.efetivado_por || 'Operador';
                        document.getElementById('detDataEfetivacao').innerText = data.efetivado_em || '-';
                        if (data.protocolo_abaco) {
                            document.getElementById('detProtocoloAbaco').innerText = data.protocolo_abaco;
                            document.getElementById('detLinhaProtocolo').style.display = 'block';
                        } else {
                            document.getElementById('detLinhaProtocolo').style.display = 'none';
                        }
                        if (data.observacao_efetivacao) {
                            document.getElementById('detObsEfetivacao').innerText = data.observacao_efetivacao;
                            document.getElementById('detLinhaObs').style.display = 'block';
                        } else {
                            document.getElementById('detLinhaObs').style.display = 'none';
                        }
                    } else if (data.is_confirmada) {
                        blocoEfetivada.style.display = 'none';
                        blocoPendenteBaixa.style.display = 'flex';
                    } else {
                        blocoEfetivada.style.display = 'none';
                        blocoPendenteBaixa.style.display = 'none';
                    }

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
                    modalAuditoria.hide();
                });
        });
    });
});
</script>
@endsection
