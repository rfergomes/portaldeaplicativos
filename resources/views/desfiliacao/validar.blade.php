<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validação Pública de Autenticidade - Sindicato Químicos Unificados</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-100/80 text-slate-800 antialiased min-h-screen flex flex-col justify-between">
    <!-- Header Institucional com Logotipo -->
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
            <a href="{{ route('desfiliacao.create') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 hover:text-red-700 bg-slate-50 hover:bg-red-50 border border-slate-200 hover:border-red-200 rounded-lg px-3.5 py-2 shadow-xs transition">
                <i class="fa-solid fa-arrow-left text-slate-400 group-hover:text-red-500"></i>
                <span>Solicitação de Desfiliação</span>
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-2xl w-full mx-auto px-4 py-10 flex-grow">
        <!-- Card de Busca por Hash -->
        <div class="bg-white rounded-2xl shadow-md border border-slate-200 p-6 md:p-8 mb-6 relative pt-8">
            <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-red-600 text-white font-bold text-xs uppercase tracking-wider px-5 py-1 rounded-full shadow-sm">
                Conferência de Autenticidade
            </div>

            <h2 class="text-xl md:text-2xl font-bold text-slate-900 mb-1 flex items-center gap-2">
                <i class="fa-solid fa-magnifying-glass text-red-600"></i>
                Validação de Termo de Desfiliação
            </h2>
            <p class="text-slate-500 text-xs md:text-sm mb-6 leading-relaxed">
                Informe o código criptográfico SHA-256 impresso no rodapé do documento (ou escaneie o QR Code oficial) para atestar a autenticidade e integridade do requerimento perante a entidade sindical.
            </p>

            <form action="{{ route('desfiliacao.validar') }}" method="GET" class="space-y-4">
                <div>
                    <label for="hash" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Código Verificador / Hash SHA-256
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-red-500">
                            <i class="fa-solid fa-key"></i>
                        </div>
                        <input type="text" name="hash" id="hash" required
                            value="{{ $hashBuscado ?? request('hash') }}"
                            placeholder="Ex.: e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855"
                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-red-500/20 focus:border-red-600 text-sm font-mono transition placeholder-slate-400 text-slate-800">
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-3.5 px-5 rounded-xl bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-bold text-sm shadow-md shadow-red-600/20 hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer transform hover:-translate-y-0.5 active:translate-y-0">
                    <i class="fa-solid fa-check"></i>
                    <span>Consultar Autenticidade do Requerimento</span>
                </button>
            </form>
        </div>

        <!-- Resultado da Validação -->
        @if ($hashBuscado)
            @if ($desfiliacao && $desfiliacao->isAssinada())
                <!-- Autêntico -->
                <div class="bg-white rounded-2xl shadow-md border border-emerald-200 overflow-hidden">
                    <div class="bg-emerald-600 p-5 text-white flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center text-2xl flex-shrink-0">
                            <i class="fa-solid fa-shield-check"></i>
                        </div>
                        <div>
                            <span class="text-xs uppercase tracking-wider font-semibold text-emerald-100">Resultado da Verificação</span>
                            <h3 class="text-lg font-bold leading-tight">DOCUMENTO AUTÊNTICO E ÍNTEGRO</h3>
                        </div>
                    </div>

                    <div class="p-6 space-y-4 text-sm text-slate-700">
                        <p class="text-xs text-slate-500 leading-relaxed border-b border-slate-100 pb-3">
                            Atestamos que o documento correspondente a este hash confere integralmente com o requerimento emitido originalmente e possui plena conformidade legal com a Lei Federal nº 14.063/2020.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs md:text-sm">
                            <div>
                                <span class="block text-xs font-semibold text-slate-400 uppercase">Trabalhador Titular</span>
                                <span class="font-bold text-slate-900">{{ $desfiliacao->nome_completo }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-slate-400 uppercase">CPF</span>
                                <span class="font-medium text-slate-800 font-mono">{{ $desfiliacao->cpf_formatado }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-slate-400 uppercase">Empresa Empregadora</span>
                                <span class="font-medium text-slate-800">{{ $desfiliacao->empresa }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-slate-400 uppercase">Data e Hora da Assinatura</span>
                                <span class="font-medium text-slate-800">{{ $desfiliacao->confirmado_em?->format('d/m/Y H:i:s') ?? '-' }}</span>
                            </div>
                            <div class="md:col-span-2 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <span class="block text-xs font-semibold text-slate-400 uppercase mb-1">Situação no Sindicato</span>
                                    @if ($desfiliacao->isEfetivada())
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            <i class="fa-solid fa-circle-check text-emerald-600"></i> Desfiliação Efetivada (Baixa no ERP Ábaco Concluída{{ $desfiliacao->efetivado_em ? ' em ' . $desfiliacao->efetivado_em->format('d/m/Y H:i') : '' }})
                                        </span>
                                    @elseif ($desfiliacao->isRecepcionada())
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300">
                                            <i class="fa-solid fa-stamp text-blue-600"></i> Recepcionada pelo Sindicato (Ciência Formal{{ $desfiliacao->recepcionado_em ? ' em ' . $desfiliacao->recepcionado_em->format('d/m/Y H:i') : '' }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                            <i class="fa-solid fa-envelope-open-text text-amber-600"></i> Assinada Eletronicamente (Aguardando Ciência Interna)
                                        </span>
                                    @endif
                                </div>
                                @if ($desfiliacao->protocolo_abaco)
                                <div class="text-right">
                                    <span class="block text-[11px] font-semibold text-slate-400 uppercase">Protocolo Ábaco</span>
                                    <span class="font-mono text-xs font-bold text-slate-700">{{ $desfiliacao->protocolo_abaco }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 mt-4">
                            <span class="block text-[11px] font-semibold text-slate-500 uppercase mb-1">Hash SHA-256 Registrado</span>
                            <span class="font-mono text-xs text-emerald-800 break-all select-all font-semibold">
                                {{ $desfiliacao->termo_hash }}
                            </span>
                        </div>
                    </div>
                </div>
            @else
                <!-- Inválido / Não Encontrado -->
                <div class="bg-white rounded-2xl shadow-md border border-red-200 overflow-hidden">
                    <div class="bg-red-600 p-5 text-white flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center text-2xl flex-shrink-0">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <span class="text-xs uppercase tracking-wider font-semibold text-red-100">Resultado da Verificação</span>
                            <h3 class="text-lg font-bold leading-tight">DOCUMENTO NÃO ENCONTRADO OU INVÁLIDO</h3>
                        </div>
                    </div>

                    <div class="p-6 text-sm text-slate-600 leading-relaxed">
                        <p>
                            O código identificador informado <strong>não corresponde a nenhum termo de desfiliação autenticado</strong> em nossa base de dados.
                        </p>
                        <p class="text-xs text-slate-500 mt-2">
                            Certifique-se de que o código digitado foi copiado sem espaços adicionais ou caracteres faltantes. Se o problema persistir, solicite a reapresentação do documento emitido pelo Portal Sindical.
                        </p>
                    </div>
                </div>
            @endif
        @endif
    </main>

    <!-- Footer Institucional -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-600">
        <div class="max-w-4xl mx-auto px-4 space-y-1">
            <p class="font-semibold text-slate-800">Sindicato Químicos Unificados &copy; {{ date('Y') }} - Regional Campinas e Osasco</p>
            <p class="text-slate-500 text-[11px]">Verificação de Assinatura Eletrônica em conformidade com a Lei Federal nº 14.063/2020.</p>
        </div>
    </footer>
</body>
</html>
