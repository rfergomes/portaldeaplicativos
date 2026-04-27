<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Termo de Devolução de Equipamentos - {{ $cessao->codigo_cessao }}</title>
    <style>
        body { font-family: sans-serif; font-size: 13px; line-height: 1.6; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { max-width: 150px; margin-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; text-transform: uppercase; margin: 20px 0; }
        .section { margin-bottom: 25px; text-align: justify; }
        .section-title { font-weight: bold; text-transform: uppercase; margin-bottom: 10px; }
        .data-row { margin-bottom: 5px; }
        .label { font-weight: bold; }
        .item-list { margin-top: 15px; }
        .item { margin-bottom: 10px; padding-left: 20px; position: relative; }
        .item:before { content: "-"; position: absolute; left: 0; }
        .footer { margin-top: 50px; }
        .signature-table { width: 100%; margin-top: 60px; }
        .signature-table td { width: 45%; text-align: center; border-top: 1px solid #000; padding-top: 10px; }
        .signature-table .spacer { width: 10%; border-top: 0; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
             <img src="{{ public_path('img/logo.jpg') }}" alt="Logo" style="max-width: 150px;">
        </div>
        <div class="title">Termo de Devolução de Equipamentos</div>
    </div>

    <div class="section">
        <p><span class="label">CEDENTE:</span> O SINDICATO DOS QUÍMICOS UNIFICADOS REGIONAL CAMPINAS, pessoa jurídica de direito privado, inscrita no CNPJ sob o nº 46.095.717/0001-65, com sede na Avenida Barão de Itapura, 2022 – Guanabara - Campinas/SP.</p>
        
        <p><span class="label">CESSIONÁRIO(A):</span> <span style="text-transform: uppercase; font-weight: bold;">{{ $cessao->usuario->nome }}</span>, portador(a) do CPF nº {{ $cessao->usuario->cpf ?? '___.___.___-__' }}, residente e domiciliado(a) no endereço: {{ $cessao->usuario->endereco ?? '__________________________________' }}.</p>
    </div>

    <div class="section">
        <p>Pelo presente instrumento, a CESSIONÁRIA declara ter devolvido à CEDENTE os equipamentos abaixo discriminados, em conformidade com o Termo de Cessão de Uso nº <strong>{{ $cessao->codigo_cessao }}</strong>.</p>
    </div>

    <div class="section">
        <div class="section-title">EQUIPAMENTOS DEVOLVIDOS</div>
        <p>A CEDENTE declara ter recebido da CESSIONÁRIA os seguintes equipamentos:</p>
        
        <div class="item-list">
            @foreach($movimentacoes as $mov)
            <div class="item">
                01 {{ $mov->equipamento->descricao }} {{ $mov->equipamento->modelo }} 
                (N/S: {{ $mov->equipamento->numero_serie ?? 'N/A' }}) - 
                Status de Recebimento: Conforme.
                @if($mov->observacao)
                    <br><small>Observação: {{ $mov->observacao }}</small>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <div class="section">
        <p>Com a entrega dos equipamentos acima descritos, a CEDENTE dá plena e geral quitação à CESSIONÁRIA quanto à guarda e conservação dos mesmos, encerrando-se as obrigações relativas ao comodato destes itens específicos.</p>
    </div>

    <div class="section">
        <p>Campinas, {{ now()->translatedFormat('d \d\e F \d\e Y') }}</p>
    </div>

    <table class="signature-table">
        <tr>
            <td>Cedente (Recebedor)</td>
            <td class="spacer"></td>
            <td>Cessionária (Devolvedor)</td>
        </tr>
    </table>
</body>
</html>
