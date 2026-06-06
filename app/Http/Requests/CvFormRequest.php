<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CvFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Middleware đã xử lý auth + employee
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Thông tin cá nhân
            'full_name' => 'required|string|max:255',
            'phone'     => ['nullable', 'string', 'max:20', 'regex:/^[\d\s\+\-\(\)]+$/'],
            'email'     => 'nullable|email|max:255',
            'address'   => 'nullable|string|max:255',
            'objective' => 'nullable|string|max:5000',

            // Ảnh đại diện
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // max 2MB

            // Template — lấy từ config tập trung
            'template' => ['required', Rule::in(config('cv.templates'))],

            // Kỹ năng
            'skills_text' => 'nullable|string|max:5000',

            // ─── Repeatable sections ────────────────────────────────
            // Education
            'education'              => 'nullable|array',
            'education.*.school'     => 'nullable|string|max:255',
            'education.*.degree'     => 'nullable|string|max:255',
            'education.*.year_start' => 'nullable|string|max:10',
            'education.*.year_end'   => 'nullable|string|max:10',

            // Experience
            'experience'              => 'nullable|array',
            'experience.*.company'    => 'nullable|string|max:255',
            'experience.*.role'       => 'nullable|string|max:255',
            'experience.*.year_start' => 'nullable|string|max:10',
            'experience.*.year_end'   => 'nullable|string|max:10',
            'experience.*.desc'       => 'nullable|string|max:2000',

            // Projects
            'projects'          => 'nullable|array',
            'projects.*.name'   => 'nullable|string|max:255',
            'projects.*.tech'   => 'nullable|string|max:255',
            'projects.*.url'    => 'nullable|url|max:500',
            'projects.*.desc'   => 'nullable|string|max:2000',

            // Certifications
            'certifications'          => 'nullable|array',
            'certifications.*.name'   => 'nullable|string|max:255',
            'certifications.*.issuer' => 'nullable|string|max:255',
            'certifications.*.year'   => 'nullable|string|max:10',

            // Languages
            'languages'          => 'nullable|array',
            'languages.*.lang'   => 'nullable|string|max:100',
            'languages.*.level'  => 'nullable|string|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Họ và tên là bắt buộc.',
            'full_name.max'      => 'Họ và tên không được vượt quá 255 ký tự.',
            'phone.regex'        => 'Số điện thoại chỉ được chứa số, dấu cách, +, -, (, ).',
            'phone.max'          => 'Số điện thoại không được vượt quá 20 ký tự.',
            'email.email'        => 'Email không đúng định dạng.',
            'photo.image'        => 'Ảnh đại diện phải là hình ảnh.',
            'photo.mimes'        => 'Ảnh đại diện chỉ hỗ trợ định dạng: JPEG, PNG, JPG, WEBP.',
            'photo.max'          => 'Kích thước ảnh đại diện tối đa là 2MB.',
            'template.required'  => 'Vui lòng chọn template CV.',
            'template.in'        => 'Template không hợp lệ.',
        ];
    }
}
