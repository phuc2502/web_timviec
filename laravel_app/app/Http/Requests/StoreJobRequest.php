<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->user_type === 'employer';
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string|max:255',
            'job_type' => 'required|in:full_time,part_time,contract,internship,freelance',
            'category_id' => 'required|exists:categories,id',
            'level' => 'nullable|in:intern,junior,middle,senior,manager,director',

            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'is_negotiable' => 'boolean',
            'hide_salary' => 'boolean',

            'application_close_date' => 'required|date|after:today',
            'start_date' => 'nullable|date',
            'vacancy_count' => 'nullable|integer|min:1',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',

            'jd_file' => 'nullable|file|mimes:pdf,docx|max:5120', // 5MB

            'publish_mode' => 'required|in:immediate,scheduled,draft',
            'scheduled_at' => 'required_if:publish_mode,scheduled|nullable|date|after:+5 minutes',

            'skills' => 'nullable|array|max:20',
            'skills.*' => 'integer|exists:skills,id',
        ];
    }

    /**
     * Custom validation: salary requirement.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Nếu không chọn "thỏa thuận" thì phải nhập khoảng lương
            if (!$this->boolean('is_negotiable') &&
                is_null($this->salary_min) &&
                is_null($this->salary_max)) {
                $validator->errors()->add(
                    'salary',
                    'Vui lòng nhập khoảng lương hoặc chọn "Thỏa thuận".'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề công việc là bắt buộc.',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'description.required' => 'Mô tả công việc là bắt buộc.',
            'address.required' => 'Địa chỉ làm việc là bắt buộc.',
            'job_type.required' => 'Loại công việc là bắt buộc.',
            'job_type.in' => 'Loại công việc không hợp lệ.',
            'category_id.required' => 'Ngành nghề là bắt buộc.',
            'category_id.exists' => 'Ngành nghề không tồn tại.',
            'salary_min.min' => 'Lương tối thiểu không được âm.',
            'salary_max.gte' => 'Lương tối đa phải lớn hơn hoặc bằng lương tối thiểu.',
            'application_close_date.required' => 'Ngày đóng nhận hồ sơ là bắt buộc.',
            'application_close_date.after' => 'Ngày đóng nhận hồ sơ phải sau ngày hôm nay.',
            'jd_file.mimes' => 'File JD chỉ chấp nhận định dạng PDF hoặc DOCX.',
            'jd_file.max' => 'File JD không được vượt quá 5MB.',
            'publish_mode.required' => 'Chế độ đăng tin là bắt buộc.',
            'scheduled_at.required_if' => 'Thời gian lên lịch là bắt buộc khi chọn chế độ "Lên lịch".',
            'scheduled_at.after' => 'Thời gian lên lịch phải sau thời điểm hiện tại ít nhất 5 phút.',
            'skills.max' => 'Tối đa 20 kỹ năng.',
        ];
    }
}
