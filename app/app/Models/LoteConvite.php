<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoteConvite extends Model
{
    use HasFactory;

    protected $table = 'lotes_convite';

    protected $fillable = [
        'evento_id',
        'nome',
        'quantidade_total',
        'quantidade_disponivel',
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    public function convites()
    {
        return $this->hasMany(Convite::class, 'lote_id');
    }
}

