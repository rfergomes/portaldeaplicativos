@extends('layouts.app')

@section('title', 'Tokens AR-Online')

@section('content')
    <div class="container-fluid">
        <div class="row mb-1"></div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger rounded-3 shadow-sm">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Card Principal -->
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header border-0 py-3 d-flex align-items-center flex-wrap">
                <h3 class="card-title fw-bold m-0"><i class="fa-solid fa-key me-2 text-primary"></i>Gerenciar Tokens
                    AR-Online</h3>
                <div class="card-tools ms-auto">
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-bold"
                        data-bs-toggle="modal" data-bs-target="#modalNovoToken">
                        <i class="fa-solid fa-plus me-1"></i> Novo Token / Departamento
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4" style="width: 80px;">ID</th>
                                <th>Departamento</th>
                                <th>E-mail Associado</th>
                                <th>Token</th>
                                <th class="text-center">Usuários Vinculados</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tokens as $t)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge text-bg-light border shadow-sm px-2">#{{ $t->id }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $t->departamento }}</div>
                                    </td>
                                    <td>
                                        <div class="text-muted"><i class="fa-solid fa-at me-1"></i>{{ $t->email }}</div>
                                    </td>
                                    <td>
                                        <div
                                            class="font-monospace text-muted small bg-light px-2 py-1 rounded border d-inline-block">
                                            {{ Str::limit($t->token, 15, '***') }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge bg-{{ $t->users->count() > 0 ? 'info' : 'secondary' }} rounded-pill px-3">
                                            <i class="fa-solid fa-users me-1"></i>{{ $t->users->count() }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-light btn-sm border-0 rounded-circle shadow-sm me-1"
                                            onclick="editarToken({{ json_encode($t) }})" title="Editar Configurações">
                                            <i class="fa-solid fa-pen text-info"></i>
                                        </button>
                                        <button class="btn btn-light btn-sm border-0 rounded-circle shadow-sm"
                                            onclick="confirmDeleteToken({{ $t->id }}, {{ $t->users->count() }})"
                                            title="Excluir">
                                            <i class="fa-solid fa-trash text-danger"></i>
                                        </button>

                                        <form id="delete-token-{{ $t->id }}" action="{{ route('token-deptos.destroy', $t) }}"
                                            method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5 mt-4 mb-4">
                                        <div class="mb-2"><i class="fa-solid fa-key fa-3x opacity-25"></i></div>
                                        <h5>Nenhum token configurado.</h5>
                                        <p class="small">Cadastre um departamento e seu token da API para disponibilizar os
                                            envios de protocolos.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-muted small py-3 border-top">
                <i class="fa-solid fa-circle-info me-1"></i> Os tokens cadastrados aqui poderão ser vinculados a usuários
                específicos no formulário de edição de usuários.
            </div>
        </div>
    </div>

    <!-- Modal Novo / Editar Token -->
    <div class="modal fade" id="modalNovoToken" tabindex="-1" aria-labelledby="modalNovoTokenLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <form action="{{ route('token-deptos.store') }}" method="POST" id="formToken">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">

                    <div class="modal-header bg-primary text-white border-0">
                        <h5 class="modal-title fw-bold" id="modalNovoTokenLabel">
                            <i class="fa-solid fa-key me-2"></i><span id="modalConfigTitle">Cadastrar Novo Token</span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body bg-light">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Nome do Departamento / Setor
                                <span class="text-danger">*</span></label>
                            <input type="text" name="departamento" id="inputDepartamento"
                                class="form-control form-control-lg shadow-sm" placeholder="Ex: TI, RH, DIRETORIA" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">E-mail Associado na API <span
                                    class="text-danger">*</span></label>
                            <input type="email" name="email" id="inputEmail" class="form-control form-control-lg shadow-sm"
                                placeholder="Ex: depto@ccb.org.br" required>
                            <small class="text-muted"><i class="fa-solid fa-info-circle me-1 mt-1"></i>O e-mail que gerou
                                este token na plataforma AR-Online.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">Chave do Token (Hash) <span
                                    class="text-danger" id="reqToken">*</span></label>
                            <input type="text" name="token" id="inputToken"
                                class="form-control form-control-lg shadow-sm font-monospace"
                                placeholder="Cole o token aqui..." required>
                            <small class="text-muted" id="helpTokenText">O token será salvo de forma criptografada no banco
                                de dados.</small>
                        </div>
                    </div>

                    <div class="modal-footer border-0 bg-white">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
                            <i class="fa-solid fa-save me-1"></i> Salvar Configuração
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const modalEl = document.getElementById('modalNovoToken');
        const modal = new bootstrap.Modal(modalEl);
        const form = document.getElementById('formToken');

        // Ao fechar o modal, resetar o formulário para estado de "Criar"
        modalEl.addEventListener('hidden.bs.modal', function () {
            form.reset();
            form.action = "{{ route('token-deptos.store') }}";
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('modalConfigTitle').innerText = 'Cadastrar Novo Token';
            document.getElementById('reqToken').style.display = 'inline';
            document.getElementById('inputToken').required = true;
            document.getElementById('helpTokenText').innerText = 'O token será salvo de forma criptografada no banco de dados.';
        });

        function editarToken(tokenDepto) {
            form.action = `/token-deptos/${tokenDepto.id}`;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('modalConfigTitle').innerText = 'Editar Configuração #' + tokenDepto.id;

            document.getElementById('inputDepartamento').value = tokenDepto.departamento;
            document.getElementById('inputEmail').value = tokenDepto.email;

            // Na edição, o token não é obrigatório pra não sobrescrever à toa
            document.getElementById('reqToken').style.display = 'none';
            document.getElementById('inputToken').required = false;
            document.getElementById('inputToken').value = '';
            document.getElementById('helpTokenText').innerHTML = '<span class="text-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i>Deixe em branco para <b>NÃO</b> alterar a chave atual.</span>';

            modal.show();
        }

        function confirmDeleteToken(id, usersCount) {
            if (usersCount > 0) {
                Swal.fire({
                    title: 'Não é possível excluir!',
                    text: `Existem ${usersCount} usuários vinculados a este departamento/token. Por favor, reatribua os usuários antes de excluir.`,
                    icon: 'error',
                    confirmButtonColor: '#3085d6',
                });
                return;
            }

            Swal.fire({
                title: 'Você tem certeza?',
                text: "Esta ação excluirá o Token e não poderá ser desfeita!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-token-' + id).submit();
                }
            });
        }
    </script>
@endpush