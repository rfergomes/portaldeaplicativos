@extends('layouts.app')

@section('title', 'Gerenciar Regiões')

@section('content')
    <div class="container-fluid">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header border-0 d-flex align-items-center flex-wrap py-3">
                <h3 class="card-title fw-bold m-0">Regiões Geográficas</h3>
                <div class="card-tools ms-auto">
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal"
                        data-bs-target="#modalNovaRegiao">
                        <i class="fa-solid fa-plus me-1"></i> Nova Região
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4" style="width: 80px;">ID</th>
                                <th>Nome da Região</th>
                                <th>Área Adm (ERP)</th>
                                <th>Empresas</th>
                                <th class="text-end pe-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($regioes as $regiao)
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge text-bg-light border shadow-sm px-2">#{{ $regiao->id }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $regiao->nome }}</div>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-secondary rounded-pill shadow-sm px-2">
                                            {{ $regiao->area_adm ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-info text-white rounded-pill shadow-sm px-2">
                                            {{ $regiao->empresas_count ?? 0 }} Empresas
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-light btn-sm border-0 rounded-circle shadow-sm me-1"
                                            onclick="editRegiao({{ json_encode($regiao) }})" data-bs-toggle="modal"
                                            data-bs-target="#modalEditarRegiao" title="Editar">
                                            <i class="fa-solid fa-pen text-info"></i>
                                        </button>
                                        <button class="btn btn-light btn-sm border-0 rounded-circle shadow-sm"
                                            onclick="confirmDeleteRegiao({{ $regiao->id }})" title="Excluir">
                                            <i class="fa-solid fa-trash text-danger"></i>
                                        </button>
                                        <form id="delete-form-{{ $regiao->id }}"
                                            action="{{ route('regioes.destroy', $regiao) }}" method="POST"
                                            style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5 mt-4 mb-4">
                                        <div class="mb-2"><i class="fa-solid fa-map-location-dot fa-3x opacity-25"></i></div>
                                        Nenhuma região cadastrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($regioes->hasPages())
                <div class="card-footer py-2 bg-white">
                    {{ $regioes->links() }}
                </div>
            @endif
            <div class="card-footer bg-white text-muted small py-3 border-top">
                <i class="fa-solid fa-circle-info me-1"></i> Segmentação geográfica compatível com o sistema legado ERP.
            </div>
        </div>
    </div>

    <!-- Modal Nova Região -->
    <div class="modal fade" id="modalNovaRegiao" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <form action="{{ route('regioes.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fa-solid fa-plus me-2"></i>Nova Região Geográfica</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">ID Legado (AREA_ADM)</label>
                            <input type="text" name="area_adm" class="form-control" placeholder="ID NO ERP">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome da Região</label>
                            <input type="text" name="nome" class="form-control" required placeholder="EX: CAMPINAS">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar Região</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Região -->
    <div class="modal fade" id="modalEditarRegiao" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <form id="formEditarRegiao" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fa-solid fa-pen-to-square me-2"></i>Editar Região</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">ID Legado (AREA_ADM)</label>
                            <input type="text" name="area_adm" id="edit_area_adm" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome da Região</label>
                            <input type="text" name="nome" id="edit_nome" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function editRegiao(regiao) {
                const form = document.getElementById('formEditarRegiao');
                form.action = `/regioes/${regiao.id}`;

                document.getElementById('edit_nome').value = regiao.nome;
                document.getElementById('edit_area_adm').value = regiao.area_adm || '';
            }

            function confirmDeleteRegiao(id) {
                Swal.fire({
                    title: 'Tem certeza?',
                    text: "Esta ação não poderá ser revertida!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-form-' + id).submit();
                    }
                });
            }
        </script>
    @endpush
@endsection