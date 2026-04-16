<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Envio de Protocolos com Falha</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10pt; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #dc3545; padding-bottom: 10px; }
        .title { font-size: 18pt; font-weight: bold; margin: 0; color: #dc3545; }
        .subtitle { font-size: 11pt; color: #666; margin-top: 5px; }
        .filters { font-size: 9pt; background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 9pt; }
        th, td { border: 1px solid #ddd; padding: 8px 6px; text-align: left; vertical-align: middle; }
        th { background-color: #f1f1f1; font-weight: bold; color: #444; border-bottom: 2px solid #ccc; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge { display: inline-block; padding: 3px 6px; border-radius: 4px; font-size: 8pt; font-weight: bold; color: white; background-color: #dc3545; }
        .small-text { font-size: 8pt; color: #666; }
        .footer { margin-top: 30px; text-align: center; font-size: 8pt; color: #999; border-top: 1px solid #eee; padding-top: 10px; position: fixed; bottom: 0; width: 100%; }
        .page-number:before { content: "Página " counter(page); }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="title">Envios com Falha - Protocolos</h1>
        <div class="subtitle">Relatório Analítico de Rastreamento Automático</div>
    </div>

    <div class="filters">
        <strong>Filtros aplicados:</strong><br>
        Mês/Ano: {{ $mes ? \Carbon\Carbon::create()->month($mes)->locale('pt_BR')->translatedFormat('F') : 'Todos' }}/{{ $ano ?: 'Todos' }}
        @if($termo) | Termo: "{{ $termo }}" @endif
        <br>
        Total de falhas encontradas: <strong>{{ $falhas->count() }}</strong>
    </div>

    @if($falhas->isEmpty())
        <div style="text-align: center; margin-top: 50px; color: #999;">
            Nenhuma falha de envio foi encontrada com os filtros selecionados.
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">Data</th>
                    <th style="width: 8%;">Prot. ID</th>
                    <th style="width: 20%;">Assunto</th>
                    <th style="width: 25%;">Empresa</th>
                    <th style="width: 24%;">Contato Geração/Falha</th>
                    <th style="width: 15%;">Motivo/Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($falhas as $envio)
                    @php
                        $protocolo = $envio->protocolo;
                        $dest = $envio->destinatario;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $envio->created_at->format('d/m/Y') }}<br><span class="small-text">{{ $envio->created_at->format('H:i') }}</span></td>
                        <td class="text-center">#{{ $protocolo->id }}</td>
                        <td>
                            <strong>{{ \Illuminate\Support\Str::limit($protocolo->assunto, 40) }}</strong>
                            <div class="small-text pb-1">{{ $protocolo->referencia_documento }}</div>
                        </td>
                        <td>{{ $protocolo->empresa ? \Illuminate\Support\Str::limit($protocolo->empresa->razao_social, 45) : '—' }}</td>
                        <td>
                            <strong>{{ $dest ? $dest->nome : 'Desconhecido' }}</strong><br>
                            <span class="small-text">{{ $dest ? $dest->email : '—' }}</span>
                        </td>
                        <td class="text-center">
                            @php
                                $msgErro = 'Falha no servidor';
                                if($envio->ultima_resposta) {
                                    $resp = json_decode($envio->ultima_resposta, true);
                                    if(isset($resp['statusFull']['email']) && is_array($resp['statusFull']['email'])) {
                                        foreach($resp['statusFull']['email'] as $st) {
                                           if(stripos($st['label'] ?? '', 'falha') !== false) {
                                               $msgErro = $st['label'];
                                               break;
                                           }
                                        }
                                    }
                                }
                            @endphp
                            <span class="badge">FALHA</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Gerado pelo Portal de Aplicativos em {{ date('d/m/Y H:i:s') }} - <span class="page-number"></span>
    </div>

</body>
</html>
