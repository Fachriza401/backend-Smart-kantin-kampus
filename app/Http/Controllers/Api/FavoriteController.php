<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Notifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    /** GET /api/favorites?user_id= */
    public function index(Request $request): JsonResponse
    {
        $rows = Favorite::where('userId', (int) $request->query('user_id'))->get();

        return response()->json([
            'data' => $rows->map(fn ($row) => [
                'userId' => (int) $row->userId,
                'menuId' => (int) $row->menuId,
                'createdAt' => Carbon::parse($row->createdAt)->toIso8601String(),
            ])->values(),
        ]);
    }

    /** POST /api/favorites — idempoten (menggantikan ConflictAlgorithm.replace). */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'userId' => 'required|integer',
            'menuId' => 'required|integer',
        ]);

        DB::table('favorites')->insertOrIgnore([
            'userId' => $data['userId'],
            'menuId' => $data['menuId'],
            'createdAt' => Carbon::now(),
        ]);

        return response()->json(['data' => ['ok' => true]], 201);
    }

    /** DELETE /api/favorites?user_id=&menu_id= */
    public function destroy(Request $request): JsonResponse
    {
        Favorite::where('userId', (int) $request->query('user_id'))
            ->where('menuId', (int) $request->query('menu_id'))
            ->delete();

        return response()->json(['data' => ['ok' => true]]);
    }
}
