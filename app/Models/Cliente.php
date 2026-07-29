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
        'email_valido',
        'email_bounce_code',
        'email_bounce_description',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'email_valido' => 'boolean',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function tipo()
    {
        return $this->belongsTo(TipoCliente::class, 'tipo_cliente_id');
    }

    public function temBounce(): bool
    {
        return $this->email_valido === false || !empty($this->email_bounce_code);
    }
}

