<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->is_admin;
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => 'required|string|min:10',
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Lý do từ chối là bắt buộc.',
            'rejection_reason.min' => 'Lý do từ chối phải có ít nhất 10 ký tự.',
        ];
    }
}
