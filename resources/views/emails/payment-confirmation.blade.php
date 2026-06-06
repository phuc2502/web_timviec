<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Xác nhận thanh toán thành công</title>
  <style>
    body { margin:0; padding:0; background:#f4f7f9; font-family:'Helvetica Neue',Arial,sans-serif; color:#333; }
    .wrapper { max-width:600px; margin:40px auto; }
    .header { border-radius:12px 12px 0 0; padding:36px 40px; text-align:center; }
    .header-token { background:linear-gradient(135deg,#00B14F,#009140); }
    .header-subscription { background:linear-gradient(135deg,#1B2B4B,#2d4a7a); }
    .header .icon { font-size:40px; margin-bottom:10px; }
    .header h1 { margin:0; color:#fff; font-size:22px; font-weight:700; }
    .header p  { margin:6px 0 0; color:rgba(255,255,255,0.85); font-size:14px; }
    .body { background:#fff; padding:36px 40px; }
    .greeting { font-size:17px; font-weight:600; color:#1a1a1a; margin-bottom:16px; }
    .text { font-size:14px; line-height:1.75; color:#444; margin-bottom:16px; }
    .success-badge { display:inline-flex; align-items:center; gap:8px; background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:10px 16px; font-size:14px; font-weight:600; color:#15803d; margin-bottom:20px; }
    .invoice { border:1px solid #e8edf2; border-radius:10px; overflow:hidden; margin:20px 0; }
    .invoice-row { display:flex; justify-content:space-between; align-items:center; padding:13px 18px; font-size:14px; border-bottom:1px solid #f0f0f0; }
    .invoice-row:last-child { border-bottom:none; font-weight:700; background:#f8f9fa; }
    .invoice-row .label { color:#6f7882; }
    .invoice-row .value { color:#1a1a1a; font-weight:600; }
    .badge-token { background:#00B14F; color:#fff; padding:3px 12px; border-radius:20px; font-size:12px; font-weight:700; }
    .badge-premium { background:#1B2B4B; color:#fff; padding:3px 12px; border-radius:20px; font-size:12px; font-weight:700; }
    .amount { color:#00B14F; font-size:18px; font-weight:700; }
    .features { background:#f8f9fa; border-radius:10px; padding:20px 24px; margin:20px 0; }
    .features h3 { margin:0 0 12px; font-size:14px; color:#333; font-weight:700; }
    .features ul { margin:0; padding:0; list-style:none; }
    .features ul li { font-size:13px; color:#444; line-height:2; }
    .cta { text-align:center; margin:28px 0 16px; }
    .cta a { background:#00B14F; color:#fff; padding:13px 32px; border-radius:8px; text-decoration:none; font-size:15px; font-weight:600; display:inline-block; }
    .cta-dark a { background:#1B2B4B; }
    .divider { border:none; border-top:1px solid #eee; margin:24px 0; }
    .txn-ref { text-align:center; font-size:12px; color:#aaa; margin-top:8px; }
    .footer { background:#f4f7f9; border-radius:0 0 12px 12px; padding:24px 40px; text-align:center; }
    .footer p { font-size:12px; color:#999; margin:4px 0; }
    .footer .brand { color:#00B14F; font-weight:700; }
  </style>
</head>
<body>
<div class="wrapper">

  {{-- Header — màu khác nhau theo loại giao dịch --}}
  @if($payment->type === 'token')
  <div class="header header-token">
    <div class="icon">🎟️</div>
    <h1>Thanh Toán Thành Công!</h1>
    <p>Lượt ứng tuyển đã được cộng vào tài khoản của bạn</p>
  </div>
  @else
  <div class="header header-subscription">
    <div class="icon">👑</div>
    <h1>Thanh Toán Thành Công!</h1>
    <p>Chào mừng bạn đến với {{ config('app.name') }} Premium</p>
  </div>
  @endif

  {{-- Body --}}
  <div class="body">
    <p class="greeting">Xin chào {{ $payment->user->name }},</p>

    <div class="success-badge">
      ✅ Giao dịch của bạn đã được xử lý thành công
    </div>

    <p class="text">
      @if($payment->type === 'token')
        Cảm ơn bạn đã mua <strong>{{ $payment->token_amount }} lượt ứng tuyển</strong>.
        Lượt đã được cộng vào tài khoản và sẵn sàng sử dụng ngay.
      @else
        Cảm ơn bạn đã nâng cấp lên <strong>{{ config('app.name') }} Premium</strong>.
        Tài khoản của bạn đã được kích hoạt và sẵn sàng sử dụng đầy đủ tính năng.
      @endif
    </p>

    {{-- Invoice --}}
    <div class="invoice">
      <div class="invoice-row">
        <span class="label">Loại giao dịch</span>
        @if($payment->type === 'token')
          <span class="badge-token">Mua lượt ứng tuyển</span>
        @else
          <span class="badge-premium">Gói Premium {{ ucfirst($payment->plan) }}</span>
        @endif
      </div>

      @if($payment->type === 'token')
      <div class="invoice-row">
        <span class="label">Số lượt ứng tuyển</span>
        <span class="value">+{{ $payment->token_amount }} lượt</span>
      </div>
      @else
      <div class="invoice-row">
        <span class="label">Ngày kích hoạt</span>
        <span class="value">{{ now()->format('d/m/Y') }}</span>
      </div>
      @php
        $activeSub = $payment->user->subscriptions()
            ->where('status', 'active')
            ->orderByDesc('id')
            ->first();
      @endphp
      @if($activeSub?->billing_ends)
      <div class="invoice-row">
        <span class="label">Hết hạn vào</span>
        <span class="value">{{ \Carbon\Carbon::parse($activeSub->billing_ends)->format('d/m/Y') }}</span>
      </div>
      @endif
      @endif

      <div class="invoice-row">
        <span class="label">Phương thức thanh toán</span>
        <span class="value">VNPay</span>
      </div>
      <div class="invoice-row">
        <span>💰 Tổng thanh toán</span>
        <span class="amount">{{ number_format($payment->amount) }} ₫</span>
      </div>
    </div>

    {{-- Features theo loại --}}
    @if($payment->type === 'token')
    <div class="features">
      <h3>🎯 Lượt ứng tuyển dùng để làm gì?</h3>
      <ul>
        <li>✅ Ứng tuyển vào các vị trí việc làm trên {{ config('app.name') }}</li>
        <li>✅ Mỗi lần nộp đơn ứng tuyển tiêu thụ 1 lượt</li>
        <li>✅ Không giới hạn thời gian sử dụng</li>
        <li>✅ Xem chi tiết lịch sử ứng tuyển trong Dashboard</li>
      </ul>
    </div>
    @else
    <div class="features">
      <h3>👑 Với tài khoản Premium, bạn có thể:</h3>
      <ul>
        <li>✅ Đăng tin tuyển dụng không giới hạn</li>
        <li>✅ Xem đầy đủ hồ sơ và CV ứng viên</li>
        <li>✅ Shortlist và liên hệ trực tiếp ứng viên</li>
        <li>✅ Ưu tiên hiển thị tin đăng</li>
        <li>✅ Truy cập tất cả tính năng Premium</li>
      </ul>
    </div>
    @endif

    {{-- CTA Button --}}
    <div class="cta {{ $payment->type !== 'token' ? 'cta-dark' : '' }}">
      @if($payment->type === 'token')
        <a href="{{ url('/job') }}">Tìm việc ngay</a>
      @else
        <a href="{{ url('/dashboard') }}">Vào Dashboard ngay</a>
      @endif
    </div>

    <hr class="divider">

    <p class="text" style="font-size:13px;color:#888">
      Trân trọng,<br>
      <strong style="color:#333">Đội ngũ {{ config('app.name') }} 💚</strong>
    </p>
  </div>

  {{-- Footer --}}
  <div class="footer">
    <p>Email này được gửi tự động từ hệ thống <span class="brand">{{ config('app.name') }}</span></p>
    <p>Vui lòng không reply trực tiếp email này.</p>
    @if($payment->vnpay_txn_ref)
    <p style="margin-top:8px;color:#bbb">Mã giao dịch: <strong>{{ $payment->vnpay_txn_ref }}</strong></p>
    @endif
    <p style="margin-top:6px;color:#bbb">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
  </div>

</div>
</body>
</html>