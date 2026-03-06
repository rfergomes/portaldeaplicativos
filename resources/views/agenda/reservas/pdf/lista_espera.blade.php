<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Lista de Espera - {{ $colonia->nome }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 11px;
            color: #333;
        }

        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #ffc107;
            padding-bottom: 10px;
        }

        .logo {
            width: 100px;
            float: left;
        }

        .info-header {
            text-align: right;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            color: #856404;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px 10px;
            text-align: left;
        }

        th {
            background-color: #fff3cd;
            color: #856404;
            text-transform: uppercase;
            font-size: 10px;
        }

        .col-pos {
            width: 40px;
            text-align: center;
            font-weight: bold;
        }

        .col-nome {
            width: auto;
            font-weight: bold;
        }

        .col-contato {
            width: 150px;
        }

        .col-empresa {
            width: 150px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>

<body>
    <div class="header clearfix">
        <div style="float: left; width: 50%;">
            <div style="font-weight: bold; color: #1a237e; font-size: 14pt;">SINDICATO QUÍMICOS UNIFICADOS</div>
            <div style="font-size: 8pt; color: #666;">Portal de Aplicativos</div>
        </div>
        <div class="info-header" style="float: right; width: 48%; text-align: right;">
            <strong>{{ $colonia->nome }}</strong><br>
            Período: {{ $periodo->descricao }}<br>
            De {{ $periodo->data_inicial->format('d/m/Y') }} a {{ $periodo->data_final->format('d/m/Y') }}
        </div>
    </div>

    <h1 class="title">Lista de Espera (Suplentes)</h1>

    <table>
        <thead>
            <tr>
                <th class="col-pos">Pos.</th>
                <th class="col-nome">Nome Completo</th>
                <th class="col-contato">Contato</th>
                <th class="col-empresa">Empresa</th>
                <th style="width: 80px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($filaEspera as $index => $espera)
                <tr>
                    <td class="col-pos">{{ $index + 1 }}º</td>
                    <td class="col-nome">{{ $espera->hospede->nome ?? '---' }}</td>
                    <td class="col-contato">{{ $espera->hospede->telefone ?? '---' }}</td>
                    <td class="col-empresa">
                        {{ \Illuminate\Support\Str::limit($espera->hospede->empresa->razao_social ?? '---', 30) }}
                    </td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 30px; color: #999;">
                        Nenhum suplente aguardando nesta colônia para este período.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Relatório de Suplentes - Portal de Aplicativos - {{ now()->format('d/m/Y H:i') }}
    </div>
</body>

</html>