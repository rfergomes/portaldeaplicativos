<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaContato extends Model
{
    use HasFactory;

    protected $table = 'empresa_contatos';

    protected $fillable = [
        'cnpj',
        'razao_social',
        'nome_fantasia',
        'email_rh',
        'telefone_rh',
        'responsavel_rh',
    ];

    /**
     * Localiza contato de empresa pelo CNPJ ou pela razão social.
     */
    public static function encontrarPorCnpjOuNome(?string $cnpj, ?string $nomeEmpresa): ?self
    {
        if ($cnpj) {
            $cnpjLimpo = preg_replace('/\D/', '', $cnpj);
            $encontrado = static::whereRaw("REPLACE(REPLACE(REPLACE(cnpj, '.', ''), '-', ''), '/', '') = ?", [$cnpjLimpo])->first();
            if ($encontrado) {
                return $encontrado;
            }
        }

        if ($nomeEmpresa) {
            return static::where('razao_social', 'like', '%' . trim($nomeEmpresa) . '%')
                ->orWhere('nome_fantasia', 'like', '%' . trim($nomeEmpresa) . '%')
                ->first();
        }

        return null;
    }

    /**
     * Busca contato em empresa_contatos ou pesquisa na tabela empresas e seus clientes vinculados,
     * salvando automaticamente em empresa_contatos quando não existir.
     */
    public static function obterOuSincronizarContatoEmpresa(?string $nomeEmpresa, ?string $cnpj = null): ?self
    {
        try {
            // 0. Se não tiver CNPJ explícito, tenta extrair CNPJ formatado ou numérico da string do nome
            if (empty($cnpj) && !empty($nomeEmpresa) && preg_match('/\d{2}\.?\d{3}\.?\d{3}\/?\d{4}-?\d{2}/', $nomeEmpresa, $matches)) {
                $cnpj = $matches[0];
            }

            // 1. Tenta encontrar registro já existente na tabela empresa_contatos
            $contatoExistente = static::encontrarPorCnpjOuNome($cnpj, $nomeEmpresa);
            if ($contatoExistente && !empty($contatoExistente->email_rh)) {
                return $contatoExistente;
            }

            // 2. Se não existir ou o e-mail estiver em branco, busca na tabela oficial empresas
            $query = Empresa::query();

            if ($cnpj) {
                $cnpjLimpo = preg_replace('/\D/', '', $cnpj);
                $query->whereRaw("REPLACE(REPLACE(REPLACE(cnpj, '.', ''), '-', ''), '/', '') = ?", [$cnpjLimpo]);
            } elseif ($nomeEmpresa) {
                $nomeLimpo = trim($nomeEmpresa);
                $query->where(function ($q) use ($nomeLimpo) {
                    $q->where('razao_social', 'like', "%{$nomeLimpo}%")
                      ->orWhere('nome_fantasia', 'like', "%{$nomeLimpo}%")
                      ->orWhere('nome_curto', 'like', "%{$nomeLimpo}%");
                });
            } else {
                return $contatoExistente;
            }

            $empresa = $query->with(['clientes' => function ($q) {
                $q->where('ativo', true);
            }])->first();

            // 2.1 Fallback: busca por partes do nome se houver separadores (ex: "EMPRESA X - CNPJ...")
            if (!$empresa && $nomeEmpresa) {
                $partes = preg_split('/[\-\(]/', $nomeEmpresa);
                $primeiraParte = trim($partes[0] ?? '');
                if (mb_strlen($primeiraParte) >= 3) {
                    $empresa = Empresa::where(function ($q) use ($primeiraParte) {
                        $q->where('razao_social', 'like', "%{$primeiraParte}%")
                          ->orWhere('nome_fantasia', 'like', "%{$primeiraParte}%")
                          ->orWhere('nome_curto', 'like', "%{$primeiraParte}%");
                    })->with(['clientes' => function ($q) {
                        $q->where('ativo', true);
                    }])->first();
                }
            }

            if (!$empresa) {
                return $contatoExistente;
            }

            // 3. Determina o melhor e-mail e telefone a partir da empresa e clientes vinculados
            $emailFinal = null;
            $telefoneFinal = $empresa->telefone;
            $responsavelFinal = 'Departamento Pessoal / RH';

            if ($empresa->clientes->isNotEmpty()) {
                $clientesValidos = $empresa->clientes->filter(function ($c) {
                    return !empty($c->email) && ($c->ativo ?? true) && ($c->email_valido !== false);
                });

                $clienteComEmail = $clientesValidos->first(function ($c) {
                    $cargo = mb_strtoupper($c->tipo->nome ?? '');
                    return str_contains($cargo, 'RH') || 
                           str_contains($cargo, 'RECURSO') || 
                           str_contains($cargo, 'PESSOAL') || 
                           str_contains($cargo, 'DP');
                }) ?? $clientesValidos->first();

                if ($clienteComEmail) {
                    $emailFinal = $clienteComEmail->email;
                    if (!empty($clienteComEmail->telefone)) {
                        $telefoneFinal = $clienteComEmail->telefone;
                    }
                    if (!empty($clienteComEmail->nome)) {
                        $responsavelFinal = $clienteComEmail->nome;
                    }
                }
            }

            // Se os clientes não tinham e-mail, utiliza o e-mail geral cadastrado na tabela empresas (ex: SIS Ábaco)
            if (empty($emailFinal) && !empty($empresa->email)) {
                $emailFinal = $empresa->email;
            }

            // 4. Salva ou sincroniza na tabela empresa_contatos
            $dados = [
                'cnpj' => $empresa->cnpj ?? $cnpj,
                'razao_social' => $empresa->razao_social ?? $nomeEmpresa,
                'nome_fantasia' => $empresa->nome_fantasia ?? $empresa->razao_social ?? $nomeEmpresa,
                'email_rh' => $emailFinal ?? $contatoExistente?->email_rh,
                'telefone_rh' => $telefoneFinal ?? $contatoExistente?->telefone_rh,
                'responsavel_rh' => $responsavelFinal ?? $contatoExistente?->responsavel_rh ?? 'Departamento Pessoal / RH',
            ];

            if ($contatoExistente) {
                $contatoExistente->update(array_filter($dados, fn ($val) => !is_null($val)));
                return $contatoExistente;
            }

            return static::create($dados);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Erro ao sincronizar contato de empresa: ' . $e->getMessage());
            return $contatoExistente ?? null;
        }
    }

    /**
     * Scope para busca simples.
     */
    public function scopeBuscar(Builder $query, string $termo): Builder
    {
        $termo = trim($termo);
        return $query->where('razao_social', 'like', "%{$termo}%")
            ->orWhere('nome_fantasia', 'like', "%{$termo}%")
            ->orWhere('cnpj', 'like', "%{$termo}%")
            ->orWhere('email_rh', 'like', "%{$termo}%");
    }
}
