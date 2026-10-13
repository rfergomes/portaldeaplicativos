<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Requerimento Formal de Desfiliação Sindical</title>
    <style>
        @page {
            margin: 25mm 20mm 25mm 20mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #1a1a1a;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 15pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header .entidade {
            font-size: 11pt;
            font-weight: bold;
            color: #333;
            margin-top: 5px;
        }
        .header .setor {
            font-size: 9pt;
            color: #555;
            margin-top: 2px;
        }
        .doc-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            margin: 18px 0;
            text-decoration: underline;
        }
        .paragraph {
            text-align: justify;
            margin-bottom: 12px;
            text-indent: 25px;
        }
        .item-destaque {
            margin: 10px 0 10px 25px;
            text-align: justify;
        }
        .item-destaque strong {
            text-transform: uppercase;
        }
        .box-responsabilidade {
            background-color: #f8fafc;
            border-left: 3px solid #334155;
            padding: 10px 14px;
            margin: 14px 0;
            font-size: 10pt;
            text-align: justify;
        }
        .data-local {
            text-align: right;
            margin-top: 25px;
            margin-bottom: 20px;
            font-size: 11pt;
        }
        .assinatura-container {
            text-align: center;
            margin-top: 15px;
        }
        .rubrica-img {
            max-width: 220px;
            max-height: 70px;
            margin-bottom: 5px;
        }
        .linha-assinatura {
            width: 320px;
            border-top: 1px solid #333;
            margin: 0 auto 5px auto;
        }
        .nome-assinante {
            font-weight: bold;
            font-size: 11pt;
        }
        .cpf-assinante {
            font-size: 10pt;
            color: #444;
        }
        /* Box de Trilha Probatória de Autenticidade */
        .box-probatario {
            margin-top: 25px;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            padding: 10px;
            font-size: 7.5pt;
            line-height: 1.35;
            color: #334155;
            border-radius: 4px;
        }
        .box-probatario-titulo {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin-bottom: 6px;
            color: #0f172a;
        }
        .tabela-probatario {
            width: 100%;
            border-collapse: collapse;
        }
        .tabela-probatario td {
            vertical-align: top;
            padding: 2px 4px;
        }
        .hash-code {
            font-family: 'Courier', monospace;
            font-size: 7.5pt;
            color: #1e3a8a;
            word-break: break-all;
        }
        .qr-cell {
            width: 85px;
            text-align: center;
            vertical-align: middle;
        }
        .qr-img {
            width: 75px;
            height: 75px;
        }
    </style>
