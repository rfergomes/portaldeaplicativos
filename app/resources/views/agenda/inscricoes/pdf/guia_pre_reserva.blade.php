<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Guia de Pré-Reserva</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
            color: #333;
        }

        .container {
            width: 100%;
            padding: 20px;
            box-sizing: border-box;
        }

        .guia {
            width: 100%;
            border: 1px solid #999;
            padding: 15px;
            position: relative;
            margin-bottom: 20px;
            box-sizing: border-box;
            background: #fff;
        }

        .guia:nth-child(even) {
            margin-top: 40px;
            /* Espaçamento para o corte no meio da A4 */
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #d32f2f;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .logo {
            width: 150px;
        }

        .titulo-guia {
            text-align: right;
            color: #d32f2f;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .subtitulo {
            text-align: right;
            color: #d32f2f;
            font-size: 14px;
            font-weight: bold;
        }

        .secao {
            background: #777;
            color: #fff;
            padding: 3px 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 10px 0 5px 0;
        }

        .row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }

        .col {
            display: table-cell;
            vertical-align: top;
        }

        .field-label {
            background: #ddd;
            padding: 3px 8px;
            font-weight: bold;
            display: inline-block;
            min-width: 80px;
        }

        .field-value {
            padding: 3px 8px;
            border-bottom: 1px solid #ddd;
            display: inline-block;
            flex-grow: 1;
        }

        .info-box {
            border: 1px solid #ccc;
            padding: 10px;
            margin-top: 5px;
            font-size: 11px;
            line-height: 1.4;
        }

        .qr-section {
            display: table-cell;
            width: 300px;
            vertical-align: middle;
            text-align: center;
            border-left: 1px dashed #ccc;
            padding-left: 10px;
        }

        .qr-box {
            display: inline-block;
            text-align: left;
        }

        .footer-note {
            font-size: 10px;
            color: #666;
            margin-top: 10px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    @for($i = 0; $i < $quantidade; $i++)
        <div class="container">
            <div class="guia">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 200px;">
                            <img src="https://www.quimicosunificados.com.br/wp-content/uploads/2019/11/logo-quimicos-unificados.png"
                                class="logo" alt="Sindicato Químicos Unificados">
                        </td>
                        <td style="text-align: right;">
                            <div class="titulo-guia">PRÉ RESERVA - COLÔNIA DE FÉRIAS</div>
                            <div class="subtitulo">{{ $colonia->nome }}</div>
                        </td>
                    </tr>
                </table>

                <div class="row" style="margin-top: 10px;">
                    <div class="col" style="width: 70%;">
                        <span class="field-label">NOME</span>
                        <span class="field-value"
                            style="width: 80%; display: inline-block; border-bottom: 1px solid #333;">&nbsp;</span>
                    </div>
                    <div class="col" style="width: 30%;">
                        <span class="field-label">DATA DO SORTEIO</span>
                        <span
                            class="field-value">{{ $periodo->data_sorteio ? $periodo->data_sorteio->format('d/m/Y') : '' }}</span>
                    </div>
                </div>

                <div class="row">
                    <div class="col" style="width: 50%;">
                        <span class="field-label">PERÍODO</span>
                        <span class="field-value">{{ $periodo->descricao }}</span>
                    </div>
                    <div class="col" style="width: 50%;">
                        <span class="field-label">DATA</span>
                        <span class="field-value">DE {{ $periodo->data_inicial->format('d/m/Y') }} À
                            {{ $periodo->data_final->format('d/m/Y') }}</span>
                    </div>
                </div>

                <div class="secao">DATAS LIMITES PARA PAGAMENTO</div>
                <div class="info-box">
                    <p>☛ Para confirmar o interesse da reserva, deverá efetuar o pagamento até:
                        <strong>{{ $periodo->data_limite_pagamento ? $periodo->data_limite_pagamento->format('d/m/Y') . ' (' . $periodo->data_limite_pagamento->locale('pt_BR')->dayName . ')' : '____/____/________' }}</strong>
                    </p>
                </div>

                <div class="secao">EM CASO DE DESISTÊNCIA</div>
                <div class="info-box">
                    <p>☛ Desistência até <strong>5 dias antecipados</strong>, terá um <strong>crédito de 100%</strong> do
                        valor pago.</p>
                    <p>☛ Desistência até <strong>4 dias antecipados ou menos</strong>, terá um <strong>crédito de
                            50%</strong> do valor pago.</p>
                    <p>☛ <strong>Caso não informe a desistência antecipadamente, não haverá devolução do valor.</strong></p>
                </div>

                <div class="secao">ATENDIMENTO / RESERVAS / FORMAS DE PAGAMENTO</div>
                <table style="width: 100%;">
                    <tr>
                        <td style="vertical-align: top; padding-right: 15px;">
                            <p style="text-align: justify; margin-top: 0;">
                                Todas as informações referente a reserva, instrução de pagamento, inclusive documentação a
                                ser apresentada, deverá ser encaminhada pelo nosso canal do WhatsApp:
                            </p>
                        </td>
                        <td style="width: 250px; border-left: 1px dashed #ccc; padding-left: 15px;">
                            <div style="display: table;">
                                <div style="display: table-cell; vertical-align: middle;">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=https://wa.me/5519974055662"
                                        alt="QR WhatsApp">
                                </div>
                                <div style="display: table-cell; vertical-align: middle; padding-left: 10px;">
                                    <strong>Nosso canal de WhatsApp</strong><br>
                                    <span style="color: #25D366; font-size: 10px;">https://wa.me/coloniadeferias</span><br>
                                    <strong>+55 19 97405-5662</strong>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="footer-note" style="border-top: 1px dotted #ccc; text-align: center; padding-top: 5px;">
                    GeraLista V2.0 - Sindicato Químicos Unificados
                </div>
            </div>

            @if(($i + 1) % 2 != 0 && ($i + 1) < $quantidade)
                {{-- Linha de corte se houver mais uma guia na mesma página --}}
                <div style="border-top: 1px dashed #000; margin: 10px 0; position: relative;">
                    <span
                        style="position: absolute; top: -8px; left: 50%; background: #fff; padding: 0 10px; font-size: 10px;">CORTE
                        AQUI</span>
                </div>
            @endif
        </div>

        @if(($i + 1) % 2 == 0 && ($i + 1) < $quantidade)
            <div class="page-break"></div>
        @endif
    @endfor
</body>

</html>