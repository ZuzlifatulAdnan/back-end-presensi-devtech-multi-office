<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Services\GeofenceService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct(private readonly GeofenceService $geofence) {}

    /**
     * The office the signed-in user belongs to, falling back to the first active
     * office when the account is not bound to one.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        $company = $user->company_id
            ? Company::query()->find($user->company_id)
            : $this->geofence->activeCompanies()->first();

        if (! $company) {
            return ApiResponse::error('Lokasi kantor belum dikonfigurasi.', 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data kantor berhasil dimuat.',
            'data' => new CompanyResource($company),
            // Legacy key kept so older app builds keep working.
            'company' => new CompanyResource($company),
        ]);
    }

    /**
     * Every office the user may check in at, for the map screen.
     */
    public function index(Request $request): JsonResponse
    {
        $companies = $this->geofence->companiesFor($request->user());

        return ApiResponse::success(
            CompanyResource::collection($companies),
            'Daftar kantor berhasil dimuat.',
            200,
            ['total' => $companies->count()]
        );
    }
}
