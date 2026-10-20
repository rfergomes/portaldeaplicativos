<div class="modal fade" id="modalGerarLink" tabindex="-1" aria-labelledby="modalGerarLinkLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.desfiliacao.links.store') }}" method="POST" id="formGerarLink">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="modalGerarLinkLabel">
                        <i class="fa-solid fa-link me-2"></i> Gerar Link Exclusivo de Desfiliação
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 small d-flex align-items-center mb-3">
                        <i class="fa-solid fa-shield-halved me-2 fa-lg"></i>
                        <span>O link gerado será de uso único e temporário, blindando a entidade contra disparos em massa.</span>
                    </div>

                    <div class="mb-3">
                        <label for="destinatario_nome" class="form-label fw-semibold">Nome do Trabalhador <span class="text-muted fw-normal">(opcional)</span></label>
                        <input type="text" class="form-control" id="destinatario_nome" name="destinatario_nome" placeholder="Ex.: Carlos Eduardo">
                    </div>

                    <div class="mb-3">
                        <label for="destinatario_contato" class="form-label fw-semibold">WhatsApp / Telefone <span class="text-muted fw-normal">(opcional)</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-brands fa-whatsapp text-success"></i></span>
                            <input type="text" class="form-control" id="destinatario_contato" name="destinatario_contato" placeholder="(19) 98765-4321">
                        </div>
                        <div class="form-text">Facilita o compartilhamento direto pelo WhatsApp Web ao emitir o link.</div>
                    </div>

                    <div class="mb-3">
                        <label for="destinatario_email" class="form-label fw-semibold">E-mail do Trabalhador <span class="text-muted fw-normal">(opcional)</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-primary"></i></span>
                            <input type="email" class="form-control" id="destinatario_email" name="destinatario_email" placeholder="trabalhador@empresa.com.br">
                        </div>
                        <div class="form-text">Facilita o envio por correio eletrônico ou registro cadastral.</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="horas_validade" class="form-label fw-semibold">Validade do Link</label>
                            <select class="form-select" id="horas_validade" name="horas_validade" required>
                                <option value="24">24 Horas (1 dia)</option>
                                <option value="48" selected>48 Horas (2 dias - Padrão)</option>
                                <option value="72">72 Horas (3 dias)</option>
                                <option value="168">7 Dias (1 semana)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="canal_envio" class="form-label fw-semibold">Canal Pretendido</label>
                            <select class="form-select" id="canal_envio" name="canal_envio">
                                <option value="whatsapp" selected>WhatsApp Web</option>
                                <option value="manual">Copiar Manualmente</option>
                                <option value="email">E-mail</option>
                                <option value="kwik">KWIK (Oficial)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Bloco de Template KWIK --}}
                    <div class="mb-3" id="blocoKwikTemplate" style="display: none;">
                        <label for="kwik_template" class="form-label fw-semibold">
                            <i class="fa-solid fa-robot text-primary me-1"></i> Template Oficial KWIK
                        </label>
                        <select class="form-select" id="kwik_template" name="kwik_template">
                            <option value="desfiliacao_link_exclusivo" selected>Padrão (desfiliacao_link_exclusivo)</option>
                            @if(isset($templates) && $templates->count() > 0)
                                @foreach($templates as $tmpl)
                                    @if($tmpl->nome !== 'desfiliacao_link_exclusivo')
                                        <option value="{{ $tmpl->nome }}">{{ $tmpl->nome }} ({{ $tmpl->categoria ?? 'UTILITY' }})</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                        <div class="form-text">Selecione o modelo de mensagem aprovado no KWIK/Meta para disparo oficial.</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="tipo_link_admin" class="form-label fw-semibold">Tipo do Link</label>
                            <select class="form-select" id="tipo_link_admin" name="tipo_link">
                                <option value="individual" selected>Individual (Uso Único)</option>
                                <option value="compartilhado">Compartilhado (Cota N Usos)</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="colLimiteUsosAdmin" style="display: none;">
                            <label for="limite_usos_admin" class="form-label fw-semibold">Cota de Utilizações</label>
                            <input type="number" class="form-control" id="limite_usos_admin" name="limite_usos" value="3" min="2" max="50" disabled>
                        </div>
                        <div class="col-md-6" id="colQuantidadeAdmin">
                            <label for="quantidade" class="form-label fw-semibold">Quantidade de Links</label>
                            <input type="number" class="form-control" id="quantidade" name="quantidade" value="1" min="1" max="50">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold" id="btnConfirmarGerarLink">
                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Gerar Link Agora
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    function sincronizarModalLink() {
        const $tipo = $('#tipo_link_admin');
        const $canal = $('#canal_envio');
        const $colLimite = $('#colLimiteUsosAdmin');
        const $colQtd = $('#colQuantidadeAdmin');
        const $inputLimite = $('#limite_usos_admin');
        const $inputQtd = $('#quantidade');
        const $blocoKwik = $('#blocoKwikTemplate');

        function alternarTipo(valor) {
            if (valor === 'compartilhado') {
                $colLimite.show();
                $inputLimite.prop('disabled', false);
                $colQtd.hide();
                $inputQtd.prop('disabled', true);
            } else {
                $colLimite.hide();
                $inputLimite.prop('disabled', true);
                $colQtd.show();
                $inputQtd.prop('disabled', false);
            }
        }

        function alternarCanal(valor) {
            if (valor === 'kwik') {
                $blocoKwik.show();
            } else {
                $blocoKwik.hide();
            }
        }

        // Suporte unificado a jQuery e Select2
        $tipo.off('change select2:select').on('change select2:select', function () {
            alternarTipo($(this).val());
        });

        $canal.off('change select2:select').on('change select2:select', function () {
            alternarCanal($(this).val());
        });

        // Suporte vanilla fallback caso jQuery Select2 não esteja presente
        const elTipo = document.getElementById('tipo_link_admin');
        if (elTipo) {
            elTipo.addEventListener('change', function () {
                alternarTipo(this.value);
            });
        }
        const elCanal = document.getElementById('canal_envio');
        if (elCanal) {
            elCanal.addEventListener('change', function () {
                alternarCanal(this.value);
            });
        }

        // Estado inicial
        alternarTipo($tipo.val() || 'individual');
        alternarCanal($canal.val() || 'whatsapp');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', sincronizarModalLink);
    } else {
        sincronizarModalLink();
    }

    // Re-sincronizar sempre que o modal for aberto
    const modalEl = document.getElementById('modalGerarLink');
    if (modalEl) {
        modalEl.addEventListener('show.bs.modal', function () {
            setTimeout(sincronizarModalLink, 100);
        });
    }
})();
</script>
