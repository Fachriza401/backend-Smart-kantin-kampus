<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PasswordResetRequestResource;
use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PasswordResetRequestController extends Controller
{
    /** POST /api/password-reset-requests */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'userId' => 'required|integer',
            'role' => 'required|string|max:30',
        ]);

        $row = PasswordResetRequest::create([
            'userId' => $data['userId'],
            'role' => $data['role'],
            'status' => 'pending',
            'createdAt' => Carbon::now(),
        ]);

        return response()->json(['data' => ['id' => (int) $row->id]], 201);
    }

    /**
     * GET /api/password-reset-requests?status=pending
     * Menggantikan query `SELECT r.*, u.name, u.email, u.tenantName ... JOIN users`.
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status', 'pending');

        $rows = PasswordResetRequest::query()
            ->join('users', 'users.id', '=', 'password_reset_requests.userId')
            ->where('password_reset_requests.status', $status)
            ->orderByDesc('password_reset_requests.id')
            ->get([
                'password_reset_requests.id',
                'password_reset_requests.userId',
                'password_reset_requests.role',
                'password_reset_requests.status',
                'password_reset_requests.createdAt',
                'users.name',
                'users.email',
                'users.tenantName',
            ]);

        return response()->json([
            'data' => PasswordResetRequestResource::collection($rows),
        ]);
    }

    /**
     * PUT /api/password-reset-requests/{id}
     * WAJIB satu transaksi: update password lalu set status resolved.
     */
    public function resolve(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'userId' => 'required|integer',
            'password' => 'required|string|max:191',
        ]);

        DB::transaction(function () use ($id, $data) {
            User::where('id', $data['userId'])->update([
                'password' => $data['password'],
            ]);

            PasswordResetRequest::where('id', $id)->update([
                'status' => 'resolved',
            ]);
        });

        return response()->json(['affected' => 1]);
    }
}
