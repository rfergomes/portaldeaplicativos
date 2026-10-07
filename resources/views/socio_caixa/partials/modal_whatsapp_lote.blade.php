<div class="modal fade" id="modalWhatsappLote" tabindex="-1" aria-labelledby="modalWhatsappLoteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="modalWhatsappLoteLabel">
                    <i class="fa-brands fa-whatsapp me-2"></i>Disparo em Massa de WhatsApp (Sócio Caixa)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Resumo dos Filtros e Destinatários -->
                <div class="card border-0 bg-light mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-3">
                            <i class="fas fa-filter text-primary me-2"></i>Resumo da Base Filtrada
                        </h6>
                        <div id="loadingPreviewLote" class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-success me-2" role="status"></div>
                            <span class="text-muted">Calculando associados aptos com os filtros ativos...</span>
                        </div>
                        <div id="conteudoPreviewLote" class="row g-3 d-none">
                            <div class="col-md-4">
                                <div class="p-3 bg-white rounded border text-center">
                                    <small class="text-muted d-block text-uppercase fw-semibold">Total Filtrado</small>
                                    <span id="previewTotalSocios" class="fs-4 fw-bold text-dark">0</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-white rounded border border-success-subtle text-center">
                                    <small class="text-success d-block text-uppercase fw-semibold">Aptos (WhatsApp Válido)</small>
                                    <span id="previewTotalAptos" class="fs-4 fw-bold text-success">0</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-white rounded border border-warning-subtle text-center">
                                    <small class="text-warning d-block text-uppercase fw-semibold">Inaptos (Sem Telefone)</small>
                                    <span id="previewTotalSemTelefone" class="fs-4 fw-bold text-warning">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulário de Seleção do Template -->
                <form id="formWhatsappLote">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Selecione o Template de WhatsApp <span class="text-danger">*</span></label>
                        <select id="selectTemplateLote" class="form-select" required>
                            <option value="">Carregando templates homologados...</option>
                        </select>
                    </div>

                    <!-- Prévia do Template Selecionado -->
                    <div id="boxPreviaTemplate" class="p-3 bg-light rounded border border-secondary-subtle mb-3 d-none">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-secondary" id="badgeNomeTemplate">template_nome</span>
                            <small class="text-muted" id="txtDescricaoTemplate"></small>
                        </div>
                        <div class="small text-muted font-monospace bg-white p-2 rounded border mt-2" id="txtCorpoTemplate" style="white-space: pre-wrap;"></div>
                    </div>

                    <!-- Campos para Parâmetros Extras (se existirem) -->
                    <div id="boxParametrosExtras" class="d-none mb-3">
                        <h6 class="fw-bold text-dark mb-2">
                            <i class="fas fa-sliders text-secondary me-2"></i>Parâmetros Dinâmicos da Mensagem
                        </h6>
                        <small class="text-muted d-block mb-3">O 1º parâmetro (<code>{{ '{' . '{1}' . '}' }}</code>) é automaticamente preenchido com o <strong>Nome do Associado</strong>. Preencha os valores adicionais abaixo:</small>
                        <div id="containerCamposParametros" class="vstack gap-2"></div>
                    </div>

                    <!-- Alerta de Auditoria Obrigatória -->
                    <div class="alert alert-info py-2 px-3 small border-0 mb-0 d-flex align-items-center">
                        <i class="fas fa-shield-alt text-primary fs-5 me-2"></i>
                        <span><strong>Auditoria Contínua:</strong> Cada mensagem enviada será registrada de forma atômica no <strong>Histórico de Ocorrências</strong> do associado com identificação do operador e data/hora.</span>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarDisparoLote" class="btn btn-success fw-bold px-4" disabled>
                    <i class="fas fa-paper-plane me-1"></i>Iniciar Disparo em Massa
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('modalWhatsappLote');
    if (!modalEl) return;

    let templatesDisponiveis = [];

    // Ao abrir a modal, carregar preview e templates
    modalEl.addEventListener('show.bs.modal', function () {
        carregarTemplates();
        carregarPreview();
    });

    function getFiltrosAtuais() {
        const urlParams = new URLSearchParams(window.location.search);
        const filtros = {};
        for (const [key, value] of urlParams.entries()) {
            filtros[key] = value;
        }
        return filtros;
    }

    function encontrarTemplate(id) {
        if (!id) return null;
        return templatesDisponiveis.find(t => String(t.id) === String(id)) || null;
    }

    function atualizarEstadoBotaoDisparo() {
        const btn = document.getElementById('btnConfirmarDisparoLote');
        const select = document.getElementById('selectTemplateLote');
        const aptos = parseInt(document.getElementById('previewTotalAptos').innerText) || 0;
        const template = encontrarTemplate(select.value);

        if (!template || aptos <= 0) {
            btn.disabled = true;
            return;
        }

        // Valida parâmetros extras se exigidos
        const inputsExtras = document.querySelectorAll('.campo-parametro-extra');
        for (const input of inputsExtras) {
            if (!input.value.trim()) {
                btn.disabled = true;
                return;
            }
        }

        btn.disabled = false;
    }

    function renderizarPreviaTemplate(templateId) {
        const template = encontrarTemplate(templateId);
        const boxPrevia = document.getElementById('boxPreviaTemplate');
        const boxParams = document.getElementById('boxParametrosExtras');
        const containerParams = document.getElementById('containerCamposParametros');
        containerParams.innerHTML = '';

        if (!template) {
            boxPrevia.classList.add('d-none');
            boxParams.classList.add('d-none');
            atualizarEstadoBotaoDisparo();
            return;
        }

        // Exibe prévia formatada
        boxPrevia.classList.remove('d-none');
        document.getElementById('badgeNomeTemplate').textContent = template.nome;
        document.getElementById('txtDescricaoTemplate').textContent = template.descricao;
        document.getElementById('txtCorpoTemplate').textContent = template.corpo_exemplo || 'Texto de exemplo não cadastrado.';

        // Gera campos para parâmetros extras (se houver)
        if (Array.isArray(template.parametros_esperados) && template.parametros_esperados.length > 0) {
            boxParams.classList.remove('d-none');
            template.parametros_esperados.forEach((paramNome, index) => {
                const div = document.createElement('div');
                div.className = 'input-group';
                div.innerHTML = `
                    <span class="input-group-text bg-light text-muted small" style="min-width: 140px;">${paramNome}</span>
                    <input type="text" class="form-control campo-parametro-extra" data-index="${index}" placeholder="Valor para ${paramNome}" required>
                `;
                const inputEl = div.querySelector('input');
                inputEl.addEventListener('input', atualizarEstadoBotaoDisparo);
                containerParams.appendChild(div);
            });
        } else {
            boxParams.classList.add('d-none');
        }

        atualizarEstadoBotaoDisparo();
    }

    function carregarPreview() {
        document.getElementById('loadingPreviewLote').classList.remove('d-none');
        document.getElementById('conteudoPreviewLote').classList.add('d-none');
        document.getElementById('btnConfirmarDisparoLote').disabled = true;

        fetch('{{ route("socios-caixa.whatsapp-lote.preview") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(getFiltrosAtuais())
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('loadingPreviewLote').classList.add('d-none');
            document.getElementById('conteudoPreviewLote').classList.remove('d-none');

            document.getElementById('previewTotalSocios').innerText = data.total_socios || 0;
            document.getElementById('previewTotalAptos').innerText = data.total_aptos || 0;
            document.getElementById('previewTotalSemTelefone').innerText = data.total_sem_telefone || 0;

            atualizarEstadoBotaoDisparo();
        })
        .catch(err => {
            console.error('Erro ao carregar preview do lote:', err);
            document.getElementById('loadingPreviewLote').innerHTML = '<span class="text-danger"><i class="fas fa-times-circle me-1"></i>Erro ao carregar prévia de associados.</span>';
            atualizarEstadoBotaoDisparo();
        });
    }

    function carregarTemplates() {
        const select = document.getElementById('selectTemplateLote');
        select.innerHTML = '<option value="">Carregando templates...</option>';

        fetch('{{ route("whatsapp-templates.ativos") }}')
        .then(res => res.json())
        .then(templates => {
            templatesDisponiveis = templates;
            select.innerHTML = '<option value="">-- Selecione o template --</option>';
            templates.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = `${t.nome} - ${t.descricao}`;
                select.appendChild(opt);
            });

            // Se houver apenas 1 template ativo homologado, auto-seleciona para facilitar
            if (templates.length === 1) {
                select.value = templates[0].id;
            }

            if (select.value) {
                renderizarPreviaTemplate(select.value);
            } else {
                atualizarEstadoBotaoDisparo();
            }
        })
        .catch(err => {
            console.error('Erro ao carregar templates:', err);
            select.innerHTML = '<option value="">Erro ao carregar templates</option>';
            atualizarEstadoBotaoDisparo();
        });
    }

    document.getElementById('selectTemplateLote').addEventListener('change', function () {
        renderizarPreviaTemplate(this.value);
    });

    document.getElementById('btnConfirmarDisparoLote').addEventListener('click', function () {
        const select = document.getElementById('selectTemplateLote');
        const template = encontrarTemplate(select.value);
        if (!template) {
            alert('Por favor, selecione um template antes de disparar.');
            return;
        }

        const paramsInputs = document.querySelectorAll('.campo-parametro-extra');
        const paramsExtras = [];
        for (const input of paramsInputs) {
            if (!input.value.trim()) {
                alert('Preencha todos os parâmetros dinâmicos do template.');
                input.focus();
                return;
            }
            paramsExtras.push(input.value.trim());
        }

        const totalAptos = document.getElementById('previewTotalAptos').innerText;
        if (!confirm(`Confirma o envio em massa para ${totalAptos} associado(s) com WhatsApp válido? Esta ação é irreversível e será auditada no histórico de cada associado.`)) {
            return;
        }

        const btn = document.getElementById('btnConfirmarDisparoLote');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Iniciando...';

        fetch('{{ route("socios-caixa.whatsapp-lote.disparar") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                whatsapp_template_id: template.id,
                filtros: getFiltrosAtuais(),
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
            console.error('Erro ao disparar lote:', err);
            alert('Falha na comunicação com o servidor ao disparar lote.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Iniciar Disparo em Massa';
        });
    });
});
</script>
