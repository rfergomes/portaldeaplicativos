@extends('layouts.app')

@section('title', 'Detalhes da Convenção')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-sm-6">
            <h1 class="h4 mb-0 text-gray-800 fw-bold">
                <i class="fa-solid fa-file-contract text-primary me-2"></i>{{ $convencao->titulo }}
            </h1>
            <p class="text-muted small mb-0">Gestão de vigência, documento oficial, cláusulas e termos aditivos</p>
        </div>
        <div class="col-sm-6 text-end">
            <a href="{{ route('admin.convencoes.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm me-1">
                <i class="fa-solid fa-arrow-left me-1"></i> Voltar
            </a>
            @if($convencao->arquivo_pdf)
                <a href="{{ route('admin.convencoes.download-pdf', $convencao) }}" target="_blank" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm me-1">
                    <i class="fa-solid fa-file-pdf me-1"></i> Baixar Convenção (PDF)
                </a>
            @endif
            <a href="{{ route('admin.convencoes.edit', $convencao) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fa-solid fa-pen me-1"></i> Editar Convenção
            </a>
        </div>
    </div>

    <!-- Informações da Convenção -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-3">
                            <small class="text-uppercase text-muted fw-bold d-block small">Categoria</small>
                            @if($convencao->categoria === 'QUIMICA')
                                <span class="badge bg-primary-subtle text-primary border border-primary px-3 py-2 fs-6">
                                    <i class="fa-solid fa-flask me-1"></i> Química
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-6">
                                    <i class="fa-solid fa-pills me-1"></i> Farmacêutica
                                </span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <small class="text-uppercase text-muted fw-bold d-block small">Vigência Geral</small>
                            <span class="fw-bold text-dark fs-6">
                                <i class="fa-regular fa-calendar-check text-primary me-1"></i>
                                {{ $convencao->vigencia_inicio ? $convencao->vigencia_inicio->format('d/m/Y') : '-' }} a {{ $convencao->vigencia_fim ? $convencao->vigencia_fim->format('d/m/Y') : '-' }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-uppercase text-muted fw-bold d-block small">Data-Base / Dissídio</small>
                            <span class="badge bg-light text-dark border px-3 py-2 fs-6">
                                {{ $convencao->data_base }}
                            </span>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <small class="text-uppercase text-muted fw-bold d-block small">Status & Anexo</small>
                            @if($convencao->ativo)
                                <span class="badge bg-success px-3 py-2 rounded-pill">Ativa</span>
                            @else
                                <span class="badge bg-secondary px-3 py-2 rounded-pill">Inativa</span>
                            @endif

                            @if($convencao->arquivo_pdf)
                                <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-2 rounded-pill ms-1" title="{{ $convencao->arquivo_nome_original }}">
                                    <i class="fa-solid fa-file-pdf me-1"></i> PDF Anexado ({{ $convencao->arquivo_tamanho_formatado }})
                                </span>
                            @endif
                        </div>
                        @if($convencao->abrangencia)
                            <div class="col-12 pt-2 border-top">
                                <small class="text-uppercase text-muted fw-bold d-block small mb-1">Abrangência Territorial e Econômica</small>
                                <p class="text-muted small mb-0">{{ $convencao->abrangencia }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Seção de Termos Aditivos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-0 py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="m-0 fw-bold text-primary">
                            <i class="fa-solid fa-file-circle-plus me-2"></i>Termos Aditivos Registrados ({{ $convencao->aditivos->count() }})
                        </h6>
                        <small class="text-muted">Renovações de campanhas salariais intermediárias (ex: 2027) e aditivos contratuais</small>
                    </div>
                    <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 shadow-sm fw-bold text-dark" onclick="abrirModalNovoAditivo()">
                        <i class="fa-solid fa-plus me-1"></i> Novo Termo Aditivo
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 premium-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" style="width: 140px;">Nº Termo</th>
                                    <th>Título e Resumo do Aditivo</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-center">Vigência do Aditivo</th>
                                    <th class="text-center">Documento</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-4" style="width: 130px;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($convencao->aditivos as $aditivo)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2 py-2 fs-6">
                                                <i class="fa-solid fa-file-signature me-1"></i> {{ $aditivo->numero_termo }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="d-block text-dark">{{ $aditivo->titulo }}</strong>
                                            @if($aditivo->descricao)
                                                <div class="text-muted small mt-1" style="max-width: 500px;">{{ Str::limit($aditivo->descricao, 150) }}</div>
                                            @endif
                                            @if($aditivo->data_assinatura)
                                                <small class="text-muted d-block mt-1">
                                                    <i class="fa-solid fa-signature me-1"></i> Assinado/Registrado em: {{ $aditivo->data_assinatura->format('d/m/Y') }}
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($aditivo->tipo === 'SALARIAL_ECONOMICO')
                                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1">Salarial / Econômico</span>
                                            @elseif($aditivo->tipo === 'GERAL_RETIFICATIVO')
                                                <span class="badge bg-info-subtle text-info border border-info px-2 py-1">Retificativo</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary px-2 py-1">{{ $aditivo->tipo }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center small">
                                            <i class="fa-regular fa-calendar-days text-muted me-1"></i>
                                            {{ $aditivo->vigencia_inicio->format('d/m/Y') }} a {{ $aditivo->vigencia_fim->format('d/m/Y') }}
                                        </td>
                                        <td class="text-center">
                                            @if($aditivo->arquivo_pdf)
                                                <a href="{{ route('admin.convencoes.aditivos.download-pdf', [$convencao, $aditivo]) }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 small shadow-sm" title="Baixar PDF do Aditivo ({{ $aditivo->arquivo_tamanho_formatado }})">
                                                    <i class="fa-solid fa-file-pdf me-1"></i> PDF
                                                </a>
                                            @else
                                                <span class="text-muted small">Sem PDF</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($aditivo->ativo)
                                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1">Ativo</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary px-2 py-1">Inativo</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" 
                                                    onclick='abrirModalEditarAditivo(@json($aditivo))' title="Editar Aditivo">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" 
                                                    onclick="confirmDeleteAditivo({{ $aditivo->id }})" title="Excluir Aditivo">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                            <form id="delete-aditivo-{{ $aditivo->id }}" 
                                                action="{{ route('admin.convencoes.aditivos.destroy', [$convencao, $aditivo]) }}" 
                                                method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <div class="mb-1"><i class="fa-solid fa-file-circle-plus fa-2x opacity-25"></i></div>
                                            Nenhum termo aditivo registrado ainda para esta convenção coletiva.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cláusulas da Convenção -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-0 py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="m-0 fw-bold text-primary">
                            <i class="fa-solid fa-list-ol me-2"></i>Cláusulas Registradas ({{ $convencao->clausulas->count() }})
                        </h6>
                        <small class="text-muted">Apenas as cláusulas relevantes cadastradas sob demanda para esta convenção</small>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" onclick="abrirModalNovaClausula()">
                        <i class="fa-solid fa-plus me-1"></i> Adicionar Cláusula
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 premium-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" style="width: 100px;">Cláusula</th>
                                    <th>Título, Origem e Teor Normativo</th>
                                    <th class="text-center">Categoria</th>
                                    <th class="text-center">Lembrete Automático</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-4" style="width: 130px;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($convencao->clausulas as $clausula)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="badge bg-dark text-white rounded-pill px-3 py-2 fs-6">
                                                Nº {{ $clausula->numero }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center flex-wrap gap-2">
                                                <strong class="text-dark">{{ $clausula->titulo }}</strong>
                                                @if($clausula->termoAditivo)
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning small">
                                                        <i class="fa-solid fa-file-circle-plus me-1"></i> {{ $clausula->termoAditivo->numero_termo }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-muted small mt-1" style="max-width: 600px; white-space: pre-line;">{{ Str::limit($clausula->texto, 200) }}</div>
                                            @if($clausula->vigencia_inicio || $clausula->vigencia_fim)
                                                <small class="text-muted d-block mt-1">
                                                    <i class="fa-regular fa-clock me-1"></i> Vigência específica: {{ $clausula->vigencia_inicio ? $clausula->vigencia_inicio->format('d/m/Y') : 'Início' }} a {{ $clausula->vigencia_fim ? $clausula->vigencia_fim->format('d/m/Y') : 'Fim' }}
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($clausula->categoria_clausula === 'CONTRIBUICAO')
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2 py-1">
                                                    Contribuição
                                                </span>
                                            @elseif($clausula->categoria_clausula === 'SALARIO_NORMATIVO')
                                                <span class="badge bg-info-subtle text-info-emphasis border border-info px-2 py-1">
                                                    Piso Salarial
                                                </span>
                                            @elseif($clausula->categoria_clausula === 'REAJUSTE')
                                                <span class="badge bg-success-subtle text-success-emphasis border border-success px-2 py-1">
                                                    Reajuste
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary px-2 py-1">
                                                    {{ $clausula->categoria_clausula }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($clausula->dispara_lembrete_lista_nominal)
                                                <span class="badge bg-primary text-white rounded-pill px-3 py-2 shadow-sm" title="Gatilho ativo para envio da lista nominal">
                                                    <i class="fa-solid fa-bell me-1"></i> Lista Nominal (15d)
                                                </span>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1 small" 
                                                    onclick="toggleLembrete({{ $clausula->id }})" title="Ativar como cláusula de cobrança de lista nominal">
                                                    <i class="fa-regular fa-bell me-1"></i> Definir como Ativa
                                                </button>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($clausula->ativo)
                                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1">Ativa</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary px-2 py-1">Inativa</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" 
                                                    onclick='abrirModalEditarClausula(@json($clausula))' title="Editar Cláusula">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" 
                                                    onclick="confirmDeleteClausula({{ $clausula->id }})" title="Excluir">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                            <form id="delete-clausula-{{ $clausula->id }}" 
                                                action="{{ route('admin.convencoes.clausulas.destroy', [$convencao, $clausula]) }}" 
                                                method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <div class="mb-2"><i class="fa-solid fa-clipboard-list fa-3x opacity-25"></i></div>
                                            Nenhuma cláusula cadastrada ainda nesta convenção coletiva.
                                            <div class="mt-2">
                                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="abrirModalNovaClausula()">
                                                    <i class="fa-solid fa-plus me-1"></i> Cadastrar Primeira Cláusula (Ex: Cláusula 76)
                                                </button>
                                            </div>
                                        </td>
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

@include('admin.convencoes._form_clausula_modal')
@include('admin.convencoes._form_aditivo_modal')

@push('scripts')
<script>
    const modalClausulaObj = new bootstrap.Modal(document.getElementById('modalClausula'));
    const modalAditivoObj = new bootstrap.Modal(document.getElementById('modalAditivo'));

    function abrirModalNovaClausula() {
        document.getElementById('modalClausulaTitle').innerHTML = '<i class="fa-solid fa-plus-circle me-2"></i>Adicionar Cláusula';
        document.getElementById('formClausula').action = "{{ route('admin.convencoes.clausulas.store', $convencao) }}";
        document.getElementById('clausulaMethod').value = 'POST';

        document.getElementById('clausulaNumero').value = '';
        document.getElementById('clausulaTitulo').value = '';
        document.getElementById('clausulaCategoria').value = 'CONTRIBUICAO';
        document.getElementById('clausulaTermoAditivo').value = '';
        document.getElementById('clausulaTexto').value = '';
        document.getElementById('clausulaVigenciaInicio').value = '';
        document.getElementById('clausulaVigenciaFim').value = '';
        document.getElementById('clausulaOrdem').value = '0';
        document.getElementById('clausulaLembrete').checked = false;
        document.getElementById('clausulaAtivo').checked = true;

        modalClausulaObj.show();
    }

    function abrirModalEditarClausula(clausula) {
        document.getElementById('modalClausulaTitle').innerHTML = '<i class="fa-solid fa-pen-to-square me-2"></i>Editar Cláusula ' + clausula.numero;
        document.getElementById('formClausula').action = `/admin/convencoes/{{ $convencao->id }}/clausulas/${clausula.id}`;
        document.getElementById('clausulaMethod').value = 'PUT';

        document.getElementById('clausulaNumero').value = clausula.numero;
        document.getElementById('clausulaTitulo').value = clausula.titulo;
        document.getElementById('clausulaCategoria').value = clausula.categoria_clausula;
        document.getElementById('clausulaTermoAditivo').value = clausula.convencao_termo_aditivo_id || '';
        document.getElementById('clausulaTexto').value = clausula.texto;
        document.getElementById('clausulaVigenciaInicio').value = clausula.vigencia_inicio ? clausula.vigencia_inicio.substring(0, 10) : '';
        document.getElementById('clausulaVigenciaFim').value = clausula.vigencia_fim ? clausula.vigencia_fim.substring(0, 10) : '';
        document.getElementById('clausulaOrdem').value = clausula.ordem || 0;
        document.getElementById('clausulaLembrete').checked = Boolean(clausula.dispara_lembrete_lista_nominal);
        document.getElementById('clausulaAtivo').checked = Boolean(clausula.ativo);

        modalClausulaObj.show();
    }

    function confirmDeleteClausula(id) {
        Swal.fire({
            title: 'Remover Cláusula?',
            text: "Esta cláusula será removida da convenção coletiva.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sim, remover',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-clausula-' + id).submit();
            }
        });
    }

    function toggleLembrete(clausulaId) {
        Swal.fire({
            title: 'Definir como Regra de Cobrança?',
            text: "Esta cláusula será utilizada como fundamento no envio automático do lembrete de 15 dias da lista nominal.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#033c5a',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sim, ativar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.patch(`/admin/convencoes/{{ $convencao->id }}/clausulas/${clausulaId}/toggle-lembrete`, {}, {
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                }).then(res => {
                    if (res.data.success) {
                        Swal.fire('Sucesso!', res.data.message, 'success').then(() => {
                            window.location.reload();
                        });
                    }
                }).catch(err => {
                    Swal.fire('Erro', 'Falha ao alterar regra de cobrança.', 'error');
                });
            }
        });
    }

    // Gestão de Termos Aditivos
    function abrirModalNovoAditivo() {
        document.getElementById('modalAditivoTitle').innerHTML = '<i class="fa-solid fa-file-circle-plus me-2"></i>Novo Termo Aditivo';
        document.getElementById('formAditivo').action = "{{ route('admin.convencoes.aditivos.store', $convencao) }}";
        document.getElementById('aditivoMethod').value = 'POST';

        document.getElementById('aditivoNumeroTermo').value = '';
        document.getElementById('aditivoTitulo').value = '';
        document.getElementById('aditivoTipo').value = 'SALARIAL_ECONOMICO';
        document.getElementById('aditivoDataAssinatura').value = '';
        document.getElementById('aditivoVigenciaInicio').value = '';
        document.getElementById('aditivoVigenciaFim').value = '';
        document.getElementById('aditivoDescricao').value = '';
        document.getElementById('aditivoArquivoPdf').value = '';
        document.getElementById('aditivoArquivoAtual').style.display = 'none';
        document.getElementById('aditivoAtivo').checked = true;

        modalAditivoObj.show();
    }

    function abrirModalEditarAditivo(aditivo) {
        document.getElementById('modalAditivoTitle').innerHTML = '<i class="fa-solid fa-pen-to-square me-2"></i>Editar ' + aditivo.numero_termo;
        document.getElementById('formAditivo').action = `/admin/convencoes/{{ $convencao->id }}/aditivos/${aditivo.id}`;
        document.getElementById('aditivoMethod').value = 'PUT';

        document.getElementById('aditivoNumeroTermo').value = aditivo.numero_termo;
        document.getElementById('aditivoTitulo').value = aditivo.titulo;
        document.getElementById('aditivoTipo').value = aditivo.tipo;
        document.getElementById('aditivoDataAssinatura').value = aditivo.data_assinatura ? aditivo.data_assinatura.substring(0, 10) : '';
        document.getElementById('aditivoVigenciaInicio').value = aditivo.vigencia_inicio ? aditivo.vigencia_inicio.substring(0, 10) : '';
        document.getElementById('aditivoVigenciaFim').value = aditivo.vigencia_fim ? aditivo.vigencia_fim.substring(0, 10) : '';
        document.getElementById('aditivoDescricao').value = aditivo.descricao || '';
        document.getElementById('aditivoArquivoPdf').value = '';

        if (aditivo.arquivo_pdf) {
            document.getElementById('aditivoArquivoAtual').style.display = 'block';
            document.getElementById('aditivoArquivoNome').innerText = aditivo.arquivo_nome_original || 'Arquivo Anexo PDF';
        } else {
            document.getElementById('aditivoArquivoAtual').style.display = 'none';
        }

        document.getElementById('aditivoAtivo').checked = Boolean(aditivo.ativo);

        modalAditivoObj.show();
    }

    function confirmDeleteAditivo(id) {
        Swal.fire({
            title: 'Excluir Termo Aditivo?',
            text: "O termo aditivo e seu documento em PDF serão removidos permanentemente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-aditivo-' + id).submit();
            }
        });
    }
</script>
@endpush
@endsection
