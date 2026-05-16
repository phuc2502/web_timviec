<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin') — ITWorks</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  @stack('styles')
</head>
<body>

<div class="admin-layout">
  {{-- ADMIN SIDEBAR --}}
  <aside class="admin-sidebar">
    <div class="admin-sidebar__brand"><i class="fas fa-shield-alt"></i> Admin Panel</div>
    <nav class="admin-nav" style="margin-top:8px">
      <div class="admin-nav__section">Tổng quan</div>
      <a href="{{ url('/admin') }}" class="{{ request()->is('admin') ? 'active' : '' }}">
        <i class="fas fa-chart-bar fa-fw"></i> Dashboard
      </a>
      <div class="admin-nav__section">Quản lý</div>
      <a href="{{ url('/admin/users') }}" class="{{ request()->is('admin/users*') ? 'active' : '' }}">
        <i class="fas fa-users fa-fw"></i> Người dùng
      </a>
      <a href="{{ url('/admin/jobs') }}" class="{{ request()->is('admin/jobs*') ? 'active' : '' }}">
        <i class="fas fa-briefcase fa-fw"></i> Tin tuyển dụng
      </a>
      <a href="{{ url('/admin/transactions') }}" class="{{ request()->is('admin/transactions*') ? 'active' : '' }}">
        <i class="fas fa-credit-card fa-fw"></i> Giao dịch
      </a>
      <div class="admin-nav__section">Hệ thống</div>
      <a href="{{ url('/') }}" target="_blank">
        <i class="fas fa-external-link-alt fa-fw"></i> Xem website
      </a>
      <form action="{{ url('/logout') }}" method="POST" style="margin:0">
        @csrf
        <button type="submit" style="display:flex;align-items:center;gap:10px;padding:12px 20px;color:rgba(255,255,255,.7);font-size:14px;font-weight:500;width:100%;border:none;background:none;cursor:pointer;font-family:inherit" onmouseover="this.style.background='rgba(220,53,69,.2)';this.style.color='#fff'" onmouseout="this.style.background='';this.style.color='rgba(255,255,255,.7)'">
          <i class="fas fa-sign-out-alt fa-fw"></i> Đăng xuất
        </button>
      </form>
    </nav>
  </aside>

  {{-- MAIN --}}
  <div style="flex:1;display:flex;flex-direction:column;min-height:100vh">
    <div class="admin-topbar">
      <div class="fw-700 fs-16">@yield('title', 'Dashboard')</div>
      <div class="flex gap-12" style="align-items:center">
        <span class="text-muted fs-13">{{ auth()->user()->name }}</span>
        <div class="avatar avatar-sm avatar-placeholder" style="background:var(--primary-light);color:var(--primary);font-size:13px">
          {{ strtoupper(substr(auth()->user()->name,0,1)) }}
        </div>
      </div>
    </div>
    <div class="admin-content">
      @if(session('success'))
        <div class="alert alert-success mb-16"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger mb-16"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
      @endif
      @yield('content')
    </div>
  </div>
</div>

@stack('scripts')
</body>
</html>
