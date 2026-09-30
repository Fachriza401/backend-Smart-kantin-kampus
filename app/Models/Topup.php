<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topup extends BaseModel
{
    protected $table = 'topups';

    protected $fillable = ['userId', 'amount', 'status', 'createdAt'];

    protected $casts = [
        'amount' => 'float',
        'createdAt' => 'datetime',
    ];
}
