<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Histórico de Movimentações de Ativos</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 10px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 12px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        td {
            border: 1px solid #ddd;
            padding: 8px;
            vertical-align: top;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #999;
            padding-top: 5px;
            border-top: 1px solid #eee;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .bg-primary { background-color: #0d6efd; color: white; }
        .bg-info { background-color: #0dcaf0; color: #333; }
        .bg-success { background-color: #198754; color: white; }
        .bg-warning { background-color: #ffc107; color: #333; }
        .bg-secondary { background-color: #6c757d; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <div style="text-align: center; margin-bottom: 10px;">
            <img src="{{ public_path('img/logo.jpg') }}" alt="Logo" style="max-width: 120px;">
        </div>
        <div class="title">Histórico de Movimentações de Ativos</div>
        <div class="subtitle">Relatório gerado em {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Data / Hora</th>
                <th style="width: 25%;">Equipamento</th>
                <th style="width: 10%;">Tipo</th>
                <th style="width: 20%;">Cessionário</th>
                <th style="width: 20%;">Destino / Local</th>
                <th style="width: 13%;">Operador</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movimentacoes as $mov)
            <tr>
                <td>
                    <b>{{ $mov->data_movimentacao->format('d/m/Y') }}</b><br>
                    {{ $mov->data_movimentacao->format('H:i') }}
                </td>
                <td>
                    <b>#EQP_{{ $mov->equipamento->id }}</b><br>
                    {{ $mov->equipamento->descricao }}
                    @if($mov->equipamento->modelo)
                        <br><small>Mod: {{ $mov->equipamento->modelo }}</small>
                    @endif
                </td>
                <td>
                    @php
                        $tipoClasses = [
                            'cessao' => 'bg-primary',
                            'emprestimo' => 'bg-info',
                            'devolucao' => 'bg-success',
                            'manutencao' => 'bg-warning',
                            'transferencia' => 'bg-secondary',
                        ];
                        $badgeClass = $tipoClasses[$mov->tipo] ?? '';
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $mov->tipo }}</span>
                </td>
                <td>
                    @if($mov->usuario)
                        {{ $mov->usuario->nome }}<br>
                        <small>{{ $mov->usuario->empresa->razao_social ?? 'S/ Empresa' }}</small>
                    @else
                        -
                    @endif
                </td>
                <td>
                    {{ $mov->destino ?? '-' }}
                    @if($mov->observacao)
                        <br><small style="color: #666;">Obs: {{ $mov->observacao }}</small>
                    @endif
                </td>
                <td>{{ $mov->responsavel->name }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Página <span class="pagenum"></span> - Portal de Aplicativos
    </div>
</body>
</html>
