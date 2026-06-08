<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandaHistorico extends Model
{
    use HasFactory;

    protected $table = 'demanda_historicos';

    protected $fillable = [
        'demanda_id',
        'user_id',
        'acao',
        'descricao',
    ];

    public function demanda()
    {
        return $this->belongsTo(Demanda::class, 'demanda_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
