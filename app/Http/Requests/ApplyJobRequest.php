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
            'fullname'         => ['required', 'string', 'min:2', 'max:100'],
            'phone'            => ['required', 'string', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            'email'            => ['required', 'email'],
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
            'fullname.required'        => 'Vui lòng nhập họ và tên của bạn.',
            'fullname.min'             => 'Họ và tên phải có ít nhất 2 ký tự.',
            'phone.required'           => 'Vui lòng nhập số điện thoại của bạn.',
            'phone.regex'              => 'Số điện thoại không hợp lệ. Vui lòng nhập đúng định dạng (10–11 chữ số).',
            'email.required'           => 'Vui lòng nhập địa chỉ email của bạn.',
            'email.email'              => 'Địa chỉ email không hợp lệ. Vui lòng kiểm tra lại.',
            'cv_id.exists'             => 'CV không hợp lệ. Vui lòng chọn lại.',
            'cv_file.required_without' => 'Vui lòng tải lên file CV của bạn.',
            'cv_file.mimes'            => 'File CV phải có định dạng PDF, DOC hoặc DOCX.',
            'cv_file.max'              => 'File CV không được vượt quá 5MB.',
            'is_agreed_terms.required' => 'Vui lòng đồng ý với điều khoản dịch vụ để tiếp tục.',
            'is_agreed_terms.accepted' => 'Vui lòng đồng ý với điều khoản dịch vụ để tiếp tục.',
        ];
    }
}
