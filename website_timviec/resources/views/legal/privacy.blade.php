@extends('layouts.app')
@section('title', 'Chính sách bảo mật — ITWorks')

@section('content')
<div style="max-width:800px;margin:0 auto;padding:48px 24px">

  {{-- Header --}}
  <div style="margin-bottom:36px">
    <a href="{{ url('/') }}" class="navbar-brand" style="font-size:24px;justify-content:flex-start;margin-bottom:16px;display:inline-flex">IT<span>Works</span></a>
    <h1 style="font-size:28px;font-weight:800;color:var(--text-primary);margin-bottom:8px">Chính sách bảo mật</h1>
    <p style="color:var(--text-muted);font-size:14px"><i class="fas fa-calendar-alt" style="margin-right:6px"></i>Cập nhật lần cuối: 03/06/2026</p>
  </div>

  <div style="background:var(--bg-card);border-radius:var(--radius-lg);padding:32px;box-shadow:var(--shadow-sm);line-height:1.8;color:var(--text-secondary);font-size:15px">

    <p style="margin-bottom:20px">ITWorks cam kết bảo vệ quyền riêng tư của bạn. Chính sách này mô tả cách chúng tôi thu thập, sử dụng và bảo vệ thông tin cá nhân của bạn khi sử dụng nền tảng ITWorks.</p>

    <h2 style="font-size:18px;font-weight:700;color:var(--text-primary);margin:28px 0 12px">1. Thông tin chúng tôi thu thập</h2>
    <p style="margin-bottom:12px"><strong>Thông tin bạn cung cấp trực tiếp:</strong></p>
    <ul style="margin:0 0 16px 20px;padding:0">
      <li style="margin-bottom:8px">Họ tên, địa chỉ email, số điện thoại khi đăng ký tài khoản.</li>
      <li style="margin-bottom:8px">Thông tin hồ sơ cá nhân, CV, kinh nghiệm làm việc (đối với ứng viên).</li>
      <li style="margin-bottom:8px">Tên công ty, mô tả doanh nghiệp, thông tin liên hệ (đối với nhà tuyển dụng).</li>
    </ul>
    <p style="margin-bottom:12px"><strong>Thông tin thu thập tự động:</strong></p>
    <ul style="margin:0 0 16px 20px;padding:0">
      <li style="margin-bottom:8px">Địa chỉ IP, loại trình duyệt, hệ điều hành.</li>
      <li style="margin-bottom:8px">Trang bạn truy cập, thời gian sử dụng, hành vi điều hướng trên nền tảng.</li>
      <li style="margin-bottom:8px">Cookie và các công nghệ theo dõi tương tự.</li>
    </ul>

    <h2 style="font-size:18px;font-weight:700;color:var(--text-primary);margin:28px 0 12px">2. Mục đích sử dụng thông tin</h2>
    <ul style="margin:0 0 16px 20px;padding:0">
      <li style="margin-bottom:8px">Cung cấp và cải thiện các dịch vụ của ITWorks.</li>
      <li style="margin-bottom:8px">Kết nối ứng viên phù hợp với nhà tuyển dụng.</li>
      <li style="margin-bottom:8px">Gửi thông báo về cơ hội việc làm, cập nhật tài khoản và tin tức liên quan.</li>
      <li style="margin-bottom:8px">Xác minh danh tính và ngăn chặn gian lận.</li>
      <li style="margin-bottom:8px">Phân tích dữ liệu để nâng cao trải nghiệm người dùng.</li>
      <li style="margin-bottom:8px">Tuân thủ các yêu cầu pháp lý của Việt Nam.</li>
    </ul>

    <h2 style="font-size:18px;font-weight:700;color:var(--text-primary);margin:28px 0 12px">3. Chia sẻ thông tin</h2>
    <p style="margin-bottom:12px">ITWorks <strong>không bán</strong> thông tin cá nhân của bạn cho bên thứ ba. Chúng tôi chỉ chia sẻ thông tin trong các trường hợp sau:</p>
    <ul style="margin:0 0 16px 20px;padding:0">
      <li style="margin-bottom:8px"><strong>Với đối tác dịch vụ:</strong> Các nhà cung cấp dịch vụ kỹ thuật (lưu trữ đám mây, thanh toán) hoạt động theo hợp đồng bảo mật với ITWorks.</li>
      <li style="margin-bottom:8px"><strong>Kết nối tuyển dụng:</strong> Thông tin CV của ứng viên chỉ được chia sẻ với nhà tuyển dụng khi ứng viên chủ động nộp đơn.</li>
      <li style="margin-bottom:8px"><strong>Yêu cầu pháp lý:</strong> Khi có yêu cầu từ cơ quan nhà nước có thẩm quyền theo quy định pháp luật.</li>
    </ul>

    <h2 style="font-size:18px;font-weight:700;color:var(--text-primary);margin:28px 0 12px">4. Bảo mật dữ liệu</h2>
    <p>Chúng tôi áp dụng các biện pháp bảo mật kỹ thuật và tổ chức phù hợp để bảo vệ thông tin của bạn, bao gồm mã hóa SSL/TLS, kiểm soát truy cập nội bộ và sao lưu dữ liệu định kỳ. Tuy nhiên, không có hệ thống nào đảm bảo bảo mật tuyệt đối, và bạn có trách nhiệm bảo vệ mật khẩu tài khoản của mình.</p>

    <h2 style="font-size:18px;font-weight:700;color:var(--text-primary);margin:28px 0 12px">5. Cookie</h2>
    <p>ITWorks sử dụng cookie để duy trì phiên đăng nhập, ghi nhớ tùy chọn của bạn và phân tích lưu lượng truy cập. Bạn có thể tắt cookie trong trình duyệt, nhưng điều này có thể ảnh hưởng đến một số tính năng của nền tảng.</p>

    <h2 style="font-size:18px;font-weight:700;color:var(--text-primary);margin:28px 0 12px">6. Quyền của bạn</h2>
    <p style="margin-bottom:12px">Theo quy định pháp luật Việt Nam, bạn có quyền:</p>
    <ul style="margin:0 0 16px 20px;padding:0">
      <li style="margin-bottom:8px"><strong>Truy cập:</strong> Yêu cầu xem thông tin cá nhân chúng tôi đang lưu trữ về bạn.</li>
      <li style="margin-bottom:8px"><strong>Chỉnh sửa:</strong> Cập nhật hoặc sửa thông tin không chính xác.</li>
      <li style="margin-bottom:8px"><strong>Xóa:</strong> Yêu cầu xóa tài khoản và dữ liệu liên quan.</li>
      <li style="margin-bottom:8px"><strong>Phản đối:</strong> Từ chối nhận email marketing bằng cách nhấn "Hủy đăng ký" trong bất kỳ email nào.</li>
    </ul>

    <h2 style="font-size:18px;font-weight:700;color:var(--text-primary);margin:28px 0 12px">7. Lưu trữ dữ liệu</h2>
    <p>Chúng tôi lưu trữ thông tin cá nhân của bạn trong suốt thời gian tài khoản hoạt động và tối đa 2 năm sau khi tài khoản bị xóa, trừ trường hợp pháp luật yêu cầu lưu trữ lâu hơn.</p>

    <h2 style="font-size:18px;font-weight:700;color:var(--text-primary);margin:28px 0 12px">8. Trẻ em</h2>
    <p>Dịch vụ ITWorks không dành cho người dưới 16 tuổi. Chúng tôi không cố ý thu thập thông tin từ trẻ em. Nếu phát hiện tài khoản thuộc về người dưới 16 tuổi, chúng tôi sẽ xóa tài khoản đó ngay lập tức.</p>

    <h2 style="font-size:18px;font-weight:700;color:var(--text-primary);margin:28px 0 12px">9. Liên hệ</h2>
    <p>Nếu bạn có câu hỏi về Chính sách bảo mật hoặc muốn thực hiện quyền của mình, vui lòng liên hệ:</p>
    <ul style="margin:0 0 16px 20px;padding:0">
      <li style="margin-bottom:8px"><strong>Email:</strong> privacy@itworks.vn</li>
      <li style="margin-bottom:8px"><strong>Địa chỉ:</strong> Hà Nội, Việt Nam</li>
    </ul>

    <div style="background:#f0f7ff;border:1px solid #cce0ff;border-radius:8px;padding:16px;margin-top:24px">
      <p style="margin:0;font-size:13px;color:#1a5fb4">
        <i class="fas fa-shield-alt" style="margin-right:8px"></i>
        <strong>Cam kết của ITWorks:</strong> Chúng tôi không bao giờ bán dữ liệu người dùng và luôn ưu tiên bảo mật thông tin cá nhân của bạn.
      </p>
    </div>

  </div>

  <div style="margin-top:24px;text-align:center">
    <a href="javascript:history.back()" class="btn btn-outline" style="margin-right:12px">
      <i class="fas fa-arrow-left" style="margin-right:6px"></i> Quay lại
    </a>
    <a href="{{ route('terms') }}" class="btn btn-primary">
      Xem Điều khoản sử dụng <i class="fas fa-arrow-right" style="margin-left:6px"></i>
    </a>
  </div>

</div>
@endsection
