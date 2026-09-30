<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\PasswordHasher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * GET /api/users
     * Query: email | nirm | identifier | role
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($role = $request->query('role')) {
            return response()->json([
                'data' => UserResource::collection(
                    User::where('role', $role)->orderBy('id')->get()
                ),
            ]);
        }

        if ($email = $request->query('email')) {
            $user = User::where('email', $email)->first();
        } elseif ($nirm = $request->query('nirm')) {
            $user = User::where('nirm', $nirm)->first();
        } elseif ($identifier = $request->query('identifier')) {
            $user = User::where('email', $identifier)
                ->orWhere('nirm', $identifier)
                ->first();
        } else {
            return response()->json([
                'data' => UserResource::collection(User::orderBy('id')->get()),
            ]);
        }

        return response()->json([
            'data' => $user ? (new UserResource($user))->resolve($request) : null,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        return response()->json([
            'data' => $user ? (new UserResource($user))->resolve($request) : null,
        ]);
    }

    /** POST /api/users — id di-generate server (AUTO_INCREMENT). */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:191|unique:users,email',
            'password' => 'required|string|max:191',
            'role' => 'required|string|max:30',
            'nirm' => 'nullable|string|max:50|unique:users,nirm',
            'photoPath' => 'nullable|string',
            'saldo' => 'nullable|numeric',
            'tenantName' => 'nullable|string|max:150',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            // Password datang ter-hash dari Dart; apa adanya disimpan.
            'password' => $data['password'],
            'role' => $data['role'],
            'nirm' => $data['nirm'] ?? null,
            'photoPath' => $data['photoPath'] ?? null,
            'saldo' => $data['saldo'] ?? 0,
            'tenantName' => $data['tenantName'] ?? null,
        ]);

        return response()->json(['data' => ['id' => (int) $user->id]], 201);
    }

    /** PUT /api/users/{id} */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'email' => ['sometimes', 'required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($id)],
            'password' => 'sometimes|required|string|max:191',
            'role' => 'sometimes|required|string|max:30',
            'nirm' => ['sometimes', 'nullable', 'string', 'max:50', Rule::unique('users', 'nirm')->ignore($id)],
            'photoPath' => 'sometimes|nullable|string',
            'saldo' => 'sometimes|nullable|numeric',
            'tenantName' => 'sometimes|nullable|string|max:150',
        ]);

        $user->fill($data)->save();

        return response()->json(['affected' => 1]);
    }

    /** PUT /api/users/{id}/password — `password` sudah sha256 dari Dart. */
    public function updatePassword(Request $request, int $id): JsonResponse
    {
        $request->validate(['password' => 'required|string|max:191']);

        $affected = User::where('id', $id)->update([
            'password' => $request->input('password'),
        ]);

        return response()->json(['affected' => $affected]);
    }
}
