<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; background: #f4f7fb; margin: 0; padding: 20px; }
    .container { max-width: 560px; margin: 0 auto; background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
    .header { background: linear-gradient(135deg, #0052CC, #0070E0); padding: 28px; text-align: center; color: #fff; }
    .body { padding: 28px 32px; font-size: 14px; color: #333; }
    .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f0f0; }
    .btn { display: inline-block; background: #0052CC; color: #fff; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 700; margin-top: 20px; }
    .footer { padding: 16px; background: #f8fafc; text-align: center; font-size: 12px; color: #999; }
  </style>
</head>
<body>
<div class="container">
  <div class="header">
    <div style="font-size:36px;margin-bottom:8px">📥</div>
    <h2 style="margin:0;font-size:18px">Có ứng viên mới ứng tuyển!</h2>
  </div>
  <div class="body">
    <p>Xin chào <strong>{{ $application->listing->user->name }}</strong>,</p>
    <p>Vị trí <strong>{{ $application->listing->title }}</strong> vừa nhận được một đơn ứng tuyển mới.</p>

    <div style="background:#f8fafc;border-radius:10px;padding:16px;margin:16px 0">
      <div class="info-row"><span style="color:#666">Ứng viên</span><strong>{{ $application->applicant_name ?? $application->user->name }}</strong></div>
      <div class="info-row"><span style="color:#666">Email</span><span>{{ $application->applicant_email ?? $application->user->email }}</span></div>
      <div class="info-row" style="border:none"><span style="color:#666">Thời gian nộp</span><span>{{ $application->applied_at?->format('H:i d/m/Y') }}</span></div>
    </div>

    <div style="text-align:center">
      <a href="{{ url('/employer/applications/'.$application->id) }}" class="btn">Xem hồ sơ ứng viên</a>
    </div>
  </div>
  <div class="footer">ITWorks — Nền tảng tuyển dụng IT</div>
</div>
</body>
</html>
