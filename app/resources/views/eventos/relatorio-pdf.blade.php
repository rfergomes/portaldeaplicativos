<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Relatório de Evento - {{ $evento->nome }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }

        .header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        .event-title {
            color: #448aff;
            /* Tom de azul do screenshot */
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .event-info {
            width: 100%;
            margin-top: 5px;
            color: #444;
            font-size: 10px;
        }

        .info-label {
            font-weight: bold;
            text-transform: uppercase;
            color: #333;
        }

        .lista-header {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .convite-block {
            margin-bottom: 20px;
        }

        .convite-header {
            background-color: #e3f2fd;
            /* Azul clarinho como no screenshot */
            padding: 8px 10px;
            margin-bottom: 0px;
            border-top: 1px solid #bbdefb;
            border-bottom: 1px solid #bbdefb;
            line-height: normal;
            min-height: 15px;
        }

        .responsavel {
            color: #2e7d32;
            /* Verde escuro para o nome do responsável no cabeçalho */
            font-weight: bold;
            display: inline-block;
            font-size: 13px;
            text-transform: uppercase;
        }

        .placa {
            color: #1976d2;
            /* Azul para a placa */
            font-weight: bold;
            float: right;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0px;
        }

        th {
            text-align: left;
            text-transform: uppercase;
            font-size: 9px;
            color: #444;
            border-bottom: 1px solid #999;
            padding: 4px 0;
        }

        td {
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }

        .text-right {
            text-align: right;
        }

        .total-row {
            font-weight: bold;
            text-align: right;
            padding: 5px 0;
            text-transform: uppercase;
            font-size: 11px;
        }

        .total-geral {
            margin-top: 20px;
            text-align: right;
            border-top: 2px solid #333;
            padding-top: 8px;
            font-size: 14px;
            font-weight: bold;
        }

        .obs {
            margin-top: 30px;
            padding: 10px;
            border-top: 1px solid #999;
            font-size: 12px;
        }

        .footer {
            position: fixed;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="header">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 70%; border: none;">
                    <span class="event-title">{{ $evento->nome }}</span>
                </td>
                <td style="width: 30%; text-align: right; border: none;">
                    <span style="font-size: 16px; font-weight: bold; color: #444;">EVENTOS</span>
                </td>
            </tr>
        </table>

        <table class="event-info">
            <tr>
                <td><span class="info-label">Data:</span> {{ $evento->data_inicio?->format('d/m/Y H:i') ?? 'N/A' }}</td>
                <td><span class="info-label">Local:</span> {{ $evento->local ?? 'N/A' }}</td>
                <td class="text-right">
                    <span class="info-label">Convites:</span> {{ $evento->convites->count() }} &nbsp;&nbsp;
                    <span class="info-label">Convidados:</span> {{ $evento->convidados()->count() }}
                </td>
            </tr>
        </table>
    </div>

    <div class="lista-header">Lista de Participantes</div>

    @foreach($evento->convites as $convite)
        <div class="convite-block">
            <div class="convite-header">
                <span class="responsavel">{{ $convite->nome_responsavel }}</span>
                <span class="placa">PLACA: {{ $convite->placa ?? '-' }}</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 45%;">Nome</th>
                        <th style="width: 20%;">CPF</th>
                        <th style="width: 20%;">Empresa</th>
                        @if(!$semValor)
                            <th style="width: 15%; text-align: right;">Valor</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($convite->convidados as $convidado)
                        <tr>
                            <td style="font-weight: {{ $loop->first ? 'bold' : 'normal' }}">{{ $convidado->nome }}</td>
                            <td>{{ $convidado->documento ?? '-' }}</td>
                            <td>{{ $convidado->empresa ?? '-' }}</td>
                            @if(!$semValor)
                                <td class="text-right">R$ {{ number_format($convidado->valor, 2, ',', '.') }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if(!$semValor)
                <div class="total-row">
                    TOTAL: &nbsp;&nbsp; R$ {{ number_format($convite->convidados->sum('valor'), 2, ',', '.') }}
                </div>
            @endif
        </div>
    @endforeach

    @if(!$semValor)
        <div class="total-geral">
            TOTAL GERAL: &nbsp;&nbsp; R$ {{ number_format($totalGeral, 2, ',', '.') }}
        </div>
    @endif

    <div class="obs">
        <strong>OBS:</strong> {{ $evento->obs ?? '-' }}
    </div>

    <div class="footer">
        Gerado em {{ date('d/m/Y H:i') }} | Portal de Aplicativos  - TI Químicos Unificados
    </div>
</body>

</html>