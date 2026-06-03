<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thông báo kết quả ứng tuyển</title>
  <style>
    body { margin:0; padding:0; background:#f4f7f9; font-family:'Helvetica Neue',Arial,sans-serif; color:#333; }
    .wrapper { max-width:600px; margin:40px auto; }
    .header { background:linear-gradient(135deg,#1B2B4B,#2d4a7a); border-radius:12px 12px 0 0; padding:36px 40px; text-align:center; }
    .header h1 { margin:0; color:#fff; font-size:22px; font-weight:700; }
    .header p  { margin:6px 0 0; color:rgba(255,255,255,0.75); font-size:14px; }
    .body { background:#fff; padding:36px 40px; }
    .greeting { font-size:17px; font-weight:600; color:#1a1a1a; margin-bottom:16px; }
    .text { font-size:14px; line-height:1.85; color:#444; margin-bottom:16px; }
    .text strong { color:#1a1a1a; }
    .highlight-box { background:#f8f9fa; border-left:4px solid #6c757d; border-radius:0 8px 8px 0; padding:18px 22px; margin:20px 0; }
    .highlight-box .row { display:flex; gap:12px; margin-bottom:8px; font-size:14px; }
    .highlight-box .row:last-child { margin-bottom:0; }
    .highlight-box .label { color:#888; min-width:120px; flex-shrink:0; }
    .highlight-box .value { color:#333; font-weight:600; }
    .encouragement-box { background:linear-gradient(135deg,#f0f9ff,#e0f2fe); border-radius:12px; padding:24px 28px; margin:24px 0; border:1px solid #bae6fd; }
    .encouragement-box h3 { margin:0 0 12px; font-size:15px; color:#0369a1; }
    .encouragement-box ul { margin:0; padding:0 0 0 18px; }
    .encouragement-box ul li { font-size:13px; color:#0c4a6e; line-height:1.8; }
    .cta { text-align:center; margin:28px 0; }
    .cta a { background:#1B2B4B; color:#fff; padding:13px 32px; border-radius:8px; text-decoration:none; font-size:15px; font-weight:600; display:inline-block; }
    hr { border:none; border-top:1px solid #eee; margin:24px 0; }
    .footer { background:#f4f7f9; border-radius:0 0 12px 12px; padding:24px 40px; text-align:center; }
    .footer p { font-size:12px; color:#999; margin:4px 0; }
    .footer .brand { color:#1B2B4B; font-weight:700; }
  </style>
</head>
<body>
<div class="wrapper">

  
  <div class="header">
    <h1>Thông Báo Kết Quả Ứng Tuyển</h1>
    <p><?php echo e(config('app.name')); ?> — Kết nối nhân tài & doanh nghiệp</p>
  </div>

  
  <div class="body">
    <p class="greeting">Kính gửi <?php echo e($application->user->name); ?>,</p>

    <p class="text">
      Đầu tiên, <strong><?php echo e($application->listing->user->company_name ?? $application->listing->user->name); ?></strong>
      xin chân thành cảm ơn bạn đã dành thời gian tìm hiểu và ứng tuyển vào vị trí tại công ty chúng tôi.
      Chúng tôi thực sự trân trọng sự quan tâm và nỗ lực mà bạn đã bỏ ra trong quá trình ứng tuyển.
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
        <span class="label">📅 Ngày nộp:</span>
        <span class="value"><?php echo e($application->applied_at?->format('d/m/Y') ?? 'N/A'); ?></span>
      </div>
    </div>

    <p class="text">
      Sau khi xem xét kỹ lưỡng hồ sơ của bạn cùng với các ứng viên khác, chúng tôi rất tiếc phải thông báo rằng
      <strong>hồ sơ của bạn chưa phù hợp với yêu cầu hiện tại</strong> của vị trí này.
      Đây không phải là phản ánh về năng lực hay tiềm năng của bạn, mà đơn giản là chúng tôi đang tìm kiếm
      một profile đặc thù hơn cho thời điểm này.
    </p>

    <p class="text">
      Chúng tôi hiểu rằng đây có thể là một tin không như mong đợi, và chúng tôi chân thành xin lỗi vì điều đó.
    </p>

    
    <div class="encouragement-box">
      <h3>💡 Một vài gợi ý cho hành trình tiếp theo của bạn:</h3>
      <ul>
        <li>Tiếp tục khám phá các cơ hội việc làm phù hợp khác trên <strong><?php echo e(config('app.name')); ?></strong></li>
        <li>Cập nhật và hoàn thiện CV với các kỹ năng và kinh nghiệm mới nhất</li>
        <li>Theo dõi trang tuyển dụng của chúng tôi để không bỏ lỡ cơ hội trong tương lai</li>
        <li>Kết nối với cộng đồng IT để mở rộng mạng lưới chuyên nghiệp</li>
      </ul>
    </div>

    <p class="text">
      Chúng tôi thực sự hy vọng sẽ có cơ hội hợp tác với bạn trong tương lai khi có vị trí phù hợp hơn.
      Một lần nữa, <strong>cảm ơn bạn đã tin tưởng và lựa chọn <?php echo e($application->listing->user->company_name ?? $application->listing->user->name); ?></strong>.
    </p>

    <p class="text">
      Chúc bạn thật nhiều sức khỏe, thành công và sớm tìm được cơ hội việc làm xứng đáng với năng lực của mình! 🌟
    </p>

    <div class="cta">
      <a href="<?php echo e(url('/job')); ?>">Tìm kiếm cơ hội khác</a>
    </div>

    <hr>

    <p class="text" style="font-size:13px;color:#888">
      Trân trọng,<br>
      <strong style="color:#333">Bộ phận Tuyển dụng</strong><br>
      <?php echo e($application->listing->user->company_name ?? $application->listing->user->name); ?>

    </p>
  </div>

  
  <div class="footer">
    <p>Email này được gửi tự động từ hệ thống <span class="brand"><?php echo e(config('app.name')); ?></span></p>
    <p>Vui lòng không reply trực tiếp email này.</p>
    <p style="margin-top:10px;color:#bbb">© <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?>. All rights reserved.</p>
  </div>

</div>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\web_timviec_fixed\web_timviec_fixed\website_timviec\resources\views/emails/application-rejected.blade.php ENDPATH**/ ?>