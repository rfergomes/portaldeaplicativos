@extends('layouts.app')

@push('styles')
    <style>
        /* Estilos específicos para a Planilha de Reservas */
        .grid-reservas {
            display: grid;
            grid-template-columns: minmax(300px, 1fr) minmax(300px, 1fr);
            gap: 1.5rem;
        }

        .coluna-acomodacoes .card-acomodacao {
            border-left: 5px solid #0d6efd;
            /* Azul Padrão */
            transition: all 0.2s ease-in-out;
        }

        .coluna-acomodacoes .card-acomodacao:hover {
            transform: translateY(-2px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
        }

        .coluna-espera .card-espera {
            border-left: 5px solid #ffc107;
            /* Amarelo Espera */
            cursor: grab;
            transition: all 0.2s;
        }

        .coluna-espera .card-espera.dragging {
            opacity: 0.4;
            transform: scale(0.97);
        }

        .slot-vazio {
            transition: background-color 0.2s, border 0.2s;
        }

        .slot-vazio.drag-over {
            background-color: #d4edda !important;
            border: 2px dashed #198754 !important;
            border-radius: 6px;
        }

        .status-reservado {
            background-color: #e8f4f8;
        }

        /* Azul clarinho */
        .status-pago {
            border-left-color: #198754 !important;
            background-color: #e8f8f5;
        }

        .status-confirmado {
            border-left-color: #001f3f !important;
            background-color: #e7f1ff;
        }

        .status-reservado {
            border-left-color: #0dcaf0 !important;
            background-color: #e3f2fd;
        }

        .status-livre {
            border-left-color: #dee2e6 !important;
            background-color: #fbfbfb;
        }

        .status-bloqueado {
            border-left-color: #dc3545 !important;
            background-color: #f8e8e8;
        }

        /* Vermelho clarinho */
        .status-osasco {
            border-left-color: #6f42c1 !important;
            background-color: #f4e8f8;
        }

        /* Roxo clarinho */

        @media (max-width: 991.98px) {
            .grid-reservas {
                grid-template-columns: 1fr;
                /* Stack em telas menores */
            }
        }
    </style>
@endpush

@section('title', 'Painel de Reservas (Sorteio)')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body rounded">
                    <form action="{{ route('agenda.reservas.index') }}" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-primary"><i class="fa-solid fa-umbrella-beach me-1"></i>
                                1. Selecione a Colônia</label>
                            <select name="colonia_id" class="form-select" required onchange="this.form.submit()">
                                <option value="">-- Escolha o Local --</option>
                                @foreach($colonias as $col)
                                    <option value="{{ $col->id }}" {{ $coloniaSelecionada == $col->id ? 'selected' : '' }}>
                                        {{ $col->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-primary"><i class="fa-solid fa-clock me-1"></i> 2.
                                Selecione o Período</label>
                            <select name="periodo_id" class="form-select" required onchange="this.form.submit()">
                                <option value="">-- Escolha a Semana --</option>
                                @foreach($periodos as $per)
                                    <option value="{{ $per->id }}" {{ $periodoSelecionado == $per->id ? 'selected' : '' }}>
                                        {{ $per->descricao }} ({{ $per->data_inicial->format('d/m') }} a
                                        {{ $per->data_final->format('d/m') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                                <i class="fa-solid fa-magnifying-glass me-2"></i> Carregar Planilha
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($periodoSelecionado && $coloniaSelecionada)
        @php
            $periodoModel = $periodos->firstWhere('id', $periodoSelecionado);
            $coloniaModel = $colonias->firstWhere('id', $coloniaSelecionada);
        @endphp

        <div class="row mb-4">
            <div class="col-md-7">
                <div
                    class="d-flex flex-column justify-content-center h-100 p-3 rounded shadow-sm border border-primary border-opacity-25">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 class="fw-bold mb-0 text-primary">{{ $coloniaModel->nome }}</h4>
                        <div>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle me-1" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-print me-1"></i> PDF / Relatórios
                                </button>
                                <ul class="dropdown-menu shadow-sm">
                                    <li>
                                        <a class="dropdown-item py-2"
                                            href="{{ route('agenda.reservas.pdf.acomodacoes', ['colonia_id' => $coloniaSelecionada, 'periodo_id' => $periodoSelecionado]) }}"
                                            target="_blank">
                                            <i class="fa-solid fa-file-pdf text-danger me-2"></i> Lista de Reservas
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-2"
                                            href="{{ route('agenda.reservas.pdf.espera', ['colonia_id' => $coloniaSelecionada, 'periodo_id' => $periodoSelecionado]) }}"
                                            target="_blank">
                                            <i class="fa-solid fa-file-pdf text-warning me-2"></i> Lista de Espera
                                        </a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-2" href="javascript:void(0)" onclick="window.print()">
                                            <i class="fa-solid fa-desktop text-primary me-2"></i> Imprimir Tela (Navegador)
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <button class="btn btn-sm btn-success shadow-sm" data-bs-toggle="modal"
                                data-bs-target="#modalNovaReserva">
                                <i class="fa-solid fa-plus"></i> Adicionar
                            </button>
                        </div>
                    </div>
                    <p class="text-muted mb-0 fs-5 pb-2 border-bottom">
                        <i class="fa-regular fa-calendar text-primary me-1"></i> <strong>{{ $periodoModel->descricao }}</strong>
                    </p>
                    <p class="mb-0 mt-2 text-danger">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> <strong>Limite para acerto:</strong>
                        {{ $periodoModel->data_limite ? $periodoModel->data_limite->format('d/m/Y') : 'Não definido' }}
                    </p>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card shadow-sm border-0 h-100 border-start border-4 border-info">
                    <div class="card-header py-2">
                        <h6 class="mb-0 fw-bold"><i class="fa-solid fa-chart-pie me-2"></i>Resumo da Semana</h6>
                    </div>
                    <div class="card-body p-2" style="font-size: 0.9rem;">
                        <div class="row text-center mb-2">
                            <div class="col"><span
                                    class="text-info fw-bold d-block fs-5">{{ collect($estatisticas ?? [])->get('reservado', 0) }}</span>
                                Reservado</div>
                            <div class="col border-start"><span class="fw-bold d-block fs-5"
                                    style="color: #001f3f;">{{ collect($estatisticas ?? [])->get('confirmado', 0) }}</span>
                                Confirmado</div>
                            <div class="col border-start"><span
                                    class="text-success fw-bold d-block fs-5">{{ collect($estatisticas ?? [])->get('pago', 0) }}</span>
                                Pago</div>
                            <div class="col border-start"><span
                                    class="text-muted fw-bold d-block fs-5">{{ collect($estatisticas ?? [])->get('livre', 0) }}</span>
                                Livre</div>
                        </div>
                        <div class="row text-center border-top pt-2">
                            <div class="col"><span
                                    class="text-danger fw-bold d-block fs-6">{{ collect($estatisticas ?? [])->get('bloqueado', 0) }}</span>
                                Bloqueios</div>
                            <div class="col border-start"><span
                                    class="text-warning fw-bold d-block fs-6">{{ collect($estatisticas ?? [])->get('espera', 0) }}</span>
                                Fila Espera</div>
                            <div class="col border-start"><span
                                    class="fw-bold d-block fs-6">{{ collect($estatisticas ?? [])->get('total', 0) }}</span>
                                Total</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRID DIVISÃÆ’O: ACOMODAÃâ€¡Ãâ€¢ES vs FILA DE ESPERA -->
        <div class="grid-reservas">

            <!-- LADO ESQUERDO: ACOMODAÃâ€¡Ãâ€¢ES -->
            <div class="coluna-acomodacoes">
                <h5 class="fw-bold text-primary border-bottom border-primary pb-2 mb-3">
                    <i class="fa-solid fa-house-chimney me-2"></i> Lista de Reservas
                </h5>

                @if($acomodacoes->isEmpty() && request('colonia_id'))
                    <div class="alert alert-warning text-center border-0 shadow-sm mt-4 p-4">
                        <i class="fa-solid fa-person-digging fa-3x mb-3 text-warning opacity-75"></i>
                        <h5 class="fw-bold">Nenhuma Acomodação Cadastrada!</h5>
                        <p class="text-muted mb-4">Para utilizar a planilha desta colônia, você precisa registrar as unidades
                            (quartos/chalés) separadamente primeiro.</p>
                        <a href="{{ route('agenda.colonias.acomodacoes.index', request('colonia_id')) }}"
                            class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <i class="fa-solid fa-fw fa-bed me-2"></i> Ir para Cadastro de Acomodações
                        </a>
                    </div>
                @endif

                @foreach($acomodacoes as $aco)
                    @php
                        $reserva = $reservas->get($aco->id);
                        $statusClass = '';
                        $statusText = '';

                        if ($reserva) {
                            if ($reserva->status == 'pago') {
                                $statusClass = 'status-pago';
                                $statusText = '<span class="badge bg-success">Pago</span>';
                            } elseif ($reserva->status == 'confirmado') {
                                $statusClass = 'status-confirmado';
                                $statusText = '<span class="badge" style="background-color: #001f3f; color: #fff;">Confirmado</span>';
                            } elseif ($reserva->status == 'bloqueado') {
                                $statusClass = stripos($reserva->bloqueio_nota, 'osasco') !== false ? 'status-osasco' : 'status-bloqueado';
                                $statusText = stripos($reserva->bloqueio_nota, 'osasco') !== false
                                    ? '<span class="badge" style="background-color: #6f42c1;">Cota Osasco</span>'
                                    : '<span class="badge bg-danger"><i class="fa-solid fa-lock me-1"></i>Bloqueado</span>';
                            } else {
                                $statusClass = 'status-reservado';
                                $statusText = '<span class="badge bg-info text-dark">Reservado</span>';
                            }
                        } else {
                            $statusClass = 'status-livre';
                            $statusText = '<span class="badge bg-light text-muted border">Livre</span>';
                        }
                    @endphp

                    <div class="card card-acomodacao shadow-sm mb-1 {{ $statusClass }}">
                        <div class="card-body p-2 d-flex justify-content-between align-items-center">
                            <div class="w-25 border-end pe-2" style="max-width: 80px;">
                                <span class="text-muted d-block"
                                    style="font-size: 0.7rem; line-height: 1;">{{ $aco->tipo ?? 'Unidade' }}</span>
                                <strong class="fs-6">{{ $aco->identificador }}</strong>
                            </div>
                            <div class="w-75 ps-2">
                                @if($reserva)
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            @if($reserva->hospede)
                                                <h6 class="mb-0 fw-bold fs-6">{{ $reserva->hospede->nome }}</h6>
                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                    <span class="me-2"><i
                                                            class="fa-solid fa-phone text-secondary me-1"></i>{{ $reserva->hospede->telefone ?? '--' }}</span>
                                                    @if($reserva->hospede->empresa)
                                                        <span class="me-2"><i
                                                                class="fa-solid fa-building text-secondary me-1"></i>{{ \Illuminate\Support\Str::limit($reserva->hospede->empresa->razao_social, 15) }}</span>
                                                    @endif
                                                    @if($reserva->hospede->email)
                                                        <span><i
                                                                class="fa-regular fa-envelope text-secondary me-1"></i>{{ $reserva->hospede->email }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <h6 class="mb-0 fw-bold text-danger fst-italic fs-6">{{ $reserva->bloqueio_nota }}</h6>
                                            @endif
                                        </div>
                                        <div class="text-end d-flex flex-column align-items-end">
                                            {!! $statusText !!}
                                            <div class="mt-1">
                                                <button class="btn btn-sm btn-light border py-0 px-1 me-1" title="Editar Reserva"
                                                    data-bs-toggle="modal" data-bs-target="#modalEditarReserva"
                                                    onclick="preencherEdicao({{ $reserva->id }}, '{{ $reserva->status }}', '{{ $reserva->bloqueio_nota }}', '{{ addslashes($reserva->hospede->nome ?? '') }}', '{{ $reserva->hospede->telefone ?? '' }}', '{{ $reserva->hospede->email ?? '' }}', '{{ $reserva->hospede->empresa_id ?? '' }}')">
                                                    <i class="fa-solid fa-pen fa-xs text-primary"></i>
                                                </button>
                                                <form action="{{ route('agenda.reservas.destroy', $reserva->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-light border py-0 px-1"
                                                        title="Liberar Vaga" onclick="confirmarDelete(this.closest('form'))">
                                                        <i class="fa-solid fa-trash-can fa-xs text-danger"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="slot-vazio d-flex justify-content-between align-items-center h-100 px-2 py-1 rounded"
                                        data-aco-id="{{ $aco->id }}" data-aco-nome="{{ $aco->identificador }}"
                                        ondragover="event.preventDefault(); this.classList.add('drag-over')"
                                        ondragleave="this.classList.remove('drag-over')" ondrop="onDropEspera(event, {{ $aco->id }})"
                                        style="cursor: pointer; transition: background-color 0.2s; min-height: 42px;"
                                        onmouseover="this.style.backgroundColor='#f0f8ff'"
                                        onmouseout="this.style.backgroundColor='transparent'"
                                        onclick="preencherAcomodacao({{ $aco->id }}, '{{ $aco->identificador }}')"
                                        data-bs-toggle="modal" data-bs-target="#modalNovaReserva">
                                        <span class="text-muted fw-bold" style="font-size: 0.85rem;"><i
                                                class="fa-solid fa-ban me-1 opacity-50"></i> Livre</span>
                                        <span class="text-primary fw-bold" style="font-size: 0.85rem;">
                                            <i class="fa-solid fa-plus-circle me-1"></i> Preencher
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- LADO DIREITO: FILA DE ESPERA -->
            <div class="coluna-espera">
                <h5 class="fw-bold text-warning border-bottom border-warning pb-2 mb-3">
                    <i class="fa-solid fa-user-clock me-2"></i> Fila de Espera (Suplentes)
                </h5>

                <div class="p-3 rounded border" id="zonaFilaEspera">
                    @forelse($filaEspera as $index => $espera)
                        <div class="card card-espera shadow-sm mb-2" draggable="true" data-espera-id="{{ $espera->id }}"
                            data-nome="{{ $espera->hospede->nome ?? 'Suplente' }}"
                            ondragstart="onDragStart(event, {{ $espera->id }}, '{{ addslashes($espera->hospede->nome ?? 'Suplente') }}')">
                            <div class="card-body p-2 px-3 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <span class="badge rounded-circle bg-warning text-dark me-3"
                                        style="width: 25px; height: 25px; display: flex; align-items: center; justify-content: center;">{{ $index + 1 }}</span>
                                    <div>
                                        <strong class="d-block">{{ $espera->hospede->nome ?? 'Desconhecido' }}</strong>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            <span class="me-2"><i
                                                    class="fa-solid fa-phone text-secondary me-1"></i>{{ $espera->hospede->telefone ?? '--' }}</span>
                                            @if(isset($espera->hospede->empresa))
                                                <span class="me-2"><i
                                                        class="fa-solid fa-building text-secondary me-1"></i>{{ \Illuminate\Support\Str::limit($espera->hospede->empresa->razao_social, 15) }}</span>
                                            @endif
                                            @if(isset($espera->hospede->email))
                                                <span><i
                                                        class="fa-regular fa-envelope text-secondary me-1"></i>{{ $espera->hospede->email }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-outline-primary border-0" title="Editar Dados"
                                        data-bs-toggle="modal" data-bs-target="#modalEditarReserva"
                                        onclick="preencherEdicao({{ $espera->id }}, '{{ $espera->status }}', '{{ $espera->bloqueio_nota }}', '{{ addslashes($espera->hospede->nome ?? '') }}', '{{ $espera->hospede->telefone ?? '' }}', '{{ $espera->hospede->email ?? '' }}', '{{ $espera->hospede->empresa_id ?? '' }}')">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success border-0"
                                        title="Promover para Vaga (Puxar da fila)"
                                        onclick="abrirPromover({{ $espera->id }}, '{{ addslashes($espera->hospede->nome ?? 'Suplente') }}')">
                                        <i class="fa-solid fa-arrow-left-long"></i> Vaga
                                    </button>
                                    <form action="{{ route('agenda.reservas.destroy', $espera->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-outline-danger border-0" title="Remover da Fila"
                                            onclick="confirmarDelete(this.closest('form'))">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-mug-hot fa-2x mb-2 opacity-25"></i>
                            <p class="mb-0">Nenhum hóspede na fila de espera.</p>
                        </div>
                    @endforelse

                    <div class="mt-3 text-center">
                        <button class="btn btn-warning w-100 fw-bold shadow-sm" data-bs-toggle="modal"
                            data-bs-target="#modalNovaReserva" onclick="preencherFilaEspera()">
                            <i class="fa-solid fa-user-plus me-1"></i> Adicionar à Fila de Espera
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5 mt-4 text-muted rounded shadow-sm border">
            <i class="fa-solid fa-clipboard-list fa-4x mb-3 text-primary opacity-25"></i>
            <h4 class="fw-bold">Planilha de Controle de Vagas</h4>
            <p class="mb-0">Selecione o período e a colônia acima para começar a gerenciar as acomodações.</p>
        </div>
    @endif

    <!-- Modal Adicionar Reserva/Fila -->
    {{-- Modal Promover Vaga (Fila de Espera ââ€ â€™ Acomodação) --}}
    @if($periodoSelecionado && $coloniaSelecionada)
        <div class="modal fade" id="modalPromoverVaga" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-arrow-left-long me-2"></i>Promover para Vaga</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="formPromoverVaga" method="POST" action="">
                        @csrf
                        <div class="modal-body">
                            <p class="mb-3">Selecione qual acomodação <strong id="nomePromovido"></strong> irá ocupar:</p>
                            <select name="colonia_acomodacao_id" id="selectVagaPromover" class="form-select" required>
                                <option value="">-- Escolha a Acomodação --</option>
                                @foreach($acomodacoes as $aco)
                                    @if(!isset($reservas[$aco->id]))
                                        <option value="{{ $aco->id }}">Unidade {{ $aco->identificador }} ({{ $aco->tipo ?? 'Padrão' }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success fw-bold"><i class="fa-solid fa-check me-1"></i>
                                Confirmar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalNovaReserva" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-calendar-plus me-2"></i>Alocar Vaga
                            ou Fila</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('agenda.reservas.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="agenda_periodo_id" value="{{ $periodoSelecionado }}">
                        <input type="hidden" name="colonia_id" value="{{ $coloniaSelecionada }}">

                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Tipo de Alocação</label>
                                    <select class="form-select border-primary bg-light" id="tipoAlocacao"
                                        onchange="toggleAlocacaoForms()">
                                        <option value="hospede">Hóspede</option>
                                        <option value="bloqueio">Bloqueio / Cota Fixa</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3" id="blocoStatus">
                                    <label class="form-label fw-bold">Situação Inicial *</label>
                                    <select name="status" id="statusReserva" class="form-select border-primary bg-light">
                                        <option value="reservado">Reservado</option>
                                        <option value="confirmado">Confirmado</option>
                                        <option value="pago">Pago</option>
                                        <option value="bloqueado" hidden>Bloqueado</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Bloco Hóspede -->
                            <div id="blocoHospede" class="border p-3 rounded bg-white shadow-sm mb-3">
                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i
                                        class="fa-solid fa-user me-2"></i>Dados do Ganhador</h6>
                                <div class="row gx-2">
                                    <div class="col-md-12 mb-2">
                                        <label class="form-label fw-bold small mb-1">Nome Completo *</label>
                                        <input type="text" name="nome_hospede" class="form-control form-control-sm"
                                            placeholder="Ex: João da Silva" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-bold small mb-1">Telefone / WhatsApp</label>
                                        <input type="text" name="telefone_hospede" class="form-control form-control-sm"
                                            placeholder="(11) 99999-9999">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-bold small mb-1">E-mail</label>
                                        <input type="email" name="email_hospede" class="form-control form-control-sm"
                                            placeholder="email@exemplo.com">
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <label class="form-label fw-bold small mb-1">Empresa</label>
                                        <select name="empresa_id" id="selectEmpresa" class="form-select form-select-sm"
                                            placeholder="Digite para buscar empresa...">
                                            <option value="">-- Sem Empresa Vinculada --</option>
                                            @foreach($empresas as $emp)
                                                <option value="{{ $emp->id }}">{{ $emp->razao_social }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Bloco Bloqueio -->
                            <div id="blocoBloqueio" class="d-none">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-danger">Nota de Bloqueio *</label>
                                    <input type="text" class="form-control border-danger" name="bloqueio_nota"
                                        placeholder="Ex: COTA OSASCO">
                                    <small class="text-muted d-block mt-1">Hóspede será ignorado caso utilize o
                                        bloqueio.</small>
                                </div>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Destino da Vaga</label>
                                <select name="colonia_acomodacao_id" id="selectAcomodacao" class="form-select">
                                    <option value="">-> ACOMODAÇÃO <-</option>
                                            <optgroup label="Acomodações Disponíveis">
                                                @foreach($acomodacoes as $aco)
                                                    @if(!isset($reservas[$aco->id]))
                                                        <option value="{{ $aco->id }}">Unidade: {{ $aco->identificador }}
                                                            ({{ $aco->tipo ?? 'Padrão' }})</option>
                                                    @endif
                                                @endforeach
                                            </optgroup>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Sair</button>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check me-1"></i> Confirmar
                                Alocação</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Editar Reserva/Fila -->
        <div class="modal fade" id="modalEditarReserva" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-pen-to-square me-2"></i>Editar
                            Reserva</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <!-- O action é preenchido dinamicamente via JS -->
                    <form id="formEditarReserva" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="agenda_periodo_id" value="{{ $periodoSelecionado }}">
                        <input type="hidden" name="colonia_id" value="{{ $coloniaSelecionada }}">

                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Tipo de Alocação</label>
                                    <select class="form-select border-primary bg-light" id="tipoAlocacaoEdit"
                                        onchange="toggleAlocacaoEditForms()">
                                        <option value="hospede">Hóspede (Sorteado)</option>
                                        <option value="bloqueio">Bloqueio / Cota Fixa</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3" id="blocoStatusEdit">
                                    <label class="form-label fw-bold">Situação *</label>
                                    <select name="status" id="statusReservaEdit" class="form-select border-primary bg-light">
                                        <option value="reservado">Reservado</option>
                                        <option value="confirmado">Confirmado</option>
                                        <option value="pago">Pago</option>
                                        <option value="bloqueado" hidden>Bloqueado</option>
                                        <option value="fila_espera" hidden>Fila de Espera</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Bloco Hóspede Edit -->
                            <div id="blocoHospedeEdit" class="border p-3 rounded bg-white shadow-sm mb-3">
                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2"><i
                                        class="fa-solid fa-user me-2"></i>Dados do Ganhador</h6>
                                <div class="row gx-2">
                                    <div class="col-md-12 mb-2">
                                        <label class="form-label fw-bold small mb-1">Nome Completo *</label>
                                        <input type="text" name="nome_hospede" id="edit_nome"
                                            class="form-control form-control-sm" required>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-bold small mb-1">Telefone / WhatsApp</label>
                                        <input type="text" name="telefone_hospede" id="edit_telefone"
                                            class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-bold small mb-1">E-mail</label>
                                        <input type="email" name="email_hospede" id="edit_email"
                                            class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <label class="form-label fw-bold small mb-1">Empresa</label>
                                        <select name="empresa_id" id="selectEmpresaEdit" class="form-select form-select-sm">
                                            <option value="">-- Sem Empresa Vinculada --</option>
                                            @foreach($empresas as $emp)
                                                <option value="{{ $emp->id }}">{{ $emp->razao_social }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Bloco Bloqueio Edit -->
                            <div id="blocoBloqueioEdit" class="d-none">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-danger">Nota de Bloqueio *</label>
                                    <input type="text" class="form-control border-danger" name="bloqueio_nota"
                                        id="edit_bloqueio" placeholder="Ex: COTA OSASCO">
                                    <small class="text-muted d-block mt-1">Hóspede será ignorado caso utilize o
                                        bloqueio.</small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Sair</button>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Salvar
                                Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        {{-- SweetAlert flash --}}
        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', timer: 3500, title: @json(session('success')), showConfirmButton: false, timerProgressBar: true });
                });
            </script>
        @endif
        @if(session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'error', timer: 5000, title: @json(session('error')), showConfirmButton: false, timerProgressBar: true });
                });
            </script>
        @endif

        <script>
            // Confirmar exclusão com motivo via SweetAlert
            function confirmarDelete(form) {
                Swal.fire({
                    title: 'Motivo da Exclusão',
                    html: `
                                                                                                                    <p class="text-muted small mb-3">Informe o motivo para excluir esta reserva. Este registro ficará salvo no histórico.</p>
                                                                                                                    <textarea id="motivoExclusao" rows="3" style="width:calc(100% - 4px);box-sizing:border-box;resize:vertical;border:1px solid #d9d9d9;border-radius:6px;padding:10px 12px;font-size:0.9rem;display:block;"
                                                                                                                        placeholder="Ex: Desistência do hóspede, reagendamento, cancelamento, bloqueio..."></textarea>
                                                                                                                `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fa-solid fa-trash-can me-1"></i> Excluir',
                    cancelButtonText: 'Cancelar',
                    focusCancel: false,
                    preConfirm: () => {
                        const motivo = document.getElementById('motivoExclusao').value.trim();
                        if (!motivo || motivo.length < 3) {
                            Swal.showValidationMessage('Por favor, informe o motivo com pelo menos 3 caracteres.');
                            return false;
                        }
                        return motivo;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Trocar action do form para a rota com histórico
                        const originalAction = form.action;
                        // Detectar se é rota de destroy (DELETE) e montar a url de excluir
                        // O form tem action como a rota destroy; vamos derivar a excluirComMotivo
                        // A rota destroy é: /agenda/reservas/{id}  (DELETE)
                        // A rota excluir é: /agenda/reservas/{id}/excluir (POST)
                        const match = originalAction.match(/\/agenda\/reservas\/(\d+)/);
                        if (match) {
                            const excluirUrl = `/agenda/reservas/${match[1]}/excluir`;
                            // Criar form temporário pois o original usa DELETE
                            const tempForm = document.createElement('form');
                            tempForm.method = 'POST';
                            tempForm.action = excluirUrl;

                            const csrfInput = document.createElement('input');
                            csrfInput.type = 'hidden';
                            csrfInput.name = '_token';
                            csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.content
                                || '{{ csrf_token() }}';

                            const motivoInput = document.createElement('input');
                            motivoInput.type = 'hidden';
                            motivoInput.name = 'motivo';
                            motivoInput.value = result.value;

                            tempForm.appendChild(csrfInput);
                            tempForm.appendChild(motivoInput);
                            document.body.appendChild(tempForm);
                            tempForm.submit();
                        } else {
                            // Fallback para o destroy original
                            form.submit();
                        }
                    }
                });
            }


            // Promover vaga modal
            function abrirPromover(esperaId, nome) {
                document.getElementById('nomePromovido').textContent = nome;
                const baseUrl = '{{ url("/agenda/reservas") }}';
                document.getElementById('formPromoverVaga').action = baseUrl + '/' + esperaId + '/promover';
                new bootstrap.Modal(document.getElementById('modalPromoverVaga')).show();
            }

            // Drag-and-Drop: Fila de Espera ââ€ â€™ Acomodação Vazia
            let draggedEsperaId = null;
            let draggedNome = null;

            function onDragStart(event, esperaId, nome) {
                draggedEsperaId = esperaId;
                draggedNome = nome;
                event.dataTransfer.effectAllowed = 'move';
                event.currentTarget.classList.add('dragging');
                setTimeout(() => event.currentTarget.classList.add('dragging'), 0);
            }

            document.addEventListener('dragend', function () {
                document.querySelectorAll('.card-espera.dragging').forEach(el => el.classList.remove('dragging'));
                document.querySelectorAll('.slot-vazio.drag-over').forEach(el => el.classList.remove('drag-over'));
            });

            function onDropEspera(event, acoId) {
                event.preventDefault();
                event.currentTarget.classList.remove('drag-over');
                if (!draggedEsperaId) return;

                // Pré-preencher o modal de promover
                document.getElementById('nomePromovido').textContent = draggedNome;
                const baseUrl = '{{ url("/agenda/reservas") }}';
                document.getElementById('formPromoverVaga').action = baseUrl + '/' + draggedEsperaId + '/promover';
                document.getElementById('selectVagaPromover').value = acoId;

                // Abrir confirmação
                Swal.fire({
                    title: 'Confirmar alocação?',
                    html: '<strong>' + draggedNome + '</strong> será promovido para a acomodação <strong>' + acoId + '</strong>.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    confirmButtonText: 'Sim, alocar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) document.getElementById('formPromoverVaga').submit();
                    draggedEsperaId = null;
                    draggedNome = null;
                });
            }

            function toggleAlocacaoForms() {
                const tipo = document.getElementById('tipoAlocacao').value;
                const statusSelect = document.getElementById('statusReserva');
                const blocoStatus = document.getElementById('blocoStatus');

                if (tipo === 'bloqueio') {
                    document.getElementById('blocoHospede').classList.add('d-none');
                    document.getElementById('blocoBloqueio').classList.remove('d-none');
                    if (blocoStatus) blocoStatus.style.display = 'none';
                    statusSelect.value = 'bloqueado';
                    document.querySelector('input[name="nome_hospede"]').required = false;
                } else {
                    document.getElementById('blocoHospede').classList.remove('d-none');
                    document.getElementById('blocoBloqueio').classList.add('d-none');
                    document.querySelector('input[name="bloqueio_nota"]').value = '';

                    if (blocoStatus) blocoStatus.style.display = 'block';
                    if (statusSelect.value === 'bloqueado') statusSelect.value = 'reservado';
                    document.querySelector('input[name="nome_hospede"]').required = true;
                }
            }

            function preencherAcomodacao(id, identificador) {
                document.getElementById('tipoAlocacao').value = 'hospede';
                toggleAlocacaoForms();
                document.getElementById('selectAcomodacao').value = id;
            }

            function preencherFilaEspera() {
                document.getElementById('tipoAlocacao').value = 'hospede';
                toggleAlocacaoForms();
                document.getElementById('selectAcomodacao').value = ''; // Empty string represents Fila de Espera
            }

            // Initialize TomSelect for Empresa in Create and Edit modals
            document.addEventListener("DOMContentLoaded", function () {
                // Initialize Select Add
                if (document.getElementById('selectEmpresa')) {
                    if (typeof TomSelect !== 'undefined') {
                        new TomSelect('#selectEmpresa', { create: false, sortField: { field: "text", direction: "asc" } });
                    } else if (typeof $ !== 'undefined' && $.fn.select2) {
                        $('#selectEmpresa').select2({
                            dropdownParent: $('#modalNovaReserva'),
                            theme: 'bootstrap-5',
                            width: '100%',
                            placeholder: 'Digite para buscar empresa...'
                        });
                    }
                }

                // Initialize Select Edit 
                if (document.getElementById('selectEmpresaEdit')) {
                    if (typeof TomSelect !== 'undefined') {
                        window.tomSelectEdit = new TomSelect('#selectEmpresaEdit', { create: false, sortField: { field: "text", direction: "asc" } });
                    } else if (typeof $ !== 'undefined' && $.fn.select2) {
                        window.select2Edit = $('#selectEmpresaEdit').select2({
                            dropdownParent: $('#modalEditarReserva'),
                            theme: 'bootstrap-5',
                            width: '100%',
                            placeholder: 'Digite para buscar empresa...'
                        });
                    }
                }
            });

            // Logica para Editar Reserva
            function toggleAlocacaoEditForms() {
                const tipo = document.getElementById('tipoAlocacaoEdit').value;
                const statusSelect = document.getElementById('statusReservaEdit');
                const blocoStatus = document.getElementById('blocoStatusEdit');

                if (tipo === 'bloqueio') {
                    document.getElementById('blocoHospedeEdit').classList.add('d-none');
                    document.getElementById('blocoBloqueioEdit').classList.remove('d-none');
                    if (blocoStatus) blocoStatus.style.display = 'none';
                    statusSelect.value = 'bloqueado';
                    document.getElementById('edit_nome').required = false;
                } else {
                    document.getElementById('blocoHospedeEdit').classList.remove('d-none');
                    document.getElementById('blocoBloqueioEdit').classList.add('d-none');
                    document.getElementById('edit_bloqueio').value = '';

                    if (blocoStatus) blocoStatus.style.display = 'block';
                    if (statusSelect.value === 'bloqueado' || statusSelect.value === 'fila_espera') {
                        statusSelect.value = 'reservado';
                    }
                    document.getElementById('edit_nome').required = true;
                }
            }

            function preencherEdicao(reservaId, status, bloqueioNota, nome, telefone, email, empresaId) {
                // Configura a Rota do Form
                const urlBase = "{{ route('agenda.reservas.update', ':id') }}";
                document.getElementById('formEditarReserva').action = urlBase.replace(':id', reservaId);

                // Define se é Bloqueio ou Hospede
                const tipoAlocacao = (bloqueioNota && bloqueioNota.trim() !== '') ? 'bloqueio' : 'hospede';
                document.getElementById('tipoAlocacaoEdit').value = tipoAlocacao;

                // Preencher campos
                document.getElementById('edit_bloqueio').value = bloqueioNota;
                document.getElementById('statusReservaEdit').value = status;

                document.getElementById('edit_nome').value = nome;
                document.getElementById('edit_telefone').value = telefone;
                document.getElementById('edit_email').value = email;

                if (window.tomSelectEdit) {
                    window.tomSelectEdit.setValue(empresaId);
                } else if (window.select2Edit) {
                    window.select2Edit.val(empresaId).trigger('change');
                } else {
                    document.getElementById('selectEmpresaEdit').value = empresaId;
                }

                // Ajusta visualização
                toggleAlocacaoEditForms();
            }
        </script>
    @endpush
@endsection