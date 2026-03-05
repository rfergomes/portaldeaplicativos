@extends('layouts.app')

@section('title', 'Acomodações - ' . $colonia->nome)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-info shadow-sm mb-4">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center flex-grow-1">
                        <a href="{{ route('agenda.colonias.index') }}"
                            class="btn btn-outline-secondary btn-sm rounded-circle me-3" title="Voltar para Colônias">
                            <i class="fa-solid fa-arrow-left"></i>
                        </a>
                        <h3 class="card-title fw-bold m-0"><i class="fa-solid fa-bed me-2"></i>Acomodações:
                            {{ $colonia->nome }}
                        </h3>
                    </div>
                    <div class="card-tools">
                        <button class="btn btn-info text-white btn-sm rounded-pill shadow-sm" data-bs-toggle="modal"
                            data-bs-target="#modalNovaAcomodacao">
                            <i class="fa-solid fa-plus me-1"></i> Adicionar Unidade
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="100" class="text-center">ID</th>
                                    <th>Tipo (Bloco/Categoria)</th>
                                    <th>Identificador (Ex: Número)</th>
                                    <th class="text-center">Status</th>
                                    <th width="150" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($acomodacoes as $aco)
                                    <tr>
                                        <td class="text-center text-muted">#{{ $aco->id }}</td>
                                        <td>
                                            @if($aco->tipo)
                                                <span class="badge text-bg-secondary">{{ $aco->tipo }}</span>
                                            @else
                                                <span class="text-muted fst-italic">Padrão</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold fs-5">{{ $aco->identificador }}</td>
                                        <td class="text-center">
                                            @if($aco->ativo)
                                                <span class="badge text-bg-success rounded-pill px-3">Ativa</span>
                                            @else
                                                <span class="badge text-bg-secondary rounded-pill px-3">Inativa</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-secondary rounded-circle" title="Editar"
                                                onclick="editarAcomodacao({{ $aco->toJson() }})">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <form action="{{ route('agenda.acomodacoes.destroy', $aco->id) }}" method="POST"
                                                id="delete-form-{{ $aco->id }}" class="d-inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle"
                                                    title="Excluir"
                                                    onclick="confirmDelete('delete-form-{{ $aco->id }}', 'Tem certeza que deseja excluir esta acomodação?')">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fa-solid fa-bed fa-2x mb-3 opacity-25"></i>
                                            <p class="m-0">Nenhuma acomodação cadastrada para esta colônia.</p>
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

    <!-- Modal Nova/Editar Acomodação -->
    <div class="modal fade" id="modalNovaAcomodacao" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTitleAcomodacao"><i class="fa-solid fa-bed me-2"></i>Nova
                        Acomodação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('agenda.colonias.acomodacoes.store', $colonia->id) }}" method="POST"
                    id="formAcomodacao">
                    @csrf
                    <input type="hidden" name="_method" id="methodAcomodacao" value="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo / Categoria (Opcional)</label>
                            <input type="text" class="form-control" name="tipo" id="tipoAcomodacao"
                                placeholder="Ex: Chalé, Térreo, 1º Andar">
                            <small class="text-muted">Deixe em branco se a colônia possuir apenas um tipo padrão.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Identificador *</label>
                            <input type="text" class="form-control" name="identificador" id="idAcomodacao" required
                                placeholder="Ex: 1, 102, A, B">
                            <small class="text-muted">Como a unidade é chamada visualmente na planilha.</small>
                        </div>
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="ativo" id="ativoAcomodacao" value="1"
                                checked>
                            <label class="form-check-label" for="ativoAcomodacao">Acomodação Ativa</label>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info text-white"><i class="fa-solid fa-save me-1"></i>
                            Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function editarAcomodacao(aco) {
                document.getElementById('modalTitleAcomodacao').innerHTML = '<i class="fa-solid fa-pen me-2"></i>Editar Acomodação #' + aco.identificador;
                document.getElementById('formAcomodacao').action = '/agenda/colonias.acomodacoes/' + aco.id;
                document.getElementById('methodAcomodacao').value = 'PUT';

                document.getElementById('tipoAcomodacao').value = aco.tipo || '';
                document.getElementById('idAcomodacao').value = aco.identificador || '';
                document.getElementById('ativoAcomodacao').checked = aco.ativo == 1;

                var myModal = new bootstrap.Modal(document.getElementById('modalNovaAcomodacao'));
                myModal.show();
            }

            document.getElementById('modalNovaAcomodacao').addEventListener('hidden.bs.modal', function () {
                document.getElementById('modalTitleAcomodacao').innerHTML = '<i class="fa-solid fa-bed me-2"></i>Nova Acomodação';
                document.getElementById('formAcomodacao').action = '{{ route('agenda.colonias.acomodacoes.store', $colonia->id) }}';
                document.getElementById('methodAcomodacao').value = 'POST';
                document.getElementById('formAcomodacao').reset();
            });
        </script>
    @endpush
@endsection