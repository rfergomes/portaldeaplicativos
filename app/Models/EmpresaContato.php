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
