<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtivoCessao extends Model
{
    use HasFactory;

    protected $table = 'ativo_cessoes';

    protected $fillable = [
        'usuario_id',
        'data_cessao',
        'codigo_cessao',
        'termo_pdf_path',
        'observacoes',
    ];

    protected $casts = [
        'data_cessao' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(AtivoUsuario::class, 'usuario_id');
    }

    public function movimentacoes()
    {
        return $this->hasMany(AtivoMovimentacao::class, 'cessao_id');
    }

    public function anexos()
    {
        return $this->hasMany(AtivoAnexo::class, 'cessao_id');
    }
}
