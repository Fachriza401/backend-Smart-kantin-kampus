<?php

namespace App\Models;

class User extends BaseModel
{
    protected $table = 'users';

    protected $fillable = [
        'name', 'email', 'password', 'role', 'nirm', 'photoPath', 'saldo', 'tenantName',
    ];

    protected $casts = [
        // WAJIB float: AppUser.fromMap memakai `(map['saldo'] as num)`.
        'saldo' => 'float',
    ];

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'userId');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'userId');
    }
}
