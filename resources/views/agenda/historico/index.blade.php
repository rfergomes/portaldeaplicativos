@extends('layouts.app')

@section('title', 'Histórico de Exclusões de Reservas')

@section('content')
    <div class="container-fluid py-2">

        {{-- SweetAlert flash --}}
        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', timer: 3500, title: @json(session('success')), showConfirmButton: false, timerProgressBar: true });
                });
            </script>
        @endif

        {{-- CABEÇALHO --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h4 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left text-danger me-2"></i>Histórico de
                    Exclusões</h4>
                <p class="text-muted small mb-0">Registro de todas as reservas excluídas com o motivo informado pelo
                    operador.</p>
            </div>
            <a href="{{ route('agenda.reservas.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                <i class="fa-solid fa-arrow-left me-1"></i> Voltar ao Painel
            </a>
        </div>

        {{-- FILTROS --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('agenda.historico.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold mb-1"><i
                                class="fa-solid fa-umbrella-beach me-1 text-primary"></i>Colônia</label>
                        <select name="colonia_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Todas --</option>
                            @foreach($colonias as $col)
                                <option value="{{ $col->id }}" {{ request('colonia_id') == $col->id ? 'selected' : '' }}>
                                    {{ $col->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold mb-1"><i
                                class="fa-solid fa-clock me-1 text-primary"></i>Período</label>
                        <select name="periodo_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Todos --</option>
                            @foreach($periodos as $per)
                                <option value="{{ $per->id }}" {{ request('periodo_id') == $per->id ? 'selected' : '' }}>
                                    {{ $per->descricao }}
                                    ({{ $per->data_inicial->format('d/m') }}–{{ $per->data_final->format('d/m') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold mb-1"><i
                                class="fa-solid fa-calendar-day me-1 text-primary"></i>De</label>
                        <input type="date" name="data_inicio" class="form-control form-control-sm"
                            value="{{ request('data_inicio') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold mb-1"><i
                                class="fa-solid fa-calendar-day me-1 text-primary"></i>Até</label>
                        <input type="date" name="data_fim" class="form-control form-control-sm"
                            value="{{ request('data_fim') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold mb-1"><i
                                class="fa-solid fa-magnifying-glass me-1 text-primary"></i>Busca</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="busca" class="form-control" placeholder="Nome ou motivo..."
                                value="{{ request('busca') }}">
                            <button class="btn btn-primary" type="submit"><i class="fa-solid fa-search"></i></button>
                        </div>
                    </div>
                    @if(request()->hasAny(['colonia_id', 'periodo_id', 'busca', 'data_inicio', 'data_fim']))
                        <div class="col-12">
                            <a href="{{ route('agenda.historico.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fa-solid fa-xmark me-1"></i>Limpar filtros
                            </a>
                            <span class="text-muted small ms-2">{{ $historicos->total() }} resultado(s) encontrado(s)</span>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        {{-- TABELA --}}
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                @if($historicos->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fa-solid fa-clock-rotate-left fa-3x mb-3 opacity-25"></i>
                        <p class="fw-bold mb-0">Nenhum registro de exclusão encontrado.</p>
                        <p class="small">As exclusões de reservas aparecerão aqui.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3" style="width:55px">#</th>
                                    <th>Hóspede / Bloqueio</th>
                                    <th>Colônia</th>
                                    <th>Período</th>
                                    <th>Acomodação</th>
                                    <th>Status</th>
                                    <th>Motivo da Exclusão</th>
                                    <th class="text-center">Excluído por</th>
                                    <th class="text-center">Data/Hora</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($historicos as $h)
                                    @php
                                        $badgeStatus = match ($h->status_reserva) {
                                            'confirmado' => 'bg-success',
                                            'reservado' => 'bg-primary',
                                            'fila_espera' => 'bg-warning',
                                            'bloqueado' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                        $statusLabel = match ($h->status_reserva) {
                                            'confirmado' => 'Confirmado',
                                            'reservado' => 'Agendado',
                                            'fila_espera' => 'Fila de Espera',
                                            'bloqueado' => 'Bloqueado',
                                            default => $h->status_reserva ?? '—',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="ps-3 text-muted fw-bold">{{ $h->id }}</td>
                                        <td>
                                            @if($h->hospede_nome)
                                                <strong class="d-block">{{ $h->hospede_nome }}</strong>
                                                @if($h->hospede_telefone)
                                                    <span class="text-muted small"><i
                                                            class="fa-solid fa-phone me-1"></i>{{ $h->hospede_telefone }}</span>
                                                @endif
                                            @elseif($h->bloqueio_nota)
                                                <span class="text-danger fst-italic"><i
                                                        class="fa-solid fa-lock me-1"></i>{{ $h->bloqueio_nota }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $h->colonia_nome }}</span>
                                        </td>
                                        <td>
                                            <span class="d-block fw-bold small">{{ $h->periodo_descricao }}</span>
                                            @if($h->periodo_data_inicial && $h->periodo_data_final)
                                                <span class="text-muted" style="font-size:0.75rem;">
                                                    {{ $h->periodo_data_inicial->format('d/m/Y') }} —
                                                    {{ $h->periodo_data_final->format('d/m/Y') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($h->acomodacao_identificador)
                                                <span class="badge bg-info">{{ $h->acomodacao_identificador }}</span>
                                                @if($h->acomodacao_tipo)
                                                    <span class="text-muted small d-block">{{ $h->acomodacao_tipo }}</span>
                                                @endif
                                            @else
                                                <span class="text-muted small">Fila</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $badgeStatus }} rounded-pill">{{ $statusLabel }}</span>
                                        </td>
                                        <td style="max-width: 280px;">
                                            <span style="font-size:0.88rem;">{{ $h->motivo }}</span>
                                        </td>
                                        <td class="text-center small">
                                            <span class="d-block fw-bold">{{ $h->excluido_por_nome ?? '—' }}</span>
                                        </td>
                                        <td class="text-center small text-muted">
                                            {{ $h->created_at->format('d/m/Y H:i') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($historicos->hasPages())
                        <div class="card-footer border-0 bg-transparent py-3">
                            {{ $historicos->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

    </div>
@endsection