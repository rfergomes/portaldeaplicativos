<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventário Físico de Equipamentos</title>
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
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('img/logo.jpg')))
            <div style="text-align: center; margin-bottom: 10px;">
                <img src="{{ public_path('img/logo.jpg') }}" alt="Logo" style="max-width: 150px;">
            </div>
        @endif
        <div class="title">Inventário Físico de Equipamentos</div>
        <div class="subtitle">Relatório de Conferência - Gerado em {{ now()->format('d/m/Y H:i') }}</div>
        <div style="margin-top: 5px; font-weight: bold; color: #d9534f;">APENAS EQUIPAMENTOS DISPONÍVEIS NO ESTOQUE</div>
    </div>

    @forelse($equipamentos as $nf => $itens)
        <div class="nf-header">
            NOTA FISCAL: {{ $nf }}
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">ID</th>
                    <th style="width: 30%;">DESCRIÇÃO</th>
                    <th style="width: 15%;">MODELO</th>
                    <th style="width: 15%;">Nº SÉRIE</th>
                    <th style="width: 15%;">VALOR (R$)</th>
                    <th style="width: 20%;">LOCALIDADE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itens as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->descricao }}</td>
                        <td>{{ $item->modelo ?? '-' }}</td>
                        <td>{{ $item->numero_serie ?? '-' }}</td>
                        <td>{{ number_format($item->valor_atual, 2, ',', '.') }}</td>
                        <td style="background-color: #fff;"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <div style="text-align: center; margin-top: 50px; font-size: 16px;">
            Nenhum equipamento disponível encontrado para inventário.
        </div>
    @endforelse

    <div class="footer">
        Portal de Aplicativos - Inventário Físico - Página <span class="pagenum"></span>
    </div>
</body>
</html>
