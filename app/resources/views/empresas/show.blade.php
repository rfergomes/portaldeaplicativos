@extends('layouts.app')

@section('title', 'Detalhes da Empresa')

@section('content')
    <div class="container-fluid">
        <div class="row mb-4 align-items-center">
            <div class="col-sm-9">
                <h1 class="h4 mb-0 text-gray-800 fw-bold">{{ $empresa->razao_social }}</h1>
            </div>
            <div class="col-sm-3 text-end">
                <a href="{{ route('empresas.index') }}"
                    class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="fa-solid fa-arrow-left me-2"></i>Voltar
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Informações da Empresa -->
            <div class="col-xl-4 col-lg-5">
                <div class="card card-outline card-primary shadow-sm mb-4">
                    <div class="card-header border-0">
                        <h6 class="card-title fw-bold m-0"><i class="fa-solid fa-building me-2"></i>Informações Gerais</h6>
                    </div>
                    <div class="card-body pt-0">
                        <div class="mb-3">
                            <small class="text-uppercase text-muted fw-bold d-block small">CNPJ</small>
                            <span class="h6 fw-bold">{{ $empresa->cnpj }}</span>
                        </div>
                        <div class="mb-3">
                            <small class="text-uppercase text-muted fw-bold d-block small">Nome Fantasia</small>
                            <span>{{ $empresa->nome_fantasia ?? 'NÃO INFORMADO' }}</span>
                        </div>
                        <div class="mb-3">
                            <small class="text-uppercase text-muted fw-bold d-block small">Categoria</small>
                            <span
                                class="badge text-bg-primary rounded-pill shadow-sm px-2">{{ $empresa->categoria ?? 'GERAL' }}</span>
                        </div>
                        <div class="mb-3">
                            <small class="text-uppercase text-muted fw-bold d-block small">Região</small>
                            <span
                                class="badge text-bg-secondary rounded-pill shadow-sm px-2">{{ $empresa->regiao->nome ?? 'N/D' }}</span>
                        </div>
                        <hr class="text-muted opacity-25">
                        <div class="mb-3">
                            <i class="fa-solid fa-envelope me-2 text-muted"></i>{{ $empresa->email ?? 'Sem email' }}
                        </div>
                        <div class="mb-3">
                            <i class="fa-solid fa-phone me-2 text-muted"></i>{{ $empresa->telefone ?? 'Sem telefone' }}
                        </div>
                        <div class="mb-0">
                            <i
                                class="fa-solid fa-location-dot me-2 text-muted"></i>{{ $empresa->cidade ?? '-' }}/{{ $empresa->estado ?? '-' }}
                        </div>
                    </div>
                    <div class="card-footer bg-white text-muted small py-2 border-top border-0">
                        <i class="fa-solid fa-circle-info me-1"></i> Dados cadastrados no ERP.
                    </div>
                </div>
            </div>

            <!-- Lista de Contatos (Clientes) -->
            <div class="col-xl-8 col-lg-7">
                <div class="card card-outline card-primary shadow-sm mb-4">
                    <div class="card-header border-0 d-flex align-items-center">
                        <h6 class="card-title fw-bold m-0">Contatos / Clientes Vinculados</h6>
                        <div class="card-tools ms-auto">
                            <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal"
                                data-bs-target="#modalNovoContato">
                                <i class="fa-solid fa-user-plus me-1"></i> Adicionar Contato
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-4">Nome</th>
                                        <th>Tipo / Cargo</th>
                                        <th>Contato</th>
                                        <th class="text-end pe-4">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($empresa->clientes as $contato)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold text-dark">{{ $contato->nome }}</div>
                                                <small class="text-muted small">ID: {{ $contato->id }}</small>
                                            </td>
                                            <td>
                                                <span class="badge text-bg-secondary rounded-pill shadow-sm px-2">
                                                    {{ $contato->tipo->nome ?? 'CONTATO' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="small"><i
                                                        class="fa-solid fa-envelope me-1 text-muted"></i>{{ $contato->email ?? '-' }}
                                                </div>
                                                <div class="small"><i
                                                        class="fa-solid fa-phone me-1 text-muted"></i>{{ $contato->telefone ?? '-' }}
                                                </div>
                                            </td>
                                            <td class="text-end pe-4">
                                                <button class="btn btn-light btn-sm border-0 rounded-circle shadow-sm me-1"
                                                    onclick="editContato({{ json_encode($contato) }})" title="Editar">
                                                    <i class="fa-solid fa-pen text-info"></i>
                                                </button>
                                                <button class="btn btn-light btn-sm border-0 rounded-circle shadow-sm"
                                                    onclick="confirmDeleteContato({{ $contato->id }})" title="Excluir">
                                                    <i class="fa-solid fa-trash text-danger"></i>
                                                </button>
                                                <form id="delete-contato-{{ $contato->id }}"
                                                    action="{{ route('clientes.destroy', $contato) }}" method="POST"
                                                    style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-5 mt-4 mb-4">
                                                <div class="mb-2"><i class="fa-solid fa-users-slash fa-3x opacity-25"></i></div>
                                                Nenhum contato cadastrado.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-muted small py-3 border-top">
                        <i class="fa-solid fa-circle-info me-1"></i> Pessoas de contato vinculadas a esta empresa.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Novo Contato -->
    <div class="modal fade" id="modalNovoContato" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <form action="{{ route('clientes.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="empresa_id" value="{{ $empresa->id }}">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Novo Contato em {{ $empresa->razao_social }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo / Cargo</label>
                            <select name="tipo_cliente_id" class="form-select" required>
                                @foreach($tiposClientes as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome Completo</label>
                            <input type="text" name="nome" class="form-control" required placeholder="NOME DO CONTATO">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">CPF</label>
                                <input type="text" name="documento" class="form-control" placeholder="000.000.000-00">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Telefone</label>
                                <input type="text" name="telefone" class="form-control" placeholder="(00) 00000-0000">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="email@contato.com">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar Contato</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Contato -->
    <div class="modal fade" id="modalEditarContato" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <form id="formEditarContato" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">Editar Contato</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo / Cargo</label>
                            <select name="tipo_cliente_id" id="edit_tipo_cliente_id" class="form-select" required>
                                @foreach($tiposClientes as $tipo)
                                    <option value="{{ $tipo->id }}">{{ $tipo->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome Completo</label>
                            <input type="text" name="nome" id="edit_contato_nome" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">CPF</label>
                                <input type="text" name="documento" id="edit_contato_documento" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Telefone</label>
                                <input type="text" name="telefone" id="edit_contato_telefone" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" id="edit_contato_email" class="form-control">
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
            function editContato(contato) {
                document.getElementById('edit_tipo_cliente_id').value = contato.tipo_cliente_id;
                document.getElementById('edit_contato_nome').value = contato.nome;
                document.getElementById('edit_contato_documento').value = contato.documento || '';
                document.getElementById('edit_contato_telefone').value = contato.telefone || '';
                document.getElementById('edit_contato_email').value = contato.email || '';

                document.getElementById('formEditarContato').action = `/clientes/${contato.id}`;

                new bootstrap.Modal(document.getElementById('modalEditarContato')).show();
            }

            function confirmDeleteContato(id) {
                Swal.fire({
                    title: 'Excluir contato?',
                    text: "Esta ação não poderá ser desfeita!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-contato-' + id).submit();
                    }
                })
            }
        </script>
    @endpush
@endsection