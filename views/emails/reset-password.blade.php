@component('mail::message')
# Đặt lại mật khẩu

Xin chào **{{ $notifiable->name }}**,

Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản ITWorks liên kết với địa chỉ email này.

Nhấn vào nút bên dưới để tạo mật khẩu mới:

@component('mail::button', ['url' => $resetUrl, 'color' => 'success'])
🔑 Đặt lại mật khẩu
@endcomponent

**Lưu ý:** Link này sẽ hết hạn sau **60 phút**.

Nếu bạn **không** yêu cầu đặt lại mật khẩu, hãy bỏ qua email này. Tài khoản của bạn vẫn an toàn.

---

Nếu nút không hoạt động, hãy sao chép đường dẫn sau vào trình duyệt:

`{{ $resetUrl }}`

Trân trọng,
**Đội ngũ ITWorks**

@component('mail::subcopy')
Yêu cầu đặt lại mật khẩu được gửi từ địa chỉ IP: {{ request()->ip() }}
@endcomponent
@endcomponent
