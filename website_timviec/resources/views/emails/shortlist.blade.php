<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Chúc mừng! Bạn đã được Shortlist</title>
  <style>
    body { font-family: 'Helvetica Neue', Arial, sans-serif; background: #f4f5f5; margin: 0; padding: 0; }
    .wrapper { max-width: 580px; margin: 30px auto; }
    .header { background: linear-gradient(135deg, #00b14f, #008a3e); padding: 28px 32px; border-radius: 12px 12px 0 0; text-align: center; }
    .header h1 { color: #fff; font-size: 22px; margin: 0; }
    .header p { color: rgba(255,255,255,.85); font-size: 13px; margin: 6px 0 0; }
    .body { background: #fff; padding: 28px 32px; }
    .highlight { background: #f0fdf7; border-left: 4px solid #00b14f; padding: 14px 16px; border-radius: 0 8px 8px 0; margin: 20px 0; }
    .highlight h3 { margin: 0 0 4px; color: #212f3f; font-size: 15px; }
    .highlight p { margin: 0; color: #6f7882; font-size: 13px; }
    .btn { display: inline-block; background: #00b14f; color: #fff; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 14px; margin-top: 20px; }
    .footer { background: #f4f5f5; padding: 16px 32px; text-align: center; border-radius: 0 0 12px 12px; font-size: 12px; color: #6f7882; }
    p { line-height: 1.7; color: #444; font-size: 14px; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <div style="font-size: 36px; margin-bottom: 8px;">🎉</div>
      <h1>Chúc mừng! Bạn đã được Shortlist</h1>
      <p>Nhà tuyển dụng đã quan tâm đến hồ sơ của bạn</p>
    </div>
    <div class="body">
      <p>Xin chào <strong>{{ $applicant->name }}</strong>,</p>
      <p>Chúng tôi có tin vui! Hồ sơ của bạn đã được nhà tuyển dụng <strong>shortlist</strong> cho vị trí:</p>

      <div class="highlight">
        <h3>{{ $listing->title }}</h3>
        <p>{{ $listing->user->company_name ?? $listing->user->name }} · {{ $listing->address }}</p>
      </div>

      <p>Điều này có nghĩa là nhà tuyển dụng đang <strong>rất quan tâm</strong> đến bạn và có thể sẽ liên hệ sớm để sắp xếp phỏng vấn. Hãy chuẩn bị sẵn sàng!</p>
      <p>Trong thời gian chờ đợi, bạn có thể:</p>
      <ul style="line-height:2;color:#444;font-size:14px">
        <li>Cập nhật thêm thông tin vào hồ sơ của mình</li>
        <li>Nghiên cứu về công ty và vị trí ứng tuyển</li>
        <li>Chuẩn bị câu hỏi cho buổi phỏng vấn</li>
      </ul>

      <div style="text-align: center">
        <a href="{{ url('/dashboard') }}" class="btn">Xem hồ sơ ứng tuyển</a>
      </div>

      <p style="margin-top:24px">Chúc bạn thành công!<br><strong>Đội ngũ ITWorks 💚</strong></p>
    </div>
    <div class="footer">
      © {{ date('Y') }} ITWorks · Bạn nhận email này vì đã ứng tuyển tại ITWorks<br>
      <a href="{{ url('/user/mail') }}" style="color: #00b14f">Huỷ nhận thông báo</a>
    </div>
  </div>
</body>
</html>
