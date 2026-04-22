<?php

namespace App\Imports;

use App\Models\Empresa;
use App\Models\Regiao;
use App\Models\SocioFolha;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LegacySocioFolhaImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Dados' => new LegacySocioFolhaDataImport(),
        ];
    }
}

class LegacySocioFolhaDataImport implements ToModel, WithUpserts
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Pula a linha de cabeçalho
        if (isset($row[0]) && (strtoupper((string)$row[0]) === 'ID')) {
            return null;
        }

        $lancamentoId = trim((string) ($row[0] ?? ''));

        if (empty($lancamentoId) || !is_numeric($lancamentoId)) {
            return null;
        }

        // Determinar a região (Coluna 4)
        $regiaoVal = trim((string) ($row[4] ?? ''));
        $regiaoId = null;
        if (!empty($regiaoVal)) {
            if (is_numeric($regiaoVal)) {
                $regiaoId = (int)$regiaoVal;
            } else {
                $regiao = Regiao::firstOrCreate(['nome' => $regiaoVal], ['ativo' => true]);
                $regiaoId = $regiao->id;
            }
        }

        // Empresa (Cód: 1, Razão: 2, CNPJ: 3)
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

        // Datas
        $dataVencimento = $this->parseDate($row[10] ?? null);
        $dataAutenticacao = $this->parseDate($row[13] ?? null);
        $dataLista = $this->parseDate($row[19] ?? null);
        $dataBaixa = $this->parseDate($row[21] ?? null);

        return new SocioFolha([
            'lancamento_id'     => $lancamentoId,
            'empresa_id'        => $empresa->id,
            'regiao_id'         => $regiaoId,
            'ano'               => (int) ($row[8] ?? 0),
            'mes'               => (int) ($row[9] ?? 0),
            'data_vencimento'   => $dataVencimento ? $dataVencimento->format('Y-m-d') : null,
            'valor_mensalidade' => (float) ($row[11] ?? 0),
            'situacao'          => strtoupper(trim((string) ($row[12] ?? 'ABERTO'))),
            'data_autenticacao' => $dataAutenticacao ? $dataAutenticacao->format('Y-m-d') : null,
            'multa'             => isset($row[14]) ? (float) $row[14] : null,
            'total'             => isset($row[15]) ? (float) $row[15] : null,
            'vl_credit'         => isset($row[16]) ? (float) $row[16] : null,
            'origem'            => $row[17] ?? null,
            'data_lista'        => $dataLista,
            'data_baixa'        => $dataBaixa,
        ]);
    }

    private function parseDate($value)
    {
        if (empty($value)) return null;
        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
            }
            return Carbon::parse(str_replace('/', '-', $value));
        } catch (\Exception $e) {
            return null;
        }
    }

    public function uniqueBy()
    {
        return 'lancamento_id';
    }
}
