<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') — ITWorks</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  @stack('styles')
</head>
<body>

<nav class="navbar">
  <div class="container navbar-inner">
    <a href="{{ url('/') }}" class="navbar-brand">IT<span>Works</span></a>
    <div class="navbar-actions" style="margin-left:auto">
      <a href="{{ url('/') }}" class="btn btn-outline btn-sm"><i class="fas fa-home"></i> Trang chủ</a>
      <div style="position:relative;cursor:pointer" onclick="toggleDropdown()">
        @if(auth()->user()->profile_pic)
          <img src="{{ asset('storage/images/'.auth()->user()->profile_pic) }}" class="avatar avatar-sm" alt="">
        @else
          <div class="avatar avatar-sm avatar-placeholder" style="font-size:13px;background:var(--primary-light);color:var(--primary)">
            {{ strtoupper(substr(auth()->user()->name,0,1)) }}
          </div>
        @endif
        <div id="user-dropdown" style="display:none;position:absolute;right:0;top:40px;background:#fff;border:1px solid var(--border);border-radius:var(--radius-lg);box-shadow:var(--shadow-lg);min-width:180px;z-index:200;overflow:hidden">
          <div style="padding:12px 16px;border-bottom:1px solid var(--border)">
            <div class="fw-600 fs-13">{{ auth()->user()->name }}</div>
            <div class="text-muted fs-12">{{ auth()->user()->user_type === 'employer' ? 'Nhà tuyển dụng' : 'Ứng viên' }}</div>
          </div>
          <a href="{{ url('/user/profile') }}" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:14px;color:var(--text-secondary)" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background=''">
            <i class="fas fa-user fa-fw"></i> Tài khoản
          </a>
          <form action="{{ url('/logout') }}" method="POST">
            @csrf
            <button type="submit" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:14px;color:#dc3545;width:100%" onmouseover="this.style.background='#fdf0f0'" onmouseout="this.style.background=''">
              <i class="fas fa-sign-out-alt fa-fw"></i> Đăng xuất
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</nav>

<div class="dash-layout">
  {{-- SIDEBAR --}}
  <aside class="dash-sidebar">
    <div style="padding:16px;text-align:center;border-bottom:1px solid var(--border)">
      @if(auth()->user()->profile_pic)
        <img src="{{ asset('storage/images/'.auth()->user()->profile_pic) }}" class="avatar avatar-lg" style="margin:0 auto 8px" alt="">
      @else
        <div class="avatar avatar-lg avatar-placeholder" style="margin:0 auto 8px;font-size:24px;background:var(--primary-light);color:var(--primary)">
          {{ strtoupper(substr(auth()->user()->name,0,1)) }}
        </div>
      @endif
      <div class="fw-600 fs-13">{{ auth()->user()->name }}</div>
      <div class="text-muted" style="font-size:11px">{{ auth()->user()->email }}</div>
      @if(auth()->user()->user_type === 'employer')
        <div class="tag tag-green mt-8" style="font-size:11px;margin:6px auto 0;display:inline-flex">
          @if(auth()->user()->billing_ends && auth()->user()->billing_ends > now())
            <i class="fas fa-crown" style="margin-right:4px"></i> Premium
          @elseif(auth()->user()->user_trial && auth()->user()->user_trial > now())
            <i class="fas fa-clock" style="margin-right:4px"></i> Dùng thử
          @else
            Nhà tuyển dụng
          @endif
        </div>
      @endif
    </div>

    <nav class="dash-nav" style="margin-top:8px">
      @if(auth()->user()->user_type === 'employer')
        <div class="dash-nav__section">Tổng quan</div>
        <a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">
          <i class="fas fa-chart-bar fa-fw"></i> Dashboard
        </a>
        <div class="dash-nav__section">Tuyển dụng</div>
        <a href="{{ url('/job') }}" class="{{ request()->is('job') ? 'active' : '' }}">
          <i class="fas fa-briefcase fa-fw"></i> Tin đăng của tôi
        </a>
        <a href="{{ url('/job/create') }}" class="{{ request()->is('job/create') ? 'active' : '' }}">
          <i class="fas fa-plus-circle fa-fw"></i> Đăng tin mới
        </a>
        <a href="{{ url('/applicants') }}" class="{{ request()->is('applicants') ? 'active' : '' }}">
          <i class="fas fa-users fa-fw"></i> Ứng viên
        </a>
        <div class="dash-nav__section">Tài khoản</div>
        <a href="{{ route('payment.subscription') }}" class="{{ request()->is('payment/subscription') ? 'active' : '' }}">
          <i class="fas fa-crown fa-fw"></i> Gói premium
        </a>
        <a href="{{ url('/messages') }}" class="{{ request()->is('messages*') ? 'active' : '' }}">
          <i class="fas fa-comment-dots fa-fw"></i> Tin nhắn
        </a>
      @elseif(auth()->user()->user_type === 'admin')
        <div class="dash-nav__section">Tổng quan</div>
        <a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">
          <i class="fas fa-home fa-fw"></i> Dashboard Admin
        </a>
        <div class="dash-nav__section" style="color:#ef4444;"><i class="fas fa-shield-alt"></i> Khu vực quản trị</div>
        <a href="{{ url('/admin') }}" style="color:#ef4444; font-weight:700;">
          <i class="fas fa-cog fa-fw"></i> Bảng điều khiển Admin
        </a>
        <a href="{{ url('/admin/users') }}" style="color:#ef4444; font-weight:700;">
          <i class="fas fa-users fa-fw"></i> Quản lý & Phân quyền
        </a>
      @else
        <div class="dash-nav__section">Của tôi</div>
        <a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">
          <i class="fas fa-home fa-fw"></i> Tổng quan
        </a>
        <a href="{{ url('/applicants') }}" class="{{ request()->is('applicants') ? 'active' : '' }}">
          <i class="fas fa-file-alt fa-fw"></i> Việc đã ứng tuyển
        </a>
        <a href="{{ url('/messages') }}" class="{{ request()->is('messages*') ? 'active' : '' }}">
          <i class="fas fa-comment-dots fa-fw"></i> Tin nhắn
        </a>
        <div class="dash-nav__section">Hồ sơ</div>
        <a href="{{ url('/user/cv') }}" class="{{ request()->is('user/cv') ? 'active' : '' }}">
          <i class="fas fa-upload fa-fw"></i> Upload CV
        </a>
        <a href="{{ url('/user/cv/create') }}" class="{{ request()->is('user/cv/create') ? 'active' : '' }}">
          <i class="fas fa-magic fa-fw"></i> Tạo CV online
        </a>
      @endif
      <div class="dash-nav__section">Cài đặt</div>
      <a href="{{ url('/user/profile') }}" class="{{ request()->is('user/profile') ? 'active' : '' }}">
        <i class="fas fa-user-cog fa-fw"></i> Thông tin cá nhân
      </a>
    </nav>
  </aside>

  {{-- CONTENT --}}
  <div class="dash-content">
    @if(session('success'))
      <div class="alert alert-success mb-16"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger mb-16"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif
    @if(session('warning'))
      <div class="alert alert-warning mb-16"><i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}</div>
    @endif
    @if(session('info'))
      <div class="alert alert-info mb-16"><i class="fas fa-info-circle"></i> {{ session('info') }}</div>
    @endif
    @yield('content')
  </div>
