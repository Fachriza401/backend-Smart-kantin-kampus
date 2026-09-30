<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromoResource;
use App\Models\Promo;
use App\Models\User;
use App\Services\Notifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => PromoResource::collection(Promo::orderBy('id')->get()),
        ]);
    }

    /**
     * GET /api/promos/best-active
     * WAJIB didaftarkan SEBELUM /promos/{id} agar tidak tertangkap route.
     */
    public function bestActive(): JsonResponse
    {
        $promo = Promo::where('active', 1)->orderByDesc('discount')->first();

        return response()->json([
            'data' => $promo ? (new PromoResource($promo))->resolve() : null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'required|string',
            'discount' => 'required|numeric',
            'active' => 'nullable|integer|min:0|max:1',
            'icon' => 'nullable|string|max:50',
            'imageUrl' => 'nullable|string',
            'startDate' => 'nullable|string|max:50',
            'endDate' => 'nullable|string|max:50',
        ]);

        $promo = Promo::create($data);

        return response()->json(['data' => ['id' => (int) $promo->id]], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:191',
            'description' => 'sometimes|required|string',
            'discount' => 'sometimes|required|numeric',
            'active' => 'nullable|integer|min:0|max:1',
            'icon' => 'nullable|string|max:50',
            'imageUrl' => 'nullable|string',
            'startDate' => 'nullable|string|max:50',
            'endDate' => 'nullable|string|max:50',
        ]);

        return response()->json(['affected' => Promo::where('id', $id)->update($data)]);
    }

    public function destroy(int $id): JsonResponse
    {
        return response()->json(['affected' => Promo::where('id', $id)->delete()]);
    }

    /**
     * POST /api/promos/sync-notifications
     * Body: { userId } opsional. Tanpa userId = semua mahasiswa & dosen.
     * Menggantikan DBHelper.syncPromoNotifications().
     */
    public function syncNotifications(Request $request): JsonResponse
    {
        $userId = $request->input('userId');

        $users = $userId
            ? User::where('id', (int) $userId)->get()
            : User::whereIn('role', ['mahasiswa', 'dosen'])->get();

        $promos = Promo::where('active', 1)->orderByDesc('id')->get();
        $created = 0;

        foreach ($promos as $promo) {
            $title = (string) $promo->title;
            $message = "Diskon {$promo->discount}%: ".($promo->description ?: $title);

            foreach ($users as $user) {
                $result = Notifier::sendOnce(
                    (int) $user->id,
                    "Promo: {$title}",
                    $message,
                    'promo',
                    'promo',
                    (int) $promo->id,
                );

                if ($result) {
                    $created++;
                }
            }
        }

        return response()->json(['data' => ['created' => $created]]);
    }
}
