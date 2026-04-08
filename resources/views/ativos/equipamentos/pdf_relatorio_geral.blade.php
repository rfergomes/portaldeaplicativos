<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 12px;
            color: #555;
            margin-top: 5px;
        }
        .summary-box {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            font-size: 11px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        th {
            background-color: #f2f2f2;
            border: 1px solid #999;
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
        }
        td {
            border: 1px solid #999;
            padding: 6px 5px;
            vertical-align: middle;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .status-disponivel { background-color: #d1e7dd; color: #0f5132; }
        .status-em_uso { background-color: #cfe2ff; color: #084298; }
        .status-manutencao { background-color: #fff3cd; color: #664d03; }
        .status-baixado { background-color: #f8d7da; color: #842029; }
        
        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #777;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
        .pagenum:before {
            content: counter(page);
        }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('img/logo.jpg')))
            <div style="text-align: center; margin-bottom: 10px;">
                <img src="{{ public_path('img/logo.jpg') }}" alt="Logo" style="max-width: 120px;">
            </div>
        @endif
        <div class="title">{{ $titulo }}</div>
        <div class="subtitle">Relatório Gerencial - Gerado em {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="summary-box">
        Total de Equipamentos listados: {{ $equipamentos->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">ID</th>
                <th style="width: 27%;">Descrição / Modelo</th>
                <th style="width: 15%;">Série</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 38%;">Localização / Atribuição</th>
            </tr>
        </thead>
        <tbody>
            @forelse($equipamentos as $item)
                <tr>
                    <td class="text-center fw-bold">#EQP_{{ $item->id }}</td>
                    <td>
                        <div style="font-weight: bold;">{{ $item->descricao }}</div>
                        <div style="color: #666; font-size: 9px;">Mod: {{ $item->modelo ?? 'N/D' }} {{ $item->fabricante ? ' | Fab: '.$item->fabricante->nome : '' }}</div>
                    </td>
                    <td>{{ $item->numero_serie ?? '-' }}</td>
                    <td class="text-center">
                        <span class="status-badge status-{{ $item->status }}">
                            {{ str_replace('_', ' ', $item->status) }}
                        </span>
                    </td>
                    <td>
                        @if($item->estacao)
                            <div style="font-weight: bold;">{{ $item->estacao->nome }}</div>
                            <div style="color: #666; font-size: 9px;">Dep: {{ $item->estacao->departamento->nome ?? 'N/D' }}</div>
                        @elseif($item->status === 'em_uso' && $item->ultimaMovimentacao && $item->ultimaMovimentacao->usuario)
                            <div style="font-weight: bold;">{{ $item->ultimaMovimentacao->usuario->nome }}</div>
                            <div style="color: #666; font-size: 9px;">Contrato/Cessão</div>
                        @else
                            {{ $item->localizacao_atual }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px;">
                        Nenhum equipamento encontrado para este filtro.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Portal de Aplicativos &copy; {{ date('Y') }} - {{ $titulo }} - Página <span class="pagenum"></span>
    </div>
</body>
</html>
