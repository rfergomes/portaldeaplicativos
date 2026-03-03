@extends('layouts.app')

@section('title', 'Hóspedes / Ganhadores do Sorteio')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-success shadow-sm mb-4">
                <div class="card-header border-0 d-flex justify-content-between align-items-center">
                    <h3 class="card-title fw-bold m-0"><i class="fa-solid fa-users me-2"></i>Hóspedes Cadastrados</h3>
                    <div class="card-tools">
                        <button class="btn btn-success btn-sm rounded-pill shadow-sm" data-bs-toggle="modal"
                            data-bs-target="#modalNovoHospede">
                            <i class="fa-solid fa-plus me-1"></i> Cadastrar Hóspede
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="80" class="text-center">ID</th>
                                    <th>Nome do Hóspede</th>
                                    <th>Telefone/Celular</th>
                                    <th>Empresa Vinculada</th>
                                    <th class="text-center">Tipo</th>
                                    <th width="150" class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hospedes as $hospede)
                                    <tr>
                                        <td class="text-center text-muted">#{{ $hospede->id }}</td>
                                        <td class="fw-bold">{{ $hospede->nome }}</td>
                                        <td>
                                            @if($hospede->telefone)
                                                <a href="https://wa.me/55{{ preg_replace('/[^0-9]/', '', $hospede->telefone) }}"
                                                    target="_blank" class="text-decoration-none text-success">
                                                    <i class="fa-brands fa-whatsapp me-1"></i>{{ $hospede->telefone }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $hospede->empresa->razao_social ?? 'Sem Vínculo' }}</td>
                                        <td class="text-center">
                                            @if($hospede->associado)
                                                <span class="badge text-bg-primary rounded-pill px-3">Sócio</span>
                                            @else
                                                <span class="badge text-bg-secondary rounded-pill px-3">Não Sócio</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-secondary rounded-circle" title="Editar"
                                                onclick="editarHospede({{ collect($hospede)->toJson() }})">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <form action="{{ route('agenda.hospedes.destroy', $hospede->id) }}" method="POST"
                                                class="d-inline-block"
                                                onsubmit="return confirm('Tem certeza que deseja apagar este hóspede do histórico?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle"
                                                    title="Excluir">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fa-solid fa-users-slash fa-2x mb-3 opacity-25"></i>
                                            <p class="m-0">Nenhum hóspede cadastrado.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($hospedes->hasPages())
                    <div class="card-footer clearfix">
                        {{ $hospedes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Novo/Editar Hóspede -->
    <div class="modal fade" id="modalNovoHospede" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalTitleHospede"><i class="fa-solid fa-user me-2"></i>Novo Hóspede
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('agenda.hospedes.store') }}" method="POST" id="formHospede">
                    @csrf
                    <input type="hidden" name="_method" id="methodHospede" value="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nome Completo *</label>
                            <input type="text" class="form-control" name="nome" id="nomeHospede" required
                                placeholder="Ex: José da Silva">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Telefone / WhatsApp</label>
                            <input type="text" class="form-control w-50" name="telefone" id="telHospede"
                                placeholder="(00) 00000-0000">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Empresa</label>
                            <select name="empresa_id" id="empresaHospede" class="form-select">
                                <option value="">-- Selecione (Opcional) --</option>
                                @foreach($empresas as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->razao_social }} ({{ $emp->nome_fantasia }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" name="associado" id="associadoHospede" value="1"
                                checked>
                            <label class="form-check-label" for="associadoHospede">Sócio Associado</label>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="fa-solid fa-save me-1"></i> Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function editarHospede(hosp) {
                document.getElementById('modalTitleHospede').innerHTML = '<i class="fa-solid fa-pen me-2"></i>Editar Hóspede #' + hosp.id;
                document.getElementById('formHospede').action = '/agenda/hospedes/' + hosp.id;
                document.getElementById('methodHospede').value = 'PUT';

                document.getElementById('nomeHospede').value = hosp.nome;
                document.getElementById('telHospede').value = hosp.telefone || '';
                document.getElementById('associadoHospede').checked = hosp.associado == 1;

                // Select2 trick for Empresa
                $('#empresaHospede').val(hosp.empresa_id).trigger('change');

                var myModal = new bootstrap.Modal(document.getElementById('modalNovoHospede'));
                myModal.show();
            }

            document.getElementById('modalNovoHospede').addEventListener('hidden.bs.modal', function () {
                document.getElementById('modalTitleHospede').innerHTML = '<i class="fa-solid fa-user me-2"></i>Novo Hóspede';
                document.getElementById('formHospede').action = '{{ route('agenda.hospedes.store') }}';
                document.getElementById('methodHospede').value = 'POST';
                document.getElementById('formHospede').reset();
                $('#empresaHospede').val(null).trigger('change');
            });
        </script>
    @endpush
@endsection