<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'IT Works — Tìm Việc IT Hàng Đầu')</title>
  <meta name="description" content="@yield('description', 'Nền tảng tuyển dụng IT hàng đầu Việt Nam. Hàng nghìn việc làm IT đang chờ bạn.')">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  @stack('styles')
</head>
<body>

{{-- NAVBAR --}}
<nav class="navbar">
  <div class="container navbar-inner">
    <a href="{{ url('/') }}" class="navbar-brand">IT<span>Works</span></a>
    <div class="navbar-nav">
      <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">Trang chủ</a>
      @auth
        @if(auth()->user()->user_type === 'employer')
          {{-- ===== NAV: NHÀ TUYỂN DỤNG ===== --}}
          <a href="{{ url('/dashboard') }}" class="{{ request()->is('dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-pie fa-fw" style="margin-right:4px"></i>Thống kê
          </a>
          <div class="nav-dropdown" id="nd-recruiter">
            <a href="#" class="nav-dropdown__trigger {{ request()->is('job/manage') || request()->is('job/create') || request()->is('job/edit*') ? 'active' : '' }}" onclick="toggleNavDropdown('nd-recruiter');return false;">
              Tin tuyển dụng <i class="fas fa-chevron-down" style="font-size:10px;margin-left:4px"></i>
            </a>
            <div class="nav-dropdown__menu">
              <a href="{{ url('/job/manage') }}"><i class="fas fa-list-alt fa-fw"></i> Quản lý Tin tuyển dụng</a>
              <a href="{{ url('/job/create') }}"><i class="fas fa-plus-circle fa-fw"></i> Đăng tin mới</a>
            </div>
          </div>
          <a href="{{ url('/applicants') }}" class="{{ request()->is('applicants*') ? 'active' : '' }}">
            <i class="fas fa-user-tie fa-fw" style="margin-right:4px"></i>Ứng viên
          </a>
          <a href="{{ url('/messages') }}" class="{{ request()->is('messages*') ? 'active' : '' }}">
            <i class="fas fa-comment-dots fa-fw" style="margin-right:4px"></i>Tin nhắn
          </a>
        @else
          {{-- ===== NAV: ỨNG VIÊN ===== --}}
          <a href="{{ url('/job') }}" class="{{ request()->is('job') ? 'active' : '' }}">
            <i class="fas fa-search fa-fw" style="margin-right:4px"></i>Khám phá việc làm
          </a>
          <div class="nav-dropdown" id="nd-employee">
            <a href="#" class="nav-dropdown__trigger {{ request()->is('user/cv') || request()->is('user/cv/create') ? 'active' : '' }}" onclick="toggleNavDropdown('nd-employee');return false;">
              Hồ sơ & CV <i class="fas fa-chevron-down" style="font-size:10px;margin-left:4px"></i>
            </a>
            <div class="nav-dropdown__menu">
              <a href="{{ url('/user/cv') }}"><i class="fas fa-folder-open fa-fw"></i> Quản lý CV & Hồ sơ</a>
              <a href="{{ url('/user/cv/create') }}"><i class="fas fa-magic fa-fw"></i> Tạo CV online</a>
            </div>
          </div>
          <a href="{{ url('/applicants') }}" class="{{ request()->is('applicants*') ? 'active' : '' }}">
            <i class="fas fa-tasks fa-fw" style="margin-right:4px"></i>Theo dõi ứng tuyển
          </a>
          <a href="{{ url('/messages') }}" class="{{ request()->is('messages*') ? 'active' : '' }}">
            <i class="fas fa-comment-dots fa-fw" style="margin-right:4px"></i>Tin nhắn
          </a>
        @endif
      @else
        <a href="{{ url('/job') }}" class="{{ request()->is('job') ? 'active' : '' }}">Tìm việc</a>
      @endauth
    </div>

    <div class="navbar-actions">
      @auth
        <div class="flex gap-8" style="align-items:center">
          @if(auth()->user()->user_type === 'employer' && !auth()->user()->billing_ends)
            <a href="{{ url('/subscribe') }}" class="btn btn-outline btn-sm">
              <i class="fas fa-crown"></i> Nâng cấp
            </a>
          @elseif(auth()->user()->user_type !== 'employer' && !auth()->user()->billing_ends)
            <a href="{{ url('/subscribe') }}" class="btn btn-outline btn-sm" style="font-size:12px">
              <i class="fas fa-crown"></i> Nâng cấp
            </a>
          @endif
          <div style="position:relative;cursor:pointer" onclick="toggleDropdown()">
            @if(auth()->user()->profile_pic)
              <img src="{{ auth()->user()->avatar_url }}" class="avatar avatar-sm" alt="avatar">
            @else
              <div class="avatar avatar-sm avatar-placeholder" style="font-size:13px">
                {{ strtoupper(substr(auth()->user()->name,0,1)) }}
              </div>
            @endif
            <div id="user-dropdown" style="display:none;position:absolute;right:0;top:44px;background:#fff;border:1px solid var(--border);border-radius:var(--radius-lg);box-shadow:var(--shadow-lg);min-width:200px;z-index:200;overflow:hidden">
              <div style="padding:12px 16px;border-bottom:1px solid var(--border)">
                <div class="fw-600 fs-13">{{ auth()->user()->name }}</div>
                <div class="text-muted fs-12">{{ auth()->user()->email }}</div>
                @if(auth()->user()->user_type === 'employer')
                  <span style="font-size:10px;background:#eff6ff;color:#1e40af;padding:2px 8px;border-radius:10px;display:inline-block;margin-top:4px;font-weight:600">
                    <i class="fas fa-building" style="margin-right:3px"></i>Nhà tuyển dụng
                  </span>
                @else
                  <span style="font-size:10px;background:#f0fdf4;color:#16a34a;padding:2px 8px;border-radius:10px;display:inline-block;margin-top:4px;font-weight:600">
                    <i class="fas fa-user" style="margin-right:3px"></i>Ứng viên
                  </span>
                @endif
              </div>
              @if(auth()->user()->user_type === 'employer')
                <a href="{{ url('/dashboard') }}" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:13px;color:var(--text-secondary);transition:var(--transition)" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background=''">
                  <i class="fas fa-chart-pie fa-fw"></i> Thống kê tuyển dụng
                </a>
                <a href="{{ url('/job/manage') }}" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:13px;color:var(--text-secondary);transition:var(--transition)" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background=''">
                  <i class="fas fa-clipboard-list fa-fw"></i> Quản lý Tin tuyển dụng
                </a>
                <a href="{{ url('/applicants') }}" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:13px;color:var(--text-secondary);transition:var(--transition)" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background=''">
                  <i class="fas fa-user-tie fa-fw"></i> Quản lý Ứng viên
                </a>
                <div style="border-top:1px solid var(--border);margin:4px 0"></div>
                <a href="{{ url('/subscribe') }}" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:13px;color:#d97706;transition:var(--transition)" onmouseover="this.style.background='#fffbeb'" onmouseout="this.style.background=''">
                  <i class="fas fa-crown fa-fw"></i> Nâng cấp tài khoản
                </a>
              @else
                <a href="{{ url('/dashboard') }}" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:13px;color:var(--text-secondary);transition:var(--transition)" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background=''">
                  <i class="fas fa-th-large fa-fw"></i> Tổng quan cá nhân
                </a>
                <a href="{{ url('/user/cv') }}" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:13px;color:var(--text-secondary);transition:var(--transition)" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background=''">
                  <i class="fas fa-folder-open fa-fw"></i> Quản lý CV & Hồ sơ
                </a>
                <a href="{{ url('/applicants') }}" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:13px;color:var(--text-secondary);transition:var(--transition)" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background=''">
                  <i class="fas fa-tasks fa-fw"></i> Theo dõi ứng tuyển
                </a>
                <div style="border-top:1px solid var(--border);margin:4px 0"></div>
                <a href="{{ url('/subscribe') }}" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:13px;color:#d97706;transition:var(--transition)" onmouseover="this.style.background='#fffbeb'" onmouseout="this.style.background=''">
                  <i class="fas fa-crown fa-fw"></i> Nâng cấp tài khoản
                </a>
              @endif
              <div style="border-top:1px solid var(--border);margin:4px 0"></div>
              <a href="{{ url('/user/profile') }}" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:13px;color:var(--text-secondary);transition:var(--transition)" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background=''">
                <i class="fas fa-user-cog fa-fw"></i> Thông tin cá nhân
              </a>
              <form action="{{ url('/logout') }}" method="POST">
                @csrf
                <button type="submit" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:13px;color:#dc3545;width:100%;background:none;border:none;cursor:pointer;transition:var(--transition)" onmouseover="this.style.background='#fdf0f0'" onmouseout="this.style.background=''">
                  <i class="fas fa-sign-out-alt fa-fw"></i> Đăng xuất
                </button>
              </form>
            </div>
          </div>
        </div>
      @else
        <a href="{{ url('/login') }}" class="btn btn-outline btn-sm">Đăng nhập</a>
        <a href="{{ url('/register') }}" class="btn btn-primary btn-sm">Đăng ký</a>
      @endauth
    </div>
  </div>
