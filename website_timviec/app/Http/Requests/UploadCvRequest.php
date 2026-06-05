<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadCvRequest extends FormRequest
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
            'cv_file' => 'required|file|mimes:pdf,doc,docx|max:5120', // max 5MB
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cv_file.required' => 'Vui lòng chọn file CV để tải lên.',
            'cv_file.file'     => 'Dữ liệu tải lên phải là một tệp tin.',
            'cv_file.mimes'    => 'Chỉ hỗ trợ định dạng: PDF, DOC, DOCX.',
            'cv_file.max'      => 'Kích thước tệp tin tối đa là 5MB.',
        ];
    }
}
