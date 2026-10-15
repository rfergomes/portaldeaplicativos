@extends('layouts.app')

@section('title', 'Emissão Rápida de Links de Desfiliação | Atendimento Sindical')

@section('content')
<div class="container py-3" style="max-width: 860px;">
    {{-- Header da Área de Representantes --}}
    <div class="card shadow-sm border-0 mb-4 bg-primary text-white">
        <div class="card-body p-4">
            <div class="d-flex align-items-center mb-2">
                <div class="rounded-circle bg-white text-primary p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-bolt-lightning fa-xl"></i>
                </div>
                <div>
                    <h1 class="h4 fw-bold mb-0">Emissão Rápida de Link Exclusivo</h1>
                    <small class="text-white-50">Portal do Representante Sindical e Dirigente de Base</small>
                </div>
            </div>
            <p class="small mb-0 opacity-90">
                Gere links exclusivos nominais, links avulsos sem identificação, links compartilhados para múltiplas utilizações ou lotes de links individuais.
            </p>
        </div>
    </div>

    {{-- Feedback de Sucesso com Disparo do WhatsApp (Link Único) --}}
    @if(session('status') && !session('links_lote'))
        <div class="alert alert-success shadow-sm d-flex flex-column gap-2 mb-4">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-circle-check fs-4 me-2"></i>
                <div>
                    <strong>Link Gerado com Sucesso!</strong>
                    <div class="small">{{ session('status') }}</div>
                </div>
            </div>
            @if(session('whatsapp_url'))
                <div class="mt-2 pt-2 border-top border-success-subtle d-grid">
                    <a href="{{ session('whatsapp_url') }}" target="_blank" rel="noopener noreferrer" class="btn btn-success fw-bold py-2 shadow-sm">
                        <i class="fa-brands fa-whatsapp fa-lg me-2"></i> Abrir WhatsApp Agora e Enviar ao Trabalhador
                    </a>
                </div>
            @endif
        </div>
    @endif

    {{-- Feedback de Sucesso em Lote com Cópia em Bloco --}}
    @if(session('links_lote'))
        <div class="card border-success shadow-sm mb-4">
            <div class="card-header bg-success text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">
                    <i class="fa-solid fa-boxes-stacked me-2"></i> {{ count(session('links_lote')) }} Links Individuais Gerados em Lote
                </h5>
                <button type="button" class="btn btn-light btn-sm fw-bold text-success" id="btnCopiarTodosLote">
                    <i class="fa-solid fa-copy me-1"></i> Copiar Todos em Bloco
                </button>
            </div>
            <div class="card-body p-3">
                <p class="small text-muted mb-2">
                    Você pode copiar todos os links de uma vez ou copiar cada um individualmente para envio.
                </p>
                <div class="list-group list-group-flush border rounded">
                    @foreach(session('links_lote') as $index => $urlLink)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                            <div class="text-truncate me-2">
                                <span class="badge bg-secondary-subtle text-secondary me-2">#{{ $index + 1 }}</span>
                                <span class="font-monospace small text-dark">{{ $urlLink }}</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-copiar-rep" data-url="{{ $urlLink }}" title="Copiar este link">
                                <i class="fa-solid fa-copy"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
                {{-- Textarea oculta para cópia em bloco --}}
                <textarea id="textareaLoteOculto" class="d-none">{{ implode("\n", session('links_lote')) }}</textarea>
            </div>
        </div>
    @endif

    {{-- Card de Emissão Imediata --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-plus-circle text-primary me-2"></i> Gerar Novo(s) Link(s)
            </h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('representante.desfiliacao.links.store') }}" method="POST" id="formLinkRapido">
                @csrf

                {{-- Seletor de Modalidade de Emissão --}}
                <label class="form-label fw-bold small text-muted text-uppercase mb-2">Modalidade do Link</label>
                <div class="row g-2 mb-4">
                    <div class="col-sm-6 col-md-3">
                        <input type="radio" class="btn-check" name="tipo_modalidade" id="modalidade_nominal" value="individual_nominal" checked>
                        <label class="btn btn-outline-primary w-100 text-start p-3 h-100 d-flex flex-column justify-content-between shadow-xs" for="modalidade_nominal">
                            <div>
                                <i class="fa-solid fa-user-tag fs-5 mb-2 text-primary"></i>
                                <div class="fw-bold small">Nominal</div>
                                <div class="text-muted" style="font-size: 11px;">Com nome e WhatsApp (1 uso)</div>
                            </div>
                        </label>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <input type="radio" class="btn-check" name="tipo_modalidade" id="modalidade_anonimo" value="individual_anonimo">
                        <label class="btn btn-outline-primary w-100 text-start p-3 h-100 d-flex flex-column justify-content-between shadow-xs" for="modalidade_anonimo">
                            <div>
                                <i class="fa-solid fa-user-secret fs-5 mb-2 text-secondary"></i>
                                <div class="fw-bold small">Sem Identificação</div>
                                <div class="text-muted" style="font-size: 11px;">Link avulso aberto (1 uso)</div>
                            </div>
                        </label>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <input type="radio" class="btn-check" name="tipo_modalidade" id="modalidade_compartilhado" value="compartilhado">
                        <label class="btn btn-outline-primary w-100 text-start p-3 h-100 d-flex flex-column justify-content-between shadow-xs" for="modalidade_compartilhado">
                            <div>
                                <i class="fa-solid fa-users fs-5 mb-2 text-warning"></i>
                                <div class="fw-bold small">Compartilhado</div>
                                <div class="text-muted" style="font-size: 11px;">Mesma URL para N pessoas</div>
                            </div>
                        </label>
                    </div>

                    <div class="col-sm-6 col-md-3">
                        <input type="radio" class="btn-check" name="tipo_modalidade" id="modalidade_lote" value="lote">
                        <label class="btn btn-outline-primary w-100 text-start p-3 h-100 d-flex flex-column justify-content-between shadow-xs" for="modalidade_lote">
                            <div>
                                <i class="fa-solid fa-boxes-stacked fs-5 mb-2 text-info"></i>
                                <div class="fw-bold small">Em Lote</div>
                                <div class="text-muted" style="font-size: 11px;">Vários links de 1 uso</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Campo Oculto real enviado para backend --}}
                <input type="hidden" name="tipo_link" id="tipo_link" value="individual">

                {{-- Bloco de Dados do Destinatário (Exibido para Individual Nominal) --}}
                <div id="blocoDestinatario">
                    <div class="mb-3">
                        <label for="destinatario_nome" class="form-label fw-bold small">Nome do Trabalhador <span class="text-danger" id="spanObrigatorioNome">*</span></label>
                        <input type="text" class="form-control form-control-lg fs-6" id="destinatario_nome" name="destinatario_nome" placeholder="Ex.: Marcos Aurélio de Souza" value="{{ old('destinatario_nome') }}">
                    </div>

                    <div class="mb-3">
                        <label for="destinatario_contato" class="form-label fw-bold small">WhatsApp / Celular <span class="text-danger" id="spanObrigatorioTel">*</span></label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light text-success"><i class="fa-brands fa-whatsapp"></i></span>
                            <input type="text" class="form-control fs-6" id="destinatario_contato" name="destinatario_contato" placeholder="(19) 98765-4321" value="{{ old('destinatario_contato') }}">
                        </div>
                        <div class="form-text small" id="helpDestinatario">O sistema já formatará a mensagem pronta para envio no WhatsApp.</div>
                    </div>
                </div>

                {{-- Bloco de Cota de Utilizações (Exibido para Compartilhado) --}}
                <div class="mb-3 d-none" id="blocoLimiteUsos">
                    <label for="limite_usos" class="form-label fw-bold small">
                        <i class="fa-solid fa-users text-warning me-1"></i> Limite de Utilizações (Cota Máxima) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-calculator"></i></span>
                        <input type="number" class="form-control fs-6" id="limite_usos" name="limite_usos" min="2" max="50" value="5" placeholder="Ex.: 5">
                        <span class="input-group-text bg-light text-muted small">pessoas</span>
                    </div>
                    <div class="form-text small">Até quantas pessoas diferentes poderão preencher o termo utilizando esta mesma URL antes que o link expire automaticamente (2 a 50).</div>
                </div>

                {{-- Bloco de Quantidade de Links em Lote (Exibido para Lote) --}}
                <div class="mb-3 d-none" id="blocoQuantidadeLote">
                    <label for="quantidade" class="form-label fw-bold small">
                        <i class="fa-solid fa-boxes-stacked text-info me-1"></i> Quantidade de Links a Gerar <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-list-ol"></i></span>
                        <input type="number" class="form-control fs-6" id="quantidade" name="quantidade" min="2" max="50" value="5" placeholder="Ex.: 5">
                        <span class="input-group-text bg-light text-muted small">links</span>
                    </div>
                    <div class="form-text small">Serão gerados $X$ links individuais independentes de uso único (2 a 50).</div>
                </div>

                <div class="mb-4">
                    <label for="horas_validade" class="form-label fw-bold small">Tempo de Validade do(s) Link(s)</label>
                    <select class="form-select" id="horas_validade" name="horas_validade">
                        <option value="24">24 Horas (1 dia)</option>
                        <option value="48" selected>48 Horas (2 dias - Recomendado)</option>
                        <option value="72">72 Horas (3 dias)</option>
                        <option value="168">7 Dias (1 semana)</option>
                    </select>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm py-3" id="btnSubmitGerar">
                        <i class="fa-solid fa-wand-magic-sparkles me-2"></i> <span id="labelBtnSubmit">Gerar Link e Preparar WhatsApp</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Histórico de Links Emitidos pelo Representante --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-clock-rotate-left text-secondary me-2"></i> Meus Links Emitidos
            </h5>
            <span class="badge bg-secondary rounded-pill">{{ $links->total() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @forelse($links as $link)
                    <div class="list-group-item p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-dark">
                                    {{ $link->destinatario_nome ?: ($link->isCompartilhado() ? 'Link Compartilhado' : 'Link Sem Identificação') }}
                                </span>
                                @if($link->isCompartilhado())
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                        <i class="fa-solid fa-users me-1"></i> Cota: {{ $link->usos_atuais }}/{{ $link->limite_usos }}
                                    </span>
                                @elseif($link->tipo_link === 'lote')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">
                                        <i class="fa-solid fa-boxes-stacked me-1"></i> Lote
                                    </span>
                                @elseif($link->isAnonimo())
                                    <span class="badge bg-light text-secondary border">
                                        <i class="fa-solid fa-user-secret me-1"></i> Sem Identificação
                                    </span>
                                @endif
                            </div>

                            <div class="small text-muted mt-1">
                                @if($link->destinatario_contato)
                                    <i class="fa-brands fa-whatsapp text-success me-1"></i> {{ $link->destinatario_contato }} &bull;
                                @endif
                                Emitido em {{ $link->created_at->format('d/m/Y H:i') }}
                            </div>

                            <div class="mt-1">
                                @if($link->isValido())
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="fa-solid fa-clock me-1"></i> Disponível (resta {{ now()->diffForHumans($link->expira_em, true) }})
                                        @if($link->isCompartilhado())
                                            &bull; {{ $link->vagas_restantes }} {{ $link->vagas_restantes === 1 ? 'vaga restante' : 'vagas restantes' }}
                                        @endif
                                    </span>
                                @elseif($link->isEsgotado() || $link->utilizado)
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">
                                        <i class="fa-solid fa-check-double me-1"></i> {{ $link->isCompartilhado() ? 'Cota Esgotada (' . $link->limite_usos . ' usos)' : 'Preenchido pelo Trabalhador' }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                        <i class="fa-solid fa-hourglass-end me-1"></i> Expirado
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div>
                            @if($link->isValido())
                                <div class="btn-group btn-group-sm">
                                    @if($link->destinatario_contato)
                                        <a href="{{ $link->whatsapp_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success">
                                            <i class="fa-brands fa-whatsapp me-1"></i> Reenviar
                                        </a>
                                    @endif
                                    <button type="button" class="btn btn-outline-secondary btn-copiar-rep" data-url="{{ $link->url }}" title="Copiar link">
                                        <i class="fa-solid fa-copy"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-muted">
                        <i class="fa-solid fa-link-slash fa-2x mb-2 opacity-50"></i>
                        <p class="mb-0">Você ainda não emitiu nenhum link exclusivo.</p>
                    </div>
                @endforelse
            </div>
        </div>
        @if($links->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $links->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Alternância de modalidades
    const radiosModalidade = document.querySelectorAll('input[name="tipo_modalidade"]');
    const inputTipoLink = document.getElementById('tipo_link');
    const blocoDestinatario = document.getElementById('blocoDestinatario');
    const blocoLimiteUsos = document.getElementById('blocoLimiteUsos');
    const blocoQuantidadeLote = document.getElementById('blocoQuantidadeLote');
    const inputNome = document.getElementById('destinatario_nome');
    const inputTel = document.getElementById('destinatario_contato');
    const spanObrigatorioNome = document.getElementById('spanObrigatorioNome');
    const spanObrigatorioTel = document.getElementById('spanObrigatorioTel');
    const labelBtnSubmit = document.getElementById('labelBtnSubmit');

    function atualizarModalidade(valor) {
        if (valor === 'individual_nominal') {
            inputTipoLink.value = 'individual';
            blocoDestinatario.classList.remove('d-none');
            blocoLimiteUsos.classList.add('d-none');
            blocoQuantidadeLote.classList.add('d-none');
            inputNome.required = true;
            inputTel.required = true;
            spanObrigatorioNome.classList.remove('d-none');
            spanObrigatorioTel.classList.remove('d-none');
            labelBtnSubmit.textContent = 'Gerar Link e Preparar WhatsApp';
        } else if (valor === 'individual_anonimo') {
            inputTipoLink.value = 'individual';
            blocoDestinatario.classList.add('d-none');
            blocoLimiteUsos.classList.add('d-none');
            blocoQuantidadeLote.classList.add('d-none');
            inputNome.required = false;
            inputTel.required = false;
            inputNome.value = '';
            inputTel.value = '';
            labelBtnSubmit.textContent = 'Gerar Link Sem Identificação';
        } else if (valor === 'compartilhado') {
            inputTipoLink.value = 'compartilhado';
            blocoDestinatario.classList.add('d-none');
            blocoLimiteUsos.classList.remove('d-none');
            blocoQuantidadeLote.classList.add('d-none');
            inputNome.required = false;
            inputTel.required = false;
            inputNome.value = '';
            inputTel.value = '';
            labelBtnSubmit.textContent = 'Gerar Link Compartilhado';
        } else if (valor === 'lote') {
            inputTipoLink.value = 'lote';
            blocoDestinatario.classList.add('d-none');
            blocoLimiteUsos.classList.add('d-none');
            blocoQuantidadeLote.classList.remove('d-none');
            inputNome.required = false;
            inputTel.required = false;
            inputNome.value = '';
            inputTel.value = '';
            labelBtnSubmit.textContent = 'Gerar Lote de Links';
        }
    }

    radiosModalidade.forEach(radio => {
        radio.addEventListener('change', function () {
            atualizarModalidade(this.value);
        });
    });

    // Máscara de Telefone no form do representante
    if (inputTel) {
        inputTel.addEventListener('input', function (e) {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length > 11) v = v.slice(0, 11);
            if (v.length > 10) {
                v = v.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
            } else if (v.length > 5) {
                v = v.replace(/^(\d{2})(\d{4})(\d{0,4})$/, '($1) $2-$3');
            } else if (v.length > 2) {
                v = v.replace(/^(\d{2})(\d{0,5})$/, '($1) $2');
            }
            e.target.value = v;
        });
    }

    // Botões de cópia rápida
    document.querySelectorAll('.btn-copiar-rep').forEach(btn => {
        btn.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(() => {
                    this.innerHTML = '<i class="fa-solid fa-check text-success"></i>';
                    setTimeout(() => { this.innerHTML = '<i class="fa-solid fa-copy"></i>'; }, 2000);
                });
            } else {
                const ta = document.createElement('textarea');
                ta.value = url;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                this.innerHTML = '<i class="fa-solid fa-check text-success"></i>';
                setTimeout(() => { this.innerHTML = '<i class="fa-solid fa-copy"></i>'; }, 2000);
            }
        });
    });

    // Botão de copiar lote em bloco
    const btnCopiarLote = document.getElementById('btnCopiarTodosLote');
    const textareaLote = document.getElementById('textareaLoteOculto');
    if (btnCopiarLote && textareaLote) {
        btnCopiarLote.addEventListener('click', function () {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(textareaLote.value).then(() => {
                    btnCopiarLote.innerHTML = '<i class="fa-solid fa-check me-1"></i> Todos Copiados!';
                    setTimeout(() => {
                        btnCopiarLote.innerHTML = '<i class="fa-solid fa-copy me-1"></i> Copiar Todos em Bloco';
                    }, 2500);
                });
            } else {
                textareaLote.classList.remove('d-none');
                textareaLote.select();
                document.execCommand('copy');
                textareaLote.classList.add('d-none');
                btnCopiarLote.innerHTML = '<i class="fa-solid fa-check me-1"></i> Todos Copiados!';
                setTimeout(() => {
                    btnCopiarLote.innerHTML = '<i class="fa-solid fa-copy me-1"></i> Copiar Todos em Bloco';
                }, 2500);
            }
        });
    }
});
</script>
@endpush
@endsection
