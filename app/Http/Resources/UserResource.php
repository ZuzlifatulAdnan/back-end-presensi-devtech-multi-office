<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'position' => $this->position,
            'department' => $this->department,
            'jabatan_id' => $this->jabatan_id,
            'departemen_id' => $this->departemen_id,
            'shift_kerja_id' => $this->shift_kerja_id,
            'company_id' => $this->company_id,
            'work_mode' => $this->work_mode,
            'image_url' => $this->imageUrl(),
            'face_embedding' => $this->face_embedding,
            'fcm_token' => $this->fcm_token,
            'jabatan' => $this->whenLoaded('jabatan', fn () => $this->jabatan ? [
                'id' => $this->jabatan->id,
                'name' => $this->jabatan->name,
            ] : null),
            'departemen' => $this->whenLoaded('departemen', fn () => $this->departemen ? [
                'id' => $this->departemen->id,
                'name' => $this->departemen->name,
            ] : null),
            'shift_kerja' => $this->whenLoaded('shiftKerja', fn () => $this->shiftKerja ? [
                'id' => $this->shiftKerja->id,
                'name' => $this->shiftKerja->name,
                'start_time' => $this->shiftKerja->start_time,
                'end_time' => $this->shiftKerja->end_time,
            ] : null),
            'company' => $this->whenLoaded('company', fn () => $this->company
                ? new CompanyResource($this->company)
                : null),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function imageUrl(): ?string
    {
        if (blank($this->image_url)) {
            return null;
        }

        if (str_starts_with($this->image_url, 'http://') || str_starts_with($this->image_url, 'https://')) {
            return $this->image_url;
        }

        return Storage::disk('public')->url($this->image_url);
    }
}
