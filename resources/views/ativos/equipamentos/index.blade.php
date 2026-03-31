@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fa-solid fa-laptop-code me-2 text-primary"></i>Controle de Equipamentos
            </h1>
            <p class="text-muted">Gerencie o inventário de hardware e dispositivos da empresa.</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('ativos.equipamentos.inventario.pdf') }}" class="btn btn-outline-danger shadow-sm me-2" target="_blank">
                <i class="fa-solid fa-file-pdf me-2"></i>Gerar Inventário
            </a>
            @can('ativos.criar')
            <a href="{{ route('ativos.equipamentos.create') }}" class="btn btn-primary shadow-sm">
                <i class="fa-solid fa-plus me-2"></i>Novo Equipamento
            </a>
            @endcan
        </div>
    </div>

    <!-- Cards Informativos -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="text-xs fw-bold text-success text-uppercase">Disponíveis</div>
                        <i class="fa-solid fa-check-circle text-success opacity-50"></i>
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalDisponivel }}</div>
                    <div class="progress progress-sm mt-3 shadow-sm">
                        <div class="progress-bar bg-success" style="width: {{ $totalGeral > 0 ? ($totalDisponivel / $totalGeral) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="text-xs fw-bold text-primary text-uppercase">Em Uso</div>
                        <i class="fa-solid fa-laptop text-primary opacity-50"></i>
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalEmUso }}</div>
                    <div class="progress progress-sm mt-3 shadow-sm">
                        <div class="progress-bar bg-primary" style="width: {{ $totalGeral > 0 ? ($totalEmUso / $totalGeral) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="text-xs fw-bold text-warning text-uppercase">Manutenção</div>
                        <i class="fa-solid fa-screwdriver-wrench text-warning opacity-50"></i>
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalManutencao }}</div>
                    <div class="progress progress-sm mt-3 shadow-sm">
                        <div class="progress-bar bg-warning" style="width: {{ $totalGeral > 0 ? ($totalManutencao / $totalGeral) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-4 border-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="text-xs fw-bold text-danger text-uppercase">Baixados</div>
                        <i class="fa-solid fa-arrow-down text-danger opacity-50"></i>
                    </div>
                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $totalBaixado }}</div>
                    <div class="progress progress-sm mt-3 shadow-sm">
                        <div class="progress-bar bg-danger" style="width: {{ $totalGeral > 0 ? ($totalBaixado / $totalGeral) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body">
            <form action="{{ route('ativos.equipamentos.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Identificador / Descrição</label>
                    <input type="text" name="identificador" class="form-control shadow-none" placeholder="Ex: NT-001..." value="{{ request('identificador') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Status</label>
                    <select name="status" class="form-select shadow-none">
                        <option value="">Todos os Status</option>
                        <option value="disponivel" {{ request('status') == 'disponivel' ? 'selected' : '' }}>Disponível</option>
                        <option value="em_uso" {{ request('status') == 'em_uso' ? 'selected' : '' }}>Em Uso</option>
                        <option value="manutencao" {{ request('status') == 'manutencao' ? 'selected' : '' }}>Manutenção</option>
                        <option value="baixado" {{ request('status') == 'baixado' ? 'selected' : '' }}>Baixado</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-dark w-100">
                        <i class="fa-solid fa-magnifying-glass me-2"></i>Filtrar
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('ativos.equipamentos.index') }}" class="btn btn-outline-secondary w-100">
                        Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Equipamentos -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Descrição</th>
                            <th>Modelo / Série</th>
                            <th>Aquisição / Valor</th>
                            <th>Status</th>
                            <th>Localização</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($equipamentos as $equipamento)
                        <tr>
                            <td class="ps-4">
                                <span class="badge text-bg-light border shadow-sm px-2">#EQP_{{ $equipamento->id }}</span>
                            </td>
                            <td>
                                <div>{{ $equipamento->descricao }}</div>
                                <div class="small text-muted">{{ $equipamento->fabricante->nome ?? 'S/ Fabricante' }}</div>
                            </td>
                            <td>
                                <div class="badge bg-light text-dark border">{{ $equipamento->modelo ?? '-' }}</div>
                                <div class="small text-muted">SN: {{ $equipamento->numero_serie ?? '-' }}</div>
                            </td>
                            <td>
                                <div>
                                    @if($equipamento->valor_nota)
                                        <span class="badge bg-secondary-subtle text-secondary border mb-1" title="Nº Nota Fiscal"><i class="fa-solid fa-file-invoice me-1"></i>{{ $equipamento->valor_nota }}</span>
                                    @else
                                        <span class="text-muted small mb-1 d-block">Sem Nota</span>
                                    @endif
                                </div>
                                <div class="small fw-bold {{ $equipamento->is_depreciavel ? 'text-primary' : 'text-muted' }}" title="Valor Contábil Atual">
                                    R$ {{ number_format($equipamento->valor_atual, 2, ',', '.') }}
                                </div>
                            </td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'disponivel' => 'bg-success-subtle text-success border-success',
                                        'em_uso' => 'bg-primary-subtle text-primary border-primary',
                                        'manutencao' => 'bg-warning-subtle text-warning border-warning',
                                        'baixado' => 'bg-danger-subtle text-danger border-danger',
                                    ];
                                    $class = $statusClasses[$equipamento->status] ?? 'bg-secondary';
                                @endphp
                                <span class="badge border {{ $class }} text-uppercase" style="font-size: 0.7rem;">
                                    {{ str_replace('_', ' ', $equipamento->status) }}
                                </span>
                            </td>
                            <td>
                                <i class="fa-solid fa-location-dot text-muted me-1"></i>
                                <span class="small">
                                    @if($equipamento->estacao)
                                        <div class="text-primary fw-bold" style="font-size: 0.8rem;">{{ $equipamento->estacao->nome }}</div>
                                        <div class="x-small text-muted text-uppercase" style="font-size: 0.65rem;">{{ $equipamento->estacao->departamento->nome }}</div>
                                    @elseif($equipamento->status === 'em_uso' && $equipamento->ultimaMovimentacao && $equipamento->ultimaMovimentacao->usuario)
                                        {{ $equipamento->ultimaMovimentacao->usuario->nome }}
                                    @else
                                        {{ $equipamento->localizacao_atual }}
                                    @endif
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm">
                                    <a href="{{ route('ativos.equipamentos.show', $equipamento) }}" class="btn btn-sm btn-white border" title="Detalhes">
                                        <i class="fa-solid fa-eye text-primary"></i>
                                    </a>
                                    @can('ativos.editar')
                                    <a href="{{ route('ativos.equipamentos.edit', $equipamento) }}" class="btn btn-sm btn-white border" title="Editar">
                                        <i class="fa-solid fa-pen-to-square text-dark"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-white border" title="Nova Movimentação" data-bs-toggle="modal" data-bs-target="#modalMovimentacao-{{ $equipamento->id }}">
                                        <i class="fa-solid fa-right-left text-success"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Nova Movimentação -->
                        <div class="modal fade" id="modalMovimentacao-{{ $equipamento->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg">
                                    <form action="{{ route('ativos.movimentacoes.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="equipamento_id" value="{{ $equipamento->id }}">
                                        <div class="modal-header bg-success text-white border-0 py-3">
                                            <h5 class="modal-title fw-bold">
                                                <i class="fa-solid fa-right-left me-2"></i>Mover: #{{ $equipamento->id }}
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                {{-- Tipo de Ação --}}
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Tipo de Ação</label>
                                                    <select name="tipo" class="form-select bg-light border-0 shadow-none select-tipo-movimento" required>
                                                        <option value="">Selecione...</option>
                                                        <option value="cessao" {{ $equipamento->status !== 'disponivel' ? 'disabled' : '' }}>Cessão de Uso (Longo Prazo)</option>
                                                        <option value="emprestimo" {{ $equipamento->status !== 'disponivel' ? 'disabled' : '' }}>Empréstimo (Curto Prazo)</option>
                                                        <option value="devolucao" {{ $equipamento->status === 'disponivel' ? 'disabled' : '' }}>Devolução ao Estoque</option>
                                                        <option value="manutencao" {{ $equipamento->status !== 'disponivel' ? 'disabled' : '' }}>Enviar para Manutenção</option>
                                                        <option value="transferencia" {{ $equipamento->status !== 'disponivel' ? 'disabled' : '' }}>Transferência Interna</option>
                                                        <option value="baixa" {{ $equipamento->status !== 'disponivel' ? 'disabled' : '' }}>Baixa de Equipamento</option>
                                                    </select>
                                                </div>

                                                {{-- CESSÃO / EMPRÉSTIMO: Cessionário + Previsão de Devolução --}}
                                                <div class="col-md-12 field-usuario" style="display:none;">
                                                    <label class="form-label small fw-bold text-muted text-uppercase field-label-usuario">Cessionário</label>
                                                    <select name="usuario_id" class="form-select bg-light border-0 shadow-none">
                                                        <option value="">Selecione...</option>
                                                        @foreach(\App\Models\AtivoUsuario::where('ativo', true)->orderBy('nome')->get() as $u)
                                                            <option value="{{ $u->id }}">{{ $u->nome }} ({{ $u->empresa->razao_social ?? 'S/ Empresa' }})</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-6 field-devolu-prev" style="display:none;">
                                                    <label class="form-label small fw-bold text-muted text-uppercase field-label-devolu">Previsão de Devolução</label>
                                                    <input type="date" name="data_previsao_devolucao" class="form-control bg-light border-0 shadow-none">
                                                </div>

                                                {{-- MANUTENÇÃO: Local + Contato + Previsão de Retorno --}}
                                                <div class="col-md-6 field-local-man" style="display:none;">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Local / Fornecedor</label>
                                                    <input type="text" name="local_manutencao" class="form-control bg-light border-0 shadow-none" placeholder="Ex: Assistência Técnica ABC">
                                                </div>

                                                <div class="col-md-6 field-contato-man" style="display:none;">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Contato</label>
                                                    <input type="text" name="contato_manutencao" class="form-control bg-light border-0 shadow-none" placeholder="Telefone ou e-mail">
                                                </div>

                                                {{-- TRANSFERÊNCIA: Departamento + Estação --}}
                                                <div class="col-md-6 field-depto-dest" style="display:none;">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Departamento de Destino</label>
                                                    <select name="destino_departamento_id" class="form-select bg-light border-0 shadow-none mov-select-depto">
                                                        <option value="">Selecione o departamento...</option>
                                                        @foreach(\App\Models\AtivoDepartamento::where('ativo', true)->orderBy('nome')->get() as $dep)
                                                            <option value="{{ $dep->id }}">{{ $dep->nome }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-6 field-estacao-dest" style="display:none;">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Estação de Trabalho de Destino</label>
                                                    <select name="destino_estacao_id" class="form-select bg-light border-0 shadow-none mov-select-estacao">
                                                        <option value="">Selecione a estação...</option>
                                                    </select>
                                                </div>

                                                {{-- BAIXA: Aviso --}}
                                                <div class="col-12 field-baixa-aviso" style="display:none;">
                                                    <div class="alert alert-danger border-0 mb-0">
                                                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                                        <strong>Atenção:</strong> Ao confirmar a baixa, o equipamento será marcado como <strong>Baixado</strong> e será gerado um documento de baixa para contabilidade.
                                                    </div>
                                                </div>

                                                {{-- ACESSÓRIOS INCLUSOS --}}
                                                <div class="col-12 field-acessorios" style="display:none;">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Acessórios Inclusos</label>
                                                    <input type="text" name="acessorios" class="form-control bg-light border-0 shadow-none" value="{{ $equipamento->acessorios }}" placeholder="Cabos, Mouses, Fontes...">
                                                    <div class="form-text small">Confirme os acessórios que acompanham o equipamento nesta saída.</div>
                                                </div>

                                                {{-- OBSERVAÇÃO (todos os tipos) --}}
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold text-muted text-uppercase">Observações / Motivo</label>
                                                    <textarea name="observacao" class="form-control bg-light border-0 shadow-none" rows="3" placeholder="Detalhes adicionais..."></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light border-0 py-3">
                                            <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-success px-4 shadow-sm fw-bold">
                                                <i class="fa-solid fa-check me-2"></i>Confirmar Registro
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-inbox fa-3x mb-3 opacity-25"></i>
                                <p>Nenhum equipamento encontrado com os filtros aplicados.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($equipamentos->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $equipamentos->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    function handleMovimentacaoChange(select) {
        const val = $(select).val();
        const modalBody = $(select).closest('.modal-body');

        // Reset
        modalBody.find('.field-usuario').slideUp(300).find('select, input').prop('required', false);
        modalBody.find('.field-devolu-prev').slideUp(300).find('input').prop('required', false);
        modalBody.find('.field-local-man').slideUp(300).find('input').prop('required', false);
        modalBody.find('.field-contato-man').slideUp(300).find('input').prop('required', false);
        modalBody.find('.field-depto-dest').slideUp(300).find('select').prop('required', false);
        modalBody.find('.field-estacao-dest').slideUp(300).find('select').prop('required', false);
        modalBody.find('.field-baixa-aviso').slideUp(300);
        modalBody.find('.field-acessorios').slideUp(300);

        if (val === 'cessao') {
            modalBody.find('.field-usuario').slideDown(300).find('select, input').prop('required', true);
            modalBody.find('.field-label-usuario').text('Cessionário');
            modalBody.find('.field-devolu-prev').slideDown(300).find('input').prop('required', false);
            modalBody.find('.field-label-devolu').text('Previsão de Devolução (Opcional)');
            modalBody.find('.field-acessorios').slideDown(300);
        } else if (val === 'emprestimo') {
            modalBody.find('.field-usuario').slideDown(300).find('select, input').prop('required', true);
            modalBody.find('.field-label-usuario').text('Responsável pelo Empréstimo');
            modalBody.find('.field-devolu-prev').slideDown(300).find('input').prop('required', true);
            modalBody.find('.field-label-devolu').text('Previsão de Devolução');
            modalBody.find('.field-acessorios').slideDown(300);
        } else if (val === 'manutencao') {
            modalBody.find('.field-local-man').slideDown(300).find('input').prop('required', true);
            modalBody.find('.field-contato-man').slideDown(300).find('input').prop('required', true);
            modalBody.find('.field-devolu-prev').slideDown(300).find('input').prop('required', true);
            modalBody.find('.field-label-devolu').text('Previsão de Retorno');
        } else if (val === 'transferencia') {
            modalBody.find('.field-depto-dest').slideDown(300).find('select').prop('required', true);
            modalBody.find('.field-estacao-dest').slideDown(300).find('select').prop('required', false); 
        } else if (val === 'baixa') {
            modalBody.find('.field-baixa-aviso').slideDown(300);
            modalBody.find('[name="observacao"]').prop('required', true);
        }

        if (val !== 'baixa') {
            modalBody.find('[name="observacao"]').prop('required', false);
        }
    }

    // Use jQuery event (Select2-compatible)
    $('.select-tipo-movimento').on('change select2:select', function() {
        handleMovimentacaoChange(this);
    });

    // Load estações when departamento changes (transferência)
    $('.mov-select-depto').on('change select2:select', function() {
        const deptoId = $(this).val();
        const estacaoSelect = $(this).closest('.modal-body').find('.mov-select-estacao');
        estacaoSelect.html('<option value="">Carregando...</option>');
        if (deptoId) {
            $.get('/ativos/api/estacoes?departamento_id=' + deptoId, function(data) {
                let opts = '<option value="">Selecione a estação...</option>';
                data.forEach(e => opts += `<option value="${e.id}">${e.nome}</option>`);
                estacaoSelect.html(opts);
            });
        } else {
            estacaoSelect.html('<option value="">Selecione a estação...</option>');
        }
    });

    @if(session('success'))
        @if(session('mov_tipo') === 'baixa')
            Swal.fire({
                title: 'Sucesso!',
                text: "{{ session('success') }}",
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-file-pdf me-1"></i> Gerar Documento de Baixa',
                cancelButtonText: 'Fechar',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open("{{ route('ativos.equipamentos.pdf_baixa', session('mov_equipamento_id')) }}", '_blank');
                }
            });
        @elseif(session('cessao_id'))
            Swal.fire({
                title: 'Sucesso!',
                text: "{{ session('success') }}",
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-file-pdf me-1"></i> Gerar Termo de Cessão',
                cancelButtonText: 'Fechar',
                confirmButtonColor: '#0d6efd'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open("{{ route('ativos.cessoes.pdf', session('cessao_id')) }}", '_blank');
                }
            });
        @elseif(session('devolucao_id'))
            Swal.fire({
                title: 'Sucesso!',
                text: "{{ session('success') }}",
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-file-pdf me-1"></i> Gerar Termo de Devolução',
                cancelButtonText: 'Fechar',
                confirmButtonColor: '#0d6efd'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open("{{ route('ativos.devolucao.pdf', session('devolucao_id')) }}", '_blank');
                }
            });
        @else
            Swal.fire('Sucesso!', "{{ session('success') }}", 'success');
        @endif
    @endif
});
</script>
@endpush
@endsection
