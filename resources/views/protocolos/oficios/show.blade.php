@extends('layouts.app')

@section('title', 'Detalhes do Ofício Coletivo #' . $protocolo->id)

@section('content')
<div class="container-fluid">
    <!-- Cabeçalho -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 text-gray-800 mb-0">Detalhes do Ofício Coletivo #{{ $protocolo->id }}</h1>
            <p class="text-muted small mb-0">Referência: <strong>{{ $protocolo->referencia_documento ?? 'N/A' }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('protocolos.oficios.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Voltar
            </a>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-novo-lote">
                <i class="fa-solid fa-paper-plane me-1"></i> Disparar Novo Lote de Empresas
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Dados Principais do Ofício -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fa-solid fa-file-invoice me-2"></i>Informações do Ofício
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Assunto:</strong><br>{{ $protocolo->assunto }}</p>
                    <p class="mb-2"><strong>Referência:</strong><br>{{ $protocolo->referencia_documento ?? 'Não informada' }}</p>
                    <p class="mb-2"><strong>Criado em:</strong><br>{{ $protocolo->created_at->format('d/m/Y H:i:s') }}</p>
                    <p class="mb-2"><strong>Criado por:</strong><br>{{ $protocolo->usuario->name ?? 'Sistema' }}</p>
                    <p class="mb-3">
                        <strong>Status Geral:</strong><br>
                        @switch($protocolo->status)
                            @case('sucesso')
                                <span class="badge bg-success fs-6">Sucesso</span>
                                @break
                            @case('enviado')
                                <span class="badge bg-info fs-6">Enviado</span>
                                @break
                            @case('falha')
                                <span class="badge bg-danger fs-6">Falha</span>
                                @break
                            @default
                                <span class="badge bg-warning text-dark fs-6">{{ ucfirst($protocolo->status) }}</span>
                        @endswitch
                    </p>

                    @if($protocolo->anexos && $protocolo->anexos->isNotEmpty())
                        <hr>
                        <h6 class="font-weight-bold text-dark mb-2"><i class="fa-solid fa-paperclip me-1"></i> Anexos:</h6>
                        <ul class="list-group list-group-flush">
                            @foreach($protocolo->anexos as $anexo)
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <span class="text-truncate me-2" style="max-width: 200px;" title="{{ $anexo->nome_original }}">{{ $anexo->nome_original }}</span>
                                    <a href="{{ route('protocolos.anexos.download', [$protocolo->id, $anexo->id]) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <!-- Timeline de Destinatários e Envios -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fa-solid fa-timeline me-2"></i>Timeline de Destinatários Notificados ({{ $protocolo->destinatarios->count() }})
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Empresa</th>
                                    <th>Contato Destinatário</th>
                                    <th>Status Envio AR-Online</th>
                                    <th class="text-end">Comprovantes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($protocolo->destinatarios as $dest)
                                    @php
                                        $envio = $dest->envios->first();
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $dest->empresa->razao_social ?? 'Sem Empresa' }}</strong>
                                            @if($dest->empresa?->cidade)
                                                <br><small class="text-muted">{{ $dest->empresa->cidade }} - {{ $dest->empresa->estado }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $dest->nome }}</strong>
                                            <br><small class="text-muted"><i class="fa-regular fa-envelope me-1"></i>{{ $dest->email }}</small>
                                        </td>
                                        <td>
                                            @if($envio)
                                                @switch($envio->status)
                                                    @case('lido')
                                                        <span class="badge bg-success"><i class="fa-solid fa-eye me-1"></i> Lido</span>
                                                        @break
                                                    @case('entregue')
                                                        <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Entregue</span>
                                                        @break
                                                    @case('enviado')
                                                        <span class="badge bg-info"><i class="fa-solid fa-paper-plane me-1"></i> Enviado</span>
                                                        @break
                                                    @case('falha')
                                                        <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i> Falha</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ ucfirst($envio->status) }}</span>
                                                @endswitch
                                                <br><small class="text-muted">ID: {{ substr($envio->id_email_externo, 0, 18) }}...</small>
                                            @else
                                                <span class="badge bg-secondary">Pendente</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($envio && $envio->id_email_externo)
                                                <a href="{{ route('protocolos.comprovante', [$protocolo->id, $envio->id]) }}" class="btn btn-sm btn-outline-danger me-1" title="Baixar Comprovante PDF">
                                                    <i class="fa-solid fa-file-pdf"></i> Comprovante
                                                </a>
                                                <a href="{{ route('protocolos.laudo', [$protocolo->id, $envio->id]) }}" class="btn btn-sm btn-outline-info" title="Laudo Pericial">
                                                    <i class="fa-solid fa-certificate"></i> Laudo
                                                </a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Nenhum destinatário registrado neste ofício.</td>
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

