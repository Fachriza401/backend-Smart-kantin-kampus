<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends BaseModel
{
    protected $table = 'orders';

    protected $fillable = [
        'userId', 'orderCode', 'tenantName', 'total', 'paymentMethod',
        'paymentRecipient', 'paymentStatus', 'status', 'pickupTime', 'note',
        'createdAt', 'guestName', 'guestEmail', 'phoneNumber', 'queueNumber',
        'paymentLink', 'paymentQrPayload',
    ];

    protected $casts = [
        'total' => 'float',
        'userId' => 'integer',
        'createdAt' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'orderId');
    }
}
