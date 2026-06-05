<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterEmployerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:100'],
            'email'        => ['required', 'email', 'max:255', 'unique:users,email'],
            'company_name' => ['required', 'string', 'max:255'],
            'password'     => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'terms'        => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Vui lòng nhập họ và tên.',
            'email.required'        => 'Vui lòng nhập địa chỉ email.',
            'email.unique'          => 'Email này đã được sử dụng. Vui lòng dùng email khác hoặc đăng nhập.',
            'company_name.required' => 'Vui lòng nhập tên công ty.',
            'password.min'          => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.letters'      => 'Mật khẩu phải chứa ít nhất 1 chữ cái.',
            'password.numbers'      => 'Mật khẩu phải chứa ít nhất 1 chữ số.',
            'password.confirmed'    => 'Xác nhận mật khẩu không khớp.',
            'terms.accepted'        => 'Bạn phải đồng ý với điều khoản sử dụng.',
        ];
    }
}
