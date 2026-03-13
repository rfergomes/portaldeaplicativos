<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtivoAnexo extends Model
{
    use HasFactory;

    protected $table = 'ativo_anexos';

    protected $fillable = [
        'equipamento_id',
        'movimentacao_id',
        'caminho',
        'nome_original',
        'mime_type',
        'tamanho',
    ];

    public function equipamento()
    {
        return $this->belongsTo(AtivoEquipamento::class, 'equipamento_id');
    }

    public function movimentacao()
    {
        return $this->belongsTo(AtivoMovimentacao::class, 'movimentacao_id');
    }
}
