<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyJobRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'listing_id'       => ['required', 'integer', 'exists:listings,id'],
            'cv_id'            => ['required_without:cv_file', 'nullable', 'integer', 'exists:cvs,id'],
            'cv_file'          => ['required_without:cv_id', 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'cover_letter'     => ['nullable', 'string', 'max:3000'],
            'is_agreed_terms'  => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'listing_id.exists'        => 'Công việc không tồn tại.',
            'cv_id.exists'             => 'CV không hợp lệ.',
            'cv_file.mimes'            => 'CV phải là file PDF, DOC hoặc DOCX.',
            'cv_file.max'              => 'CV không được vượt quá 5MB.',
            'is_agreed_terms.accepted' => 'Bạn phải đồng ý với điều khoản để ứng tuyển.',
        ];
    }
}
