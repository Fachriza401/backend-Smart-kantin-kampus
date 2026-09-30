<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends BaseModel
{
    protected $table = 'notifications';

    protected $fillable = [
        'userId', 'title', 'message', 'type', 'isRead', 'createdAt',
        'targetType', 'targetId',
    ];

    protected $casts = [
        // WAJIB 'integer', JANGAN 'boolean' — notification_screen membacanya
        // langsung dari Map lewat `notification['isRead']`.
        'isRead' => 'integer',
        'createdAt' => 'datetime',
        'userId' => 'integer',
        'targetId' => 'integer',
    ];
}
