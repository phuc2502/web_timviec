<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><title>Xác nhận thanh toán</title></head>
<body>
<p>Xin chào <strong><?php echo e($payment->user->name); ?></strong>,</p>
<p>Giao dịch thanh toán của bạn đã thành công:</p>
<ul>
    <li>Loại: <?php echo e($payment->type === 'token' ? 'Mua lượt ứng tuyển' : 'Mua gói Premium'); ?></li>
    <li>Số tiền: <?php echo e(number_format($payment->amount)); ?> VNĐ</li>
    <?php if($payment->type === 'token'): ?>
    <li>Số lượt cộng: <?php echo e($payment->token_amount); ?> lượt</li>
    <?php else: ?>
    <li>Gói: <?php echo e(ucfirst($payment->plan)); ?></li>
    <?php endif; ?>
    <li>Mã giao dịch: <?php echo e($payment->vnpay_txn_ref); ?></li>
</ul>
<p>Trân trọng,<br>Đội ngũ Tìm Việc</p>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\web_timviec_final\website_timviec\resources\views/emails/payment-confirmation.blade.php ENDPATH**/ ?>