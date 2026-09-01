<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreLeaveRequest;
use App\Http\Requests\Api\UpdateLeaveRequest;
use App\Http\Resources\LeaveResource;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Services\LeaveService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeaveController extends Controller
{
    public function __construct(private readonly LeaveService $leaveService) {}

    /**
     * Types of izin / cuti the employee can pick from.
     */
    public function getLeaveTypes(): JsonResponse
    {
        $leaveTypes = LeaveType::query()->orderBy('name')->get([
            'id', 'name', 'quota_days', 'is_paid',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jenis izin/cuti berhasil dimuat.',
            'data' => $leaveTypes,
        ]);
    }

    public function getBalance(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        $balances = LeaveBalance::query()
            ->with('leaveType:id,name,is_paid')
            ->where('employee_id', $request->user()->id)
            ->where('year', (int) ($validated['year'] ?? now()->year))
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Sisa kuota berhasil dimuat.',
            'data' => $balances,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(Leave::STATUSES)],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Leave::query()
            ->with(['leaveType', 'approver'])
            ->forEmployee($request->user()->id)
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($validated['year'] ?? null, fn ($q, $year) => $q->whereYear('start_date', $year))
            ->orderByDesc('created_at');

        if ($request->hasAny(['page', 'per_page'])) {
            return ApiResponse::success(
                LeaveResource::collection($query->paginate((int) ($validated['per_page'] ?? 25))),
                'Daftar pengajuan berhasil dimuat.'
            );
        }

        $leaves = $query->limit(300)->get();

        return ApiResponse::success(
            LeaveResource::collection($leaves),
            'Daftar pengajuan berhasil dimuat.',
            200,
            ['total' => $leaves->count()]
        );
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $leave = Leave::query()->with(['leaveType', 'approver', 'employee'])->find($id);

        if (! $leave) {
            return ApiResponse::error('Pengajuan tidak ditemukan.', 404);
        }

        if (! $this->canAccess($request, $leave)) {
            return ApiResponse::error('Anda tidak berhak mengakses pengajuan ini.', 403);
        }

        return ApiResponse::success(new LeaveResource($leave), 'Detail pengajuan berhasil dimuat.');
    }

    /**
     * Submit an izin / cuti request, optionally with a supporting document.
     */
    public function store(StoreLeaveRequest $request): JsonResponse
    {
        $leave = $this->leaveService->create(
            $request->user(),
            $request->validated(),
            $request->file('attachment')
        );

        return ApiResponse::success(
            new LeaveResource($leave->load(['leaveType', 'employee'])),
            'Pengajuan berhasil dikirim.',
            201
        );
    }

    public function update(UpdateLeaveRequest $request, int $id): JsonResponse
    {
        $leave = Leave::query()->with('employee')->find($id);

        if (! $leave) {
            return ApiResponse::error('Pengajuan tidak ditemukan.', 404);
        }

        if ($leave->employee_id !== $request->user()->id) {
            return ApiResponse::error('Anda hanya dapat mengubah pengajuan milik sendiri.', 403);
        }

        $leave = $this->leaveService->update(
            $leave,
            $request->validated(),
            $request->file('attachment'),
            (bool) $request->boolean('remove_attachment')
        );

        return ApiResponse::success(
            new LeaveResource($leave->load(['leaveType', 'employee'])),
            'Pengajuan berhasil diperbarui.'
        );
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $leave = Leave::query()->find($id);

        if (! $leave) {
            return ApiResponse::error('Pengajuan tidak ditemukan.', 404);
        }

        if ($leave->employee_id !== $request->user()->id) {
            return ApiResponse::error('Anda hanya dapat membatalkan pengajuan milik sendiri.', 403);
        }

        $leave = $this->leaveService->cancel($leave);

        return ApiResponse::success(
            new LeaveResource($leave->load('leaveType')),
            'Pengajuan berhasil dibatalkan.'
        );
    }

    /**
     * Approvals are performed by an approver role; the admin panel uses the same
     * service so balances stay consistent.
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        if (! $this->isApprover($request)) {
            return ApiResponse::error('Anda tidak berhak menyetujui pengajuan.', 403);
        }

        $leave = Leave::query()->find($id);

        if (! $leave) {
            return ApiResponse::error('Pengajuan tidak ditemukan.', 404);
        }

        $leave = $this->leaveService->approve($leave, $request->user());

        return ApiResponse::success(
            new LeaveResource($leave->load(['leaveType', 'employee', 'approver'])),
            'Pengajuan disetujui.'
        );
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        if (! $this->isApprover($request)) {
            return ApiResponse::error('Anda tidak berhak menolak pengajuan.', 403);
        }

        $validated = $request->validate([
            'notes' => ['required', 'string', 'max:1000'],
        ], [
            'notes.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $leave = Leave::query()->find($id);

        if (! $leave) {
            return ApiResponse::error('Pengajuan tidak ditemukan.', 404);
        }

        $leave = $this->leaveService->reject($leave, $request->user(), $validated['notes']);

        return ApiResponse::success(
            new LeaveResource($leave->load(['leaveType', 'employee', 'approver'])),
            'Pengajuan ditolak.'
        );
    }

    private function canAccess(Request $request, Leave $leave): bool
    {
        return $leave->employee_id === $request->user()->id || $this->isApprover($request);
    }

    private function isApprover(Request $request): bool
    {
        return in_array($request->user()->role, ['admin', 'manager', 'hr'], true);
    }
}
