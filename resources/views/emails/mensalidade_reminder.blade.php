<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
        }

        .container {
            width: 80%;
            max-width: 600px;
            margin: 20px auto;
            padding: 25px;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #edf2f7;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 1.5em;
            margin: 0;
        }

        .info-box {
            background: #f7fafc;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #4299e1;
            margin: 20px 0;
        }

        .info-box p {
            margin: 5px 0;
        }

        .footer {
            font-size: 0.8em;
            color: #718096;
            margin-top: 30px;
            border-top: 1px solid #edf2f7;
            padding-top: 15px;
            text-align: center;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3182ce;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
        }

        /* Cores temáticas para o alerta */
        .color-10 {
            color: #2b6cb0;
        }
        .color-5 {
            color: #dd6b20;
        }
        .color-1 {
            color: #e53e3e;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            @if($daysRemaining === 10)
                <h1 class="color-10">Lembrete de Vencimento (10 dias)</h1>
            @elseif($daysRemaining === 5)
                <h1 class="color-5">Atenção ao Vencimento (5 dias)</h1>
            @elseif($daysRemaining === 1)
                <h1 class="color-1">URGENTE: Vencimento Amanhã!</h1>
            @else
                <h1>Lembrete de Vencimento</h1>
            @endif
            <p>Mensalidade Associativa - {{ $socio->empresa->razao_social }}</p>
        </div>

        <p>Olá, <strong>{{ $cliente->nome }}</strong>,</p>

        @if($daysRemaining === 10)
            <p>Gostaríamos de lembrar que a Mensalidade Associativa da empresa <strong>{{ $socio->empresa->razao_social }}</strong> vencerá em <strong>10 dias</strong> (no dia <strong>{{ \Carbon\Carbon::parse($socio->data_vencimento)->format('d/m/Y') }}</strong>).</p>
        @elseif($daysRemaining === 5)
            <p>Faltam apenas <strong>5 dias</strong> para o vencimento da Mensalidade Associativa da empresa <strong>{{ $socio->empresa->razao_social }}</strong> (no dia <strong>{{ \Carbon\Carbon::parse($socio->data_vencimento)->format('d/m/Y') }}</strong>). Por favor, evite atrasos para garantir a manutenção dos seus benefícios.</p>
        @elseif($daysRemaining === 1)
            <p><strong>Aviso importante:</strong> A Mensalidade Associativa da empresa <strong>{{ $socio->empresa->razao_social }}</strong> vence <strong>amanhã</strong> (no dia <strong>{{ \Carbon\Carbon::parse($socio->data_vencimento)->format('d/m/Y') }}</strong>). Pedimos a sua atenção para a quitação do boleto.</p>
        @else
            <p>Lembramos que o vencimento da Mensalidade Associativa da empresa <strong>{{ $socio->empresa->razao_social }}</strong> está agendado para o dia <strong>{{ \Carbon\Carbon::parse($socio->data_vencimento)->format('d/m/Y') }}</strong>.</p>
        @endif

        <div class="info-box">
            <p><strong>Referência:</strong> {{ str_pad($socio->mes, 2, '0', STR_PAD_LEFT) }}/{{ $socio->ano }}</p>
            <p><strong>Vencimento:</strong> {{ \Carbon\Carbon::parse($socio->data_vencimento)->format('d/m/Y') }}</p>
        </div>

        <p>Caso precise emitir a segunda via ou gerar o boleto, acesse o portal clicando no botão abaixo:</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="https://quimicosunificados.ddns.net:8050/boletos" class="btn" target="_blank">Gerar/Acessar Boleto</a>
        </div>

        <p><em>Obs: Se o pagamento já foi realizado, por favor desconsidere este e-mail.</em></p>

        <div class="footer">
            <p>Este é um e-mail automático enviado pelo Portal de Aplicativos. Por favor, não responda.</p>
            <p>&copy; {{ date('Y') }} Portal de Aplicativos - Todos os direitos reservados.</p>
        </div>
    </div>
</body>

</html>
