<?php

namespace App\Imports;

use App\Models\SocioCaixa;
use App\Models\SocioCaixaOcorrencia;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;

class SocioCaixaImport implements ToModel, WithUpserts, WithHeadingRow, WithEvents
{
    protected array $matriculas = [];

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

        $this->matriculas[$matricula] = true;

        // Determinar se está pago baseado na coluna STATUS ou na natureza da planilha
        // Nas planilhas modelos: STATUS "Atrasado" para débitos e "Em Dia" para pagos
        $status = strtoupper(trim((string)($row['status'] ?? '')));
        $pago = ($status === 'EM DIA');

        return new SocioCaixa([
            'ano'                => (int) ($row['ano'] ?? 0),
            'mes'                => (int) ($row['mes'] ?? 0),
            'matricula'          => $matricula,
            'nome'               => $row['nome'] ?? 'N/A',
            'tipo_socio'         => $row['tp_socio'] ?? null,
            'cod_emp'            => $row['cod_emp'] ?? null,
            'valor'              => (float) ($row['valor'] ?? 0),
            'pago'               => $pago,
            'data_pagamento'     => $pago ? now() : null,
            'inativado_abaco'    => false,
            'inativado_abaco_em' => null,
        ]);
    }

    /**
     * @return string|array
     */
    public function uniqueBy()
    {
        return ['matricula', 'ano', 'mes'];
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function(AfterImport $event) {
                if (!empty($this->matriculas)) {
                    $matriculasList = array_keys($this->matriculas);

                    // Localizar todas as matrículas importadas que estavam com inativado_abaco = true
                    $inativados = SocioCaixa::whereIn('matricula', $matriculasList)
                        ->where('inativado_abaco', true)
                        ->distinct()
                        ->pluck('matricula');

                    if ($inativados->isNotEmpty()) {
                        // Reativar todos os lançamentos dessas matrículas
                        SocioCaixa::whereIn('matricula', $inativados)->update([
                            'inativado_abaco' => false,
                            'inativado_abaco_em' => null,
                        ]);

                        // Gravar ocorrência de reativação automática para cada matrícula
                        $userId = auth()->id();
                        foreach ($inativados as $mat) {
                            SocioCaixaOcorrencia::create([
                                'matricula' => $mat,
                                'user_id' => $userId,
                                'mensagem' => '[REATIVAÇÃO AUTOMÁTICA] Associado reativado automaticamente via importação de planilha do ERP Ábaco.',
                            ]);
                        }
                    }
                }
            },
        ];
    }
}
