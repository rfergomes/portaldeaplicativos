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
                @if(auth()->user()->temPermissao('criar_eventos') || auth()->user()->temPermissao('eventos.criar'))
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#novoEventoModal">
                        <i class="fa-solid fa-plus me-1"></i> Novo Evento
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            <div class="px-3 pt-3">
                <div class="btn-group" role="group" aria-label="Filtro de Eventos">
                    <input type="radio" class="btn-check" name="eventoStatus" id="btnAbertos" autocomplete="off" checked
                        onchange="toggleEventos('abertos')">
                    <label class="btn btn-outline-success" for="btnAbertos">Abertos</label>

                    <input type="radio" class="btn-check" name="eventoStatus" id="btnEncerrados" autocomplete="off"
                        onchange="toggleEventos('encerrados')">
                    <label class="btn btn-info text-white border-info" for="btnEncerrados">Encerrados</label>
                </div>
            </div>

            <div class="table-responsive mt-3" id="tabelaAbertos">
                <table class="table table-hover align-middle mb-0">
                    <thead>
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
                                <td class="text-center">{{ $evento->convites->count() }}</td>
                                <td class="text-center">{{ $evento->convidados->count() }}</td>
                                <td>R$ {{ number_format($evento->convidados->sum('valor'), 2, ',', '.') }}</td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('eventos.show', $evento) }}" class="btn btn-sm btn-outline-primary"
                                            title="Ver Detalhes">
                                            <i class="fa-solid fa-eye me-1"></i> Ver
                                        </a>

                                        @if(auth()->user()->temPermissao('criar_eventos') || auth()->user()->temPermissao('eventos.editar') || auth()->user()->temPermissao('eventos.criar'))
                                                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="editEvento({{ json_encode([
                                                'id' => $evento->id,
                                                'nome' => $evento->nome,
                                                'data' => $evento->data_inicio ? $evento->data_inicio->format('Y-m-d\TH:i') : '',
                                                'local' => $evento->local,
                                                'valor_inteira' => $evento->valor_inteira
                                            ]) }})" title="Editar Evento">
                                                                        <i class="fa-solid fa-edit"></i>
                                                                    </button>
                                        @endif

                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                data-bs-toggle="dropdown" title="Relatório PDF">
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('eventos.report', $evento) }}"
                                                        target="_blank">
                                                        <i class="fa-solid fa-file-invoice-dollar me-2 text-primary"></i>
                                                        Completo
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('eventos.report', [$evento, 'sem_valor' => 1]) }}"
                                                        target="_blank">
                                                        <i class="fa-solid fa-file-lines me-2 text-muted"></i> Sem Valores
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>

                                        @if(auth()->user()->temPermissao('criar_eventos') || auth()->user()->temPermissao('eventos.criar') || auth()->user()->temPermissao('eventos.editar'))
                                            <button type="button"
                                                class="btn btn-sm {{ $evento->encerrado ? 'btn-outline-success' : 'btn-outline-warning' }}"
                                                onclick="toggleEventStatus({{ $evento->id }}, '{{ $evento->nome }}', {{ $evento->encerrado ? 'true' : 'false' }})"
                                                title="{{ $evento->encerrado ? 'Reabrir Evento' : 'Encerrar Evento' }}">
                                                <i class="fa-solid {{ $evento->encerrado ? 'fa-unlock' : 'fa-lock' }}"></i>
                                            </button>
                                            <form id="toggle-status-{{ $evento->id }}"
                                                action="{{ route('eventos.toggleStatus', $evento) }}" method="POST"
                                                style="display: none;">
                                                @csrf
                                                @method('PATCH')
                                            </form>
                                        @endif
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

            <div class="table-responsive mt-3 d-none" id="tabelaEncerrados">
                <table class="table table-hover align-middle mb-0">
                    <thead>
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
                        @forelse($eventosEncerrados as $evento)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $evento->nome }}</div>
                                    <span class="badge text-bg-secondary py-1 px-2">Encerrado</span>
                                </td>
                                <td>{{ optional($evento->data_inicio)->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>{{ $evento->local ?? '-' }}</td>
                                <td class="text-center">{{ $evento->convites->count() }}</td>
                                <td class="text-center">{{ $evento->convidados->count() }}</td>
                                <td>R$ {{ number_format($evento->convidados->sum('valor'), 2, ',', '.') }}</td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('eventos.show', $evento) }}" class="btn btn-sm btn-outline-primary"
                                            title="Ver Detalhes">
                                            <i class="fa-solid fa-eye me-1"></i> Ver
                                        </a>

                                        @if(auth()->user()->temPermissao('criar_eventos') || auth()->user()->temPermissao('eventos.editar') || auth()->user()->temPermissao('eventos.criar'))
                                                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="editEvento({{ json_encode([
                                                'id' => $evento->id,
                                                'nome' => $evento->nome,
                                                'data' => $evento->data_inicio ? $evento->data_inicio->format('Y-m-d\TH:i') : '',
                                                'local' => $evento->local,
                                                'valor_inteira' => $evento->valor_inteira
                                            ]) }})" title="Editar Evento">
                                                                        <i class="fa-solid fa-edit"></i>
                                                                    </button>
                                        @endif

                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                data-bs-toggle="dropdown" title="Relatório PDF">
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('eventos.report', $evento) }}"
                                                        target="_blank">
                                                        <i class="fa-solid fa-file-invoice-dollar me-2 text-primary"></i>
                                                        Completo
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('eventos.report', [$evento, 'sem_valor' => 1]) }}"
                                                        target="_blank">
                                                        <i class="fa-solid fa-file-lines me-2 text-muted"></i> Sem Valores
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>

                                        @if(auth()->user()->temPermissao('criar_eventos') || auth()->user()->temPermissao('eventos.criar') || auth()->user()->temPermissao('eventos.editar'))
                                            <button type="button"
                                                class="btn btn-sm {{ $evento->encerrado ? 'btn-outline-success' : 'btn-outline-warning' }}"
                                                onclick="toggleEventStatus({{ $evento->id }}, '{{ $evento->nome }}', {{ $evento->encerrado ? 'true' : 'false' }})"
                                                title="{{ $evento->encerrado ? 'Reabrir Evento' : 'Encerrar Evento' }}">
                                                <i class="fa-solid {{ $evento->encerrado ? 'fa-unlock' : 'fa-lock' }}"></i>
                                            </button>
                                            <form id="toggle-status-{{ $evento->id }}"
                                                action="{{ route('eventos.toggleStatus', $evento) }}" method="POST"
                                                style="display: none;">
                                                @csrf
                                                @method('PATCH')
                                            </form>
                                        @endif
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
        <div class="card-footer clearfix text-muted small py-2">
            © TI Químicos Unificados
        </div>
    </div>

    @if(auth()->user()->temPermissao('criar_eventos') || auth()->user()->temPermissao('eventos.criar') || auth()->user()->temPermissao('eventos.editar'))
        <!-- Modal Novo Evento -->
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

        <!-- Modal Editar Evento -->
        <div class="modal fade" id="editEventoModal" tabindex="-1" aria-labelledby="editEventoModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content shadow-lg border-0 rounded-3">
                    <div class="modal-header border-bottom-0">
                        <h5 class="modal-title fw-bold" id="editEventoModalLabel">Editar Evento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editEventoForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body px-4">
                            <div class="mb-3">
                                <label for="edit_nome" class="form-label small fw-bold">Nome do Evento</label>
                                <input type="text" class="form-control" id="edit_nome" name="nome" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_data" class="form-label small fw-bold">Data</label>
                                <input type="datetime-local" class="form-control" id="edit_data" name="data_inicio">
                            </div>
                            <div class="mb-3">
                                <label for="edit_local" class="form-label small fw-bold">Local</label>
                                <input type="text" class="form-control" id="edit_local" name="local">
                            </div>
                            <div class="mb-3">
                                <label for="edit_valor_inteira" class="form-label small fw-bold">Valor (inteira)</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" step="0.01" min="0" class="form-control" id="edit_valor_inteira"
                                        name="valor_inteira">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 px-4 pb-4">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary fw-bold">Salvar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            function toggleEventos(tipo) {
                if (tipo === 'abertos') {
                    document.getElementById('tabelaAbertos').classList.remove('d-none');
                    document.getElementById('tabelaEncerrados').classList.add('d-none');
                } else {
                    document.getElementById('tabelaAbertos').classList.add('d-none');
                    document.getElementById('tabelaEncerrados').classList.remove('d-none');
                }
            }

            function editEvento(evento) {
                document.getElementById('editEventoForm').action = `/eventos/${evento.id}`;
                document.getElementById('edit_nome').value = evento.nome;
                document.getElementById('edit_data').value = evento.data;
                document.getElementById('edit_local').value = evento.local;
                document.getElementById('edit_valor_inteira').value = evento.valor_inteira;

                new bootstrap.Modal(document.getElementById('editEventoModal')).show();
            }

            function toggleEventStatus(id, nome, encerrado) {
                const acao = encerrado ? 'REABRIR' : 'ENCERRAR';
                const msg = encerrado ? 'O evento voltará a aparecer na lista de eventos abertos.' : 'O evento será movido para a lista de eventos encerrados.';

                Swal.fire({
                    title: `Deseja ${acao} o evento?`,
                    text: `${nome}: ${msg}`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: encerrado ? '#28a745' : '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: `Sim, ${acao.toLowerCase()}!`,
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(`toggle-status-${id}`).submit();
                    }
                });
            }
        </script>
    @endpush
@endsection