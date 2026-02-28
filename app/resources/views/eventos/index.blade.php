@extends('layouts.app')

@section('title', 'Controle de Eventos')

@section('content')
    <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon text-bg-primary shadow-sm">
                    <i class="fa-solid fa-calendar-check"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Eventos</span>
                    <span class="info-box-number">{{ $totalEventos }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon text-bg-success shadow-sm">
                    <i class="fa-solid fa-ticket"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Convites</span>
                    <span class="info-box-number">{{ $totalConvites }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon text-bg-info shadow-sm">
                    <i class="fa-solid fa-user-group"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Convidados</span>
                    <span class="info-box-number">{{ $totalConvidados }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
                <span class="info-box-icon text-bg-warning shadow-sm">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Arrecadado</span>
                    <span class="info-box-number">R$ {{ number_format($totalArrecadado, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title m-0">Lista de Eventos</h3>
            <div class="card-tools ms-auto">
                @if(auth()->user()->temPermissao('criar_eventos'))
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#novoEventoModal">
                        <i class="fa-solid fa-plus me-1"></i> Novo Evento
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Evento</th>
                            <th>Data</th>
                            <th>Local</th>
                            <th class="text-center">Convites</th>
                            <th class="text-center">Convidados</th>
                            <th>Arrecadado</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($eventosAbertos as $evento)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $evento->nome }}</div>
                                    <span class="badge text-bg-success py-1 px-2">Aberto</span>
                                </td>
                                <td>{{ optional($evento->data_inicio)->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>{{ $evento->local ?? '-' }}</td>
                                <td class="text-center">{{ $evento->convites()->count() }}</td>
                                <td class="text-center">{{ $evento->convidados()->count() }}</td>
                                <td>R$ {{ number_format($evento->vendas()->sum('valor_venda'), 2, ',', '.') }}</td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('eventos.show', $evento) }}" class="btn btn-sm btn-outline-primary"
                                            title="Ver Detalhes">
                                            <i class="fa-solid fa-eye me-1"></i> Ver
                                        </a>
                                        <a href="{{ route('eventos.report', $evento) }}" target="_blank"
                                            class="btn btn-outline-secondary btn-sm" title="Relatório">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">Nenhum evento encontrado</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer clearfix bg-white text-muted small py-2">
            © TI Químicos Unificados
        </div>
    </div>

    @if(auth()->user()->temPermissao('criar_eventos'))
        <!-- Modal -->
        <div class="modal fade" id="novoEventoModal" tabindex="-1" aria-labelledby="novoEventoModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content shadow-lg border-0 rounded-3">
                    <div class="modal-header border-bottom-0">
                        <h5 class="modal-title fw-bold" id="novoEventoModalLabel">Adicionar Evento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('eventos.store') }}">
                        @csrf
                        <div class="modal-body px-4">
                            <div class="mb-3">
                                <label for="nome" class="form-label small fw-bold">Nome do Evento</label>
                                <input type="text" class="form-control" id="nome" name="nome" value="{{ old('nome') }}"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="data" class="form-label small fw-bold">Data</label>
                                <input type="datetime-local" class="form-control" id="data" name="data"
                                    value="{{ old('data') }}">
                            </div>
                            <div class="mb-3">
                                <label for="local" class="form-label small fw-bold">Local</label>
                                <input type="text" class="form-control" id="local" name="local" value="{{ old('local') }}">
                            </div>
                            <div class="mb-3">
                                <label for="valor_inteira" class="form-label small fw-bold">Valor (inteira)</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" step="0.01" min="0" class="form-control" id="valor_inteira"
                                        name="valor_inteira" value="{{ old('valor_inteira') }}">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 px-4 pb-4">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary fw-bold">Salvar Evento</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection