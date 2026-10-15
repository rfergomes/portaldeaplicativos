<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cliente;
use App\Models\Empresa;
use Illuminate\Support\Collection;

class EmpresaContatoResolver
{
    /**
     * Localiza a empresa no cadastro corporativo por CNPJ ou Razão Social / Fantasia.
     */
    public static function encontrarEmpresa(?string $cnpj = null, ?string $nome = null): ?Empresa
    {
        if (!empty($cnpj)) {
            $cnpjLimpo = preg_replace('/\D/', '', $cnpj);
            $empresa = Empresa::whereRaw("REPLACE(REPLACE(REPLACE(cnpj, '.', ''), '/', ''), '-', '') = ?", [$cnpjLimpo])
                ->first();

            if ($empresa) {
                return $empresa;
            }
        }

        if (!empty($nome)) {
            $nomeLimpo = trim($nome);

            // Busca exata
            $empresa = Empresa::where('razao_social', $nomeLimpo)
                ->orWhere('nome_fantasia', $nomeLimpo)
                ->orWhere('nome_curto', $nomeLimpo)
                ->first();

            if ($empresa) {
                return $empresa;
            }

            // Busca aproximada caso não encontre exata
            return Empresa::where('razao_social', 'LIKE', '%' . $nomeLimpo . '%')
                ->orWhere('nome_fantasia', 'LIKE', '%' . $nomeLimpo . '%')
                ->first();
        }

        return null;
    }

    /**
     * Obtém os contatos ativos e com e-mails válidos vinculados à empresa na tabela clientes.
     *
     * @return array{empresa: ?Empresa, contatos: Collection<int, Cliente>, emails: array<string>}
     */
    public static function obterContatosValidosParaNotificacao(?string $cnpj = null, ?string $nome = null): array
    {
        $empresa = self::encontrarEmpresa($cnpj, $nome);

        if (!$empresa) {
            return [
                'empresa' => null,
                'contatos' => collect(),
                'emails' => [],
            ];
        }

        /** @var Collection<int, Cliente> $contatos */
        $contatos = $empresa->clientes()
            ->validosParaEnvio()
            ->get();

        $emails = $contatos->pluck('email')
            ->filter()
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->unique()
            ->values()
            ->all();

        return [
            'empresa' => $empresa,
            'contatos' => $contatos,
            'emails' => $emails,
        ];
    }

    /**
     * Registra ou atualiza um contato de RH na tabela clientes para uma empresa.
     */
    public static function registrarContatoRh(
        Empresa $empresa,
        string $email,
        ?string $nome = null,
        ?string $telefone = null
    ): Cliente {
        $emailNormalizado = strtolower(trim($email));

        $cliente = Cliente::where('empresa_id', $empresa->id)
            ->where('email', $emailNormalizado)
            ->first();

        if ($cliente) {
            $cliente->update([
                'ativo' => true,
                'email_valido' => true,
                'email_bounce_code' => null,
                'email_bounce_description' => null,
                'nome' => $nome ? trim($nome) : ($cliente->nome ?: 'RH / Departamento Pessoal'),
                'telefone' => $telefone ? trim($telefone) : $cliente->telefone,
            ]);

            return $cliente;
        }

        return Cliente::create([
            'empresa_id' => $empresa->id,
            'nome' => $nome ? trim($nome) : 'RH / Departamento Pessoal',
            'email' => $emailNormalizado,
            'telefone' => $telefone ? trim($telefone) : null,
            'ativo' => true,
            'email_valido' => true,
        ]);
    }
}
