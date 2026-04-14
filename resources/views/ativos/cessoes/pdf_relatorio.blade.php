<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Cessões de Equipamentos</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 10px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 11px;
            color: #555;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background-color: #f2f2f2;
            border: 1px solid #999;
            padding: 5px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
        }
        td {
            border: 1px solid #999;
            padding: 5px;
            vertical-align: top;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #777;
            padding-top: 5px;
            border-top: 1px solid #ccc;
        }
        .filter-info {
            background-color: #f8f9fa;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('img/logo.jpg')))
            <div style="text-align: center; margin-bottom: 10px;">
                <img src="{{ public_path('img/logo.jpg') }}" alt="Logo" style="max-width: 100px;">
            </div>
        @endif
        <div class="title">Relatório de Cessões de Equipamentos</div>
        <div class="subtitle">Gerado em {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="filter-info">
        <strong>Filtros aplicados:</strong>
        @if($request->filled('search')) [Código: {{ $request->search }}] @endif
        @if($request->filled('usuario_id')) [Cessionário: {{ \App\Models\AtivoUsuario::find($request->usuario_id)->nome ?? 'N/D' }}] @endif
        @if($request->filled('data_inicio')) [Início: {{ date('d/m/Y', strtotime($request->data_inicio)) }}] @endif
        @if($request->filled('data_fim')) [Fim: {{ date('d/m/Y', strtotime($request->data_fim)) }}] @endif
        @if(!$request->filled('search') && !$request->filled('usuario_id') && !$request->filled('data_inicio') && !$request->filled('data_fim'))
            Nenhum (Todos os registros)
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">CESSÃO</th>
                <th style="width: 10%;">DATA</th>
                <th style="width: 20%;">CESSIONÁRIO / EMPRESA</th>
                <th style="width: 50%;">ITENS (ID - DESCRIÇÃO - MODELO - SÉRIE)</th>
                <th style="width: 10%;">DOC.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cessoes as $cessao)
                <tr>
                    <td><strong>{{ $cessao->codigo_cessao }}</strong></td>
                    <td>{{ $cessao->data_cessao->format('d/m/Y') }}</td>
                    <td>
                        {{ $cessao->usuario->nome }}<br>
                        <small style="color: #666">{{ $cessao->usuario->empresa->razao_social ?? 'S/ Empresa' }}</small>
                    </td>
                    <td>
                        <ul style="margin: 0; padding-left: 15px;">
                            @foreach($cessao->movimentacoes as $mov)
                                <li>
                                    #{{ optional($mov->equipamento)->id }} - 
                                    {{ optional($mov->equipamento)->descricao }} - 
                                    {{ optional($mov->equipamento)->modelo ?? '-' }} 
                                    (S/N: {{ optional($mov->equipamento)->numero_serie ?? '-' }})
                                </li>
                            @endforeach
                        </ul>
                    </td>
                    <td style="text-align: center;">
                        @if($cessao->termo_pdf_path) Sim @else Não @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">Nenhuma cessão encontrada para os filtros aplicados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Portal de Aplicativos - Relatório de Cessões - Página <script type="text/php">if (isset($pdf)) { $pdf->page_script('echo "$PAGE_NUM de $PAGE_COUNT";'); }</script>
    </div>
</body>
</html>
