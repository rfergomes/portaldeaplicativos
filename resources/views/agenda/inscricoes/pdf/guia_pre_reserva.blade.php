<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Guia de Pré-Reserva</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #333;
        }

        .page {
            width: 210mm;
            height: 297mm;
            padding: 3mm 10mm;
            box-sizing: border-box;
            position: relative;
        }

        .guia-wrapper {
            height: 110mm;
            width: 100%;
            border: 2px solid #1a237e;
            border-radius: 6px;
            padding: 4mm 8mm;
            box-sizing: border-box;
            position: relative;
            overflow: hidden;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .header {
            border-bottom: 2px solid #1a237e;
            padding-bottom: 2mm;
            margin-bottom: 3mm;
        }

        .logo-container {
            float: left;
            width: 40%;
        }

        .logo {
            max-width: 100%;
            max-height: 18mm;
        }

        .title-container {
            float: right;
            width: 58%;
            text-align: right;
        }

        .main-title {
            font-size: 14pt;
            font-weight: bold;
            color: #1a237e;
            margin: 0;
        }

        .sub-title {
            font-size: 10pt;
            color: #d32f2f;
            font-weight: bold;
            margin: 1mm 0 0 0;
        }

        .info-section {
            margin-bottom: 2mm;
        }

        .field-row {
            margin-bottom: 2mm;
            width: 100%;
        }

        .label {
            font-size: 7.5pt;
            font-weight: bold;
            color: #666;
            text-transform: uppercase;
            display: block;
            margin-bottom: 0.5mm;
        }

        .value {
            font-size: 10pt;
            font-weight: bold;
            border-bottom: 1px solid #ccc;
            padding: 0.5mm 0;
            display: block;
            min-height: 1em;
        }

        .col-2 {
            width: 48%;
            float: left;
        }

        .col-2-right {
            width: 48%;
            float: right;
        }

        .alert-box {
            background-color: #f9f9f9;
            border-left: 3px solid #1a237e;
            padding: 2mm 3mm;
            margin-top: 2mm;
        }

        .alert-title {
            font-size: 8.5pt;
            font-weight: bold;
            color: #1a237e;
            margin-bottom: 0.5mm;
        }

        .alert-content {
            font-size: 8pt;
            line-height: 1.1;
        }

        .deadline-text {
            font-size: 10pt;
            color: #d32f2f;
            font-weight: bold;
        }

        .footer {
            position: absolute;
            bottom: 2mm;
            left: 8mm;
            right: 8mm;
            text-align: center;
            font-size: 7pt;
            color: #999;
            border-top: 1px solid #eee;
        }

        .cutting-line {
            width: 100%;
            border-top: 1px dashed #000;
            margin: 4mm 0;
            text-align: center;
            height: 1px;
            position: relative;
        }

        .cutting-label {
            position: absolute;
            top: -6px;
            left: 50%;
            margin-left: -20mm;
            width: 40mm;
            background: #fff;
            font-size: 6.5pt;
            font-weight: bold;
        }
    </style>
</head>

<body>
    @php
        $logoPath = public_path('img/logo.jpg');
        // Para DomPDF offline em alguns ambientes, base64 é mais seguro
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/jpeg;base64,' . $logoData;
        } else {
            $logoSrc = '';
        }
    @endphp

    @for($i = 0; $i < $quantidade; $i++)
        @if($i % 2 == 0)
            <div class="page">
        @endif

            <div class="guia-wrapper">
                <div class="header clearfix">
                    <div class="logo-container">
                        @if($logoSrc)
                            <img src="{{ $logoSrc }}" class="logo">
                        @else
                            <div style="font-weight: bold; color: #1a237e;">Sindicato Químicos Unificados</div>
                        @endif
                    </div>
                    <div class="title-container">
                        <h1 class="main-title">PRÉ-RESERVA</h1>
                        <div class="sub-title">{{ $colonia->nome }}</div>
                    </div>
                </div>

                <div class="info-section">
                    <div class="field-row">
                        <span class="label">Pendente de Sorteio / Beneficiário</span>
                        <span class="value" style="border-bottom: 2px solid #333;">&nbsp;</span>
                    </div>

                    <div class="clearfix" style="margin-top: 3mm;">
                        <div class="col-2">
                            <span class="label">Data do Sorteio</span>
                            <span
                                class="value">{{ $periodo->data_sorteio ? $periodo->data_sorteio->format('d/m/Y') : '____/____/________' }}</span>
                        </div>
                        <div class="col-2-right">
                            <span class="label">Período de Estadia</span>
                            <span class="value">{{ $periodo->data_inicial->format('d/m/Y') }} à
                                {{ $periodo->data_final->format('d/m/Y') }}</span>
                        </div>
                    </div>

                    <div class="field-row" style="margin-top: 3mm;">
                        <span class="label">Identificação da Semana / Bloco</span>
                        <span class="value">{{ $periodo->descricao }}</span>
                    </div>
                </div>

                <div class="alert-box">
                    <div class="alert-title">Confirmação e Pagamento</div>
                    <div class="alert-content">
                        Para garantir sua reserva, o pagamento integral deve ser realizado impreterivelmente até:<br>
                        <span class="deadline-text">
                            {{ $periodo->data_limite_pagamento ? $periodo->data_limite_pagamento->format('d/m/Y') . ' (' . $periodo->data_limite_pagamento->locale('pt_BR')->dayName . ')' : '____/____/________' }}
                        </span>
                    </div>
                </div>

                <div class="alert-box" style="border-left-color: #d32f2f; margin-top: 1.5mm;">
                    <div class="alert-title" style="color: #d32f2f;">Regras de Desistência</div>
                    <div class="alert-content">
                        • Até 5 dias de antecedência: 100% de crédito.<br>
                        • 4 dias ou menos: 50% de crédito.<br>
                        • <strong>Sem aviso prévio: não haverá devolução.</strong>
                    </div>
                </div>

                <div class="clearfix" style="margin-top: 2.5mm;">
                    <div style="font-size: 7.5pt; color: #555; line-height: 1.2;">
                        <strong>WhatsApp: +55 19 97405-5662</strong> — Envie comprovante e documentos aqui.
                    </div>
                </div>

                <div class="footer">
                    Portal de Aplicativos - {{ date('d/m/Y H:i') }}
                </div>
            </div>

            @if(($i + 1) % 2 != 0 && ($i + 1) < $quantidade)
                <div class="cutting-line">
                    <div class="cutting-label">TESOURA / CORTE AQUI</div>
                </div>
            @endif

            @if(($i + 1) % 2 == 0 || ($i + 1) == $quantidade)
                </div> <!-- End page -->
                @if(($i + 1) < $quantidade)
                    <div style="page-break-after: always;"></div>
                @endif
            @endif
    @endfor
</body>

</html>