<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><title>Xác nhận thanh toán</title></head>
<body>
<p>Xin chào <strong>{{ $payment->user->name }}</strong>,</p>
<p>Giao dịch thanh toán của bạn đã thành công:</p>
<ul>
    <li>Loại: {{ $payment->type === 'token' ? 'Mua lượt ứng tuyển' : 'Mua gói Premium' }}</li>
    <li>Số tiền: {{ number_format($payment->amount) }} VNĐ</li>
    @if($payment->type === 'token')
    <li>Số lượt cộng: {{ $payment->token_amount }} lượt</li>
    @else
    <li>Gói: {{ ucfirst($payment->plan) }}</li>
    @endif
    <li>Mã giao dịch: {{ $payment->vnpay_txn_ref }}</li>
</ul>
<p>Trân trọng,<br>Đội ngũ Tìm Việc</p>
</body>
</html>
