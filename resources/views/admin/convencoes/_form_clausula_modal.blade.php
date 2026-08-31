<!-- Modal de Cláusula (Criar / Editar) -->
<div class="modal fade" id="modalClausula" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
            <form id="formClausula" action="{{ route('admin.convencoes.clausulas.store', $convencao) }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="clausulaMethod" value="POST">
                
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-primary" id="modalClausulaTitle">
                        <i class="fa-solid fa-file-lines me-2"></i>Adicionar Cláusula
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary">Número <span class="text-danger">*</span></label>
                            <input type="text" name="numero" id="clausulaNumero" class="form-control form-control-sm" placeholder="Ex: 76 ou 03" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-secondary">Título da Cláusula <span class="text-danger">*</span></label>
                            <input type="text" name="titulo" id="clausulaTitulo" class="form-control form-control-sm" placeholder="Ex: Contribuições Associativas Mensais" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">Categoria <span class="text-danger">*</span></label>
                            <select name="categoria_clausula" id="clausulaCategoria" class="form-select form-select-sm" required>
                                <option value="CONTRIBUICAO">Contribuição</option>
                                <option value="SALARIO_NORMATIVO">Salário Normativo</option>
                                <option value="REAJUSTE">Reajuste Salarial</option>
                                <option value="BENEFICIO">Benefício</option>
                                <option value="GERAL">Geral / Outros</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary">Texto / Teor Normativo da Cláusula <span class="text-danger">*</span></label>
                            <textarea name="texto" id="clausulaTexto" rows="6" class="form-control" placeholder="Transcreva aqui o texto da cláusula..." required></textarea>
                            <div class="form-text small">
                                O teor da cláusula será utilizado como citação oficial no envio automático de lembretes e relatórios.
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">Vigência Início (Opcional)</label>
                            <input type="date" name="vigencia_inicio" id="clausulaVigenciaInicio" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">Vigência Fim (Opcional)</label>
                            <input type="date" name="vigencia_fim" id="clausulaVigenciaFim" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">Ordem de Exibição</label>
                            <input type="number" name="ordem" id="clausulaOrdem" class="form-control form-control-sm" value="0">
                        </div>

                        <div class="col-12">
                            <div class="bg-light p-3 rounded border">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="dispara_lembrete_lista_nominal" id="clausulaLembrete" value="1">
                                    <label class="form-check-label fw-bold text-primary" for="clausulaLembrete">
                                        <i class="fa-solid fa-bell me-1"></i> Disparar Lembrete Automático da Lista Nominal (Cláusula 76)
                                    </label>
                                </div>
                                <small class="text-muted d-block">
                                    Ao marcar esta opção, esta cláusula servirá de base para o texto do e-mail automático enviado às empresas 15 dias após o vencimento da contribuição. (Apenas uma cláusula por convenção).
                                </small>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="ativo" id="clausulaAtivo" value="1" checked>
                                <label class="form-check-label fw-bold text-dark" for="clausulaAtivo">
                                    Cláusula Ativa
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-check me-1"></i> Salvar Cláusula
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
