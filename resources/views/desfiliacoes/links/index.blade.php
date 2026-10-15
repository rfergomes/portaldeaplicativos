@extends('layouts.app')

@section('title', 'Gestão de Links Exclusivos de Desfiliação')

@section('content')
<div class="container-fluid">
    {{-- Cabeçalho da Página --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.desfiliacoes.index') }}">Desfiliações</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Links Exclusivos</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold text-dark mb-0">
                <i class="fa-solid fa-link text-primary me-2"></i> Links Exclusivos de Desfiliação
            </h2>
            <p class="text-muted small mb-0">
                Gere e compartilhe links temporários e de uso único para blindar a entidade contra desfiliações em massa.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.desfiliacoes.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-list-check me-1"></i> Fila de Triagem
            </a>
            <button type="button" class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalGerarLink">
                <i class="fa-solid fa-plus-circle me-1"></i> Novo Link Exclusivo
            </button>
        </div>
    </div>

    {{-- Alertas de Sessão --}}
    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    {{-- Filtros e Busca --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.desfiliacao.links.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <div class="btn-group w-100" role="group">
                        <a href="{{ route('admin.desfiliacao.links.index') }}" class="btn btn-sm {{ empty($status) ? 'btn-primary' : 'btn-outline-primary' }}">
                            Todos
                        </a>
                        <a href="{{ route('admin.desfiliacao.links.index', ['status' => 'disponivel']) }}" class="btn btn-sm {{ $status === 'disponivel' ? 'btn-success' : 'btn-outline-success' }}">
                            Disponíveis
                        </a>
                        <a href="{{ route('admin.desfiliacao.links.index', ['status' => 'utilizado']) }}" class="btn btn-sm {{ $status === 'utilizado' ? 'btn-info text-white' : 'btn-outline-info' }}">
                            Utilizados
                        </a>
                        <a href="{{ route('admin.desfiliacao.links.index', ['status' => 'expirado']) }}" class="btn btn-sm {{ $status === 'expirado' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                            Expirados
                        </a>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="busca" class="form-control" placeholder="Buscar por destinatário, telefone ou token..." value="{{ $busca ?? '' }}">
                    </div>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-dark w-100">Filtrar</button>
                    @if(!empty($status) || !empty($busca))
                        <a href="{{ route('admin.desfiliacao.links.index') }}" class="btn btn-sm btn-outline-secondary" title="Limpar Filtros">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tabela de Links --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">ID / Token</th>
                            <th>Destinatário</th>
                            <th>Gerado Por</th>
                            <th>Canal</th>
                            <th>Expiração / Validade</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Ações Rápidas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($links as $link)
                            <tr class="{{ session('ultimo_link_id') == $link->id ? 'table-warning' : '' }}">
                                <td class="ps-3">
                                    <span class="fw-bold text-dark">#{{ $link->id }}</span>
                                    <div class="small font-monospace text-muted" title="{{ $link->token }}">
                                        {{ substr($link->token, 0, 12) }}...{{ substr($link->token, -6) }}
                                    </div>
                                </td>
                                <td>
                                    @if($link->isCompartilhado())
                                        <div class="d-flex align-items-center gap-1 mb-1">
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                                <i class="fa-solid fa-users me-1"></i> Compartilhado
                                            </span>
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                {{ $link->usos_atuais }}/{{ $link->limite_usos }} usos
                                            </span>
                                        </div>
                                    @elseif($link->tipo_link === 'lote')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle mb-1">
                                            <i class="fa-solid fa-boxes-stacked me-1"></i> Lote
                                        </span>
                                    @endif

                                    @if($link->destinatario_nome)
                                        <div class="fw-semibold text-dark">{{ $link->destinatario_nome }}</div>
                                    @elseif(!$link->isCompartilhado())
                                        <span class="text-muted fst-italic">Não especificado (avulso)</span>
                                    @endif

                                    @if($link->destinatario_contato)
                                        <div class="small text-muted">
                                            <i class="fa-brands fa-whatsapp text-success me-1"></i> {{ $link->destinatario_contato }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark">{{ $link->user->name ?? 'Sistema' }}</div>
                                    <div class="text-muted small">{{ $link->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>
                                    @if($link->canal_envio === 'whatsapp')
                                        <span class="badge bg-success-subtle text-success"><i class="fa-brands fa-whatsapp"></i> WhatsApp</span>
                                    @elseif($link->canal_envio === 'kwik')
                                        <span class="badge bg-primary-subtle text-primary"><i class="fa-solid fa-robot"></i> KWIK</span>
                                    @elseif($link->canal_envio === 'email')
                                        <span class="badge bg-info-subtle text-info"><i class="fa-solid fa-envelope"></i> E-mail</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary"><i class="fa-solid fa-hand"></i> Manual</span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $link->expira_em->format('d/m/Y H:i') }}</div>
                                    @if($link->isValido())
                                        @php
                                            $horasRestantes = now()->diffInHours($link->expira_em, false);
                                        @endphp
                                        <div class="small {{ $horasRestantes <= 12 ? 'text-danger fw-bold' : 'text-success' }}">
                                            <i class="fa-solid fa-hourglass-start me-1"></i>
                                            Resta {{ now()->diffForHumans($link->expira_em, true) }}
                                        </div>
                                    @elseif($link->isExpirado())
                                        <div class="small text-muted">Expirou há {{ $link->expira_em->diffForHumans() }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($link->isValido())
                                        <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Disponível</span>
                                        @if($link->isCompartilhado())
                                            <div class="small text-success mt-1" style="font-size: 11px;">
                                                <i class="fa-solid fa-user-check me-1"></i> {{ $link->vagas_restantes }} {{ $link->vagas_restantes === 1 ? 'vaga' : 'vagas' }}
                                            </div>
                                        @endif
                                    @elseif($link->isEsgotado() || $link->utilizado)
                                        <span class="badge bg-info text-white"><i class="fa-solid fa-check-double me-1"></i> {{ $link->isCompartilhado() ? 'Cota Esgotada' : 'Utilizado' }}</span>
                                    @elseif($link->isCancelado())
                                        <span class="badge bg-danger"><i class="fa-solid fa-ban me-1"></i> Cancelado</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="fa-solid fa-clock-rotate-left me-1"></i> Expirado</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    @include('desfiliacoes.links.partials.acoes-compartilhamento', ['link' => $link])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-link-slash fa-3x mb-3 text-secondary opacity-50"></i>
                                    <p class="mb-2 fw-semibold">Nenhum link exclusivo encontrado com os filtros selecionados.</p>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalGerarLink">
                                        Gerar Primeiro Link Agora
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($links->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $links->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Modal de Criação --}}
@include('desfiliacoes.links.modal-gerar')

{{-- Script de Cópia Rápida --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const botoesCopiar = document.querySelectorAll('.btn-copiar-link');
    botoesCopiar.forEach(botao => {
        botao.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(() => {
                    notificarCopia(this);
                }).catch(err => {
                    fallbackCopiar(url, this);
                });
            } else {
                fallbackCopiar(url, this);
            }
        });
    });

    function fallbackCopiar(texto, elemento) {
        const inputTemporario = document.createElement('input');
        inputTemporario.value = texto;
        document.body.appendChild(inputTemporario);
        inputTemporario.select();
        document.execCommand('copy');
        document.body.removeChild(inputTemporario);
        notificarCopia(elemento);
    }

    function notificarCopia(elemento) {
        const textoOriginal = elemento.innerHTML;
        elemento.classList.remove('btn-outline-secondary');
        elemento.classList.add('btn-success');
        elemento.innerHTML = '<i class="fa-solid fa-check me-1"></i> Copiado!';
        setTimeout(() => {
            elemento.classList.remove('btn-success');
            elemento.classList.add('btn-outline-secondary');
            elemento.innerHTML = textoOriginal;
        }, 2000);
    }
});
</script>
@endpush
@endsection
