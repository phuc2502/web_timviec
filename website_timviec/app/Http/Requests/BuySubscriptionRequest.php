<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BuySubscriptionRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    /** Các gói Premium (plan => giá VNĐ) */
    public const PLANS = [
        'monthly' => 299_000,
        'yearly'  => 2_990_000,
    ];

    public function rules(): array
    {
        return [
            'plan' => ['required', Rule::in(array_keys(self::PLANS))],
        ];
    }

    public function messages(): array
    {
        return ['plan.in' => 'Gói dịch vụ không hợp lệ.'];
    }
}
