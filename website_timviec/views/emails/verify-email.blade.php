<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Xác thực Email — ITWorks</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Arial,sans-serif">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 0">
    <tr>
      <td align="center">
        <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08)">

          {{-- Header --}}
          <tr>
            <td style="background:linear-gradient(135deg,#10b981,#059669);padding:32px 40px;text-align:center">
              <h1 style="margin:0;color:#ffffff;font-size:28px;font-weight:800;letter-spacing:-0.5px">
                IT<span style="color:#d1fae5">Works</span>
              </h1>
            </td>
          </tr>

          {{-- Body --}}
          <tr>
            <td style="padding:40px 40px 32px">
              <h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;font-weight:700">
                Xác thực địa chỉ email của bạn
              </h2>
              <p style="margin:0 0 12px;color:#475569;font-size:15px;line-height:1.6">
                Xin chào <strong>{{ $notifiable->name }}</strong>,
              </p>
              <p style="margin:0 0 28px;color:#475569;font-size:15px;line-height:1.6">
                Cảm ơn bạn đã đăng ký tài khoản <strong>ITWorks</strong>. Vui lòng nhấn vào nút bên dưới để xác thực địa chỉ email và bắt đầu sử dụng dịch vụ.
              </p>

              {{-- CTA Button --}}
              <table cellpadding="0" cellspacing="0" style="margin:0 auto 32px">
                <tr>
                  <td style="background:#10b981;border-radius:10px">
                    <a href="{{ $verificationUrl }}"
                       style="display:inline-block;padding:14px 36px;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;letter-spacing:0.2px">
                      ✅ Xác thực Email ngay
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin:0 0 8px;color:#94a3b8;font-size:13px;line-height:1.5">
                Nếu nút trên không hoạt động, hãy copy và dán đường link sau vào trình duyệt:
              </p>
              <p style="margin:0 0 28px;word-break:break-all">
                <a href="{{ $verificationUrl }}" style="color:#10b981;font-size:12px">{{ $verificationUrl }}</a>
              </p>

              <div style="border-top:1px solid #f1f5f9;padding-top:20px">
                <p style="margin:0;color:#94a3b8;font-size:13px;line-height:1.5">
                  Link xác thực có hiệu lực trong <strong>60 phút</strong>. Nếu bạn không đăng ký tài khoản này, hãy bỏ qua email này.
                </p>
              </div>
            </td>
          </tr>

          {{-- Footer --}}
          <tr>
            <td style="background:#f8fafc;padding:20px 40px;text-align:center;border-top:1px solid #f1f5f9">
              <p style="margin:0;color:#94a3b8;font-size:12px">
                © {{ date('Y') }} ITWorks — Nền tảng tuyển dụng IT hàng đầu Việt Nam
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
