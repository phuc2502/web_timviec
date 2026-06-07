<?php

namespace App\Http\Requests;

use App\Models\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(array_keys(Application::STATUS_LABELS))],
            // Bắt buộc nhập ngày giờ phỏng vấn khi chọn status = interviewing
            'interview_scheduled_at' => [
                Rule::requiredIf($this->input('status') === 'interviewing'),
                'nullable',
                'date',
                'after:now',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required'                    => 'Vui lòng chọn trạng thái.',
            'status.in'                          => 'Trạng thái không hợp lệ.',
            'interview_scheduled_at.required'    => 'Vui lòng chọn ngày/giờ phỏng vấn dự kiến.',
            'interview_scheduled_at.after'       => 'Ngày phỏng vấn phải sau thời điểm hiện tại.',
        ];
    }
}
