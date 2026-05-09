<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'image_url' => $this->image_url ? asset('storage/'.$this->image_url) : null,
            'face_embedding' => $this->face_embedding,
            'fcm_token' => $this->fcm_token,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
