<?php

namespace App\Models;

use App\Domain\Shared\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
    use Auditable;

    protected $fillable = [
        'empresa_id',
        'tipo_cliente_id',
        'nome',
        'documento',
        'email',
        'telefone',
        'cidade',
        'estado',
        'ativo',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function tipo()
    {
        return $this->belongsTo(TipoCliente::class, 'tipo_cliente_id');
    }
}

