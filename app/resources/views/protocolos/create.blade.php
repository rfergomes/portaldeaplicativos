@extends('layouts.app')

@section('title', 'Novo Protocolo')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="card card-outline card-primary shadow-sm mb-4">
                    <div class="card-header border-0">
                        <h3 class="card-title fw-bold m-0"><i class="fa-solid fa-paper-plane me-2"></i>Novo Protocolo</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="bs-stepper" id="stepperProtocolo">
                            <div class="bs-stepper-header" role="tablist">
                                <!-- Step 1 -->
                                <div class="step" data-target="#step-identificacao">
                                    <button type="button" class="step-trigger" role="tab" aria-controls="step-identificacao"
                                        id="step-identificacao-trigger">
                                        <span class="bs-stepper-circle bg-primary"><i class="fa-solid fa-tag"></i></span>
                                        <span class="bs-stepper-label">Dados Básicos</span>
                                    </button>
                                </div>
                                <div class="line"></div>
                                <!-- Step 2 -->
                                <div class="step" data-target="#step-destinatarios">
                                    <button type="button" class="step-trigger" role="tab" aria-controls="step-destinatarios"
                                        id="step-destinatarios-trigger">
                                        <span class="bs-stepper-circle bg-info"><i class="fa-solid fa-users"></i></span>
                                        <span class="bs-stepper-label">Destinatários</span>
                                    </button>
                                </div>
                                <div class="line"></div>
                                <!-- Step 3 -->
                                <div class="step" data-target="#step-mensagem">
                                    <button type="button" class="step-trigger" role="tab" aria-controls="step-mensagem"
                                        id="step-mensagem-trigger">
                                        <span class="bs-stepper-circle bg-secondary"><i
                                                class="fa-solid fa-align-left"></i></span>
                                        <span class="bs-stepper-label">Mensagem & Anexos</span>
                                    </button>
                                </div>
                            </div>

                            <div class="bs-stepper-content p-4 border-top">
                                <form action="{{ route('protocolos.store') }}" method="POST" id="formProtocolo"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @if($errors->any())
                                        <div class="alert alert-danger rounded-3 shadow-sm mb-4">
                                            <ul class="mb-0">
                                                @foreach($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <!-- Passo 1: Identificação -->
                                    <div id="step-identificacao" class="content" role="tabpanel"
                                        aria-labelledby="step-identificacao-trigger">
                                        <div class="row align-items-center mb-4">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Tipo de Protocolo</label>
                                                <select name="tipo_protocolo_id" class="form-select" id="inputTipo"
                                                    required>
                                                    <option value="">— SELECIONE UM TIPO —</option>
                                                    @foreach($tiposProtocolo as $tipo)
                                                        <option value="{{ $tipo->id }}" {{ old('tipo_protocolo_id') == $tipo->id ? 'selected' : '' }}>
                                                            {{ $tipo->nome }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">Referência / Nº do Documento</label>
                                                <input type="text" name="referencia_documento" class="form-control"
                                                    placeholder="EX: Ofício 001/2026"
                                                    value="{{ old('referencia_documento') }}">
                                                <small class="text-muted">Opcional.</small>
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label fw-bold">Empresa Relacionada</label>
                                                <div class="d-flex gap-2">
                                                    <select name="empresa_id" id="empresa_id"
                                                        class="form-select select2 w-100">
                                                        <option value="">— NENHUMA —</option>
                                                        @foreach($empresas as $empresa)
                                                            <option value="{{ $empresa->id }}" {{ old('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                                                {{ $empresa->razao_social }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" class="btn btn-outline-info text-nowrap"
                                                        id="btnPuxarContatos" title="Puxar Contatos" disabled>
                                                        <i class="fa-solid fa-cloud-arrow-down"></i>
                                                    </button>
                                                </div>
                                                <small class="text-muted">Selecione a empresa e clique no botão para puxar
                                                    contatos registrados.</small>
                                            </div>
                                            <div class="col-md-12 mb-0">
                                                <label class="form-label fw-bold">Assunto</label>
                                                <input type="text" name="assunto"
                                                    class="form-control @error('assunto') is-invalid @enderror"
                                                    id="inputAssunto" placeholder="ASSUNTO DO PROTOCOLO" required
                                                    value="{{ old('assunto') }}">
                                                @error('assunto')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end bg-light p-3 rounded shadow-sm border mt-4">
                                            <button type="button" class="btn btn-primary px-4 shadow-sm"
                                                onclick="proximoPasso(1)">
                                                Próximo Passo <i class="fa-solid fa-arrow-right ms-2"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Passo 2: Destinatários -->
                                    <div id="step-destinatarios" class="content" role="tabpanel"
                                        aria-labelledby="step-destinatarios-trigger">
                                        <div class="d-flex justify-content-between align-items-end mb-3">
                                            <div>
                                                <h5 class="fw-bold mb-1">Lista de Destinatários</h5>
                                                <small class="text-muted">Adicione as pessoas que irão receber o
                                                    protocolo.</small>
                                            </div>
                                            <button type="button"
                                                class="btn btn-info btn-sm rounded-pill px-3 shadow-sm text-white"
                                                onclick="adicionarDestinatario()">
                                                <i class="fa-solid fa-user-plus me-1"></i> Adicionar Manualmente
                                            </button>
                                        </div>

                                        <div id="listaDestinatarios" class="border rounded shadow-sm mb-4">
                                            <!-- Destinatário 1 (padrão) -->
                                            <div class="destinatario-row px-4 py-3 border-bottom">
                                                <div class="row g-2 align-items-end">
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold text-muted">NOME <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" name="destinatarios[0][nome]"
                                                            class="form-control form-control-sm dt-nome"
                                                            placeholder="NOME COMPLETO">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold text-muted">E-MAIL <span
                                                                class="text-danger">*</span></label>
                                                        <input type="email" name="destinatarios[0][email]"
                                                            class="form-control form-control-sm dt-email"
                                                            placeholder="email@empresa.com">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small fw-bold text-muted">CELULAR <small
                                                                class="text-info">(WhatsApp)</small></label>
                                                        <input type="text" name="destinatarios[0][telefone]"
                                                            class="form-control form-control-sm telefone-input"
                                                            placeholder="+55 19 9.9999-9999">
                                                    </div>
                                                </div>
                                                <div class="row g-2 mt-1">
                                                    <div class="col-md-4">
                                                        <label class="form-label small fw-bold text-muted">CPF/CNPJ</label>
                                                        <input type="text" name="destinatarios[0][cpf_cnpj]"
                                                            class="form-control form-control-sm"
                                                            placeholder="Apenas números">
                                                    </div>
                                                    <div class="col-md-7">
                                                        <label class="form-label small fw-bold text-muted">ENDEREÇO</label>
                                                        <input type="text" name="destinatarios[0][endereco][logradouro]"
                                                            class="form-control form-control-sm"
                                                            placeholder="Rua, Número, Bairro, CEP, Cidade-UF">
                                                    </div>
                                                    <div class="col-md-1 text-end"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            class="d-flex justify-content-between bg-light p-3 rounded shadow-sm border mt-4">
                                            <button type="button" class="btn btn-secondary px-4 shadow-sm"
                                                onclick="voltarPasso()">
                                                <i class="fa-solid fa-arrow-left me-2"></i> Anterior
                                            </button>
                                            <button type="button" class="btn btn-primary px-4 shadow-sm"
                                                onclick="proximoPasso(2)">
                                                Próximo Passo <i class="fa-solid fa-arrow-right ms-2"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Passo 3: Mensagem -->
                                    <div id="step-mensagem" class="content" role="tabpanel"
                                        aria-labelledby="step-mensagem-trigger">
                                        <div class="mb-4">
                                            <label class="form-label fw-bold">Conteúdo da Mensagem <span
                                                    class="text-danger">*</span></label>
                                            <textarea name="corpo" id="corpoProtocolo"
                                                class="form-control @error('corpo') is-invalid @enderror dt-corpo" rows="8"
                                                placeholder="Escreva o conteúdo do protocolo aqui...">{{ old('corpo') }}</textarea>
                                            <small class="text-muted mt-1 d-block mb-3">
                                                <i class="fa-solid fa-circle-info me-1"></i> HTML é suportado no corpo do
                                                e-mail (usará formatação rica se houver Summernote).
                                            </small>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label fw-bold"><i
                                                    class="fa-solid fa-paperclip me-2"></i>Anexos Oficiais</label>
                                            <input type="file" name="anexos[]" class="form-control border-secondary"
                                                multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                                            <small class="text-muted">Selecione um ou mais arquivos. (Lembrando que o peso
                                                somado não deve exceder 20MB)</small>
                                        </div>

                                        <div
                                            class="d-flex justify-content-between bg-light p-3 rounded shadow-sm border mt-5">
                                            <button type="button" class="btn btn-secondary px-4 shadow-sm"
                                                onclick="voltarPasso()">
                                                <i class="fa-solid fa-arrow-left me-2"></i> Anterior
                                            </button>
                                            <button type="submit" id="btnEnviarFinal"
                                                class="btn btn-success px-5 shadow fw-bold">
                                                <i class="fa-solid fa-paper-plane me-2"></i> Enviar Protocolo Oficial
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
        <script>
            let stepper;
            let destinatarioIndex = 1;

            document.addEventListener('DOMContentLoaded', function () {
                stepper = new Stepper(document.querySelector('.bs-stepper'), {
                    linear: false,
                    animation: true
                });
            });

            function proximoPasso(step) {
                if (step === 1) {
                    if (!$('#inputTipo').val() || !$('#inputAssunto').val()) {
                        Swal.fire('Atenção', 'Preencha o Tipo e Assunto antes de avançar.', 'warning');
                        return;
                    }
                }
                stepper.next();
            }

            function voltarPasso() {
                stepper.previous();
            }

            function adicionarDestinatario() {
                const i = destinatarioIndex++;
                const row = document.createElement('div');
                row.className = 'destinatario-row border-top px-4 py-3';
                row.innerHTML = `
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">NOME</label>
                            <input type="text" name="destinatarios[${i}][nome]"
                                class="form-control form-control-sm" placeholder="NOME COMPLETO" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">E-MAIL</label>
                            <input type="email" name="destinatarios[${i}][email]"
                                class="form-control form-control-sm" placeholder="email@empresa.com" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">
                                CELULAR <small class="text-info">(WhatsApp)</small>
                            </label>
                            <input type="text" name="destinatarios[${i}][telefone]"
                                class="form-control form-control-sm telefone-input"
                                placeholder="+55 19 9.9999-9999">
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">CPF/CNPJ <small class="text-info">(Opcional)</small></label>
                            <input type="text" name="destinatarios[${i}][cpf_cnpj]"
                                class="form-control form-control-sm" placeholder="Apenas números">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label small fw-bold text-muted">ENDEREÇO <small class="text-info">(Para AR-Cartas no futuro)</small></label>
                            <input type="text" name="destinatarios[${i}][endereco][logradouro]"
                                class="form-control form-control-sm" placeholder="Rua, Número, Bairro, CEP, Cidade-UF">
                        </div>
                        <div class="col-md-1 text-end pt-4">
                            <button type="button" class="btn btn-light btn-sm border-0 rounded-circle shadow-sm"
                                onclick="this.closest('.destinatario-row').remove()" title="Remover">
                                <i class="fa-solid fa-times text-danger"></i>
                            </button>
                        </div>
                    </div>`;
                document.getElementById('listaDestinatarios').appendChild(row);
            }

            function adicionarDestinatarioPreenchido(nome, email, telefone, documento) {
                const i = destinatarioIndex++;
                const row = document.createElement('div');
                row.className = 'destinatario-row border-top px-4 py-3 bg-light';
                row.innerHTML = `
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">NOME</label>
                            <input type="text" name="destinatarios[${i}][nome]" value="${nome}"
                                class="form-control form-control-sm" placeholder="NOME COMPLETO" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">E-MAIL</label>
                            <input type="email" name="destinatarios[${i}][email]" value="${email}"
                                class="form-control form-control-sm" placeholder="email@empresa.com" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-muted">
                                CELULAR <small class="text-info">(WhatsApp)</small>
                            </label>
                            <input type="text" name="destinatarios[${i}][telefone]" value="${telefone || ''}"
                                class="form-control form-control-sm telefone-input"
                                placeholder="+55 19 9.9999-9999">
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-muted">CPF/CNPJ <small class="text-info">(Opcional)</small></label>
                            <input type="text" name="destinatarios[${i}][cpf_cnpj]" value="${documento || ''}"
                                class="form-control form-control-sm" placeholder="Apenas números">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label small fw-bold text-muted">ENDEREÇO <small class="text-info">(Para AR-Cartas no futuro)</small></label>
                            <input type="text" name="destinatarios[${i}][endereco][logradouro]"
                                class="form-control form-control-sm" placeholder="Rua, Número, Bairro, CEP, Cidade-UF">
                        </div>
                        <div class="col-md-1 text-end pt-4">
                            <button type="button" class="btn btn-light btn-sm border-0 rounded-circle shadow-sm"
                                onclick="this.closest('.destinatario-row').remove()" title="Remover">
                                <i class="fa-solid fa-times text-danger"></i>
                            </button>
                        </div>
                    </div>`;
                document.getElementById('listaDestinatarios').appendChild(row);
            }

            $(document).ready(function () {
                const empresaSelect = $('#empresa_id');
                const btnPuxar = $('#btnPuxarContatos');

                function togglePuxarBtn() {
                    if (empresaSelect.val()) {
                        btnPuxar.removeAttr('disabled');
                    } else {
                        btnPuxar.attr('disabled', 'disabled');
                    }
                }

                // Initialize state
                togglePuxarBtn();

                // Listen for change
                empresaSelect.on('change', togglePuxarBtn);

                btnPuxar.on('click', function () {
                    const empresaId = empresaSelect.val();
                    if (!empresaId) return;

                    btnPuxar.html('<i class="fa-solid fa-spinner fa-spin"></i>').attr('disabled', 'disabled');

                    $.get('/empresas/' + empresaId + '/contatos')
                        .done(function (data) {
                            if (data.length === 0) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Sem contatos',
                                    text: 'Esta empresa não possui contatos ativos cadastrados.',
                                    toast: true, position: 'top-end', showConfirmButton: false, timer: 3000
                                });
                                return;
                            }

                            // Limpar lista atual e recriar com os puxados
                            document.getElementById('listaDestinatarios').innerHTML = '';
                            destinatarioIndex = 0; // reset index

                            data.forEach(function (contato) {
                                adicionarDestinatarioPreenchido(contato.nome, contato.email, contato.telefone, contato.documento);
                            });

                            Swal.fire({
                                icon: 'success',
                                title: data.length + ' contatos adicionados!',
                                toast: true, position: 'top-end', showConfirmButton: false, timer: 3000
                            });
                        })
                        .fail(function () {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Falha ao buscar contatos da empresa.',
                                toast: true, position: 'top-end', showConfirmButton: false, timer: 3000
                            });
                        })
                        .always(function () {
                            btnPuxar.html('<i class="fa-solid fa-cloud-arrow-down"></i>').removeAttr('disabled');
                        });
                });
            });
        </script>
    @endpush
@endsection