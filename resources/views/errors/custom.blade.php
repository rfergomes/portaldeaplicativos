<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Aviso do Sistema' }} - Portal Sindical</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col justify-between">
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

    <main class="max-w-md w-full mx-auto px-4 py-12 flex-grow flex items-center">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 text-center w-full">
            <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-5 text-2xl border border-amber-100">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-900 mb-2">{{ $title ?? 'Link Expirado' }}</h2>
            <p class="text-slate-600 text-sm leading-relaxed mb-6">{{ $message ?? 'O link acessado perdeu a validade ou foi adulterado.' }}</p>
            @if(isset($actionUrl))
                <a href="{{ $actionUrl }}" class="inline-flex items-center justify-center gap-2 py-3 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm transition shadow">
                    <span>{{ $actionText ?? 'Voltar ao Início' }}</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            @endif
        </div>
    </main>

    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <p>Portal de Aplicativos - TI Químicos Unificados Campinas e Região &copy; {{ date('Y') }}</p>
    </footer>
</body>
</html>
