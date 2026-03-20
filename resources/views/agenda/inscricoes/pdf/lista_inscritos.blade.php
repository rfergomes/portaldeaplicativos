<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Lista de Inscritos para Sorteio</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }

        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .logo {
            width: 120px;
            float: left;
        }

        .info-header {
            text-align: right;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            text-transform: uppercase;
        }

        .col-num {
            width: 30px;
            text-align: center;
        }

        .col-nome {
            width: auto;
        }

        .col-tel {
            width: 120px;
        }

        .col-empresa {
            width: 150px;
        }

        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #666;
            text-align: center;
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
        <div style="float: left; width: 40%;">
            <img src="{{ public_path('img/logo.jpg') }}" alt="Logo" style="max-height: 20mm;">
        </div>
        <div class="info-header" style="float: right; width: 48%; text-align: right;">
            <strong>{{ $colonia->nome }}</strong><br>
            {{ $periodo->descricao }} (De {{ $periodo->data_inicial->format('d/m/Y') }} à
            {{ $periodo->data_final->format('d/m/Y') }})
        </div>
    </div>

    <h1 class="title">Lista de Inscrição para Sorteio</h1>

    <table>
        <thead>
            <tr>
                <th class="col-num">Nº</th>
                <th class="col-nome">NOME COMPLETO</th>
                <th class="col-tel">TELEFONE</th>
                <th class="col-empresa">EMPRESA</th>
                <th style="width: 100px;">RUBRICA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inscritos as $index => $inscricao)
                <tr>
                    <td class="col-num">{{ $index + 1 }}</td>
                    <td class="col-nome">{{ $inscricao->hospede->nome ?? '---' }}</td>
                    <td class="col-tel">{{ $inscricao->hospede->telefone ?? '---' }}</td>
                    <td class="col-empresa">
                        {{ \Illuminate\Support\Str::limit($inscricao->hospede->empresa->razao_social ?? '---', 25) }}
                    </td>
                    <td></td>
                </tr>
            @endforeach

            @if($inscritos->isEmpty())
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">Nenhuma inscrição encontrada para este
                        período.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        Gerado pelo Sistema Portal de Aplicativos em {{ now()->format('d/m/Y H:i') }}
    </div>
</body>

</html>