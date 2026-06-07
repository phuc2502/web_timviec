<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chào mừng đến ITWorks</title>
    <style>
        body { margin:0;padding:0;font-family:'Segoe UI',Arial,sans-serif;background-color:#f4f6f9;color:#333; }
        .wrapper { width:100%;background-color:#f4f6f9;padding:40px 0; }
        .container { max-width:560px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08); }
        .header-employee { background:linear-gradient(135deg,#1a73e8 0%,#0d47a1 100%);padding:36px 40px;text-align:center; }
        .header-employer { background:linear-gradient(135deg,#e65100 0%,#bf360c 100%);padding:36px 40px;text-align:center; }
        .header h1 { margin:0;color:#fff;font-size:26px;font-weight:700; }
        .header p  { margin:6px 0 0;color:rgba(255,255,255,0.85);font-size:14px; }
        .body { padding:36px 40px; }
        .greeting { font-size:18px;font-weight:600;margin:0 0 12px; }
        .greeting-employee { color:#1a73e8; }
        .greeting-employer { color:#e65100; }
        .body p { font-size:15px;line-height:1.7;color:#555;margin:0 0 16px; }
        .info-box { border-left:4px solid #1a73e8;background:#f0f7ff;border-radius:6px;padding:16px 20px;margin:24px 0; }
        .info-box-employer { border-left:4px solid #e65100;background:#fff3e0; }
        .info-box p { margin:4px 0;font-size:14px;color:#444; }
        .label-employee { color:#1a73e8; }
        .label-employer { color:#e65100; }
        .feature-list { background:#f8f9fa;border-radius:8px;padding:20px 24px;margin:20px 0; }
        .feature-list p { font-size:13px;font-weight:600;color:#333;margin:0 0 10px; }
        .feature-list ul { margin:0;padding-left:18px; }
        .feature-list li { font-size:13px;color:#555;line-height:2; }
        .btn-wrapper { text-align:center;margin:28px 0; }
        .btn-employee { display:inline-block;background:linear-gradient(135deg,#1a73e8,#0d47a1);color:#fff!important;text-decoration:none;padding:14px 36px;border-radius:8px;font-size:15px;font-weight:600; }
        .btn-employer { display:inline-block;background:linear-gradient(135deg,#e65100,#bf360c);color:#fff!important;text-decoration:none;padding:14px 36px;border-radius:8px;font-size:15px;font-weight:600; }
        .divider { border:none;border-top:1px solid #eee;margin:28px 0; }
        .footer { background:#f9f9f9;padding:20px 40px;text-align:center; }
        .footer p { font-size:12px;color:#999;margin:4px 0;line-height:1.6; }
    </style>
</head>
<body>
<div class="wrapper">
  <div class="container">

    @if($user->user_type === 'employer')
    <div class="header header-employer">
      <h1>🏢 Chào mừng Nhà tuyển dụng!</h1>
      <p>Kết nối với hàng nghìn ứng viên IT tài năng</p>
    </div>
    <div class="body">
      <p class="greeting greeting-employer">Xin chào, {{ $user->name }}!</p>
      <p>Bạn vừa đăng ký tài khoản <strong>Nhà tuyển dụng</strong> trên ITWorks thành công. Hãy bắt đầu đăng tin tuyển dụng và tìm kiếm ứng viên phù hợp ngay hôm nay!</p>
      <div class="info-box info-box-employer">
        <p><strong class="label-employer">Email:</strong> {{ $user->email }}</p>
        <p><strong class="label-employer">Loại tài khoản:</strong> Nhà tuyển dụng</p>
        @if($user->company_name)
        <p><strong class="label-employer">Công ty:</strong> {{ $user->company_name }}</p>
        @endif
        <p><strong class="label-employer">Đăng ký lúc:</strong> {{ \Carbon\Carbon::now('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }} (Giờ VN)</p>
        @if($user->user_trial)
        <p><strong class="label-employer">🎁 Dùng thử miễn phí đến:</strong> {{ \Carbon\Carbon::parse($user->user_trial)->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y') }}</p>
        @endif
      </div>
      <div class="feature-list">
        <p>🚀 Với tài khoản Nhà tuyển dụng, bạn có thể:</p>
        <ul>
          <li>Đăng tin tuyển dụng không giới hạn trong thời gian dùng thử</li>
          <li>Duyệt hồ sơ ứng viên IT chuyên nghiệp</li>
          <li>Nhắn tin trực tiếp với ứng viên</li>
          <li>Quản lý toàn bộ quy trình tuyển dụng</li>
        </ul>
      </div>
      <div class="btn-wrapper">
        <a href="{{ url('/') }}" class="btn-employer">Vào trang tuyển dụng →</a>
      </div>

    @else
    <div class="header header-employee">
      <h1>🎉 Chào mừng đến ITWorks!</h1>
      <p>Nền tảng tìm việc làm IT hàng đầu Việt Nam</p>
    </div>
    <div class="body">
      <p class="greeting greeting-employee">Xin chào, {{ $user->name }}!</p>
      <p>Bạn vừa đăng ký tài khoản <strong>Ứng viên</strong> trên ITWorks thành công. Hàng nghìn cơ hội việc làm IT đang chờ bạn khám phá!</p>
      <div class="info-box">
        <p><strong class="label-employee">Email:</strong> {{ $user->email }}</p>
        <p><strong class="label-employee">Loại tài khoản:</strong> Ứng viên</p>
        <p><strong class="label-employee">Đăng ký lúc:</strong> {{ \Carbon\Carbon::now('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }} (Giờ VN)</p>
      </div>
      <div class="feature-list">
        <p>✨ Với tài khoản Ứng viên, bạn có thể:</p>
        <ul>
          <li>Tìm kiếm và ứng tuyển hàng nghìn việc làm IT</li>
          <li>Tạo và quản lý hồ sơ CV chuyên nghiệp</li>
          <li>Nhận thông báo việc làm phù hợp</li>
          <li>Nhắn tin trực tiếp với nhà tuyển dụng</li>
        </ul>
      </div>
      <div class="btn-wrapper">
        <a href="{{ url('/') }}" class="btn-employee">Vào trang tuyển dụng →</a>
      </div>
    @endif

      <hr class="divider">
      <p style="font-size:13px;color:#888;">Nếu bạn không thực hiện đăng ký này, vui lòng bỏ qua email hoặc liên hệ <a href="mailto:support@itworks.vn" style="color:#1a73e8;">support@itworks.vn</a>.</p>
    </div>

    <div class="footer">
      <p>© {{ date('Y') }} ITWorks. All rights reserved.</p>
      <p>Email này được gửi tự động, vui lòng không trả lời.</p>
    </div>
  </div>
</div>
</body>
</html>
