<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveRequest extends FormRequest
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
            'leave_type_id' => ['sometimes', 'integer', 'exists:leave_types,id'],
            'start_date' => ['sometimes', 'date_format:Y-m-d'],
            'end_date' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'reason' => ['sometimes', 'string', 'min:5', 'max:1000'],
            'attachment' => [
                'nullable',
                'file',
                'mimes:'.StoreLeaveRequest::ATTACHMENT_MIMES,
                'max:'.StoreLeaveRequest::ATTACHMENT_MAX_KB,
            ],
            'remove_attachment' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'attachment.mimes' => 'Lampiran harus berformat JPG, PNG, WEBP, atau PDF.',
            'attachment.max' => 'Ukuran lampiran maksimal 5 MB.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('remove_attachment')) {
            $this->merge([
                'remove_attachment' => filter_var($this->input('remove_attachment'), FILTER_VALIDATE_BOOL),
            ]);
        }
    }
}
