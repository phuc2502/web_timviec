<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'IT Works — Tìm Việc IT Hàng Đầu')</title>
  <meta name="description" content="@yield('description', 'Nền tảng tuyển dụng IT hàng đầu Việt Nam. Hàng nghìn việc làm IT đang chờ bạn.')">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
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
        @if(auth()->user()->user_type !== 'employer')
          <a href="{{ url('/job') }}" class="{{ request()->is('job') ? 'active' : '' }}">Tìm việc</a>
        @endif
        @if(auth()->user()->user_type === 'employer')
          <a href="{{ route('job.create') }}"><i class="fas fa-plus-circle"></i> Đăng tuyển</a>
          <a href="{{ route('job.manage') }}">Quản lý tin</a>
          <a href="{{ route('employer.subscription.status') }}"><i class="fas fa-crown" style="color:var(--pro-gold)"></i> Premium</a>
          <a href="{{ route('dashboard') }}">Dashboard</a>
        @else
          <a href="{{ route('user.cv') }}">Hồ sơ CV</a>
          <a href="{{ route('candidate.history') }}">Việc đã nộp</a>
          <a href="{{ route('payment.token') }}">Mua lượt</a>
        @endif
        <a href="{{ url('/messages') }}"><i class="fas fa-comment-dots"></i> Tin nhắn</a>
      @else
        <a href="{{ url('/job') }}" class="{{ request()->is('job') ? 'active' : '' }}">Tìm việc</a>
      @endauth
    </div>

    <div class="navbar-actions">
      @auth
        <div class="flex gap-8" style="align-items:center">
          @if(auth()->user()->user_type === 'employer' && !auth()->user()->billing_ends)
            <a href="{{ route('payment.subscription') }}" class="btn btn-sm" style="background:linear-gradient(135deg,#F59E0B,#D97706);color:#fff;border:none;gap:6px">
              <i class="fas fa-crown"></i> Nâng cấp
            </a>
          @endif

          {{-- Notification Bell --}}
          <div id="notif-bell" style="position:relative;cursor:pointer" onclick="toggleNotifDropdown()">
            <div style="width:38px;height:38px;border-radius:50%;background:var(--bg-soft);display:flex;align-items:center;justify-content:center;border:1.5px solid var(--border);transition:var(--transition)" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background='var(--bg-soft)'">
              <i class="fas fa-bell" style="color:var(--text-secondary);font-size:15px"></i>
            </div>
            <span id="notif-badge" style="display:none;position:absolute;top:-3px;right:-3px;background:var(--danger);color:#fff;border-radius:50%;width:18px;height:18px;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;border:2px solid #fff">0</span>
          </div>

          {{-- Notification Dropdown --}}
          <div id="notif-dropdown" style="display:none;position:absolute;top:60px;right:60px;width:370px;background:#fff;border-radius:var(--radius-md);box-shadow:var(--shadow-xl);border:1px solid var(--border);z-index:9999">
            <div style="padding:14px 18px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
              <span style="font-family:var(--font-display);font-weight:700;font-size:14px;color:var(--text-dark)">Thông báo</span>
              <button onclick="markAllRead()" style="background:none;border:none;color:var(--primary);font-size:12px;cursor:pointer;padding:0;font-weight:600">Đánh dấu đã đọc</button>
            </div>
            <div id="notif-list" style="max-height:380px;overflow-y:auto">
              <div style="padding:28px;text-align:center;color:var(--text-muted);font-size:13px">
                <i class="fas fa-bell fa-2x mb-8" style="opacity:.2;display:block;margin-bottom:10px"></i>
                <p>Đang tải...</p>
              </div>
            </div>
          </div>

          {{-- User avatar / dropdown --}}
          <div style="position:relative;cursor:pointer" onclick="toggleDropdown()">
            @if(auth()->user()->profile_pic)
              <img src="{{ asset('storage/images/'.auth()->user()->profile_pic) }}" class="avatar avatar-sm" alt="avatar" style="border:2px solid var(--border)">
            @else
              <div class="avatar avatar-sm avatar-placeholder">
                {{ strtoupper(substr(auth()->user()->name,0,1)) }}
              </div>
            @endif
            <div id="user-dropdown" style="display:none;position:absolute;right:0;top:44px;min-width:200px;z-index:200" class="dropdown-menu">
              <div class="dropdown-header">
                <div style="font-family:var(--font-display);font-weight:700;font-size:13px;color:var(--text-dark)">{{ auth()->user()->name }}</div>
                <div style="font-size:11.5px;color:var(--text-muted);margin-top:2px">{{ auth()->user()->email }}</div>
              </div>
              <a href="{{ url('/user/profile') }}" class="dropdown-item">
                <i class="fas fa-user-circle"></i> Tài khoản
              </a>
              @if(auth()->user()->user_type === 'employer')
                <a href="{{ route('dashboard') }}" class="dropdown-item">
                  <i class="fas fa-chart-bar"></i> Dashboard
                </a>
                <a href="{{ route('employer.subscription.status') }}" class="dropdown-item">
                  <i class="fas fa-crown" style="color:var(--pro-gold)"></i> Gói Premium
                </a>
              @else
                <a href="{{ route('candidate.history') }}" class="dropdown-item">
                  <i class="fas fa-history"></i> Lịch sử ứng tuyển
                </a>
                <a href="{{ route('payment.token') }}" class="dropdown-item">
                  <i class="fas fa-ticket-alt"></i> Mua lượt ứng tuyển
                </a>
              @endif
              <div class="dropdown-divider"></div>
              <form action="{{ url('/logout') }}" method="POST">
                @csrf
                <button type="submit" class="dropdown-item danger">
                  <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </button>
              </form>
            </div>
          </div>
        </div>
      @else
        <a href="{{ url('/login') }}" class="btn btn-ghost btn-sm">Đăng nhập</a>
        <a href="{{ url('/register') }}" class="btn btn-primary btn-sm">Đăng ký</a>
      @endauth
    </div>
  </div>
