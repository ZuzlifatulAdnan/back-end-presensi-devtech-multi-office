<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdatePasswordRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    private const PROFILE_RELATIONS = ['company', 'shiftKerja', 'departemen', 'jabatan'];

    /**
     * A user may only read their own profile; managers and admins may read any.
     */
    public function getUserId(Request $request, int $id): JsonResponse
    {
        $currentUser = $request->user();

        if ($currentUser->id !== $id && ! in_array($currentUser->role, ['admin', 'manager', 'hr'], true)) {
            return ApiResponse::error('Anda tidak berhak mengakses data pengguna ini.', 403);
        }

        $user = User::query()->with(self::PROFILE_RELATIONS)->find($id);

        if (! $user) {
            return ApiResponse::error('Pengguna tidak ditemukan.', 404);
        }

        return ApiResponse::success(
            ['user' => new UserResource($user)],
            'Data pengguna berhasil dimuat.'
        );
    }

    /**
     * Update the signed-in user's own profile. The target is always the token
     * owner, never an id taken from the request body.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->fill(Arr::only($validated, ['name', 'email', 'phone']));

        if ($request->hasFile('image')) {
            $previousImage = $user->image_url;

            $user->image_url = $request->file('image')->storeAs(
                'images/users',
                $user->id.'-'.now()->format('YmdHis').'.'.($request->file('image')->extension() ?: 'jpg'),
                'public'
            );

            if ($previousImage && $previousImage !== $user->image_url) {
                Storage::disk('public')->delete($previousImage);
            }
        }

        $user->save();
        $user->load(self::PROFILE_RELATIONS);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data' => new UserResource($user),
            // Legacy keys kept so older app builds keep working.
            'status' => 'Success',
        ]);
    }

    /**
     * Change the signed-in user's password and invalidate every other session.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->forceFill([
            'password' => $request->validated('password'),
            'password_changed_at' => now(),
        ])->save();

        $currentTokenId = $request->user()->currentAccessToken()?->id;

        $user->tokens()
            ->when($currentTokenId, fn ($query) => $query->where('id', '!=', $currentTokenId))
            ->delete();

        return ApiResponse::success(
            ['password_changed_at' => $user->password_changed_at?->toIso8601String()],
            'Password berhasil diubah. Perangkat lain telah dikeluarkan.'
        );
    }
}
