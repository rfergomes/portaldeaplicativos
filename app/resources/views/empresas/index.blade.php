@extends('layouts.app')

@section('title', 'Empresas')

@section('content')
    <div class="container-fluid">
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header border-0 py-3 d-flex align-items-center flex-wrap">
                <h3 class="card-title fw-bold mb-0">Cadastro de Empresas</h3>
                <div class="card-tools ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal"
                        data-bs-target="#modalNovaEmpresa">
                        <i class="fa-solid fa-plus me-1"></i> Nova Empresa
                    </button>
                </div>
            </div>

            <div class="card-body border-top bg-light py-2">
                <form action="{{ route('empresas.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <select name="regiao_id" class="form-select form-select-sm rounded-pill px-3"
                            onchange="this.form.submit()">
                            <option value="">TODAS AS REGIÕES</option>
                            @foreach($regioes as $regiao)
                                <option value="{{ $regiao->id }}" {{ $regiao_id == $regiao->id ? 'selected' : '' }}>
                                    {{ $regiao->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control rounded-start-pill px-3"
                                placeholder="Buscar por razão, cnpj, erp..." value="{{ $search }}">
                            <button type="submit" class="btn btn-primary rounded-end-pill px-3">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2 text-end">
                        <a href="{{ route('empresas.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill w-100"
                            title="Limpar Filtros">
                            <i class="fa-solid fa-rotate-left me-1"></i> Limpar
                        </a>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4" style="width: 80px;">ID</th>
                                <th>ERP / CNPJ</th>
                                <th>Região</th>
                                <th>Razão Social / Nome Fantasia</th>
                                <th>Cidade/UF</th>
                                <th>Telefone/Email</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($empresas as $empresa)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge text-bg-light border shadow-sm px-2">#{{ $empresa->id }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $empresa->empresa_erp ?? '-' }}</div>
                                        <small class="text-muted">{{ $empresa->cnpj }}</small>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-secondary rounded-pill shadow-sm px-2">
                                            {{ $empresa->regiao->nome ?? 'N/D' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $empresa->razao_social }}</div>
                                        <small class="text-muted small">
                                            {{ $empresa->nome_curto ? $empresa->nome_curto . ' | ' : '' }}
                                            {{ $empresa->nome_fantasia ?? '-' }}
                                        </small>
                                    </td>
                                    <td>{{ $empresa->cidade ?? '-' }}/{{ $empresa->estado ?? '-' }}</td>
                                    <td>
                                        <div class="small"><i
                                                class="fa-solid fa-phone me-1 text-muted"></i>{{ $empresa->telefone ?? '-' }}
                                        </div>
                                        <div class="small"><i
                                                class="fa-solid fa-envelope me-1 text-muted"></i>{{ $empresa->email ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('empresas.show', $empresa) }}"
                                            class="btn btn-light btn-sm border-0 rounded-circle shadow-sm me-1"
                                            title="Ver Detalhes">
                                            <i class="fa-solid fa-eye text-primary"></i>
                                        </a>
                                        <button class="btn btn-light btn-sm border-0 rounded-circle shadow-sm me-1"
                                            onclick="editEmpresa({{ json_encode($empresa) }})" title="Editar">
                                            <i class="fa-solid fa-pen text-info"></i>
                                        </button>
                                        <button class="btn btn-light btn-sm border-0 rounded-circle shadow-sm"
                                            onclick="confirmDeleteEmpresa({{ $empresa->id }})" title="Excluir">
                                            <i class="fa-solid fa-trash text-danger"></i>
                                        </button>
                                        <form id="delete-empresa-{{ $empresa->id }}"
                                            action="{{ route('empresas.destroy', $empresa) }}" method="POST"
                                            style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5 mt-4 mb-4">
                                        <div class="mb-2"><i class="fa-solid fa-building fa-3x opacity-25"></i></div>
                                        Nenhuma empresa encontrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($empresas->hasPages())
                <div class="card-footer py-2 bg-white">
                    {{ $empresas->links() }}
                </div>
            @endif
            <div class="card-footer bg-white text-muted small py-3 border-top">
                <i class="fa-solid fa-circle-info me-1"></i> Gerencie o cadastro de empresas e seus contatos vinculados.
            </div>
        </div>
    </div>

    <!-- Modal Nova Empresa -->
    <div class="modal fade" id="modalNovaEmpresa" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow">
                <form action="{{ route('empresas.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fa-solid fa-building me-2"></i>Cadastrar Nova Empresa</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Região Geográfica</label>
                                <select name="regiao_id" class="form-select" required>
                                    <option value="">SELECIONE UMA REGIÃO</option>
                                    @foreach($regioes as $regiao)
                                        <option value="{{ $regiao->id }}">{{ $regiao->nome }} ({{ $regiao->area_adm }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">ID ERP (EMPRESA)</label>
                                <input type="text" name="empresa_erp" class="form-control" placeholder="ID NO ERP">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Razão Social</label>
                                <input type="text" name="razao_social" class="form-control" required
                                    placeholder="RAZÃO SOCIAL DA EMPRESA">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">CNPJ</label>
                                <input type="text" name="cnpj" class="form-control" required
                                    placeholder="00.000.000/0000-00">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nome Fantasia</label>
                                <input type="text" name="nome_fantasia" class="form-control"
                                    placeholder="NOME FANTASIA (OPCIONAL)">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nome Curto (Apelido)</label>
                                <input type="text" name="nome_curto" class="form-control"
                                    placeholder="NOME COMPACTO DE EXIBIÇÃO">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="contato@empresa.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Telefone</label>
                                <input type="text" name="telefone" class="form-control" placeholder="(00) 0000-0000">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Cidade</label>
                                <input type="text" name="cidade" class="form-control" placeholder="CIDADE">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label fw-bold">UF</label>
                                <input type="text" name="estado" class="form-control" maxlength="2" placeholder="UF">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Categoria</label>
                                <input type="text" name="categoria" class="form-control" placeholder="EX: TRANSPORTE">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar Empresa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Empresa -->
    <div class="modal fade" id="modalEditarEmpresa" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow">
                <form id="formEditarEmpresa" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title"><i class="fa-solid fa-pen-to-square me-2"></i>Editar Empresa</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Região Geográfica</label>
                                <select name="regiao_id" id="edit_regiao_id" class="form-select" required>
                                    @foreach($regioes as $regiao)
                                        <option value="{{ $regiao->id }}">{{ $regiao->nome }} ({{ $regiao->area_adm }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">ID ERP (EMPRESA)</label>
                                <input type="text" name="empresa_erp" id="edit_empresa_erp" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Razão Social</label>
                                <input type="text" name="razao_social" id="edit_razao_social" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">CNPJ</label>
                                <input type="text" name="cnpj" id="edit_cnpj" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nome Fantasia</label>
                                <input type="text" name="nome_fantasia" id="edit_nome_fantasia" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nome Curto (Apelido)</label>
                                <input type="text" name="nome_curto" id="edit_nome_curto" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" id="edit_email" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Telefone</label>
                                <input type="text" name="telefone" id="edit_telefone" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Cidade</label>
                                <input type="text" name="cidade" id="edit_cidade" class="form-control">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="form-label fw-bold">UF</label>
                                <input type="text" name="estado" id="edit_estado" class="form-control" maxlength="2">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Categoria</label>
                                <input type="text" name="categoria" id="edit_categoria" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-info text-white">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function editEmpresa(empresa) {
                document.getElementById('edit_regiao_id').value = empresa.regiao_id;
                document.getElementById('edit_empresa_erp').value = empresa.empresa_erp || '';
                document.getElementById('edit_razao_social').value = empresa.razao_social;
                document.getElementById('edit_nome_fantasia').value = empresa.nome_fantasia || '';
                document.getElementById('edit_nome_curto').value = empresa.nome_curto || '';
                document.getElementById('edit_cnpj').value = empresa.cnpj;
                document.getElementById('edit_email').value = empresa.email || '';
                document.getElementById('edit_telefone').value = empresa.telefone || '';
                document.getElementById('edit_cidade').value = empresa.cidade || '';
                document.getElementById('edit_estado').value = empresa.estado || '';
                document.getElementById('edit_categoria').value = empresa.categoria || '';

                document.getElementById('formEditarEmpresa').action = `/empresas/${empresa.id}`;

                new bootstrap.Modal(document.getElementById('modalEditarEmpresa')).show();
            }

            function confirmDeleteEmpresa(id) {
                Swal.fire({
                    title: 'Tem certeza?',
                    text: "Todos os contatos vinculados a esta empresa também serão afetados!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-empresa-' + id).submit();
                    }
                })
            }
        </script>
    @endpush
@endsection