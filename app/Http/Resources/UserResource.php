<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * User — `password` DIKIRIM apa adanya (sha256) karena
 * AppUser.fromMap melakukan `password: map['password'] as String`
 * dan tidak boleh diubah di sisi Dart.
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'name' => (string) $this->name,
            'email' => (string) $this->email,
            'password' => (string) $this->password,
            'role' => (string) $this->role,
            'nirm' => $this->nirm,
            'photoPath' => $this->photoPath,
            'saldo' => (float) $this->saldo,
            'tenantName' => $this->tenantName,
        ];
    }
}
