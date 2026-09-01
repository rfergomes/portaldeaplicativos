<!-- Modal de Termo Aditivo (Criar / Editar) -->
<div class="modal fade" id="modalAditivo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
            <form id="formAditivo" action="{{ route('admin.convencoes.aditivos.store', $convencao) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="aditivoMethod" value="POST">
                
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-primary" id="modalAditivoTitle">
                        <i class="fa-solid fa-file-circle-plus me-2"></i>Novo Termo Aditivo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">Nº do Termo <span class="text-danger">*</span></label>
                            <input type="text" name="numero_termo" id="aditivoNumeroTermo" class="form-control form-control-sm" placeholder="Ex: 01/2027 ou Aditivo Salarial" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-secondary">Título do Termo Aditivo <span class="text-danger">*</span></label>
                            <input type="text" name="titulo" id="aditivoTitulo" class="form-control form-control-sm" placeholder="Ex: Termo Aditivo da Campanha Salarial 2027/2028" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">Tipo de Aditivo <span class="text-danger">*</span></label>
                            <select name="tipo" id="aditivoTipo" class="form-select form-select-sm" required>
                                <option value="SALARIAL_ECONOMICO">Salarial / Econômico</option>
                                <option value="GERAL_RETIFICATIVO">Geral / Retificativo</option>
                                <option value="OUTRO">Outro / Específico</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">Início da Vigência do Aditivo <span class="text-danger">*</span></label>
                            <input type="date" name="vigencia_inicio" id="aditivoVigenciaInicio" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">Término da Vigência do Aditivo <span class="text-danger">*</span></label>
                            <input type="date" name="vigencia_fim" id="aditivoVigenciaFim" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Data de Assinatura / Registro (Opcional)</label>
                            <input type="date" name="data_assinatura" id="aditivoDataAssinatura" class="form-control form-control-sm">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary">Descrição / Resumo das Alterações</label>
                            <textarea name="descricao" id="aditivoDescricao" rows="3" class="form-control form-control-sm" placeholder="Descreva os pontos acordados neste termo aditivo (ex: reajuste de 4.5%, novo piso salarial de R$ 2.100,00)..."></textarea>
                        </div>

                        <div class="col-12">
                            <div class="bg-light p-3 rounded border">
                                <label class="form-label small fw-bold text-primary mb-1">
                                    <i class="fa-solid fa-file-pdf text-danger me-1"></i> Documento do Termo Aditivo (PDF)
                                </label>
                                <div id="aditivoArquivoAtual" class="mb-2" style="display: none;">
                                    <span class="badge bg-secondary-subtle text-secondary p-2">
                                        <i class="fa-solid fa-file-pdf text-danger me-1"></i>
                                        <span id="aditivoArquivoNome"></span>
                                    </span>
                                </div>
                                <input type="file" name="arquivo_pdf" id="aditivoArquivoPdf" class="form-control form-control-sm" accept=".pdf,application/pdf">
                                <div class="form-text small text-muted">
                                    Anexe a cópia do termo aditivo assinado em formato PDF (máximo 25MB).
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="ativo" id="aditivoAtivo" value="1" checked>
                                <label class="form-check-label fw-bold text-dark" for="aditivoAtivo">
                                    Termo Aditivo Ativo
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-check me-1"></i> Salvar Termo Aditivo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
