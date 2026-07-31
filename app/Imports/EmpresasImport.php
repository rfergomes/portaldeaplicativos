<?php

namespace App\Imports;

use App\Models\Empresa;
use App\Models\Regiao;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class EmpresasImport implements ToModel, WithUpserts, WithHeadingRow
{
    /**
     * Cache de IDs de regiões válidas
     *
     * @var array
     */
    private array $validRegioes;

    public function __construct()
    {
        $this->validRegioes = Regiao::pluck('id')->toArray();
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Sanitizar CNPJ
        $rawCnpj = $row['cnpj'] ?? null;
        $cnpj = $this->sanitizeCnpj((string) $rawCnpj);

        if (empty($cnpj)) {
            return null;
        }

        // Razão Social é obrigatória
        $razaoSocial = trim((string) ($row['razao_social'] ?? ''));
        if (empty($razaoSocial)) {
            return null;
        }

        // Validar regiao_id (se <= 0 ou se não existir na tabela regioes, atribui null)
        $regiaoId = (isset($row['regiao_id']) && (int) $row['regiao_id'] > 0) ? (int) $row['regiao_id'] : null;
        if ($regiaoId !== null && !in_array($regiaoId, $this->validRegioes)) {
            $regiaoId = null;
        }

        // Tratar empresa_erp
        $empresaErp = null;
        if (isset($row['empresa_erp']) && $row['empresa_erp'] !== '') {
            $cleaned = preg_replace('/[^\d]/', '', (string) $row['empresa_erp']);
            $empresaErp = !empty($cleaned) ? $cleaned : null;
        }

        // Tratar ativo
        $ativo = true;
        if (array_key_exists('ativo', $row) && $row['ativo'] !== null && $row['ativo'] !== '') {
            $ativo = (bool) ((int) $row['ativo']);
        }

        return new Empresa([
            'regiao_id'          => $regiaoId,
            'razao_social'       => $razaoSocial,
            'nome_fantasia'      => !empty($row['nome_fantasia']) ? trim((string) $row['nome_fantasia']) : null,
            'nome_curto'         => !empty($row['nome_curto']) ? trim((string) $row['nome_curto']) : null,
            'cnpj'               => $cnpj,
            'empresa_erp'        => $empresaErp,
            'inscricao_estadual' => !empty($row['inscricao_estadual']) ? trim((string) $row['inscricao_estadual']) : null,
            'email'              => !empty($row['email']) ? strtolower(trim((string) $row['email'])) : null,
            'telefone'           => !empty($row['telefone']) ? trim((string) $row['telefone']) : null,
            'cidade'             => !empty($row['cidade']) ? mb_strtoupper(trim((string) $row['cidade'])) : null,
            'estado'             => !empty($row['estado']) ? mb_strtoupper(trim((string) $row['estado'])) : null,
            'categoria'          => !empty($row['categoria']) ? mb_strtoupper(trim((string) $row['categoria'])) : null,
            'ativo'              => $ativo,
        ]);
    }

    /**
     * Define a chave única para a operação de Upsert
     *
     * @return string|array
     */
    public function uniqueBy()
    {
        return 'cnpj';
    }

    /**
     * Normaliza e formata o CNPJ para 00.000.000/0000-00
     */
    private function sanitizeCnpj(?string $raw): ?string
    {
        if (empty($raw)) {
            return null;
        }

        // Remove tudo que não for dígito
        $digits = preg_replace('/\D/', '', $raw);

        if (empty($digits)) {
            return null;
        }

        // Preenche com zeros à esquerda até atingir 14 dígitos (caso tenha menos por perda de formatação no excel)
        $digits = str_pad($digits, 14, '0', STR_PAD_LEFT);

        if (strlen($digits) > 14) {
            $digits = substr($digits, -14);
        }

        // Aplica a máscara 00.000.000/0000-00
        return preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $digits);
    }
}
