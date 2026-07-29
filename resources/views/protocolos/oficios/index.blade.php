@extends('layouts.app')

@section('title', 'Gestão de Ofícios Coletivos')

@section('content')
<div class="container-fluid">
    <!-- Cabeçalho -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 text-gray-800 mb-0">Envio Coletivo de Ofícios</h1>
            <p class="text-muted small mb-0">Gestão e disparo em massa de comunicações oficiais com AR-Online.</p>
        </div>
        <a href="{{ route('protocolos.oficios.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i> Novo Ofício Coletivo
        </a>
    </div>

    <!-- Métricas -->
    <div class="row mb-4">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="flex-shrink-0 bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="fa-solid fa-mail-bulk fa-2x text-primary"></i>
                    </div>
                    <div>
                        <p class="text-muted small fw-bold mb-0">Total de Ofícios</p>
                        <h4 class="fw-bold mb-0">{{ number_format($totalGeral, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="flex-shrink-0 bg-success bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="fa-solid fa-check-double fa-2x text-success"></i>
                    </div>
                    <div>
                        <p class="text-muted small fw-bold mb-0">Entregues / Lidos</p>
                        <h4 class="fw-bold mb-0">{{ number_format($totalSucesso, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="flex-shrink-0 bg-info bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="fa-solid fa-paper-plane fa-2x text-info"></i>
                    </div>
                    <div>
                        <p class="text-muted small fw-bold mb-0">Enviados</p>
                        <h4 class="fw-bold mb-0">{{ number_format($totalEnviados, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="flex-shrink-0 bg-danger bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="fa-solid fa-triangle-exclamation fa-2x text-danger"></i>
                    </div>
                    <div>
                        <p class="text-muted small fw-bold mb-0">Com Falhas</p>
                        <h4 class="fw-bold mb-0">{{ number_format($totalFalhas, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('protocolos.oficios.index') }}" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" name="termo" class="form-control form-control-sm" placeholder="Buscar por referência, assunto ou destinatário..." value="{{ $termo }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="data" class="form-control form-control-sm" title="Data Específica" value="{{ request('data', $data ?? '') }}">
                </div>
                <div class="col-md-2">
                    <select name="tipo_protocolo_id" class="form-select form-select-sm">
                        <option value="">Todos os Tipos</option>
                        @foreach($tiposProtocolo as $tipo)
                            <option value="{{ $tipo->id }}" {{ request('tipo_protocolo_id', $tipoProtocoloId ?? '') == $tipo->id ? 'selected' : '' }}>
                                {{ $tipo->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <select name="mes" class="form-select form-select-sm">
                        <option value="">Mês</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $mes == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->locale('pt_BR')->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-1">
                    <select name="ano" class="form-select form-select-sm">
                        @for($a = date('Y'); $a >= 2024; $a--)
                            <option value="{{ $a }}" {{ $ano == $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-1">
                    <select name="status_envio" class="form-select form-select-sm">
                        <option value="">Status</option>
                        <option value="sucesso" {{ $status == 'sucesso' ? 'selected' : '' }}>Sucesso</option>
                        <option value="enviado" {{ $status == 'enviado' ? 'selected' : '' }}>Enviado</option>
                        <option value="pendente" {{ $status == 'pendente' ? 'selected' : '' }}>Pendente</option>
                        <option value="falha" {{ $status == 'falha' ? 'selected' : '' }}>Falha</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1" title="Filtrar"><i class="fa-solid fa-magnifying-glass me-1"></i> Filtrar</button>
                    <a href="{{ route('protocolos.oficios.index') }}" class="btn btn-outline-secondary btn-sm" title="Limpar"><i class="fa-solid fa-xmark"></i></a>
                    <a href="{{ route('protocolos.pdf.falhas', array_merge(request()->all(), ['tipo_escopo' => 'coletivo'])) }}" target="_blank" class="btn btn-danger btn-sm" title="Exportar Relatório PDF Analítico">
                        <i class="fa-solid fa-file-pdf"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Ofícios Coletivos -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ref. Documento</th>
                            <th>Assunto</th>
                            <th>Destinatários / Empresas</th>
                            <th>Data Disparo</th>
                            <th>Status Geral</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($oficios as $oficio)
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark border fw-bold">{{ $oficio->referencia_documento ?? 'OFÍCIO #' . $oficio->id }}</span>
                                </td>
                                <td>
                                    <strong>{{ $oficio->assunto }}</strong>
                                    <br><small class="text-muted">Criado por: {{ $oficio->usuario->name ?? 'Sistema' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">
                                        <i class="fa-solid fa-users me-1"></i> {{ $oficio->destinatarios->count() }} destinatário(s)
                                    </span>
                                </td>
                                <td>{{ $oficio->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @switch($oficio->status)
                                        @case('sucesso')
                                            <span class="badge bg-success">Sucesso</span>
                                            @break
                                        @case('enviado')
                                            <span class="badge bg-info">Enviado</span>
                                            @break
                                        @case('falha')
                                            <span class="badge bg-danger">Falha</span>
                                            @break
                                        @default
                                            <span class="badge bg-warning text-dark">{{ ucfirst($oficio->status) }}</span>
                                    @endswitch
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('protocolos.oficios.show', $oficio->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Ver Detalhes e Timeline">
                                        <i class="fa-solid fa-eye"></i> Detalhes
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fa-solid fa-inbox fa-2x mb-2"></i><br>Nenhum Ofício Coletivo encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($oficios->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $oficios->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
