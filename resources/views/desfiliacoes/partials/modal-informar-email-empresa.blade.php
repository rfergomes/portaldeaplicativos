<div class="modal fade" id="modalInformarEmailEmpresa" tabindex="-1" aria-labelledby="modalInformarEmailEmpresaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formNotificarEmpresa" method="POST" action="">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold" id="modalInformarEmailEmpresaLabel">
                        <i class="fa-solid fa-building-circle-arrow-right me-2"></i> Notificar Empresa Empregadora (AR-Online)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 small d-flex align-items-center mb-3">
                        <i class="fa-solid fa-stamp me-2 fa-lg"></i>
                        <span>O envio será processado pela <strong>API AR-Online</strong>, gerando carimbo de tempo institucional e comprovante com validade jurídica.</span>
                    </div>

                    <div class="bg-light p-3 rounded mb-3 small">
                        <div><strong>Empresa:</strong> <span id="empresaNotifRazaoSocial"></span></div>
                        <div><strong>Trabalhador:</strong> <span id="empresaNotifTrabalhador"></span></div>
                    </div>

                    <div class="mb-3">
                        <label for="email_rh" class="form-label fw-bold text-dark">
                            E-mail do RH / Departamento Pessoal <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email_rh" name="email_rh" required placeholder="rh@empresa.com.br">
                        </div>
                        <div class="form-text">Endereço corporativo oficial onde o ofício de desfiliação com o termo em PDF será protocolado.</div>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="salvar_contato" value="1" id="salvar_contato" checked>
                        <label class="form-check-label small" for="salvar_contato">
                            Salvar este e-mail no catálogo da empresa para que futuras desfiliações sejam enviadas automaticamente.
                        </label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning fw-bold text-dark" id="btnConfirmarNotifEmpresa">
                        <i class="fa-solid fa-paper-plane me-1"></i> Despachar via AR-Online
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
