<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Xác thực Email — ITWorks</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 16px;">
    <tr>
      <td align="center">
        <table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 4px 32px rgba(0,0,0,0.08);">

          {{-- Header --}}
          <tr>
            <td style="background:linear-gradient(135deg,#00D97E,#00B368);padding:36px 40px;text-align:center;">
              <div style="display:inline-block;background:rgba(255,255,255,0.15);border-radius:16px;padding:10px 24px;margin-bottom:16px;">
                <span style="color:#ffffff;font-size:26px;font-weight:800;letter-spacing:-0.5px;">
                  IT<span style="color:#d1fae5;">Works</span>
                </span>
              </div>
              <p style="margin:0;color:rgba(255,255,255,0.85);font-size:14px;">Nền tảng tuyển dụng IT hàng đầu Việt Nam</p>
            </td>
          </tr>

          {{-- Icon --}}
          <tr>
            <td style="text-align:center;padding:36px 40px 0;">
              <div style="display:inline-flex;align-items:center;justify-content:center;width:72px;height:72px;background:#e6fbf3;border-radius:50%;margin-bottom:24px;">
                <span style="font-size:32px;">✉️</span>
              </div>
              <h2 style="margin:0 0 12px;color:#0f172a;font-size:22px;font-weight:800;">
                Xác thực địa chỉ email
              </h2>
              <p style="margin:0;color:#64748b;font-size:15px;line-height:1.6;">
                Xin chào <strong style="color:#0f172a;">{{ $notifiable->name }}</strong>,
              </p>
            </td>
          </tr>

          {{-- Body --}}
          <tr>
            <td style="padding:20px 40px 32px;">
              <p style="margin:0 0 28px;color:#475569;font-size:15px;line-height:1.7;text-align:center;">
                Cảm ơn bạn đã đăng ký tài khoản <strong style="color:#00B368;">ITWorks</strong>.<br>
                Vui lòng nhấn vào nút bên dưới để xác thực email và bắt đầu tìm kiếm việc làm IT phù hợp.
              </p>

              {{-- CTA Button --}}
              <table cellpadding="0" cellspacing="0" style="margin:0 auto 32px;">
                <tr>
                  <td style="background:#00D97E;border-radius:12px;box-shadow:0 4px 16px rgba(0,217,126,0.4);">
                    <a href="{{ $verificationUrl }}"
                       style="display:inline-block;padding:16px 48px;color:#ffffff;font-size:16px;font-weight:700;text-decoration:none;letter-spacing:0.3px;">
                      ✅ Xác thực Email ngay
                    </a>
                  </td>
                </tr>
              </table>

              {{-- Warning box --}}
              <table cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
                <tr>
                  <td style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px 20px;">
                    <p style="margin:0;color:#64748b;font-size:13px;line-height:1.6;">
                      ⏰ <strong>Link có hiệu lực trong 5 phút.</strong> Nếu bạn không đăng ký tài khoản này, hãy bỏ qua email này.
                    </p>
                  </td>
                </tr>
              </table>

              {{-- Fallback URL --}}
              <p style="margin:0 0 6px;color:#94a3b8;font-size:13px;">Nếu nút không hoạt động, copy đường link sau vào trình duyệt:</p>
              <p style="margin:0;word-break:break-all;">
                <a href="{{ $verificationUrl }}" style="color:#00B368;font-size:12px;">{{ $verificationUrl }}</a>
              </p>
            </td>
          </tr>

          {{-- Footer --}}
          <tr>
            <td style="background:#f8fafc;border-top:1px solid #f1f5f9;padding:24px 40px;text-align:center;">
              <p style="margin:0 0 6px;color:#94a3b8;font-size:12px;">
                © {{ date('Y') }} <strong>ITWorks</strong> — Nền tảng tuyển dụng IT hàng đầu Việt Nam
              </p>
              <p style="margin:0;color:#cbd5e1;font-size:11px;">
                Email này được gửi tự động, vui lòng không trả lời.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
