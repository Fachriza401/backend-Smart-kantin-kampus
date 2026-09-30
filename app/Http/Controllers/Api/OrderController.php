<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Notifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * GET /api/orders
     * Query: user_id | guest_email | tenant_name | order_code
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::query()->with('items')->orderByDesc('id');

        if ($userId = $request->query('user_id')) {
            $query->where('userId', (int) $userId);
        }

        if ($guestEmail = $request->query('guest_email')) {
            // Guest checkout: userId = 0 (lihat getOrdersByGuestEmail).
            $query->where('guestEmail', mb_strtolower(trim($guestEmail)))
                ->where('userId', 0);
        }

        if ($tenantName = $request->query('tenant_name')) {
            $query->where('tenantName', $tenantName);
        }

        if ($code = $request->query('order_code')) {
            $order = (clone $query)->where('orderCode', trim($code))->first();

            return response()->json([
                'data' => $order ? (new OrderResource($order))->resolve($request) : null,
            ]);
        }

        return response()->json([
            'data' => OrderResource::collection($query->get()),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $order = Order::with('items')->find($id);

        return response()->json([
            'data' => $order ? (new OrderResource($order))->resolve($request) : null,
        ]);
    }

    /**
     * POST /api/orders
     * Pesanan + order_items + nomor antrean + notifikasi, satu transaksi.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'userId' => 'nullable|integer',
            'orderCode' => 'required|string|max:50|unique:orders,orderCode',
            'tenantName' => 'required|string|max:150',
            'total' => 'required|numeric',
            'paymentMethod' => 'required|string|max:50',
            'paymentRecipient' => 'nullable|string|max:150',
            'paymentStatus' => 'nullable|string|max:50',
            'status' => 'required|string|max:50',
            'pickupTime' => 'required|string|max:50',
            'note' => 'nullable|string',
            'createdAt' => 'nullable|string',
            'guestName' => 'nullable|string|max:150',
            'guestEmail' => 'nullable|string|max:191',
            'phoneNumber' => 'nullable|string|max:30',
            'queueNumber' => 'nullable|string|max:10',
            'paymentLink' => 'nullable|string',
            'paymentQrPayload' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.menuName' => 'required|string|max:191',
            'items.*.price' => 'required|numeric',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $order = DB::transaction(function () use ($data) {
            $order = Order::create([
                'userId' => (int) ($data['userId'] ?? 0),
                'orderCode' => $data['orderCode'],
                'tenantName' => $data['tenantName'],
                'total' => (float) $data['total'],
                'paymentMethod' => $data['paymentMethod'],
                'paymentRecipient' => $data['paymentRecipient'] ?? 'Admin Smart Kantin',
                // Menyalin DBHelper.createOrder(): status pembayaran SELALU
                // diturunkan dari paymentMethod, nilai dari Dart diabaikan.
                'paymentStatus' => $data['paymentMethod'] === 'Bayar Langsung'
                    ? 'Menunggu Pembayaran'
                    : 'Lunas',
                'status' => $data['status'],
                'pickupTime' => $data['pickupTime'],
                'note' => $data['note'] ?? null,
                'createdAt' => isset($data['createdAt'])
                    ? Carbon::parse($data['createdAt'])
                    : Carbon::now(),
                'guestName' => $data['guestName'] ?? null,
                'guestEmail' => isset($data['guestEmail'])
                    ? mb_strtolower(trim($data['guestEmail']))
                    : null,
                'phoneNumber' => $data['phoneNumber'] ?? '',
                'queueNumber' => $data['queueNumber'] ?? 'A01',
                'paymentLink' => $data['paymentLink'] ?? null,
                'paymentQrPayload' => $data['paymentQrPayload'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                OrderItem::create([
                    'orderId' => $order->id,
                    'menuName' => $item['menuName'],
                    'price' => (float) $item['price'],
                    'quantity' => (int) $item['quantity'],
                ]);
            }

            return $order;
        });

        // ---- Fan-out notifikasi (teks disalin dari DBHelper.createOrder()) ----
        $buyer = $order->guestName ?: 'Guest';

        if ($order->userId > 0) {
            Notifier::send(
                (int) $order->userId,
                'Pesanan berhasil dibuat',
                "{$order->orderCode} tersimpan. Nomor antrean {$order->queueNumber}.",
                'order',
                'order',
                (int) $order->id,
            );
        }

        foreach (Notifier::staffIdsFor($order->tenantName) as $staffId) {
            Notifier::send(
                $staffId,
                'Pesanan Baru',
                "{$order->orderCode} dari {$buyer} menunggu proses.",
                'order',
                'order',
                (int) $order->id,
            );
        }

        foreach (Notifier::adminIds() as $adminId) {
            Notifier::send(
                $adminId,
                'Transaksi Guest Masuk',
                "{$order->orderCode} • {$buyer} • {$order->paymentMethod}.",
                'payment',
                'order',
                (int) $order->id,
            );
        }

        return response()->json(['data' => ['id' => (int) $order->id]], 201);
    }

    /** PUT /api/orders/{id}/status */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['status' => 'required|string|max:50']);

        $order = Order::find($id);

        if (! $order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        }

        Order::where('id', $id)->update(['status' => $data['status']]);

        $userId = (int) $order->userId;
        $code = $order->orderCode;
        $status = $data['status'];

        if ($userId > 0) {
            Notifier::send(
                $userId,
                'Status Pesanan',
                "Pesanan {$code} sekarang: {$status}.",
                'order',
                'order',
                (int) $order->id,
            );
        }

        foreach (Notifier::staffIdsFor($order->tenantName) as $staffId) {
            if ($status === 'Siap Diambil') {
                Notifier::send(
                    $staffId,
                    'Pesanan Siap Diambil',
                    "{$code} sudah selesai disiapkan dan siap diverifikasi kasir.",
                    'order',
                    'order',
                    (int) $order->id,
                );
            } elseif (in_array($status, ['Diproses', 'Dimasak'], true)) {
                Notifier::send(
                    $staffId,
                    'Update Produksi Pesanan',
                    "{$code} sekarang berstatus {$status}.",
                    'order',
                    'order',
                    (int) $order->id,
                );
            }
        }

        foreach (Notifier::adminIds() as $adminId) {
            Notifier::send(
                $adminId,
                'Monitoring Pesanan',
                "{$code} sekarang berstatus {$status}.",
                'order',
                'order',
                (int) $order->id,
            );
        }

        return response()->json(['affected' => 1]);
    }

    /** POST /api/orders/{id}/confirm-cash-payment */
    public function confirmCashPayment(int $id): JsonResponse
    {
        $order = Order::find($id);

        if (! $order) {
            return response()->json(['affected' => 0]);
        }

        if (in_array($order->status, ['Dibatalkan', 'Selesai'], true)) {
            return response()->json(['affected' => 0]);
        }

        $affected = Order::where('id', $id)->update([
            'paymentStatus' => 'Lunas',
            'status' => 'Menunggu Persetujuan Tenant',
        ]);

        if ($affected > 0) {
            $code = $order->orderCode;

            if ((int) $order->userId > 0) {
                Notifier::send(
                    (int) $order->userId,
                    'Pembayaran Dikonfirmasi',
                    "Pembayaran {$code} sudah dinyatakan Lunas oleh petugas.",
                    'payment',
                    'order',
                    (int) $order->id,
                );
            }

            foreach (Notifier::adminIds() as $adminId) {
                Notifier::send(
                    $adminId,
                    'Pembayaran Lunas',
                    "Pembayaran langsung {$code} telah dikonfirmasi.",
                    'payment',
                    'order',
                    (int) $order->id,
                );
            }

            foreach (Notifier::allStaffWithRoles() as $staffId => $role) {
                $isTenant = $role === 'tenant';

                Notifier::send(
                    $staffId,
                    $isTenant ? 'Pesanan Menunggu Persetujuan' : 'Pembayaran Lunas',
                    $isTenant
                        ? "{$code} sudah lunas dan menunggu persetujuan Tenant sebelum diproses."
                        : "{$code} sudah lunas dan akan diproses setelah disetujui Tenant.",
                    'payment',
                    'order',
                    (int) $order->id,
                );
            }
        }

        return response()->json(['affected' => $affected]);
    }

    /** POST /api/orders/{id}/mark-virtual-paid */
    public function markVirtualPaymentPaid(int $id): JsonResponse
    {
        $order = Order::find($id);

        if (! $order) {
            return response()->json(['affected' => 0]);
        }

        if (in_array($order->status, ['Dibatalkan', 'Selesai'], true)) {
            return response()->json(['affected' => 0]);
        }

        $affected = Order::where('id', $id)->update([
            'paymentStatus' => 'Lunas',
            'status' => 'Menunggu Persetujuan Tenant',
        ]);

        if ($affected > 0) {
            $code = $order->orderCode;
            $userId = (int) $order->userId;

            if ($userId > 0) {
                Notifier::send(
                    $userId,
                    'Pembayaran Berhasil',
                    "Pembayaran {$code} sudah dinyatakan Lunas. Pesanan menunggu persetujuan Tenant.",
                    'payment',
                    'order',
                    (int) $order->id,
                );
            }

            foreach (Notifier::adminIds() as $adminId) {
                Notifier::send(
                    $adminId,
                    'Pembayaran Guest Lunas',
                    "Pembayaran virtual {$code} sudah diverifikasi.",
                    'payment',
                    'order',
                    (int) $order->id,
                );
            }

            foreach (Notifier::allStaffWithRoles() as $staffId => $role) {
                Notifier::send(
                    $staffId,
                    'Pembayaran Guest Lunas',
                    "{$code} dapat diproses setelah pembayaran diterima.",
                    'payment',
                    'order',
                    (int) $order->id,
                );
            }
        }

        return response()->json(['affected' => $affected]);
    }
}
