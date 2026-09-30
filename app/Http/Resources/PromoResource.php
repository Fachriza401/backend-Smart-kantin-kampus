<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'title' => (string) $this->title,
            'description' => (string) $this->description,
            'discount' => (float) $this->discount,
            // WAJIB int 0/1, bukan boolean.
            'active' => (int) $this->active,
            'icon' => $this->icon,
            'imageUrl' => $this->imageUrl,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ];
    }
}
