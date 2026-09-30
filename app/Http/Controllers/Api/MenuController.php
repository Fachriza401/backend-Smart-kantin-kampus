<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use App\Models\Rating;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /** GET /api/menus */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => MenuResource::collection(Menu::orderBy('id')->get()),
        ]);
    }

    /** POST /api/menus */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $menu = Menu::create($data);

        return response()->json(['data' => ['id' => (int) $menu->id]], 201);
    }

    /** PUT /api/menus/{id} */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $this->validated($request, true);

        $affected = Menu::where('id', $id)->update($data);

        return response()->json(['affected' => $affected]);
    }

    /** DELETE /api/menus/{id} */
    public function destroy(int $id): JsonResponse
    {
        return response()->json(['affected' => Menu::where('id', $id)->delete()]);
    }

    /** PATCH /api/menus/{id}/availability */
    public function availability(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['tersedia' => 'required|integer|min:0|max:1']);

        $affected = Menu::where('id', $id)->update([
            'tersedia' => (int) $data['tersedia'],
        ]);

        return response()->json(['affected' => $affected]);
    }

    /**
     * POST /api/menus/{id}/recalculate-rating
     * Menggantikan DBHelper.updateMenuRating() (AVG + COUNT dari `ratings`).
     */
    public function recalculateRating(int $id): JsonResponse
    {
        $row = Rating::where('menuId', $id)
            ->selectRaw('AVG(rating) AS avgRating, COUNT(*) AS total')
            ->first();

        $avg = $row ? (float) $row->avgRating : 0.0;
        $total = $row ? (int) $row->total : 0;

        Menu::where('id', $id)->update([
            'rating' => $avg,
            'reviewCount' => $total,
        ]);

        return response()->json([
            'data' => ['rating' => $avg, 'reviewCount' => $total],
        ]);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $rules = [
            'tenantId' => $partial ? 'sometimes|integer' : 'required|integer',
            'tenantName' => $partial ? 'sometimes|string|max:150' : 'required|string|max:150',
            'name' => $partial ? 'sometimes|string|max:191' : 'required|string|max:191',
            'price' => $partial ? 'sometimes|numeric' : 'required|numeric',
            'category' => $partial ? 'sometimes|string|max:100' : 'required|string|max:100',
            'description' => 'nullable|string',
            'rating' => 'nullable|numeric',
            'reviewCount' => 'nullable|integer',
            'estimasi' => 'nullable|string|max:50',
            'tersedia' => 'nullable|integer|min:0|max:1',
            'icon' => 'nullable|string|max:50',
            'imageUrl' => 'nullable|string',
        ];

        return $request->validate($rules);
    }
}