</head>
<body>
    @php
        setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
        $meses = [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
            5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
            9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'
        ];
        $dataRef = $desfiliacao->confirmado_em ?? now();
        $dia = $dataRef->format('d');
        $mes = $meses[(int)$dataRef->format('m')] ?? $dataRef->format('F');
        $ano = $dataRef->format('Y');
        $cidade = config('app.cidade_sede', 'Campinas - SP');
        $urlValidacao = route('desfiliacao.validar', ['hash' => $desfiliacao->termo_hash]);
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=90x90&data=' . urlencode($urlValidacao);
    @endphp

    <div class="header">
        <h1>REQUERIMENTO FORMAL DE DESFILIAÇÃO SINDICAL</h1>
        <div class="entidade">À Diretoria do Sindicato</div>
        <div class="setor">A/C: Departamento de Cadastro e Arrecadação</div>
    </div>

    <div class="paragraph">
        Eu, <strong>{{ $desfiliacao->nome_completo }}</strong>, portador(a) do CPF nº <strong>{{ $desfiliacao->cpf_formatado }}</strong>,
        matrícula funcional nº <strong>{{ $desfiliacao->matricula ?: 'Não informado' }}</strong>,
        atualmente empregado(a) na empresa <strong>{{ $desfiliacao->empresa }}</strong>,
        venho, por meio deste instrumento e por livre e espontânea manifestação de vontade:
    </div>

    <div class="item-destaque">
        <strong>REQUERER A MINHA DESFILIAÇÃO IMEDIATA</strong> do quadro de sócios/associados desta entidade sindical, renunciando expressamente a quaisquer benefícios estatutários, convênios ou prerrogativas vinculadas à condição de filiado;
    </div>

    <div class="item-destaque">
        <strong>MANIFESTAR MEU DESEJO DE INTERRUPÇÃO</strong> de qualquer desconto de mensalidade associativa ou contribuição voluntária em folha de pagamento em favor desta agremiação, ato amparado pelas garantias constitucionais da liberdade associativa e sindical previstas no art. 5º, inciso XX, e no art. 8º, inciso V, da Constituição Federal de 1988;
    </div>

    <div class="box-responsabilidade">
        <strong>DECLARAR CIÊNCIA DE RESPONSABILIDADE EXCLUSIVA:</strong><br>
        Declaro estar plenamente ciente e de acordo que é de minha inteira e exclusiva responsabilidade apresentar e protocolar a via/cópia deste termo devidamente emitido e assinado junto ao Departamento de Recursos Humanos (RH) ou Departamento Pessoal da minha empresa empregadora, a fim de que esta cesse os descontos mensais em folha de pagamento. Reconheço que esta entidade sindical processará a baixa no seu quadro interno de associados, cabendo a mim a comunicação perante a fonte pagadora.
    </div>

    <div class="paragraph">
        Por ser a expressão fiel da minha vontade, firmo o presente requerimento.
    </div>

    <div class="data-local">
        {{ $cidade }}, {{ $dia }} de {{ $mes }} de {{ $ano }}.
    </div>

    <div class="assinatura-container">
        @if($desfiliacao->assinatura_base64)
            <div>
                <img src="{{ $desfiliacao->assinatura_base64 }}" class="rubrica-img" alt="Rubrica">
            </div>
        @endif
        <div class="linha-assinatura"></div>
        <div class="nome-assinante">{{ $desfiliacao->nome_completo }}</div>
        <div class="cpf-assinante">CPF: {{ $desfiliacao->cpf_formatado }}</div>
    </div>

    <!-- Trilha Probatória de Autenticidade (Lei Federal nº 14.063/2020) -->
    <div class="box-probatario">
        <div class="box-probatario-titulo">
            EVIDÊNCIAS DE AUTENTICIDADE E TRILHA DE AUDITORIA DIGITAL (LEI FEDERAL Nº 14.063/2020)
        </div>
        <table class="tabela-probatario">
            <tr>
                <td>
                    <strong>Identificador do Requerimento:</strong> #{{ str_pad((string)$desfiliacao->id, 6, '0', STR_PAD_LEFT) }}<br>
                    <strong>Data/Hora da Solicitação:</strong> {{ $desfiliacao->created_at->format('d/m/Y H:i:s') }} (IP: {{ $desfiliacao->ip_solicitacao ?: 'Não registrado' }})<br>
                    <strong>Data/Hora da Confirmação:</strong> {{ $desfiliacao->confirmado_em ? $desfiliacao->confirmado_em->format('d/m/Y H:i:s') : 'N/A' }} (IP: {{ $desfiliacao->ip_confirmacao ?: 'Não registrado' }})<br>
                    <strong>E-mail Autenticado:</strong> {{ $desfiliacao->email }}<br>
                    <strong>Hash Criptográfico SHA-256:</strong><br>
                    <span class="hash-code">{{ $desfiliacao->termo_hash }}</span><br>
                    <strong>Validação Pública da Autenticidade:</strong><br>
                    <span style="font-size: 7pt; color: #2563eb;">{{ $urlValidacao }}</span>
                </td>
                <td class="qr-cell">
                    <img src="{{ $qrCodeUrl }}" class="qr-img" alt="QR Code de Autenticidade"><br>
                    <span style="font-size: 6.5pt; color: #64748b;">Consulte pelo QR Code</span>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
