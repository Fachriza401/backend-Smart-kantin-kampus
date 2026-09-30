<?php

namespace App\Models;

class Promo extends BaseModel
{
    protected $table = 'promos';

    protected $fillable = [
        'title', 'description', 'discount', 'active', 'icon', 'imageUrl',
        'startDate', 'endDate',
    ];

    protected $casts = [
        'discount' => 'float',
        // WAJIB 'integer', JANGAN 'boolean':
        // Promo.fromMap menulis `(map['active'] as int) == 1`.
        'active' => 'integer',
    ];
}
