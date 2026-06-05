<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin tuyển dụng đã được duyệt</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f6f9fc; margin: 0; padding: 0; }
        .wrapper { width: 100%; background-color: #f6f9fc; padding: 40px 0; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { background: linear-gradient(135deg, #10b981, #059669); padding: 32px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .content { padding: 40px 32px; color: #334155; line-height: 1.6; }
        .content h2 { color: #1e293b; font-size: 20px; font-weight: 600; margin-top: 0; }
        .button-container { text-align: center; margin: 32px 0 16px 0; }
        .button { background-color: #10b981; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 15px; display: inline-block; box-shadow: 0 4px 6px rgba(16,185,129,0.2); }
        .footer { text-align: center; padding: 24px; font-size: 13px; color: #64748b; background-color: #f8fafc; border-top: 1px solid #e2e8f0; }
        .highlight-box { background-color: #f1f5f9; border-left: 4px solid #10b981; padding: 16px 20px; border-radius: 0 8px 8px 0; margin: 24px 0; }
        .highlight-item { margin: 8px 0; font-size: 14px; }
        .highlight-label { font-weight: 600; color: #475569; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main">
            <div class="header">
                <h1>Tin Tuyển Dụng Đã Được Duyệt!</h1>
            </div>
            <div class="content">
                <h2>Xin chào {{ $listing->user->name }},</h2>
                <p>Chúng tôi xin vui mừng thông báo tin tuyển dụng của bạn đã được ban quản trị phê duyệt thành công và hiện đang hiển thị công khai trên hệ thống.</p>
                
                <div class="highlight-box">
                    <div class="highlight-item">
                        <span class="highlight-label">Tiêu đề:</span> {{ $listing->title }}
                    </div>
                    <div class="highlight-item">
                        <span class="highlight-label">Ngành nghề:</span> {{ $listing->category->name }}
                    </div>
                    <div class="highlight-item">
                        <span class="highlight-label">Hạn nộp hồ sơ:</span> {{ $listing->application_close_date->format('d/m/Y') }}
                    </div>
                </div>

                <p>Ứng viên đã có thể xem chi tiết thông tin và gửi hồ sơ ứng tuyển trực tiếp vào vị trí này.</p>

                <div class="button-container">
                    <a href="{{ url('/listings/' . $listing->id) }}" class="button" target="_blank">Xem Tin Tuyển Dụng</a>
                </div>
            </div>
            <div class="footer">
                <p>Đây là email tự động từ hệ thống TimViec. Vui lòng không trả lời email này.</p>
                <p>&copy; {{ date('Y') }} TimViec. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