</div>

<script>
function toggleDropdown() {
  var d = document.getElementById('user-dropdown');
  d.style.display = d.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', function(e) {
  if (!e.target.closest('[onclick="toggleDropdown()"]')) {
    var d = document.getElementById('user-dropdown');
    if (d) d.style.display = 'none';
  }
});
</script>
@stack('scripts')

@if(session('show_welcome_modal'))
<div id="welcome-modal" style="display:flex;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:9999;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:20px;padding:44px 40px;max-width:500px;width:90%;text-align:center;box-shadow:0 24px 64px rgba(0,0,0,0.18)">
    <div style="font-size:52px;margin-bottom:16px">🎉</div>
    <h2 class="fw-800 fs-22" style="color:#1a1a1a;margin-bottom:12px">Chào mừng bạn đến với ITWorks!</h2>
    <p class="fs-14 text-muted" style="line-height:1.75;margin-bottom:24px">
      Tài khoản nhà tuyển dụng của bạn đã được tạo thành công.<br><br>
      Với tài khoản <strong>Free</strong>, bạn có thể đăng tối đa <strong>3 tin tuyển dụng/tháng</strong>.
      Nâng cấp <strong>Premium</strong> để đăng không giới hạn và tiếp cận nhiều ứng viên hơn!
    </p>
    <div style="background:#f8fafc;border-radius:12px;padding:20px;margin-bottom:24px;text-align:left">
      <div class="fw-700 fs-13" style="margin-bottom:12px;color:#333">✨ Premium bao gồm:</div>
      <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px">
        <li class="fs-13" style="color:#555"><i class="fas fa-check" style="color:#00B14F;margin-right:8px"></i>Đăng tin không giới hạn mỗi tháng</li>
        <li class="fs-13" style="color:#555"><i class="fas fa-check" style="color:#00B14F;margin-right:8px"></i>Hiển thị ưu tiên top danh sách</li>
        <li class="fs-13" style="color:#555"><i class="fas fa-check" style="color:#00B14F;margin-right:8px"></i>Thống kê ứng viên nâng cao</li>
        <li class="fs-13" style="color:#555"><i class="fas fa-check" style="color:#00B14F;margin-right:8px"></i>Badge "Premium" trên mỗi tin đăng</li>
      </ul>
    </div>
    <div style="display:flex;gap:12px;justify-content:center">
      <button onclick="document.getElementById('welcome-modal').style.display='none'" class="btn btn-outline">Dùng miễn phí trước</button>
      <a href="{{ route('payment.subscription') }}" class="btn btn-primary" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
        <i class="fas fa-crown"></i> Xem gói Premium
      </a>
    </div>
  </div>
</div>
@endif
</body>
</html>
