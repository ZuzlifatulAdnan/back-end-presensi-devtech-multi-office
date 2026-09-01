<?php

namespace App\Services;

use App\Exceptions\LeaveException;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use App\Support\WorkdayCalculator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Owns every mutation of an izin / cuti request so the API and the admin panel
 * apply exactly the same rules to leave balances.
 */
class LeaveService
{
    public const ATTACHMENT_DIRECTORY = 'leave-attachments';

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws LeaveException
     */
    public function create(User $employee, array $data, ?UploadedFile $attachment = null): Leave
    {
        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = Carbon::parse($data['end_date'])->startOfDay();

        $this->assertNoOverlap($employee, $startDate, $endDate);

        $totalDays = WorkdayCalculator::countWorkdaysExcludingHolidays($startDate, $endDate);

        if ($totalDays < 1) {
            throw new LeaveException('Rentang tanggal yang dipilih tidak mengandung hari kerja.', 422);
        }

        $leaveType = LeaveType::query()->findOrFail($data['leave_type_id']);
        $balance = $this->resolveBalance($employee, $leaveType, $startDate->year);

        if ($balance->remaining_days < $totalDays) {
            throw new LeaveException(
                'Sisa kuota '.$leaveType->name.' tidak mencukupi.',
                422,
                [
                    'remaining_days' => (int) $balance->remaining_days,
                    'requested_days' => $totalDays,
                ]
            );
        }

        $stored = $attachment ? $this->storeAttachment($attachment, $employee) : null;

        try {
            return Leave::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'total_days' => $totalDays,
                'reason' => $data['reason'] ?? null,
                'attachment_url' => $stored['path'] ?? null,
                'attachment_name' => $stored['name'] ?? null,
                'attachment_mime' => $stored['mime'] ?? null,
                'attachment_size' => $stored['size'] ?? null,
                'status' => Leave::STATUS_PENDING,
            ]);
        } catch (\Throwable $exception) {
            if (isset($stored['path'])) {
                Storage::disk('public')->delete($stored['path']);
            }

            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws LeaveException
     */
    public function update(Leave $leave, array $data, ?UploadedFile $attachment = null, bool $removeAttachment = false): Leave
    {
        if (! $leave->isPending()) {
            throw new LeaveException('Pengajuan yang sudah diproses tidak dapat diubah.', 409);
        }

        $startDate = Carbon::parse($data['start_date'] ?? $leave->start_date)->startOfDay();
        $endDate = Carbon::parse($data['end_date'] ?? $leave->end_date)->startOfDay();

        if ($endDate->lessThan($startDate)) {
            throw new LeaveException('Tanggal selesai tidak boleh sebelum tanggal mulai.', 422);
        }

        $this->assertNoOverlap($leave->employee, $startDate, $endDate, $leave->id);

        $totalDays = WorkdayCalculator::countWorkdaysExcludingHolidays($startDate, $endDate);

        if ($totalDays < 1) {
            throw new LeaveException('Rentang tanggal yang dipilih tidak mengandung hari kerja.', 422);
        }

        $leaveTypeId = $data['leave_type_id'] ?? $leave->leave_type_id;
        $leaveType = LeaveType::query()->findOrFail($leaveTypeId);
        $balance = $this->resolveBalance($leave->employee, $leaveType, $startDate->year);

        if ($balance->remaining_days < $totalDays) {
            throw new LeaveException(
                'Sisa kuota '.$leaveType->name.' tidak mencukupi.',
                422,
                [
                    'remaining_days' => (int) $balance->remaining_days,
                    'requested_days' => $totalDays,
                ]
            );
        }

        $previousAttachment = $leave->attachment_url;

        $leave->fill([
            'leave_type_id' => $leaveType->id,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_days' => $totalDays,
            'reason' => $data['reason'] ?? $leave->reason,
        ]);

        if ($attachment) {
            $stored = $this->storeAttachment($attachment, $leave->employee);

            $leave->fill([
                'attachment_url' => $stored['path'],
                'attachment_name' => $stored['name'],
                'attachment_mime' => $stored['mime'],
                'attachment_size' => $stored['size'],
            ]);
        } elseif ($removeAttachment) {
            $leave->fill([
                'attachment_url' => null,
                'attachment_name' => null,
                'attachment_mime' => null,
                'attachment_size' => null,
            ]);
        }

        $leave->save();

        if ($previousAttachment && $previousAttachment !== $leave->attachment_url) {
            Storage::disk('public')->delete($previousAttachment);
        }

        return $leave;
    }

    /**
     * @throws LeaveException
     */
    public function cancel(Leave $leave): Leave
    {
        if (! $leave->isPending()) {
            throw new LeaveException('Pengajuan yang sudah diproses tidak dapat dibatalkan.', 409);
        }

        $leave->update([
            'status' => Leave::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        return $leave;
    }

    /**
     * Approve and deduct the balance atomically; the balance row is locked so
     * two approvers cannot spend the same remaining days.
     *
     * @throws LeaveException
     */
    public function approve(Leave $leave, User $approver): Leave
    {
        return DB::transaction(function () use ($leave, $approver): Leave {
            $leave->refresh();

            if (! $leave->isPending()) {
                throw new LeaveException('Pengajuan sudah diproses sebelumnya.', 409);
            }

            $totalDays = WorkdayCalculator::countWorkdaysExcludingHolidays(
                Carbon::parse($leave->start_date),
                Carbon::parse($leave->end_date)
            );

            $balance = LeaveBalance::query()
                ->where('employee_id', $leave->employee_id)
                ->where('leave_type_id', $leave->leave_type_id)
                ->where('year', Carbon::parse($leave->start_date)->year)
                ->lockForUpdate()
                ->first();

            if (! $balance) {
                throw new LeaveException('Kuota cuti untuk jenis dan tahun ini belum tersedia.', 422);
            }

            if ($balance->remaining_days < $totalDays) {
                throw new LeaveException(
                    'Sisa kuota tidak mencukupi. Dibutuhkan '.$totalDays.' hari, tersedia '.$balance->remaining_days.' hari.',
                    422
                );
            }

            $balance->update([
                'used_days' => $balance->used_days + $totalDays,
                'remaining_days' => $balance->remaining_days - $totalDays,
                'last_updated' => now(),
            ]);

            $leave->update([
                'status' => Leave::STATUS_APPROVED,
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'total_days' => $totalDays,
            ]);

            return $leave;
        });
    }

    /**
     * @throws LeaveException
     */
    public function reject(Leave $leave, User $approver, ?string $notes = null): Leave
    {
        if (! $leave->isPending()) {
            throw new LeaveException('Pengajuan sudah diproses sebelumnya.', 409);
        }

        $leave->update([
            'status' => Leave::STATUS_REJECTED,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'notes' => $notes,
        ]);

        return $leave;
    }

    /**
     * Restore the deducted days when an approved request is revoked.
     */
    public function revokeApproval(Leave $leave): Leave
    {
        return DB::transaction(function () use ($leave): Leave {
            $leave->refresh();

            if ($leave->status !== Leave::STATUS_APPROVED) {
                throw new LeaveException('Hanya pengajuan yang sudah disetujui yang dapat dibatalkan.', 409);
            }

            $balance = LeaveBalance::query()
                ->where('employee_id', $leave->employee_id)
                ->where('leave_type_id', $leave->leave_type_id)
                ->where('year', Carbon::parse($leave->start_date)->year)
                ->lockForUpdate()
                ->first();

            if ($balance) {
                $balance->update([
                    'used_days' => max(0, $balance->used_days - $leave->total_days),
                    'remaining_days' => $balance->remaining_days + $leave->total_days,
                    'last_updated' => now(),
                ]);
            }

            $leave->update([
                'status' => Leave::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            return $leave;
        });
    }

    /**
     * Look up the balance for a year, creating it from the leave type quota when
     * an employee was added after the type was configured.
     */
    private function resolveBalance(User $employee, LeaveType $leaveType, int $year): LeaveBalance
    {
        return LeaveBalance::query()->firstOrCreate(
            [
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'year' => $year,
            ],
            [
                'quota_days' => $leaveType->quota_days,
                'used_days' => 0,
                'remaining_days' => $leaveType->quota_days,
                'carry_over_days' => 0,
                'last_updated' => now(),
            ]
        );
    }

    /**
     * @throws LeaveException
     */
    private function assertNoOverlap(User $employee, Carbon $startDate, Carbon $endDate, ?int $ignoreLeaveId = null): void
    {
        $exists = Leave::query()
            ->forEmployee($employee->id)
            ->blocking()
            ->overlapping($startDate->toDateString(), $endDate->toDateString())
            ->when($ignoreLeaveId, fn ($query) => $query->where('id', '!=', $ignoreLeaveId))
            ->exists();

        if ($exists) {
            throw new LeaveException('Anda sudah memiliki pengajuan pada rentang tanggal tersebut.', 409);
        }
    }

    /**
     * @return array{path: string, name: string, mime: string|null, size: int}
     */
    private function storeAttachment(UploadedFile $file, User $employee): array
    {
        $filename = sprintf(
            '%d-%s.%s',
            $employee->id,
            now()->format('YmdHis'),
            $file->extension() ?: $file->getClientOriginalExtension() ?: 'bin'
        );

        return [
            'path' => $file->storeAs(self::ATTACHMENT_DIRECTORY.'/'.now()->format('Y/m'), $filename, 'public'),
            'name' => $file->getClientOriginalName(),
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize() ?: 0,
        ];
    }
}
