@extends('layouts.app')

@section('title', 'Criar Nova Demanda')

@push('styles')
    <style>
        .form-card {
            border-radius: 12px;
            border: none;
        }
        .btn-premium {
            background: linear-gradient(135deg, #033c5a 0%, #0b72a6 100%);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-premium:hover {
            background: linear-gradient(135deg, #0b72a6 0%, #033c5a 100%);
            color: #ffffff;
        }
        .checklist-item-row {
            display: flex;
            align-items: center;
            background: #f8fafc;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 8px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }
        .checklist-item-row:hover {
            border-color: #cbd5e1;
        }
        [data-bs-theme="dark"] .checklist-item-row {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
        }
        .dropzone-area {
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .dropzone-area:hover {
            border-color: #0b72a6;
            background: #eff6ff;
        }
        [data-bs-theme="dark"] .dropzone-area {
            border-color: rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.02);
        }
    </style>
@endpush

@section('content')
    <div class="mb-4">
        <a href="{{ route('demandas.index') }}" class="text-decoration-none text-muted">
            <i class="fa-solid fa-arrow-left me-1"></i> Voltar ao Painel
        </a>
        <h1 class="h3 mb-0 text-slate-800 fw-bold mt-2">Criar Nova Demanda</h1>
    </div>

    <div class="row">
        <div class="col-lg-9 col-12 mx-auto">
            <form method="POST" action="{{ route('demandas.store') }}" enctype="multipart/form-data" class="card form-card shadow-sm">
                @csrf
                <div class="card-body p-4">
                    <!-- Título -->
                    <div class="mb-3">
                        <label for="titulo" class="form-label fw-bold text-secondary">Título da Demanda <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('titulo') is-invalid @enderror" id="titulo" name="titulo" value="{{ old('titulo') }}" required placeholder="Ex: Ajustar fiação do ar condicionado na colônia">
                        @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Descrição -->
                    <div class="mb-4">
                        <label for="descricao" class="form-label fw-bold text-secondary">Descrição Detalhada <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('descricao') is-invalid @enderror" id="descricao" name="descricao" rows="5" required placeholder="Insira todos os detalhes necessários para a execução da tarefa...">{{ old('descricao') }}</textarea>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <!-- Prazo -->
                        <div class="col-md-6 col-12">
                            <label for="prazo" class="form-label fw-bold text-secondary">Prazo Limite de Execução</label>
                            <input type="datetime-local" class="form-control @error('prazo') is-invalid @enderror" id="prazo" name="prazo" value="{{ old('prazo') }}">
                            @error('prazo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Prioridade -->
                        <div class="col-md-6 col-12">
                            <label for="prioridade" class="form-label fw-bold text-secondary">Prioridade da Demanda <span class="text-danger">*</span></label>
                            <select class="form-select @error('prioridade') is-invalid @enderror" id="prioridade" name="prioridade" required>
                                <option value="baixa" {{ old('prioridade') == 'baixa' ? 'selected' : '' }}>Baixa</option>
                                <option value="media" {{ old('prioridade', 'media') == 'media' ? 'selected' : '' }}>Média</option>
                                <option value="alta" {{ old('prioridade') == 'alta' ? 'selected' : '' }}>Alta</option>
                                <option value="urgente" {{ old('prioridade') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                            </select>
                            @error('prioridade')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Responsável Seleção -->
                    <div class="card p-3 mb-4 border border-secondary-subtle">
                        <h6 class="fw-bold mb-3"><i class="fa-solid fa-user-check text-primary me-2"></i>Responsável pela Execução</h6>

                        <!-- Tipo Responsável -->
                        <div class="mb-3">
                            <label class="form-label d-block fw-semibold text-muted">Tipo de Responsável</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="tipo_responsavel" id="tipo_interno" value="usuario" {{ old('tipo_responsavel', 'usuario') == 'usuario' ? 'checked' : '' }}>
                                <label class="form-check-label" for="tipo_interno">Usuário do Sistema (Interno)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="tipo_responsavel" id="tipo_externo" value="externo" {{ old('tipo_responsavel') == 'externo' ? 'checked' : '' }}>
                                <label class="form-check-label" for="tipo_externo">Contato Externo (Aviso via WhatsApp)</label>
                            </div>
                        </div>

                        <!-- Div Usuário Interno -->
                        <div id="div_responsavel_interno" class="mb-2">
                            <div class="row align-items-end g-2">
                                <div class="col-md-9 col-12">
                                    <label for="responsavel_usuario_id" class="form-label small fw-bold text-secondary">Selecione o Usuário</label>
                                    <select class="form-select select2-enable" id="responsavel_usuario_id" name="responsavel_usuario_id" style="width: 100%;">
                                        <option value="">Selecione...</option>
                                        @foreach($usuarios as $user)
                                            <option value="{{ $user->id }}" {{ old('responsavel_usuario_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 col-12 text-end">
                                    <button type="button" class="btn btn-outline-secondary w-100" id="btn_atribuir_mim">
                                        <i class="fa-solid fa-user-gear me-1"></i> Atribuir a Mim
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Div Contato Externo -->
                        <div id="div_responsavel_externo" style="display: none;">
                            <!-- Busca rápida em contatos existentes -->
                            <div class="mb-3">
                                <label for="cliente_select" class="form-label small fw-bold text-secondary">Buscar em Contatos Cadastrados (Opcional)</label>
                                <select class="form-select select2-enable" id="cliente_select" style="width: 100%;">
                                    <option value="">Selecione um contato para preenchimento automático...</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" data-nome="{{ $cliente->nome }}" data-telefone="{{ $cliente->telefone }}" data-email="{{ $cliente->email }}">
                                            {{ $cliente->nome }} ({{ $cliente->telefone ?? 'Sem Telefone' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6 col-12">
                                    <label for="responsavel_nome" class="form-label small fw-bold text-secondary">Nome do Responsável <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="responsavel_nome" name="responsavel_nome" value="{{ old('responsavel_nome') }}" placeholder="Nome do prestador/contato">
                                </div>
                                <div class="col-md-6 col-12">
                                    <label for="responsavel_telefone" class="form-label small fw-bold text-secondary">Telefone WhatsApp <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control cell-mask" id="responsavel_telefone" name="responsavel_telefone" value="{{ old('responsavel_telefone') }}" placeholder="(99) 99999-9999">
                                    <small class="text-muted text-xs">Utilizado para disparar a notificação de nova demanda.</small>
                                </div>
                                <div class="col-12 mt-2">
                                    <label for="responsavel_email" class="form-label small fw-bold text-secondary">Email do Responsável (Opcional)</label>
                                    <input type="email" class="form-control" id="responsavel_email" name="responsavel_email" value="{{ old('responsavel_email') }}" placeholder="prestador@provedor.com">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Checklist (Sub-tarefas) -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary"><i class="fa-solid fa-list-check text-success me-2"></i>Checklist de Sub-tarefas (Opcional)</label>
                        <div class="d-flex gap-2 mb-3">
                            <input type="text" class="form-control" id="checklist_item_input" placeholder="Nova tarefa (ex: Comprar material)">
                            <button type="button" class="btn btn-outline-success" id="btn_add_checklist_item">
                                <i class="fa-solid fa-plus"></i> Adicionar
                            </button>
                        </div>
                        <div id="checklist_container">
                            <!-- Inserção via Javascript -->
                        </div>
                    </div>

                    <!-- Anexos de Arquivos -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary"><i class="fa-solid fa-paperclip text-muted me-2"></i>Anexar Arquivos (Opcional)</label>
                        <div class="dropzone-area" onclick="document.getElementById('anexos').click()">
                            <i class="fa-solid fa-cloud-arrow-up fa-2x text-muted mb-2"></i>
                            <p class="mb-0 small fw-bold text-muted">Clique ou arraste arquivos para anexar</p>
                            <span class="text-muted text-xs d-block">Tamanho máximo: 10MB por arquivo (PDF, imagens, planilhas)</span>
                            <input type="file" class="d-none" id="anexos" name="anexos[]" multiple onchange="showAttachedFiles(this)">
                        </div>
                        <div id="anexos_list" class="mt-2 d-flex flex-wrap gap-2"></div>
                    </div>

                    <div class="divider border-bottom mb-4"></div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('demandas.index') }}" class="btn btn-light px-4">Cancelar</a>
                        <button type="submit" class="btn btn-premium px-5 py-2 shadow-sm">
                            <i class="fa-solid fa-paper-plane me-2"></i>Salvar e Notificar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- InputMask & Select2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2
            $('.select2-enable').select2({
                theme: 'bootstrap-5',
                placeholder: 'Selecione...'
            });

            // Máscara de Telefone
            $('.cell-mask').inputmask({
                mask: ['(99) 9999-9999', '(99) 99999-9999'],
                keepStatic: true,
                removeMaskOnSubmit: true
            });

            // Toggle Tipo Responsável
            $('input[name="tipo_responsavel"]').change(function() {
                if (this.value === 'usuario') {
                    $('#div_responsavel_interno').slideDown();
                    $('#div_responsavel_externo').slideUp();
                    // Limpar campos de validação requerida no outro campo
                    $('#responsavel_nome, #responsavel_telefone').prop('required', false);
                    $('#responsavel_usuario_id').prop('required', true);
                } else {
                    $('#div_responsavel_interno').slideUp();
                    $('#div_responsavel_externo').slideDown();
                    $('#responsavel_usuario_id').prop('required', false);
                    $('#responsavel_nome, #responsavel_telefone').prop('required', true);
                }
            });

            // Trigger inicial se houve erro de validação
            $('input[name="tipo_responsavel"]:checked').trigger('change');

            // Atribuir a mim mesmo
            $('#btn_atribuir_mim').click(function() {
                var currentUserId = "{{ auth()->id() }}";
                $('#responsavel_usuario_id').val(currentUserId).trigger('change');
            });

            // Preenchimento de Contatos Cadastrados
            $('#cliente_select').change(function() {
                var option = $(this).find(':selected');
                if (option.val()) {
                    $('#responsavel_nome').val(option.data('nome'));
                    $('#responsavel_telefone').val(option.data('telefone')).trigger('input');
                    $('#responsavel_email').val(option.data('email'));
                }
            });

            // Checklist Dinâmico
            $('#btn_add_checklist_item').click(function() {
                var input = $('#checklist_item_input');
                var text = input.val().trim();
                if (text !== '') {
                    addChecklistItem(text);
                    input.val('').focus();
                }
            });

            // Permitir pressionar enter para adicionar item no checklist
            $('#checklist_item_input').keypress(function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#btn_add_checklist_item').click();
                }
            });
        });

        // Função para Adicionar Item no Checklist
        function addChecklistItem(text) {
            var container = $('#checklist_container');
            var index = container.children().length;
            
            var row = $(`
                <div class="checklist-item-row" id="chk_row_${index}">
                    <span class="flex-grow-1"><i class="fa-solid fa-list-check text-muted me-2"></i>${text}</span>
                    <input type="hidden" name="checklist_items[]" value="${text}">
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeChecklistItem(${index})">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            `);
            
            container.append(row);
        }

        // Remover Item do Checklist
        function removeChecklistItem(index) {
            $(`#chk_row_${index}`).remove();
        }

        // Mostrar Lista de Arquivos Anexados
        function showAttachedFiles(input) {
            var list = $('#anexos_list');
            list.empty();
            
            if (input.files.length > 0) {
                for (var i = 0; i < input.files.length; i++) {
                    var file = input.files[i];
                    var size = (file.size / 1024 / 1024).toFixed(2);
                    list.append(`
                        <span class="badge bg-secondary p-2 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-file-invoice"></i> ${file.name} (${size} MB)
                        </span>
                    `);
                }
            }
        }
    </script>
@endpush
