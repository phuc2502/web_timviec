<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * VNPayService - Tạo URL thanh toán & xác thực callback.
 *
 * Cấu hình trong config/vnpay.php hoặc .env:
 *   VNPAY_TMN_CODE, VNPAY_HASH_SECRET, VNPAY_URL
 *
 * return_url được truyền dynamically theo từng loại thanh toán
 * (token callback ≠ subscription callback).
 */
class VNPayService
{
    private string $tmnCode;
    private string $hashSecret;
    private string $payUrl;

    public function __construct()
    {
        $this->tmnCode    = config('vnpay.tmn_code');
        $this->hashSecret = config('vnpay.hash_secret');
        $this->payUrl     = config('vnpay.url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
    }

    /**
     * Tạo URL thanh toán VNPay.
     *
     * @param  string      $txnRef      Mã giao dịch duy nhất
     * @param  int         $amount      Số tiền VNĐ (service tự x100)
     * @param  string      $orderInfo   Mô tả đơn hàng
     * @param  string      $returnUrl   URL callback sau thanh toán (per-type)
     * @param  string      $ipAddr      IP người dùng
     * @return string                   URL redirect sang VNPay
     */
    public function createPaymentUrl(
        string $txnRef,
        int    $amount,
        string $orderInfo,
        string $returnUrl,
        string $ipAddr = '127.0.0.1',
    ): string {
        $params = [
            'vnp_Version'    => '2.1.0',
            'vnp_Command'    => 'pay',
            'vnp_TmnCode'    => $this->tmnCode,
            'vnp_Amount'     => $amount * 100,       // VNPay yêu cầu x100
            'vnp_CurrCode'   => 'VND',
            'vnp_TxnRef'     => $txnRef,
            'vnp_OrderInfo'  => $orderInfo,
            'vnp_OrderType'  => 'other',
            'vnp_Locale'     => 'vn',
            'vnp_ReturnUrl'  => $returnUrl,
            'vnp_IpAddr'     => $ipAddr,
            'vnp_CreateDate' => now('Asia/Ho_Chi_Minh')->format('YmdHis'),
            'vnp_ExpireDate' => now('Asia/Ho_Chi_Minh')->addMinutes(15)->format('YmdHis'),
        ];

        ksort($params);

        $queryString = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $secureHash  = hash_hmac('sha512', $queryString, $this->hashSecret);

        return $this->payUrl . '?' . $queryString . '&vnp_SecureHash=' . $secureHash;
    }

    /**
     * Xác thực chữ ký callback từ VNPay.
     */
    public function verifySignature(array $data): bool
    {
        $receivedHash = $data['vnp_SecureHash'] ?? '';

        $filtered = collect($data)
            ->except(['vnp_SecureHash', 'vnp_SecureHashType'])
            ->toArray();

        ksort($filtered);

        $queryString  = http_build_query($filtered, '', '&', PHP_QUERY_RFC3986);
        $computedHash = hash_hmac('sha512', $queryString, $this->hashSecret);

        return hash_equals($computedHash, $receivedHash);
    }

    /**
     * Kiểm tra giao dịch có thành công không (mã 00).
     */
    public function isSuccess(array $data): bool
    {
        return ($data['vnp_ResponseCode'] ?? '') === '00'
            && ($data['vnp_TransactionStatus'] ?? '') === '00';
    }

    /**
     * Sinh mã txnRef duy nhất cho mỗi payment.
     */
    public function generateTxnRef(): string
    {
        return now('Asia/Ho_Chi_Minh')->format('YmdHis') . '_' . Str::upper(Str::random(6));
    }
}
