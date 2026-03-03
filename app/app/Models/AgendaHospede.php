<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaHospede extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'telefone',
        'email',
        'empresa_id',
        'associado',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function reservas()
    {
        return $this->hasMany(AgendaReserva::class);
    }
}
