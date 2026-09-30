<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends BaseModel
{
    protected $table = 'order_items';

    protected $fillable = ['orderId', 'menuName', 'price', 'quantity'];

    protected $casts = [
        'price' => 'float',
        'quantity' => 'integer',
        'orderId' => 'integer',
    ];
}
