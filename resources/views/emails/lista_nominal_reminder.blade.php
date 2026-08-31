<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #334155;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            max-width: 620px;
            margin: 30px auto;
            padding: 30px;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 1.4em;
            color: #033c5a;
            margin: 0 0 5px 0;
            font-weight: 700;
        }

        .header p {
            margin: 0;
            font-size: 0.9em;
            color: #64748b;
            font-weight: 600;
        }

        .greeting {
            font-size: 1.05em;
            margin-bottom: 15px;
            color: #1e293b;
        }

        .info-box {
            background: #f8fafc;
            padding: 16px 20px;
            border-radius: 8px;
            border-left: 4px solid #033c5a;
            margin: 20px 0;
            border-top: 1px solid #edf2f7;
            border-right: 1px solid #edf2f7;
            border-bottom: 1px solid #edf2f7;
        }

        .info-box p {
            margin: 6px 0;
            font-size: 0.92em;
        }

        .clausula-card {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 18px;
            margin: 25px 0;
        }

        .clausula-header {
            font-weight: 700;
            color: #1e40af;
            font-size: 0.95em;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .clausula-text {
            font-size: 0.88em;
            color: #1e3a8a;
            line-height: 1.55;
            font-style: italic;
            margin: 0;
            white-space: pre-line;
        }

        .disclaimer-box {
            background-color: #f1f5f9;
            padding: 14px 18px;
            border-radius: 6px;
            font-size: 0.85em;
            color: #475569;
            margin: 20px 0;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #033c5a;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
            font-size: 0.95em;
        }

        .footer {
            font-size: 0.78em;
            color: #94a3b8;
            margin-top: 30px;
            border-top: 1px solid #f1f5f9;
            padding-top: 20px;
            text-align: center;
            line-height: 1.5;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Lembrete de Envio da Lista Nominal</h1>
            <p>{{ $socio->empresa->razao_social ?? 'Entidade Empregadora' }}</p>
        </div>

        <p class="greeting">Prezados(as) Senhores(as) / Olá <strong>{{ $cliente->nome }}</strong>,</p>

        <p>
            Esperamos que esta mensagem o(a) encontre bem.
        </p>

        <p>
            Gostaríamos de lembrar cordialmente sobre o encaminhamento da <strong>relação nominal dos empregados contribuintes e respectivos valores descontados</strong> referente à contribuição associativa/mensalidade da empresa <strong>{{ $socio->empresa->razao_social }}</strong>.
        </p>

        <div class="info-box">
            <p><strong>Empresa:</strong> {{ $socio->empresa->razao_social }}</p>
            <p><strong>CNPJ:</strong> {{ $socio->empresa->cnpj ?? 'Não informado' }}</p>
            <p><strong>Competência de Referência:</strong> {{ str_pad((string)$socio->mes, 2, '0', STR_PAD_LEFT) }}/{{ $socio->ano }}</p>
            <p><strong>Data de Vencimento da Contribuição:</strong> {{ \Carbon\Carbon::parse($socio->data_vencimento)->format('d/m/Y') }}</p>
        </div>

        @if($clausula)
            <div class="clausula-card">
                <div class="clausula-header">
                    Cláusula {{ $clausula->numero }} - {{ $clausula->titulo }}
                    @if($convencao)
                        <span style="font-weight: normal; color: #64748b; font-size: 0.85em; margin-left: 8px;">({{ $convencao->titulo }})</span>
                    @endif
                </div>
                <p class="clausula-text">"{{ $clausula->texto }}"</p>
            </div>
        @else
            <div class="clausula-card">
                <div class="clausula-header">
                    Cláusula 76 - Contribuições Associativas Mensais
                </div>
                <p class="clausula-text">"As empresas fornecerão no prazo de 15 (quinze) dias, contados da data de recolhimento, às respectivas entidades sindicais dos trabalhadores, em caráter confidencial e mediante recibo, uma relação contendo os nomes e valores da contribuição."</p>
            </div>
        @endif

        <p>
            A relação nominal é fundamental para a correta identificação dos associados e manutenção dos seus direitos e benefícios sindicais. Solicitamos a gentileza de encaminhar o arquivo/documento respondendo diretamente a este e-mail ou para o nosso setor de arrecadação no endereço <a href="mailto:arrecadacao@quimicosunificados.com.br" style="color: #033c5a; font-weight: bold;">arrecadacao@quimicosunificados.com.br</a>.
        </p>

        <div class="disclaimer-box">
            <strong><i class="fa-solid fa-circle-info"></i> Mensagem Automática:</strong> Caso a relação nominal e comprovante referente a esta competência já tenham sido transmitidos para a entidade sindical, por favor <strong>desconsidere este aviso</strong>.
        </div>

        <div class="footer">
            <p>Este é um comunicado automático gerado pelo Portal de Aplicativos - Químicos Unificados.</p>
            <p>&copy; {{ date('Y') }} Sindicato dos Químicos Unificados de Campinas e Região. Todos os direitos reservados.</p>
        </div>
    </div>
</body>

</html>
