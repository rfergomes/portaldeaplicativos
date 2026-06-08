@extends('layouts.app')

@section('title', 'Painel de Demandas')

@push('styles')
    <style>
        .stat-card {
            border-radius: 12px;
            border: none;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1) !important;
        }
        .filter-card {
            border-radius: 12px;
            border: none;
            background: #ffffff;
        }
        [data-bs-theme="dark"] .filter-card {
            background: var(--bs-secondary-bg);
        }
        .btn-premium {
            background: linear-gradient(135deg, #033c5a 0%, #0b72a6 100%);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .btn-premium:hover {
            background: linear-gradient(135deg, #0b72a6 0%, #033c5a 100%);
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(11, 114, 166, 0.3);
        }
        .badge-prioridade-urgente { background-color: #ffeef0; color: #ef4444; border: 1px solid #fecaca; }
        .badge-prioridade-alta { background-color: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
        .badge-prioridade-media { background-color: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .badge-prioridade-baixa { background-color: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }

        [data-bs-theme="dark"] .badge-prioridade-urgente { background-color: rgba(239, 68, 68, 0.15); color: #f87171; border-color: rgba(239, 68, 68, 0.3); }
        [data-bs-theme="dark"] .badge-prioridade-alta { background-color: rgba(217, 119, 6, 0.15); color: #fbbf24; border-color: rgba(217, 119, 6, 0.3); }
        [data-bs-theme="dark"] .badge-prioridade-media { background-color: rgba(37, 99, 235, 0.15); color: #60a5fa; border-color: rgba(37, 99, 235, 0.3); }
        [data-bs-theme="dark"] .badge-prioridade-baixa { background-color: rgba(75, 85, 99, 0.15); color: #9ca3af; border-color: rgba(75, 85, 99, 0.3); }

        .progress-micro {
            height: 6px;
            border-radius: 3px;
        }
    </style>
@endpush

@section('content')
    <!-- Header Page -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-slate-800 fw-bold">Painel de Demandas</h1>
            <p class="text-muted mb-0">Gerencie, acompanhe e delege tarefas para usuários internos e contatos externos.</p>
        </div>
        <a href="{{ route('demandas.create') }}" class="btn btn-premium px-4 py-2 shadow-sm">
            <i class="fa-solid fa-plus me-2"></i>Nova Demanda
        </a>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <!-- Abertas -->
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card shadow-sm bg-body border-0 border-start border-4 border-primary h-100">
                <div class="card-body py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Abertas no Prazo</span>
                        <h3 class="mb-0 fw-bold mt-1 text-primary">{{ $stats['abertas'] }}</h3>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                        <i class="fa-solid fa-folder-open fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aguardando -->
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card shadow-sm bg-body border-0 border-start border-4 border-warning h-100">
                <div class="card-body py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Aguardando Devolutiva</span>
                        <h3 class="mb-0 fw-bold mt-1 text-warning">{{ $stats['aguardando'] }}</h3>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                        <i class="fa-solid fa-hourglass-half fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vencidas -->
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card shadow-sm bg-body border-0 border-start border-4 border-danger h-100">
                <div class="card-body py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Prazos Vencidos</span>
                        <h3 class="mb-0 fw-bold mt-1 text-danger">{{ $stats['vencidas'] }}</h3>
                    </div>
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 text-danger">
                        <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Executadas -->
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card shadow-sm bg-body border-0 border-start border-4 border-success h-100">
                <div class="card-body py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Concluídas</span>
                        <h3 class="mb-0 fw-bold mt-1 text-success">{{ $stats['executadas'] }}</h3>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                        <i class="fa-solid fa-circle-check fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card filter-card shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('demandas.index') }}" class="row g-3 align-items-end">
                <!-- Visão -->
                <div class="col-md-3 col-sm-6">
                    <label for="visao" class="form-label small fw-bold text-secondary">Visão das Demandas</label>
                    <select class="form-select" id="visao" name="visao">
                        <option value="todas" {{ request('visao') == 'todas' ? 'selected' : '' }}>Todas as delegações</option>
                        <option value="minhas" {{ request('visao') == 'minhas' ? 'selected' : '' }}>Sob minha responsabilidade</option>
                        <option value="criadas_por_mim" {{ request('visao') == 'criadas_por_mim' ? 'selected' : '' }}>Criadas por mim</option>
                    </select>
                </div>

                <!-- Status -->
                <div class="col-md-2 col-sm-6">
                    <label for="status" class="form-label small fw-bold text-secondary">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Todos os status</option>
                        <option value="aberta" {{ request('status') == 'aberta' ? 'selected' : '' }}>Abertas no Prazo</option>
                        <option value="aguardando" {{ request('status') == 'aguardando' ? 'selected' : '' }}>Aguardando</option>
                        <option value="vencidas" {{ request('status') == 'vencidas' ? 'selected' : '' }}>Vencidas</option>
                        <option value="executada" {{ request('status') == 'executada' ? 'selected' : '' }}>Executadas</option>
                        <option value="nao_executada" {{ request('status') == 'nao_executada' ? 'selected' : '' }}>Não Executadas</option>
                        <option value="cancelada" {{ request('status') == 'cancelada' ? 'selected' : '' }}>Canceladas</option>
                    </select>
                </div>

                <!-- Prioridade -->
                <div class="col-md-2 col-sm-6">
                    <label for="prioridade" class="form-label small fw-bold text-secondary">Prioridade</label>
                    <select class="form-select" id="prioridade" name="prioridade">
                        <option value="">Todas</option>
                        <option value="baixa" {{ request('prioridade') == 'baixa' ? 'selected' : '' }}>Baixa</option>
                        <option value="media" {{ request('prioridade') == 'media' ? 'selected' : '' }}>Média</option>
                        <option value="alta" {{ request('prioridade') == 'alta' ? 'selected' : '' }}>Alta</option>
                        <option value="urgente" {{ request('prioridade') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                    </select>
                </div>

                <!-- Busca textual -->
                <div class="col-md-3 col-sm-6">
                    <label for="busca" class="form-label small fw-bold text-secondary">Buscar</label>
                    <input type="text" class="form-control" id="busca" name="busca" value="{{ request('busca') }}" placeholder="Título, descrição ou nome...">
                </div>

                <!-- Ações -->
                <div class="col-md-2 col-sm-12 text-end d-flex gap-2">
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="fa-solid fa-filter me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('demandas.index') }}" class="btn btn-light" title="Limpar Filtros">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Content Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table premium-table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="border-0 px-4">Demanda</th>
                            <th class="border-0">Prioridade</th>
                            <th class="border-0">Responsável</th>
                            <th class="border-0">Prazo</th>
                            <th class="border-0">Progresso Checklist</th>
                            <th class="border-0">Status</th>
                            <th class="border-0 text-center px-4" style="width: 120px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($demandas as $demanda)
                            @php
                                $isOverdue = $demanda->isVencida();
                            @endphp
                            <tr class="{{ $isOverdue ? 'table-danger-subtle' : '' }}">
                                <!-- Título & Descrição Curta -->
                                <td class="px-4">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <a href="{{ route('demandas.show', $demanda) }}" class="fw-bold text-slate-800 text-decoration-none d-block">
                                                {{ $demanda->titulo }}
                                            </a>
                                            <small class="text-muted d-block" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                {{ Str::limit($demanda->descricao, 60) }}
                                            </small>
                                            <small class="text-xs text-secondary d-block">
                                                Criado por: {{ $demanda->criador->nickname ?: $demanda->criador->name }} em {{ $demanda->created_at->format('d/m/Y') }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <!-- Prioridade -->
                                <td>
                                    @php
                                        $prioLabels = [
                                            'baixa' => ['badge-prioridade-baixa', 'Baixa'],
                                            'media' => ['badge-prioridade-media', 'Média'],
                                            'alta' => ['badge-prioridade-alta', 'Alta'],
                                            'urgente' => ['badge-prioridade-urgente', 'Urgente']
                                        ];
                                        $prio = $prioLabels[$demanda->prioridade] ?? ['badge-secondary', 'Média'];
                                    @endphp
                                    <span class="badge {{ $prio[0] }} px-2 py-1" style="font-weight: 600;">
                                        {{ $prio[1] }}
                                    </span>
                                </td>

                                <!-- Responsável -->
                                <td>
                                    @if($demanda->tipo_responsavel === 'usuario')
                                        <span class="d-flex align-items-center text-slate-700">
                                            <i class="fa-solid fa-user-tie text-primary me-2"></i>
                                            {{ $demanda->responsavelUsuario ? ($demanda->responsavelUsuario->nickname ?: $demanda->responsavelUsuario->name) : 'N/D' }}
                                        </span>
                                    @else
                                        <span class="d-flex align-items-center text-slate-700" title="Contato Externo">
                                            <i class="fa-brands fa-whatsapp text-success me-2 fs-5"></i>
                                            {{ $demanda->responsavel_nome }}
                                            <small class="text-muted ms-1" style="font-size: 0.7rem;">(Externo)</small>
                                        </span>
                                    @endif
                                </td>

                                <!-- Prazo -->
                                <td>
                                    @if($demanda->prazo)
                                        <span class="d-block {{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                            <i class="fa-regular fa-calendar-days me-1"></i>
                                            {{ $demanda->prazo->format('d/m/Y') }}
                                            @if($isOverdue)
                                                <small class="d-block text-danger font-weight-bold" style="font-size: 0.7rem;">(VENCIDO)</small>
                                            @else
                                                <small class="d-block text-muted" style="font-size: 0.7rem;">{{ $demanda->prazo->format('H:i') }}</small>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <!-- Checklist Progresso -->
                                <td>
                                    @php
                                        $progresso = $demanda->progresso_checklist;
                                        $checklistCount = $demanda->checklists()->count();
                                    @endphp
                                    @if($checklistCount > 0)
                                        <div class="d-flex align-items-center">
                                            <div class="progress progress-micro w-100 me-2" style="max-width: 100px;">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progresso }}%" aria-valuenow="{{ $progresso }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <small class="fw-bold text-success">{{ $progresso }}%</small>
                                        </div>
                                        <small class="text-muted d-block text-xs" style="font-size: 0.7rem;">
                                            {{ $demanda->checklists()->where('concluido', true)->count() }} de {{ $checklistCount }} sub-tarefas
                                        </small>
                                    @else
                                        <span class="text-muted text-xs font-italic" style="font-size: 0.75rem;">Sem sub-tarefas</span>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td>
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
                                            'aguardando' => 'Aguardando',
                                            'executada' => 'Executada',
                                            'nao_executada' => 'Não Executada',
                                            'cancelada' => 'Cancelada'
                                        ];
                                        $stStyle = $statusStyles[$demanda->status] ?? 'bg-secondary';
                                        $stLabel = $statusLabels[$demanda->status] ?? ucfirst($demanda->status);
                                    @endphp
                                    <span class="badge {{ $stStyle }} rounded-pill px-3 py-1 shadow-sm" style="font-size: 0.75rem;">
                                        {{ $stLabel }}
                                    </span>
                                </td>

                                <!-- Ações -->
                                <td class="text-center px-4">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <a href="{{ route('demandas.show', $demanda) }}" class="btn btn-sm btn-outline-primary" title="Visualizar Detalhes">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @if(auth()->user()->temPermissao('demandas.gerenciar') || $demanda->criador_id === auth()->id())
                                            <a href="{{ route('demandas.edit', $demanda) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-5 text-center text-muted">
                                    <i class="fa-solid fa-list-check fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">Nenhuma demanda encontrada correspondente aos filtros.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Paginação -->
        @if($demandas->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $demandas->links() }}
            </div>
        @endif
    </div>
@endsection
