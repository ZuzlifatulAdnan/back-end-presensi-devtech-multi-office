<?php

namespace App\Http\Resources;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Company
 */
class CompanyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'address' => $this->address,
            'latitude' => is_numeric($this->latitude) ? (float) $this->latitude : null,
            'longitude' => is_numeric($this->longitude) ? (float) $this->longitude : null,
            'radius_km' => (float) $this->radius_km,
            'radius_meters' => $this->radiusInMeters(),
            'attendance_type' => $this->attendance_type,
            'is_active' => (bool) $this->is_active,
            'logo_url' => $this->logoUrl(),
        ];
    }
}
