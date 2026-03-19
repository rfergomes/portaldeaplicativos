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
        'aquisicao_id',
        'movimentacao_id',
        'cessao_id',
        'caminho',
        'nome_original',
        'mime_type',
        'tamanho',
    ];

    public function equipamento()
    {
        return $this->belongsTo(AtivoEquipamento::class, 'equipamento_id');
    }

    public function aquisicao()
    {
        return $this->belongsTo(AtivoAquisicao::class, 'aquisicao_id');
    }

    public function movimentacao()
    {
        return $this->belongsTo(AtivoMovimentacao::class, 'movimentacao_id');
    }

    public function cessao()
    {
        return $this->belongsTo(AtivoCessao::class, 'cessao_id');
    }
}
