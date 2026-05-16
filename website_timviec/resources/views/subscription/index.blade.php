@extends('layouts.app')
@section('title', 'Nâng cấp tài khoản Premium')

@section('content')
<div style="background:linear-gradient(135deg,#00b14f,#008a3e);padding:48px 16px;text-align:center;color:#fff">
  <div class="container">
    <div style="font-size:36px;margin-bottom:12px">👑</div>
    <h1 style="font-size:28px;font-weight:800;margin-bottom:8px">Nâng cấp Premium</h1>
    <p style="font-size:15px;opacity:.85">Mở khóa toàn bộ tính năng tuyển dụng mạnh mẽ</p>
  </div>
</div>

<div class="container section">

  {{-- FEATURE LIST --}}
  <div class="text-center mb-32">
    <div class="grid-4" style="gap:16px;margin-bottom:32px">
      @foreach([
        ['fas fa-paper-plane','Đăng tin không giới hạn','Không bị hạn chế số lượng tin đăng'],
        ['fas fa-users','Xem hồ sơ ứng viên','Truy cập đầy đủ thông tin ứng viên'],
        ['fas fa-bolt','Ưu tiên hiển thị','Tin của bạn lên top tìm kiếm'],
        ['fas fa-headset','Hỗ trợ ưu tiên','Đội ngũ hỗ trợ 24/7'],
      ] as [$icon, $title, $desc])
        <div class="card" style="padding:20px;text-align:center">
          <div style="width:48px;height:48px;background:var(--primary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;color:var(--primary);font-size:18px">
            <i class="{{ $icon }}"></i>
          </div>
          <div class="fw-700 fs-14 mb-4">{{ $title }}</div>
          <div class="text-muted fs-12">{{ $desc }}</div>
        </div>
      @endforeach
    </div>
  </div>

  {{-- PLANS --}}
  <div class="text-center mb-24">
    <h2 class="fs-24 fw-800" style="color:var(--secondary)">Chọn gói phù hợp</h2>
    <p class="text-muted mt-8">Thanh toán an toàn qua VNPay</p>
  </div>

  <div class="grid-2" style="max-width:680px;margin:0 auto;gap:20px">

    {{-- MONTHLY --}}
    <div class="plan-card">
      <div class="plan-card__price"><sup>₫</sup>100K</div>
      <div class="plan-card__period">/ tháng</div>
      <div class="fw-700 fs-18 mt-12 mb-4">Gói Tháng</div>
      <p class="text-muted fs-13 mb-20">Linh hoạt, tốt cho doanh nghiệp mới bắt đầu</p>
      <ul class="plan-features">
        @foreach(['Đăng tin không giới hạn','Xem đầy đủ hồ sơ ứng viên','Shortlist ứng viên','Gửi email thông báo','Hỗ trợ qua email'] as $feature)
          <li>{{ $feature }}</li>
        @endforeach
      </ul>
      <form action="{{ url('/pay/monthly') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-outline btn-block btn-lg mt-4">Chọn gói tháng</button>
      </form>
    </div>

    {{-- YEARLY --}}
    <div class="plan-card featured">
      <div class="plan-card__badge">🔥 Tiết kiệm 33%</div>
      <div class="plan-card__price" style="color:var(--primary)"><sup>₫</sup>799K</div>
      <div class="plan-card__period">/ năm <span style="text-decoration:line-through;color:var(--text-muted);font-size:13px">1.2M</span></div>
      <div class="fw-700 fs-18 mt-12 mb-4">Gói Năm</div>
      <p class="text-muted fs-13 mb-20">Giá trị nhất, phù hợp doanh nghiệp dài hạn</p>
      <ul class="plan-features">
        @foreach(['Đăng tin không giới hạn','Xem đầy đủ hồ sơ ứng viên','Shortlist ứng viên','Gửi email thông báo','Chat trực tiếp với ứng viên','Hỗ trợ ưu tiên 24/7','Báo cáo & thống kê nâng cao'] as $feature)
          <li>{{ $feature }}</li>
        @endforeach
      </ul>
      <form action="{{ url('/pay/yearly') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary btn-block btn-lg mt-4">
          <i class="fas fa-crown"></i> Chọn gói năm
        </button>
      </form>
    </div>
  </div>

  {{-- GUARANTEE --}}
  <div class="text-center mt-32">
    <div class="flex-center gap-24" style="flex-wrap:wrap">
      <div class="flex gap-8" style="align-items:center;font-size:13px;color:var(--text-secondary)">
        <i class="fas fa-shield-alt" style="color:var(--primary)"></i> Thanh toán bảo mật qua VNPay
      </div>
      <div class="flex gap-8" style="align-items:center;font-size:13px;color:var(--text-secondary)">
        <i class="fas fa-undo" style="color:var(--primary)"></i> Hoàn tiền trong 7 ngày nếu không hài lòng
      </div>
      <div class="flex gap-8" style="align-items:center;font-size:13px;color:var(--text-secondary)">
        <i class="fas fa-lock" style="color:var(--primary)"></i> Dữ liệu được mã hóa SSL
      </div>
    </div>
  </div>
</div>
@endsection
