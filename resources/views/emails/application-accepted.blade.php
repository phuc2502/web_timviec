<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chúc mừng! Bạn đã được nhận</title>
  <style>
    body { margin:0; padding:0; background:#f4f7f9; font-family:'Helvetica Neue',Arial,sans-serif; color:#333; }
    .wrapper { max-width:600px; margin:40px auto; }
    .header { background:linear-gradient(135deg,#00B14F,#009140); border-radius:12px 12px 0 0; padding:36px 40px; text-align:center; }
    .header h1 { margin:0; color:#fff; font-size:22px; font-weight:700; }
    .header p  { margin:6px 0 0; color:rgba(255,255,255,0.85); font-size:14px; }
    .body { background:#fff; padding:36px 40px; }
    .greeting { font-size:17px; font-weight:600; color:#1a1a1a; margin-bottom:16px; }
    .text { font-size:14px; line-height:1.75; color:#444; margin-bottom:16px; }
    .highlight-box { background:#f0fdf4; border-left:4px solid #00B14F; border-radius:0 8px 8px 0; padding:20px 24px; margin:24px 0; }
    .highlight-box .row { display:flex; gap:12px; margin-bottom:10px; font-size:14px; }
    .highlight-box .row:last-child { margin-bottom:0; }
    .highlight-box .label { color:#666; min-width:140px; flex-shrink:0; }
    .highlight-box .value { color:#1a1a1a; font-weight:600; }
    .congrats-box { background:linear-gradient(135deg,#00B14F,#009140); border-radius:10px; padding:24px; margin:20px 0; text-align:center; }
    .congrats-box .emoji { font-size:48px; margin-bottom:8px; }
    .congrats-box .text { color:#fff; font-size:18px; font-weight:700; margin:0; }
    .cta { text-align:center; margin:28px 0; }
    .cta a { background:#00B14F; color:#fff; padding:13px 32px; border-radius:8px; text-decoration:none; font-size:15px; font-weight:600; display:inline-block; }
    .divider { border:none; border-top:1px solid #eee; margin:24px 0; }
    .contact-box { background:#f8f9fa; border-radius:8px; padding:18px 20px; font-size:13px; color:#555; }
    .contact-box strong { color:#1a1a1a; display:block; font-size:14px; margin-bottom:10px; }
    .footer { background:#f4f7f9; border-radius:0 0 12px 12px; padding:24px 40px; text-align:center; }
    .footer p { font-size:12px; color:#999; margin:4px 0; }
    .footer .brand { color:#00B14F; font-weight:700; }
  </style>
</head>
<body>
<div class="wrapper">

  {{-- Header --}}
  <div class="header">
    <h1>✅ Chúc Mừng Bạn Đã Được Nhận!</h1>
    <p>{{ config('app.name') }} — Kết nối nhân tài & doanh nghiệp</p>
  </div>

  {{-- Body --}}
  <div class="body">
    <p class="greeting">Xin chào {{ $application->user->name }},</p>

    <div class="congrats-box">
      <div class="emoji">🎉</div>
      <p class="text">Chúc mừng! Bạn đã được nhận vào vị trí này!</p>
    </div>

    <p class="text">
      Chúng tôi vui mừng thông báo rằng <strong>{{ $application->listing->user->company_name ?? $application->listing->user->name }}</strong>
      đã chính thức chấp nhận đơn ứng tuyển của bạn cho vị trí:
    </p>

    {{-- Job info --}}
    <div class="highlight-box">
      <div class="row">
        <span class="label">🏢 Công ty:</span>
        <span class="value">{{ $application->listing->user->company_name ?? $application->listing->user->name }}</span>
      </div>
      <div class="row">
        <span class="label">💼 Vị trí:</span>
        <span class="value">{{ $application->listing->title }}</span>
      </div>
      <div class="row">
        <span class="label">📍 Địa điểm:</span>
        <span class="value">{{ $application->listing->address }}</span>
      </div>
    </div>

    <p class="text">
      Nhà tuyển dụng sẽ liên hệ với bạn sớm để thông báo các bước tiếp theo.
      Hãy kiểm tra email và điện thoại thường xuyên để không bỏ lỡ thông tin quan trọng.
    </p>

    <div class="cta">
      <a href="{{ url('/candidate/history') }}">Xem chi tiết đơn ứng tuyển</a>
    </div>

    <hr class="divider">

    {{-- Contact info --}}
    <div class="contact-box">
      <strong>📞 Thông tin liên hệ Nhà tuyển dụng</strong>
      <div>🏢 {{ $application->listing->user->company_name ?? $application->listing->user->name }}</div>
      @if($application->listing->user->phone ?? false)
        <div style="margin-top:5px">📱 {{ $application->listing->user->phone }}</div>
      @endif
      <div style="margin-top:5px">📧 {{ $application->listing->user->email }}</div>
    </div>
  </div>

  {{-- Footer --}}
  <div class="footer">
    <p>Email này được gửi tự động từ hệ thống <span class="brand">{{ config('app.name') }}</span></p>
    <p>Vui lòng không reply trực tiếp email này.</p>
    <p style="margin-top:10px;color:#bbb">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
  </div>

</div>
</body>
</html>
