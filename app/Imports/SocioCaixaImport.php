<?php

namespace App\Imports;

use App\Models\SocioCaixa;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class SocioCaixaImport implements ToModel, WithUpserts, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // O Maatwebsite Excel converte "MAT." para "mat" por padrão
        $matricula = trim((string) ($row['mat'] ?? ''));

        if (empty($matricula)) {
            return null;
        }

        // Filtro para garantir que estamos importando apenas registros do tipo CAIXA
        $tipoDesconto = strtoupper(trim((string)($row['tp_desconto'] ?? '')));
        if ($tipoDesconto !== 'CAIXA') {
            return null;
        }

        // Determinar se está pago baseado na coluna STATUS ou na natureza da planilha
        // Nas planilhas modelos: STATUS "Atrasado" para débitos e "Em Dia" para pagos
        $status = strtoupper(trim((string)($row['status'] ?? '')));
        $pago = ($status === 'EM DIA');

        return new SocioCaixa([
            'ano'            => (int) ($row['ano'] ?? 0),
            'mes'            => (int) ($row['mes'] ?? 0),
            'matricula'      => $matricula,
            'nome'           => $row['nome'] ?? 'N/A',
            'tipo_socio'     => $row['tp_socio'] ?? null,
            'cod_emp'        => $row['cod_emp'] ?? null,
            'valor'          => (float) ($row['valor'] ?? 0),
            'pago'           => $pago,
            'data_pagamento' => $pago ? now() : null,
        ]);
    }

    /**
     * @return string|array
     */
    public function uniqueBy()
    {
        return ['matricula', 'ano', 'mes'];
    }
}
