<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Envio de Protocolos</title>
    @php
        $temaCor = match($statusEnvio) {
            'falha' => '#dc3545',
            'sucesso' => '#198754',
            'enviado' => '#0d6efd',
            default => '#033c5a',
        };

        $tituloRelatorio = match($statusEnvio) {
            'falha' => 'Envios com Falha - Protocolos',
            'sucesso' => 'Envios Concluídos com Sucesso - Protocolos',
            'enviado' => 'Envios Realizados - Protocolos',
            default => 'Relatório Geral de Envio de Protocolos',
        };
    @endphp
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10pt; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid {{ $temaCor }}; padding-bottom: 10px; }
        .title { font-size: 18pt; font-weight: bold; margin: 0; color: {{ $temaCor }}; }
        .subtitle { font-size: 11pt; color: #666; margin-top: 5px; }
        .filters { font-size: 9pt; background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 9pt; }
        th, td { border: 1px solid #ddd; padding: 8px 6px; text-align: left; vertical-align: middle; }
        th { background-color: #f1f1f1; font-weight: bold; color: #444; border-bottom: 2px solid #ccc; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge { display: inline-block; padding: 3px 6px; border-radius: 4px; font-size: 8pt; font-weight: bold; color: white; }
        .badge-sucesso { background-color: #198754; }
        .badge-enviado { background-color: #0d6efd; }
        .badge-falha { background-color: #dc3545; }
        .badge-pendente { background-color: #ffc107; color: #212529; }
        .small-text { font-size: 8pt; color: #666; }
        .footer { margin-top: 30px; text-align: center; font-size: 8pt; color: #999; border-top: 1px solid #eee; padding-top: 10px; position: fixed; bottom: 0; width: 100%; }
        .page-number:before { content: "Página " counter(page); }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="title">{{ $tituloRelatorio }}</h1>
        <div class="subtitle">Relatório Analítico de Rastreamento Automático - AR-Online</div>
    </div>

    <div class="filters">
        <strong>Filtros aplicados:</strong><br>
        Mês/Ano: <strong>{{ $mes ? \Carbon\Carbon::create()->month((int)$mes)->locale('pt_BR')->translatedFormat('F') : 'Todos' }}/{{ $ano ?: 'Todos' }}</strong>
        | Status: <strong>{{ $statusEnvio ? ucfirst($statusEnvio) : 'Todos' }}</strong>
        @if($termo) | Termo: <strong>"{{ $termo }}"</strong> @endif
        <br>
        Total de envios encontrados: <strong>{{ $falhas->count() }}</strong>
    </div>

    @if($falhas->isEmpty())
        <div style="text-align: center; margin-top: 50px; color: #999;">
            Nenhum registro de envio foi encontrado com os filtros selecionados.
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">Data/Hora</th>
                    <th style="width: 8%;">Prot. ID</th>
                    <th style="width: 22%;">Assunto / Referência</th>
                    <th style="width: 25%;">Empresa</th>
                    <th style="width: 23%;">Contato Destinatário</th>
                    <th style="width: 12%; text-align: center;">Status Envio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($falhas as $envio)
                    @php
                        $protocolo = $envio->protocolo;
                        $dest = $envio->destinatario;
                        $empresaNome = $dest?->empresa?->razao_social ?? $protocolo?->empresa?->razao_social ?? '—';
                    @endphp
                    <tr>
                        <td class="text-center">
                            {{ $envio->created_at->format('d/m/Y') }}<br>
                            <span class="small-text">{{ $envio->created_at->format('H:i') }}</span>
                        </td>
                        <td class="text-center">#{{ $protocolo->id }}</td>
                        <td>
                            <strong>{{ \Illuminate\Support\Str::limit($protocolo->assunto, 40) }}</strong>
                            @if($protocolo->referencia_documento)
                                <div class="small-text">{{ $protocolo->referencia_documento }}</div>
                            @endif
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit($empresaNome, 45) }}</td>
                        <td>
                            <strong>{{ $dest ? $dest->nome : 'Desconhecido' }}</strong><br>
                            <span class="small-text">{{ $dest ? $dest->email : '—' }}</span>
                        </td>
                        <td class="text-center">
                            @switch($envio->status)
                                @case('lido')
                                @case('entregue')
                                @case('sucesso')
                                    <span class="badge badge-sucesso">{{ strtoupper($envio->status) }}</span>
                                    @break
                                @case('enviado')
                                    <span class="badge badge-enviado">ENVIADO</span>
                                    @break
                                @case('falha')
                                    <span class="badge badge-falha">FALHA</span>
                                    @break
                                @default
                                    <span class="badge badge-pendente">{{ strtoupper($envio->status) }}</span>
                            @endswitch
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Gerado pelo Portal de Aplicativos em {{ date('d/m/Y H:i:s') }} - TI Químicos Unificados<span class="page-number"></span>
    </div>

</body>
</html>
