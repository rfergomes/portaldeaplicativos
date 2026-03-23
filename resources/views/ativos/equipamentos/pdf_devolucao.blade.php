<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Termo de Devolução de Equipamento #{{ $movimentacao->equipamento->identificador }}</title>
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
            color: #0d6efd;
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
            border-left: 4px solid #0d6efd;
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
            width: 100%;
        }
        .signature-table {
            width: 100%;
            border: none;
            margin-top: 40px;
        }
        .signature-table td {
            border: none;
            text-align: center;
            width: 50%;
            padding: 0 20px;
        }
        .signature-line {
            width: 100%;
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
        <h1>Termo de Devolução de Equipamento</h1>
        <p>Documento Emitido em {{ now()->format('d/m/Y \à\s H:i') }}</p>
    </div>

    <p style="text-align: justify; margin-bottom: 20px;">
        Pelo presente instrumento, atesta-se a devolução definitiva do equipamento descrito abaixo
        feita pelo cessionário à administração central, encerrando-se a responsabilidade de uso e guarda do mesmo.
    </p>

    <div class="section">
        <div class="section-title">Dados do Equipamento Restituído</div>
        <table>
            <tr>
                <th>Descrição</th>
                <td>{{ $movimentacao->equipamento->descricao }}</td>
            </tr>
            <tr>
                <th>Identificador</th>
                <td>{{ $movimentacao->equipamento->identificador ?? 'Não Informado' }}</td>
            </tr>
            <tr>
                <th>Fabricante</th>
                <td>{{ $movimentacao->equipamento->fabricante->nome ?? 'Não Informado' }}</td>
            </tr>
            <tr>
                <th>Modelo</th>
                <td>{{ $movimentacao->equipamento->modelo ?? 'Não Informado' }}</td>
            </tr>
            <tr>
                <th>Número de Série</th>
                <td>{{ $movimentacao->equipamento->numero_serie ?? 'Não Informado' }}</td>
            </tr>
            <tr>
                <th>Acessórios Devolvidos</th>
                <td>{{ $movimentacao->acessorios ?? 'Nenhum acessório registrado ou vazio.' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Detalhes da Devolução</div>
        <table>
            <tr>
                <th>Cessionário Devolvedor</th>
                <td>{{ $movimentacao->cessao->usuario->nome ?? 'Usuário Desconhecido' }} ({{ $movimentacao->cessao->usuario->empresa->razao_social ?? 'S/ Empresa' }})</td>
            </tr>
            <tr>
                <th>Data da Devolução</th>
                <td>{{ $movimentacao->data_movimentacao->format('d/m/Y \à\s H:i') }}</td>
            </tr>
            <tr>
                <th>Receptor / Responsável</th>
                <td>{{ $movimentacao->responsavel->name ?? 'Sistema' }}</td>
            </tr>
            <tr>
                <th>Observações de Retorno</th>
                <td>{{ $movimentacao->observacao ?? strtoupper('Recebido em conformidade, sem ressalvas.') }}</td>
            </tr>
        </table>
    </div>

    <div class="date-location">
        São Paulo, SP - {{ now()->format('d \d\e F \d\e Y') }}
    </div>

    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-line"></div>
                <p style="margin: 0; font-weight: bold;">Cessionário (Devolvedor)</p>
                <p style="margin: 0; font-size: 12px; color: #777;">Assinatura de quem entregou o bem</p>
            </td>
            <td>
                <div class="signature-line"></div>
                <p style="margin: 0; font-weight: bold;">{{ $movimentacao->responsavel->name ?? 'Responsável' }}</p>
                <p style="margin: 0; font-size: 12px; color: #777;">Recebedor (Administração)</p>
            </td>
        </tr>
    </table>

</body>
</html>
