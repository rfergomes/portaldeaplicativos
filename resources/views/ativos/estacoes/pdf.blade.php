<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Estações de Trabalho</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 13px;
            color: #555;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 25px;
        }
        th {
            background-color: #f2f2f2;
            border: 1px solid #999;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
        }
        td {
            border: 1px solid #999;
            padding: 8px;
            vertical-align: middle;
        }
        .nf-header {
            background-color: #e9ecef;
            padding: 10px;
            font-weight: bold;
            font-size: 14px;
            border: 1px solid #999;
            margin-top: 20px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #777;
            padding-top: 10px;
            border-top: 1px solid #ccc;
        }
        .page-break {
            page-break-after: always;
        }
        .badge {
            background: #eee;
            border: 1px solid #ccc;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 10px;
            display: inline-block;
            margin-bottom: 2px;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('img/logo.jpg')))
            <div style="text-align: center; margin-bottom: 10px;">
                <img src="{{ public_path('img/logo.jpg') }}" alt="Logo" style="max-width: 150px;">
            </div>
        @endif
        <div class="title">Relatório de Estações de Trabalho</div>
        <div class="subtitle">Inventário de Postos e Ativos Vinculados - Gerado em {{ now()->format('d/m/Y H:i') }}</div>
        
        @if($busca || $status)
        <div style="margin-top: 5px; font-weight: bold; color: #d9534f;">
            FILTROS APLICADOS: 
            @if($busca) [Busca: "{{ $busca }}"] @endif
            @if($status) [Status: {{ strtoupper($status) }}] @endif
        </div>
        @endif
    </div>

    @forelse($departamentos as $depto)
        @if($depto->estacoes->count() > 0)
        <div class="nf-header" style="font-size: 12px; text-transform: uppercase;">
            <span style="display:inline-block; width: 80%; color:#d9534f">DEPARTAMENTO: <span style="font-weight:normal; color:#333">{{ $depto->nome }}</span></span>
            <span style="display:inline-block; width: 15%; text-align: right; color:#d9534f"><span style="font-weight:normal; color:#333">{{ $depto->estacoes->count() }} POSTOS</span></span>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width: 25%;">APELIDO / NOME DO POSTO</th>
                    <th style="width: 25%;">DESCRIÇÃO / LOCAL</th>
                    <th style="width: 40%;">EQUIPAMENTOS VINCULADOS</th>
                    <th style="width: 10%;">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($depto->estacoes as $estacao)
                    <tr>
                        <td style="font-weight: bold;">{{ $estacao->nome }}</td>
                        <td>{{ $estacao->descricao ?: '-' }}</td>
                        <td>
                            @forelse($estacao->equipamentos as $equipa)
                                <div class="badge">{{ $equipa->descricao }}</div>
                            @empty
                                <span style="font-style: italic; color: #777;">Nenhum equipamento</span>
                            @endforelse
                        </td>
                        <td>
                            @if($estacao->equipamentos->count() > 0)
                                <span style="color: green; font-weight: bold;">Ativa</span>
                            @else
                                <span style="color: orange; font-weight: bold;">Vazia</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    @empty
        <div style="text-align: center; margin-top: 50px; font-size: 16px;">
            Nenhuma estação de trabalho encontrada com os critérios informados.
        </div>
    @endforelse

    <div class="footer">
        Portal de Aplicativos - Inventário de Estações - Página <span class="pagenum"></span>
    </div>
</body>
</html>
