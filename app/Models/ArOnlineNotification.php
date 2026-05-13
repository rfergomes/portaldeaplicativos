<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArOnlineNotification extends Model
{
    use HasFactory;

    protected $table = 'aronline_notifications';

    protected $fillable = [
        'notification_id',
        'phone',
        'template',
        'status',
        'payload',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