</nav>

{{-- FLASH MESSAGES --}}
@if(session('success') || session('error') || session('warning'))
  <div style="position:fixed;top:70px;right:20px;z-index:999;max-width:360px">
    @if(session('success'))
      <div class="alert alert-success" style="animation:slideIn .3s ease">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
      </div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger" style="animation:slideIn .3s ease">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
      </div>
    @endif
    @if(session('warning'))
      <div class="alert alert-warning" style="animation:slideIn .3s ease">
        <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
      </div>
    @endif
  </div>
@endif

{{-- MAIN CONTENT --}}
<main>@yield('content')</main>

{{-- FOOTER --}}
<footer class="footer">
  <div class="container">
    <div class="grid-4" style="grid-template-columns:2fr 1fr 1fr 1fr">
      <div>
        <div style="font-size:24px;font-weight:800;color:var(--primary);margin-bottom:10px">IT<span style="color:#fff">Works</span></div>
        <p style="font-size:13px;color:rgba(255,255,255,.6);line-height:1.7">Nền tảng tuyển dụng IT hàng đầu Việt Nam. Kết nối nhân tài và doanh nghiệp công nghệ.</p>
        <div class="flex gap-12 mt-16">
          <a href="#" style="color:rgba(255,255,255,.6);font-size:18px"><i class="fab fa-facebook"></i></a>
          <a href="#" style="color:rgba(255,255,255,.6);font-size:18px"><i class="fab fa-linkedin"></i></a>
          <a href="#" style="color:rgba(255,255,255,.6);font-size:18px"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
      <div>
        <h4>Ứng viên</h4>
        <ul>
          <li><a href="{{ url('/job') }}">Tìm việc làm</a></li>
          <li><a href="{{ url('/user/cv/create') }}">Tạo CV online</a></li>
          <li><a href="{{ url('/user/cv') }}">Upload CV</a></li>
        </ul>
      </div>
      <div>
        <h4>Nhà tuyển dụng</h4>
        <ul>
          <li><a href="{{ url('/job/create') }}">Đăng tin tuyển dụng</a></li>
          <li><a href="{{ url('/applicants') }}">Quản lý ứng viên</a></li>
          <li><a href="{{ url('/subscribe') }}">Gói premium</a></li>
        </ul>
      </div>
      <div>
        <h4>Hỗ trợ</h4>
        <ul>
          <li><a href="#">Trung tâm hỗ trợ</a></li>
          <li><a href="#">Điều khoản dịch vụ</a></li>
          <li><a href="#">Chính sách bảo mật</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© {{ date('Y') }} ITWorks. All rights reserved.</span>
      <span>Made with ❤️ in Vietnam</span>
    </div>
  </div>
