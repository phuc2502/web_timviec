<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') — ITWorks</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  @stack('styles')
</head>
<body>

<nav class="navbar">
  <div class="container navbar-inner">
    <a href="{{ url('/') }}" class="navbar-brand">IT<span>Works</span></a>
    <div class="navbar-actions" style="margin-left:auto">
      <a href="{{ url('/') }}" class="btn btn-ghost btn-sm"><i class="fas fa-home"></i> Trang chủ</a>
      @include('partials.notification-bell')
      <div style="position:relative;cursor:pointer" onclick="toggleDropdown()">
        @if(auth()->user()->profile_pic)
          <img src="{{ asset('storage/images/'.auth()->user()->profile_pic) }}" class="avatar avatar-sm" alt="" style="border:2px solid var(--border)">
        @else
          <div class="avatar avatar-sm avatar-placeholder">
            {{ strtoupper(substr(auth()->user()->name,0,1)) }}
          </div>
        @endif
        <div id="user-dropdown" style="display:none;position:absolute;right:0;top:44px;min-width:200px;z-index:200" class="dropdown-menu">
          <div class="dropdown-header">
            <div style="font-family:var(--font-display);font-weight:700;font-size:13px;color:var(--text-dark)">{{ auth()->user()->name }}</div>
            <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px">{{ auth()->user()->user_type === 'employer' ? 'Nhà tuyển dụng' : 'Ứng viên' }}</div>
          </div>
          <a href="{{ url('/user/profile') }}" class="dropdown-item"><i class="fas fa-user-circle"></i> Tài khoản</a>
          <div class="dropdown-divider"></div>
          <form action="{{ url('/logout') }}" method="POST">
            @csrf
            <button type="submit" class="dropdown-item danger"><i class="fas fa-sign-out-alt"></i> Đăng xuất</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</nav>

<div class="dash-layout">
  {{-- SIDEBAR --}}
  <aside class="dash-sidebar">
    <div class="dash-sidebar__profile">
      @if(auth()->user()->profile_pic)
        <img src="{{ asset('storage/images/'.auth()->user()->profile_pic) }}" class="avatar avatar-lg" style="margin:0 auto 12px;border:3px solid rgba(0,217,126,0.25)" alt="">
      @else
        <div class="avatar avatar-lg avatar-placeholder" style="margin:0 auto 12px;font-size:28px">
          {{ strtoupper(substr(auth()->user()->name,0,1)) }}
        </div>
      @endif
      <div style="font-family:var(--font-display);font-weight:700;font-size:14px;color:var(--text-dark)">{{ auth()->user()->name }}</div>
      <div style="font-size:12px;color:var(--text-muted);margin-top:3px">{{ auth()->user()->email }}</div>
      @if(auth()->user()->user_type === 'employer')
        <div class="badge badge-primary mt-8">
          @if(auth()->user()->billing_ends && auth()->user()->billing_ends > now())
            <i class="fas fa-crown" style="color:var(--pro-gold)"></i> Premium
          @elseif(auth()->user()->user_trial && auth()->user()->user_trial > now())
            <i class="fas fa-clock"></i> Dùng thử
          @else
            Nhà tuyển dụng
          @endif
        </div>
      @endif
    </div>

    <nav class="dash-nav">
      @if(auth()->user()->user_type === 'employer')
        <div class="dash-nav__section">Tổng quan</div>
        <a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">
          <i class="fas fa-chart-bar fa-fw"></i> Dashboard
        </a>
        <div class="dash-nav__section">Tuyển dụng</div>
        <a href="{{ route('job.manage') }}" class="{{ request()->is('job/manage') ? 'active' : '' }}">
          <i class="fas fa-briefcase fa-fw"></i> Quản lý tin đăng
        </a>
        <a href="{{ route('job.create') }}" class="{{ request()->is('job/create') ? 'active' : '' }}">
          <i class="fas fa-plus-circle fa-fw"></i> Đăng tin mới
        </a>
        <a href="{{ url('/applicants') }}" class="{{ request()->is('applicants') ? 'active' : '' }}">
          <i class="fas fa-users fa-fw"></i> Ứng viên
        </a>
        <div class="dash-nav__section">Tài khoản</div>
        <a href="{{ url('/user/profile') }}" class="{{ request()->is('user/profile') ? 'active' : '' }}">
          <i class="fas fa-user-circle fa-fw"></i> Hồ sơ cá nhân
        </a>
        <a href="{{ route('employer.subscription.status') }}" class="{{ request()->is('payment/subscription/status') ? 'active' : '' }}">
          <i class="fas fa-crown fa-fw" style="color:var(--pro-gold)"></i> Gói Premium
        </a>
      @else
        <div class="dash-nav__section">Ứng viên</div>
        <a href="{{ route('candidate.history') }}" class="{{ request()->is('application/history') ? 'active' : '' }}">
          <i class="fas fa-history fa-fw"></i> Lịch sử ứng tuyển
        </a>
        <a href="{{ route('user.cv') }}" class="{{ request()->is('user/cv') ? 'active' : '' }}">
          <i class="fas fa-file-alt fa-fw"></i> Hồ sơ CV
        </a>
        <div class="dash-nav__section">Tài khoản</div>
        <a href="{{ url('/user/profile') }}" class="{{ request()->is('user/profile') ? 'active' : '' }}">
          <i class="fas fa-user-circle fa-fw"></i> Tài khoản
        </a>
        <a href="{{ route('payment.token') }}" class="{{ request()->is('payment/token') ? 'active' : '' }}">
          <i class="fas fa-ticket-alt fa-fw"></i> Mua lượt ứng tuyển
        </a>
      @endif
      <div class="dash-nav__section">Hệ thống</div>
      <a href="{{ url('/messages') }}"><i class="fas fa-comment-dots fa-fw"></i> Tin nhắn</a>
      <a href="#" onclick="event.preventDefault();document.getElementById('logout-form').submit()">
        <i class="fas fa-sign-out-alt fa-fw"></i> Đăng xuất
      </a>
    </nav>
    <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display:none">@csrf</form>
  </aside>

  {{-- MAIN CONTENT --}}
  <div class="dash-content">
    @if(session('success') || session('error') || session('warning'))
      @if(session('success'))
        <div class="alert alert-success mb-16"><i class="fas fa-check-circle" style="flex-shrink:0"></i> {{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger mb-16"><i class="fas fa-exclamation-circle" style="flex-shrink:0"></i> {{ session('error') }}</div>
      @endif
      @if(session('warning'))
        <div class="alert alert-warning mb-16"><i class="fas fa-exclamation-triangle" style="flex-shrink:0"></i> {{ session('warning') }}</div>
      @endif
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
