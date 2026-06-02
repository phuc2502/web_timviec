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
      <a href="{{ url('/job') }}" class="{{ request()->is('job') ? 'active' : '' }}">Tìm việc</a>
      @auth
        @if(auth()->user()->user_type === 'employer')
          <a href="{{ route('job.create') }}">Đăng tuyển</a>
          <a href="{{ route('job.manage') }}">Quản lý tin</a>
          <a href="{{ route('employer.subscription.status') }}">Gói Premium</a>
          <a href="{{ route('dashboard') }}">Dashboard</a>
        @else
          <a href="{{ route('user.cv') }}">Hồ sơ CV</a>
          <a href="{{ route('candidate.history') }}">Việc đã nộp</a>
          <a href="{{ route('payment.token') }}">Mua lượt</a>
        @endif
        <a href="{{ url('/messages') }}">
          <i class="fas fa-comment-dots"></i> Tin nhắn
        </a>
      @endauth
    </div>
    <div class="navbar-actions">
      @auth
        <div class="flex gap-8" style="align-items:center">
          @if(auth()->user()->user_type === 'employer' && !auth()->user()->billing_ends)
            <a href="{{ route('payment.subscription') }}" class="btn btn-outline btn-sm">
              <i class="fas fa-crown"></i> Nâng cấp
            </a>
          @endif
          <div style="position:relative;cursor:pointer" onclick="toggleDropdown()">
            @if(auth()->user()->profile_pic)
              <img src="{{ asset('storage/images/'.auth()->user()->profile_pic) }}" class="avatar avatar-sm" alt="avatar">
            @else
              <div class="avatar avatar-sm avatar-placeholder" style="font-size:13px">
                {{ strtoupper(substr(auth()->user()->name,0,1)) }}
              </div>
            @endif
            <div id="user-dropdown" style="display:none;position:absolute;right:0;top:40px;background:#fff;border:1px solid var(--border);border-radius:var(--radius-lg);box-shadow:var(--shadow-lg);min-width:180px;z-index:200;overflow:hidden">
              <div style="padding:12px 16px;border-bottom:1px solid var(--border)">
                <div class="fw-600 fs-13">{{ auth()->user()->name }}</div>
                <div class="text-muted fs-12">{{ auth()->user()->email }}</div>
              </div>
              <a href="{{ url('/user/profile') }}" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:14px;color:var(--text-secondary);transition:var(--transition)" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background=''">
                <i class="fas fa-user fa-fw"></i> Tài khoản
              </a>
              @if(auth()->user()->user_type === 'employer')
                <a href="{{ route('dashboard') }}" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:14px;color:var(--text-secondary);transition:var(--transition)" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background=''">
                  <i class="fas fa-chart-bar fa-fw"></i> Dashboard
                </a>
                <a href="{{ route('employer.subscription.status') }}" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:14px;color:var(--text-secondary);transition:var(--transition)" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background=''">
                  <i class="fas fa-crown fa-fw"></i> Gói Premium
                </a>
              @else
                <a href="{{ route('candidate.history') }}" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:14px;color:var(--text-secondary);transition:var(--transition)" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background=''">
                  <i class="fas fa-history fa-fw"></i> Lịch sử ứng tuyển
                </a>
                <a href="{{ route('payment.token') }}" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:14px;color:var(--text-secondary);transition:var(--transition)" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background=''">
                  <i class="fas fa-ticket-alt fa-fw"></i> Mua lượt ứng tuyển
                </a>
              @endif
              <form action="{{ url('/logout') }}" method="POST">
                @csrf
                <button type="submit" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:14px;color:#dc3545;width:100%;transition:var(--transition)" onmouseover="this.style.background='#fdf0f0'" onmouseout="this.style.background=''">
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
</style>
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
// Auto hide flash after 4s
setTimeout(function() {
  var alerts = document.querySelectorAll('.alert');
  alerts.forEach(function(a) { a.style.opacity='0'; a.style.transition='.5s'; setTimeout(function(){ a.remove(); },500); });
}, 4000);
</script>
@stack('scripts')
</body>
</html>
