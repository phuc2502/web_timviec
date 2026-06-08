<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tin nhắn chưa đọc</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border: 1px solid #e1e8ed;
        }
        .header {
            background: #007bff;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }
        .content {
            padding: 30px 20px;
        }
        .msg-list {
            margin: 20px 0;
            padding: 0;
            list-style: none;
        }
        .msg-item {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 12px 15px;
            margin-bottom: 12px;
            border-radius: 0 6px 6px 0;
        }
        .msg-sender {
            font-weight: bold;
            font-size: 14px;
            color: #495057;
            margin-bottom: 4px;
        }
        .msg-body {
            font-size: 13px;
            color: #6c757d;
        }
        .msg-time {
            font-size: 11px;
            color: #adb5bd;
            margin-top: 5px;
            text-align: right;
        }
        .btn-wrapper {
            text-align: center;
            margin-top: 30px;
        }
        .btn {
            background-color: #007bff;
            color: white !important;
            padding: 12px 24px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 6px;
            display: inline-block;
            box-shadow: 0 3px 6px rgba(0,123,255,0.2);
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #adb5bd;
            border-top: 1px solid #eee;
            background: #fafafa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 Tin nhắn tuyển dụng mới</h1>
        </div>
        <div class="content">
            <p>Chào <strong>{{ $recipient->name }}</strong>,</p>
            <p>Bạn có tin nhắn mới chưa đọc trên hệ thống **Tim Viec** từ các cuộc hội thoại tuyển dụng:</p>
            
            <ul class="msg-list">
                @foreach($unreadMessagesData as $data)
                    <li class="msg-item">
                        <div class="msg-sender">👤 {{ $data['sender_name'] }}</div>
                        <div class="msg-body">
                            @if(!empty($data['attachment_name']))
                                📎 [Đính kèm file: {{ $data['attachment_name'] }}]
                            @else
                                {{ Str::limit($data['body'], 100) }}
                            @endif
                        </div>
                        <div class="msg-time">{{ $data['time'] }}</div>
                    </li>
                @endforeach
            </ul>
            
            <p>Vui lòng đăng nhập hệ thống để phản hồi sớm nhất có thể, tránh bỏ lỡ các thông tin quan trọng từ đối phương.</p>
            
            <div class="btn-wrapper">
                <a href="{{ url('/messages') }}" class="btn" target="_blank">Đi đến đoạn chat</a>
            </div>
        </div>
        <div class="footer">
            Đây là email tự động từ hệ thống Tim Viec. Vui lòng không phản hồi email này.
        </div>
    </div>
</body>
</html>
