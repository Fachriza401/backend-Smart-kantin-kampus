<?php

namespace App\Models;

class Menu extends BaseModel
{
    protected $table = 'menus';

    protected $fillable = [
        'tenantId', 'tenantName', 'name', 'price', 'category', 'description',
        'rating', 'reviewCount', 'estimasi', 'tersedia', 'icon', 'imageUrl',
    ];

    protected $casts = [
        'price' => 'float',
        'rating' => 'float',
        'reviewCount' => 'integer',
        'tenantId' => 'integer',
        // WAJIB 'integer', JANGAN 'boolean':
        // MenuItem.fromMap menulis `(map['tersedia'] as int?) == 1`
        // dan `as int?` akan melempar kalau Dart menerima true/false.
        'tersedia' => 'integer',
    ];
}
