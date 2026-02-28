@extends('layouts.app')

@section('title', 'Detalhes do Protocolo #' . $protocolo->id)

@section('content')
    <div class="container-fluid">
        <div class="row g-4">
            <!-- COLUNA ESQUERDA: Cabeçalho do Protocolo -->
            <div class="col-lg-4">
                <div class="card card-outline card-primary shadow-sm mb-4">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h6 class="card-title fw-bold m-0">
                            <i class="fa-solid fa-file-contract me-2"></i>Protocolo #{{ $protocolo->id }}
                        </h6>
                        <div class="card-tools d-flex gap-2">
                            <form action="{{ route('protocolos.syncStatus', $protocolo) }}" method="GET" class="m-0 p-0">
                                <button type="submit" class="btn btn-outline-primary btn-sm rounded-circle shadow-sm"
                                    style="width: 32px; height: 32px; padding: 0;" title="Atualizar Status">
                                    <i class="fa-solid fa-rotate"></i>
                                </button>
                            </form>
                            <a href="{{ route('protocolos.index') }}"
                                class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                                style="width: 32px; height: 32px; padding: 0;" title="Voltar para Listagem">
                                <i class="fa-solid fa-arrow-left"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($protocolo->tipo)
                            <div class="mb-3">
                                <small class="text-muted text-uppercase fw-bold d-block mb-1">Tipo</small>
                                <span class="badge text-bg-{{ $protocolo->tipo->cor }} rounded-pill shadow-sm px-3 py-2">
                                    <i class="{{ $protocolo->tipo->icone }} me-1"></i>{{ $protocolo->tipo->nome }}
                                </span>
                            </div>
                        @endif

                        @if($protocolo->referencia_documento)
                            <div class="mb-3">
                                <small class="text-muted text-uppercase fw-bold d-block mb-1">Referência</small>
                                <span class="fw-bold text-dark">{{ $protocolo->referencia_documento }}</span>
                            </div>
                        @endif

                        <div class="mb-3">
                            <small class="text-muted text-uppercase fw-bold d-block mb-1">Assunto</small>
                            <span class="fw-bold text-dark">{{ $protocolo->assunto }}</span>
                        </div>

                        @if($protocolo->empresa)
                            <div class="mb-3">
                                <small class="text-muted text-uppercase fw-bold d-block mb-1">Empresa</small>
                                <span>{{ $protocolo->empresa->razao_social }}</span>
                            </div>
                        @endif

                        <div class="mb-3">
                            <small class="text-muted text-uppercase fw-bold d-block mb-1">Enviado Por</small>
                            <span>{{ $protocolo->usuario?->name ?? '—' }}</span>
                        </div>

                        <div class="mb-0">
                            <small class="text-muted text-uppercase fw-bold d-block mb-1">Status Geral</small>
                            @php
                                $cores = ['enviado' => 'primary', 'pendente' => 'warning', 'falha' => 'danger', 'concluido' => 'success'];
                                $cor = $cores[$protocolo->status] ?? 'secondary';
                            @endphp
                            <span
                                class="badge text-bg-{{ $cor }} rounded-pill px-3 py-2">{{ ucfirst($protocolo->status) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Corpo do Protocolo -->
                <div class="card card-outline card-secondary shadow-sm">
                    <div class="card-header border-0">
                        <h6 class="card-title fw-bold m-0"><i class="fa-solid fa-align-left me-2"></i>Corpo</h6>
                    </div>
                    <div class="card-body small text-muted" style="max-height: 300px; overflow-y: auto;">
                        {!! $protocolo->corpo !!}
                    </div>
                </div>
            </div>

            <!-- COLUNA DIREITA: Timeline por Destinatário -->
            <div class="col-lg-8">
                <div class="card card-outline card-info shadow-sm">
                    <div class="card-header border-0">
                        <h6 class="card-title fw-bold m-0">
                            <i class="fa-solid fa-timeline me-2"></i>Timeline de Envios por Destinatário
                        </h6>
                        <small class="text-muted">{{ $protocolo->destinatarios->count() }} destinatário(s)</small>
                    </div>
                    <div class="card-body">
                        @forelse($protocolo->destinatarios as $destinatario)
                            <div class="mb-4">
                                <!-- Cabeçalho do destinatário -->
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="avatar bg-info text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                        style="width:38px;height:38px;font-size:1rem;">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $destinatario->nome }}</div>
                                        <small class="text-muted">
                                            <i class="fa-solid fa-envelope me-1"></i>{{ $destinatario->email }}
                                            @if($destinatario->isCelularValido())
                                                &nbsp; <i
                                                    class="fa-brands fa-whatsapp text-success me-1"></i>{{ $destinatario->telefone }}
                                            @endif
                                        </small>
                                    </div>
                                </div>

                                <!-- Timeline vertical de envios deste destinatário -->
                                @if($destinatario->envios->isEmpty())
                                    <div class="text-muted small ps-5">
                                        <i class="fa-solid fa-clock me-1"></i> Nenhum envio registrado.
                                    </div>
                                @else
                                    <div class="position-relative ps-4" style="border-left: 2px solid #dee2e6; margin-left: 19px;">
                                        @foreach($destinatario->envios as $envio)
                                                            <div class="position-relative mb-3" style="padding-left: 1.5rem;">
                                                                <!-- Bolinha na linha do tempo -->
                                                                <div class="position-absolute start-0 translate-middle-x" style="width:14px;height:14px;border-radius:50%;top:4px;left:-1px;
                                                                                                                                                                                                                                                                                                                       background:{{ match ($envio->status) {
                                                'enviado' => '#0d6efd',
                                                'entregue' => '#0dcaf0',
                                                'lido' => '#198754',
                                                'falha' => '#dc3545',
                                                default => '#6c757d'
                                            } }}; border: 2px solid white; box-shadow:0 0 0 2px {{ match ($envio->status) {
                                                'enviado' => '#0d6efd',
                                                'entregue' => '#0dcaf0',
                                                'lido' => '#198754',
                                                'falha' => '#dc3545',
                                                default => '#6c757d'
                                            } }};">
                                                                </div>

                                                                <!-- Conteúdo do evento -->
                                                                <div class="card border-0 shadow-sm position-relative" style="background:#f8f9fa;">
                                                                    <div class="card-body py-2 px-3">
                                                                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-1">
                                                                            <div>
                                                                                <span class="badge text-bg-{{ $envio->statusCor() }} shadow-sm me-2"
                                                                                    style="font-size: 0.70rem; vertical-align: middle;">
                                                                                    {{ $envio->statusLabel() }}
                                                                                </span>
                                                                                <span class="small text-muted" style="vertical-align: middle;">
                                                                                    <i
                                                                                        class="fa-solid fa-{{ $envio->canal === 'email' ? 'envelope' : 'whatsapp' }} me-1"></i>
                                                                                    {{ strtoupper($envio->canal) }}
                                                                                </span>
                                                                            </div>
                                                                            <small class="text-muted mr-5 pe-5">
                                                                                {{ $envio->enviado_em?->format('d/m/Y H:i') ?? $envio->created_at?->format('d/m/Y H:i') }}
                                                                            </small>
                                                                        </div>

                                                                        @if($envio->id_email_externo)
                                                                            <div class="mt-1">
                                                                                <small class="text-muted font-monospace">
                                                                                    ID: {{ $envio->id_email_externo }}
                                                                                </small>
                                                                            </div>
                                                                        @endif

                                                                        <!-- Datas de progresso -->
                                                                        <div class="d-flex gap-3 mt-2 flex-wrap">
                                                                            @if($envio->enviado_em)
                                                                                <small class="text-muted">
                                                                                    <i class="fa-solid fa-paper-plane text-primary me-1"></i>
                                                                                    Enviado: {{ $envio->enviado_em->format('d/m H:i') }}
                                                                                </small>
                                                                            @endif
                                                                            @if($envio->entregue_em)
                                                                                <small class="text-muted">
                                                                                    <i class="fa-solid fa-inbox text-info me-1"></i>
                                                                                    Entregue: {{ $envio->entregue_em->format('d/m H:i') }}
                                                                                </small>
                                                                            @endif
                                                                            @if($envio->lido_em)
                                                                                <small class="text-muted">
                                                                                    <i class="fa-solid fa-eye text-success me-1"></i>
                                                                                    Lido: {{ $envio->lido_em->format('d/m H:i') }}
                                                                                </small>
                                                                            @endif
                                                                        </div>

                                                                        <!-- Botões AR-Online -->
                                                                        @if($envio->id_email_externo && !in_array($envio->status, ['falha', 'queued']))
                                                                            <div class="d-flex gap-2 mt-2">
                                                                                <a href="{{ route('protocolos.comprovante', [$protocolo, $envio]) }}"
                                                                                    class="btn btn-light btn-sm border rounded-pill px-2 shadow-sm"
                                                                                    title="Baixar Comprovante PDF">
                                                                                    <i class="fa-solid fa-file-pdf text-danger me-1"></i>
                                                                                    <span class="small">Comprovante</span>
                                                                                </a>
                                                                                <a href="https://portal.ar-online.com.br/emails/info/public/{{ $envio->id_email_externo }}"
                                                                                    target="_blank"
                                                                                    class="btn btn-light btn-sm border rounded-pill px-2 shadow-sm"
                                                                                    title="Visualizar Comprovante Público">
                                                                                    <i class="fa-solid fa-arrow-up-right-from-square text-primary me-1"></i>
                                                                                    <span class="small">Abrir no AR-Online</span>
                                                                                </a>
                                                                                @if($envio->status === 'lido' || $envio->status === 'entregue')
                                                                                    <a href="{{ route('protocolos.laudo', [$protocolo, $envio]) }}"
                                                                                        class="btn btn-light btn-sm border rounded-pill px-2 shadow-sm"
                                                                                        title="Baixar Laudo Pericial PDF">
                                                                                        <i class="fa-solid fa-scale-balanced text-warning me-1"></i>
                                                                                        <span class="small">Laudo Pericial</span>
                                                                                    </a>
                                                                                @endif
                                                                            </div>
                                                                        @endif

                                                                        @if($envio->status === 'falha' && $envio->ultima_resposta)
                                                                            <div class="mt-1">
                                                                                <small class="text-danger">
                                                                                    <i class="fa-solid fa-circle-exclamation me-1"></i>
                                                                                    {{ Str::limit($envio->ultima_resposta, 120) }}
                                                                                </small>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            @if(!$loop->last)
                                <hr class="my-3">
                            @endif
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="fa-solid fa-users-slash fa-2x opacity-25 mb-2"></i>
                                <p>Nenhum destinatário registrado.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection