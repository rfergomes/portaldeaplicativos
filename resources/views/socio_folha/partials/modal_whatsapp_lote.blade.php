<div class="modal fade" id="modalWhatsappLoteFolha" tabindex="-1" aria-labelledby="modalWhatsappLoteFolhaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="modalWhatsappLoteFolhaLabel">
                    <i class="fa-brands fa-whatsapp me-2"></i>Disparo em Massa de WhatsApp (Sócio Folha)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Resumo dos Filtros e Destinatários -->
                <div class="card border-0 bg-light mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-3">
                            <i class="fas fa-filter text-primary me-2"></i>Resumo das Empresas e Contatos Filtrados
                        </h6>
                        <div id="loadingPreviewLoteFolha" class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-success me-2" role="status"></div>
                            <span class="text-muted">Calculando empresas e contatos com os filtros ativos...</span>
                        </div>
                        <div id="conteudoPreviewLoteFolha" class="row g-3 d-none">
                            <div class="col-md-4">
                                <div class="p-3 bg-white rounded border text-center">
                                    <small class="text-muted d-block text-uppercase fw-semibold">Empresas Filtradas</small>
                                    <span id="previewTotalEmpresasFolha" class="fs-4 fw-bold text-dark">0</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-white rounded border border-success-subtle text-center">
                                    <small class="text-success d-block text-uppercase fw-semibold">Contatos Aptos (WhatsApp)</small>
                                    <span id="previewTotalAptosFolha" class="fs-4 fw-bold text-success">0</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-white rounded border border-warning-subtle text-center">
                                    <small class="text-warning d-block text-uppercase fw-semibold">Sem Telefone</small>
                                    <span id="previewTotalSemTelefoneFolha" class="fs-4 fw-bold text-warning">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulário de Seleção do Template -->
                <form id="formWhatsappLoteFolha">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Selecione o Template de WhatsApp <span class="text-danger">*</span></label>
                        <select id="selectTemplateLoteFolha" class="form-select" required>
                            <option value="">Carregando templates homologados...</option>
                        </select>
                    </div>

                    <!-- Prévia do Template Selecionado -->
                    <div id="boxPreviaTemplateFolha" class="p-3 bg-light rounded border border-secondary-subtle mb-3 d-none">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-secondary" id="badgeNomeTemplateFolha">template_nome</span>
                            <small class="text-muted" id="txtDescricaoTemplateFolha"></small>
                        </div>
                        <div class="small text-muted font-monospace bg-white p-2 rounded border mt-2" id="txtCorpoTemplateFolha" style="white-space: pre-wrap;"></div>
                    </div>

                    <!-- Campos para Parâmetros Extras (se existirem) -->
                    <div id="boxParametrosExtrasFolha" class="d-none mb-3">
                        <h6 class="fw-bold text-dark mb-2">
                            <i class="fas fa-sliders text-secondary me-2"></i>Parâmetros Dinâmicos da Mensagem
                        </h6>
                        <small class="text-muted d-block mb-3">O 1º parâmetro (<code>{{ '{' . '{1}' . '}' }}</code>) é automaticamente preenchido com o <strong>Nome do Contato / Empresa</strong>. Preencha os valores adicionais abaixo:</small>
                        <div id="containerCamposParametrosFolha" class="vstack gap-2"></div>
                    </div>

                    <!-- Alerta de Auditoria Obrigatória -->
                    <div class="alert alert-info py-2 px-3 small border-0 mb-0 d-flex align-items-center">
                        <i class="fas fa-shield-alt text-primary fs-5 me-2"></i>
                        <span><strong>Auditoria Contínua:</strong> Cada mensagem enviada será registrada de forma atômica no <strong>Histórico de Alterações</strong> da folha/empresa com operador e data/hora.</span>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarDisparoLoteFolha" class="btn btn-success fw-bold px-4" disabled>
                    <i class="fas fa-paper-plane me-1"></i>Iniciar Disparo em Massa
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('modalWhatsappLoteFolha');
    if (!modalEl) return;

    let templatesDisponiveisFolha = [];

    modalEl.addEventListener('show.bs.modal', function () {
        carregarTemplatesFolha();
        carregarPreviewFolha();
    });

    function getFiltrosAtuaisFolha() {
        const urlParams = new URLSearchParams(window.location.search);
        const filtros = {};
        for (const [key, value] of urlParams.entries()) {
            filtros[key] = value;
        }
        return filtros;
    }

    function carregarPreviewFolha() {
        document.getElementById('loadingPreviewLoteFolha').classList.remove('d-none');
        document.getElementById('conteudoPreviewLoteFolha').classList.add('d-none');
        document.getElementById('btnConfirmarDisparoLoteFolha').disabled = true;

        fetch('{{ route("socios-folha.whatsapp-lote.preview") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(getFiltrosAtuaisFolha())
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('loadingPreviewLoteFolha').classList.add('d-none');
            document.getElementById('conteudoPreviewLoteFolha').classList.remove('d-none');

            document.getElementById('previewTotalEmpresasFolha').innerText = data.total_empresas || 0;
            document.getElementById('previewTotalAptosFolha').innerText = data.total_aptos || 0;
            document.getElementById('previewTotalSemTelefoneFolha').innerText = data.total_sem_telefone || 0;

            if (data.total_aptos > 0 && document.getElementById('selectTemplateLoteFolha').value) {
                document.getElementById('btnConfirmarDisparoLoteFolha').disabled = false;
            }
        })
        .catch(err => {
            console.error('Erro ao carregar preview do lote folha:', err);
            document.getElementById('loadingPreviewLoteFolha').innerHTML = '<span class="text-danger"><i class="fas fa-times-circle me-1"></i>Erro ao carregar prévia da folha.</span>';
        });
    }

    function carregarTemplatesFolha() {
        const select = document.getElementById('selectTemplateLoteFolha');
        select.innerHTML = '<option value="">Carregando templates...</option>';

        fetch('{{ route("whatsapp-templates.ativos") }}')
        .then(res => res.json())
        .then(templates => {
            templatesDisponiveisFolha = templates;
            select.innerHTML = '<option value="">-- Selecione o template --</option>';
            templates.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = `${t.nome} - ${t.descricao}`;
                select.appendChild(opt);
            });
        })
        .catch(err => {
            console.error('Erro ao carregar templates:', err);
            select.innerHTML = '<option value="">Erro ao carregar templates</option>';
        });
    }

    document.getElementById('selectTemplateLoteFolha').addEventListener('change', function () {
        const templateId = parseInt(this.value);
        const template = templatesDisponiveisFolha.find(t => t.id === templateId);

        const boxPrevia = document.getElementById('boxPreviaTemplateFolha');
        const boxParams = document.getElementById('boxParametrosExtrasFolha');
        const containerParams = document.getElementById('containerCamposParametrosFolha');
        containerParams.innerHTML = '';

        if (!template) {
            boxPrevia.classList.add('d-none');
            boxParams.classList.add('d-none');
            document.getElementById('btnConfirmarDisparoLoteFolha').disabled = true;
            return;
        }

        boxPrevia.classList.remove('d-none');
        document.getElementById('badgeNomeTemplateFolha').textContent = template.nome;
        document.getElementById('txtDescricaoTemplateFolha').textContent = template.descricao;
        document.getElementById('txtCorpoTemplateFolha').textContent = template.corpo_exemplo || 'Texto de exemplo não cadastrado.';

        if (Array.isArray(template.parametros_esperados) && template.parametros_esperados.length > 0) {
            boxParams.classList.remove('d-none');
            template.parametros_esperados.forEach((paramNome, index) => {
                const div = document.createElement('div');
                div.className = 'input-group';
                div.innerHTML = `
                    <span class="input-group-text bg-light text-muted small" style="min-width: 140px;">${paramNome}</span>
                    <input type="text" class="form-control campo-parametro-extra-folha" data-index="${index}" placeholder="Valor para ${paramNome}" required>
                `;
                containerParams.appendChild(div);
            });
        } else {
            boxParams.classList.add('d-none');
        }

        const aptos = parseInt(document.getElementById('previewTotalAptosFolha').innerText) || 0;
        if (aptos > 0) {
            document.getElementById('btnConfirmarDisparoLoteFolha').disabled = false;
        }
    });

    document.getElementById('btnConfirmarDisparoLoteFolha').addEventListener('click', function () {
        const select = document.getElementById('selectTemplateLoteFolha');
        if (!select.value) {
            alert('Por favor, selecione um template antes de disparar.');
            return;
        }

        const paramsInputs = document.querySelectorAll('.campo-parametro-extra-folha');
        const paramsExtras = [];
        for (const input of paramsInputs) {
            if (!input.value.trim()) {
                alert('Preencha todos os parâmetros dinâmicos do template.');
                input.focus();
                return;
            }
            paramsExtras.push(input.value.trim());
        }

        const totalAptos = document.getElementById('previewTotalAptosFolha').innerText;
        if (!confirm(`Confirma o envio em massa para ${totalAptos} contato(s) de empresas com WhatsApp válido? Esta ação é irreversível e será auditada no histórico.`)) {
            return;
        }

        const btn = document.getElementById('btnConfirmarDisparoLoteFolha');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Iniciando...';

        fetch('{{ route("socios-folha.whatsapp-lote.disparar") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                whatsapp_template_id: parseInt(select.value),
                filtros: getFiltrosAtuaisFolha(),
                parametros_extras: paramsExtras
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(`Lote #${data.lote_id} criado com sucesso! ${data.total_enfileirados} mensagens foram enfileiradas e estão sendo enviadas em segundo plano.`);
                window.location.href = '{{ route("whatsapp-lotes.index") }}';
            } else {
                alert('Erro ao iniciar lote: ' + (data.message || 'Erro desconhecido.'));
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Iniciar Disparo em Massa';
            }
        })
        .catch(err => {
            console.error('Erro ao disparar lote folha:', err);
            alert('Falha na comunicação com o servidor ao disparar lote.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Iniciar Disparo em Massa';
        });
    });
});
</script>
