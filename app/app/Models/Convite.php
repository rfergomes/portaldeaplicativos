<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Convite extends Model
{
    use HasFactory;

    protected $fillable = [
        'evento_id',
        'lote_id',
        'tipo',
        'valor',
        'codigo',
        'utilizado',
    ];

    protected $casts = [
        'utilizado' => 'boolean',
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }

    public function lote()
    {
        return $this->belongsTo(LoteConvite::class, 'lote_id');
    }

    public function convidados()
    {
        return $this->hasMany(Convidado::class);
    }

    public function venda()
    {
        return $this->hasOne(VendaConvite::class);
    }
}

