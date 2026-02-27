<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocoloComprovante extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocolo_id',
        'pdf_base64',
        'hash_documento',
    ];

    public function protocolo()
    {
        return $this->belongsTo(Protocolo::class);
    }
}

