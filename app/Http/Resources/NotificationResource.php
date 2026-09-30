<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Semua key WAJIB ada, karena notification_screen.dart membaca baris ini
 * langsung dari `Map<String, dynamic>`.
 */
class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'userId' => (int) $this->userId,
            'title' => (string) $this->title,
            'message' => (string) $this->message,
            'type' => (string) $this->type,
            'isRead' => (int) $this->isRead,
            'createdAt' => $this->createdAt?->toIso8601String(),
            'targetType' => $this->targetType,
            'targetId' => $this->targetId === null ? null : (int) $this->targetId,
        ];
    }
}
