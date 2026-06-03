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

        if (empty($lancamentoId) || !is_numeric($lancamentoId)) {
            return null;
        }

        // Determinar a região
        $regiaoNome = trim((string) ($row['regiao'] ?? ''));
        $regiaoId = null;
        if (!empty($regiaoNome)) {
            if (is_numeric($regiaoNome)) {
                // Tenta buscar pela area_adm (mapeamento oficial do seeder)
                $regiao = Regiao::where('area_adm', $regiaoNome)->first();
                if ($regiao) {
                    $regiaoId = $regiao->id;
                }
            } else {
                $regiao = Regiao::firstOrCreate(
                    ['nome' => $regiaoNome],
                    ['ativo' => true]
                );
                $regiaoId = $regiao->id;
            }
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
        $vencimentoRaw = $row['dt_vencto'] ?? $row['vencimento'] ?? $row['dtvencto'] ?? null;
        if (!empty($vencimentoRaw)) {
            try {
                if (is_numeric($vencimentoRaw)) {
                    $dataVencimento = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($vencimentoRaw)->format('Y-m-d');
                } else {
                    try {
                        $dataVencimento = Carbon::createFromFormat('d/m/Y', $vencimentoRaw)->format('Y-m-d');
                    } catch (\Exception $ex) {
                        $dataVencimento = Carbon::parse(str_replace('/', '-', $vencimentoRaw))->format('Y-m-d');
                    }
                }
            } catch (\Exception $e) {
                $dataVencimento = null;
            }
        }

        $dataAutenticacao = null;
        $autenticacaoRaw = $row['dt_autent'] ?? $row['autenticacao'] ?? $row['dtautent'] ?? null;
        if (!empty($autenticacaoRaw)) {
            try {
                if (is_numeric($autenticacaoRaw)) {
                    $dataAutenticacao = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($autenticacaoRaw)->format('Y-m-d');
                } else {
                    try {
                        $dataAutenticacao = Carbon::createFromFormat('d/m/Y', $autenticacaoRaw)->format('Y-m-d');
                    } catch (\Exception $ex) {
                        $dataAutenticacao = Carbon::parse(str_replace('/', '-', $autenticacaoRaw))->format('Y-m-d');
                    }
                }
            } catch (\Exception $e) {
                $dataAutenticacao = null;
            }
        }

        $situacao = strtoupper(trim((string) ($row['situacao'] ?? 'ABERTO')));
        $valorMensalidade = $row['vl_mens'] ?? $row['valor'] ?? $row['vlmens'] ?? 0;
        $vlCredit = $row['vl_credit'] ?? $row['creditado'] ?? $row['vlcredit'] ?? null;

        return new SocioFolha([
            'lancamento_id'     => $lancamentoId,
            'empresa_id'        => $empresa->id,
            'regiao_id'         => $regiaoId,
            'ano'               => is_numeric($row['ano'] ?? null) ? (int) $row['ano'] : 0,
            'mes'               => is_numeric($row['mes'] ?? null) ? (int) $row['mes'] : 0,
            'data_vencimento'   => $dataVencimento,
            'valor_mensalidade' => (float) $valorMensalidade,
            'situacao'          => $situacao,
            'data_autenticacao' => $dataAutenticacao,
            'multa'             => isset($row['multa']) ? (float) $row['multa'] : null,
            'total'             => isset($row['total']) ? (float) $row['total'] : null,
            'vl_credit'         => $vlCredit !== null ? (float) $vlCredit : null,
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
