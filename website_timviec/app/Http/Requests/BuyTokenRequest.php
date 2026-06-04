<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BuyTokenRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    /** Các gói lượt ứng tuyển hỗ trợ (số lượt => giá VNĐ) */
    public const PACKAGES = [
        5  => 50_000,
        10 => 90_000,
        20 => 160_000,
    ];

    public function rules(): array
    {
        return [
            'package' => ['required', Rule::in(array_keys(self::PACKAGES))],
        ];
    }

    public function messages(): array
    {
        return ['package.in' => 'Gói lượt ứng tuyển không hợp lệ.'];
    }
}
