<?php

namespace App\Imports;

use App\Models\Empresa;
use App\Models\Regiao;
use App\Models\SocioFolha;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class LegacySocioFolhaImport implements ToModel, WithUpserts, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $lancamentoId = trim((string) ($row['id'] ?? ''));

        if (empty($lancamentoId)) {
            return null;
        }

        // Determinar a região
        $regiaoNome = trim((string) ($row['regiao'] ?? ''));
        $regiaoId = null;
        if (!empty($regiaoNome)) {
            $regiao = Regiao::firstOrCreate(
                ['nome' => $regiaoNome],
                ['ativo' => true]
            );
            $regiaoId = $regiao->id;
        }

        // Determinar ou criar a empresa baseada no Código ERP (Cód) e CNPJ
        $codErp = trim((string) ($row['cod'] ?? ''));
        $cnpj = trim((string) ($row['cnpj'] ?? ''));
        $razaoSocial = trim((string) ($row['razao_social'] ?? 'Nova Empresa Importada'));

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

        $dataVencimento = null;
        if (!empty($row['vencimento'])) {
            try {
                if (is_numeric($row['vencimento'])) {
                    $dataVencimento = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['vencimento'])->format('Y-m-d');
                } else {
                    $dataVencimento = Carbon::parse(str_replace('/', '-', $row['vencimento']))->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $dataVencimento = null;
            }
        }

        $dataAutenticacao = null;
        if (!empty($row['autenticacao'])) {
            try {
                if (is_numeric($row['autenticacao'])) {
                    $dataAutenticacao = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['autenticacao'])->format('Y-m-d');
                } else {
                    $dataAutenticacao = Carbon::parse(str_replace('/', '-', $row['autenticacao']))->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $dataAutenticacao = null;
            }
        }

        $situacao = strtoupper(trim((string) ($row['situacao'] ?? 'ABERTO')));

        $dataLista = null;
        if (!empty($row['lista_data'])) {
            try {
                if (is_numeric($row['lista_data'])) {
                    $dataLista = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['lista_data']);
                } else {
                    $dataLista = Carbon::parse(str_replace('/', '-', $row['lista_data']));
                }
            } catch (\Exception $e) {
                $dataLista = null;
            }
        }

        $dataBaixa = null;
        if (!empty($row['baixa_data'])) {
            try {
                if (is_numeric($row['baixa_data'])) {
                    $dataBaixa = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['baixa_data']);
                } else {
                    $dataBaixa = Carbon::parse(str_replace('/', '-', $row['baixa_data']));
                }
            } catch (\Exception $e) {
                $dataBaixa = null;
            }
        }

        return new SocioFolha([
            'lancamento_id'     => $lancamentoId,
            'empresa_id'        => $empresa->id,
            'regiao_id'         => $regiaoId,
            'ano'               => (int) ($row['ano'] ?? 0),
            'mes'               => (int) ($row['mes'] ?? 0),
            'data_vencimento'   => $dataVencimento,
            'valor_mensalidade' => (float) ($row['valor'] ?? 0),
            'situacao'          => $situacao,
            'data_autenticacao' => $dataAutenticacao,
            'multa'             => isset($row['multa']) ? (float) $row['multa'] : null,
            'total'             => isset($row['total']) ? (float) $row['total'] : null,
            'vl_credit'         => isset($row['creditado']) ? (float) $row['creditado'] : null,
            'origem'            => $row['origem'] ?? null,
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
