@extends('layouts.app')

@section('title', 'Emissão Rápida de Links de Desfiliação | Atendimento Sindical')

@section('content')
<div class="container py-3" style="max-width: 800px;">
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
                Gere um link nominal de uso único para envio imediato pelo WhatsApp ao trabalhador que solicitou atendimento.
            </p>
        </div>
    </div>

    {{-- Feedback de Sucesso com Disparo do WhatsApp --}}
    @if(session('status'))
        <div class="alert alert-success shadow-sm d-flex flex-column gap-2 mb-4">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-circle-check fs-4 me-2"></i>
                <div>
                    <strong>Link Exclusivo Gerado com Sucesso!</strong>
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

    {{-- Card de Emissão Imediata --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-user-plus text-primary me-2"></i> Novo Atendimento ao Trabalhador
            </h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('representante.desfiliacao.links.store') }}" method="POST" id="formLinkRapido">
                @csrf
                <div class="mb-3">
                    <label for="destinatario_nome" class="form-label fw-bold small">Nome do Trabalhador <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg fs-6" id="destinatario_nome" name="destinatario_nome" required placeholder="Ex.: Marcos Aurélio de Souza" value="{{ old('destinatario_nome') }}">
                </div>

                <div class="mb-3">
                    <label for="destinatario_contato" class="form-label fw-bold small">WhatsApp / Celular <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light text-success"><i class="fa-brands fa-whatsapp"></i></span>
                        <input type="text" class="form-control fs-6" id="destinatario_contato" name="destinatario_contato" required placeholder="(19) 98765-4321" value="{{ old('destinatario_contato') }}">
                    </div>
                    <div class="form-text small">O sistema já formatará a mensagem pronta com o link de uso único.</div>
                </div>

                <div class="mb-4">
                    <label for="horas_validade" class="form-label fw-bold small">Tempo de Validade do Link</label>
                    <select class="form-select" id="horas_validade" name="horas_validade">
                        <option value="24">24 Horas (1 dia)</option>
                        <option value="48" selected>48 Horas (2 dias - Recomendado)</option>
                        <option value="72">72 Horas (3 dias)</option>
                    </select>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm py-3">
                        <i class="fa-solid fa-wand-magic-sparkles me-2"></i> Gerar Link e Preparar WhatsApp
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
                            <div class="fw-bold text-dark">{{ $link->destinatario_nome ?: 'Trabalhador sem nome' }}</div>
                            <div class="small text-muted">
                                <i class="fa-brands fa-whatsapp text-success me-1"></i> {{ $link->destinatario_contato ?: 'Sem telefone' }}
                                &bull; Emitido em {{ $link->created_at->format('d/m/Y H:i') }}
                            </div>
                            <div class="mt-1">
                                @if($link->isValido())
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="fa-solid fa-clock me-1"></i> Disponível (resta {{ now()->diffForHumans($link->expira_em, true) }})
                                    </span>
                                @elseif($link->utilizado)
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">
                                        <i class="fa-solid fa-check-double me-1"></i> Preenchido pelo Trabalhador
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
                                    <a href="{{ $link->whatsapp_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success">
                                        <i class="fa-brands fa-whatsapp me-1"></i> Reenviar
                                    </a>
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
    // Máscara de Telefone no form do representante
    const telInput = document.getElementById('destinatario_contato');
    if (telInput) {
        telInput.addEventListener('input', function (e) {
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
            }
        });
    });
});
</script>
@endpush
@endsection
