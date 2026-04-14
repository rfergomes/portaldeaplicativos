<?php

namespace App\Imports;

use App\Models\SocioCaixa;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class SocioCaixaImport implements ToModel, WithUpserts
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Pular linhas totalmente vazias ou com matrícula ausente
        if (empty($row[2]) || $row[0] === 'ANO' || $row[2] === 'MATRICULA') {
            return null;
        }

        // Tentar identificar se é um lançamento do tipo CAIXA em diferentes colunas possíveis
        $isCaixa = false;
        $valoresEncontrados = [];
        foreach ([$row[4] ?? '', $row[5] ?? '', $row[6] ?? '', $row[7] ?? ''] as $col) {
            $val = strtoupper(trim((string)$col));
            $valoresEncontrados[] = $val;
            if ($val === 'CAIXA') {
                $isCaixa = true;
                break;
            }
        }

        if (!$isCaixa) {
            \Illuminate\Support\Facades\Log::info("Linha pulada (não é CAIXA). Matrícula: {$row[2]}. Colunas testadas: " . implode(', ', $valoresEncontrados));
            return null;
        }

        \Illuminate\Support\Facades\Log::info("Importando linha: Matrícula {$row[2]}, Ano {$row[0]}, Mês {$row[1]}");

        $matricula = trim((string) $row[2]);

        return new SocioCaixa([
            'ano'            => (int) ($row[0] ?? 0),
            'mes'            => (int) ($row[1] ?? 0),
            'matricula'      => $matricula,
            'nome'           => $row[3] ?? 'N/A',
            'tipo_socio'     => $row[5] ?? ($row[4] ?? null), 
            'valor'          => 0, 
            'pago'           => false,
            'data_pagamento' => null,
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
