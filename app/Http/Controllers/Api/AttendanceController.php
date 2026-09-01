<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckInRequest;
use App\Http\Requests\Api\CheckOutRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Services\AttendanceService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendanceService) {}

    /**
     * Map + eligibility payload rendered before the user presses "Presensi".
     */
    public function preCheck(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $state = $this->attendanceService->preCheck(
            $request->user(),
            isset($validated['latitude']) ? (float) $validated['latitude'] : null,
            isset($validated['longitude']) ? (float) $validated['longitude'] : null,
        );

        $attendance = $state['attendance'];
        $state['attendance'] = $attendance ? new AttendanceResource($attendance) : null;

        return ApiResponse::success($state, 'Status presensi berhasil dimuat.');
    }

    /**
     * Office pins for the map screen, ordered by distance when a position is sent.
     */
    public function locations(Request $request): JsonResponse
    {
        $state = $this->attendanceService->preCheck(
            $request->user(),
            $request->filled('latitude') ? (float) $request->input('latitude') : null,
            $request->filled('longitude') ? (float) $request->input('longitude') : null,
        );

        return ApiResponse::success([
            'locations' => $state['locations'],
            'nearest_location' => $state['nearest_location'],
            'map' => $state['map'],
        ], 'Daftar lokasi kantor berhasil dimuat.');
    }

    public function checkin(CheckInRequest $request): JsonResponse
    {
        $attendance = $this->attendanceService->checkIn($request->user(), $request->payload());
        $attendance->load(['shift', 'company']);

        return response()->json([
            'success' => true,
            'message' => 'Absen masuk berhasil.',
            'data' => new AttendanceResource($attendance),
            // Legacy key kept so older app builds keep working.
            'attendance' => new AttendanceResource($attendance),
        ], 201);
    }

    public function checkout(CheckOutRequest $request): JsonResponse
    {
        $attendance = $this->attendanceService->checkOut($request->user(), $request->payload());
        $attendance->load(['shift', 'company']);

        return response()->json([
            'success' => true,
            'message' => 'Absen pulang berhasil.',
            'data' => new AttendanceResource($attendance),
            'attendance' => new AttendanceResource($attendance),
        ]);
    }

    /**
     * Today's record together with the next action the app should offer.
     */
    public function today(Request $request): JsonResponse
    {
        $attendance = $this->attendanceService->findTodayAttendance($request->user());

        return ApiResponse::success([
            'checkedin' => $attendance !== null,
            'checkedout' => (bool) $attendance?->isCheckedOut(),
            'next_action' => match (true) {
                $attendance === null => 'check_in',
                ! $attendance->isCheckedOut() => 'check_out',
                default => 'done',
            },
            'attendance' => $attendance ? new AttendanceResource($attendance) : null,
        ], 'Status presensi hari ini berhasil dimuat.');
    }

    /**
     * Legacy endpoint kept for older app builds.
     */
    public function isCheckedin(Request $request): JsonResponse
    {
        $attendance = $this->attendanceService->findTodayAttendance($request->user());

        return response()->json([
            'success' => true,
            'checkedin' => $attendance !== null,
            'checkedout' => (bool) $attendance?->isCheckedOut(),
            'data' => $attendance ? new AttendanceResource($attendance) : null,
        ]);
    }

    /**
     * Attendance history. Paginated when the caller asks for a page, otherwise
     * the most recent records are returned in one payload.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'status' => ['nullable', Rule::in([Attendance::STATUS_ON_TIME, Attendance::STATUS_LATE, Attendance::STATUS_ABSENT])],
            'work_mode' => ['nullable', Rule::in(Attendance::MODES)],
            'per_page' => ['nullable', 'integer', 'between:1,200'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Attendance::query()
            ->with(['shift', 'company'])
            ->forUser($request->user()->id)
            ->when($validated['date'] ?? null, fn ($q, $date) => $q->onDate($date))
            ->when($validated['from'] ?? null, fn ($q, $from) => $q->whereDate('date', '>=', $from))
            ->when($validated['to'] ?? null, fn ($q, $to) => $q->whereDate('date', '<=', $to))
            ->when($validated['month'] ?? null, fn ($q, $month) => $q->whereMonth('date', $month))
            ->when($validated['year'] ?? null, fn ($q, $year) => $q->whereYear('date', $year))
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($validated['work_mode'] ?? null, fn ($q, $mode) => $q->where('work_mode', $mode))
            ->orderByDesc('date')
            ->orderByDesc('time_in');

        if ($request->hasAny(['page', 'per_page'])) {
            $paginated = $query->paginate((int) ($validated['per_page'] ?? 25));

            return ApiResponse::success(
                AttendanceResource::collection($paginated),
                'Riwayat presensi berhasil dimuat.'
            );
        }

        $records = $query->limit(500)->get();

        return ApiResponse::success(
            AttendanceResource::collection($records),
            'Riwayat presensi berhasil dimuat.',
            200,
            ['total' => $records->count()]
        );
    }

    /**
     * Monthly recap for the app dashboard.
     */
    public function summary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        $summary = $this->attendanceService->monthlySummary(
            $request->user(),
            (int) ($validated['year'] ?? now()->year),
            (int) ($validated['month'] ?? now()->month),
        );

        return ApiResponse::success($summary, 'Rekap presensi berhasil dimuat.');
    }
}
