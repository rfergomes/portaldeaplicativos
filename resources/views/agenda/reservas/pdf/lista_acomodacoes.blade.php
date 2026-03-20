<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Lista de Reservas - {{ $colonia->nome }}</title>
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
            border-bottom: 2px solid #0056b3;
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
            color: #0056b3;
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
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
            color: #0056b3;
            text-transform: uppercase;
            font-size: 10px;
        }

        .col-aco {
            width: 15%;
            font-weight: bold;
        }

        .col-hospede {
            width: 45%;
        }

        .col-status {
            width: 25%;
            text-align: center;
        }

        .col-obs {
            width: 15%;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            color: white;
            display: inline-block;
        }

        .bg-success {
            background-color: #198754;
        }

        .bg-primary {
            background-color: #0d6efd;
        }

        .bg-danger {
            background-color: #dc3545;
        }

        .bg-purple {
            background-color: #6f42c1;
        }

        .text-muted {
            color: #6c757d;
            font-size: 9px;
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
        <div style="float: left; width: 40%;">
            <img src="{{ public_path('img/logo.jpg') }}" alt="Logo" style="max-height: 20mm;">
        </div>
        <div class="info-header" style="float: right; width: 48%; text-align: right;">
            <strong>{{ $colonia->nome }}</strong><br>
            Período: {{ $periodo->descricao }}<br>
            De {{ $periodo->data_inicial->format('d/m/Y') }} a {{ $periodo->data_final->format('d/m/Y') }}
        </div>
    </div>

    <h1 class="title">Lista de Reservas</h1>

    <table>
        <thead>
            <tr>
                <th class="col-aco">Acomodação</th>
                <th class="col-hospede">Hóspede / Beneficiário</th>
                <th class="col-status">Situação</th>
                <th class="col-obs">Rubrica</th>
            </tr>
        </thead>
        <tbody>
            @foreach($colonia->acomodacoes as $aco)
                @php $reserva = $reservas->get($aco->id); @endphp
                <tr>
                    <td class="col-aco">
                        <small style="color: #666; display: block; font-weight: normal;">{{ $aco->tipo }}</small>
                        {{ $aco->identificador }}
                    </td>
                    <td class="col-hospede">
                        @if($reserva)
                            @if($reserva->hospede)
                                <strong>{{ $reserva->hospede->nome }}</strong><br>
                                <span class="text-muted">
                                    {{ $reserva->hospede->telefone }}
                                    @if($reserva->hospede->empresa) | {{ $reserva->hospede->empresa->razao_social }} @endif
                                </span>
                            @else
                                <strong style="color: #dc3545;">{{ $reserva->bloqueio_nota }}</strong>
                            @endif
                        @else
                            <span style="color: #aaa; font-style: italic;">-- Livre / Disponível --</span>
                        @endif
                    </td>
                    <td class="col-status">
                        @if($reserva)
                            @if($reserva->status == 'pago')
                                <span class="badge bg-success">Pago</span>
                            @elseif($reserva->status == 'confirmado')
                                <span class="badge" style="background-color: #001f3f; color: #fff;">Confirmado</span>
                            @elseif($reserva->bloqueio_nota && stripos($reserva->bloqueio_nota, 'osasco') !== false)
                                <span class="badge bg-purple">Cota Osasco</span>
                            @elseif($reserva->bloqueio_nota)
                                <span class="badge bg-danger">Bloqueado</span>
                            @else
                                <span class="badge bg-info text-dark">Reservado</span>
                            @endif
                        @else
                            <span class="badge"
                                style="background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6;">Livre</span>
                        @endif
                    </td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Relatório Gerencial - Portal de Aplicativos - {{ now()->format('d/m/Y H:i') }}
    </div>
</body>

</html>