</footer>

<style>
@keyframes slideIn { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:translateX(0); } }
@keyframes dropDown { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }

/* Nav dropdown (header) */
.nav-dropdown { position: relative; }
.nav-dropdown__trigger { display: flex; align-items: center; cursor: pointer; }
.nav-dropdown__menu {
  display: none;
  position: absolute;
  top: calc(100% + 10px);
  left: 0;
  background: #fff;
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  min-width: 210px;
  z-index: 300;
  overflow: hidden;
  animation: dropDown .15s ease;
}
.nav-dropdown__menu a {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 10px 16px;
  font-size: 13px;
  color: var(--text-secondary);
  transition: var(--transition);
  white-space: nowrap;
}
.nav-dropdown__menu a:hover { background: var(--primary-light); color: var(--primary); }
.nav-dropdown__menu a i { width: 16px; text-align: center; }
.nav-dropdown.open .nav-dropdown__menu { display: block; }
</style>
<script>
function toggleDropdown() {
  var d = document.getElementById('user-dropdown');
  d.style.display = d.style.display === 'none' ? 'block' : 'none';
}
function toggleNavDropdown(id) {
  var el = document.getElementById(id);
  var isOpen = el.classList.contains('open');
  // Close all nav dropdowns first
  document.querySelectorAll('.nav-dropdown').forEach(function(nd) { nd.classList.remove('open'); });
  if (!isOpen) el.classList.add('open');
}
document.addEventListener('click', function(e) {
  if (!e.target.closest('[onclick="toggleDropdown()"]')) {
    var d = document.getElementById('user-dropdown');
    if (d) d.style.display = 'none';
  }
  if (!e.target.closest('.nav-dropdown')) {
    document.querySelectorAll('.nav-dropdown').forEach(function(nd) { nd.classList.remove('open'); });
  }
});
// Auto hide flash after 4s
setTimeout(function() {
  var alerts = document.querySelectorAll('.alert');
  alerts.forEach(function(a) { a.style.opacity='0'; a.style.transition='.5s'; setTimeout(function(){ a.remove(); },500); });
}, 4000);
</script>
@stack('scripts')
</body>
</html>