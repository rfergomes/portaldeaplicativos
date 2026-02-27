<?php

namespace App\Models;

use App\Domain\Shared\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;
    use Auditable;

    protected $fillable = [
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'inscricao_estadual',
        'email',
        'telefone',
        'cidade',
        'estado',
        'categoria',
        'ativo',
    ];

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }
}

