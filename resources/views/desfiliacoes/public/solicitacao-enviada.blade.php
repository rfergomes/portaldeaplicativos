<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação Enviada com Sucesso | Sindicato Químicos Unificados</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-100/80 text-slate-800 antialiased min-h-screen flex flex-col justify-between">
    <!-- Header Institucional com Logotipo -->
    <header class="bg-white border-t-4 border-red-600 border-b border-slate-200 py-3.5 px-4 sm:px-6 shadow-sm sticky top-0 z-20">
        <div class="max-w-4xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center space-x-3.5">
                <div class="flex-shrink-0">
                    <img src="{{ asset('img/logo.jpg') }}" alt="Sindicato Químicos Unificados" class="h-12 sm:h-14 w-auto object-contain rounded border border-slate-200 shadow-sm">
                </div>
                <div>
                    <h1 class="text-slate-900 font-extrabold text-lg sm:text-xl leading-tight tracking-tight">Sindicato Químicos Unificados</h1>
                    <p class="text-slate-500 text-[11px] sm:text-xs font-medium">
                        Regional Campinas, Osasco e Região &bull; Atendimento Digital
                    </p>
                </div>
            </div>
            <div class="flex items-center text-xs text-emerald-800 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-full font-medium shadow-xs">
                <i class="fa-solid fa-circle-check text-emerald-600 me-1.5"></i> Requerimento Protocolado
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-2xl w-full mx-auto px-4 py-12 flex-grow">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden text-center p-8 md:p-10 space-y-6">
            <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto shadow-inner">
                <i class="fa-solid fa-file-circle-check text-4xl"></i>
            </div>

            <div>
                <span class="inline-flex items-center gap-1.5 bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full mb-2">
                    Protocolo #{{ $desfiliacao->id }}
                </span>
                <h2 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight">
                    Solicitação Enviada com Sucesso!
                </h2>
                <p class="text-slate-600 text-sm md:text-base mt-2 leading-relaxed">
                    Olá <strong>{{ $desfiliacao->nome_completo }}</strong>, seu requerimento de desfiliação com rubrica eletrônica foi registrado e está na fila de triagem da diretoria.
                </p>
            </div>

            <!-- Resumo das Próximas Etapas -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 text-left text-xs md:text-sm space-y-3">
                <h4 class="font-bold text-slate-700 uppercase tracking-wider text-[11px] flex items-center gap-2">
                    <i class="fa-solid fa-timeline text-blue-600"></i> Próximos Passos do Procedimento:
                </h4>
                <ol class="list-decimal list-inside space-y-2 text-slate-600">
                    <li><strong>Conferência Cadastral:</strong> A equipe do sindicato validará os dados informados junto ao sistema de arrecadação da categoria.</li>
                    <li><strong>Aceite Institucional:</strong> Após a homologação, será gerado o Termo Oficial em PDF com carimbo e hash de autenticidade.</li>
                    <li><strong>Notificações Automáticas:</strong> Você receberá a carta final homologada no e-mail <strong>{{ $desfiliacao->email }}</strong> e o sindicato enviará notificação oficial com validade jurídica à sua empresa empregadora (<strong>{{ $desfiliacao->empresa }}</strong>).</li>
                </ol>
            </div>

            <div class="text-xs text-slate-400">
                Data do envio: {{ $desfiliacao->created_at->format('d/m/Y \à\s H:i:s') }} &bull; IP: {{ $desfiliacao->ip_solicitacao }}
            </div>

            <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('desfiliacao.validar') }}" class="btn px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition inline-flex items-center justify-center gap-2">
                    <i class="fa-solid fa-qrcode"></i> Consultar Autenticidade
                </a>
            </div>
        </div>
    </main>

    <footer class="py-4 text-center text-xs text-slate-400 border-t border-slate-200 bg-white">
        Sindicato Químicos Unificados &bull; Regional Campinas, Osasco e Região &bull; Segurança e Validade Jurídica (Lei 14.063/2020)
    </footer>
</body>
</html>
