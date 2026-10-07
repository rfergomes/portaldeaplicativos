@extends('layouts.app')

@section('title', 'Templates de WhatsApp')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold">
                <i class="fa-brands fa-whatsapp text-success me-2"></i>Templates de WhatsApp
            </h1>
            <p class="text-muted mb-0">Modelos de mensagens homologados para comunicação e disparos em lote</p>
        </div>
        <div>
            <a href="{{ route('whatsapp-lotes.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-history me-1"></i>Histórico de Lotes
            </a>
            <button type="button" class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#modalNovoTemplate">
                <i class="fas fa-plus me-1"></i>Novo Template
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><strong>Verifique os erros:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Card de Filtros e Listagem -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('whatsapp-templates.index') }}" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="busca" class="form-control border-start-0" placeholder="Buscar por nome ou descrição..." value="{{ request('busca') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Todos os status</option>
                        <option value="ativo" {{ request('status') === 'ativo' ? 'selected' : '' }}>Apenas Ativos</option>
                        <option value="inativo" {{ request('status') === 'inativo' ? 'selected' : '' }}>Apenas Inativos</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter me-1"></i>Filtrar</button>
                    <a href="{{ route('whatsapp-templates.index') }}" class="btn btn-light border"><i class="fas fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Templates -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 220px;">Nome do Template</th>
                            <th>Descrição</th>
                            <th>Corpo de Exemplo</th>
                            <th style="width: 180px;">Parâmetros Extras</th>
                            <th class="text-center" style="width: 100px;">Status</th>
                            <th class="text-end pe-3" style="width: 140px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark font-monospace">{{ $template->nome }}</div>
                                    <small class="text-muted">Criado por: {{ $template->user?->name ?? 'Sistema' }}</small>
                                </td>
                                <td>
                                    <span class="text-secondary">{{ $template->descricao }}</span>
                                </td>
                                <td>
                                    @if($template->corpo_exemplo)
                                        <div class="small text-muted bg-light p-2 rounded border border-light-subtle" style="max-width: 420px; white-space: pre-wrap;">{{ $template->corpo_exemplo }}</div>
                                    @else
                                        <span class="text-muted fst-italic small">Não informado</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($template->parametros_esperados))
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($template->parametros_esperados as $param)
                                                <span class="badge bg-secondary-subtle text-secondary border">{{ $param }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted small">Apenas Nome (@{{1}})</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($template->ativo)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Ativo</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Inativo</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                            onclick='abrirModalEditar(@json($template))' 
                                            title="Editar Template">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('whatsapp-templates.destroy', $template) }}" method="POST" class="d-inline" onsubmit="return confirm('Deseja realmente inativar/remover este template?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Inativar/Excluir">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-brands fa-whatsapp fs-1 text-muted d-block mb-2"></i>
                                    Nenhum template de WhatsApp cadastrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($templates->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $templates->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Novo Template -->
<div class="modal fade" id="modalNovoTemplate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('whatsapp-templates.store') }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Cadastrar Template de WhatsApp</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nome do Template (API Kwik / WhatsApp) <span class="text-danger">*</span></label>
                        <input type="text" name="nome" class="form-control font-monospace" placeholder="Ex: convite_evento_anual" required pattern="^[a-zA-Z0-9_\-]+$">
                        <small class="text-muted">Deve coincidir exatamente com o nome homologado no painel da API (letras minúsculas, números e sublinhados).</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Descrição / Finalidade <span class="text-danger">*</span></label>
                        <input type="text" name="descricao" class="form-control" placeholder="Ex: Convite para festa de confraternização dos associados" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Texto / Corpo de Exemplo</label>
                        <textarea name="corpo_exemplo" rows="3" class="form-control" placeholder="Ex: Olá {{1}}, você está convidado para {{2}} no dia {{3}}."></textarea>
                        <small class="text-muted">Utilize marcadores {{1}}, {{2}} para ilustrar onde cada parâmetro será posicionado.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Parâmetros Adicionais Esperados (Separados por vírgula)</label>
                        <input type="text" name="parametros_esperados" class="form-control" placeholder="Ex: Nome do Evento, Data do Evento">
                        <small class="text-muted">O 1º parâmetro é sempre o Nome do Associado. Informe apenas parâmetros extras se o template exigir mais variáveis.</small>
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="ativo" value="1" id="novoAtivo" checked>
                        <label class="form-check-label fw-bold" for="novoAtivo">Template Ativo para Envio</label>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold px-4"><i class="fas fa-save me-1"></i>Salvar Template</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Template -->
<div class="modal fade" id="modalEditarTemplate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="formEditarTemplate" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Editar Template de WhatsApp</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nome do Template</label>
                        <input type="text" name="nome" id="editNome" class="form-control font-monospace" required pattern="^[a-zA-Z0-9_\-]+$">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Descrição / Finalidade <span class="text-danger">*</span></label>
                        <input type="text" name="descricao" id="editDescricao" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Texto / Corpo de Exemplo</label>
                        <textarea name="corpo_exemplo" id="editCorpo" rows="3" class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Parâmetros Adicionais Esperados (Separados por vírgula)</label>
                        <input type="text" name="parametros_esperados" id="editParametros" class="form-control">
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="ativo" value="1" id="editAtivo">
                        <label class="form-check-label fw-bold" for="editAtivo">Template Ativo para Envio</label>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4"><i class="fas fa-save me-1"></i>Salvar Alterações</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function abrirModalEditar(template) {
        const form = document.getElementById('formEditarTemplate');
        form.action = `/whatsapp-templates/${template.id}`;
        
        document.getElementById('editNome').value = template.nome || '';
        document.getElementById('editDescricao').value = template.descricao || '';
        document.getElementById('editCorpo').value = template.corpo_exemplo || '';
        document.getElementById('editParametros').value = Array.isArray(template.parametros_esperados) 
            ? template.parametros_esperados.join(', ') 
            : '';
        document.getElementById('editAtivo').checked = !!template.ativo;
        
        const modal = new bootstrap.Modal(document.getElementById('modalEditarTemplate'));
        modal.show();
    }
</script>
@endpush
@endsection
