<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PasswordResetRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'userId' => (int) $this->userId,
            'role' => (string) $this->role,
            'status' => (string) $this->status,
            'createdAt' => $this->createdAt instanceof \DateTimeInterface
                ? \Illuminate\Support\Carbon::parse($this->createdAt)->toIso8601String()
                : (string) $this->createdAt,
            'name' => $this->name,
            'email' => $this->email,
            'tenantName' => $this->tenantName,
        ];
    }
}
