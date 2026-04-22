<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocioFolhaHistorico extends Model
{
    protected $table = 'socio_folha_historicos';

    protected $fillable = [
        'socio_folha_id',
        'user_id',
        'acao',
        'detalhes'
    ];

    public function socioFolha()
    {
        return $this->belongsTo(SocioFolha::class, 'socio_folha_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
