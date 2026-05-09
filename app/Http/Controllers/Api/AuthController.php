<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // login
    public function login(Request $request)
    {
        $loginData = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $loginData['email'])->first();

        // check user exist
        if (! $user) {
            return response(['message' => 'Invalid credentials'], 401);
        }

        // check password
        if (! Hash::check($loginData['password'], $user->password)) {
            return response(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Load relationships
        $user->load(['shiftKerja', 'departemen', 'jabatan']);

        $response = [
            'user' => new UserResource($user),
            'token' => $token,
            'role' => $user->role,
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

        return response($response, 200);
    }

    // logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response(['message' => 'Logged out'], 200);
    }

    // update image profile & face_embedding
    public function updateProfile(Request $request)
    {
        $request->validate([
            // 'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'face_embedding' => 'required',
        ]);

        $user = $request->user();
        // $image = $request->file('image');
        $face_embedding = $request->face_embedding;

        // //save image
        // $image->storeAs('public/images', $image->hashName());
        // $user->image_url = $image->hashName();
        $user->face_embedding = $face_embedding;
        $user->save();

        return response([
            'message' => 'Profile updated',
            'user' => new UserResource($user),
        ], 200);
    }

    // update fcm token
    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required',
        ]);

        $user = $request->user();
        $user->fcm_token = $request->fcm_token;
        $user->save();

        return response([
            'message' => 'FCM token updated',
        ], 200);
    }

    // get current user data
    public function me(Request $request)
    {
        $user = $request->user();

        // Load relationships
        $user->load(['shiftKerja', 'departemen', 'jabatan']);

        $response = [
            'user' => new UserResource($user),
            'role' => $user->role,
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

        return response($response, 200);
    }
}
