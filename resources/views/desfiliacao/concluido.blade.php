<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desfiliação Concluída com Sucesso - Portal Sindical</title>
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
                <div class="w-10 h-10 rounded-lg bg-emerald-600 flex items-center justify-center text-white font-bold text-lg shadow-md">
                    <i class="fa-solid fa-file-circle-check"></i>
                </div>
                <div>
                    <h1 class="text-white font-semibold text-lg leading-tight">Portal Sindical</h1>
                    <p class="text-slate-400 text-xs">Módulo de Desfiliação Digital</p>
                </div>
            </div>
            <a href="{{ route('desfiliacao.validar', ['hash' => $desfiliacao->termo_hash]) }}" class="text-xs font-medium text-emerald-400 hover:text-emerald-300 flex items-center gap-1.5 transition">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Verificar Autenticidade</span>
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-2xl w-full mx-auto px-4 py-10 flex-grow flex items-center">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 md:p-8 text-center w-full">
            <div class="w-20 h-20 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-5 text-3xl shadow-inner border border-emerald-100">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <h2 class="text-2xl md:text-3xl font-bold text-slate-900 mb-2">Desfiliação Concluída com Sucesso!</h2>
            <p class="text-slate-600 text-sm leading-relaxed mb-6">
                O seu requerimento formal de desfiliação foi devidamente assinado eletronicamente e registrado em nossa base com trilha probatória de auditoria.
            </p>

            <!-- ALERTA DE DESTAQUE REQUISITADO PELO USUÁRIO (UX) -->
            <div class="bg-amber-50 border-2 border-amber-400 rounded-2xl p-5 md:p-6 text-left mb-6 shadow-sm">
                <div class="flex items-start gap-3.5">
                    <div class="text-amber-500 text-2xl mt-0.5">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h3 class="text-sm md:text-base font-bold text-amber-900 uppercase tracking-wide mb-1">
                            Atenção Importante sobre seu Desconto em Folha:
                        </h3>
                        <p class="text-xs md:text-sm text-amber-800 leading-relaxed font-medium">
                            A sua desfiliação foi processada junto ao Sindicato. Para suspender o desconto em folha, faça o download do documento gerado e entregue uma cópia impressa ou digitalizada ao RH da sua empresa.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Botão de Download do PDF Oficial -->
            <div class="mb-6">
                <a href="{{ route('desfiliacao.download', ['id' => $desfiliacao->id]) }}"
                    class="w-full py-4 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-base shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-file-pdf text-xl"></i>
                    <span>Baixar Termo de Desfiliação Assinado (PDF)</span>
                </a>
            </div>

            <!-- Dados Probatórios de Validação -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-left text-xs text-slate-700 space-y-2.5 mb-6">
                <div class="flex justify-between items-center border-b border-slate-200 pb-2">
                    <span class="text-slate-500 font-semibold uppercase">Requerente</span>
                    <span class="font-medium text-slate-900">{{ $desfiliacao->nome_completo }}</span>
                </div>
                <div class="flex justify-between items-center border-b border-slate-200 pb-2">
                    <span class="text-slate-500 font-semibold uppercase">CPF</span>
                    <span class="font-medium text-slate-900 font-mono">{{ $desfiliacao->cpf_formatado }}</span>
                </div>
                <div class="flex justify-between items-center border-b border-slate-200 pb-2">
                    <span class="text-slate-500 font-semibold uppercase">Empresa</span>
                    <span class="font-medium text-slate-900">{{ $desfiliacao->empresa }}</span>
                </div>
                <div class="flex justify-between items-center border-b border-slate-200 pb-2">
                    <span class="text-slate-500 font-semibold uppercase">Data e Hora da Assinatura</span>
                    <span class="font-medium text-slate-900">{{ $desfiliacao->confirmado_em ? $desfiliacao->confirmado_em->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s') }}</span>
                </div>
                <div>
                    <span class="block text-slate-500 font-semibold uppercase mb-1">Código Criptográfico de Autenticidade (SHA-256)</span>
                    <div class="bg-white p-2 rounded border border-slate-200 font-mono text-[11px] text-blue-700 break-all select-all">
                        {{ $desfiliacao->termo_hash }}
                    </div>
                </div>
            </div>

            <div>
                <a href="{{ route('desfiliacao.create') }}" class="text-xs font-medium text-slate-500 hover:text-slate-800 transition">
                    <i class="fa-solid fa-house mr-1"></i> Retornar à página inicial
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
