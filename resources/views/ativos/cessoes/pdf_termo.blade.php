<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Termo de Cessão de Uso - {{ $cessao->codigo_cessao }}</title>
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
        <div class="title">Termo de Cessão de Uso de Equipamentos</div>
    </div>

    <div class="section">
        <p><span class="label">CEDENTE:</span> O SINDICATO DOS QUÍMICOS UNIFICADOS REGIONAL CAMPINAS, pessoa jurídica de direito privado, inscrita no CNPJ sob o nº 46.095.717/0001-65, com sede na Avenida Barão de Itapura, 2022 – Guanabara - Campinas/SP.</p>
        
        <p><span class="label">CESSIONÁRIO(A):</span> <span style="text-transform: uppercase; font-weight: bold;">{{ $cessao->usuario->nome }}</span>, portador(a) do CPF nº {{ $cessao->usuario->cpf ?? '___.___.___-__' }}, residente e domiciliado(a) no endereço: {{ $cessao->usuario->endereco ?? '__________________________________' }}.</p>
    </div>

    <div class="section">
        <p>Pelo presente instrumento, a CEDENTE cede à CESSIONÁRIA, a título de empréstimo gratuito (comodato), o pleno uso dos bens móveis abaixo discriminados, mediante as seguintes cláusulas e condições:</p>
    </div>

    <div class="section">
        <div class="section-title">CLÁUSULA PRIMEIRA - DOS EQUIPAMENTOS</div>
        <p>A CEDENTE entrega neste ato à CESSIONÁRIA, os seguintes equipamentos em perfeito estado de funcionamento e conservação:</p>
        
        <div class="item-list">
            @foreach($cessao->movimentacoes as $mov)
            <div class="item">
                01 {{ $mov->equipamento->descricao }} {{ $mov->equipamento->modelo }} 
                (N/S: {{ $mov->equipamento->numero_serie ?? 'N/A' }}) - 
                @if($mov->equipamento->valor_nota) 
                    Observação: NF.: {{ $mov->equipamento->valor_nota }} - R$ {{ number_format($mov->equipamento->valor_item, 2, ',', '.') }}
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <div class="section">
        <div class="section-title">CLÁUSULA SEGUNDA - DAS OBRIGAÇÕES</div>
        <p>A CESSIONÁRIA se compromete a utilizar os equipamentos exclusivamente para o desempenho de suas atividades profissionais, zelando por sua integridade e conservação. Fica vedado o empréstimo, aluguel ou cessão dos bens a terceiros.</p>
    </div>

    <div class="section">
        <div class="section-title">CLÁUSULA TERCEIRA - DA RESPONSABILIDADE</div>
        <p>A CESSIONÁRIA será responsável por quaisquer danos, perda, roubo ou furto dos equipamentos. Em caso de dano, deverá comunicar imediatamente à CEDENTE para que sejam tomadas as devidas providências.</p>
    </div>

    <div class="section">
        <div class="section-title">CLÁUSULA QUARTA - DA DEVOLUÇÃO</div>
        <p>Os equipamentos deverão ser devolvidos à CEDENTE ao término do vínculo de trabalho ou a qualquer momento mediante solicitação, nas mesmas condições em que foram recebidos, ressalvado o desgaste natural pelo uso. A data de devolução prevista é {{ $cessao->movimentacoes->max('data_previsao_devolucao') ? $cessao->movimentacoes->max('data_previsao_devolucao')->format('d/m/Y') : 'Prazo Indeterminado' }}.</p>
    </div>

    <div class="section">
        <p>E, por estarem de acordo, assinam o presente termo em 2 (duas) vias de igual teor.</p>
        <p>Campinas, {{ $cessao->data_cessao->translatedFormat('d \d\e F \d\e Y') }}</p>
    </div>

    <table class="signature-table">
        <tr>
            <td>Cedente</td>
            <td class="spacer"></td>
            <td>Cessionária</td>
        </tr>
    </table>
</body>
</html>
