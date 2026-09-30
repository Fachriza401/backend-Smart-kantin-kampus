<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tabel `favorites` tidak punya kolom `id` (primary key komposit).
 * Karena itu auto-increment dimatikan.
 */
class Favorite extends BaseModel
{
    protected $table = 'favorites';

    protected $primaryKey = null;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['userId', 'menuId', 'createdAt'];

    protected $casts = [
        'createdAt' => 'datetime',
    ];
}
