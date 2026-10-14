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
