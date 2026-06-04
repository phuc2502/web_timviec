<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Xác nhận thanh toán Premium</title>
  <style>
    body { font-family: 'Helvetica Neue', Arial, sans-serif; background: #f4f5f5; margin: 0; padding: 0; }
    .wrapper { max-width: 580px; margin: 30px auto; }
    .header { background: linear-gradient(135deg, #212f3f, #2c3e50); padding: 28px 32px; border-radius: 12px 12px 0 0; text-align: center; }
    .header h1 { color: #fff; font-size: 20px; margin: 0; }
    .body { background: #fff; padding: 28px 32px; }
    .invoice { border: 1px solid #e8edf2; border-radius: 8px; overflow: hidden; margin: 20px 0; }
    .invoice-row { display: flex; justify-content: space-between; padding: 12px 16px; font-size: 14px; border-bottom: 1px solid #f0f0f0; }
    .invoice-row:last-child { border-bottom: none; font-weight: 700; background: #f8f9fa; }
    .badge { display: inline-block; background: #00b14f; color: #fff; padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; }
    .btn { display: inline-block; background: #00b14f; color: #fff; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 14px; }
    .footer { background: #f4f5f5; padding: 16px 32px; text-align: center; border-radius: 0 0 12px 12px; font-size: 12px; color: #6f7882; }
    p { line-height: 1.7; color: #444; font-size: 14px; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <div style="font-size:32px;margin-bottom:8px">👑</div>
      <h1>Thanh toán thành công!</h1>
      <p style="color:rgba(255,255,255,.7);font-size:13px;margin:6px 0 0">Chào mừng bạn đến với ITWorks Premium</p>
    </div>
    <div class="body">
      <p>Xin chào <strong>{{ auth()->user()->name ?? 'Quý khách' }}</strong>,</p>
      <p>Cảm ơn bạn đã tin tưởng và nâng cấp tài khoản <strong>ITWorks Premium</strong>. Giao dịch của bạn đã được xử lý thành công.</p>

      <div class="invoice">
        <div class="invoice-row">
          <span style="color:#6f7882">Gói đăng ký</span>
          <span class="badge">{{ ucfirst($plan) }}</span>
        </div>
        <div class="invoice-row">
          <span style="color:#6f7882">Ngày kích hoạt</span>
          <span>{{ now()->format('d/m/Y') }}</span>
        </div>
        <div class="invoice-row">
          <span style="color:#6f7882">Hết hạn vào</span>
          <span>{{ \Carbon\Carbon::parse($billingEnds)->format('d/m/Y') }}</span>
        </div>
        <div class="invoice-row">
          <span>Tổng thanh toán</span>
          <span style="color:#00b14f;font-size:16px">{{ $plan === 'monthly' ? '100.000 ₫' : '799.000 ₫' }}</span>
        </div>
      </div>

      <p>Với tài khoản Premium, bạn hiện có thể:</p>
      <ul style="line-height:2;color:#444;font-size:14px">
        <li>✅ Đăng tin tuyển dụng không giới hạn</li>
        <li>✅ Xem đầy đủ hồ sơ và CV ứng viên</li>
        <li>✅ Shortlist và liên hệ trực tiếp ứng viên</li>
        <li>✅ Ưu tiên hiển thị tin đăng</li>
      </ul>

      <div style="text-align:center;margin-top:24px">
        <a href="{{ url('/dashboard') }}" class="btn">Vào Dashboard ngay</a>
      </div>

      <p style="margin-top:24px">Trân trọng,<br><strong>Đội ngũ ITWorks 💚</strong></p>
    </div>
    <div class="footer">
      © {{ date('Y') }} ITWorks · Email xác nhận giao dịch<br>
      Mã giao dịch: <strong>#{{ time() }}</strong>
    </div>
  </div>
</body>
</html>
