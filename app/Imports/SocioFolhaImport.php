<?php

namespace App\Imports;

use App\Models\Empresa;
use App\Models\Regiao;
use App\Models\SocioFolha;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class SocioFolhaImport implements ToModel, WithUpserts, WithHeadingRow
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
            return null; // Sem identificação de empresa, pula a linha
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

        // Processar datas usando PhpSpreadsheet shared date helpers
        $dataVencimento = null;
        if (!empty($row['dt_vencto'])) {
            try {
                if (is_numeric($row['dt_vencto'])) {
                    $dataVencimento = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['dt_vencto'])->format('Y-m-d');
                } else {
                    $dataVencimento = Carbon::createFromFormat('d/m/Y', $row['dt_vencto'])->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $dataVencimento = null;
            }
        }

        $dataAutenticacao = null;
        if (!empty($row['dt_autent'])) {
            try {
                if (is_numeric($row['dt_autent'])) {
                    $dataAutenticacao = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['dt_autent'])->format('Y-m-d');
                } else {
                    $dataAutenticacao = Carbon::createFromFormat('d/m/Y', $row['dt_autent'])->format('Y-m-d');
                }
            } catch (\Exception $e) {
                $dataAutenticacao = null;
            }
        }

        $situacao = strtoupper(trim((string) ($row['situacao'] ?? 'ABERTO')));

        return new SocioFolha([
            'lancamento_id'     => $lancamentoId,
            'empresa_id'        => $empresa->id,
            'regiao_id'         => $regiaoId,
            'ano'               => (int) ($row['ano'] ?? 0),
            'mes'               => (int) ($row['mes'] ?? 0),
            'data_vencimento'   => $dataVencimento,
            'valor_mensalidade' => (float) ($row['vl_mens'] ?? 0),
            'situacao'          => $situacao,
            'data_autenticacao' => $dataAutenticacao,
            'multa'             => isset($row['multa']) ? (float) $row['multa'] : null,
            'total'             => isset($row['total']) ? (float) $row['total'] : null,
            'vl_credit'         => isset($row['vl_credit']) ? (float) $row['vl_credit'] : null,
            'origem'            => $row['origem'] ?? null,
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
