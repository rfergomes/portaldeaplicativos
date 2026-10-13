<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validação Pública de Autenticidade - Portal Sindical</title>
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
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <h1 class="text-white font-semibold text-lg leading-tight">Portal Sindical</h1>
                    <p class="text-slate-400 text-xs">Conferência de Autenticidade de Documentos</p>
                </div>
            </div>
            <a href="{{ route('desfiliacao.create') }}" class="text-xs font-medium text-slate-300 hover:text-white flex items-center gap-1.5 transition">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Solicitação de Desfiliação</span>
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-2xl w-full mx-auto px-4 py-10 flex-grow">
        <!-- Card de Busca por Hash -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 md:p-8 mb-6">
            <h2 class="text-xl md:text-2xl font-bold text-slate-900 mb-1 flex items-center gap-2">
                <i class="fa-solid fa-magnifying-glass text-blue-600"></i>
                Validação de Termo de Desfiliação
            </h2>
            <p class="text-slate-500 text-xs md:text-sm mb-6 leading-relaxed">
                Informe o código criptográfico SHA-256 impresso no rodapé do documento (ou escaneie o QR Code) para atestar a autenticidade e integridade do requerimento.
            </p>

            <form action="{{ route('desfiliacao.validar') }}" method="GET" class="space-y-4">
                <div>
                    <label for="hash" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                        Código Verificador / Hash SHA-256
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-key"></i>
                        </div>
                        <input type="text" name="hash" id="hash" required
                            value="{{ $hashBuscado ?? request('hash') }}"
                            placeholder="Ex.: e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855"
                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-mono transition placeholder-slate-400 text-slate-800">
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-3 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-sm shadow transition flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-check"></i>
                    <span>Consultar Autenticidade</span>
                </button>
            </form>
        </div>

        <!-- Resultado da Validação -->
        @if ($hashBuscado)
            @if ($desfiliacao && $desfiliacao->isConfirmada())
                <!-- Autêntico -->
                <div class="bg-white rounded-2xl shadow-sm border border-emerald-200 overflow-hidden">
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
                                <span class="font-medium text-slate-800">{{ $desfiliacao->confirmado_em->format('d/m/Y H:i:s') }}</span>
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
                <div class="bg-white rounded-2xl shadow-sm border border-red-200 overflow-hidden">
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

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <p>Portal de Aplicativos Sindicais &copy; {{ date('Y') }} - Verificação de Assinatura Eletrônica (Lei nº 14.063/2020).</p>
    </footer>
</body>
</html>
