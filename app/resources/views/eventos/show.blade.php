@extends('layouts.app')

@section('title', 'Detalhes do Evento: ' . $evento->nome)

@section('content')
    <div class="row mb-4">
        <div class="col-md">
            <div class="info-box shadow-sm">
                <span class="info-box-icon text-bg-primary"><i class="fa-solid fa-calendar-day"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-uppercase small fw-bold">Data</span>
                    <span
                        class="info-box-number text-truncate">{{ $evento->data_inicio?->format('d/m/Y H:i') ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
        <div class="col-md">
            <div class="info-box shadow-sm">
                <span class="info-box-icon text-bg-info"><i class="fa-solid fa-money-bill-wave"></i></span>
                <div class="info-box-content">
                    <span class="info-box-number">
                        Inteira: R$ {{ number_format($evento->valor_inteira, 2, ',', '.') }}<br>
                        Meia: R$ {{ number_format($evento->valor_meia, 2, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md">
            <div class="info-box shadow-sm">
                <span class="info-box-icon text-bg-success"><i class="fa-solid fa-ticket"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-uppercase small fw-bold">Convites</span>
                    <span class="info-box-number">{{ $totalConvites }}</span>
                </div>
            </div>
        </div>
        <div class="col-md">
            <div class="info-box shadow-sm">
                <span class="info-box-icon text-bg-warning"><i class="fa-solid fa-user-group"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-uppercase small fw-bold">Convidados</span>
                    <span class="info-box-number">{{ $totalConvidados }}</span>
                </div>
            </div>
        </div>
        <div class="col-md">
            <div class="info-box shadow-sm">
                <span class="info-box-icon text-bg-danger"><i class="fa-solid fa-hand-holding-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text text-uppercase small fw-bold">Arrecadação</span>
                    <span class="info-box-number">R$ {{ number_format($totalArrecadado, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header border-0 bg-white d-flex align-items-center">
            <h3 class="card-title m-0"><i class="fa-solid fa-list me-2"></i> Lista de Convites</h3>
            <div class="card-tools ms-auto">
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#modalNovoConvite">
                    <i class="fa-solid fa-plus me-1"></i> Novo Convite
                </button>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-danger btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-file-pdf me-1"></i> Relatórios
                    </button>
                    <ul class="dropdown-menu shadow">
                        <li>
                            <a class="dropdown-item" href="{{ route('eventos.report', $evento) }}" target="_blank">
                                <i class="fa-solid fa-file-invoice-dollar me-2 text-danger"></i> Com Valores
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('eventos.report', [$evento, 'sem_valor' => 1]) }}"
                                target="_blank">
                                <i class="fa-solid fa-file-lines me-2 text-muted"></i> Sem Valores
                            </a>
                        </li>
                    </ul>
                </div>
                <a href="{{ route('eventos.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Voltar
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 25%">Nome Responsável</th>
                            <th style="width: 15%">Placa</th>
                            <th style="width: 20%">Empresa</th>
                            <th style="width: 15%">Valor Total</th>
                            <th style="width: 15%" class="text-center">Convidados</th>
                            <th style="width: 10%" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($evento->convites as $convite)
                            <tr>
                                <td><strong>{{ $convite->nome_responsavel }}</strong></td>
                                <td><span class="badge bg-light text-dark border">{{ $convite->placa ?? '-' }}</span></td>
                                <td>{{ $convite->empresa ?? '-' }}</td>
                                <td>R$ {{ number_format($convite->convidados->sum('valor'), 2, ',', '.') }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary rounded-pill px-3 py-2 cursor-pointer invite-popover"
                                        style="cursor: pointer;" data-bs-toggle="popover" data-bs-trigger="hover"
                                        data-bs-html="true" data-bs-title="Convidados"
                                        data-bs-content="<ul>@foreach($convite->convidados as $con)<li>{{ $con->nome }}</li>@endforeach</ul>"
                                        onclick="openGuestModal({{ $convite->id }}, '{{ $convite->nome_responsavel }}')">
                                        {{ $convite->convidados->count() }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary"
                                        onclick="openGuestModal({{ $convite->id }}, '{{ $convite->nome_responsavel }}')">
                                        <i class="fa-solid fa-users"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-info"
                                        onclick="editInvite({{ json_encode($convite) }})">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger"
                                        onclick="confirmDeleteInvite({{ $convite->id }}, '{{ $convite->nome_responsavel }}')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    <form id="delete-invite-{{ $convite->id }}"
                                        action="{{ route('convites.destroy', $convite) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-ticket-simple fa-2x mb-3 d-block opacity-25"></i>
                                    Nenhum convite cadastrado para este evento.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Novo Convite -->
    <div class="modal fade" id="modalNovoConvite" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('convites.store', $evento) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Novo Convite</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Responsável pelo Convite</label>
                            <input type="text" name="nome_responsavel" class="form-control" placeholder="Nome Completo"
                                required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Placa do Veículo</label>
                                <input type="text" name="placa" class="form-control" placeholder="ABC-1234">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Empresa</label>
                                <select name="empresa" class="form-select">
                                    <option value="">Nome da Empresa</option>
                                    @foreach($empresas as $emp)
                                        <option value="{{ $emp->nome_curto ?? $emp->razao_social }}">
                                            {{ $emp->nome_curto ?? $emp->razao_social }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo de Valor</label>
                            <select name="tipo" class="form-select">
                                <option value="inteira">Inteira (R$
                                    {{ number_format($evento->valor_inteira, 2, ',', '.') }})
                                </option>
                                <option value="meia">Meia (R$ {{ number_format($evento->valor_meia, 2, ',', '.') }})
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar Convite</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Convite -->
    <div class="modal fade" id="modalEditarConvite" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEditarConvite" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Convite</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Responsável pelo Convite</label>
                            <input type="text" name="nome_responsavel" id="editInviteResponsavel" class="form-control"
                                placeholder="Nome Completo" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Placa do Veículo</label>
                                <input type="text" name="placa" id="editInvitePlaca" class="form-control"
                                    placeholder="ABC-1234">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Empresa</label>
                                <select name="empresa" id="editInviteEmpresa" class="form-select">
                                    <option value="">Nome da Empresa</option>
                                    @foreach($empresas as $emp)
                                        <option value="{{ $emp->nome_curto ?? $emp->razao_social }}">
                                            {{ $emp->nome_curto ?? $emp->razao_social }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo de Valor</label>
                            <select name="tipo" id="editInviteTipo" class="form-select">
                                <option value="inteira">Inteira (R$
                                    {{ number_format($evento->valor_inteira, 2, ',', '.') }})
                                </option>
                                <option value="meia">Meia (R$ {{ number_format($evento->valor_meia, 2, ',', '.') }})
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Gerenciar Convidados -->
    <div class="modal fade" id="modalConvidados" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">Convidados de <span id="spanResponsavel"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="card mb-4 border shadow-none">
                        <div class="card-body bg-light-subtle">
                            <h6 class="mb-3 fw-bold">Adicionar Convidado</h6>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <input type="text" id="guestName" class="form-control form-control-sm"
                                        placeholder="Nome Completo">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" id="guestDocument" class="form-control form-control-sm"
                                        placeholder="CPF">
                                </div>
                                <div class="col-md-3">
                                    <select id="guestCompany" class="form-select form-select-sm">
                                        <option value="">Empresa</option>
                                        @foreach($empresas as $emp)
                                            <option value="{{ $emp->nome_curto ?? $emp->razao_social }}">
                                                {{ $emp->nome_curto ?? $emp->razao_social }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" id="guestValue" class="form-control form-control-sm"
                                        value="{{ $evento->valor_inteira }}" placeholder="Valor">
                                </div>
                                <div class="col-md-1">
                                    <button class="btn btn-primary btn-sm w-100" onclick="addGuest()"><i
                                            class="fa-solid fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>CPF</th>
                                    <th>Empresa</th>
                                    <th>Valor</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyConvidados">
                                <!-- JS will populate -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        onclick="window.location.reload()">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let currentInviteId = null;

            document.addEventListener('DOMContentLoaded', function () {
                const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
                popoverTriggerList.map(function (popoverTriggerEl) {
                    return new bootstrap.Popover(popoverTriggerEl)
                })
            });

            function openGuestModal(inviteId, responsavel) {
                currentInviteId = inviteId;
                document.getElementById('spanResponsavel').innerText = responsavel;
                document.getElementById('tbodyConvidados').innerHTML = '<tr><td colspan="5" class="text-center py-3"><i class="fa-solid fa-spinner fa-spin me-2"></i>Carregando...</td></tr>';
                loadGuests(inviteId);
                new bootstrap.Modal(document.getElementById('modalConvidados')).show();
            }

            function loadGuests(inviteId) {
                fetch(`/eventos/convidados/${inviteId}`)
                    .then(res => {
                        if (!res.ok) throw new Error('Erro ao carregar convidados');
                        return res.json();
                    })
                    .then(data => {
                        const tbody = document.getElementById('tbodyConvidados');
                        tbody.innerHTML = '';
                        data.forEach(g => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                                <td>${g.nome}</td>
                                                <td>${g.documento || '-'}</td>
                                                <td>${g.empresa || '-'}</td>
                                                <td>R$ ${parseFloat(g.valor).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</td>
                                                <td class="text-end">
                                                    <button class="btn btn-xs btn-outline-info btn-edit-guest">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-outline-danger" onclick="deleteGuest(${g.id})">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </td>
                                            `;

                            // Anexa evento de clique ao botão de editar de forma segura
                            row.querySelector('.btn-edit-guest').addEventListener('click', () => editGuest(g));

                            tbody.appendChild(row);
                        });
                    })
                    .catch(err => {
                        console.error(err);
                        document.getElementById('tbodyConvidados').innerHTML = '<tr><td colspan="5" class="text-center py-3 text-danger">Erro ao carregar lista.</td></tr>';
                    });
            }

            function editInvite(invite) {
                document.getElementById('editInviteResponsavel').value = invite.nome_responsavel;
                document.getElementById('editInvitePlaca').value = invite.placa;
                document.getElementById('editInviteEmpresa').value = invite.empresa;
                document.getElementById('editInviteTipo').value = invite.tipo;
                document.getElementById('formEditarConvite').action = `/convites/${invite.id}`;
                new bootstrap.Modal(document.getElementById('modalEditarConvite')).show();
            }

            function addGuest() {
                const name = document.getElementById('guestName').value;
                if (!name) return;

                fetch(`/convites/${currentInviteId}/convidados`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        nome: name,
                        documento: document.getElementById('guestDocument').value,
                        empresa: document.getElementById('guestCompany').value,
                        valor: document.getElementById('guestValue').value
                    })
                }).then(() => {
                    document.getElementById('guestName').value = '';
                    document.getElementById('guestDocument').value = '';
                    document.getElementById('guestCompany').value = '';
                    loadGuests(currentInviteId);
                });
            }

            function editGuest(guest) {
                Swal.fire({
                    title: 'Editar Convidado',
                    html: `
                                                <input id="swalName" class="swal2-input" placeholder="Nome" value="${guest.nome}">
                                                <input id="swalDoc" class="swal2-input" style="max-width: 90%; margin: 10px auto;" placeholder="CPF" value="${guest.documento || ''}">
                                                <select id="swalEmp" class="swal2-select" style="max-width: 90%; margin: 10px auto; display: flex;">
                                                    <option value="">Nenhuma / Outra</option>
                                                    @foreach($empresas as $emp)
                                                        <option value="{{ $emp->nome_curto ?? $emp->razao_social }}" ${guest.empresa === '{{ $emp->nome_curto ?? $emp->razao_social }}' ? 'selected' : ''}>{{ $emp->nome_curto ?? $emp->razao_social }}</option>
                                                    @endforeach
                                                </select>
                                                <input id="swalVal" type="number" step="0.01" class="swal2-input" style="max-width: 90%; margin: 10px auto;" placeholder="Valor" value="${guest.valor}">
                                            `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'Salvar',
                    preConfirm: () => {
                        return {
                            nome: document.getElementById('swalName').value,
                            documento: document.getElementById('swalDoc').value,
                            empresa: document.getElementById('swalEmp').value,
                            valor: document.getElementById('swalVal').value
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/convidados/${guest.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(result.value)
                        }).then(() => loadGuests(currentInviteId));
                    }
                });
            }

            function deleteGuest(guestId) {
                Swal.fire({
                    title: 'Tem certeza?',
                    text: "Deseja excluir este convidado?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/convidados/${guestId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        }).then(() => loadGuests(currentInviteId));
                    }
                });
            }

            function confirmDeleteInvite(inviteId, nome) {
                Swal.fire({
                    title: 'Atenção!',
                    text: `Deseja realmente excluir o convite de ${nome}? TODOS os convidados vinculados a este convite também serão excluídos.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, excluir tudo!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(`delete-invite-${inviteId}`).submit();
                    }
                });
            }
        </script>
    @endpush
@endsection