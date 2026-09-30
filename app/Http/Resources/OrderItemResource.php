<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'orderId' => (int) $this->orderId,
            'menuName' => (string) $this->menuName,
            'price' => (float) $this->price,
            'quantity' => (int) $this->quantity,
        ];
    }
}
