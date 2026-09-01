<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AppSettingResource;
use App\Models\AppSetting;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class AppSettingController extends Controller
{
    /**
     * Public branding + feature flags. Called before login so the splash and
     * login screens can render the right app name and logo.
     */
    public function show(): JsonResponse
    {
        return ApiResponse::success(
            new AppSettingResource(AppSetting::current()),
            'Pengaturan aplikasi berhasil dimuat.'
        );
    }
}
