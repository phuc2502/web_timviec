<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'address' => 'sometimes|string|max:255',
            'job_type' => 'sometimes|in:full_time,part_time,contract,internship,freelance',
            'category_id' => 'sometimes|exists:categories,id',
            'level' => 'nullable|in:intern,junior,middle,senior,manager,director',

            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'is_negotiable' => 'boolean',
            'hide_salary' => 'boolean',

            'application_close_date' => 'sometimes|date|after:today',
            'start_date' => 'nullable|date',
            'vacancy_count' => 'nullable|integer|min:1',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',

            'jd_file' => 'nullable|file|mimes:pdf,docx|max:5120',

            'skills' => 'nullable|array|max:20',
            'skills.*' => 'integer|exists:skills,id',
        ];
    }

    /**
     * Kiểm tra xem có thay đổi trường quan trọng (title, description) không.
     * Nếu có → listing active sẽ cần re-moderation.
     */
    public function requiresReModeration(): bool
    {
        return $this->has('title') || $this->has('description');
    }

    public function messages(): array
    {
        return [
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'salary_max.gte' => 'Lương tối đa phải lớn hơn hoặc bằng lương tối thiểu.',
            'application_close_date.after' => 'Ngày đóng nhận hồ sơ phải sau ngày hôm nay.',
            'jd_file.mimes' => 'File JD chỉ chấp nhận định dạng PDF hoặc DOCX.',
            'jd_file.max' => 'File JD không được vượt quá 5MB.',
            'skills.max' => 'Tối đa 20 kỹ năng.',
        ];
    }
}
