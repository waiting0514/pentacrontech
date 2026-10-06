<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('user.manage');

        $query = User::with('roles');

        // Manager cannot see super-admin users
        if (!$request->user()->hasRole('super-admin')) {
            $query->whereDoesntHave('roles', fn ($q) => $q->where('name', 'super-admin'));
        }

        $users = $query->latest()->get();

        return $this->successResponse(UserResource::collection($users));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('user.manage');

        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username|regex:/^[a-zA-Z0-9_]+$/',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:manager,editor',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $user->assignRole($validated['role']);

        return $this->successResponse(new UserResource($user->load('roles')), '使用者已建立', 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('user.manage');

        // 非 super-admin 不可編輯 super-admin
        if ($user->hasRole('super-admin') && !$request->user()->hasRole('super-admin')) {
            return response()->json(['message' => '無權限編輯此使用者'], 403);
        }

        $validated = $request->validate([
            'username' => 'sometimes|string|max:50|unique:users,username,' . $user->id . '|regex:/^[a-zA-Z0-9_]+$/',
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'sometimes|string|in:manager,editor',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $role = $validated['role'] ?? null;
        unset($validated['role']);

        $user->update($validated);

        if ($role) {
            $user->syncRoles([$role]);
        }

        return $this->successResponse(new UserResource($user->load('roles')), '使用者已更新');
    }

    public function toggleStatus(User $user): JsonResponse
    {
        $this->authorize('user.manage');

        if ($user->username === 'admin') {
            return response()->json(['message' => '預設管理員帳號不可停用'], 422);
        }

        if ($user->id === auth()->id()) {
            return response()->json(['message' => '不可停用自己的帳號'], 422);
        }

        // 非 super-admin 不可停用 super-admin
        if ($user->hasRole('super-admin') && !auth()->user()->hasRole('super-admin')) {
            return response()->json(['message' => '無權限操作此使用者'], 403);
        }

        $user->update(['is_active' => !$user->is_active]);

        return $this->successResponse(new UserResource($user->load('roles')), $user->is_active ? '帳號已啟用' : '帳號已停用');
    }

    public function destroy(User $user): JsonResponse
    {
        if (!auth()->user()->hasRole('super-admin')) {
            return response()->json(['message' => '僅限超級管理員可刪除使用者'], 403);
        }

        if ($user->username === 'admin') {
            return response()->json(['message' => '預設管理員帳號不可刪除'], 422);
        }

        if ($user->id === auth()->id()) {
            return response()->json(['message' => '不可刪除自己的帳號'], 422);
        }

        $user->delete();

        return $this->successResponse(null, '使用者已刪除');
    }
}
