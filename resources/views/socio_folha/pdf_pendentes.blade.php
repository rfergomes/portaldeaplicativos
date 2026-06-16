<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Pendências - Sócio Folha</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0056b3; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #0056b3; font-size: 20px; }
        .header p { margin: 5px 0 0; color: #666; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; font-size: 11px; }
        th { background-color: #f4f4f4; color: #333; font-weight: bold; text-transform: uppercase; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .total-row td { font-weight: bold; background-color: #f9f9f9; }
        .footer { position: fixed; bottom: -20px; left: 0px; right: 0px; height: 30px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Lançamentos Pendentes - Sócio Folha</h1>
        <p>Gerado em {{ date('d/m/Y H:i') }} - Reflete os filtros aplicados na tela</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Empresa</th>
                <th>Região</th>
                <th class="text-center">Referência</th>
                <th class="text-center">Vencimento</th>
                <th class="text-right">Valor (R$)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalValor = 0; @endphp
            @forelse($pendentes as $p)
                @php $totalValor += $p->valor_mensalidade; @endphp
                <tr>
                    <td>
                        <strong>{{ $p->empresa->razao_social ?? 'N/A' }}</strong><br>
                        <span style="font-size: 9px; color: #666;">CNPJ/Cod: {{ $p->empresa->cnpj ?? $p->empresa->empresa_erp }}</span>
                    </td>
                    <td>{{ $p->regiao->nome ?? 'N/A' }}</td>
                    <td class="text-center">{{ str_pad($p->mes, 2, '0', STR_PAD_LEFT) }}/{{ $p->ano }}</td>
                    <td class="text-center">{{ $p->data_vencimento ? $p->data_vencimento->format('d/m/Y') : 'N/A' }}</td>
                    <td class="text-right">{{ number_format($p->valor_mensalidade, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Nenhuma pendência encontrada com os filtros atuais.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right">TOTAL PENDENTE:</td>
                <td class="text-right">R$ {{ number_format($totalValor, 2, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="4" class="text-right">QUANTIDADE DE LANÇAMENTOS:</td>
                <td class="text-right">{{ $pendentes->count() }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Portal de Aplicativos - Sócio Folha
    </div>
</body>
</html>
