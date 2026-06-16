<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Débitos da Empresa - Sócio Folha</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #dc3545; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #dc3545; font-size: 20px; }
        .header h3 { margin: 5px 0; color: #333; font-size: 16px; }
        .header p { margin: 2px 0; color: #666; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; font-size: 11px; }
        th { background-color: #f4f4f4; color: #333; font-weight: bold; text-transform: uppercase; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .total-row td { font-weight: bold; background-color: #fff3f3; color: #dc3545; }
        .footer { position: fixed; bottom: -20px; left: 0px; right: 0px; height: 30px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Extrato de Débitos em Aberto</h1>
        <h3>{{ $empresa->razao_social }}</h3>
        <p><strong>CNPJ:</strong> {{ $empresa->cnpj ?? 'N/A' }} | <strong>Cód. ERP:</strong> {{ $empresa->empresa_erp ?? 'N/A' }}</p>
        <p>Gerado em {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">Referência</th>
                <th class="text-center">Vencimento</th>
                <th class="text-center">Situação</th>
                <th class="text-right">Valor (R$)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalValor = 0; @endphp
            @forelse($debitos as $d)
                @php $totalValor += $d->valor_mensalidade; @endphp
                <tr>
                    <td class="text-center">{{ str_pad($d->mes, 2, '0', STR_PAD_LEFT) }}/{{ $d->ano }}</td>
                    <td class="text-center">{{ $d->data_vencimento ? $d->data_vencimento->format('d/m/Y') : 'N/A' }}</td>
                    <td class="text-center" style="color: #dc3545; font-weight: bold;">ABERTO</td>
                    <td class="text-right">{{ number_format($d->valor_mensalidade, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Nenhum débito em aberto encontrado.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right">TOTAL EM ABERTO:</td>
                <td class="text-right">R$ {{ number_format($totalValor, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Portal de Aplicativos - Departamento de Arrecadação
    </div>
</body>
</html>
