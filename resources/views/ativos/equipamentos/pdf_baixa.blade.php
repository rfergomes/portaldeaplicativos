<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Termo de Baixa de Equipamento #{{ $equipamento->id }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #ccc;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            margin: 0 0 5px 0;
            color: #d9534f;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            color: #777;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            background-color: #f5f5f5;
            padding: 5px 10px;
            border-left: 4px solid #d9534f;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #f9f9f9;
            width: 30%;
            font-weight: bold;
            color: #555;
        }
        .signature-area {
            margin-top: 50px;
            text-align: center;
        }
        .signature-line {
            width: 60%;
            border-top: 1px solid #333;
            margin: 0 auto 5px auto;
        }
        .date-location {
            text-align: right;
            margin-top: 30px;
            font-style: italic;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Termo de Baixa de Equipamento</h1>
        <p>Documento Emitido em {{ now()->format('d/m/Y \à\s H:i') }}</p>
    </div>

    <p style="text-align: justify; margin-bottom: 20px;">
        Pelo presente instrumento, atestamos a baixa definitiva do equipamento descrito abaixo
        do nosso inventário de ativos, conforme registro efetuado no sistema.
    </p>

    <div class="section">
        <div class="section-title">Dados do Equipamento</div>
        <table>
            <tr>
                <th>Identificador</th>
                <td>{{ $equipamento->descricao }}</td>
            </tr>
            <tr>
                <th>Categoria</th>
                <td>{{ $equipamento->categoria ?? 'Não Informada' }}</td>
            </tr>
            <tr>
                <th>Fabricante</th>
                <td>{{ $equipamento->fabricante->nome ?? 'Não Informado' }}</td>
            </tr>
            <tr>
                <th>Modelo</th>
                <td>{{ $equipamento->modelo ?? 'Não Informado' }}</td>
            </tr>
            <tr>
                <th>Número de Série</th>
                <td>{{ $equipamento->numero_serie ?? 'Não Informado' }}</td>
            </tr>
            <tr>
                <th>Nota Fiscal</th>
                <td>{{ $equipamento->aquisicao->numero_nf ?? 'Sem NF vinculada' }}</td>
            </tr>
            <tr>
                <th>Valor Aprox.</th>
                <td>R$ {{ number_format($equipamento->valor_nota, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Detalhes da Baixa</div>
        <table>
            <tr>
                <th>Data da Baixa</th>
                <td>{{ $movimentacao->data_movimentacao->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Responsável pelo Registro</th>
                <td>{{ $movimentacao->responsavel->name ?? 'Sistema' }}</td>
            </tr>
            <tr>
                <th>Motivo / Observações</th>
                <td>{{ $movimentacao->observacao ?? strtoupper('Sem observações complementares.') }}</td>
            </tr>
        </table>
    </div>

    <div class="date-location">
        São Paulo, SP - {{ now()->format('d \d\e F \d\e Y') }}
    </div>

    <div class="signature-area">
        <div class="signature-line"></div>
        <p style="margin: 0; font-weight: bold;">{{ $movimentacao->responsavel->name ?? 'Responsável' }}</p>
        <p style="margin: 0; font-size: 12px; color: #777;">Autorização de Baixa de Ativo</p>
    </div>

    <div class="signature-area" style="margin-top: 80px;">
        <div class="signature-line"></div>
        <p style="margin: 0; font-weight: bold;">Departamento de Contabilidade / Financeiro</p>
        <p style="margin: 0; font-size: 12px; color: #777;">Ciente para exclusão contábil</p>
    </div>

</body>
</html>