<!-- Modal para Disparar Novo Lote -->
<div class="modal fade" id="modal-novo-lote" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="{{ route('protocolos.oficios.dispararLote', $protocolo->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold text-primary">
                        <i class="fa-solid fa-paper-plane me-2"></i>Disparar Novo Lote de Empresas
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Selecione novas empresas/contatos para receber este mesmo Ofício. Destinatários que já foram notificados não serão re-enviados.</p>

                    <!-- Filtros Rápidos no Modal -->
                    <div class="row g-2 mb-3 bg-light p-3 rounded">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Região</label>
                            <select class="form-select form-select-sm" id="modal-filtro-regiao">
                                <option value="">Todas as Regiões</option>
                                @foreach($regioes as $regiao)
                                    <option value="{{ $regiao->id }}">{{ $regiao->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Categoria</label>
                            <select class="form-select form-select-sm" id="modal-filtro-categoria">
                                <option value="">Todas as Categorias</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Buscar</label>
                            <input type="text" class="form-control form-select-sm" id="modal-filtro-termo" placeholder="Razão social...">
                        </div>
                    </div>

                    <!-- Tabela de Empresas para o Lote -->
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 40px;" class="text-center">
                                        <input type="checkbox" class="form-check-input" id="modal-check-selecionar-todos">
                                    </th>
                                    <th>Empresa</th>
                                    <th>Contatos Disponíveis</th>
                                </tr>
                            </thead>
                            <tbody id="modal-tbody-empresas">
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i> Carregando lista...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btn-enviar-lote">
                        <i class="fa-solid fa-paper-plane me-1"></i> Disparar Lote para Selecionados
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const jaNotificados = @json($jaNotificadosClienteIds);
    const filtroRegiao = document.getElementById('modal-filtro-regiao');
    const filtroCategoria = document.getElementById('modal-filtro-categoria');
    const filtroTermo = document.getElementById('modal-filtro-termo');
    const tbody = document.getElementById('modal-tbody-empresas');
    const checkSelecionarTodos = document.getElementById('modal-check-selecionar-todos');

    function carregarEmpresasModal() {
        const params = new URLSearchParams({
            regiao_id: filtroRegiao.value,
            categoria: filtroCategoria.value,
            termo: filtroTermo.value,
            apenas_ativas: '1'
        });

        fetch(`{{ route('protocolos.oficios.api.empresas') }}?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                tbody.innerHTML = '';
                if (!data.empresas || data.empresas.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="3" class="text-center text-muted py-4">Nenhuma empresa encontrada.</td></tr>`;
                    return;
                }

                data.empresas.forEach(emp => {
                    const contatosHtml = emp.contatos.map(c => {
                        const jaRecebeu = jaNotificados.includes(c.id);
                        if (jaRecebeu) {
                            return `<div class="text-muted small mb-1"><i class="fa-solid fa-circle-check text-success me-1"></i> ${c.nome} (${c.email}) - <span class="badge bg-light text-success border">Já Notificado</span></div>`;
                        }
                        return `
                            <div class="form-check">
                                <input class="form-check-input check-modal-contato" type="checkbox" name="novos_contatos[]" value="${c.id}" id="mc-${c.id}">
                                <label class="form-check-label" for="mc-${c.id}">
                                    <strong>${c.nome}</strong> (${c.email})
                                </label>
                            </div>
                        `;
                    }).join('');

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input check-modal-empresa">
                        </td>
                        <td>
                            <strong>${emp.razao_social}</strong><br>
                            <small class="text-muted">${emp.cidade || ''} - ${emp.regiao || ''}</small>
                        </td>
                        <td>${contatosHtml}</td>
                    `;
                    tbody.appendChild(tr);
                });

                bindModalCheckboxes();
            });
    }

    function bindModalCheckboxes() {
        document.querySelectorAll('.check-modal-empresa').forEach(chkEmp => {
            chkEmp.addEventListener('change', function () {
                const tr = this.closest('tr');
                tr.querySelectorAll('.check-modal-contato').forEach(chk => chk.checked = this.checked);
            });
        });
    }

    checkSelecionarTodos.addEventListener('change', function () {
        const isChecked = this.checked;
        document.querySelectorAll('.check-modal-empresa').forEach(chk => chk.checked = isChecked);
        document.querySelectorAll('.check-modal-contato').forEach(chk => chk.checked = isChecked);
    });

    filtroRegiao.addEventListener('change', carregarEmpresasModal);
    filtroCategoria.addEventListener('change', carregarEmpresasModal);
    filtroTermo.addEventListener('input', carregarEmpresasModal);

    carregarEmpresasModal();
});
</script>
@endpush
@endsection
