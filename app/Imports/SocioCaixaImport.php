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
        // Pular cabeçalho ou linhas totalmente vazias
        if ($row[0] === 'ANO' || empty($row[0]) || !isset($row[2]) || $row[0] === 'ANO') {
            return null;
        }

        // Importar apenas lançamentos do tipo CAIXA (Agora no índice 6 devido à nova coluna 'Tipo' no índice 5)
        $tipoPlanilha = strtoupper(trim((string) ($row[6] ?? '')));
        if ($tipoPlanilha !== 'CAIXA') {
            return null;
        }

        // Garantir que a matrícula existe e remover vírgulas/pontos se necessário
        $matricula = trim((string) $row[2]);
        if (empty($matricula)) {
            return null;
        }

        return new SocioCaixa([
            'ano'            => (int) $row[0],
            'mes'            => (int) $row[1],
            'matricula'      => $matricula,
            'nome'           => $row[3] ?? 'N/A',
            'tipo_socio'     => $row[5] ?? null, // Coluna 'Tipo' (Ex: SOCIO FABRICA-NORMAL)
            'valor'          => 0, // Valor padrão pois a planilha não possui coluna de valor numérico
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
