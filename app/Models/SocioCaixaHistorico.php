<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocioCaixaHistorico extends Model
{
    use HasFactory;

    protected $fillable = [
        'socio_caixa_id',
        'user_id',
        'acao',
        'observacao',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function socioCaixa()
    {
        return $this->belongsTo(SocioCaixa::class);
    }

    public function socio()
    {
        return $this->belongsTo(SocioCaixa::class, 'socio_caixa_id');
    }
}
