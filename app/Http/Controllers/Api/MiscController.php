<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Rating;
use App\Models\Topup;
use App\Services\Notifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MiscController extends Controller
{
    /** POST /api/topups */
    public function storeTopup(Request $request): JsonResponse
    {
        $data = $request->validate([
            'userId' => 'required|integer',
            'amount' => 'required|numeric',
            'status' => 'nullable|string|max:30',
        ]);

        $row = Topup::create([
            'userId' => $data['userId'],
            'amount' => (float) $data['amount'],
            'status' => $data['status'] ?? 'success',
            'createdAt' => Carbon::now(),
        ]);

        return response()->json(['data' => ['id' => (int) $row->id]], 201);
    }

    /**
     * POST /api/ratings
     * updateOrCreate pada (userId, menuId, orderId) — menggantikan
     * ConflictAlgorithm.replace-nya SQLite.
     */
    public function storeRating(Request $request): JsonResponse
    {
        $data = $request->validate([
            'userId' => 'required|integer',
            'menuId' => 'required|integer',
            'orderId' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
        ]);

        $row = Rating::updateOrCreate(
            [
                'userId' => $data['userId'],
                'menuId' => $data['menuId'],
                'orderId' => $data['orderId'],
            ],
            [
                'rating' => (int) $data['rating'],
                'review' => $data['review'] ?? null,
                'createdAt' => Carbon::now(),
            ],
        );

        return response()->json(['data' => ['id' => (int) $row->id]], 201);
    }

    /**
     * POST /api/customers/{userId}/sync-notifications
     * Menggantikan DBHelper.syncCustomerNotifications():
     * 3 menu paling sering dipesan (by quantity, exclude pesanan dibatalkan).
     */
    public function syncCustomerNotifications(int $userId): JsonResponse
    {
        $popular = OrderItem::query()
            ->select('order_items.menuName', DB::raw('SUM(order_items.quantity) AS total'))
            ->join('orders', 'orders.id', '=', 'order_items.orderId')
            ->where('orders.status', '!=', 'Dibatalkan')
            ->groupBy('order_items.menuName')
            ->orderByDesc('total')
            ->limit(3)
            ->get();

        $created = 0;

        if ($popular->isNotEmpty()) {
            $names = $popular->pluck('menuName')->implode(', ');

            $result = Notifier::sendOnce(
                $userId,
                'Pesanan Populer',
                "Menu yang paling sering dipesan: {$names}.",
                'popular',
                'popular',
            );

            if ($result) {
                $created++;
            }
        }

        // Sync promo juga (itu yang dilakukan DBHelper aslinya).
        $promoRequest = Request::create('/api/promos/sync-notifications', 'POST', [
            'userId' => $userId,
        ]);
        app(PromoController::class)->syncNotifications($promoRequest);

        return response()->json(['data' => ['created' => $created]]);
    }
}
