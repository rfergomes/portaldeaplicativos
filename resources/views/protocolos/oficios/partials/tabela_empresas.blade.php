<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fa-solid fa-building-user me-2"></i>Seleção de Empresas e Contatos Destinatários
        </h6>
        <span class="badge bg-primary fs-6" id="badge-contador-selecionados">0 contatos selecionados</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0" id="tabela-empresas-filtradas">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;" class="text-center">
                            <input type="checkbox" class="form-check-input" id="check-selecionar-todos" title="Selecionar Todos Visíveis">
                        </th>
                        <th>Empresa / Razão Social</th>
                        <th>CNPJ / Cidade</th>
                        <th>Região</th>
                        <th>Contatos (E-mail)</th>
                    </tr>
                </thead>
                <tbody id="tbody-empresas">
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="fa-solid fa-filter me-2"></i>Utilize os filtros acima para carregar a lista de empresas.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
