@extends('layouts.app')

@section('title', 'Tipos de Protocolo')

@section('content')
    <div class="container-fluid">
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header border-0 py-3 d-flex align-items-center flex-wrap">
                <h3 class="card-title fw-bold mb-0">Tipos de Protocolo</h3>
                <div class="card-tools ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal"
                        data-bs-target="#modalNovoTipo">
                        <i class="fa-solid fa-plus me-1"></i> Novo Tipo
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4" style="width: 80px;">ID</th>
                                <th>Nome</th>
                                <th>Ícone</th>
                                <th>Cor (Badge)</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tipos as $tipo)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge text-bg-light border shadow-sm px-2">#{{ $tipo->id }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><i
                                                class="{{ $tipo->icone }} text-{{ $tipo->cor }} me-2"></i>{{ $tipo->nome }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($tipo->icone)
                                            <i class="{{ $tipo->icone }} fs-5 me-2 text-{{ $tipo->cor }}"></i>
                                        @endif
                                        <code>{{ $tipo->icone }}</code>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $tipo->cor }}">{{ $tipo->cor }}</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-light btn-sm border-0 rounded-circle shadow-sm me-1"
                                            onclick="editTipo({{ json_encode($tipo) }})" title="Editar">
                                            <i class="fa-solid fa-pen text-info"></i>
                                        </button>
                                        <button class="btn btn-light btn-sm border-0 rounded-circle shadow-sm"
                                            onclick="confirmDeleteTipo({{ $tipo->id }})" title="Excluir">
                                            <i class="fa-solid fa-trash text-danger"></i>
                                        </button>
                                        <form id="delete-tipo-{{ $tipo->id }}"
                                            action="{{ route('protocolos.tipos.destroy', $tipo) }}" method="POST"
                                            style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5 mt-4 mb-4">
                                        <div class="mb-2"><i class="fa-solid fa-tags fa-3x opacity-25"></i></div>
                                        Nenhum tipo de protocolo encontrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-muted small py-3 border-top">
                <i class="fa-solid fa-circle-info me-1"></i> Gerencie os tipos de protocolos para classificar as
                comunicações.
            </div>
        </div>
    </div>

    <!-- Modal Novo Tipo -->
    <div class="modal fade" id="modalNovoTipo" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <form action="{{ route('protocolos.tipos.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fa-solid fa-tags me-2"></i>Cadastrar Tipo</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome</label>
                            <input type="text" name="nome" class="form-control" required placeholder="EX: OFÍCIO">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ícone (FontAwesome Class)</label>
                            <input type="text" name="icone" class="form-control" placeholder="EX: fa-solid fa-file">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Cor Tema (Bootstrap)</label>
                            <select name="cor" class="form-select">
                                <option value="primary">Primary (Azul)</option>
                                <option value="secondary">Secondary (Cinza)</option>
                                <option value="success">Success (Verde)</option>
                                <option value="danger">Danger (Vermelho)</option>
                                <option value="warning">Warning (Amarelo)</option>
                                <option value="info">Info (Azul Claro)</option>
                                <option value="dark">Dark (Escuro)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Tipo -->
    <div class="modal fade" id="modalEditarTipo" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <form id="formEditarTipo" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title"><i class="fa-solid fa-pen-to-square me-2"></i>Editar Tipo</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome</label>
                            <input type="text" name="nome" id="edit_nome" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ícone (FontAwesome Class)</label>
                            <input type="text" name="icone" id="edit_icone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Cor Tema (Bootstrap)</label>
                            <select name="cor" id="edit_cor" class="form-select">
                                <option value="primary">Primary (Azul)</option>
                                <option value="secondary">Secondary (Cinza)</option>
                                <option value="success">Success (Verde)</option>
                                <option value="danger">Danger (Vermelho)</option>
                                <option value="warning">Warning (Amarelo)</option>
                                <option value="info">Info (Azul Claro)</option>
                                <option value="dark">Dark (Escuro)</option>
                            </select>
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
            function editTipo(tipo) {
                document.getElementById('edit_nome').value = tipo.nome;
                document.getElementById('edit_icone').value = tipo.icone || '';
                document.getElementById('edit_cor').value = tipo.cor || 'primary';
                document.getElementById('formEditarTipo').action = `/protocolos/tipos/${tipo.id}`;
                new bootstrap.Modal(document.getElementById('modalEditarTipo')).show();
            }

            function confirmDeleteTipo(id) {
                Swal.fire({
                    title: 'Tem certeza?',
                    text: "Este tipo de protocolo será excluído. (Não poderá ser excluído se houver protocolos vinculados a ele)",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-tipo-' + id).submit();
                    }
                })
            }
        </script>
    @endpush
@endsection