</nav>

{{-- FLASH MESSAGES --}}
@if(session('success') || session('error') || session('warning'))
  <div style="position:fixed;top:72px;right:20px;z-index:999;max-width:380px;display:flex;flex-direction:column;gap:8px">
    @if(session('success'))
      <div class="alert alert-success" style="animation:slideInRight .3s ease;border-radius:var(--radius-md);box-shadow:var(--shadow-lg)">
        <i class="fas fa-check-circle" style="flex-shrink:0;font-size:15px"></i>
        <span>{{ session('success') }}</span>
      </div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger" style="animation:slideInRight .3s ease;border-radius:var(--radius-md);box-shadow:var(--shadow-lg)">
        <i class="fas fa-exclamation-circle" style="flex-shrink:0;font-size:15px"></i>
        <span>{{ session('error') }}</span>
      </div>
    @endif
    @if(session('warning'))
      <div class="alert alert-warning" style="animation:slideInRight .3s ease;border-radius:var(--radius-md);box-shadow:var(--shadow-lg)">
        <i class="fas fa-exclamation-triangle" style="flex-shrink:0;font-size:15px"></i>
        <span>{{ session('warning') }}</span>
      </div>
    @endif
  </div>
@endif

{{-- MAIN CONTENT --}}
<main>@yield('content')</main>

{{-- FOOTER --}}
<footer class="footer">
  <div class="container">
    <div class="grid-4" style="grid-template-columns:2fr 1fr 1fr 1fr;gap:40px">
      <div>
        <div class="footer-logo">IT<span>Works</span></div>
        <p style="font-size:13.5px;color:rgba(255,255,255,.5);line-height:1.75;margin-bottom:20px">Nền tảng tuyển dụng IT hàng đầu Việt Nam. Kết nối nhân tài và doanh nghiệp công nghệ.</p>
        <div class="footer-social flex gap-8">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-linkedin-in"></i></a>
          <a href="#"><i class="fab fa-youtube"></i></a>
          <a href="#"><i class="fab fa-github"></i></a>
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
          <li><a href="{{ route('payment.subscription') }}">Gói premium</a></li>
        </ul>
      </div>
      <div>
        <h4>Hỗ trợ</h4>
        <ul>
          <li><a href="#">Trung tâm hỗ trợ</a></li>
          <li><a href="{{ url('/legal/terms') }}">Điều khoản dịch vụ</a></li>
          <li><a href="{{ url('/legal/privacy') }}">Chính sách bảo mật</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© {{ date('Y') }} ITWorks. All rights reserved.</span>
      <span>Made with ❤️ in Vietnam</span>
    </div>
  </div>
