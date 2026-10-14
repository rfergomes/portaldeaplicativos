<div class="modal fade" id="modalRecusarSolicitacao" tabindex="-1" aria-labelledby="modalRecusarSolicitacaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formRecusarSolicitacao" method="POST" action="">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="modalRecusarSolicitacaoLabel">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> Recusar Solicitação com Justificativa
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger py-2 small d-flex align-items-start mb-3">
                        <i class="fa-solid fa-circle-exclamation me-2 mt-1 fa-lg"></i>
                        <div>
                            <strong>Atenção:</strong> Ao recusar, a solicitação será <strong>invalidada formalmente</strong> e o trabalhador receberá um e-mail com a justificativa oficial e orientações para sanar divergências ou solicitar novo link.
                        </div>
                    </div>

                    <div class="bg-light p-3 rounded mb-3 small">
                        <div><strong>Solicitante:</strong> <span id="recusaNomeTrabalhador"></span></div>
                        <div><strong>CPF:</strong> <span id="recusaCpfTrabalhador" class="font-monospace"></span></div>
                        <div><strong>Empresa:</strong> <span id="recusaEmpresaTrabalhador"></span></div>
                    </div>

                    <div class="mb-3">
                        <label for="motivo_recusa" class="form-label fw-bold text-dark">
                            Motivo / Justificativa da Recusa <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" 
                                  id="motivo_recusa" 
                                  name="motivo_recusa" 
                                  rows="4" 
                                  required 
                                  minlength="10" 
                                  placeholder="Ex.: Após conferência no ERP Ábaco, constatamos que o solicitante não consta no quadro de associados ativos da entidade sindical para a empresa informada."></textarea>
                        <div class="form-text">Mínimo de 10 caracteres. Esta justificativa será transcrita no e-mail enviado ao trabalhador.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger fw-bold" id="btnConfirmarRecusa">
                        <i class="fa-solid fa-ban me-1"></i> Confirmar Recusa e Notificar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
