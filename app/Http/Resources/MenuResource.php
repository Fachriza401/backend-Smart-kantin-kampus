<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'tenantId' => (int) $this->tenantId,
            'tenantName' => (string) $this->tenantName,
            'name' => (string) $this->name,
            'price' => (float) $this->price,
            'category' => (string) $this->category,
            'description' => $this->description,
            'rating' => (float) $this->rating,
            'reviewCount' => (int) $this->reviewCount,
            'estimasi' => $this->estimasi,
            // WAJIB int 0/1, bukan boolean.
            'tersedia' => (int) $this->tersedia,
            'icon' => $this->icon,
            'imageUrl' => $this->imageUrl,
        ];
    }
}
