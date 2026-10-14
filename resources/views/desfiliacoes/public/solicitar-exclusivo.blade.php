<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação de Desfiliação Sindical | Link Exclusivo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- jQuery & Select2 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        #canvas-assinatura { touch-action: none; cursor: crosshair; }

        /* Custom Select2 Tailwind Styling */
        .select2-container {
            width: 100% !important;
        }
        .select2-container--default .select2-selection--single {
            height: 42px;
            padding: 6px 14px;
            border-radius: 0.75rem; /* rounded-xl */
            border: 1px solid #cbd5e1; /* border-slate-300 */
            background-color: #ffffff;
            display: flex;
            align-items: center;
            transition: all 0.2s ease-in-out;
        }
        .select2-container--default .select2-selection--single:focus,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #2563eb; /* blue-600 */
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
            outline: none;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1e293b;
            font-size: 0.875rem;
            padding-left: 0;
            line-height: normal;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
            right: 12px;
        }
        .select2-dropdown {
            border-radius: 0.75rem;
            border: 1px solid #cbd5e1;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            font-size: 0.875rem;
            z-index: 50;
        }
        .select2-container--default .select2-search--dropdown {
            padding: 8px;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            padding: 6px 10px;
            outline: none;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #2563eb;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #2563eb;
            color: #ffffff;
        }
        .select2-container--default .select2-results__option--selected {
            background-color: #eff6ff;
            color: #1d4ed8;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col justify-between">
    <!-- Header -->
    <header class="bg-slate-900 border-b border-slate-800 py-4 px-6 shadow-sm">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-md">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h1 class="text-white font-semibold text-lg leading-tight">Sindicato Químicos Unificados</h1>
                    <p class="text-slate-400 text-xs">Atendimento Digital &bull; Link Exclusivo</p>
                </div>
            </div>
            <div class="hidden sm:flex items-center text-xs text-emerald-400 bg-emerald-950/60 border border-emerald-800 px-3 py-1.5 rounded-full">
                <i class="fa-solid fa-lock me-1.5"></i> Conexão Segura e Criptografada
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-3xl w-full mx-auto px-4 py-8 flex-grow">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8">
            <!-- Hero Header -->
            <div class="bg-gradient-to-r from-blue-700 via-indigo-800 to-slate-900 p-6 md:p-8 text-white">
                <span class="inline-flex items-center gap-1.5 bg-blue-500/30 text-blue-100 text-xs font-semibold px-3 py-1 rounded-full border border-blue-400/30 mb-3">
                    <i class="fa-solid fa-link"></i> Atendimento Individualizado
                </span>
                <h2 class="text-2xl md:text-3xl font-bold tracking-tight">Requerimento de Desfiliação Sindical</h2>
                <p class="mt-2 text-blue-100 text-sm md:text-base leading-relaxed opacity-95">
                    Preencha os dados abaixo, declare a ciência dos termos da LGPD e assine digitalmente sua solicitação. O processo é unificado e rápido.
                </p>
                <div class="mt-4 pt-3 border-t border-blue-500/30 flex flex-wrap items-center justify-between text-xs text-blue-200 gap-2">
                    <span><i class="fa-solid fa-clock me-1"></i> Válido até: <strong>{{ $link->expira_em->format('d/m/Y \à\s H:i') }}</strong></span>
                    <span><i class="fa-solid fa-fingerprint me-1"></i> Uso Único e Nominal</span>
                </div>
            </div>

            <div class="p-6 md:p-8 space-y-6">
                @if ($errors->any())
                    <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                        <div class="flex items-center gap-2 font-semibold mb-1">
                            <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                            Atenção aos seguintes campos:
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('desfiliacao.solicitar.submit', $token) }}" method="POST" id="form-desfiliacao" class="space-y-6">
                    @csrf
                    <input type="hidden" name="assinatura_base64" id="assinatura_base64" value="{{ old('assinatura_base64') }}">

                    <!-- Dados Cadastrais -->
                    <div class="space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                            <i class="fa-solid fa-id-card text-blue-600"></i> 1. Dados Pessoais do Trabalhador
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label for="nome_completo" class="block text-xs font-semibold uppercase text-slate-700 mb-1">
                                    Nome Completo <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nome_completo" id="nome_completo" required
                                       value="{{ old('nome_completo', $link->destinatario_nome) }}"
                                       placeholder="Digite seu nome completo conforme documento"
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none transition">
                            </div>

                            <div>
                                <label for="cpf" class="block text-xs font-semibold uppercase text-slate-700 mb-1">
                                    CPF <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="cpf" id="cpf" required
                                       value="{{ old('cpf') }}"
                                       placeholder="000.000.000-00"
                                       maxlength="14"
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none transition">
                            </div>

                            <div>
                                <label for="empresa" class="block text-xs font-semibold uppercase text-slate-700 mb-1">
                                    Empresa Empregadora <span class="text-red-500">*</span>
                                </label>
                                <select name="empresa" id="empresa" required class="w-full">
                                    <option value="">Selecione ou digite o nome da empresa</option>
                                    @if(old('empresa') && isset($empresas) && !$empresas->contains('razao_social', old('empresa')))
                                        <option value="{{ old('empresa') }}" selected>{{ old('empresa') }}</option>
                                    @endif
                                    @if(isset($empresas))
                                        @foreach($empresas as $emp)
                                            @php
                                                $rotulo = $emp->razao_social;
                                                if ($emp->nome_fantasia && $emp->nome_fantasia !== $emp->razao_social) {
                                                    $rotulo .= ' (' . $emp->nome_fantasia . ')';
                                                }
                                                if ($emp->cnpj) {
                                                    $rotulo .= ' - CNPJ: ' . $emp->cnpj;
                                                }
                                            @endphp
                                            <option value="{{ $emp->razao_social }}" {{ old('empresa') == $emp->razao_social ? 'selected' : '' }}>
                                                {{ $rotulo }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <span class="text-[11px] text-slate-400">Busque por Razão Social, Fantasia ou digite se sua empresa não estiver listada.</span>
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-semibold uppercase text-slate-700 mb-1">
                                    E-mail de Contato <span class="text-red-500">*</span>
                                </label>
                                <input type="email" name="email" id="email" required
                                       value="{{ old('email') }}"
                                       placeholder="seu.email@exemplo.com"
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none transition">
                                <span class="text-[11px] text-slate-400">Você receberá a cópia homologada do termo neste e-mail.</span>
                            </div>

                            <div>
                                <label for="telefone" class="block text-xs font-semibold uppercase text-slate-700 mb-1">
                                    Telefone / WhatsApp <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="telefone" id="telefone" required
                                       value="{{ old('telefone', $link->destinatario_contato) }}"
                                       placeholder="(00) 00000-0000"
                                       maxlength="15"
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none transition">
                            </div>

                            <div class="md:col-span-2">
                                <label for="motivo" class="block text-xs font-semibold uppercase text-slate-700 mb-1">
                                    Motivo da Desfiliação <span class="text-slate-400 font-normal">(opcional)</span>
                                </label>
                                <textarea name="motivo" id="motivo" rows="2"
                                          placeholder="Se desejar, descreva brevemente a razão da sua solicitação"
                                          class="w-full px-4 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 outline-none transition">{{ old('motivo') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Termo LGPD -->
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-emerald-600"></i> 2. Termo de Tratamento de Dados (LGPD)
                        </h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Em cumprimento à Lei Federal nº 13.709/2018 (Lei Geral de Proteção de Dados Pessoais - LGPD), declaramos que os dados pessoais informados neste requerimento serão utilizados estritamente para a formalização do ato de desfiliação, homologação cadastral perante a entidade sindical e notificação da fonte pagadora empregadora para suspensão dos descontos estatutários, garantida a segurança, integridade e não compartilhamento com terceiros não autorizados.
                        </p>
                        <div class="pt-2">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="aceite_lgpd" value="1" id="aceite_lgpd" required
                                       {{ old('aceite_lgpd') ? 'checked' : '' }}
                                       class="mt-0.5 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-xs text-slate-700 font-medium">
                                    Declaro que li e concordo com os termos de tratamento dos meus dados pessoais para fins de desfiliação sindical e notificação ao empregador. <span class="text-red-500">*</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Canvas de Assinatura Eletrônica -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                                <i class="fa-solid fa-pen-nib text-blue-600"></i> 3. Assinatura Manuscrita / Rubrica <span class="text-red-500">*</span>
                            </h3>
                            <button type="button" id="btn-limpar" class="text-xs text-red-600 hover:text-red-800 font-semibold flex items-center gap-1 transition">
                                <i class="fa-solid fa-rotate-left"></i> Limpar Assinatura
                            </button>
                        </div>

                        <div class="border-2 border-dashed border-slate-300 rounded-2xl p-2 bg-slate-50/50 hover:border-blue-400 transition">
                            <canvas id="canvas-assinatura" class="w-full h-44 bg-white rounded-xl border border-slate-200 shadow-inner"></canvas>
                        </div>
                        <p class="text-xs text-slate-400 flex items-center gap-1">
                            <i class="fa-solid fa-info-circle text-blue-500"></i>
                            Desenhe sua rubrica no quadro acima usando o mouse (computador) ou a ponta do dedo (celular).
                        </p>
                    </div>

                    <!-- Botão de Envio -->
                    <div class="pt-4 border-t border-slate-200">
                        <button type="submit" id="btn-submeter" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 px-6 rounded-xl shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 text-base">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>Enviar Solicitação de Desfiliação</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer class="py-4 text-center text-xs text-slate-400 border-t border-slate-200 bg-white">
        Sindicato Químicos Unificados &bull; Segurança e Validade Jurídica (Lei 14.063/2020)
    </footer>

    <!-- Scripts de Interação do Canvas e Máscaras -->
    <script>
        $(document).ready(function () {
            // Inicialização do Select2 com busca de empresas e criação dinâmica (tags)
            $('#empresa').select2({
                placeholder: 'Selecione ou digite o nome da empresa',
                allowClear: true,
                tags: true,
                width: '100%',
                language: {
                    noResults: function () {
                        return "Nenhuma empresa cadastrada encontrada (tecle Enter para usar o nome digitado)";
                    }
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            // Máscara de CPF
            const cpfInput = document.getElementById('cpf');
            cpfInput.addEventListener('input', function (e) {
                let v = e.target.value.replace(/\D/g, '');
                if (v.length > 11) v = v.slice(0, 11);
                v = v.replace(/(\d{3})(\d)/, '$1.$2');
                v = v.replace(/(\d{3})(\d)/, '$1.$2');
                v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                e.target.value = v;
            });

            // Máscara de Telefone
            const telInput = document.getElementById('telefone');
            telInput.addEventListener('input', function (e) {
                let v = e.target.value.replace(/\D/g, '');
                if (v.length > 11) v = v.slice(0, 11);
                if (v.length > 10) {
                    v = v.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
                } else if (v.length > 5) {
                    v = v.replace(/^(\d{2})(\d{4})(\d{0,4})$/, '($1) $2-$3');
                } else if (v.length > 2) {
                    v = v.replace(/^(\d{2})(\d{0,5})$/, '($1) $2');
                }
                e.target.value = v;
            });

            // Configuração do Canvas de Assinatura
            const canvas = document.getElementById('canvas-assinatura');
            const ctx = canvas.getContext('2d');
            const hiddenInput = document.getElementById('assinatura_base64');
            const btnLimpar = document.getElementById('btn-limpar');
            const form = document.getElementById('form-desfiliacao');
            const btnSubmeter = document.getElementById('btn-submeter');

            let desenhando = false;
            let temAssinatura = false;

            function ajustarResolucao() {
                const rect = canvas.getBoundingClientRect();
                const dpr = window.devicePixelRatio || 1;
                canvas.width = rect.width * dpr;
                canvas.height = rect.height * dpr;
                ctx.scale(dpr, dpr);
                ctx.strokeStyle = '#0f172a';
                ctx.lineWidth = 2.5;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
            }

            ajustarResolucao();
            window.addEventListener('resize', ajustarResolucao);

            function getCoordenadas(e) {
                const rect = canvas.getBoundingClientRect();
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                return {
                    x: clientX - rect.left,
                    y: clientY - rect.top
                };
            }

            function iniciarDesenho(e) {
                e.preventDefault();
                desenhando = true;
                temAssinatura = true;
                const { x, y } = getCoordenadas(e);
                ctx.beginPath();
                ctx.moveTo(x, y);
            }

            function desenhar(e) {
                if (!desenhando) return;
                e.preventDefault();
                const { x, y } = getCoordenadas(e);
                ctx.lineTo(x, y);
                ctx.stroke();
            }

            function finalizarDesenho(e) {
                if (!desenhando) return;
                desenhando = false;
                hiddenInput.value = canvas.toDataURL('image/png');
            }

            canvas.addEventListener('mousedown', iniciarDesenho);
            canvas.addEventListener('mousemove', desenhar);
            window.addEventListener('mouseup', finalizarDesenho);

            canvas.addEventListener('touchstart', iniciarDesenho, { passive: false });
            canvas.addEventListener('touchmove', desenhar, { passive: false });
            window.addEventListener('touchend', finalizarDesenho);

            btnLimpar.addEventListener('click', function () {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                hiddenInput.value = '';
                temAssinatura = false;
            });

            form.addEventListener('submit', function (e) {
                if (!temAssinatura && !hiddenInput.value) {
                    e.preventDefault();
                    alert('Por favor, desenhe sua assinatura no quadro antes de enviar.');
                    canvas.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return false;
                }

                btnSubmeter.disabled = true;
                btnSubmeter.classList.add('opacity-75', 'cursor-not-allowed');
                btnSubmeter.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>Enviando solicitação...</span>';
            });
        });
    </script>
</body>
</html>
