@extends('layouts.app')

@section('title', 'Inscrições / Sorteio - Agenda Colônias')

@section('content')
    <div class="container-fluid py-3 px-4">

        {{-- ALERTAS DE SESSÃO via SweetAlert2 --}}
        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        toast: true, position: 'top-end', icon: 'success', timer: 3500,
                        title: @json(session('success')),
                        showConfirmButton: false, timerProgressBar: true
                    });
                });
            </script>
        @endif
        @if(session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        toast: true, position: 'top-end', icon: 'error', timer: 5000,
                        title: @json(session('error')),
                        showConfirmButton: false, timerProgressBar: true
                    });
                });
            </script>
        @endif

        {{-- CABEÇALHO --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h4 class="fw-bold mb-0"><i class="fa-solid fa-ticket text-primary me-2"></i>Inscrições / Sorteio
                </h4>
                <p class="text-muted small mb-0">Módulo opcional — registre os candidatos e marque os sorteados para
                    pré-reserva automática.</p>
            </div>
        </div>

        {{-- FILTROS: COLÔNIA + PERÍODO --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('agenda.inscricoes.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold mb-1">1. Selecione a Colônia</label>
                        <select name="colonia_id" class="form-select" required onchange="this.form.submit()">
                            <option value="">-- Escolha uma Colônia --</option>
                            @foreach($colonias as $col)
                                <option value="{{ $col->id }}" {{ $coloniaSelecionada == $col->id ? 'selected' : '' }}>
                                    {{ $col->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold mb-1">2. Selecione o Período</label>
                        <select name="periodo_id" class="form-select">
                            <option value="">-- Selecione um Período --</option>
                            @foreach($periodos as $per)
                                <option value="{{ $per->id }}" {{ $periodoSelecionado == $per->id ? 'selected' : '' }}>
                                    {{ $per->descricao }} ({{ \Carbon\Carbon::parse($per->data_inicial)->format('d/m') }} a
                                    {{ \Carbon\Carbon::parse($per->data_final)->format('d/m') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill shadow-sm">
                            <i class="fa-solid fa-search me-2"></i>Carregar Inscrições
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if($coloniaSelecionada && $periodoSelecionado && $colonia && $periodo)

            {{-- CABEÇALHO DO PAINEL --}}
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold text-primary mb-0">{{ $colonia->nome }}</h5>
                        <span class="text-muted small">
                            <i class="fa-solid fa-calendar-week me-1"></i>{{ $periodo->descricao }}
                            — {{ \Carbon\Carbon::parse($periodo->data_inicial)->format('d/m/Y') }} a
                            {{ \Carbon\Carbon::parse($periodo->data_final)->format('d/m/Y') }}
                        </span>
                    </div>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <span class="badge bg-secondary rounded-pill px-3 d-flex align-items-center gap-1">Total: {{ $inscricoes->count() }}</span>
                        <span class="badge bg-warning rounded-pill px-3 d-flex align-items-center gap-1">Pendentes: {{ $inscricoes->where('status', 'pendente')->count() }}</span>
                        <span class="badge bg-success rounded-pill px-3 d-flex align-items-center gap-1">Sorteados: {{ $inscricoes->where('status', 'sorteado')->count() }}</span>
                        <span class="badge bg-info rounded-pill px-3 d-flex align-items-center gap-1">Espera: {{ $inscricoes->where('status', 'espera')->count() }}</span>
                        <button class="btn btn-outline-primary btn-sm rounded-pill shadow-sm" data-bs-toggle="modal"
                            data-bs-target="#modalImpressao">
                            <i class="fa-solid fa-print me-1"></i>Impressões
                        </button>
                        <button class="btn btn-success btn-sm rounded-pill shadow-sm" data-bs-toggle="modal"
                            data-bs-target="#modalNovaInscricao">
                            <i class="fa-solid fa-user-plus me-1"></i>Nova Inscrição
                        </button>
                    </div>
                </div>
            </div>

            {{-- LISTA DE INSCRITOS --}}
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    @if($inscricoes->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-clipboard-list fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0 fw-bold">Nenhuma inscrição para este sorteio ainda.</p>
                            <p class="small">Clique em "Nova Inscrição" para adicionar os candidatos.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                            <thead>
                                    <tr>
                                        <th class="ps-3" style="width:40px">#</th>
                                        <th>Candidato</th>
                                        <th>Contato</th>
                                        <th>Empresa</th>
                                        <th>Observação</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Acomodação</th>
                                        <th class="text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inscricoes as $i => $insc)
                                        @php
                                            $rowClass = match ($insc->status) {
                                                'sorteado' => 'table-success',
                                                'espera' => 'table-warning',
                                                'cancelado' => 'table-secondary text-muted',
                                                default => '',
                                            };
                                            $badgeClass = match ($insc->status) {
                                                'sorteado' => 'bg-success',
                                                'espera' => 'bg-warning',
                                                'cancelado' => 'bg-secondary',
                                                default => 'badge-outline border',
                                            };
                                        @endphp
                                        <tr class="{{ $rowClass }}">
                                            <td class="ps-3 fw-bold text-muted">{{ $i + 1 }}</td>
                                            <td>
                                                <strong class="d-block">{{ $insc->hospede->nome ?? '—' }}</strong>
                                                @if($insc->reserva_id)
                                                    <span class="badge bg-primary rounded-pill" style="font-size:0.7rem;">
                                                        <i class="fa-solid fa-link me-1"></i>Pré-Reserva Gerada
                                                    </span>
                                                @endif
                                            </td>
                                            <td style="font-size:0.8rem;">
                                                <div><i
                                                        class="fa-solid fa-phone text-secondary me-1"></i>{{ $insc->hospede->telefone ?? '—' }}
                                                </div>
                                                @if($insc->hospede?->email)
                                                    <div><i
                                                            class="fa-regular fa-envelope text-secondary me-1"></i>{{ $insc->hospede->email }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td style="font-size:0.8rem;">{{ $insc->hospede?->empresa?->razao_social ?? '—' }}</td>
                                            <td style="font-size:0.8rem; max-width:180px;">
                                                {{ Str::limit($insc->observacao, 60) ?? '—' }}</td>
                                            <td class="text-center">
                                                <span
                                                    class="badge {{ $badgeClass }} rounded-pill px-3">{{ \App\Models\Agenda\AgendaInscricao::statusLabel($insc->status) }}</span>
                                                @if($insc->status === 'espera' && $insc->ordem_espera)
                                                    <div class="text-muted" style="font-size:0.7rem;">Posição {{ $insc->ordem_espera }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-center" style="font-size:0.85rem;">
                                                @if($insc->acomodacao)
                                                    <span class="fw-bold text-success">{{ $insc->acomodacao->identificador }}</span>
                                                    <span class="text-muted d-block"
                                                        style="font-size:0.7rem;">{{ $insc->acomodacao->tipo }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center align-items-center gap-2">
                                                    <button class="btn btn-sm btn-outline-primary" title="Definir Resultado do Sorteio"
                                                        onclick="abrirResultado({{ $insc->id }}, '{{ $insc->status }}', '{{ $insc->acomodacao_id }}', '{{ addslashes($insc->observacao ?? '') }}')"
                                                        data-bs-toggle="modal" data-bs-target="#modalResultado">
                                                        <i class="fa-solid fa-trophy"></i>
                                                    </button>
                                                    <form action="{{ route('agenda.inscricoes.destroy', $insc->id) }}" method="POST"
                                                        id="delete-form-{{ $insc->id }}" class="mb-0">
                                                        @csrf @method('DELETE')
                                                        <input type="hidden" name="colonia_id" value="{{ $coloniaSelecionada }}">
                                                        <input type="hidden" name="periodo_id" value="{{ $periodoSelecionado }}">
                                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Remover Inscrição"
                                                            onclick="confirmDelete('delete-form-{{ $insc->id }}', 'Remover esta inscrição?')">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        @else
            {{-- Estado Inicial: filtros não aplicados --}}
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-filter fa-3x mb-3 opacity-25"></i>
                <p class="fw-bold">Selecione a Colônia e o Período para ver as inscrições.</p>
            </div>
        @endif

    </div>

    {{-- MODAL: NOVA INSCRIÇÃO --}}
    <div class="modal fade" id="modalNovaInscricao" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-primary">
                        <i class="fa-solid fa-user-plus me-2"></i>Nova Inscrição para o Sorteio
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('agenda.inscricoes.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="colonia_id" value="{{ request('colonia_id') }}">
                    <input type="hidden" name="periodo_id" value="{{ request('periodo_id') }}">
                    <div class="modal-body">
                        <div class="alert alert-info border-0 small py-2">
                            <i class="fa-solid fa-info-circle me-1"></i>
                            Inscrição para <strong>{{ $colonia->nome ?? '' }}</strong> —
                            <strong>{{ $periodo->descricao ?? '' }}</strong>.
                            O status inicial é <strong>Pendente</strong>; o resultado é definido após o sorteio.
                        </div>
                        <div class="row gx-3">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Nome Completo *</label>
                                <input type="text" name="nome_hospede" class="form-control" placeholder="Ex: João da Silva"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Telefone / WhatsApp</label>
                                <input type="text" name="telefone_hospede" class="form-control"
                                    placeholder="(11) 99999-9999">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">E-mail</label>
                                <input type="email" name="email_hospede" class="form-control"
                                    placeholder="email@exemplo.com">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Empresa / Sindicato</label>
                                <select name="empresa_id" id="selectEmpresaInsc" class="form-select"
                                    placeholder="Digite para buscar...">
                                    <option value="">-- Sem Empresa Vinculada --</option>
                                    @foreach($empresas as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->razao_social }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label fw-bold">Observação</label>
                                <textarea name="observacao" class="form-control" rows="2"
                                    placeholder="Observações do atendimento..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-save me-1"></i> Registrar Inscrição
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: DEFINIR RESULTADO DO SORTEIO --}}
    <div class="modal fade" id="modalResultado" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-warning">
                        <i class="fa-solid fa-trophy me-2"></i>Resultado do Sorteio
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formResultado" action="" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Situação *</label>
                            <select name="status" id="statusResultado" class="form-select" required
                                onchange="toggleAcomodacao(this.value)">
                                <option value="pendente">Pendente (aguardando)</option>
                                <option value="sorteado">✅ Sorteado — Ganhou uma Vaga!</option>
                                <option value="espera">🟡 Lista de Espera — Suplente</option>
                                <option value="cancelado">❌ Cancelado</option>
                            </select>
                        </div>

                        <div id="blocoAcomodacao" class="mb-3 d-none">
                            <label class="form-label fw-bold text-success">
                                <i class="fa-solid fa-bed me-1"></i>Qual Acomodação ele ganhou? *
                            </label>
                            <select name="acomodacao_id" id="selectAcomodacaoResultado" class="form-select">
                                <option value="">-- Selecione --</option>
                                @if(isset($acomodacoesLivres))
                                    @foreach($acomodacoesLivres as $aco)
                                        <option value="{{ $aco->id }}">{{ $aco->identificador }} — {{ $aco->tipo ?? 'Padrão' }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted">Apenas unidades <strong>livres</strong> aparecem aqui. Ao salvar, a
                                pré-reserva será criada automaticamente no Painel de Reservas.</small>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold">Observação Complementar</label>
                            <textarea name="observacao" id="obsResultado" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning fw-bold">
                            <i class="fa-solid fa-check me-1"></i> Confirmar Resultado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Impressões --}}
    <div class="modal fade" id="modalImpressao" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-print me-2"></i>Gerar Documentos PDF</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        {{-- Guia de Pré-Reserva --}}
                        <div class="list-group-item border-0 rounded mb-3 p-3">
                            <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-file-invoice me-2"></i>Guias de Pré-Reserva (2 por folha)</h6>
                            <form action="{{ route('agenda.inscricoes.pdf.guia') }}" method="GET" target="_blank">
                                <input type="hidden" name="colonia_id" value="{{ $coloniaSelecionada }}">
                                <input type="hidden" name="periodo_id" value="{{ $periodoSelecionado }}">
                                
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="small fw-bold">Qtd. de Guias</label>
                                        <input type="number" name="quantidade" class="form-control form-control-sm" value="2" min="1" max="100">
                                    </div>
                                    <div class="col-6">
                                        <label class="small fw-bold">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                                            <i class="fa-solid fa-download me-1"></i>Gerar Guias
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted" style="font-size: 0.7rem;">Dica: 2 guias preenchem exatamente uma folha A4.</small>
                            </form>
                        </div>

                        {{-- Lista de Inscritos --}}
                        <div class="list-group-item border-0 rounded p-3">
                            <h6 class="fw-bold text-success mb-3"><i class="fa-solid fa-list-ol me-2"></i>Lista de Inscritos para Sorteio</h6>
                            <form action="{{ route('agenda.inscricoes.pdf.lista') }}" method="GET" target="_blank">
                                <input type="hidden" name="colonia_id" value="{{ $coloniaSelecionada }}">
                                <input type="hidden" name="periodo_id" value="{{ $periodoSelecionado }}">
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success btn-sm fw-bold">
                                        <i class="fa-solid fa-download me-1"></i>Gerar Lista Numerada
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-2" style="font-size: 0.7rem;">Gera a listagem sequencial de 1 em diante de todos que se inscreveram no sistema.</small>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function abrirResultado(inscId, status, acomodacaoId, observacao) {
                document.getElementById('formResultado').action = `/agenda/inscricoes/${inscId}?colonia_id={{ request('colonia_id') }}&periodo_id={{ request('periodo_id') }}`;
                document.getElementById('statusResultado').value = status;
                document.getElementById('obsResultado').value = observacao || '';

                if (acomodacaoId) {
                    const sel = document.getElementById('selectAcomodacaoResultado');
                    for (let opt of sel.options) { if (opt.value == acomodacaoId) { opt.selected = true; break; } }
                }

                toggleAcomodacao(status);
            }

            function toggleAcomodacao(status) {
                const bloco = document.getElementById('blocoAcomodacao');
                if (status === 'sorteado') {
                    bloco.classList.remove('d-none');
                } else {
                    bloco.classList.add('d-none');
                }
            }

            // TomSelect para busca de empresa
            document.addEventListener('DOMContentLoaded', function () {
                const selEmp = document.getElementById('selectEmpresaInsc');
                if (selEmp && typeof TomSelect !== 'undefined') {
                    new TomSelect(selEmp, { maxOptions: 50 });
                }
            });
        </script>
    @endpush

@endsection