</footer>

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
setTimeout(function() {
  var alerts = document.querySelectorAll('.alert');
  alerts.forEach(function(a) { a.style.opacity='0'; a.style.transition='.5s'; setTimeout(function(){ a.remove(); },500); });
}, 4500);
</script>
@stack('scripts')

@auth
<script>
let notifOpen = false;

function toggleNotifDropdown() {
  notifOpen = !notifOpen;
  const dd = document.getElementById('notif-dropdown');
  dd.style.display = notifOpen ? 'block' : 'none';
  if (notifOpen) loadNotifications();
}

document.addEventListener('click', function(e) {
  if (!document.getElementById('notif-bell').contains(e.target)) {
    document.getElementById('notif-dropdown').style.display = 'none';
    notifOpen = false;
  }
});

async function loadNotifications() {
  const res  = await fetch('/notifications');
  const data = await res.json();
  updateBadge(data.unread_count);
  renderNotifs(data.notifications);
}

function updateBadge(count) {
  const badge = document.getElementById('notif-badge');
  if (count > 0) {
    badge.textContent = count > 9 ? '9+' : count;
    badge.style.display = 'flex';
  } else {
    badge.style.display = 'none';
  }
}

function renderNotifs(notifs) {
  const list = document.getElementById('notif-list');
  if (!notifs.length) {
    list.innerHTML = `<div style="padding:36px;text-align:center;color:var(--text-muted);font-size:13px">
      <i class="fas fa-bell-slash fa-2x" style="opacity:.2;display:block;margin-bottom:12px"></i>
      Chưa có thông báo nào
    </div>`;
    return;
  }
  list.innerHTML = notifs.map(n => {
    const icons = {application_status:'fa-file-alt',payment:'fa-credit-card'};
    const icon  = icons[n.type] || 'fa-bell';
    const unread = !n.read_at;
    const timeAgo = formatTime(n.created_at);
    return `
    <div onclick="readNotif(${n.id}, this)" style="padding:14px 18px;border-bottom:1px solid var(--border);cursor:pointer;background:${unread?'#F0FDF7':'#fff'};transition:.15s"
      onmouseover="this.style.background='var(--bg-soft)'" onmouseout="this.style.background='${unread?'#F0FDF7':'#fff'}'">
      <div style="display:flex;gap:12px;align-items:flex-start">
        <div style="width:36px;height:36px;border-radius:50%;background:${unread?'var(--primary-light)':'var(--bg-soft)'};display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <i class="fas ${icon} fa-fw" style="color:${unread?'var(--primary)':'var(--text-muted)'};font-size:13px"></i>
        </div>
        <div style="flex:1;min-width:0">
          <div style="font-size:13px;font-weight:${unread?'700':'500'};color:var(--text-dark);line-height:1.4">${n.title}</div>
          <div style="font-size:12px;color:var(--text-secondary);margin-top:3px;line-height:1.5">${n.body}</div>
          <div style="font-size:11px;color:var(--text-muted);margin-top:4px">${timeAgo}</div>
        </div>
        ${unread ? '<div style="width:8px;height:8px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:5px"></div>' : ''}
      </div>
    </div>`;
  }).join('');
}

async function readNotif(id, el) {
  await fetch(`/notifications/${id}/read`, {method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}});
  el.style.background = '#fff';
  await loadNotifications();
}

async function markAllRead() {
  await fetch('/notifications/read-all', {method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}});
  loadNotifications();
}

function formatTime(dateStr) {
  const diff = (Date.now() - new Date(dateStr)) / 1000;
  if (diff < 60)    return 'Vừa xong';
  if (diff < 3600)  return Math.floor(diff/60) + ' phút trước';
  if (diff < 86400) return Math.floor(diff/3600) + ' giờ trước';
  return Math.floor(diff/86400) + ' ngày trước';
}

document.addEventListener('DOMContentLoaded', async () => {
  try {
    const res  = await fetch('/notifications');
    const data = await res.json();
    updateBadge(data.unread_count);
  } catch(e) {}
});
</script>
@endauth
</body>
</html>
