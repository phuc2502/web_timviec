@extends('layouts.app')

@section('title', 'Gói Premium Nhà tuyển dụng')

@section('content')
<div class="container section" style="max-width:860px">

  <div class="mb-24">
    <h1 class="fw-700 fs-24" style="color:var(--secondary)">👑 Gói Premium cho Nhà tuyển dụng</h1>
    <p class="text-muted fs-13 mt-8">Đăng tin không giới hạn và tiếp cận ứng viên chất lượng cao</p>
  </div>

  @if(session('error'))
    <div class="alert alert-danger mb-16">⚠️ {{ session('error') }}</div>
  @endif

  @if($status['has_active'])
    <div class="alert alert-success mb-24" style="display:flex;align-items:center;justify-content:space-between">
      <div>
        <strong><i class="fas fa-check-circle fa-fw"></i> Đang dùng gói: {{ ucfirst($status['plan']) }}</strong>
        <div class="fs-13 mt-8">Hết hạn: <strong>{{ $status['billing_ends'] }}</strong> · Còn <strong>{{ $status['days_remaining'] }} ngày</strong></div>
      </div>
      <a href="{{ route('employer.subscription.status') }}" class="btn btn-outline btn-sm">Xem chi tiết</a>
    </div>
    <div class="alert alert-warning mb-24">
      <i class="fas fa-info-circle fa-fw"></i> Bạn đang có gói đang hoạt động. Chỉ có thể mua gói mới sau khi hết hạn.
    </div>
  @endif

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">

    {{-- Gói tháng --}}
    <div style="border:2px solid var(--border);border-radius:var(--radius-xl);overflow:hidden;background:#fff;box-shadow:var(--shadow-md)">
      <div style="height:6px;background:var(--text-secondary)"></div>
      <div style="padding:28px">
        <div style="font-size:36px;margin-bottom:12px">📅</div>
        <div class="fw-700 fs-20" style="color:var(--secondary)">Gói Tháng</div>
        <div style="margin:12px 0">
          <span class="fw-700" style="font-size:32px;color:var(--primary)">299.000</span>
          <span class="text-muted fs-13">đ / tháng</span>
        </div>
        <div style="border-top:1px solid var(--border);padding-top:16px;margin-top:16px">
          @foreach(['Đăng tin tuyển dụng không giới hạn','Xem hồ sơ ứng viên đầy đủ','Quản lý ứng viên nâng cao','Hỗ trợ ưu tiên'] as $feature)
            <div class="fs-13 mb-8" style="display:flex;align-items:center;gap:8px">
              <i class="fas fa-check-circle" style="color:var(--primary)"></i> {{ $feature }}
            </div>
          @endforeach
        </div>
        @if(!$status['has_active'])
          <form action="{{ route('payment.subscription.initiate') }}" method="POST" class="mt-16">
            @csrf
            <input type="hidden" name="plan" value="monthly">
            <button type="submit" class="btn btn-outline btn-block">Mua gói tháng →</button>
          </form>
        @endif
      </div>
    </div>

    {{-- Gói năm --}}
    <div style="border:2px solid var(--primary);border-radius:var(--radius-xl);overflow:hidden;background:#fff;box-shadow:var(--shadow-lg);position:relative">
      <div style="background:var(--primary);color:#fff;text-align:center;font-size:12px;font-weight:600;padding:6px;letter-spacing:.5px">
        🔥 TIẾT KIỆM 17% — PHỔ BIẾN NHẤT
      </div>
      <div style="padding:28px">
        <div style="font-size:36px;margin-bottom:12px">🌟</div>
        <div class="fw-700 fs-20" style="color:var(--secondary)">Gói Năm</div>
        <div style="margin:12px 0">
          <span class="fw-700" style="font-size:32px;color:var(--primary)">2.990.000</span>
          <span class="text-muted fs-13">đ / năm</span>
        </div>
        <div class="fs-12 text-muted mb-8">≈ 249.167đ/tháng</div>
        <div style="border-top:1px solid var(--border);padding-top:16px;margin-top:16px">
          @foreach(['Tất cả tính năng gói Tháng','Ưu tiên hiển thị tin tuyển dụng','Báo cáo & thống kê chi tiết','Huy hiệu nhà tuyển dụng uy tín'] as $feature)
            <div class="fs-13 mb-8" style="display:flex;align-items:center;gap:8px">
              <i class="fas fa-check-circle" style="color:var(--primary)"></i> {{ $feature }}
            </div>
          @endforeach
        </div>
        @if(!$status['has_active'])
          <form action="{{ route('payment.subscription.initiate') }}" method="POST" class="mt-16">
            @csrf
            <input type="hidden" name="plan" value="yearly">
            <button type="submit" class="btn btn-primary btn-block">Mua gói năm →</button>
          </form>
        @endif
      </div>
    </div>

  </div>

  <div class="text-center mt-24" style="color:var(--text-secondary);font-size:12px">
    <i class="fas fa-lock fa-fw" style="color:var(--primary)"></i>
    Thanh toán an toàn qua <strong>VNPay</strong>. Gói được kích hoạt ngay sau khi thanh toán thành công.
  </div>

</div>
@endsection
