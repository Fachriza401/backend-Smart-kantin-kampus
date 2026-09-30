<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends BaseModel
{
    protected $table = 'ratings';

    protected $fillable = ['userId', 'menuId', 'orderId', 'rating', 'review', 'createdAt'];

    protected $casts = [
        'rating' => 'integer',
        'createdAt' => 'datetime',
    ];
}
