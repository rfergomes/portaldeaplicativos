@extends('layouts.app')

@section('title', 'Detalhes da Demanda')

@push('styles')
    <style>
        .details-card, .action-card, .timeline-card {
            border-radius: 12px;
            border: none;
        }
        .timeline-item-custom {
            position: relative;
            padding-left: 2.5rem;
            margin-bottom: 1.5rem;
        }
        .timeline-item-custom::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: -1.5rem;
            width: 2px;
            background: #e2e8f0;
        }
        .timeline-item-custom:last-child::before {
            display: none;
        }
        .timeline-icon-custom {
            position: absolute;
            left: 0;
            top: 0;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 0.85rem;
            z-index: 1;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        [data-bs-theme="dark"] .timeline-item-custom::before {
            background: rgba(255, 255, 255, 0.1);
        }
        .btn-premium {
            background: linear-gradient(135deg, #033c5a 0%, #0b72a6 100%);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-premium:hover {
            background: linear-gradient(135deg, #0b72a6 0%, #033c5a 100%);
            color: #ffffff;
        }
        .checklist-item {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .checklist-item:hover {
            background: #f8fafc;
        }
        [data-bs-theme="dark"] .checklist-item:hover {
            background: rgba(255, 255, 255, 0.02);
        }
        .checklist-done {
            text-decoration: line-through;
            color: #94a3b8;
        }
    </style>
@endpush

@section('content')
    <!-- Header Page -->
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <div>
            <a href="{{ route('demandas.index') }}" class="text-decoration-none text-muted">
                <i class="fa-solid fa-arrow-left me-1"></i> Voltar ao Painel
            </a>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()->temPermissao('demandas.gerenciar') || $demanda->criador_id === auth()->id())
                <a href="{{ route('demandas.edit', $demanda) }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Editar
                </a>
            @endif
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4">
        <!-- Coluna da Esquerda (Informações e Checklist) -->
        <div class="col-lg-8 col-12">
            <!-- Card de Detalhes -->
            <div class="card details-card shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        @php
                            $statusStyles = [
                                'aberta' => 'bg-primary text-white',
                                'aguardando' => 'bg-warning text-dark',
                                'executada' => 'bg-success text-white',
                                'nao_executada' => 'bg-danger text-white',
                                'cancelada' => 'bg-secondary text-white'
                            ];
                            $statusLabels = [
                                'aberta' => 'Aberta',
                                'aguardando' => 'Aguardando Devolutiva',
                                'executada' => 'Executada',
                                'nao_executada' => 'Não Executada',
                                'cancelada' => 'Cancelada'
                            ];
                            $stStyle = $statusStyles[$demanda->status] ?? 'bg-secondary';
                            $stLabel = $statusLabels[$demanda->status] ?? ucfirst($demanda->status);
                        @endphp
                        <span class="badge {{ $stStyle }} rounded-pill px-3 py-2 shadow-sm" style="font-size: 0.85rem;">
                            {{ $stLabel }}
                        </span>

                        <span class="text-muted small">
                            <i class="fa-regular fa-clock me-1"></i> Criada em {{ $demanda->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>

                    <h2 class="h4 fw-bold text-slate-800 mb-3">{{ $demanda->titulo }}</h2>
                    <div class="text-slate-700 mb-4" style="white-space: pre-wrap; line-height: 1.6;">{{ $demanda->descricao }}</div>

                    <hr class="my-4">

                    <!-- Metadados da Demanda -->
                    <div class="row g-3">
                        <div class="col-md-6 col-12">
                            <span class="text-muted d-block text-xs fw-bold text-uppercase">Criador da Demanda</span>
                            <span class="text-slate-800 fw-semibold">
                                <i class="fa-solid fa-user-pen text-secondary me-2"></i>{{ $demanda->criador->name }} ({{ $demanda->criador->nickname ?: 'Sistema' }})
                            </span>
                        </div>

                        <div class="col-md-6 col-12">
                            <span class="text-muted d-block text-xs fw-bold text-uppercase">Responsável Designado</span>
                            @if($demanda->tipo_responsavel === 'usuario')
                                <span class="text-slate-800 fw-semibold">
                                    <i class="fa-solid fa-user-tie text-primary me-2"></i>{{ $demanda->responsavelUsuario ? $demanda->responsavelUsuario->name : 'N/D' }}
                                </span>
                            @else
                                <span class="text-slate-800 fw-semibold d-block">
                                    <i class="fa-brands fa-whatsapp text-success me-2"></i>{{ $demanda->responsavel_nome }}
                                </span>
                                <small class="text-muted">
                                    WhatsApp: <a href="https://wa.me/55{{ $demanda->responsavel_telefone }}" target="_blank" class="text-success fw-semibold">{{ $demanda->responsavel_telefone }}</a>
                                </small>
                            @endif
                        </div>

                        <div class="col-md-6 col-12 mt-3">
                            <span class="text-muted d-block text-xs fw-bold text-uppercase">Prazo Limite</span>
                            <span class="fw-bold {{ $demanda->isVencida() ? 'text-danger' : 'text-slate-800' }}">
                                <i class="fa-regular fa-calendar-days me-2"></i>
                                {{ $demanda->prazo ? $demanda->prazo->format('d/m/Y H:i') : 'Sem prazo definido' }}
                                @if($demanda->isVencida())
                                    <span class="badge bg-danger rounded-pill ms-2">VENCIDA</span>
                                @endif
                            </span>
                        </div>

                        <div class="col-md-6 col-12 mt-3">
                            <span class="text-muted d-block text-xs fw-bold text-uppercase">Prioridade</span>
                            @php
                                $prioLabels = [
                                    'baixa' => ['text-secondary', 'Baixa', 'fa-circle-arrow-down'],
                                    'media' => ['text-primary', 'Média', 'fa-circle-dot'],
                                    'alta' => ['text-warning', 'Alta', 'fa-triangle-exclamation'],
                                    'urgente' => ['text-danger', 'Urgente', 'fa-circle-exclamation']
                                ];
                                $p = $prioLabels[$demanda->prioridade] ?? ['text-secondary', 'Média', 'fa-circle-dot'];
                            @endphp
                            <span class="fw-semibold {{ $p[0] }}">
                                <i class="fa-solid {{ $p[2] }} me-2"></i>{{ $p[1] }}
                            </span>
                        </div>
                    </div>

                    <!-- Devolutiva Registrada (Se houver) -->
                    @if($demanda->devolutiva_em)
                        <div class="mt-4 p-3 rounded bg-light border border-secondary-subtle">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-bold text-slate-800 mb-0"><i class="fa-solid fa-reply-all text-success me-2"></i>Devolutiva Registrada</h6>
                                <small class="text-muted">{{ $demanda->devolutiva_em->format('d/m/Y H:i') }}</small>
                            </div>
                            <p class="mb-0 text-slate-700" style="white-space: pre-wrap;">{{ $demanda->motivo_devolutiva }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Card de Checklist -->
            <div class="card details-card shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title fw-bold text-slate-800 mb-0 d-flex align-items-center">
                        <i class="fa-solid fa-list-check text-success me-2"></i> Checklist de Sub-tarefas
                    </h5>
                </div>
                <div class="card-body p-0 border-top">
                    @php
                        $checklistItems = $demanda->checklists;
                        $totalChecklist = $checklistItems->count();
                        $progresso = $demanda->progresso_checklist;
                    @endphp

                    @if($totalChecklist > 0)
                        <!-- Barra de Progresso -->
                        <div class="p-3 bg-light border-bottom d-flex align-items-center justify-content-between">
                            <div class="progress w-100 me-3" style="height: 10px; border-radius: 5px;">
                                <div id="checklist_progress_bar" class="progress-bar bg-success" role="progressbar" style="width: {{ $progresso }}%" aria-valuenow="{{ $progresso }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span id="checklist_progress_text" class="fw-bold text-success" style="font-size: 0.9rem;">{{ $progresso }}%</span>
                        </div>

                        <!-- Lista de Itens -->
                        <div class="list-group list-group-flush">
                            @foreach($checklistItems as $item)
                                <div class="list-group-item d-flex align-items-center py-3 px-4 checklist-item">
                                    <div class="form-check me-2">
                                        <input class="form-check-input check-item-checkbox" type="checkbox" data-id="{{ $item->id }}" id="item_check_{{ $item->id }}" {{ $item->concluido ? 'checked' : '' }} style="cursor: pointer;">
                                    </div>
                                    <label class="form-check-label flex-grow-1 check-item-label {{ $item->concluido ? 'checklist-done' : 'text-slate-800' }}" for="item_check_{{ $item->id }}" style="cursor: pointer; margin-bottom: 0;">
                                        {{ $item->item }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fa-solid fa-rectangle-list fa-2x mb-2 opacity-25"></i>
                            <p class="mb-0 small">Esta demanda não possui sub-tarefas cadastradas.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Card de Anexos -->
            <div class="card details-card shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title fw-bold text-slate-800 mb-0 d-flex align-items-center">
                        <i class="fa-solid fa-paperclip text-muted me-2"></i> Arquivos Anexos
                    </h5>
                </div>
                <div class="card-body p-0 border-top">
                    @php
                        $anexosInstrucao = $demanda->anexos->where('tipo_origem', 'criador');
                        $anexosDevolutiva = $demanda->anexos->where('tipo_origem', 'devolutiva');
                    @endphp

                    @if($demanda->anexos->count() > 0)
                        <div class="p-3">
                            <!-- Anexos do Criador -->
                            @if($anexosInstrucao->count() > 0)
                                <h6 class="fw-bold text-slate-600 mb-2 small text-uppercase">Instruções / Cadastro Inicial</h6>
                                <div class="list-group list-group-flush mb-3">
                                    @foreach($anexosInstrucao as $anexo)
                                        <a href="{{ asset('storage/' . $anexo->caminho) }}" target="_blank" class="list-group-item list-group-item-action border-0 rounded d-flex align-items-center py-2 px-3 mb-1" style="background: #f8fafc;">
                                            <i class="fa-solid fa-file-pdf text-danger fs-5 me-3"></i>
                                            <div class="flex-grow-1">
                                                <span class="d-block text-slate-700 fw-semibold" style="font-size: 0.85rem;">{{ $anexo->nome_original }}</span>
                                            </div>
                                            <i class="fa-solid fa-download text-muted"></i>
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Anexos de Devolutiva -->
                            @if($anexosDevolutiva->count() > 0)
                                <h6 class="fw-bold text-slate-600 mb-2 small text-uppercase">Comprovantes da Devolutiva</h6>
                                <div class="list-group list-group-flush">
                                    @foreach($anexosDevolutiva as $anexo)
                                        <a href="{{ asset('storage/' . $anexo->caminho) }}" target="_blank" class="list-group-item list-group-item-action border-0 rounded d-flex align-items-center py-2 px-3 mb-1" style="background: #f8fafc;">
                                            <i class="fa-solid fa-file-image text-success fs-5 me-3"></i>
                                            <div class="flex-grow-1">
                                                <span class="d-block text-slate-700 fw-semibold" style="font-size: 0.85rem;">{{ $anexo->nome_original }}</span>
                                            </div>
                                            <i class="fa-solid fa-download text-muted"></i>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fa-solid fa-file-circle-minus fa-2x mb-2 opacity-25"></i>
                            <p class="mb-0 small">Nenhum arquivo anexado a esta demanda.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Coluna da Direita (Ações e Histórico/Timeline) -->
        <div class="col-lg-4 col-12">
            <!-- Painel de Ações -->
            @php
                $podeResponder = auth()->user()->temPermissao('demandas.gerenciar') || 
                    $demanda->criador_id === auth()->id() || 
                    ($demanda->tipo_responsavel === 'usuario' && $demanda->responsavel_usuario_id === auth()->id());
            @endphp
            @if($podeResponder && in_array($demanda->status, ['aberta', 'aguardando']))
                <div class="card action-card shadow-sm mb-4 border border-secondary-subtle">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title fw-bold text-slate-800 mb-0"><i class="fa-solid fa-gears text-primary me-2"></i>Ações Disponíveis</h5>
                    </div>
                    <div class="card-body p-3 border-top">
                        <div class="d-grid gap-2">
                            <!-- Registrar Devolutiva -->
                            <button type="button" class="btn btn-premium py-2" data-bs-toggle="modal" data-bs-target="#devolutivaModal">
                                <i class="fa-solid fa-reply-all me-2"></i>Registrar Devolutiva
                            </button>

                            <!-- Reencaminhar (Apenas criador ou gestor) -->
                            @if(auth()->user()->temPermissao('demandas.gerenciar') || $demanda->criador_id === auth()->id())
                                <button type="button" class="btn btn-outline-secondary py-2" data-bs-toggle="modal" data-bs-target="#reencaminharModal">
                                    <i class="fa-solid fa-share-from-square me-2"></i>Reencaminhar Demanda
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Painel de Exclusão Administrativa -->
            @if(auth()->user()->temPermissao('demandas.gerenciar'))
                <div class="card action-card shadow-sm mb-4 border border-danger-subtle bg-danger-subtle bg-opacity-25">
                    <div class="card-body p-3">
                        <span class="d-block small text-danger fw-bold mb-2">Área Administrativa</span>
                        <form method="POST" action="{{ route('demandas.destroy', $demanda) }}" onsubmit="return confirm('Deseja realmente excluir esta demanda permanentemente? Todas as sub-tarefas e anexos serão apagados.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                <i class="fa-solid fa-trash-can me-2"></i>Excluir Demanda
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Card de Timeline -->
            <div class="card timeline-card shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title fw-bold text-slate-800 mb-0 d-flex align-items-center">
                        <i class="fa-solid fa-timeline text-indigo me-2"></i> Histórico de Tramitação
                    </h5>
                </div>
                <div class="card-body p-4 border-top">
                    <div class="timeline-custom">
                        @forelse($demanda->historicos as $hist)
                            @php
                                $iconMap = [
                                    'criada' => ['fa-plus', 'bg-primary'],
                                    'notificada_whatsapp' => ['fa-whatsapp', 'bg-success'],
                                    'encaminhada' => ['fa-share', 'bg-info'],
                                    'alterada' => ['fa-pen', 'bg-secondary'],
                                    'devolutiva' => ['fa-reply', 'bg-success'],
                                    'cancelada' => ['fa-xmark', 'bg-danger']
                                ];
                                $icon = $iconMap[$hist->acao] ?? ['fa-circle-info', 'bg-secondary'];
                            @endphp
                            <div class="timeline-item-custom">
                                <div class="timeline-icon-custom {{ $icon[1] }}">
                                    <i class="fa-solid {{ $icon[0] }}"></i>
                                </div>
                                <div class="timeline-content-custom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-slate-800 small text-capitalize" style="font-size: 0.8rem;">
                                            {{ str_replace('_', ' ', $hist->acao) }}
                                        </span>
                                        <span class="text-muted text-xs" style="font-size: 0.7rem;">{{ $hist->created_at->format('d/m H:i') }}</span>
                                    </div>
                                    <p class="mb-0 text-slate-600 small" style="font-size: 0.75rem;">{{ $hist->descricao }}</p>
                                    @if($hist->user)
                                        <small class="text-secondary d-block mt-1" style="font-size: 0.65rem;">
                                            Operador: {{ $hist->user->nickname ?: $hist->user->name }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-muted text-center py-3 small">Nenhum registro de histórico.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modais de Ações -->

    <!-- Modal: Devolutiva -->
    <div class="modal fade" id="devolutivaModal" tabindex="-1" aria-labelledby="devolutivaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('demandas.devolutiva', $demanda) }}" enctype="multipart/form-data" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="devolutivaModalLabel"><i class="fa-solid fa-reply-all text-success me-2"></i>Registrar Devolutiva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Status Devolutiva -->
                    <div class="mb-3">
                        <label for="devolutiva_status" class="form-label fw-bold text-secondary">A demanda foi executada?</label>
                        <select class="form-select" id="devolutiva_status" name="status" required>
                            <option value="executada" {{ $demanda->status == 'executada' ? 'selected' : '' }}>Sim, Executada com Sucesso</option>
                            <option value="nao_executada" {{ $demanda->status == 'nao_executada' ? 'selected' : '' }}>Não, Não Executada / Falhou</option>
                        </select>
                    </div>

                    <!-- Justificativa -->
                    <div class="mb-3">
                        <label for="motivo_devolutiva" class="form-label fw-bold text-secondary">Justificativa / Parecer Técnico <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="motivo_devolutiva" name="motivo_devolutiva" rows="4" required placeholder="Insira as observações sobre a execução (ou o motivo pelo qual não foi possível executar)..."></textarea>
                    </div>

                    <!-- Comprovante -->
                    <div class="mb-3">
                        <label for="devolutiva_anexos" class="form-label fw-bold text-secondary">Anexar Comprovantes (Fotos/Documentos)</label>
                        <input type="file" class="form-control" id="devolutiva_anexos" name="anexos[]" multiple>
                        <small class="text-muted text-xs">Opcional. Ex: Foto do serviço concluído, recibo, etc.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-premium">Salvar Devolutiva</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Reencaminhar -->
    <div class="modal fade" id="reencaminharModal" tabindex="-1" aria-labelledby="reencaminharModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('demandas.reencaminhar', $demanda) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="reencaminharModalLabel"><i class="fa-solid fa-share-from-square text-primary me-2"></i>Reencaminhar Demanda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Motivo -->
                    <div class="mb-4">
                        <label for="motivo_reencaminhamento" class="form-label fw-bold text-secondary">Motivo do Reencaminhamento <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="motivo_reencaminhamento" name="motivo_reencaminhamento" rows="3" required placeholder="Por que esta demanda está mudando de responsável? Ex: Redistribuição de tarefas, fornecedor indisponível..."></textarea>
                    </div>

                    <!-- Novo Responsável Seleção -->
                    <div class="card p-3 border border-secondary-subtle">
                        <h6 class="fw-bold mb-3"><i class="fa-solid fa-user-plus text-primary me-2"></i>Novo Responsável</h6>

                        <!-- Tipo Novo Responsável -->
                        <div class="mb-3">
                            <label class="form-label d-block fw-semibold text-muted">Tipo de Responsável</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input new-tipo-responsavel" type="radio" name="tipo_responsavel" id="new_tipo_interno" value="usuario" checked>
                                <label class="form-check-label" for="new_tipo_interno">Usuário Interno</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input new-tipo-responsavel" type="radio" name="tipo_responsavel" id="new_tipo_externo" value="externo">
                                <label class="form-check-label" for="new_tipo_externo">Contato Externo (WhatsApp)</label>
                            </div>
                        </div>

                        <!-- Novo Usuário Interno -->
                        <div id="new_div_responsavel_interno" class="mb-2">
                            <label for="new_responsavel_usuario_id" class="form-label small fw-bold text-secondary">Selecione o Novo Usuário</label>
                            <select class="form-select select2-modal" id="new_responsavel_usuario_id" name="responsavel_usuario_id" style="width: 100%;">
                                <option value="">Selecione...</option>
                                @foreach($usuarios as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Novo Contato Externo -->
                        <div id="new_div_responsavel_externo" style="display: none;">
                            <div class="row g-2">
                                <div class="col-md-6 col-12">
                                    <label for="new_responsavel_nome" class="form-label small fw-bold text-secondary">Nome do Responsável <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="new_responsavel_nome" name="responsavel_nome" placeholder="Nome do contato externo">
                                </div>
                                <div class="col-md-6 col-12">
                                    <label for="new_responsavel_telefone" class="form-label small fw-bold text-secondary">Telefone WhatsApp <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control cell-mask" id="new_responsavel_telefone" name="responsavel_telefone" placeholder="(99) 99999-9999">
                                </div>
                                <div class="col-12 mt-2">
                                    <label for="new_responsavel_email" class="form-label small fw-bold text-secondary">Email (Opcional)</label>
                                    <input type="email" class="form-control" id="new_responsavel_email" name="responsavel_email" placeholder="contato@email.com">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-premium">Reencaminhar Demanda</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- InputMask & Select2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2 nos modais
            $('#reencaminharModal').on('shown.bs.modal', function () {
                $('.select2-modal').select2({
                    dropdownParent: $('#reencaminharModal'),
                    theme: 'bootstrap-5'
                });
            });

            // Máscara
            $('.cell-mask').inputmask({
                mask: ['(99) 9999-9999', '(99) 99999-9999'],
                keepStatic: true,
                removeMaskOnSubmit: true
            });

            // Toggle Novo Tipo Responsável no Modal
            $('.new-tipo-responsavel').change(function() {
                if (this.value === 'usuario') {
                    $('#new_div_responsavel_interno').slideDown();
                    $('#new_div_responsavel_externo').slideUp();
                    $('#new_responsavel_nome, #new_responsavel_telefone').prop('required', false);
                    $('#new_responsavel_usuario_id').prop('required', true);
                } else {
                    $('#new_div_responsavel_interno').slideUp();
                    $('#new_div_responsavel_externo').slideDown();
                    $('#new_responsavel_usuario_id').prop('required', false);
                    $('#new_responsavel_nome, #new_responsavel_telefone').prop('required', true);
                }
            });
        });

        // Clique na linha do checklist
        $('.checklist-item').on('click', function(e) {
            // Se clicar no checkbox ou no label, deixa o comportamento padrão do navegador acontecer
            if ($(e.target).closest('.form-check').length > 0 || $(e.target).is('label')) {
                return;
            }
            
            var checkbox = $(this).find('.check-item-checkbox');
            checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
        });

        // AJAX: Marcar/Desmarcar Checklist
        $('.check-item-checkbox').on('change', function() {
            var checkbox = $(this);
            var isChecked = checkbox.prop('checked');
            var id = checkbox.data('id');
            var label = checkbox.closest('.checklist-item').find('.check-item-label');

            $.ajax({
                url: "{{ route('demandas.index') }}/" + "{{ $demanda->id }}" + "/checklists/" + id + "/toggle",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    concluido: isChecked ? 1 : 0
                },
                success: function(response) {
                    if (response.success) {
                        // Atualizar estilo visual
                        if (isChecked) {
                            label.addClass('checklist-done').removeClass('text-slate-800');
                        } else {
                            label.removeClass('checklist-done').addClass('text-slate-800');
                        }
                        
                        // Atualizar barra de progresso
                        $('#checklist_progress_bar').css('width', response.progresso + '%').attr('aria-valuenow', response.progresso);
                        $('#checklist_progress_text').text(response.progresso + '%');

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Progresso salvo!',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                },
                error: function(xhr) {
                    // Reverter visual se falhar
                    checkbox.prop('checked', !isChecked);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: xhr.responseJSON.message || 'Não foi possível salvar o progresso.'
                    });
                }
            });
        });
    </script>
@endpush
