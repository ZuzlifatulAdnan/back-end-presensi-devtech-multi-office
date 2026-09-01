<?php

namespace App\Http\Resources;

use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Leave
 */
class LeaveResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'leave_type_id' => $this->leave_type_id,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'total_days' => (int) $this->total_days,
            'reason' => $this->reason,
            'status' => $this->status,
            'status_label' => match ($this->status) {
                Leave::STATUS_PENDING => 'Menunggu',
                Leave::STATUS_APPROVED => 'Disetujui',
                Leave::STATUS_REJECTED => 'Ditolak',
                Leave::STATUS_CANCELLED => 'Dibatalkan',
                default => ucfirst((string) $this->status),
            },
            'can_edit' => $this->isPending(),
            'can_cancel' => $this->isPending(),
            'attachment' => $this->attachment_url ? [
                'url' => $this->attachmentUrl(),
                'name' => $this->attachment_name,
                'mime' => $this->attachment_mime,
                'size' => $this->attachment_size,
            ] : null,
            'notes' => $this->notes,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'leave_type' => $this->whenLoaded('leaveType', fn () => $this->leaveType ? [
                'id' => $this->leaveType->id,
                'name' => $this->leaveType->name,
                'is_paid' => (bool) $this->leaveType->is_paid,
            ] : null),
            'employee' => $this->whenLoaded('employee', fn () => $this->employee ? [
                'id' => $this->employee->id,
                'name' => $this->employee->name,
            ] : null),
            'approver' => $this->whenLoaded('approver', fn () => $this->approver ? [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
