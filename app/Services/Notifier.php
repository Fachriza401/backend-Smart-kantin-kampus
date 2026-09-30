<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Pembuat notifikasi — pengganti logika fan-out yang dulu ada di dalam
 * DBHelper.createOrder() / updateOrderStatus() / confirmCashPayment().
 *
 * Judul & pesan TIDAK boleh diubah karena sudah dipakai di UI Flutter.
 */
class Notifier
{
    public static function send(
        int $userId,
        string $title,
        string $message,
        string $type,
        ?string $targetType = null,
        ?int $targetId = null,
    ): ?Notification {
        if ($userId <= 0) {
            return null;
        }

        return Notification::create([
            'userId' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'isRead' => 0,
            'createdAt' => Carbon::now(),
            'targetType' => $targetType,
            'targetId' => $targetId,
        ]);
    }

    /** Hanya membuat bila belum ada (menggantikan DBHelper._createIfMissing). */
    public static function sendOnce(
        int $userId,
        string $title,
        string $message,
        string $type,
        ?string $targetType = null,
        ?int $targetId = null,
    ): ?Notification {
        $exists = Notification::where('userId', $userId)
            ->where('title', $title)
            ->where('message', $message)
            ->exists();

        if ($exists) {
            return null;
        }

        return self::send($userId, $title, $message, $type, $targetType, $targetId);
    }

    /** @return array<int> */
    public static function staffIdsFor(string $tenantName): array
    {
        return User::where('tenantName', $tenantName)
            ->whereIn('role', ['tenant', 'kasir'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Semua tenant + kasir tanpa filter tenantName.
     * Dipakai confirmCashPayment() & markVirtualPaymentPaid() — query aslinya
     * `WHERE role IN ('tenant','kasir')` tanpa memakai tenantName sama sekali.
     *
     * @return array<int, string> id => role, untuk membedakan tenant vs kasir.
     */
    public static function allStaffWithRoles(): array
    {
        $map = [];

        foreach (User::whereIn('role', ['tenant', 'kasir'])->get(['id', 'role']) as $user) {
            $map[(int) $user->id] = (string) $user->role;
        }

        return $map;
    }

    /** @return array<int> */
    public static function adminIds(): array
    {
        return User::where('role', 'admin')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
