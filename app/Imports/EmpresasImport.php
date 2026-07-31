<?php

namespace App\Imports;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Regiao;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmpresasImport implements ToCollection, WithHeadingRow
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
     * Processa a coleção de linhas da planilha
     *
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $rawCnpj = $row['cnpj'] ?? null;
            $cnpj = $this->sanitizeCnpj((string) $rawCnpj);
            $cnpjDigits = $this->extractDigits((string) $rawCnpj);

            // Razão Social é obrigatória
            $razaoSocial = trim((string) ($row['razao_social'] ?? ''));
            if (empty($razaoSocial)) {
                continue;
            }

            // ID e código ERP da empresa
            $id = (isset($row['id']) && (int) $row['id'] > 0) ? (int) $row['id'] : null;

            $empresaErp = null;
            if (isset($row['empresa_erp']) && $row['empresa_erp'] !== '') {
                $cleaned = preg_replace('/[^\d]/', '', (string) $row['empresa_erp']);
                $empresaErp = !empty($cleaned) ? $cleaned : null;
            }

            // 1. Localizar Empresa pré-existente (Prioridade 1: ID, Prioridade 2: ERP, Prioridade 3: CNPJ Numérico)
            $empresa = $this->findExistingEmpresa($id, $empresaErp, $cnpj, $cnpjDigits);

            // 2. Se encontrar, consolidar/mesclar eventuais cadastros duplicados existentes no banco
            if ($empresa) {
                $this->mergeDuplicates($empresa, $empresaErp, $cnpj, $cnpjDigits);
            }

            // 3. Tratar FK de Região
            $regiaoId = (isset($row['regiao_id']) && (int) $row['regiao_id'] > 0) ? (int) $row['regiao_id'] : null;
            if ($regiaoId !== null && !in_array($regiaoId, $this->validRegioes)) {
                $regiaoId = null;
            }

            // 4. Tratar status ativo
            $ativo = true;
            if (isset($row['ativo']) && $row['ativo'] !== null && $row['ativo'] !== '') {
                $ativo = (bool) ((int) $row['ativo']);
            }

            // 5. Montar payload de dados
            $data = [
                'regiao_id'          => $regiaoId,
                'razao_social'       => mb_strtoupper($razaoSocial),
                'nome_fantasia'      => !empty($row['nome_fantasia']) ? mb_strtoupper(trim((string) $row['nome_fantasia'])) : null,
                'nome_curto'         => !empty($row['nome_curto']) ? mb_strtoupper(trim((string) $row['nome_curto'])) : null,
                'cnpj'               => $cnpj ?? (string) $rawCnpj,
                'empresa_erp'        => $empresaErp,
                'inscricao_estadual' => !empty($row['inscricao_estadual']) ? trim((string) $row['inscricao_estadual']) : null,
                'email'              => !empty($row['email']) ? strtolower(trim((string) $row['email'])) : null,
                'telefone'           => !empty($row['telefone']) ? trim((string) $row['telefone']) : null,
                'cidade'             => !empty($row['cidade']) ? mb_strtoupper(trim((string) $row['cidade'])) : null,
                'estado'             => !empty($row['estado']) ? mb_strtoupper(trim((string) $row['estado'])) : null,
                'categoria'          => !empty($row['categoria']) ? mb_strtoupper(trim((string) $row['categoria'])) : null,
                'ativo'              => $ativo,
            ];

            // 6. Atualizar ou Criar
            if ($empresa) {
                $empresa->update($data);
            } else {
                if ($id && !Empresa::find($id)) {
                    $data['id'] = $id;
                }
                Empresa::create($data);
            }
        }
    }

    /**
     * Busca uma empresa no banco por ID, Código ERP ou dígitos do CNPJ
     */
    private function findExistingEmpresa(?int $id, ?string $empresaErp, ?string $cnpj, ?string $cnpjDigits): ?Empresa
    {
        // 1. Busca por ID da tabela (se informado)
        if ($id) {
            $empresa = Empresa::find($id);
            if ($empresa) return $empresa;
        }

        // 2. Busca por empresa_erp (código ERP `#54`, `#1023`, `#508`)
        if ($empresaErp) {
            $empresa = Empresa::where('empresa_erp', $empresaErp)->first();
            if ($empresa) return $empresa;
        }

        // 3. Busca por CNPJ exato ou sanitizado apenas com dígitos
        if (!empty($cnpjDigits)) {
            $empresa = Empresa::where('cnpj', $cnpj)
                ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(cnpj, '.', ''), '/', ''), '-', ''), ' ', '') = ?", [$cnpjDigits])
                ->first();
            if ($empresa) return $empresa;
        }

        return null;
    }

    /**
     * Consolida e remove cadastros duplicados existentes vinculando seus contatos ao registro principal
     */
    private function mergeDuplicates(Empresa $mainEmpresa, ?string $empresaErp, ?string $cnpj, ?string $cnpjDigits): void
    {
        if (empty($cnpjDigits) && empty($empresaErp)) {
            return;
        }

        $query = Empresa::where('id', '!=', $mainEmpresa->id);

        $query->where(function ($q) use ($empresaErp, $cnpj, $cnpjDigits) {
            $hasCondition = false;
            if ($empresaErp) {
                $q->where('empresa_erp', $empresaErp);
                $hasCondition = true;
            }
            if (!empty($cnpjDigits)) {
                if ($hasCondition) {
                    $q->orWhere('cnpj', $cnpj)
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(cnpj, '.', ''), '/', ''), '-', ''), ' ', '') = ?", [$cnpjDigits]);
                } else {
                    $q->where('cnpj', $cnpj)
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(REPLACE(cnpj, '.', ''), '/', ''), '-', ''), ' ', '') = ?", [$cnpjDigits]);
                }
            }
        });

        $duplicates = $query->get();

        foreach ($duplicates as $duplicate) {
            // Re-vincular clientes/contatos da empresa duplicada para a empresa principal
            Cliente::where('empresa_id', $duplicate->id)->update(['empresa_id' => $mainEmpresa->id]);

            // Re-vincular sócios folha
            \App\Models\SocioFolha::where('empresa_id', $duplicate->id)->update(['empresa_id' => $mainEmpresa->id]);

            // Re-vincular protocolos e destinatários de protocolos
            \App\Models\Protocolo::where('empresa_id', $duplicate->id)->update(['empresa_id' => $mainEmpresa->id]);
            \App\Models\ProtocoloDestinatario::where('empresa_id', $duplicate->id)->update(['empresa_id' => $mainEmpresa->id]);

            // Re-vincular agenda de hóspedes e ativos de usuários
            \App\Models\AgendaHospede::where('empresa_id', $duplicate->id)->update(['empresa_id' => $mainEmpresa->id]);
            \App\Models\AtivoUsuario::where('empresa_id', $duplicate->id)->update(['empresa_id' => $mainEmpresa->id]);

            // Excluir registro duplicado após re-vincular todas as tabelas filhas
            $duplicate->delete();
        }
    }

    /**
     * Extrai apenas os dígitos numéricos de um CNPJ
     */
    private function extractDigits(?string $raw): ?string
    {
        if (empty($raw)) return null;
        $digits = preg_replace('/\D/', '', $raw);
        if (empty($digits)) return null;
        $digits = str_pad($digits, 14, '0', STR_PAD_LEFT);
        return strlen($digits) > 14 ? substr($digits, -14) : $digits;
    }

    /**
     * Normaliza e formata o CNPJ para 00.000.000/0000-00
     */
    private function sanitizeCnpj(?string $raw): ?string
    {
        $digits = $this->extractDigits($raw);
        if (empty($digits)) return null;
        return preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $digits);
    }
}
