<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\AppSettingResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\UserResource;
use App\Models\AppSetting;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private const PROFILE_RELATIONS = ['shiftKerja', 'departemen', 'jabatan', 'company'];

    public function login(LoginRequest $request): JsonResponse
    {
        $request->ensureIsNotRateLimited();

        $user = User::query()
            ->with(self::PROFILE_RELATIONS)
            ->where('email', $request->validated('email'))
            ->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            $request->hitRateLimiter();

            return ApiResponse::error('Email atau password salah.', 401);
        }

        $request->clearRateLimiter();

        if ($fcmToken = $request->validated('fcm_token')) {
            $user->forceFill(['fcm_token' => $fcmToken])->save();
        }

        $token = $user->createToken($request->validated('device_name') ?: 'auth_token')->plainTextToken;

        return response()->json(
            array_merge(
                ['success' => true, 'message' => 'Login berhasil.', 'token' => $token],
                $this->profilePayload($user)
            )
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return ApiResponse::success(null, 'Berhasil keluar.');
    }

    /**
     * Sign out of every device at once.
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return ApiResponse::success(null, 'Berhasil keluar dari semua perangkat.');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(self::PROFILE_RELATIONS);

        return response()->json(
            array_merge(['success' => true, 'message' => 'Data profil berhasil dimuat.'], $this->profilePayload($user))
        );
    }

    /**
     * Store the face embedding captured during enrollment.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'face_embedding' => ['required', 'string'],
        ], [
            'face_embedding.required' => 'Data wajah wajib dikirim.',
        ]);

        $user = $request->user();
        $user->forceFill(['face_embedding' => $validated['face_embedding']])->save();

        return ApiResponse::success(
            new UserResource($user->load(self::PROFILE_RELATIONS)),
            'Data wajah berhasil diperbarui.'
        );
    }

    public function updateFcmToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => ['required', 'string', 'max:512'],
        ], [
            'fcm_token.required' => 'FCM token wajib dikirim.',
        ]);

        $request->user()->forceFill(['fcm_token' => $validated['fcm_token']])->save();

        return ApiResponse::success(null, 'FCM token berhasil diperbarui.');
    }

    /**
     * Shared login/me payload. Nested keys are the modern contract; the flat
     * keys are kept because existing app builds read them directly.
     *
     * @return array<string, mixed>
     */
    private function profilePayload(User $user): array
    {
        $settings = AppSetting::current();

        return [
            'data' => [
                'user' => new UserResource($user),
                'app' => new AppSettingResource($settings),
            ],
            'user' => new UserResource($user),
            'role' => $user->role,
            'work_mode' => $user->work_mode,
            'company' => $user->company ? new CompanyResource($user->company) : null,
            'position' => $user->jabatan ? [
                'id' => $user->jabatan->id,
                'name' => $user->jabatan->name,
            ] : null,
            'default_shift' => $user->shiftKerja ? [
                'id' => $user->shiftKerja->id,
                'name' => $user->shiftKerja->name,
            ] : null,
            'default_shift_detail' => $user->shiftKerja ? [
                'id' => $user->shiftKerja->id,
                'name' => $user->shiftKerja->name,
                'start_time' => $user->shiftKerja->start_time,
                'end_time' => $user->shiftKerja->end_time,
            ] : null,
            'department' => $user->departemen ? [
                'id' => $user->departemen->id,
                'name' => $user->departemen->name,
            ] : null,
        ];
    }
}
