<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação de Desfiliação Sindical - Portal Sindical</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col justify-between">
    <!-- Top Header -->
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
            <a href="{{ route('desfiliacao.validar') }}" class="text-xs font-medium text-blue-400 hover:text-blue-300 flex items-center gap-1.5 transition">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Verificar Autenticidade</span>
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-3xl w-full mx-auto px-4 py-8 flex-grow">
        <!-- Intro Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-blue-700 via-blue-800 to-indigo-900 p-6 md:p-8 text-white">
                <span class="inline-flex items-center gap-1.5 bg-blue-500/30 text-blue-100 text-xs font-semibold px-3 py-1 rounded-full border border-blue-400/30 mb-3">
                    <i class="fa-solid fa-scale-balanced"></i> Amparo na Lei Federal nº 14.063/2020
                </span>
                <h2 class="text-2xl md:text-3xl font-bold tracking-tight">Requerimento de Desfiliação Sindical</h2>
                <p class="mt-2 text-blue-100 text-sm md:text-base leading-relaxed opacity-95">
                    Preencha o formulário abaixo para iniciar a sua solicitação. Após o envio, você receberá um link individual no seu e-mail para conferir seus dados e realizar a assinatura eletrônica da rubrica.
                </p>
            </div>

            <!-- Form Body -->
            <form action="{{ route('desfiliacao.store') }}" method="POST" class="p-6 md:p-8 space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                        <div class="flex items-center gap-2 font-semibold mb-1">
                            <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                            Por favor, corrija os erros abaixo:
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nome Completo -->
                    <div class="md:col-span-2">
                        <label for="nome_completo" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nome Completo do Trabalhador <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-regular fa-user"></i>
                            </div>
                            <input type="text" name="nome_completo" id="nome_completo" required
                                value="{{ old('nome_completo') }}"
                                placeholder="Digite seu nome completo como consta nos documentos"
                                class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition placeholder-slate-400">
                        </div>
                    </div>

                    <!-- CPF -->
                    <div>
                        <label for="cpf" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            CPF <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-id-card"></i>
                            </div>
                            <input type="text" name="cpf" id="cpf" required maxlength="14"
                                value="{{ old('cpf') }}"
                                placeholder="000.000.000-00"
                                class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition placeholder-slate-400 font-mono">
                        </div>
                    </div>

                    <!-- Matrícula -->
                    <div>
                        <label for="matricula" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Matrícula Funcional <span class="text-slate-400 font-normal">(Opcional)</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-hashtag"></i>
                            </div>
                            <input type="text" name="matricula" id="matricula"
                                value="{{ old('matricula') }}"
                                placeholder="Nº da matrícula na empresa (se souber)"
                                class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition placeholder-slate-400">
                        </div>
                    </div>

                    <!-- Empresa Empregadora -->
                    <div class="md:col-span-2">
                        <label for="empresa" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Empresa Empregadora (Razão Social / Nome Fantasia) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-building"></i>
                            </div>
                            <input type="text" name="empresa" id="empresa" required
                                value="{{ old('empresa') }}"
                                placeholder="Nome da empresa onde você trabalha atualmente"
                                class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition placeholder-slate-400">
                        </div>
                    </div>

                    <!-- E-mail -->
                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Seu E-mail <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-regular fa-envelope"></i>
                            </div>
                            <input type="email" name="email" id="email" required
                                value="{{ old('email') }}"
                                placeholder="exemplo@email.com"
                                class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition placeholder-slate-400">
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">O link de assinatura eletrônica será enviado para este e-mail.</p>
                    </div>

                    <!-- Telefone / WhatsApp -->
                    <div>
                        <label for="telefone" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Telefone / WhatsApp <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-brands fa-whatsapp"></i>
                            </div>
                            <input type="text" name="telefone" id="telefone" required maxlength="15"
                                value="{{ old('telefone') }}"
                                placeholder="(00) 00000-0000"
                                class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition placeholder-slate-400 font-mono">
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Para contato de suporte ou confirmação funcional.</p>
                    </div>

                    <!-- Motivo -->
                    <div class="md:col-span-2">
                        <label for="motivo" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Motivo da Desfiliação <span class="text-slate-400 font-normal">(Facultativo)</span>
                        </label>
                        <textarea name="motivo" id="motivo" rows="3"
                            placeholder="Caso deseje, descreva brevemente o motivo da sua decisão"
                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition placeholder-slate-400">{{ old('motivo') }}</textarea>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex gap-3 text-amber-800 text-xs leading-relaxed">
                    <i class="fa-solid fa-circle-info text-amber-600 text-base mt-0.5"></i>
                    <div>
                        <strong>Etapas do processo:</strong> Após clicar no botão abaixo, verifique sua caixa de entrada de e-mail (e a pasta de spam/lixo eletrônico). Você terá 24 horas para acessar o link exclusivo, conferir os dados e desenhar sua rubrica na tela.
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit"
                        class="w-full py-3.5 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-sm md:text-base shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 cursor-pointer">
                        <span>Enviar Solicitação e Gerar Link</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <p>Portal de Aplicativos Sindicais &copy; {{ date('Y') }} - Todos os direitos reservados.</p>
        <p class="mt-1 text-slate-400">Assinatura Eletrônica em conformidade com o Art. 5º da Lei 14.063/2020.</p>
    </footer>

    <!-- JavaScript para máscaras de CPF e Telefone -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cpfInput = document.getElementById('cpf');
            const telInput = document.getElementById('telefone');

            if (cpfInput) {
                cpfInput.addEventListener('input', function (e) {
                    let v = e.target.value.replace(/\D/g, '');
                    if (v.length > 11) v = v.slice(0, 11);
                    if (v.length > 9) {
                        v = v.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, '$1.$2.$3-$4');
                    } else if (v.length > 6) {
                        v = v.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
                    } else if (v.length > 3) {
                        v = v.replace(/(\d{3})(\d{1,3})/, '$1.$2');
                    }
                    e.target.value = v;
                });
            }

            if (telInput) {
                telInput.addEventListener('input', function (e) {
                    let v = e.target.value.replace(/\D/g, '');
                    if (v.length > 11) v = v.slice(0, 11);
                    if (v.length > 10) {
                        v = v.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
                    } else if (v.length > 6) {
                        v = v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                    } else if (v.length > 2) {
                        v = v.replace(/(\d{2})(\d{0,5})/, '($1) $2');
                    } else if (v.length > 0) {
                        v = v.replace(/(\d*)/, '($1');
                    }
                    e.target.value = v;
                });
            }
        });
    </script>
</body>
</html>
