<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rows = Notification::where('userId', (int) $request->query('user_id'))
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => NotificationResource::collection($rows)]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'userId' => 'required|integer',
            'title' => 'required|string|max:191',
            'message' => 'required|string',
            'type' => 'required|string|max:50',
            'isRead' => 'nullable|integer|min:0|max:1',
            'targetType' => 'nullable|string|max:50',
            'targetId' => 'nullable|integer',
        ]);

        $row = Notification::create([
            'userId' => $data['userId'],
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => $data['type'],
            'isRead' => $data['isRead'] ?? 0,
            'createdAt' => Carbon::now(),
            'targetType' => $data['targetType'] ?? null,
            'targetId' => $data['targetId'] ?? null,
        ]);

        return response()->json(['data' => ['id' => (int) $row->id]], 201);
    }

    /** PUT /api/notifications/read-all?user_id= */
    public function markAllRead(Request $request): JsonResponse
    {
        $affected = Notification::where('userId', (int) $request->query('user_id'))
            ->update(['isRead' => 1]);

        return response()->json(['affected' => $affected]);
    }

    /** GET /api/notifications/unread-count?user_id= */
    public function unreadCount(Request $request): JsonResponse
    {
        $total = Notification::where('userId', (int) $request->query('user_id'))
            ->where('isRead', 0)
            ->count();

        return response()->json(['data' => ['total' => (int) $total]]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        return response()->json(['affected' => Notification::where('id', $id)->delete()]);
    }

    /** DELETE /api/notifications?user_id= */
    public function destroyAll(Request $request): JsonResponse
    {
        $affected = Notification::where('userId', (int) $request->query('user_id'))->delete();

        return response()->json(['affected' => $affected]);
    }
}
