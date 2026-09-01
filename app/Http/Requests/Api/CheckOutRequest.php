<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CheckOutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'address' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'is_mock_location' => ['nullable', 'boolean'],
            'device_info' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'latitude.required' => 'Koordinat latitude wajib dikirim.',
            'latitude.numeric' => 'Koordinat latitude tidak valid.',
            'latitude.between' => 'Koordinat latitude tidak valid.',
            'longitude.required' => 'Koordinat longitude wajib dikirim.',
            'longitude.numeric' => 'Koordinat longitude tidak valid.',
            'longitude.between' => 'Koordinat longitude tidak valid.',
            'photo.image' => 'Bukti absen harus berupa gambar.',
            'photo.max' => 'Ukuran foto maksimal 4 MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_mock_location')) {
            $this->merge([
                'is_mock_location' => filter_var($this->input('is_mock_location'), FILTER_VALIDATE_BOOL),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return [
            'latitude' => (float) $this->validated('latitude'),
            'longitude' => (float) $this->validated('longitude'),
            'notes' => $this->validated('notes'),
            'address' => $this->validated('address'),
            'photo' => $this->file('photo'),
            'is_mock_location' => (bool) $this->validated('is_mock_location', false),
            'device_info' => $this->validated('device_info'),
        ];
    }
}
