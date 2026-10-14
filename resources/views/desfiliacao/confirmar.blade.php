<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação e Assinatura Eletrônica - Desfiliação Sindical</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        #canvas-assinatura {
            touch-action: none;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col justify-between">
    <!-- Header -->
    <header class="bg-slate-900 border-b border-slate-800 py-4 px-6 shadow-sm">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-md">
                    <i class="fa-solid fa-file-signature"></i>
                </div>
                <div>
                    <h1 class="text-white font-semibold text-lg leading-tight">Portal Sindical</h1>
                    <p class="text-slate-400 text-xs">Módulo de Desfiliação Digital</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-3xl w-full mx-auto px-4 py-8 flex-grow">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-emerald-700 via-teal-800 to-slate-900 p-6 md:p-8 text-white">
                <span class="inline-flex items-center gap-1.5 bg-emerald-500/30 text-emerald-100 text-xs font-semibold px-3 py-1 rounded-full border border-emerald-400/30 mb-3">
                    <i class="fa-solid fa-signature"></i> Etapa 2 de 2: Confirmação e Rubrica
                </span>
                <h2 class="text-2xl md:text-3xl font-bold tracking-tight">Assinatura Eletrônica do Requerimento</h2>
                <p class="mt-2 text-emerald-100 text-sm md:text-base leading-relaxed opacity-95">
                    Revise atentamente os dados informados e desenhe sua assinatura no campo abaixo para finalizar o processo de desfiliação.
                </p>
            </div>

            <div class="p-6 md:p-8 space-y-6">
                @if ($errors->any())
                    <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                        <div class="flex items-center gap-2 font-semibold mb-1">
                            <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                            Por favor, corrija os seguintes pontos:
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Modo Leitura: Dados Declarados -->
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-user-check text-emerald-600"></i> Dados Declarados na Solicitação
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="block text-xs text-slate-400 uppercase font-semibold">Trabalhador</span>
                            <span class="font-medium text-slate-800">{{ $desfiliacao->nome_completo }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-slate-400 uppercase font-semibold">CPF</span>
                            <span class="font-medium text-slate-800 font-mono">{{ $desfiliacao->cpf_formatado }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-slate-400 uppercase font-semibold">Empresa Empregadora</span>
                            <span class="font-medium text-slate-800">{{ $desfiliacao->empresa }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-slate-400 uppercase font-semibold">E-mail Cadastrado</span>
                            <span class="font-medium text-slate-800">{{ $desfiliacao->email }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-slate-400 uppercase font-semibold">Telefone/WhatsApp</span>
                            <span class="font-medium text-slate-800 font-mono">{{ $desfiliacao->telefone_formatado }}</span>
                        </div>
                    </div>
                </div>

                <!-- Formulário de Finalização -->
                <form action="{{ $urlFinalizar }}" method="POST" id="form-finalizar" class="space-y-6">
                    @csrf
                    <input type="hidden" name="assinatura_base64" id="assinatura_base64" value="{{ old('assinatura_base64') }}">

                    <!-- Canvas de Assinatura -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                                Desenhe sua Assinatura / Rubrica <span class="text-red-500">*</span>
                            </label>
                            <button type="button" id="btn-limpar"
                                class="text-xs font-medium text-red-600 hover:text-red-700 active:text-red-800 flex items-center gap-1 px-2.5 py-1 rounded bg-red-50 hover:bg-red-100 transition cursor-pointer">
                                <i class="fa-solid fa-eraser"></i> Limpar Assinatura
                            </button>
                        </div>

                        <div class="relative bg-white border-2 border-dashed border-slate-300 rounded-xl overflow-hidden focus-within:border-blue-500 transition shadow-inner">
                            <canvas id="canvas-assinatura" width="650" height="200"
                                class="w-full h-48 block cursor-crosshair bg-white"></canvas>
                            <div id="canvas-placeholder" class="absolute inset-0 flex items-center justify-center pointer-events-none text-slate-300 text-sm italic select-none">
                                <i class="fa-solid fa-pen mr-2 text-slate-300"></i> Use o mouse ou o dedo para assinar aqui
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Esta rubrica será impressa diretamente no Termo Oficial em PDF.</p>
                    </div>

                    <!-- Termo de Consentimento e Ciência -->
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="aceite_termos" id="aceite_termos" value="1" required
                                class="mt-1 w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500 cursor-pointer">
                            <span class="text-xs text-amber-900 leading-relaxed">
                                <strong>Declaração de Ciência e Consentimento:</strong> Declaro, sob as penas da lei, que sou o titular dos dados acima e manifesto expressamente meu desejo de desfiliação voluntária. Estou ciente de que é de minha <strong>exclusiva responsabilidade apresentar e protocolar a cópia do termo emitido junto ao RH da minha empresa</strong> para que cesse o desconto em folha, e autorizo o registro dos metadados de auditoria (data/hora, endereço IP e dispositivo) para fins probatórios (Lei Federal nº 14.063/2020).
                            </span>
                        </label>
                    </div>

                    <!-- Botão de Finalização -->
                    <div class="pt-2">
                        <button type="submit" id="btn-submeter"
                            class="w-full py-3.5 px-6 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold text-sm md:text-base shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 cursor-pointer">
                            <i class="fa-solid fa-check-circle"></i>
                            <span>Confirmar Assinatura e Concluir Desfiliação</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <p>Portal de Aplicativos Sindicais &copy; {{ date('Y') }} - Todos os direitos reservados.</p>
    </footer>

    <!-- JavaScript Vanilla para captura touch/mouse no HTML5 Canvas -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const canvas = document.getElementById('canvas-assinatura');
            const ctx = canvas.getContext('2d');
            const btnLimpar = document.getElementById('btn-limpar');
            const placeholder = document.getElementById('canvas-placeholder');
            const inputAssinatura = document.getElementById('assinatura_base64');
            const form = document.getElementById('form-finalizar');

            // Ajustar resolução interna do canvas para alta nitidez em retina/telas HD
            function ajustarCanvas() {
                const rect = canvas.getBoundingClientRect();
                const dpr = window.devicePixelRatio || 1;
                canvas.width = rect.width * dpr;
                canvas.height = rect.height * dpr;
                ctx.scale(dpr, dpr);
                ctx.lineWidth = 2.5;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.strokeStyle = '#0f172a';
            }

            ajustarCanvas();
            window.addEventListener('resize', ajustarCanvas);

            let desenhando = false;
            let temTraço = false;

            function getPos(e) {
                const rect = canvas.getBoundingClientRect();
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                return {
                    x: clientX - rect.left,
                    y: clientY - rect.top
                };
            }

            function iniciarTraço(e) {
                desenhando = true;
                temTraço = true;
                if (placeholder) placeholder.style.display = 'none';
                const pos = getPos(e);
                ctx.beginPath();
                ctx.moveTo(pos.x, pos.y);
            }

            function moverTraço(e) {
                if (!desenhando) return;
                if (e.cancelable) e.preventDefault(); // Previne scroll na tela touch
                const pos = getPos(e);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
            }

            function pararTraço() {
                if (desenhando) {
                    desenhando = false;
                    atualizarBase64();
                }
            }

            function limpar() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                temTraço = false;
                inputAssinatura.value = '';
                if (placeholder) placeholder.style.display = 'flex';
            }

            function atualizarBase64() {
                if (temTraço) {
                    inputAssinatura.value = canvas.toDataURL('image/png');
                }
            }

            // Eventos de Mouse (Desktop)
            canvas.addEventListener('mousedown', iniciarTraço);
            canvas.addEventListener('mousemove', moverTraço);
            window.addEventListener('mouseup', pararTraço);

            // Eventos Touch (Mobile / Tablet)
            canvas.addEventListener('touchstart', iniciarTraço, { passive: false });
            canvas.addEventListener('touchmove', moverTraço, { passive: false });
            window.addEventListener('touchend', pararTraço);

            btnLimpar.addEventListener('click', limpar);

            // Validação no Envio
            form.addEventListener('submit', function (e) {
                atualizarBase64();
                if (!inputAssinatura.value || !temTraço) {
                    e.preventDefault();
                    alert('Por favor, desenhe sua assinatura no campo indicado antes de prosseguir.');
                }
            });
        });
    </script>
</body>
</html>
