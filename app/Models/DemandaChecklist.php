<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandaChecklist extends Model
{
    use HasFactory;

    protected $table = 'demanda_checklists';

    protected $fillable = [
        'demanda_id',
        'item',
        'concluido',
    ];

    protected $casts = [
        'concluido' => 'boolean',
    ];

    public function demanda()
    {
        return $this->belongsTo(Demanda::class, 'demanda_id');
    }
}
