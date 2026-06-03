<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thư mời phỏng vấn</title>
  <style>
    body { margin:0; padding:0; background:#f4f7f9; font-family:'Helvetica Neue',Arial,sans-serif; color:#333; }
    .wrapper { max-width:600px; margin:40px auto; }
    .header { background:linear-gradient(135deg,#00B14F,#009140); border-radius:12px 12px 0 0; padding:36px 40px; text-align:center; }
    .header img { width:120px; margin-bottom:12px; }
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
    .interview-time { background:linear-gradient(135deg,#1B2B4B,#2d4a7a); border-radius:10px; padding:20px 24px; margin:20px 0; text-align:center; }
    .interview-time .time-label { color:rgba(255,255,255,0.7); font-size:12px; text-transform:uppercase; letter-spacing:1px; margin-bottom:6px; }
    .interview-time .time-value { color:#fff; font-size:22px; font-weight:700; }
    .interview-time .time-sub   { color:#D4A843; font-size:13px; margin-top:4px; }
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

  
  <div class="header">
    <h1>🎯 Thư Mời Phỏng Vấn</h1>
    <p><?php echo e(config('app.name')); ?> — Kết nối nhân tài & doanh nghiệp</p>
  </div>

  
  <div class="body">
    <p class="greeting">Xin chào <?php echo e($application->user->name); ?>,</p>

    <p class="text">
      Chúc mừng bạn! Sau khi xem xét hồ sơ ứng tuyển của bạn, <strong><?php echo e($application->listing->user->company_name ?? $application->listing->user->name); ?></strong>
      rất vui mừng thông báo bạn đã qua vòng lọc hồ sơ và được mời tham gia buổi phỏng vấn cho vị trí:
    </p>

    
    <div class="highlight-box">
      <div class="row">
        <span class="label">🏢 Công ty:</span>
        <span class="value"><?php echo e($application->listing->user->company_name ?? $application->listing->user->name); ?></span>
      </div>
      <div class="row">
        <span class="label">💼 Vị trí:</span>
        <span class="value"><?php echo e($application->listing->title); ?></span>
      </div>
      <div class="row">
        <span class="label">📍 Địa điểm:</span>
        <span class="value"><?php echo e($application->listing->address); ?></span>
      </div>
    </div>

    
    <?php if($application->interview_scheduled_at): ?>
    <div class="interview-time">
      <div class="time-label">⏰ Thời gian phỏng vấn dự kiến</div>
      <div class="time-value"><?php echo e($application->interview_scheduled_at->format('H:i')); ?></div>
      <div class="time-sub"><?php echo e($application->interview_scheduled_at->format('l, d/m/Y')); ?></div>
    </div>
    <?php endif; ?>

    <p class="text">
      Vui lòng xác nhận tham dự và chuẩn bị đầy đủ hồ sơ (CV, bằng cấp liên quan) trước buổi phỏng vấn.
      Nếu bạn có bất kỳ câu hỏi nào, đừng ngần ngại liên hệ với chúng tôi.
    </p>

    <div class="cta">
      <a href="<?php echo e(url('/candidate/history')); ?>">Xem chi tiết đơn ứng tuyển</a>
    </div>

    <hr class="divider">

    
    <div class="contact-box">
      <strong>📞 Thông tin liên hệ Nhà tuyển dụng</strong>
      <div>🏢 <?php echo e($application->listing->user->company_name ?? $application->listing->user->name); ?></div>
      <?php if($application->listing->user->phone ?? false): ?>
        <div style="margin-top:5px">📱 <?php echo e($application->listing->user->phone); ?></div>
      <?php endif; ?>
      <div style="margin-top:5px">📧 <?php echo e($application->listing->user->email); ?></div>
    </div>
  </div>

  
  <div class="footer">
    <p>Email này được gửi tự động từ hệ thống <span class="brand"><?php echo e(config('app.name')); ?></span></p>
    <p>Vui lòng không reply trực tiếp email này.</p>
    <p style="margin-top:10px;color:#bbb">© <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?>. All rights reserved.</p>
  </div>

</div>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\web_timviec_final\website_timviec\resources\views/emails/application-status.blade.php ENDPATH**/ ?>