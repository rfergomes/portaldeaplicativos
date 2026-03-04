@extends('layouts.app')

@section('title', 'Períodos da Agenda de Colônias')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-warning shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title fw-bold m-0"><i class="fa-solid fa-clock me-2"></i>Períodos (Semanas) de Sorteio</h3>
                    <div class="ms-auto d-flex gap-2">
                        <button class="btn btn-primary btn-sm rounded-pill shadow-sm" data-bs-toggle="modal"
                            data-bs-target="#modalGerarMes">
                            <i class="fa-solid fa-calendar-days me-1"></i> Gerar Semanas do Mês
                        </button>
                        <button class="btn btn-warning btn-sm rounded-pill shadow-sm" data-bs-toggle="modal"
                            data-bs-target="#modalNovoPeriodo">
                            <i class="fa-solid fa-plus me-1"></i> Criar Período Manual
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="80" class="text-center">ID</th>
                                    <th>Descrição do Bloco</th>
                                    <th class="text-center">De</th>
                                    <th class="text-center">Até</th>
                                    <th class="text-center text-danger">Data Limite (Confirmação)</th>
                                    <th class="text-center">Data Sorteio</th>
                                    <th class="text-center">Vencimento Guia</th>
                                    <th class="text-center">Status</th>
                                    <th width="150" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($periodos as $per)
                                    <tr>
                                        <td class="text-center text-muted">#{{ $per->id }}</td>
                                        <td class="fw-bold">{{ $per->descricao }}</td>
                                        <td class="text-center">{{ $per->data_inicial->format('d/m/Y') }}</td>
                                        <td class="text-center">{{ $per->data_final->format('d/m/Y') }}</td>
                                        <td class="text-center text-danger fw-bold">
                                            {{ $per->data_limite ? $per->data_limite->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="text-center">{{ $per->data_sorteio ? $per->data_sorteio->format('d/m/Y') : '-' }}</td>
                                        <td class="text-center text-primary">{{ $per->data_limite_pagamento ? $per->data_limite_pagamento->format('d/m/Y') : '-' }}</td>
                                        <td class="text-center">
                                            @if($per->ativo)
                                                <span class="badge text-bg-success rounded-pill px-3">Aberto</span>
                                            @else
                                                <span class="badge text-bg-secondary rounded-pill px-3">Fechado</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-secondary rounded-circle" title="Editar"
                                                onclick="editarPeriodo({{ collect($per)->merge([
                                                    'data_inicial_raw' => $per->data_inicial->format('Y-m-d'),
                                                    'data_final_raw' => $per->data_final->format('Y-m-d'),
                                                    'data_limite_raw' => $per->data_limite ? $per->data_limite->format('Y-m-d') : '',
                                                    'data_sorteio_raw' => $per->data_sorteio ? $per->data_sorteio->format('Y-m-d') : '',
                                                    'data_limite_pagamento_raw' => $per->data_limite_pagamento ? $per->data_limite_pagamento->format('Y-m-d') : ''
                                                ])->toJson() }})">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <form action="{{ route('agenda.periodos.destroy', $per->id) }}" method="POST"
                                                class="d-inline-block"
                                                onsubmit="return confirm('Tem certeza que deseja excluir este período? Vagas associadas a ele também serão impactadas.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle"
                                                    title="Excluir">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fa-solid fa-clock fa-2x mb-3 opacity-25"></i>
                                            <p class="m-0">Nenhum período cadastrado.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($periodos->hasPages())
                    <div class="card-footer clearfix">
                        {{ $periodos->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Novo/Editar Período -->
    <div class="modal fade" id="modalNovoPeriodo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTitlePeriodo"><i class="fa-solid fa-clock me-2"></i>Novo
                        Período de Sorteio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('agenda.periodos.store') }}" method="POST" id="formPeriodo">
                    @csrf
                    <input type="hidden" name="_method" id="methodPeriodo" value="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descrição *</label>
                            <input type="text" class="form-control" name="descricao" id="descPeriodo" required
                                placeholder="Ex: 2ª Semana Janeiro-2026">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Data Inicial (Entrada) *</label>
                                <input type="date" class="form-control" name="data_inicial" id="dataIniPeriodo" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Data Final (Saída) *</label>
                                <input type="date" class="form-control" name="data_final" id="dataFimPeriodo" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Data do Sorteio</label>
                                <input type="date" class="form-control" name="data_sorteio" id="dataSorteioPeriodo">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-primary">Vencimento da Guia</label>
                                <input type="date" class="form-control" name="data_limite_pagamento" id="dataLimitePagtoPeriodo">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-danger">Data Limite p/ Confirmação (Planilha)</label>
                            <input type="date" class="form-control w-50" name="data_limite" id="dataLimPeriodo">
                            <small class="text-muted">Data que aparece em vermelho no topo da planilha de reservas.</small>
                        </div>

                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="ativo" id="ativoPeriodo" value="1"
                                checked>
                            <label class="form-check-label" for="ativoPeriodo">Período Aberto / Visível</label>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning"><i class="fa-solid fa-save me-1"></i> Salvar
                            Período</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Gerar Mês -->
    <div class="modal fade" id="modalGerarMes" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-calendar-days me-2"></i>Gerar Semanas
                        Automáticas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('agenda.periodos.gerar') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info border-0 shadow-sm small py-2">
                            <i class="fa-solid fa-info-circle me-1"></i> O sistema encontrará todas as
                            <strong>Quintas-feiras</strong> do mês selecionado e criará períodos com encerramento na
                            <strong>Terça-feira</strong> seguinte de forma automática.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Selecione o Mês e Ano *</label>
                            <input type="month" class="form-control text-center fs-5 shadow-sm" name="mes_ano" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-cogs me-1"></i> Gerar
                            Semanas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function editarPeriodo(per) {
                document.getElementById('modalTitlePeriodo').innerHTML = '<i class="fa-solid fa-pen me-2"></i>Editar Período #' + per.id;
                document.getElementById('formPeriodo').action = '/agenda/periodos/' + per.id;
                document.getElementById('methodPeriodo').value = 'PUT';

                document.getElementById('descPeriodo').value = per.descricao;
                document.getElementById('dataIniPeriodo').value = per.data_inicial_raw;
                document.getElementById('dataFimPeriodo').value = per.data_final_raw;
                document.getElementById('dataLimPeriodo').value = per.data_limite_raw;
                document.getElementById('dataSorteioPeriodo').value = per.data_sorteio_raw || '';
                document.getElementById('dataLimitePagtoPeriodo').value = per.data_limite_pagamento_raw || '';
                document.getElementById('ativoPeriodo').checked = per.ativo == 1;

                var myModal = new bootstrap.Modal(document.getElementById('modalNovoPeriodo'));
                myModal.show();
            }

            document.getElementById('modalNovoPeriodo').addEventListener('hidden.bs.modal', function () {
                document.getElementById('modalTitlePeriodo').innerHTML = '<i class="fa-solid fa-clock me-2"></i>Novo Período de Sorteio';
                document.getElementById('formPeriodo').action = '{{ route('agenda.periodos.store') }}';
                document.getElementById('methodPeriodo').value = 'POST';
                document.getElementById('formPeriodo').reset();
            });
        </script>
    @endpush
@endsection