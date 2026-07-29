<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório Analítico de Protocolos e Ofícios</title>
    @php
        $temaCor = match($statusEnvio) {
            'falha' => '#dc3545',
            'sucesso' => '#198754',
            'enviado' => '#0d6efd',
            default => '#033c5a',
        };

        $tituloRelatorio = match($statusEnvio) {
            'falha' => 'Relatório de Envios com Falha',
            'sucesso' => 'Relatório de Envios Concluídos (Sucesso / Entregues)',
            'enviado' => 'Relatório de Envios Realizados',
            default => 'Relatório Analítico Geral de Protocolos e Ofícios',
        };
    @endphp
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 9pt; color: #333; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid {{ $temaCor }}; padding-bottom: 8px; }
        .title { font-size: 16pt; font-weight: bold; margin: 0; color: {{ $temaCor }}; }
        .subtitle { font-size: 10pt; color: #666; margin-top: 4px; }
        
        /* Summary Grid Cards */
        .summary-container { width: 100%; margin-bottom: 15px; }
        .summary-card { float: left; width: 23%; padding: 8px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 5px; text-align: center; margin-right: 2%; }
        .summary-card:last-child { margin-right: 0; }
        .summary-title { font-size: 8pt; font-weight: bold; color: #666; text-transform: uppercase; margin-bottom: 4px; }
        .summary-value { font-size: 14pt; font-weight: bold; }
        .val-total { color: #033c5a; }
        .val-sucesso { color: #198754; }
        .val-enviado { color: #0d6efd; }
        .val-falha { color: #dc3545; }

        .clear { clear: both; }

        .filters { font-size: 8.5pt; background: #f1f5f9; padding: 8px 12px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #cbd5e1; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 8.5pt; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 5px; text-align: left; vertical-align: middle; }
        th { background-color: #e2e8f0; font-weight: bold; color: #1e293b; border-bottom: 2px solid #94a3b8; }
        tr:nth-child(even) { background-color: #f8fafc; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge { display: inline-block; padding: 3px 6px; border-radius: 4px; font-size: 7.5pt; font-weight: bold; color: white; }
        .badge-sucesso { background-color: #198754; }
        .badge-enviado { background-color: #0d6efd; }
        .badge-falha { background-color: #dc3545; }
        .badge-pendente { background-color: #ffc107; color: #212529; }
        .badge-escopo { background-color: #64748b; font-size: 7pt; }

        .small-text { font-size: 7.5pt; color: #64748b; }
        .footer { margin-top: 25px; text-align: center; font-size: 8pt; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 8px; position: fixed; bottom: 0; width: 100%; }
        .page-number:before { content: "Página " counter(page); }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="title">{{ $tituloRelatorio }}</h1>
        <div class="subtitle">Relatório Operacional e Jurídico de Rastreamento AR-Online</div>
    </div>

    <!-- Cards de Métricas -->
    <div class="summary-container">
        <div class="summary-card">
            <div class="summary-title">Total Geral</div>
            <div class="summary-value val-total">{{ number_format($totalGeral, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-title">Entregues / Lidos</div>
            <div class="summary-value val-sucesso">{{ number_format($totalSucesso, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-title">Enviados</div>
            <div class="summary-value val-enviado">{{ number_format($totalEnviados, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-title">Com Falhas</div>
            <div class="summary-value val-falha">{{ number_format($totalFalhas, 0, ',', '.') }}</div>
        </div>
        <div class="clear"></div>
    </div>

    <!-- Filtros Aplicados -->
    <div class="filters">
        <strong>Filtros aplicados ao relatório:</strong><br>
        @if($data)
            Data Específica: <strong>{{ \Carbon\Carbon::parse(str_replace('/', '-', $data))->format('d/m/Y') }}</strong> |
        @elseif($dataInicio || $dataFim)
            Período: <strong>{{ $dataInicio ? \Carbon\Carbon::parse(str_replace('/', '-', $dataInicio))->format('d/m/Y') : 'Início' }} até {{ $dataFim ? \Carbon\Carbon::parse(str_replace('/', '-', $dataFim))->format('d/m/Y') : 'Atual' }}</strong> |
        @else
            Mês/Ano: <strong>{{ $mes ? \Carbon\Carbon::create()->month((int)$mes)->locale('pt_BR')->translatedFormat('F') : 'Todos' }}/{{ $ano ?: 'Todos' }}</strong> |
        @endif

        Status: <strong>{{ $statusEnvio ? ucfirst($statusEnvio) : 'Todos' }}</strong> |
        Escopo: <strong>{{ $tipoEscopo ? ucfirst($tipoEscopo) : 'Todos' }}</strong>
        @if($tipoProtocoloNome) | Tipo: <strong>{{ $tipoProtocoloNome }}</strong> @endif
        @if($termo) | Termo: <strong>"{{ $termo }}"</strong> @endif
    </div>

    @if($falhas->isEmpty())
        <div style="text-align: center; margin-top: 50px; color: #94a3b8; font-size: 11pt;">
            Nenhum registro de envio foi encontrado com os filtros selecionados.
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">Data/Hora</th>
                    <th style="width: 7%;">ID</th>
                    <th style="width: 13%;">Tipo / Escopo</th>
                    <th style="width: 25%;">Assunto / Referência</th>
                    <th style="width: 23%;">Empresa</th>
                    <th style="width: 14%;">Contato Destinatário</th>
                    <th style="width: 8%; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($falhas as $envio)
                    @php
                        $protocolo = $envio->protocolo;
                        $dest = $envio->destinatario;
                        $empresaNome = $dest?->empresa?->razao_social ?? $protocolo?->empresa?->razao_social ?? '—';
                        $cidade = $dest?->empresa?->cidade ?? $protocolo?->empresa?->cidade;
                    @endphp
                    <tr>
                        <td class="text-center">
                            {{ $envio->created_at->format('d/m/Y') }}<br>
                            <span class="small-text">{{ $envio->created_at->format('H:i') }}</span>
                        </td>
                        <td class="text-center">#{{ $protocolo->id }}</td>
                        <td>
                            <strong>{{ $protocolo->tipo->nome ?? 'Padrão' }}</strong><br>
                            <span class="badge badge-escopo">{{ strtoupper($protocolo->tipo_escopo ?? 'INDIVIDUAL') }}</span>
                        </td>
                        <td>
                            <strong>{{ \Illuminate\Support\Str::limit($protocolo->assunto, 45) }}</strong>
                            @if($protocolo->referencia_documento)
                                <div class="small-text">{{ $protocolo->referencia_documento }}</div>
                            @endif
                        </td>
                        <td>
                            {{ \Illuminate\Support\Str::limit($empresaNome, 45) }}
                            @if($cidade)
                                <br><span class="small-text">{{ $cidade }}</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $dest ? \Illuminate\Support\Str::limit($dest->nome, 25) : 'Desconhecido' }}</strong><br>
                            <span class="small-text">{{ $dest ? \Illuminate\Support\Str::limit($dest->email, 28) : '—' }}</span>
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
