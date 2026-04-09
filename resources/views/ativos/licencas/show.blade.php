@extends('layouts.app')

@section('title', 'Detalhes da Licença: ' . $licenca->nome)

@section('content')
<div class="container-fluid py-4">
    <div class="row g-4">
        <!-- Coluna da Esquerda: Dados da Licença -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4 h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold text-primary mb-0"><i class="fa-solid fa-key me-2"></i>Detalhes da Licença</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('ativos.licencas.edit', $licenca->id) }}"><i class="fa-solid fa-edit me-2"></i>Editar</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 80px; height: 80px;">
                            <i class="fa-solid fa-certificate text-primary fs-1"></i>
                        </div>
                        <h4 class="fw-bold mb-1">{{ $licenca->nome }}</h4>
                        <span class="badge {{ $licenca->tipo_licenca == 'vitalicia' ? 'bg-info' : 'bg-secondary' }}">{{ strtoupper($licenca->tipo_licenca) }}</span>
                    </div>

                    <div class="small fw-bold text-muted text-uppercase mb-3 mt-4">Informações Gerais</div>
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Chave / Serial:</span>
                            <span class="fw-bold font-monospace text-dark">{{ $licenca->chave ?: 'Não informada' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Fabricante:</span>
                            <span class="fw-bold">{{ $licenca->fabricante->nome ?? 'N/D' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Categoria:</span>
                            <span class="fw-bold">{{ $licenca->categoria ?: 'Software Proprietário' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Modelo:</span>
                            <span class="fw-bold">{{ $licenca->modelo ?: 'N/D' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Vencimento:</span>
                            <span class="fw-bold {{ $licenca->data_validade && $licenca->data_validade < now() ? 'text-danger' : 'text-dark' }}">
                                {{ $licenca->data_validade ? $licenca->data_validade->format('d/m/Y') : 'Vitalícia (Sem Exp.)' }}
                            </span>
                        </li>
                    </ul>

                    <div class="small fw-bold text-muted text-uppercase mb-3 mt-4">Aquisição</div>
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Número da NF:</span>
                            <span class="fw-bold">{{ $licenca->numero_nf ?: 'Sem NF registrada' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Fornecedor:</span>
                            <span class="fw-bold text-end">{{ $licenca->fornecedor->nome ?? 'Sem fornecedor' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Data da Compra:</span>
                            <span class="fw-bold">{{ $licenca->data_aquisicao ? $licenca->data_aquisicao->format('d/m/Y') : 'Não informada' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Valor Registrado:</span>
                            <span class="fw-bold">R$ {{ number_format($licenca->valor_total ?: 0, 2, ',', '.') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Coluna Direita -->
        <div class="col-lg-8">
            <!-- Utilização da Licença -->
            <div class="card shadow-sm border-0 mb-4 font-inter">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold text-success mb-0"><i class="fa-solid fa-microchip me-2 text-success"></i>Dispositivos Utilizando (Seats)</h5>
                    <div class="text-muted small">
                        <span class="fw-bold">{{ $licenca->seats_em_uso }}</span> de <span class="fw-bold">{{ $licenca->quantidade_seats }}</span> seats ocupados
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="progress rounded-0" style="height: 4px;">
                        @php $percent = ($licenca->seats_em_uso / $licenca->quantidade_seats) * 100; @endphp
                        <div class="progress-bar bg-success" role="progressbar" style="width:{{ $percent }}%;"></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted small text-uppercase" style="font-size: 0.75rem;">
                                    <th class="ps-4">Equipamento</th>
                                    <th>Identificador</th>
                                    <th>Estação / Local</th>
                                    <th class="text-center">Quantidade Usada</th>
                                    <th class="text-end pe-4">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($licenca->equipamentos as $equip)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ $equip->nome }}</div>
                                        <div class="x-small text-muted">{{ $equip->fabricante->nome ?? 'S/ Fabricante' }}</div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ $equip->identificador }}</span></td>
                                    <td>{{ $equip->estacao->nome ?? 'Estoque / Sem Local' }}</td>
                                    <td class="text-center"><span class="badge bg-primary rounded-pill px-3">{{ $equip->pivot->quantidade }}</span></td>
                                    <td class="text-end pe-4">
                                        <form action="{{ route('ativos.licencas.desvincular', [$licenca->id, $equip->id]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-link text-danger p-0" onclick="return confirm('Deseja realmente desvincular esta licença do equipamento?')">
                                                <i class="fa-solid fa-unlink"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-muted">Ainda não houve vinculação desta licença a nenhum equipamento.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Anexos e Documentação -->
            <div class="card shadow-sm border-0 mb-4 h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-file-pdf me-2 text-primary"></i>Documentos e Anexos</h5>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAnexo">
                        <i class="fa-solid fa-upload me-1"></i> Adicionar Anexo
                    </button>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        @php
                            $todosAnexos = $licenca->anexos->merge($anexosAquisicao);
                        @endphp
                        @forelse($todosAnexos as $anexo)
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border shadow-none hover-shadow transition">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-start gap-3">
                                        @php
                                            $icon = 'fa-file';
                                            $color = 'text-secondary';
                                            if (str_contains($anexo->mime_type, 'pdf')) { $icon = 'fa-file-pdf'; $color = 'text-danger'; }
                                            elseif (str_contains($anexo->mime_type, 'image')) { $icon = 'fa-file-image'; $color = 'text-success'; }
                                            elseif (str_contains($anexo->mime_type, 'sheet')) { $icon = 'fa-file-excel'; $color = 'text-success'; }
                                        @endphp
                                        <div class="fs-2 {{ $color }}"><i class="fa-solid {{ $icon }}"></i></div>
                                        <div class="min-w-0 flex-grow-1">
                                            <div class="text-truncate fw-bold small text-dark mb-0" title="{{ $anexo->nome_original }}">
                                                {{ $anexo->nome_original }}
                                            </div>
                                            <div class="x-small text-muted mb-2">
                                                {{ number_format($anexo->tamanho / 1024, 2) }} KB | {{ $anexo->created_at->format('d/m/Y') }}
                                            </div>
                                            <!-- Badge Origem -->
                                            @if($anexo->aquisicao_id)
                                                <span class="badge bg-light text-dark border x-small">Desta NF</span>
                                            @else
                                                <span class="badge bg-light text-primary border x-small">Manual</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                                        <a href="{{ route('ativos.anexos.download', $anexo->id) }}" class="btn btn-sm btn-link p-0 text-decoration-none fw-bold" target="_blank">
                                            <i class="fa-solid fa-download me-1"></i> Baixar
                                        </a>
                                        @if(!$anexo->aquisicao_id)
                                        <form action="{{ route('ativos.anexos.destroy', $anexo->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-link text-danger p-0" onclick="return confirm('Excluir este anexo permanently?')">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center py-5">
                            <i class="fa-solid fa-file-circle-minus fs-1 text-muted opacity-25 mb-3"></i>
                            <p class="text-muted">Nenhum documento anexado a esta licença.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload -->
<div class="modal fade" id="modalAnexo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('ativos.licencas.anexos.store', $licenca->id) }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Novo Anexo para Licença</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Selecione o arquivo (PDF, Imagem, Doc...)</label>
                    <input type="file" name="arquivo" class="form-control" required>
                    <div class="form-text small mt-2">Certifique-se de que o arquivo contém o comprovante ou documento relevante para este software.</div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-link link-secondary text-decoration-none fw-bold" data-bs-dismiss="modal">Fechar</button>
                <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Enviar Arquivo</button>
            </div>
        </form>
    </div>
</div>

<style>
    .hover-shadow:hover { box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    .transition { transition: all 0.2s ease-in-out; }
    .font-inter { font-family: 'Inter', sans-serif; }
    .x-small { font-size: 0.7rem; }
</style>
@endsection
