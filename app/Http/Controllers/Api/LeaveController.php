<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Support\WorkdayCalculator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
    // Get all leave types
    public function getLeaveTypes()
    {
        $leaveTypes = LeaveType::all();

        return response()->json([
            'message' => 'Leave types retrieved successfully',
            'data' => $leaveTypes,
        ], 200);
    }

    // Get leave balance for current user
    public function getBalance(Request $request)
    {
        $year = $request->query('year', now()->year);
        $userId = $request->user()->id;

        $balances = LeaveBalance::where('employee_id', $userId)
            ->where('year', $year)
            ->with('leaveType')
            ->get();

        return response()->json([
            'message' => 'Leave balance retrieved successfully',
            'data' => $balances,
        ], 200);
    }

    // Get all leaves for current user
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $status = $request->query('status');

        $query = Leave::where('employee_id', $userId)
            ->with(['leaveType', 'approver']);

        if ($status) {
            $query->where('status', $status);
        }

        $leaves = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'Leaves retrieved successfully',
            'data' => $leaves,
        ], 200);
    }

    // Get leave by ID
    public function show($id)
    {
        $leave = Leave::with(['employee', 'leaveType', 'approver'])->findOrFail($id);

        return response()->json([
            'message' => 'Leave retrieved successfully',
            'data' => $leave,
        ], 200);
    }

    // Create leave request
    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
            'attachment' => 'nullable|file|max:2048', // Max 2MB
        ]);

        $userId = $request->user()->id;

        // Calculate total days excluding weekends and holidays
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $totalDays = WorkdayCalculator::countWorkdaysExcludingHolidays($startDate, $endDate);

        // Check leave balance
        $year = $startDate->year;
        $leaveBalance = LeaveBalance::where('employee_id', $userId)
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('year', $year)
            ->first();

        if (! $leaveBalance) {
            return response()->json([
                'message' => 'Leave balance not found for this leave type',
            ], 400);
        }

        if ($leaveBalance->remaining_days < $totalDays) {
            return response()->json([
                'message' => 'Insufficient leave balance',
                'remaining_days' => $leaveBalance->remaining_days,
                'requested_days' => $totalDays,
            ], 400);
        }

        $validated['employee_id'] = $userId;
        $validated['total_days'] = $totalDays;
        $validated['status'] = 'pending';

        // Handle attachment upload if provided
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('leave_attachments', 'public');
            $validated['attachment_url'] = $path;
        }

        $leave = Leave::create($validated);

        return response()->json([
            'message' => 'Leave request created successfully',
            'data' => $leave->load(['employee', 'leaveType']),
        ], 201);
    }

    // Update leave request (only if pending)
    public function update(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);

        // Only allow update if status is pending
        if ($leave->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot update leave request that has been processed',
            ], 400);
        }

        // Only allow owner to update
        if ($leave->employee_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'leave_type_id' => 'sometimes|exists:leave_types,id',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
            'attachment_url' => 'nullable|string',
        ]);

        // Recalculate total days if dates changed
        if (isset($validated['start_date']) || isset($validated['end_date'])) {
            $startDate = Carbon::parse($validated['start_date'] ?? $leave->start_date);
            $endDate = Carbon::parse($validated['end_date'] ?? $leave->end_date);
            $validated['total_days'] = WorkdayCalculator::countWorkdaysExcludingHolidays($startDate, $endDate);
        }

        $leave->update($validated);

        return response()->json([
            'message' => 'Leave request updated successfully',
            'data' => $leave->load(['employee', 'leaveType']),
        ], 200);
    }

    // Cancel leave request (only if pending)
    public function cancel($id, Request $request)
    {
        $leave = Leave::findOrFail($id);

        // Only allow cancel if status is pending
        if ($leave->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot cancel leave request that has been processed',
            ], 400);
        }

        // Only allow owner to cancel
        if ($leave->employee_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $leave->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Leave request cancelled successfully',
            'data' => $leave,
        ], 200);
    }

    public function approve($id)
    {
        try {
            DB::beginTransaction();

            $leave = Leave::findOrFail($id);

            if ($leave->status !== 'pending') {
                return response()->json([
                    'message' => 'Leave request has already been processed',
                ], 400);
            }

            // Recalculate total days to ensure consistency with holidays
            $totalDays = WorkdayCalculator::countWorkdaysExcludingHolidays(
                Carbon::parse($leave->start_date),
                Carbon::parse($leave->end_date)
            );

            // Update leave status
            $leave->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'total_days' => $totalDays,
            ]);

            // Update leave balance
            $year = $leave->start_date->year;
            $leaveBalance = LeaveBalance::where('employee_id', $leave->employee_id)
                ->where('leave_type_id', $leave->leave_type_id)
                ->where('year', $year)
                ->firstOrFail();

            $leaveBalance->update([
                'used_days' => $leaveBalance->used_days + $leave->total_days,
                'remaining_days' => $leaveBalance->remaining_days - $leave->total_days,
                'last_updated' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Leave request approved successfully',
                'data' => $leave->load(['employee', 'leaveType', 'approver']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to approve leave request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $leave = Leave::findOrFail($id);

        if ($leave->status !== 'pending') {
            return response()->json([
                'message' => 'Leave request has already been processed',
            ], 400);
        }

        $leave->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Leave request rejected successfully',
            'data' => $leave->load(['employee', 'leaveType', 'approver']),
        ]);
    }
}
