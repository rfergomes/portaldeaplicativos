<?php

namespace App\Imports;

use App\Models\SocioCaixa;
use App\Models\SocioCaixaOcorrencia;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
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

        $ano = (int) ($row['ano'] ?? 0);
        $mes = (int) ($row['mes'] ?? 0);

        // Extrai e faz o parse das datas de vencimento e autenticação
        $rawVencimento = $row['vencimento'] ?? $row['vencto'] ?? $row['dt_vencimento'] ?? $row['venc'] ?? $row['data_vencimento'] ?? null;
        $dataVencimento = $this->parseDate($rawVencimento);

        $rawAutenticacao = $row['autenticacao'] ?? $row['autentica'] ?? $row['dt_autenticacao'] ?? $row['autenticacao_pagamento'] ?? $row['dt_pagamento'] ?? $row['data_pagamento'] ?? $row['pagamento'] ?? null;
        $dataAutenticacao = $this->parseDate($rawAutenticacao);

        // Determinar se está pago baseado na coluna STATUS ("Em Dia") ou na presença de autenticação
        $status = strtoupper(trim((string)($row['status'] ?? '')));
        $pago = ($status === 'EM DIA') || !empty($dataAutenticacao);

        // Se estiver pago, data_pagamento será a data de autenticação (ou now() se ausente)
        $dataPagamento = null;
        if ($pago) {
            $dataPagamento = $dataAutenticacao ?: now();
        }

        // Preservar data de vencimento preexistente se o arquivo atual (adimplentes) não contiver a coluna
        if (empty($dataVencimento)) {
            $existing = SocioCaixa::where('matricula', $matricula)
                ->where('ano', $ano)
                ->where('mes', $mes)
                ->first();
            if ($existing && $existing->data_vencimento) {
                $dataVencimento = $existing->data_vencimento;
            }
        }

        return new SocioCaixa([
            'ano'                => $ano,
            'mes'                => $mes,
            'matricula'          => $matricula,
            'nome'               => $row['nome'] ?? 'N/A',
            'tipo_socio'         => $row['tp_socio'] ?? null,
            'cod_emp'            => $row['cod_emp'] ?? null,
            'valor'              => (float) ($row['valor'] ?? 0),
            'data_vencimento'    => $dataVencimento,
            'pago'               => $pago,
            'data_pagamento'     => $dataPagamento,
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

    /**
     * Parse robusto de data nos formatos do Excel e texto PT-BR
     */
    protected function parseDate($value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        // Número serial do Excel (ex: 46251)
        if (is_numeric($value) && $value > 1000) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->startOfDay();
            } catch (\Throwable $e) {
                // Continua para outros formatos
            }
        }

        $str = trim((string) $value);
        if ($str === '' || $str === '-' || strtolower($str) === 'null') {
            return null;
        }

        // Formatos comuns brasileiros e ISO
        foreach (['d/m/Y', 'd/m/y', 'Y-m-d', 'd-m-Y', 'Y/m/d'] as $format) {
            try {
                $dt = Carbon::createFromFormat($format, $str);
                if ($dt !== false) {
                    return $dt->startOfDay();
                }
            } catch (\Throwable $e) {
                // Continua
            }
        }

        try {
            return Carbon::parse($str)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
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
