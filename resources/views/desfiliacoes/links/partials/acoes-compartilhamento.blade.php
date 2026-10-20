@if($link->isValido())
    <div class="btn-group btn-group-sm" role="group">
        {{-- Botão Copiar Link --}}
        <button type="button" 
                class="btn btn-outline-secondary btn-copiar-link" 
                data-url="{{ $link->url }}"
                title="Copiar link exclusivo para área de transferência">
            <i class="fa-solid fa-copy me-1"></i> Copiar
        </button>

        {{-- Botão WhatsApp Web (wa.me) --}}
        <a href="{{ $link->whatsapp_url }}" 
           target="_blank" 
           rel="noopener noreferrer" 
           class="btn btn-outline-success"
           title="Compartilhar via WhatsApp Web ou Aplicativo">
            <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp
        </a>

        @if($link->destinatario_contato)
            {{-- Botão Disparar via KWIK Oficial com Template --}}
            <button type="button" 
                    class="btn btn-outline-primary" 
                    data-bs-toggle="modal" 
                    data-bs-target="#modalEnviarKwik{{ $link->id }}"
                    title="Disparar via API Oficial KWIK por template">
                <i class="fa-solid fa-robot me-1"></i> KWIK
            </button>
        @endif

        {{-- Botão Cancelar --}}
        <form action="{{ route('admin.desfiliacao.links.cancelar', $link->id) }}" 
              method="POST" 
              class="d-inline"
              onsubmit="return confirm('Deseja realmente cancelar este link exclusivo? Ele não poderá mais ser utilizado pelo trabalhador.');">
            @csrf
            <button type="submit" class="btn btn-outline-danger" title="Revogar / Cancelar Link">
                <i class="fa-solid fa-ban"></i>
            </button>
        </form>
    </div>

    @if($link->destinatario_contato)
        {{-- Modal de Disparo Rápido via API KWIK com Template --}}
        <div class="modal fade" id="modalEnviarKwik{{ $link->id }}" tabindex="-1" aria-labelledby="modalEnviarKwikLabel{{ $link->id }}" aria-hidden="true" style="text-align: left;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form action="{{ route('admin.desfiliacao.links.enviar_kwik', $link->id) }}" method="POST">
                        @csrf
                        <div class="modal-header bg-primary text-white py-2">
                            <h6 class="modal-title fw-bold" id="modalEnviarKwikLabel{{ $link->id }}">
                                <i class="fa-solid fa-robot me-1"></i> Disparo Oficial KWIK (WhatsApp)
                            </h6>
                            <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body p-3">
                            <p class="small text-muted mb-2">
                                Confirme o template e os dados para disparo oficial automatizado via API KWIK:
                            </p>
                            <div class="mb-2">
                                <label class="form-label small fw-semibold mb-1">Destinatário</label>
                                <input type="text" class="form-control form-control-sm bg-light" value="{{ $link->destinatario_nome ?: 'Trabalhador(a)' }} ({{ $link->destinatario_contato }})" readonly>
                            </div>
                            <div class="mb-2">
                                <label for="template_nome_{{ $link->id }}" class="form-label small fw-semibold mb-1">Template Homologado KWIK</label>
                                <select class="form-select form-select-sm" id="template_nome_{{ $link->id }}" name="template_nome">
                                    <option value="desfiliacao_link_exclusivo" selected>Padrão (desfiliacao_link_exclusivo)</option>
                                    @if(isset($templates) && $templates->count() > 0)
                                        @foreach($templates as $tmpl)
                                            @if($tmpl->nome !== 'desfiliacao_link_exclusivo')
                                                <option value="{{ $tmpl->nome }}">{{ $tmpl->nome }} ({{ $tmpl->categoria ?? 'UTILITY' }})</option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-sm btn-primary fw-bold">
                                <i class="fa-solid fa-paper-plane me-1"></i> Enviar Agora
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@elseif($link->utilizado)
    <span class="badge bg-success-subtle text-success border border-success-subtle">
        <i class="fa-solid fa-check-double me-1"></i> Utilizado
        @if($link->desfiliacao_id)
            <a href="{{ route('admin.desfiliacoes.show', $link->desfiliacao_id) }}" class="text-success ms-1 text-decoration-underline" title="Ver solicitação vinculada">
                #{{ $link->desfiliacao_id }}
            </a>
        @endif
    </span>
@elseif($link->isCancelado())
    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
        <i class="fa-solid fa-ban me-1"></i> Cancelado
    </span>
@else
    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
        <i class="fa-solid fa-clock-rotate-left me-1"></i> Expirado
    </span>
@endif
