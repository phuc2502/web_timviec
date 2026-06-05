<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin tuyển dụng không được duyệt</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f6f9fc; margin: 0; padding: 0; }
        .wrapper { width: 100%; background-color: #f6f9fc; padding: 40px 0; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { background: linear-gradient(135deg, #ef4444, #dc2626); padding: 32px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .content { padding: 40px 32px; color: #334155; line-height: 1.6; }
        .content h2 { color: #1e293b; font-size: 20px; font-weight: 600; margin-top: 0; }
        .button-container { text-align: center; margin: 32px 0 16px 0; }
        .button { background-color: #3b82f6; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 15px; display: inline-block; box-shadow: 0 4px 6px rgba(59,130,246,0.2); }
        .footer { text-align: center; padding: 24px; font-size: 13px; color: #64748b; background-color: #f8fafc; border-top: 1px solid #e2e8f0; }
        .warning-box { background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 16px 20px; border-radius: 0 8px 8px 0; margin: 24px 0; color: #991b1b; }
        .highlight-item { margin: 8px 0; font-size: 14px; }
        .highlight-label { font-weight: 600; color: #475569; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main">
            <div class="header">
                <h1>Tin Tuyển Dụng Không Được Duyệt</h1>
            </div>
            <div class="content">
                <h2>Xin chào {{ $listing->user->name }},</h2>
                <p>Chúng tôi rất tiếc phải thông báo rằng tin tuyển dụng của bạn không đáp ứng đầy đủ các tiêu chuẩn kiểm duyệt của hệ thống và đã bị từ chối phê duyệt.</p>
                
                <div class="warning-box">
                    <strong>Lý do từ chối:</strong>
                    <p style="margin: 8px 0 0 0; font-size: 15px; line-height: 1.5;">{{ $reason }}</p>
                </div>

                <p>Thông tin tin tuyển dụng bị từ chối:</p>
                <div style="background-color: #f1f5f9; padding: 16px; border-radius: 8px; margin: 20px 0;">
                    <div class="highlight-item">
                        <span class="highlight-label">Tiêu đề:</span> {{ $listing->title }}
                    </div>
                    <div class="highlight-item">
                        <span class="highlight-label">Ngành nghề:</span> {{ $listing->category->name }}
                    </div>
                </div>

                <p>Vui lòng điều chỉnh lại thông tin tin tuyển dụng dựa trên lý do trên và gửi yêu cầu duyệt lại.</p>

                <div class="button-container">
                    <a href="{{ url('/employer/listings/' . $listing->id) }}" class="button" target="_blank">Chỉnh Sửa Tin</a>
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
