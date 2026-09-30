<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetRequest extends BaseModel
{
    protected $table = 'password_reset_requests';

    protected $fillable = ['userId', 'role', 'status', 'createdAt'];

    protected $casts = [
        'createdAt' => 'datetime',
    ];
}
