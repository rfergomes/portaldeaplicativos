@extends('layouts.app')

@section('title', 'Gestão de Protocolos')

@section('content')
    <div class="container-fluid">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header border-0 py-3 d-flex align-items-center flex-wrap">
                <h3 class="card-title fw-bold m-0">Protocolos Enviados</h3>
                <div class="card-tools ms-auto">
                    <a href="{{ route('protocolos.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                        <i class="fa-solid fa-plus me-1"></i> Novo Protocolo
                    </a>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card-body border-bottom bg-light pb-2 pt-3">
                <form action="{{ route('protocolos.index') }}" method="GET" class="row gx-2 gy-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Pesquisar</label>
                        <div class="input-group input-group-sm mb-0">
                            <span class="input-group-text bg-white border-end-0"><i
                                    class="fa-solid fa-search text-muted"></i></span>
                            <input type="text" name="termo" class="form-control border-start-0 ps-0"
                                placeholder="Assunto, empresa, contato..." value="{{ request('termo', $termo ?? '') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Mês</label>
                        <select name="mes" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ request('mes', $mes) == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->locale('pt_BR')->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted mb-1">Ano</label>
                        <select name="ano" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            @php $anoAtual = date('Y'); @endphp
                            @foreach(range($anoAtual - 2, $anoAtual + 1) as $a)
                                <option value="{{ $a }}" {{ request('ano', $ano) == $a ? 'selected' : '' }}>
                                    {{ $a }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted mb-1">Status do Envio</label>
                        <select name="status_envio" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="concluido" {{ request('status_envio') == 'concluido' ? 'selected' : '' }}>Recepção
                                Concluída</option>
                            <option value="enviado" {{ request('status_envio') == 'enviado' ? 'selected' : '' }}>Enviado
                            </option>
                            <option value="falha" {{ request('status_envio') == 'falha' ? 'selected' : '' }}>Falha</option>
                            <option value="pendente" {{ request('status_envio') == 'pendente' ? 'selected' : '' }}>Pendente
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1 shadow-sm">
                            <i class="fa-solid fa-filter me-1"></i> Filtrar
                        </button>
                        <a href="{{ route('protocolos.index') }}" class="btn btn-light border btn-sm shadow-sm"
                            title="Limpar Filtros">
                            <i class="fa-solid fa-eraser text-muted"></i>
                        </a>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4" style="width:80px;">ID</th>
                                <th>Tipo</th>
                                <th>Assunto / Referência</th>
                                <th>Empresa</th>
                                <th>Destinatários</th>
                                <th>Status</th>
                                <th>Enviado Por</th>
                                <th>Data</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($protocolos as $protocolo)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge text-bg-light border shadow-sm px-2">#{{ $protocolo->id }}</span>
                                    </td>
                                    <td>
                                        @if($protocolo->tipo)
                                            <span class="badge text-bg-{{ $protocolo->tipo->cor }} rounded-pill shadow-sm px-2">
                                                <i class="{{ $protocolo->tipo->icone }} me-1"></i>{{ $protocolo->tipo->nome }}
                                            </span>
                                        @else
                                            <span class="badge text-bg-secondary rounded-pill shadow-sm px-2">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $protocolo->assunto }}</div>
                                        @if($protocolo->referencia_documento)
                                            <small class="text-muted">{{ $protocolo->referencia_documento }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $protocolo->empresa?->razao_social ?? '—' }}</td>
                                    <td>
                                        <span class="badge text-bg-info text-white rounded-pill shadow-sm px-2">
                                            <i class="fa-solid fa-users me-1"></i>{{ $protocolo->destinatarios->count() }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $cores = [
                                                'enviado' => 'primary',
                                                'pendente' => 'warning',
                                                'falha' => 'danger',
                                                'concluido' => 'success',
                                            ];
                                            $cor = $cores[$protocolo->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge text-bg-{{ $cor }} rounded-pill shadow-sm px-2">
                                            {{ ucfirst($protocolo->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $protocolo->usuario?->name ?? '—' }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $protocolo->created_at?->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('protocolos.show', $protocolo) }}"
                                            class="btn btn-light btn-sm border-0 rounded-circle shadow-sm"
                                            title="Ver Detalhes e Timeline">
                                            <i class="fa-solid fa-eye text-primary"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <div class="mb-2"><i class="fa-solid fa-inbox fa-3x opacity-25"></i></div>
                                        Nenhum protocolo encontrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($protocolos->hasPages())
                <div class="card-footer py-2 bg-white">
                    {{ $protocolos->links() }}
                </div>
            @endif
            <div class="card-footer bg-white text-muted small py-3 border-top">
                <i class="fa-solid fa-circle-info me-1"></i> Rastreamento de envios com valor jurídico via AR-Online.
            </div>
        </div>
    </div>
@endsection