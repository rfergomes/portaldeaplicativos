<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DemandaAnexo extends Model
{
    use HasFactory;

    protected $table = 'demanda_anexos';

    protected $fillable = [
        'demanda_id',
        'caminho',
        'nome_original',
        'tipo_origem',
    ];

    public function demanda()
    {
        return $this->belongsTo(Demanda::class, 'demanda_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->caminho);
    }
}
