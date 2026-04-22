<?php

namespace App\Imports;

use App\Models\Empresa;
use App\Models\Regiao;
use App\Models\SocioFolha;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class LegacySocioFolhaImport implements ToModel, WithUpserts
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Pula a linha de cabeçalho (se for o caso)
        if ($row[0] === 'ID' || $row[0] === 'id') {
            return null;
        }

        $lancamentoId = trim((string) ($row[0] ?? ''));

        if (empty($lancamentoId) || !is_numeric($lancamentoId)) {
            return null;
        }

        // Determinar a região
        // Coluna 4: REGIÃO
        $regiaoNome = trim((string) ($row[4] ?? ''));
        $regiaoId = null;
        if (!empty($regiaoNome)) {
            // Se for número, podemos tentar buscar pelo ID ou nome mapeado
            if (is_numeric($regiaoNome)) {
                $regiaoId = (int)$regiaoNome;
            } else {
                $regiao = Regiao::firstOrCreate(
                    ['nome' => $regiaoNome],
                    ['ativo' => true]
                );
                $regiaoId = $regiao->id;
            }
        }

        // Determinar ou criar a empresa
        // Coluna 1: CÓD, Coluna 2: RAZÃO SOCIAL, Coluna 3: CNPJ
        $codErp = trim((string) ($row[1] ?? ''));
        $razaoSocial = trim((string) ($row[2] ?? 'Nova Empresa Importada'));
        $cnpj = trim((string) ($row[3] ?? ''));

        if (empty($codErp) && empty($cnpj)) {
            return null;
        }

        $empresa = Empresa::where('empresa_erp', $codErp)
            ->orWhere('cnpj', $cnpj)
            ->first();

        if (!$empresa) {
            $empresa = Empresa::create([
                'empresa_erp' => $codErp,
                'cnpj' => $cnpj,
                'razao_social' => $razaoSocial,
                'regiao_id' => $regiaoId,
                'ativo' => true,
            ]);
        }

        // Coluna 10: VENCIMENTO
        $dataVencimento = null;
        if (!empty($row[10])) {
            try {
                if (is_numeric($row[10])) {
                    $dataVencimento = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[10])->format('Y-m-d');
                } else {
                    $dataVencimento = Carbon::parse(str_replace('/', '-', $row[10]))->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $dataVencimento = null;
            }
        }

        // Coluna 13: AUTENTICAÇÃO
        $dataAutenticacao = null;
        if (!empty($row[13])) {
            try {
                if (is_numeric($row[13])) {
                    $dataAutenticacao = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[13])->format('Y-m-d');
                } else {
                    $dataAutenticacao = Carbon::parse(str_replace('/', '-', $row[13]))->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $dataAutenticacao = null;
            }
        }

        $situacao = strtoupper(trim((string) ($row[12] ?? 'ABERTO')));

        // Coluna 19: LISTA_DATA
        $dataLista = null;
        if (!empty($row[19])) {
            try {
                if (is_numeric($row[19])) {
                    $dataLista = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[19]);
                } else {
                    $dataLista = Carbon::parse(str_replace('/', '-', $row[19]));
                }
            } catch (\Exception $e) {
                $dataLista = null;
            }
        }

        // Coluna 21: BAIXA_DATA
        $dataBaixa = null;
        if (!empty($row[21])) {
            try {
                if (is_numeric($row[21])) {
                    $dataBaixa = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[21]);
                } else {
                    $dataBaixa = Carbon::parse(str_replace('/', '-', $row[21]));
                }
            } catch (\Exception $e) {
                $dataBaixa = null;
            }
        }

        return new SocioFolha([
            'lancamento_id'     => $lancamentoId,
            'empresa_id'        => $empresa->id,
            'regiao_id'         => $regiaoId,
            'ano'               => (int) ($row[8] ?? 0),
            'mes'               => (int) ($row[9] ?? 0),
            'data_vencimento'   => $dataVencimento,
            'valor_mensalidade' => (float) ($row[11] ?? 0),
            'situacao'          => $situacao,
            'data_autenticacao' => $dataAutenticacao,
            'multa'             => isset($row[14]) ? (float) $row[14] : null,
            'total'             => isset($row[15]) ? (float) $row[15] : null,
            'vl_credit'         => isset($row[16]) ? (float) $row[16] : null,
            'origem'            => $row[17] ?? null,
            'data_lista'        => $dataLista,
            'data_baixa'        => $dataBaixa,
        ]);
    }

    /**
     * @return string|array
     */
    public function uniqueBy()
    {
        return 'lancamento_id';
    }
}
