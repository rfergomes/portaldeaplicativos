<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação de Desfiliação Sindical - Sindicato Químicos Unificados</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-100/80 text-slate-800 antialiased min-h-screen flex flex-col justify-between">
    <!-- Top Header Institucional -->
    <header class="bg-white border-t-4 border-red-600 border-b border-slate-200 py-3.5 px-4 sm:px-6 shadow-sm sticky top-0 z-20">
        <div class="max-w-4xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center space-x-3.5">
                <a href="{{ route('desfiliacao.create') }}" class="flex-shrink-0 group">
                    <img src="{{ asset('img/logo.jpg') }}" alt="Sindicato Químicos Unificados" class="h-14 sm:h-16 w-auto object-contain rounded border border-slate-200 shadow-sm group-hover:scale-105 transition-transform">
                </a>
                <div>
                    <h1 class="text-slate-900 font-extrabold text-lg sm:text-xl leading-tight tracking-tight">Sindicato Químicos Unificados</h1>
                    <p class="text-slate-500 text-[11px] sm:text-xs font-medium">
                        Campinas - CNPJ: 46.095.717/0001-65 &bull; Osasco - CNPJ: 73.066.656/0001-80
                    </p>
                </div>
            </div>
            <a href="{{ route('desfiliacao.validar') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg px-3.5 py-2 shadow-xs transition">
                <i class="fa-solid fa-shield-halved text-red-600"></i>
                <span>Verificar Autenticidade</span>
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-3xl w-full mx-auto px-4 py-8 flex-grow">
        <!-- Card Principal -->
        <div class="bg-white rounded-2xl shadow-md border border-slate-200 overflow-hidden mb-8">
            <!-- Header do Card com Vermelho Institucional -->
            <div class="bg-gradient-to-r from-red-700 via-red-600 to-red-800 p-6 md:p-8 text-white relative">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <span class="inline-flex items-center gap-1.5 bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full border border-white/20 backdrop-blur-xs">
                        <i class="fa-solid fa-scale-balanced"></i> Amparo na Lei Federal nº 14.063/2020
                    </span>
                    <span class="text-red-100 text-xs font-medium tracking-wide">
                        Atendimento Eletrônico Oficial
                    </span>
                </div>
                <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight">Requerimento de Desfiliação Sindical</h2>
                <p class="mt-2 text-red-50 text-sm md:text-base leading-relaxed opacity-95">
                    Preencha os campos abaixo para iniciar sua solicitação. Após o envio, você receberá um link individual no seu e-mail para conferir seus dados e realizar a assinatura eletrônica da rubrica.
                </p>
            </div>

            <!-- Form Body -->
            <form action="{{ route('desfiliacao.store') }}" method="POST" class="p-6 md:p-8 space-y-8">
                @csrf

                @if ($errors->any())
                    <div class="p-4 rounded-xl bg-red-50 border-l-4 border-red-600 text-red-800 text-sm shadow-xs">
                        <div class="flex items-center gap-2 font-bold mb-1.5 text-red-900">
                            <i class="fa-solid fa-circle-exclamation text-red-600"></i>
                            Por favor, verifique os campos destacados abaixo:
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Seção 1: Dados Pessoais (Inspirada na Ficha de Sindicalização) -->
                <div class="relative border-2 border-red-200 rounded-2xl p-5 md:p-6 pt-7 bg-white">
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-red-600 text-white font-bold text-xs uppercase tracking-wider px-5 py-1 rounded-full shadow-sm">
                        Dados Pessoais
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Nome Completo -->
                        <div class="md:col-span-2">
                            <label for="nome_completo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Nome Completo do Trabalhador <span class="text-red-600">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-red-500">
                                    <i class="fa-regular fa-user"></i>
                                </div>
                                <input type="text" name="nome_completo" id="nome_completo" required
                                    value="{{ old('nome_completo') }}"
                                    placeholder="Digite seu nome completo como consta nos documentos"
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm transition placeholder-slate-400">
                            </div>
                        </div>

                        <!-- CPF -->
                        <div class="md:col-span-2">
                            <label for="cpf" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                CPF <span class="text-red-600">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-red-500">
                                    <i class="fa-solid fa-id-card"></i>
                                </div>
                                <input type="text" name="cpf" id="cpf" required maxlength="14"
                                    value="{{ old('cpf') }}"
                                    placeholder="000.000.000-00"
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm transition placeholder-slate-400 font-mono">
                            </div>
                        </div>

                        <!-- Empresa Empregadora -->
                        <div class="md:col-span-2">
                            <label for="empresa" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Empresa Empregadora (Razão Social / Nome Fantasia) <span class="text-red-600">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-red-500">
                                    <i class="fa-solid fa-building"></i>
                                </div>
                                <input type="text" name="empresa" id="empresa" required
                                    value="{{ old('empresa') }}"
                                    placeholder="Nome da empresa onde você trabalha atualmente"
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm transition placeholder-slate-400">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seção 2: Contatos (Inspirada na Ficha de Sindicalização) -->
                <div class="relative border-2 border-red-200 rounded-2xl p-5 md:p-6 pt-7 bg-white">
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-red-600 text-white font-bold text-xs uppercase tracking-wider px-5 py-1 rounded-full shadow-sm">
                        Contatos
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- E-mail -->
                        <div>
                            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Seu E-mail <span class="text-red-600">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-red-500">
                                    <i class="fa-regular fa-envelope"></i>
                                </div>
                                <input type="email" name="email" id="email" required
                                    value="{{ old('email') }}"
                                    placeholder="exemplo@email.com"
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm transition placeholder-slate-400">
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">O link individual de assinatura eletrônica será enviado para este e-mail.</p>
                        </div>

                        <!-- Telefone / WhatsApp -->
                        <div>
                            <label for="telefone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Telefone / WhatsApp <span class="text-red-600">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-red-500">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </div>
                                <input type="text" name="telefone" id="telefone" required maxlength="15"
                                    value="{{ old('telefone') }}"
                                    placeholder="(00) 00000-0000"
                                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm transition placeholder-slate-400 font-mono">
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">Número para contato ou confirmação de suporte institucional.</p>
                        </div>
                    </div>
                </div>

                <!-- Seção 3: Manifestação Facultativa -->
                <div class="relative border-2 border-red-200 rounded-2xl p-5 md:p-6 pt-7 bg-white">
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-red-600 text-white font-bold text-xs uppercase tracking-wider px-5 py-1 rounded-full shadow-sm">
                        Manifestação Voluntária
                    </div>

                    <div>
                        <label for="motivo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Motivo da Desfiliação <span class="text-slate-400 font-normal lowercase">(facultativo)</span>
                        </label>
                        <textarea name="motivo" id="motivo" rows="3"
                            placeholder="Caso deseje, descreva brevemente o motivo da sua decisão"
                            class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm transition placeholder-slate-400">{{ old('motivo') }}</textarea>
                    </div>
                </div>

                <!-- Informação e Orientações -->
                <div class="bg-amber-50/80 border border-amber-200 rounded-xl p-4 flex gap-3 text-amber-900 text-xs leading-relaxed">
                    <i class="fa-solid fa-circle-info text-amber-600 text-base mt-0.5 flex-shrink-0"></i>
                    <div>
                        <strong>Orientações sobre as próximas etapas:</strong> Após clicar no botão abaixo, verifique sua caixa de entrada de e-mail (e também a pasta de spam/lixo eletrônico). Você terá 24 horas para acessar o link exclusivo, conferir os dados e desenhar sua rubrica de assinatura eletrônica na tela.
                    </div>
                </div>

                <!-- Botão de Envio com Vermelho Químicos Unificados -->
                <div class="pt-2">
                    <button type="submit"
                        class="w-full py-4 px-6 rounded-xl bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-bold text-base md:text-lg shadow-lg shadow-red-600/25 hover:shadow-xl hover:shadow-red-600/30 transition-all duration-150 flex items-center justify-center gap-2 cursor-pointer transform hover:-translate-y-0.5 active:translate-y-0">
                        <span>Enviar Solicitação e Gerar Link</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer Institucional -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-600">
        <div class="max-w-4xl mx-auto px-4 space-y-1">
            <p class="font-semibold text-slate-800">Sindicato Químicos Unificados &copy; {{ date('Y') }} - Regional Campinas e Osasco</p>
            <p class="text-slate-500 text-[11px]">Assinatura Eletrônica Avançada em conformidade com o Art. 5º da Lei Federal nº 14.063/2020.</p>
        </div>
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
