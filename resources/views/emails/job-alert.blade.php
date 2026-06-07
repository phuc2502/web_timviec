<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: 'Inter', Arial, sans-serif; background: #f4f7fb; margin: 0; padding: 20px; }
    .container { max-width: 580px; margin: 0 auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
    .header { background: linear-gradient(135deg, #00B14F, #009a43); padding: 32px; text-align: center; color: #fff; }
    .header h1 { margin: 0; font-size: 22px; font-weight: 800; }
    .body { padding: 28px 32px; }
    .greeting { font-size: 15px; color: #333; margin-bottom: 20px; }
    .job-card { border: 1px solid #e8ecf0; border-radius: 10px; padding: 16px; margin-bottom: 12px; }
    .job-title { font-size: 15px; font-weight: 700; color: #1a1a1a; margin: 0 0 4px; }
    .job-meta { font-size: 13px; color: #666; }
    .btn { display: inline-block; background: #00B14F; color: #fff; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 700; font-size: 14px; margin-top: 20px; }
    .footer { padding: 20px 32px; background: #f8fafc; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #e8ecf0; }
  </style>
</head>
<body>
<div class="container">
  <div class="header">
    <div style="font-size: 36px; margin-bottom: 10px">🔔</div>
    <h1>{{ $listings->count() }} việc làm mới phù hợp với bạn!</h1>
  </div>
  <div class="body">
    <div class="greeting">
      Xin chào <strong>{{ $employee->name }}</strong>,<br>
      Trong tuần này có <strong>{{ $listings->count() }}</strong> tin tuyển dụng mới khớp với kỹ năng của bạn. Đừng bỏ lỡ!
    </div>

    @foreach($listings->take(5) as $listing)
    <div class="job-card">
      <div class="job-title">{{ $listing->title }}</div>
      <div class="job-meta">
        🏢 {{ $listing->user->company_name ?? $listing->user->name }}
        @if($listing->location) &nbsp;·&nbsp; 📍 {{ $listing->location }} @endif
        @if($listing->salary_min) &nbsp;·&nbsp; 💰 {{ number_format($listing->salary_min) }}–{{ number_format($listing->salary_max ?? $listing->salary_min) }} ₫ @endif
      </div>
    </div>
    @endforeach

    @if($listings->count() > 5)
    <p style="text-align:center;font-size:13px;color:#666;">Và {{ $listings->count() - 5 }} tin khác...</p>
    @endif

    <div style="text-align:center">
      <a href="{{ url('/job') }}" class="btn">Xem tất cả việc làm phù hợp</a>
    </div>
  </div>
  <div class="footer">
    Bạn nhận email này vì đã bật Job Alert trên <strong>ITWorks</strong>.<br>
    <a href="{{ url('/user/profile') }}" style="color:#00B14F">Tắt thông báo</a>
  </div>
</div>
</body>
</html>
