<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\PasswordHasher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * POST /api/auth/login
     *
     * `identifier` boleh email ATAU NIRM (persis seperti DBHelper.login()).
     * Logika verifikasi dipindahkan apa adanya dari sisi Dart.
     */
    public function login(Request $request): JsonResponse
    {
        $identifier = trim((string) $request->input('identifier', ''));
        $password = (string) $request->input('password', '');

        if ($identifier === '' || $password === '') {
            return response()->json([
                'message' => 'Username/email dan kata sandi wajib diisi',
            ], 422);
        }

        $user = User::where('email', $identifier)
            ->orWhere('nirm', $identifier)
            ->first();

        if (! $user || ! PasswordHasher::verify($password, $user->password)) {
            return response()->json([
                'message' => 'Kredensial staf tidak cocok.',
            ], 401);
        }

        // Upgrade password plaintext lama ke sha256 (meniru DBHelper.login()).
        if (PasswordHasher::needsUpgrade($user->password, $password)) {
            $user->password = PasswordHasher::hash($password);
            $user->save();
        }

        return response()->json([
            'data' => (new UserResource($user))->resolve($request),
            'token' => $this->issueToken($user),
        ]);
    }

    /**
     * Token sederhana berbasis HMAC. Cukup untuk aplikasi mobileinternal
     * (tidak butuh Sanctum/passport). Ganti dengan Sanctum bila butuh
     * refresh token & logout server-side.
     */
    private function issueToken(User $user): string
    {
        $payload = base64_encode(json_encode([
            'id' => $user->id,
            'role' => $user->role,
            'exp' => now()->addDays(7)->timestamp,
        ]));

        $signature = hash_hmac('sha256', $payload, (string) config('app.key'));

        return $payload.'.'.$signature;
    }
}
