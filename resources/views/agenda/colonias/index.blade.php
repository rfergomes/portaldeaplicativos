@extends('layouts.app')

@section('title', 'Gestão de Colônias de Férias')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title fw-bold m-0"><i class="fa-solid fa-umbrella-beach me-2"></i>Colônias Cadastradas
                    </h3>
                    <div class="ms-auto">
                        <button class="btn btn-primary btn-sm rounded-pill shadow-sm" data-bs-toggle="modal"
                            data-bs-target="#modalNovaColonia">
                            <i class="fa-solid fa-plus me-1"></i> Nova Colônia
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0 premium-table">
                            <thead>
                                <tr>
                                    <th width="50" class="text-center">ID</th>
                                    <th>Nome da Colônia</th>
                                    <th>Descrição/Local</th>
                                    <th class="text-center">Acomodações</th>
                                    <th class="text-center">Capacidade Base</th>
                                    <th class="text-center">Status</th>
                                    <th width="150" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($colonias as $col)
                                    <tr>
                                        <td class="text-center text-muted">#{{ $col->id }}</td>
                                        <td class="fw-bold">{{ $col->nome }}</td>
                                        <td>{{ $col->descricao ?? '-' }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('agenda.colonias.acomodacoes.index', $col->id) }}"
                                                class="badge text-bg-info rounded-pill text-decoration-none">
                                                <i class="fa-solid fa-bed me-1"></i> {{ $col->acomodacoes_count }} Unidades
                                            </a>
                                        </td>
                                        <td class="text-center">{{ $col->capacidade_total }} Pessoas</td>
                                        <td class="text-center">
                                            @if($col->ativo)
                                                <span class="badge text-bg-success rounded-pill px-3">Ativo</span>
                                            @else
                                                <span class="badge text-bg-secondary rounded-pill px-3">Inativo</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-secondary rounded-circle" title="Editar"
                                                onclick="editarColonia({{ $col->toJson() }})">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <form action="{{ route('agenda.colonias.destroy', $col->id) }}" method="POST"
                                                id="delete-form-{{ $col->id }}" class="d-inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle"
                                                    title="Excluir"
                                                    onclick="confirmDelete('delete-form-{{ $col->id }}', 'Tem certeza que deseja excluir esta colônia? Todos os dados vinculados podem ser afetados.')">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fa-solid fa-folder-open fa-2x mb-3 opacity-25"></i>
                                            <p class="m-0">Nenhuma colônia cadastrada no momento.</p>
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

    <!-- Modal Nova/Editar Colônia -->
    <div class="modal fade" id="modalNovaColonia" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTitleColonia"><i
                            class="fa-solid fa-umbrella-beach me-2"></i>Nova Colônia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('agenda.colonias.store') }}" method="POST" id="formColonia">
                    @csrf
                    <input type="hidden" name="_method" id="methodColonia" value="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome da Colônia *</label>
                            <input type="text" class="form-control" name="nome" id="nomeColonia" required
                                placeholder="Ex: Colônia Praia Grande, Colônia 1 Caraguá">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descrição ou Localização</label>
                            <input type="text" class="form-control" name="descricao" id="descColonia"
                                placeholder="Detalhes opcionais...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Capacidade Aproximada de Pessoas *</label>
                            <input type="number" class="form-control w-50" name="capacidade_total" id="capColonia" required
                                min="0" value="0">
                            <small class="text-muted d-block mt-1">Este número é apenas informativo para controle
                                geral.</small>
                        </div>
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="ativo" id="ativoColonia" value="1"
                                checked>
                            <label class="form-check-label" for="ativoColonia">Colônia Ativa</label>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function editarColonia(colonia) {
                document.getElementById('modalTitleColonia').innerHTML = '<i class="fa-solid fa-pen me-2"></i>Editar Colônia #' + colonia.id;
                document.getElementById('formColonia').action = '/agenda/colonias/' + colonia.id;
                document.getElementById('methodColonia').value = 'PUT';

                document.getElementById('nomeColonia').value = colonia.nome;
                document.getElementById('descColonia').value = colonia.descricao || '';
                document.getElementById('capColonia').value = colonia.capacidade_total;
                document.getElementById('ativoColonia').checked = colonia.ativo == 1;

                var myModal = new bootstrap.Modal(document.getElementById('modalNovaColonia'));
                myModal.show();
            }

            // Reset form on modal close
            document.getElementById('modalNovaColonia').addEventListener('hidden.bs.modal', function () {
                document.getElementById('modalTitleColonia').innerHTML = '<i class="fa-solid fa-umbrella-beach me-2"></i>Nova Colônia';
                document.getElementById('formColonia').action = '{{ route('agenda.colonias.store') }}';
                document.getElementById('methodColonia').value = 'POST';
                document.getElementById('formColonia').reset();
            });
        </script>
    @endpush
@endsection