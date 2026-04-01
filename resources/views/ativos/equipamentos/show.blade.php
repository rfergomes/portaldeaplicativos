@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('ativos.equipamentos.index') }}" class="btn btn-white border shadow-sm me-3">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="flex-grow-1">
            <h1 class="h3 mb-0 text-gray-800">Detalhes do Ativo: {{ $equipamento->identificador }}</h1>
            <p class="text-muted small mb-0">{{ $equipamento->descricao }}</p>
        </div>
        <div class="text-end">
            <a href="{{ route('ativos.equipamentos.edit', $equipamento) }}" class="btn btn-dark shadow-sm">
                <i class="fa-solid fa-edit me-2"></i>Editar Equipamento
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Coluna da Esquerda: Infos Técnicas -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-circle-info me-2 text-primary"></i>Status Atual</h5>
                </div>
                <div class="card-body pt-0">
                    <div class="text-center py-4 bg-light rounded-3 mb-4">
                        @php
                            $statusClasses = [
                                'disponivel' => 'text-success',
                                'em_uso' => 'text-primary',
                                'manutencao' => 'text-warning',
                                'baixado' => 'text-danger',
                            ];
                            $color = $statusClasses[$equipamento->status] ?? 'text-secondary';
                        @endphp
                        <h2 class="fw-bold {{ $color }} mb-1 text-uppercase">{{ str_replace('_', ' ', $equipamento->status) }}</h2>
                        <div class="small text-muted fw-bold">
                            @if($equipamento->estacao)
                                <i class="fa-solid fa-desktop me-1"></i>ESTAÇÃO: {{ $equipamento->estacao->nome }}
                            @else
                                <i class="fa-solid fa-location-dot me-1"></i>LOCAL: {{ $equipamento->localizacao_atual }}
                            @endif
                        </div>
                    </div>

                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Fabricante</span>
                            <span class="fw-bold">{{ $equipamento->fabricante->nome ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Modelo</span>
                            <span class="fw-bold">{{ $equipamento->modelo ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Nº de Série</span>
                            <span class="fw-bold text-break">{{ $equipamento->numero_serie ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Acessórios Inclusos</span>
                            <span class="fw-bold text-end text-break">{{ $equipamento->acessorios ?: '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Fornecedor</span>
                            <span class="fw-bold text-end">{{ $equipamento->fornecedor->nome ?? '-' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-key me-2 text-primary"></i>Licenças de Software</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalVincularLicenca">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
                <div class="card-body pt-0">
                    <div class="list-group list-group-flush">
                        @forelse($equipamento->licencas as $lic)
                            <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold small">{{ $lic->nome }}</div>
                                    <div class="x-small text-muted">{{ $lic->chave ?? 'S/ Chave' }}</div>
                                </div>
                                <form action="{{ route('ativos.licencas.desvincular', [$lic->id, $equipamento->id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link text-danger p-0" onclick="return confirm('Desvincular licença?')">
                                        <i class="fa-solid fa-unlink small"></i>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="text-center py-3 text-muted x-small">Nenhuma licença vinculada.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- ANEXOS -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-paperclip me-2 text-primary"></i>Anexos / Documentos</h5>
                </div>
                <div class="card-body pt-0">
                    <ul class="list-group list-group-flush mb-3">
                        @forelse($equipamento->anexos as $anexo)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div class="d-flex align-items-center text-truncate pe-2">
                                    <i class="fa-solid fa-file-pdf text-danger me-2 shadow-sm"></i>
                                    <small class="fw-bold text-truncate" title="{{ $anexo->nome_original }}">{{ $anexo->nome_original }}</small>
                                </div>
                                <div class="btn-group btn-group-sm flex-shrink-0">
                                    <a href="{{ route('ativos.anexos.download', [$anexo->id, Str::slug(pathinfo($anexo->nome_original, PATHINFO_FILENAME)) . '.' . pathinfo($anexo->nome_original, PATHINFO_EXTENSION)]) }}" target="_blank" class="btn btn-link text-primary p-0 me-2" title="Baixar/Visualizar">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <form action="{{ route('ativos.anexos.destroy', $anexo->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0" onclick="return confirm('Tem certeza que deseja excluir o anexo?')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center py-4 text-muted border-0">
                                <i class="fa-solid fa-folder-open d-block mb-2 opacity-50 h4"></i>
                                Nenhum formato digital anexado.
                            </li>
                        @endforelse
                    </ul>

                    <form action="{{ route('ativos.equipamentos.anexos.store', $equipamento->id) }}" method="POST" enctype="multipart/form-data" class="bg-light p-3 rounded-3 border border-dashed">
                        @csrf
                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">Novo Documento</label>
                        <div class="input-group input-group-sm">
                            <input type="file" name="arquivo" class="form-control" required>
                            <button class="btn btn-primary px-3 fw-bold" type="submit">
                                <i class="fa-solid fa-upload me-1"></i> Anexar
                            </button>
                        </div>
                        <div class="form-text x-small text-muted mt-2">Formatos aceitos: PDF, JPG, PNG (máx 10mb).</div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-receipt me-2 text-primary"></i>Aquisição</h5>
                </div>
                <div class="card-body pt-0">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Data Compra</span>
                            <span class="fw-bold">{{ $equipamento->data_compra ? $equipamento->data_compra->format('d/m/Y') : '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Valor Unitário</span>
                            <span class="fw-bold">R$ {{ number_format($equipamento->valor_item, 2, ',', '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Nota Fiscal</span>
                            <span class="fw-bold">{{ $equipamento->valor_nota ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Garantia</span>
                            <span class="fw-bold">{{ $equipamento->garantia_meses ? $equipamento->garantia_meses . ' meses' : '-' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            @if($equipamento->is_depreciavel)
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-chart-line me-2 text-primary"></i>Depreciação Contábil</h5>
                    @if($equipamento->totalmente_depreciado)
                        <span class="badge bg-danger">TOTAL</span>
                    @endif
                </div>
                <div class="card-body pt-0">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1 small">
                            @php
                                $vidaUtilSafe = max(1, $equipamento->vida_util_meses ?? 1);
                                $percent = min(100, ($equipamento->meses_uso / $vidaUtilSafe) * 100);
                            @endphp
                            <span class="text-muted">Progresso ({{ round($percent) }}%)</span>
                            <span class="fw-bold">{{ $equipamento->meses_uso }} / {{ $equipamento->vida_util_meses }} meses</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            @php
                                $progressClass = $percent >= 100 ? 'bg-danger' : ($percent >= 80 ? 'bg-warning' : 'bg-success');
                            @endphp
                            <div class="progress-bar {{ $progressClass }}" role="progressbar" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>

                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Depreciação Mensal</span>
                            <span class="fw-bold">R$ {{ number_format($equipamento->depreciacao_mensal, 2, ',', '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Acumulada</span>
                            <span class="fw-bold">R$ {{ number_format($equipamento->depreciacao_acumulada, 2, ',', '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">Valor Residual</span>
                            <span class="fw-bold">R$ {{ number_format($equipamento->valor_residual, 2, ',', '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-light p-2 rounded">
                            <span class="text-muted fw-bold">Valor Contábil Atual</span>
                            <span class="fw-bold text-primary" style="font-size: 1.1rem;">R$ {{ number_format($equipamento->valor_atual, 2, ',', '.') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            @endif
        </div>

        <!-- Coluna da Direita: Histórico e Ações -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Histórico de Movimentações</h5>
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalNovaMovimentacao">
                        <i class="fa-solid fa-plus me-1"></i>Registrar Ação
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Data</th>
                                    <th>Ação</th>
                                    <th>Pessoa Responsável</th>
                                    <th>Destino / Obs</th>
                                    <th class="text-end pe-4">Registrado por</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($equipamento->movimentacoes as $mov)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ $mov->data_movimentacao->format('d/m/Y') }}</div>
                                        <div class="x-small text-muted">{{ $mov->data_movimentacao->format('H:i') }} h</div>
                                    </td>
                                    <td>
                                        @php
                                            $tipoClasses = [
                                                'cessao' => 'bg-primary text-white',
                                                'emprestimo' => 'bg-info text-dark',
                                                'devolucao' => 'bg-success text-white',
                                                'manutencao' => 'bg-warning text-dark',
                                                'transferencia' => 'bg-secondary text-white',
                                            ];
                                            $badgeClass = $tipoClasses[$mov->tipo] ?? 'bg-light';
                                        @endphp
                                        <span class="badge {{ $badgeClass }} text-uppercase">{{ $mov->tipo }}</span>
                                    </td>
                                    <td>{{ $mov->usuario->nome ?? '-' }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $mov->destino }}</div>
                                        <div class="x-small text-muted text-truncate" style="max-width: 200px;">{{ $mov->observacao }}</div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="fw-bold">{{ $mov->responsavel->name }}</div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Ainda não há movimentações registradas para este ativo.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nova Movimentação -->
<div class="modal fade" id="modalNovaMovimentacao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('ativos.movimentacoes.store') }}" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <input type="hidden" name="equipamento_id" value="{{ $equipamento->id }}">
            <div class="modal-header bg-success text-white border-0 py-3">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-right-left me-2"></i>Registrar Movimentação
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">

                    {{-- Tipo de Ação --}}
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted text-uppercase">Tipo de Ação</label>
                        <select name="tipo" class="form-select bg-light border-0 shadow-none mov-select-tipo" id="selectTipoMovimento" required>
                            <option value="">Selecione...</option>
                            <option value="cessao">Cessão de Uso (Longo Prazo)</option>
                            <option value="emprestimo">Empréstimo (Curto Prazo)</option>
                            <option value="devolucao" {{ $equipamento->status == 'em_uso' ? '' : 'disabled' }}>Devolução ao Estoque</option>
                            <option value="manutencao">Enviar para Manutenção</option>
                            <option value="transferencia">Transferência Interna</option>
                            <option value="baixa">Baixa de Equipamento</option>
                        </select>
                    </div>

                    {{-- CESSÃO: Cessionário + Previsão de Devolução --}}
                    <div class="col-md-12 field-usuario" style="display:none;">
                        <label class="form-label small fw-bold text-muted text-uppercase mov-label-usuario">Cessionário</label>
                        <select name="usuario_id" class="form-select bg-light border-0 shadow-none">
                            <option value="">Selecione...</option>
                            @foreach(\App\Models\AtivoUsuario::where('ativo', true)->orderBy('nome')->get() as $u)
                                <option value="{{ $u->id }}">{{ $u->nome }} ({{ $u->empresa->razao_social ?? 'S/ Empresa' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 field-devolu-prev" style="display:none;">
                        <label class="form-label small fw-bold text-muted text-uppercase mov-label-devolu">Previsão de Devolução</label>
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
                            <strong>Atenção:</strong> Ao confirmar a baixa, o equipamento será marcado como <strong>Baixado</strong> e um documento para contabilidade será gerado automaticamente.
                        </div>
                    </div>

                    {{-- ACESSÓRIOS INCLUSOS --}}
                    <div class="col-12 field-acessorios" style="display:none;">
                        <label class="form-label small fw-bold text-muted text-uppercase">Acessórios Inclusos</label>
                        <input type="text" name="acessorios" class="form-control bg-light border-0 shadow-none mov-input-acessorios" value="{{ $equipamento->acessorios }}" placeholder="Cabos, Mouses, Fontes...">
                        <div class="form-text small">Confirme os acessórios que acompanham o equipamento nesta saída.</div>
                    </div>

                    {{-- OBSERVAÇÃO (todos os tipos) --}}
                    <div class="col-12" id="field-obs-wrapper">
                        <label class="form-label small fw-bold text-muted text-uppercase">Observações / Motivo</label>
                        <textarea name="observacao" class="form-control bg-light border-0 shadow-none" rows="3" placeholder="Detalhes adicionais..."></textarea>
                    </div>

                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3">
                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                    <i class="fa-solid fa-check me-2"></i>Confirmar Registro
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Vincular Licença -->
<div class="modal fade" id="modalVincularLicenca" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('ativos.licencas.vincular', $equipamento->id) }}" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Vincular Licença de Software</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Selecione o Software / Licença</label>
                    <select name="licenca_id" class="form-select" required>
                        <option value="">Selecione...</option>
                        @foreach(\App\Models\AtivoLicenca::orderBy('nome')->get() as $lic)
                            <option value="{{ $lic->id }}">{{ $lic->nome }} ({{ $lic->chave }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary px-4 fw-bold">Vincular Agora</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    function handleMovShow(val) {
        const modal = $('#modalNovaMovimentacao .modal-body');
        
        // Reset
        modal.find('.field-usuario').slideUp(300).find('select, input').prop('required', false);
        modal.find('.field-devolu-prev').slideUp(300).find('input').prop('required', false);
        modal.find('.field-local-man').slideUp(300).find('input').prop('required', false);
        modal.find('.field-contato-man').slideUp(300).find('input').prop('required', false);
        modal.find('.field-depto-dest').slideUp(300).find('select').prop('required', false);
        modal.find('.field-estacao-dest').slideUp(300).find('select').prop('required', false);
        modal.find('.field-baixa-aviso').slideUp(300);
        modal.find('.field-acessorios').slideUp(300);

        if (val === 'cessao') {
            modal.find('.field-usuario').slideDown(300).find('select, input').prop('required', true);
            modal.find('.mov-label-usuario').text('Cessionário');
            modal.find('.field-devolu-prev').slideDown(300).find('input').prop('required', false);
            modal.find('.mov-label-devolu').text('Previsão de Devolução (Opcional)');
            modal.find('.field-acessorios').slideDown(300);
        } else if (val === 'emprestimo') {
            modal.find('.field-usuario').slideDown(300).find('select, input').prop('required', true);
            modal.find('.mov-label-usuario').text('Responsável pelo Empréstimo');
            modal.find('.field-devolu-prev').slideDown(300).find('input').prop('required', true);
            modal.find('.mov-label-devolu').text('Previsão de Devolução');
            modal.find('.field-acessorios').slideDown(300);
        } else if (val === 'manutencao') {
            modal.find('.field-local-man').slideDown(300).find('input').prop('required', true);
            modal.find('.field-contato-man').slideDown(300).find('input').prop('required', true);
            modal.find('.field-devolu-prev').slideDown(300).find('input').prop('required', true);
            modal.find('.mov-label-devolu').text('Previsão de Retorno');
        } else if (val === 'transferencia') {
            modal.find('.field-depto-dest').slideDown(300).find('select').prop('required', true);
            modal.find('.field-estacao-dest').slideDown(300).find('select').prop('required', false); // Optional
        } else if (val === 'baixa') {
            modal.find('.field-baixa-aviso').slideDown(300);
            modal.find('[name="observacao"]').prop('required', true);
        }

        if (val !== 'baixa') {
            modal.find('[name="observacao"]').prop('required', false);
        }
    }

    $('#selectTipoMovimento').on('change select2:select', function() {
        handleMovShow($(this).val());
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

    // Reset when modal closes
    $('#modalNovaMovimentacao').on('hidden.bs.modal', function() {
        $('#selectTipoMovimento').val('').trigger('change');
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
        @endif
    @endif

});
</script>
@endpush
@endsection
