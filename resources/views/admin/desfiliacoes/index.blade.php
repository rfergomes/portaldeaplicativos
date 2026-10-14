@extends('layouts.app')

@section('title', 'Gestão de Desfiliações Sindicais')

@section('content')
<div class="container-fluid">
    <!-- Header e Métricas -->
    <div class="row mb-3">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-2">
            <div class="card card-outline card-primary shadow-sm h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 me-3">
                        <i class="fa-solid fa-file-signature fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Total</div>
                        <div class="fs-4 fw-bold text-dark">{{ $totais['total'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-2">
            <div class="card card-outline card-secondary shadow-sm h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary p-3 me-3">
                        <i class="fa-solid fa-hourglass-half fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Pendente Assinatura</div>
                        <div class="fs-4 fw-bold text-dark">{{ $totais['pendentes'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-4 col-sm-6 mb-2">
            <div class="card card-outline card-warning shadow-sm h-100 border-warning">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-25 text-warning-emphasis p-3 me-3">
                        <i class="fa-solid fa-envelope-open-text fa-2x text-warning"></i>
                    </div>
                    <div>
                        <div class="text-warning-emphasis small text-uppercase fw-bold">
                            1ª Etapa: Pendente Recepção
                            @if($totais['confirmadas'] > 0)
                                <span class="badge bg-danger rounded-pill ms-1">Novo</span>
                            @endif
                        </div>
                        <div class="fs-4 fw-bold text-dark">{{ $totais['confirmadas'] }}</div>
                        <small class="text-muted text-xs">Aguardando conferência/ciência</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 col-sm-6 mb-2">
            <div class="card card-outline card-info shadow-sm h-100 border-info">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-15 text-info p-3 me-3">
                        <i class="fa-solid fa-clipboard-check fa-2x text-info"></i>
                    </div>
                    <div>
                        <div class="text-info-emphasis small text-uppercase fw-bold">
                            2ª Etapa: Pendente Baixa Ábaco
                            @if($totais['recepcionadas'] > 0)
                                <span class="badge bg-warning text-dark rounded-pill ms-1">Ação</span>
                            @endif
                        </div>
                        <div class="fs-4 fw-bold text-dark">{{ $totais['recepcionadas'] }}</div>
                        <small class="text-muted text-xs">Recepcionadas prontas para baixa</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-6 col-sm-6 mb-2">
            <div class="card card-outline card-success shadow-sm h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 me-3">
                        <i class="fa-solid fa-check-double fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Efetivadas</div>
                        <div class="fs-4 fw-bold text-success">{{ $totais['efetivadas'] }}</div>
                        <small class="text-muted text-xs">Processo concluído</small>
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
                    <label class="form-label small fw-bold">Status / Etapa</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos os status</option>
                        <option value="confirmada" {{ request('status') === 'confirmada' ? 'selected' : '' }}>1ª Etapa: Pendente Recepção/Ciência</option>
                        <option value="recepcionada" {{ request('status') === 'recepcionada' ? 'selected' : '' }}>2ª Etapa: Pendente Baixa no Ábaco</option>
                        <option value="efetivada" {{ request('status') === 'efetivada' ? 'selected' : '' }}>Efetivada (Baixa Realizada)</option>
                        <option value="recusada" {{ request('status') === 'recusada' ? 'selected' : '' }}>Recusada (Com Justificativa)</option>
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
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h3 class="card-title fw-bold m-0">
                <i class="fa-solid fa-list-check me-2"></i>Registros de Desfiliação
            </h3>
            <div class="d-flex align-items-center gap-2 ms-auto">
                @can('desfiliacoes.links.gerar')
                    <a href="{{ route('admin.desfiliacao.links.index') }}" class="btn btn-sm btn-primary shadow-sm fw-semibold">
                        <i class="fa-solid fa-link me-1"></i> Links Exclusivos
                    </a>
                @endcan
                <span class="badge bg-secondary">{{ $desfiliacoes->total() }} registros</span>
            </div>
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
                            <th class="text-center" style="width: 200px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($desfiliacoes as $item)
                            <tr class="{{ $item->isConfirmada() ? 'table-warning bg-opacity-25' : ($item->isRecepcionada() ? 'table-info bg-opacity-10' : '') }}">
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
                                        <span class="badge bg-warning text-dark rounded-pill px-2" title="Assinado pelo trabalhador. Aguardando aceite e ciência do sindicato.">
                                            <i class="fa-solid fa-envelope-open-text me-1"></i>1ª Etapa: Pendente Recepção
                                        </span>
                                        <div class="text-xs text-muted mt-1">Aguardando ciência interna</div>
                                    @elseif ($item->status === 'recepcionada')
                                        <span class="badge bg-info text-dark rounded-pill px-2" title="Carta recepcionada. Aguardando baixa cadastral no ERP Ábaco.">
                                            <i class="fa-solid fa-clipboard-check me-1"></i>2ª Etapa: Baixa no Ábaco
                                        </span>
                                        <div class="text-xs text-muted mt-1">
                                            <i class="fa-solid fa-stamp me-1"></i>Recepcionado por {{ $item->recepcionadoPor?->nickname ?: ($item->recepcionadoPor?->name ?? 'Operador') }}
                                            <span class="text-muted">({{ $item->recepcionado_em?->format('d/m/Y H:i') }})</span>
                                              @elseif ($item->status === 'efetivada')
                                        <span class="badge bg-success rounded-pill px-2" title="Baixa realizada no ERP Ábaco">
                                            <i class="fa-solid fa-check-double me-1"></i>Efetivada (Ábaco)
                                        </span>
                                        <div class="text-xs text-muted mt-1">
                                            <i class="fa-regular fa-user me-1"></i>Baixado por {{ $item->efetivadoPor?->nickname ?: ($item->efetivadoPor?->name ?? 'Operador') }}
                                            <span class="text-muted">({{ $item->efetivado_em?->format('d/m/Y H:i') }})</span>
                                        </div>
                                        @if($item->protocolo_abaco)
                                            <div class="text-xs font-monospace text-primary">
                                                <i class="fa-solid fa-hashtag me-1"></i>{{ $item->protocolo_abaco }}
                                            </div>
                                        @endif
                                    @elseif ($item->isRecusada())
                                        <span class="badge bg-danger rounded-pill px-2" title="{{ $item->motivo_recusa }}">
                                            <i class="fa-solid fa-ban me-1"></i>Recusada
                                        </span>
                                        <div class="text-xs text-danger mt-1" title="{{ $item->motivo_recusa }}">
                                            <i class="fa-regular fa-user me-1"></i>Por {{ $item->recusadoPor?->name ?? 'Operador' }}
                                            <span class="text-muted">({{ $item->recusado_em?->format('d/m/Y H:i') }})</span>
                                        </div>
                                    @elseif ($item->status === 'pendente')
                                        <span class="badge bg-secondary rounded-pill px-2">
                                            <i class="fa-solid fa-hourglass-start me-1"></i>Pendente Assinatura
                                        </span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill px-2">{{ ucfirst($item->status) }}</span>
                                    @endif

                                    {{-- Badges de Notificação da Empresa (AR-Online) --}}
                                    @if ($item->empresa_envio_status === 'enviado')
                                        <div class="mt-1">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle" title="Notificação oficial enviada ao RH da empresa via API AR-Online">
                                                <i class="fa-solid fa-stamp me-1"></i>AR-Online Notificada
                                            </span>
                                        </div>
                                    @elseif ($item->empresa_envio_status === 'pendente_contato')
                                        <div class="mt-1">
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" title="Empresa não possui e-mail de RH cadastrado">
                                                <i class="fa-solid fa-building-circle-exclamation me-1"></i>RH Empresa Pendente
                                            </span>
                                        </div>
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
                                        @if ($item->isConfirmada() && auth()->user()->can('desfiliacoes.recepcionar'))
                                            <button type="button" class="btn btn-warning btn-sm btn-abrir-recepcionar text-dark fw-bold" 
                                                data-id="{{ $item->id }}" 
                                                data-nome="{{ $item->nome_completo }}" 
                                                data-cpf="{{ $item->cpf_formatado }}" 
                                                data-empresa="{{ $item->empresa }}" 
                                                title="Homologar / Dar Aceite Formal">
                                                <i class="fa-solid fa-stamp me-1"></i>Aceitar
                                            </button>
                                        @elseif ($item->isRecepcionada() && auth()->user()->can('desfiliacoes.baixar_abaco'))
                                            <button type="button" class="btn btn-info btn-sm btn-abrir-efetivar text-dark fw-bold" 
                                                data-id="{{ $item->id }}" 
                                                data-nome="{{ $item->nome_completo }}" 
                                                data-cpf="{{ $item->cpf_formatado }}" 
                                                data-empresa="{{ $item->empresa }}" 
                                                title="Confirmar Baixa no ERP Ábaco">
                                                <i class="fa-solid fa-clipboard-check me-1"></i>Baixar Ábaco
                                            </button>
                                        @endif

                                        {{-- Botão de Recusa Justificada --}}
                                        @if ($item->isConfirmada() && auth()->user()->can('desfiliacoes.recusar'))
                                            <button type="button" class="btn btn-outline-danger btn-sm btn-abrir-recusa"
                                                data-id="{{ $item->id }}"
                                                data-nome="{{ $item->nome_completo }}"
                                                data-cpf="{{ $item->cpf_formatado }}"
                                                data-empresa="{{ $item->empresa }}"
                                                title="Recusar / Desaprovar com Justificativa">
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        @endif

                                        {{-- Botão para Notificar Empresa Pendente --}}
                                        @if ($item->empresa_envio_status === 'pendente_contato' && auth()->user()->can('desfiliacoes.recepcionar'))
                                            <button type="button" class="btn btn-warning btn-sm btn-abrir-notificar-empresa"
                                                data-id="{{ $item->id }}"
                                                data-nome="{{ $item->nome_completo }}"
                                                data-empresa="{{ $item->empresa }}"
                                                title="Informar e-mail de RH e Notificar Empresa via AR-Online">
                                                <i class="fa-solid fa-building-circle-arrow-right"></i>
                                            </button>
                                        @endif

                                        <button type="button" class="btn btn-outline-info btn-detalhes" data-id="{{ $item->id }}" title="Ver Detalhes e Trilha Probatória">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        @if (($item->isConfirmada() || $item->isRecepcionada() || $item->isEfetivada()) && $item->pdf_path)
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

<!-- Modal 1: Confirmar Recepção / Ciência da Carta de Desfiliação -->
<div class="modal fade" id="modalRecepcionar" tabindex="-1" aria-labelledby="modalRecepcionarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow border-warning">
            <form id="formRecepcionar" method="POST" action="">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fs-6 fw-bold" id="modalRecepcionarLabel">
                        <i class="fa-solid fa-stamp me-2"></i>Recepcionar Carta de Desfiliação (Ciência)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2 small mb-3">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Ao confirmar a recepção, você registra a <strong>ciência formal do sindicato</strong>. O documento oficial será atualizado com o carimbo institucional contendo seu nome e horário, e a solicitação passará para a etapa de <strong>baixa no ERP Ábaco</strong>.
                    </div>

                    <div class="card bg-light mb-3">
                        <div class="card-body p-2 small">
                            <div><strong>Trabalhador:</strong> <span id="recNome"></span></div>
                            <div><strong>CPF:</strong> <span id="recCpf" class="font-monospace"></span></div>
                            <div><strong>Empresa:</strong> <span id="recEmpresa"></span></div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="inputObservacaoRecepcao" class="form-label small fw-bold">Observações do Recebimento <span class="text-muted fw-normal">(Opcional)</span></label>
                        <textarea name="observacao_recepcao" id="inputObservacaoRecepcao" rows="3" class="form-control form-control-sm" placeholder="Ex: Carta conferida e recepcionada no departamento de arrecadação."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning btn-sm text-dark fw-bold shadow-sm">
                        <i class="fa-solid fa-stamp me-1"></i> Confirmar Recepção e Carimbar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Confirmar Baixa no ERP Ábaco (Efetivação) -->
<div class="modal fade" id="modalEfetivar" tabindex="-1" aria-labelledby="modalEfetivarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow border-info">
            <form id="formEfetivar" method="POST" action="">
                @csrf
                <div class="modal-header bg-info text-dark">
                    <h5 class="modal-title fs-6 fw-bold" id="modalEfetivarLabel">
                        <i class="fa-solid fa-clipboard-check me-2"></i>Confirmar Baixa no ERP Ábaco
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="fa-solid fa-circle-check me-1"></i>
                        Esta ação altera o status da solicitação para <strong>Efetivada</strong>, registrando seu usuário e horário como comprovação de que o sócio foi baixado no sistema ERP Ábaco.
                    </div>

                    <div class="card bg-light mb-3">
                        <div class="card-body p-2 small">
                            <div><strong>Sócio:</strong> <span id="efetivarNome"></span></div>
                            <div><strong>CPF:</strong> <span id="efetivarCpf" class="font-monospace"></span></div>
                            <div><strong>Empresa:</strong> <span id="efetivarEmpresa"></span></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="inputProtocoloAbaco" class="form-label small fw-bold">Nº de Protocolo / Código no Ábaco <span class="text-muted fw-normal">(Opcional)</span></label>
                        <input type="text" name="protocolo_abaco" id="inputProtocoloAbaco" class="form-control form-control-sm" placeholder="Ex: PROTO-2026-098 ou Código da Baixa">
                        <div class="form-text text-xs">Identificador ou número do chamado registrado no ERP Ábaco.</div>
                    </div>

                    <div class="mb-2">
                        <label for="inputObservacao" class="form-label small fw-bold">Observações da Baixa <span class="text-muted fw-normal">(Opcional)</span></label>
                        <textarea name="observacao_efetivacao" id="inputObservacao" rows="3" class="form-control form-control-sm" placeholder="Ex: Sócio baixado no módulo de arrecadação do ERP Ábaco pelo operador."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-sm shadow-sm">
                        <i class="fa-solid fa-check-double me-1"></i> Confirmar Baixa no ERP Ábaco
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 3: Detalhes de Auditoria e Trilha Probatória -->
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
                    <!-- Bloco de Recepção Institucional -->
                    <div id="blocoRecepcionada" class="alert alert-info d-flex align-items-center mb-3" style="display: none;">
                        <i class="fa-solid fa-stamp fs-3 me-3 text-info"></i>
                        <div class="small w-100">
                            <strong class="d-block text-info-emphasis">Carta Recepcionada pelo Sindicato (Ciência Formal)</strong>
                            <div><strong>Operador Recebedor:</strong> <span id="detRecepcionadoPor"></span></div>
                            <div><strong>Data/Hora do Recebimento:</strong> <span id="detDataRecepcao"></span></div>
                            <div id="detLinhaObsRecepcao"><strong>Observações:</strong> <span id="detObsRecepcao"></span></div>
                        </div>
                    </div>

                    <!-- Bloco de Notificação à Empresa (AR-Online) -->
                    <div id="blocoNotificacaoEmpresa" class="alert alert-secondary d-flex align-items-center mb-3" style="display: none;">
                        <i class="fa-solid fa-stamp fs-3 me-3 text-primary"></i>
                        <div class="small w-100">
                            <strong class="d-block text-primary-emphasis">Notificação Oficial à Empresa (AR-Online)</strong>
                            <div><strong>E-mail Destinatário:</strong> <span id="detEmpresaEmail"></span></div>
                            <div><strong>Status AR-Online:</strong> <span id="detEmpresaStatus" class="badge"></span></div>
                            <div id="detLinhaArOnlineId"><strong>ID AR-Online:</strong> <span id="detEmpresaArOnlineId" class="font-monospace"></span></div>
                            <div id="detLinhaNotificadaEm"><strong>Data de Despacho:</strong> <span id="detEmpresaNotificadaEm"></span></div>
                        </div>
                    </div>

                    <!-- Bloco de Recusa Justificada -->
                    <div id="blocoRecusada" class="alert alert-danger d-flex align-items-center mb-3" style="display: none;">
                        <i class="fa-solid fa-ban fs-3 me-3 text-danger"></i>
                        <div class="small w-100">
                            <strong class="d-block text-danger">Solicitação Recusada / Não Homologada</strong>
                            <div><strong>Operador Responsável:</strong> <span id="detRecusadoPor"></span></div>
                            <div><strong>Data/Hora da Recusa:</strong> <span id="detDataRecusa"></span></div>
                            <div class="mt-2 p-2 bg-white rounded border border-danger-subtle text-dark">
                                <strong>Justificativa da Recusa:</strong>
                                <div id="detMotivoRecusa" class="fst-italic mt-1 text-danger-emphasis"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Bloco de Efetivação / Baixa ERP Ábaco -->
                    <div id="blocoEfetivada" class="alert alert-success d-flex align-items-center mb-3" style="display: none;">
                        <i class="fa-solid fa-circle-check fs-3 me-3 text-success"></i>
                        <div class="small w-100">
                            <strong class="d-block text-success-emphasis">Baixa Realizada no ERP Ábaco (Efetivada)</strong>
                            <div><strong>Operador Responsável:</strong> <span id="detEfetivadoPor"></span></div>
                            <div><strong>Data/Hora da Baixa:</strong> <span id="detDataEfetivacao"></span></div>
                            <div id="detLinhaProtocolo"><strong>Protocolo Ábaco:</strong> <span id="detProtocoloAbaco" class="font-monospace"></span></div>
                            <div id="detLinhaObs"><strong>Observação:</strong> <span id="detObsEfetivacao"></span></div>
                        </div>
                    </div>

                    <!-- Avisos de Ação Pendente -->
                    @can('desfiliacoes.recepcionar')
                    <div id="blocoAvisoRecepcao" class="alert alert-warning d-flex align-items-center justify-content-between mb-3" style="display: none;">
                        <div class="d-flex align-items-center small">
                            <i class="fa-solid fa-envelope-open-text fs-3 me-3 text-warning"></i>
                            <div>
                                <strong class="d-block text-warning-emphasis">1ª Etapa Pendente: Recepção / Homologação</strong>
                                <span>O trabalhador assinou a solicitação. Confirme os dados no ERP Ábaco antes de homologar.</span>
                            </div>
                        </div>
                        <div class="d-flex gap-2 ms-3">
                            <button type="button" id="btnRecepcionarModal" class="btn btn-warning btn-sm text-dark fw-bold text-nowrap">
                                <i class="fa-solid fa-stamp me-1"></i>Aceitar
                            </button>
                            @can('desfiliacoes.recusar')
                                <button type="button" id="btnRecusarModal" class="btn btn-outline-danger btn-sm fw-bold text-nowrap">
                                    <i class="fa-solid fa-ban me-1"></i>Recusar
                                </button>
                            @endcan
                        </div>
                    </div>
                    @endcan

                    @can('desfiliacoes.baixar_abaco')
                    <div id="blocoAvisoBaixa" class="alert alert-info d-flex align-items-center justify-content-between mb-3" style="display: none;">
                        <div class="d-flex align-items-center small">
                            <i class="fa-solid fa-clipboard-check fs-3 me-3 text-info"></i>
                            <div>
                                <strong class="d-block text-info-emphasis">2ª Etapa Pendente: Baixa no ERP Ábaco</strong>
                                <span>A carta já foi recepcionada. Conclua o processo dando a baixa cadastral no ERP Ábaco.</span>
                            </div>
                        </div>
                        <button type="button" id="btnDarBaixaModal" class="btn btn-info btn-sm text-dark fw-bold ms-3 text-nowrap">
                            <i class="fa-solid fa-clipboard-check me-1"></i>Dar Baixa Agora
                        </button>
                    </div>
                    @endcan

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Nome Completo</label>
                            <div class="fw-bold" id="detNome"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">CPF</label>
                            <div class="font-monospace" id="detCpf"></div>
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
                    <i class="fa-solid fa-file-pdf me-1"></i> Baixar PDF Oficial com Carimbo
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

    const modalRecepcionarEl = document.getElementById('modalRecepcionar');
    const modalRecepcionar = new bootstrap.Modal(modalRecepcionarEl);
    const formRecepcionar = document.getElementById('formRecepcionar');

    const modalEfetivarEl = document.getElementById('modalEfetivar');
    const modalEfetivar = new bootstrap.Modal(modalEfetivarEl);
    const formEfetivar = document.getElementById('formEfetivar');

    let currentItemData = null;

    // Abertura do modal de Recepção / Aceite
    function abrirModalRecepcionar(id, nome, cpf, empresa) {
        formRecepcionar.action = `/admin/desfiliacoes/${id}/recepcionar`;
        document.getElementById('recNome').innerText = nome;
        document.getElementById('recCpf').innerText = cpf;
        document.getElementById('recEmpresa').innerText = empresa;
        document.getElementById('inputObservacaoRecepcao').value = '';
        modalRecepcionar.show();
    }

    // Abertura do modal de Efetivação / Baixa ERP Ábaco
    function abrirModalEfetivar(id, nome, cpf, empresa) {
        formEfetivar.action = `/admin/desfiliacoes/${id}/efetivar`;
        document.getElementById('efetivarNome').innerText = nome;
        document.getElementById('efetivarCpf').innerText = cpf;
        document.getElementById('efetivarEmpresa').innerText = empresa;
        document.getElementById('inputProtocoloAbaco').value = '';
        document.getElementById('inputObservacao').value = '';
        modalEfetivar.show();
    }

    // Gatilhos da tabela
    document.querySelectorAll('.btn-abrir-recepcionar').forEach(btn => {
        btn.addEventListener('click', function () {
            abrirModalRecepcionar(
                this.dataset.id,
                this.dataset.nome,
                this.dataset.cpf,
                this.dataset.empresa
            );
        });
    });

    document.querySelectorAll('.btn-abrir-efetivar').forEach(btn => {
        btn.addEventListener('click', function () {
            abrirModalEfetivar(
                this.dataset.id,
                this.dataset.nome,
                this.dataset.cpf,
                this.dataset.empresa
            );
        });
    });

    // Botões de ação dentro do modal de auditoria
    document.getElementById('btnRecepcionarModal').addEventListener('click', function() {
        if (currentItemData) {
            modalAuditoria.hide();
            abrirModalRecepcionar(
                currentItemData.id,
                currentItemData.nome_completo,
                currentItemData.cpf,
                currentItemData.empresa
            );
        }
    });

    document.getElementById('btnDarBaixaModal').addEventListener('click', function() {
        if (currentItemData) {
            modalAuditoria.hide();
            abrirModalEfetivar(
                currentItemData.id,
                currentItemData.nome_completo,
                currentItemData.cpf,
                currentItemData.empresa
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
                    document.getElementById('detEmpresa').innerText = data.empresa;
                    document.getElementById('detEmail').innerText = data.email;
                    document.getElementById('detMotivo').innerText = data.motivo;
                    document.getElementById('detIpSol').innerText = data.ip_solicitacao || 'Não registrado';
                    document.getElementById('detIpConf').innerText = data.ip_confirmacao || 'Não registrado';
                    document.getElementById('detDataCriacao').innerText = data.created_at || '-';
                    document.getElementById('detDataConf').innerText = data.confirmado_em || 'Pendente de confirmação';
                    document.getElementById('detUserAgent').innerText = data.user_agent_confirmacao || 'N/A';
                    document.getElementById('detHash').innerText = data.termo_hash || 'Aguardando finalização';

                    // Tratamento dos blocos de status
                    const blocoRecepcionada = document.getElementById('blocoRecepcionada');
                    const blocoEfetivada = document.getElementById('blocoEfetivada');
                    const blocoAvisoRecepcao = document.getElementById('blocoAvisoRecepcao');
                    const blocoAvisoBaixa = document.getElementById('blocoAvisoBaixa');

                    // 1. Dados de Recepção
                    if (data.recepcionado_em) {
                        blocoRecepcionada.style.display = 'flex';
                        document.getElementById('detRecepcionadoPor').innerText = data.recepcionado_por || 'Operador';
                        document.getElementById('detDataRecepcao').innerText = data.recepcionado_em;
                        if (data.observacao_recepcao) {
                            document.getElementById('detObsRecepcao').innerText = data.observacao_recepcao;
                            document.getElementById('detLinhaObsRecepcao').style.display = 'block';
                        } else {
                            document.getElementById('detLinhaObsRecepcao').style.display = 'none';
                        }
                    } else {
                        blocoRecepcionada.style.display = 'none';
                    }

                    // 2. Dados de Notificação AR-Online à Empresa
                    const blocoNotificacaoEmpresa = document.getElementById('blocoNotificacaoEmpresa');
                    if (data.empresa_notificada_em || data.empresa_aronline_id || data.empresa_envio_status === 'pendente_contato') {
                        blocoNotificacaoEmpresa.style.display = 'flex';
                        document.getElementById('detEmpresaEmail').innerText = data.empresa_email || 'Não informado / Pendente';
                        const statusBadge = document.getElementById('detEmpresaStatus');
                        if (data.empresa_envio_status === 'enviado') {
                            statusBadge.className = 'badge bg-success';
                            statusBadge.innerText = 'Enviado via AR-Online';
                        } else if (data.empresa_envio_status === 'pendente_contato') {
                            statusBadge.className = 'badge bg-warning text-dark';
                            statusBadge.innerText = 'Pendente (Empresa sem E-mail)';
                        } else {
                            statusBadge.className = 'badge bg-secondary';
                            statusBadge.innerText = data.empresa_envio_status || 'N/A';
                        }

                        document.getElementById('detEmpresaArOnlineId').innerText = data.empresa_aronline_id || 'Pendente';
                        document.getElementById('detEmpresaNotificadaEm').innerText = data.empresa_notificada_em || 'Pendente';
                    } else {
                        blocoNotificacaoEmpresa.style.display = 'none';
                    }

                    // 3. Dados de Recusa Justificada
                    const blocoRecusada = document.getElementById('blocoRecusada');
                    if (data.is_recusada) {
                        blocoRecusada.style.display = 'flex';
                        document.getElementById('detRecusadoPor').innerText = data.recusado_por || 'Operador';
                        document.getElementById('detDataRecusa').innerText = data.recusado_em || '-';
                        document.getElementById('detMotivoRecusa').innerText = data.motivo_recusa || '-';
                    } else {
                        blocoRecusada.style.display = 'none';
                    }

                    // 4. Dados de Efetivação / Baixa
                    if (data.is_efetivada) {
                        blocoEfetivada.style.display = 'flex';
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
                    } else {
                        blocoEfetivada.style.display = 'none';
                    }

                    // 5. Avisos de ação pendente
                    if (data.is_confirmada) {
                        blocoAvisoRecepcao.style.display = 'flex';
                        blocoAvisoBaixa.style.display = 'none';
                    } else if (data.is_recepcionada) {
                        blocoAvisoRecepcao.style.display = 'none';
                        blocoAvisoBaixa.style.display = 'flex';
                    } else {
                        blocoAvisoRecepcao.style.display = 'none';
                        blocoAvisoBaixa.style.display = 'none';
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

    // Handlers para o Modal de Recusa
    const modalRecusarEl = document.getElementById('modalRecusarSolicitacao');
    const modalRecusar = modalRecusarEl ? new bootstrap.Modal(modalRecusarEl) : null;
    const formRecusar = document.getElementById('formRecusarSolicitacao');

    function abrirModalRecusa(id, nome, cpf, empresa) {
        if (!modalRecusar) return;
        document.getElementById('recusaNomeTrabalhador').innerText = nome;
        document.getElementById('recusaCpfTrabalhador').innerText = cpf;
        document.getElementById('recusaEmpresaTrabalhador').innerText = empresa;
        formRecusar.action = `/admin/desfiliacoes/${id}/recusar`;
        document.getElementById('motivo_recusa').value = '';
        modalRecusar.show();
    }

    document.querySelectorAll('.btn-abrir-recusa').forEach(btn => {
        btn.addEventListener('click', function() {
            abrirModalRecusa(this.dataset.id, this.dataset.nome, this.dataset.cpf, this.dataset.empresa);
        });
    });

    const btnRecusarModal = document.getElementById('btnRecusarModal');
    if (btnRecusarModal) {
        btnRecusarModal.addEventListener('click', function() {
            if (currentItemData) {
                modalAuditoria.hide();
                abrirModalRecusa(
                    currentItemData.id,
                    currentItemData.nome_completo,
                    currentItemData.cpf,
                    currentItemData.empresa
                );
            }
        });
    }

    // Handlers para o Modal de Notificação à Empresa (AR-Online)
    const modalNotifEmpresaEl = document.getElementById('modalInformarEmailEmpresa');
    const modalNotifEmpresa = modalNotifEmpresaEl ? new bootstrap.Modal(modalNotifEmpresaEl) : null;
    const formNotificarEmpresa = document.getElementById('formNotificarEmpresa');

    function abrirModalNotifEmpresa(id, nome, empresa) {
        if (!modalNotifEmpresa) return;
        document.getElementById('empresaNotifRazaoSocial').innerText = empresa;
        document.getElementById('empresaNotifTrabalhador').innerText = nome;
        formNotificarEmpresa.action = `/admin/desfiliacoes/${id}/notificar-empresa`;
        document.getElementById('email_rh').value = '';
        modalNotifEmpresa.show();
    }

    document.querySelectorAll('.btn-abrir-notificar-empresa').forEach(btn => {
        btn.addEventListener('click', function() {
            abrirModalNotifEmpresa(this.dataset.id, this.dataset.nome, this.dataset.empresa);
        });
    });
});
</script>

{{-- Inclusão dos Modais de Ação --}}
@include('desfiliacoes.partials.modal-recusar')
@include('desfiliacoes.partials.modal-informar-email-empresa')
@endsection
