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
                            <input type="number" class="form-control" id="limite_usos_admin" name="limite_usos" value="5" min="2" max="50">
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
document.addEventListener('DOMContentLoaded', function () {
    const selTipo = document.getElementById('tipo_link_admin');
    const colLimite = document.getElementById('colLimiteUsosAdmin');
    const colQtd = document.getElementById('colQuantidadeAdmin');
    if (selTipo && colLimite && colQtd) {
        selTipo.addEventListener('change', function () {
            if (this.value === 'compartilhado') {
                colLimite.style.display = 'block';
                colQtd.style.display = 'none';
            } else {
                colLimite.style.display = 'none';
                colQtd.style.display = 'block';
            }
        });
    }
});
</script>
