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
          <img src="{{ auth()->user()->avatar_url }}" class="avatar avatar-sm" alt="">
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
        <img src="{{ auth()->user()->avatar_url }}" class="avatar avatar-lg" style="margin:0 auto 8px" alt="">
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
        {{-- ===== DANH MỤC: NHÀ TUYỂN DỤNG ===== --}}
        <div class="dash-nav__section">Tuyển dụng</div>
        <a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">
          <i class="fas fa-chart-pie fa-fw"></i> Thống kê tuyển dụng
        </a>
        <a href="{{ url('/job/manage') }}" class="{{ request()->is('job/manage') || request()->is('job/create') || request()->is('job/edit*') ? 'active' : '' }}">
          <i class="fas fa-clipboard-list fa-fw"></i> Quản lý Tin tuyển dụng
        </a>
        <a href="{{ url('/applicants') }}" class="{{ request()->is('applicants*') ? 'active' : '' }}">
          <i class="fas fa-user-tie fa-fw"></i> Quản lý Ứng viên
        </a>
        <div class="dash-nav__section">Tài khoản</div>
        <a href="{{ url('/subscribe') }}" class="{{ request()->is('subscribe') ? 'active' : '' }}">
          <i class="fas fa-crown fa-fw"></i> Nâng cấp tài khoản
        </a>
        <a href="{{ url('/messages') }}" class="{{ request()->is('messages*') ? 'active' : '' }}">
          <i class="fas fa-comment-dots fa-fw"></i> Tin nhắn
        </a>
        <a href="{{ url('/user/profile') }}" class="{{ request()->is('user/profile') ? 'active' : '' }}">
          <i class="fas fa-building fa-fw"></i> Hồ sơ công ty
        </a>

      @elseif(auth()->user()->user_type === 'admin')
        {{-- ===== DANH MỤC: ADMIN ===== --}}
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
        {{-- ===== DANH MỤC: ỨNG VIÊN ===== --}}
        <div class="dash-nav__section">Cá nhân</div>
        <a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">
          <i class="fas fa-th-large fa-fw"></i> Tổng quan cá nhân
        </a>
        <a href="{{ url('/user/cv') }}" class="{{ request()->is('user/cv') || request()->is('user/cv/create') ? 'active' : '' }}">
          <i class="fas fa-folder-open fa-fw"></i> Quản lý CV &amp; Hồ sơ
        </a>
        <a href="{{ url('/applicants') }}" class="{{ request()->is('applicants*') ? 'active' : '' }}">
          <i class="fas fa-tasks fa-fw"></i> Theo dõi ứng tuyển
        </a>
        <div class="dash-nav__section">Khám phá</div>
        <a href="{{ url('/job') }}" class="{{ request()->is('job') ? 'active' : '' }}">
          <i class="fas fa-search fa-fw"></i> Khám phá việc làm
        </a>
        <div class="dash-nav__section">Tài khoản</div>
        <a href="{{ url('/subscribe') }}" class="{{ request()->is('subscribe') ? 'active' : '' }}">
          <i class="fas fa-crown fa-fw"></i> Nâng cấp tài khoản
        </a>
        <a href="{{ url('/messages') }}" class="{{ request()->is('messages*') ? 'active' : '' }}">
          <i class="fas fa-comment-dots fa-fw"></i> Tin nhắn
        </a>
        <a href="{{ url('/user/profile') }}" class="{{ request()->is('user/profile') ? 'active' : '' }}">
          <i class="fas fa-user-cog fa-fw"></i> Thông tin cá nhân
        </a>
      @endif

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
</body>
</html>