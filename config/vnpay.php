<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VNPay Configuration
    |--------------------------------------------------------------------------
    | Lấy TMN Code và Hash Secret từ cổng thông tin VNPay Merchant.
    | https://sandbox.vnpayment.vn/merchantv2/
    |
    | return_url được truyền dynamically theo từng loại thanh toán:
    |   - Mua lượt ứng tuyển → route('payment.token.callback')
    |   - Mua gói Premium    → route('payment.subscription.callback')
    */

    'tmn_code'    => env('VNPAY_TMN_CODE', 'DEMO1234'),
    'hash_secret' => env('VNPAY_HASH_SECRET', 'your-secret-key'),

    // Môi trường sandbox (thử nghiệm)
    'url' => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
];
