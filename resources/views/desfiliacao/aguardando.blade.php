<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação Enviada - Portal Sindical</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
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
    <main class="max-w-xl w-full mx-auto px-4 py-12 flex-grow flex items-center">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 text-center w-full">
            <div class="w-20 h-20 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl shadow-inner border border-blue-100">
                <i class="fa-regular fa-envelope-open"></i>
            </div>

            <h2 class="text-2xl font-bold text-slate-900 mb-2">Confirmação Enviada por E-mail!</h2>
            
            <p class="text-slate-600 text-sm leading-relaxed mb-6">
                Enviamos uma mensagem com o link de confirmação e assinatura eletrônica para:
                <br>
                <strong class="text-blue-700 font-semibold text-base mt-1 inline-block">{{ session('email', 'seu endereço de e-mail cadastrado') }}</strong>
            </p>

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 text-left mb-6 space-y-3 text-xs text-slate-700">
                <div class="flex items-start gap-2.5">
                    <i class="fa-solid fa-clock text-amber-500 text-sm mt-0.5"></i>
                    <div>
                        <strong>Prazo de Validade:</strong> O link enviado possui validade de <strong>24 horas</strong>. Após esse prazo, será necessário refazer a solicitação.
                    </div>
                </div>
                <div class="flex items-start gap-2.5">
                    <i class="fa-solid fa-magnifying-glass text-blue-500 text-sm mt-0.5"></i>
                    <div>
                        <strong>Não encontrou o e-mail?</strong> Verifique também sua pasta de <em>Spam</em> ou <em>Lixo Eletrônico</em>.
                    </div>
                </div>
                <div class="flex items-start gap-2.5">
                    <i class="fa-solid fa-pen-nib text-indigo-500 text-sm mt-0.5"></i>
                    <div>
                        <strong>Próximo Passo:</strong> Ao clicar no link, você poderá conferir todos os seus dados e desenhar sua rubrica diretamente na tela.
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <a href="{{ route('desfiliacao.create') }}" class="text-xs font-medium text-slate-500 hover:text-slate-800 transition">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Voltar à página inicial de solicitação
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <p>Portal de Aplicativos Sindicais &copy; {{ date('Y') }} - Todos os direitos reservados.</p>
    </footer>
</body>
</html>
