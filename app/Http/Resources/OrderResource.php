<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Order + `items` (array). Wajib menyertakan `items` walau kosong `[]`
 * karena Dart memisahkannya seperti query `order_items` per order.
 */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'userId' => (int) $this->userId,
            'orderCode' => (string) $this->orderCode,
            'tenantName' => (string) $this->tenantName,
            'total' => (float) $this->total,
            'paymentMethod' => (string) $this->paymentMethod,
            'paymentRecipient' => (string) $this->paymentRecipient,
            'paymentStatus' => (string) $this->paymentStatus,
            'status' => (string) $this->status,
            'pickupTime' => (string) $this->pickupTime,
            'note' => $this->note,
            'createdAt' => $this->createdAt?->toIso8601String(),
            'guestName' => $this->guestName,
            'guestEmail' => $this->guestEmail,
            'phoneNumber' => (string) $this->phoneNumber,
            'queueNumber' => (string) $this->queueNumber,
            'paymentLink' => $this->paymentLink,
            'paymentQrPayload' => $this->paymentQrPayload,
            'items' => OrderItemResource::collection(
                $this->whenLoaded('items', fn () => $this->items, fn () => $this->items)
            ),
        ];
    }
}
