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
        'regiao_id',
        'razao_social',
        'nome_fantasia',
        'nome_curto',
        'cnpj',
        'empresa_erp',
        'inscricao_estadual',
        'email',
        'telefone',
        'cidade',
        'estado',
        'categoria',
        'ativo',
    ];

    public function regiao()
    {
        return $this->belongsTo(Regiao::class);
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }
}

