@extends('layouts.app')
@section('title', 'Chọn loại tài khoản')

@section('content')
<div style="min-height:calc(100vh - 60px);display:flex;align-items:center;background:linear-gradient(135deg,#f0fdf7 0%,#e8f4fd 100%);padding:40px 16px">
  <div style="width:100%;max-width:640px;margin:0 auto">

    <div class="text-center mb-32">
      <a href="{{ url('/') }}" class="navbar-brand" style="font-size:28px;justify-content:center">IT<span>Works</span></a>
      <h1 style="font-size:24px;font-weight:800;margin-top:16px;color:var(--secondary)">Bạn muốn đăng ký với tư cách?</h1>
      <p class="text-muted mt-8">Chọn loại tài khoản phù hợp với bạn</p>
    </div>

    <div class="grid-2" style="gap:20px">
      {{-- EMPLOYEE --}}
      <a href="{{ url('/register/employee') }}" class="card" style="padding:32px 24px;text-align:center;transition:var(--transition);display:block;text-decoration:none" onmouseover="this.style.borderColor='var(--primary)';this.style.transform='translateY(-4px)';this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.borderColor='var(--border)';this.style.transform='';this.style.boxShadow='var(--shadow-sm)'">
        <div style="width:72px;height:72px;background:var(--primary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;color:var(--primary)">
          <i class="fas fa-user-tie"></i>
        </div>
        <h3 style="font-size:18px;font-weight:700;color:var(--secondary);margin-bottom:8px">Ứng viên</h3>
        <p class="text-muted" style="font-size:13px;line-height:1.6">Tìm việc làm IT phù hợp, xây dựng CV chuyên nghiệp và kết nối với nhà tuyển dụng hàng đầu.</p>
        <div class="btn btn-primary btn-block mt-16">
          <i class="fas fa-search"></i> Tìm việc làm
        </div>
      </a>

      {{-- EMPLOYER --}}
      <a href="{{ url('/register/employer') }}" class="card" style="padding:32px 24px;text-align:center;transition:var(--transition);display:block;text-decoration:none" onmouseover="this.style.borderColor='var(--primary)';this.style.transform='translateY(-4px)';this.style.boxShadow='var(--shadow-lg)'" onmouseout="this.style.borderColor='var(--border)';this.style.transform='';this.style.boxShadow='var(--shadow-sm)'">
        <div style="width:72px;height:72px;background:#fff3e0;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px;color:#f57c00">
          <i class="fas fa-building"></i>
        </div>
        <h3 style="font-size:18px;font-weight:700;color:var(--secondary);margin-bottom:8px">Nhà tuyển dụng</h3>
        <p class="text-muted" style="font-size:13px;line-height:1.6">Đăng tin tuyển dụng, tìm kiếm nhân tài IT xuất sắc cho công ty của bạn một cách hiệu quả.</p>
        <div class="btn btn-secondary btn-block mt-16" style="background:var(--secondary)">
          <i class="fas fa-plus-circle"></i> Tuyển dụng ngay
        </div>
      </a>
    </div>

    <p class="text-center text-muted fs-13 mt-24">
      Đã có tài khoản? <a href="{{ url('/login') }}" class="text-primary-color fw-600">Đăng nhập</a>
    </p>
  </div>
</div>
@endsection
