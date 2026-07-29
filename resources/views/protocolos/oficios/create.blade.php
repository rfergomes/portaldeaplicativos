@extends('layouts.app')

@section('title', 'Novo Ofício Coletivo')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 text-gray-800 mb-0">Novo Ofício Coletivo (Envio em Massa)</h1>
            <p class="text-muted small mb-0">Envio de comunicação oficial com respaldo jurídico AR-Online para múltiplas empresas.</p>
        </div>
        <a href="{{ route('protocolos.oficios.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Voltar
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i> <strong>Atenção!</strong> Por favor verifique os erros abaixo:
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('protocolos.oficios.store') }}" method="POST" enctype="multipart/form-data" id="form-oficio-coletivo">
        @csrf

        <div class="row">
            <!-- Coluna de Filtro e Seleção de Destinatários -->
            <div class="col-lg-12 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fa-solid fa-filter me-2"></i>Filtros para Seleção de Empresas e Contatos
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Região</label>
                                <select class="form-select" id="filtro-regiao">
                                    <option value="">Todas as Regiões</option>
                                    @foreach($regioes as $regiao)
                                        <option value="{{ $regiao->id }}">{{ $regiao->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Categoria / Tipo</label>
                                <select class="form-select" id="filtro-categoria">
                                    <option value="">Todas as Categorias</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Buscar por Nome / CNPJ</label>
                                <input type="text" class="form-control" id="filtro-termo" placeholder="Digite razão social, CNPJ...">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="filtro-ativas" checked>
                                    <label class="form-check-label fw-bold" for="filtro-ativas">
                                        Apenas Ativas
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Destinatários Parcial -->
            <div class="col-lg-12">
                @include('protocolos.oficios.partials.tabela_empresas')
            </div>

            <!-- Coluna do Conteúdo do Ofício -->
            <div class="col-lg-12">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fa-solid fa-file-lines me-2"></i>Dados do Ofício
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Tipo de Protocolo</label>
                                <select name="tipo_protocolo_id" class="form-select">
                                    <option value="">Nenhum / Padrão</option>
                                    @foreach($tiposProtocolo as $tipo)
                                        <option value="{{ $tipo->id }}">{{ $tipo->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Referência do Documento</label>
                                <input type="text" name="referencia_documento" class="form-control" placeholder="Ex: OFÍCIO 015/2026" value="{{ old('referencia_documento') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Anexos (Até 5 arquivos, Máx 20MB cada)</label>
                                <input type="file" name="anexos[]" class="form-control" multiple>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Assunto <span class="text-danger">*</span></label>
                                <input type="text" name="assunto" class="form-control" required placeholder="Digite o assunto do ofício..." value="{{ old('assunto') }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Corpo da Mensagem <span class="text-danger">*</span></label>
                                <textarea name="corpo" class="form-control" rows="8" required placeholder="Prezado(a) {nome_contato}, comunicamos que...">{{ old('corpo') }}</textarea>
                                <small class="text-muted">
                                    Variáveis dinâmicas disponíveis: <code>{nome_contato}</code>, <code>{empresa}</code>, <code>{email}</code>.
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-end py-3">
                        <button type="submit" class="btn btn-primary btn-lg" id="btn-disparar">
                            <i class="fa-solid fa-paper-plane me-2"></i> Disparar Ofício Coletivo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filtroRegiao = document.getElementById('filtro-regiao');
    const filtroCategoria = document.getElementById('filtro-categoria');
    const filtroTermo = document.getElementById('filtro-termo');
    const filtroAtivas = document.getElementById('filtro-ativas');
    const tbody = document.getElementById('tbody-empresas');
    const checkSelecionarTodos = document.getElementById('check-selecionar-todos');
    const badgeContador = document.getElementById('badge-contador-selecionados');

    let debounceTimer = null;

    function carregarEmpresas() {
        const params = new URLSearchParams({
            regiao_id: filtroRegiao.value,
            categoria: filtroCategoria.value,
            termo: filtroTermo.value,
            apenas_ativas: filtroAtivas.checked ? '1' : '0'
        });

        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4"><i class="fa-solid fa-spinner fa-spin me-2"></i> Carregando empresas...</td></tr>`;

        fetch(`{{ route('protocolos.oficios.api.empresas') }}?${params.toString()}`)
            .then(res => res.json())
            .then(data => {
                tbody.innerHTML = '';
                if (!data.empresas || data.empresas.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4"><i class="fa-solid fa-circle-exclamation me-2"></i> Nenhuma empresa encontrada com os filtros aplicados.</td></tr>`;
                    atualizarContador();
                    return;
                }

                data.empresas.forEach(emp => {
                    const contatosHtml = emp.contatos.map(c => `
                        <div class="form-check">
                            <input class="form-check-input check-contato" type="checkbox" name="contatos[]" value="${c.id}" id="c-${c.id}">
                            <label class="form-check-label" for="c-${c.id}">
                                <strong>${c.nome}</strong> (${c.email})
                            </label>
                        </div>
                    `).join('');

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input check-empresa" data-empresa-id="${emp.id}">
                        </td>
                        <td>
                            <strong>${emp.razao_social}</strong>
                            ${emp.nome_fantasia ? `<br><small class="text-muted">${emp.nome_fantasia}</small>` : ''}
                        </td>
                        <td>${emp.cnpj || 'NP'}<br><small class="text-muted">${emp.cidade || ''} - ${emp.estado || ''}</small></td>
                        <td><span class="badge bg-secondary">${emp.regiao || 'Sem Região'}</span></td>
                        <td>${contatosHtml || '<span class="text-danger small"><i class="fa-solid fa-triangle-exclamation"></i> Sem contatos com e-mail</span>'}</td>
                    `;
                    tbody.appendChild(tr);
                });

                bindCheckboxes();
                atualizarContador();
            })
            .catch(err => {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4"><i class="fa-solid fa-xmark me-2"></i> Erro ao carregar empresas.</td></tr>`;
            });
    }

    function bindCheckboxes() {
        document.querySelectorAll('.check-empresa').forEach(chkEmp => {
            chkEmp.addEventListener('change', function () {
                const tr = this.closest('tr');
                tr.querySelectorAll('.check-contato').forEach(chk => {
                    chk.checked = this.checked;
                });
                atualizarContador();
            });
        });

        document.querySelectorAll('.check-contato').forEach(chk => {
            chk.addEventListener('change', actualizarStatusEmpresaParent);
        });
    }

    function actualizarStatusEmpresaParent() {
        document.querySelectorAll('tbody tr').forEach(tr => {
            const chkEmp = tr.querySelector('.check-empresa');
            const contatos = tr.querySelectorAll('.check-contato');
            if (contatos.length > 0) {
                const todosMarcados = Array.from(contatos).every(c => c.checked);
                if (chkEmp) chkEmp.checked = todosMarcados;
            }
        });
        atualizarContador();
    }

    function atualizarContador() {
        const selecionados = document.querySelectorAll('.check-contato:checked').length;
        badgeContador.textContent = `${selecionados} contatos selecionados`;
    }

    checkSelecionarTodos.addEventListener('change', function () {
        const isChecked = this.checked;
        document.querySelectorAll('.check-empresa').forEach(chk => chk.checked = isChecked);
        document.querySelectorAll('.check-contato').forEach(chk => chk.checked = isChecked);
        atualizarContador();
    });

    filtroRegiao.addEventListener('change', carregarEmpresas);
    filtroCategoria.addEventListener('change', carregarEmpresas);
    filtroAtivas.addEventListener('change', carregarEmpresas);
    filtroTermo.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(carregarEmpresas, 400);
    });

    carregarEmpresas();
});
</script>
@endpush
@endsection
