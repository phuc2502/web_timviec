<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <title><?php echo $__env->yieldContent('title', 'IT Works — Tìm Việc IT Hàng Đầu'); ?></title>
  <meta name="description" content="<?php echo $__env->yieldContent('description', 'Nền tảng tuyển dụng IT hàng đầu Việt Nam. Hàng nghìn việc làm IT đang chờ bạn.'); ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
  <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>


<nav class="navbar">
  <div class="container navbar-inner">
    <a href="<?php echo e(url('/')); ?>" class="navbar-brand">IT<span>Works</span></a>
    <div class="navbar-nav">
      <a href="<?php echo e(url('/')); ?>" class="<?php echo e(request()->is('/') ? 'active' : ''); ?>">Trang chủ</a>
      <a href="<?php echo e(url('/job')); ?>" class="<?php echo e(request()->is('job') ? 'active' : ''); ?>">Tìm việc</a>
      <?php if(auth()->guard()->check()): ?>
        <?php if(auth()->user()->user_type === 'employer'): ?>
          <a href="<?php echo e(route('job.create')); ?>">Đăng tuyển</a>
          <a href="<?php echo e(route('job.manage')); ?>">Quản lý tin</a>
          <a href="<?php echo e(route('employer.subscription.status')); ?>">Gói Premium</a>
          <a href="<?php echo e(route('dashboard')); ?>">Dashboard</a>
        <?php else: ?>
          <a href="<?php echo e(route('user.cv')); ?>">Hồ sơ CV</a>
          <a href="<?php echo e(route('candidate.history')); ?>">Việc đã nộp</a>
          <a href="<?php echo e(route('payment.token')); ?>">Mua lượt</a>
        <?php endif; ?>
        <a href="<?php echo e(url('/messages')); ?>">
          <i class="fas fa-comment-dots"></i> Tin nhắn
        </a>
      <?php endif; ?>
    </div>
    <div class="navbar-actions">
      <?php if(auth()->guard()->check()): ?>
        <div class="flex gap-8" style="align-items:center">
          <?php if(auth()->user()->user_type === 'employer' && !auth()->user()->billing_ends): ?>
            <a href="<?php echo e(route('payment.subscription')); ?>" class="btn btn-outline btn-sm">
              <i class="fas fa-crown"></i> Nâng cấp
            </a>
          <?php endif; ?>

          
          <div id="notif-bell" style="position:relative;cursor:pointer" onclick="toggleNotifDropdown()">
            <div style="width:36px;height:36px;border-radius:50%;background:var(--bg-gray);display:flex;align-items:center;justify-content:center;border:1px solid var(--border)">
              <i class="fas fa-bell" style="color:var(--text-secondary);font-size:14px"></i>
            </div>
            <span id="notif-badge" style="display:none;position:absolute;top:-4px;right:-4px;background:var(--danger);color:#fff;border-radius:50%;width:18px;height:18px;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;border:2px solid #fff">0</span>
          </div>

          
          <div id="notif-dropdown" style="display:none;position:absolute;top:52px;right:120px;width:360px;background:#fff;border-radius:var(--radius-lg);box-shadow:0 8px 24px rgba(0,0,0,0.12);border:1px solid var(--border);z-index:9999">
            <div style="padding:14px 16px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
              <span class="fw-700 fs-14">Thông báo</span>
              <button onclick="markAllRead()" style="background:none;border:none;color:var(--primary);font-size:12px;cursor:pointer;padding:0">Đánh dấu đã đọc</button>
            </div>
            <div id="notif-list" style="max-height:380px;overflow-y:auto">
              <div style="padding:24px;text-align:center;color:var(--text-secondary);font-size:13px">
                <i class="fas fa-bell fa-2x mb-8" style="opacity:.2"></i>
                <p>Đang tải...</p>
              </div>
            </div>
          </div>
          <div style="position:relative;cursor:pointer" onclick="toggleDropdown()">
            <?php if(auth()->user()->profile_pic): ?>
              <img src="<?php echo e(asset('storage/images/'.auth()->user()->profile_pic)); ?>" class="avatar avatar-sm" alt="avatar">
            <?php else: ?>
              <div class="avatar avatar-sm avatar-placeholder" style="font-size:13px">
                <?php echo e(strtoupper(substr(auth()->user()->name,0,1))); ?>

              </div>
            <?php endif; ?>
            <div id="user-dropdown" style="display:none;position:absolute;right:0;top:40px;background:#fff;border:1px solid var(--border);border-radius:var(--radius-lg);box-shadow:var(--shadow-lg);min-width:180px;z-index:200;overflow:hidden">
              <div style="padding:12px 16px;border-bottom:1px solid var(--border)">
                <div class="fw-600 fs-13"><?php echo e(auth()->user()->name); ?></div>
                <div class="text-muted fs-12"><?php echo e(auth()->user()->email); ?></div>
              </div>
              <a href="<?php echo e(url('/user/profile')); ?>" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:14px;color:var(--text-secondary);transition:var(--transition)" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background=''">
                <i class="fas fa-user fa-fw"></i> Tài khoản
              </a>
              <?php if(auth()->user()->user_type === 'employer'): ?>
                <a href="<?php echo e(route('dashboard')); ?>" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:14px;color:var(--text-secondary);transition:var(--transition)" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background=''">
                  <i class="fas fa-chart-bar fa-fw"></i> Dashboard
                </a>
                <a href="<?php echo e(route('employer.subscription.status')); ?>" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:14px;color:var(--text-secondary);transition:var(--transition)" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background=''">
                  <i class="fas fa-crown fa-fw"></i> Gói Premium
                </a>
              <?php else: ?>
                <a href="<?php echo e(route('candidate.history')); ?>" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:14px;color:var(--text-secondary);transition:var(--transition)" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background=''">
                  <i class="fas fa-history fa-fw"></i> Lịch sử ứng tuyển
                </a>
                <a href="<?php echo e(route('payment.token')); ?>" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:14px;color:var(--text-secondary);transition:var(--transition)" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background=''">
                  <i class="fas fa-ticket-alt fa-fw"></i> Mua lượt ứng tuyển
                </a>
              <?php endif; ?>
              <form action="<?php echo e(url('/logout')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button type="submit" style="display:flex;align-items:center;gap:8px;padding:10px 16px;font-size:14px;color:#dc3545;width:100%;transition:var(--transition)" onmouseover="this.style.background='#fdf0f0'" onmouseout="this.style.background=''">
                  <i class="fas fa-sign-out-alt fa-fw"></i> Đăng xuất
                </button>
              </form>
            </div>
          </div>
        </div>
      <?php else: ?>
        <a href="<?php echo e(url('/login')); ?>" class="btn btn-outline btn-sm">Đăng nhập</a>
        <a href="<?php echo e(url('/register')); ?>" class="btn btn-primary btn-sm">Đăng ký</a>
      <?php endif; ?>
    </div>
  </div>
</nav>


<?php if(session('success') || session('error') || session('warning')): ?>
  <div style="position:fixed;top:70px;right:20px;z-index:999;max-width:360px">
    <?php if(session('success')): ?>
      <div class="alert alert-success" style="animation:slideIn .3s ease">
        <i class="fas fa-check-circle"></i> <?php echo e(session('success')); ?>

      </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
      <div class="alert alert-danger" style="animation:slideIn .3s ease">
        <i class="fas fa-exclamation-circle"></i> <?php echo e(session('error')); ?>

      </div>
    <?php endif; ?>
    <?php if(session('warning')): ?>
      <div class="alert alert-warning" style="animation:slideIn .3s ease">
        <i class="fas fa-exclamation-triangle"></i> <?php echo e(session('warning')); ?>

      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>


<main><?php echo $__env->yieldContent('content'); ?></main>


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
          <li><a href="<?php echo e(url('/job')); ?>">Tìm việc làm</a></li>
          <li><a href="<?php echo e(url('/user/cv/create')); ?>">Tạo CV online</a></li>
          <li><a href="<?php echo e(url('/user/cv')); ?>">Upload CV</a></li>
        </ul>
      </div>
      <div>
        <h4>Nhà tuyển dụng</h4>
        <ul>
          <li><a href="<?php echo e(url('/job/create')); ?>">Đăng tin tuyển dụng</a></li>
          <li><a href="<?php echo e(url('/applicants')); ?>">Quản lý ứng viên</a></li>
          <li><a href="<?php echo e(url('/subscribe')); ?>">Gói premium</a></li>
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
      <span>© <?php echo e(date('Y')); ?> ITWorks. All rights reserved.</span>
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
<?php echo $__env->yieldPushContent('scripts'); ?>

<?php if(auth()->guard()->check()): ?>
<script>
// ── Notification Bell ─────────────────────────────────
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
    list.innerHTML = `<div style="padding:32px;text-align:center;color:#999;font-size:13px">
      <i class="fas fa-bell-slash fa-2x" style="opacity:.2;display:block;margin-bottom:8px"></i>
      Chưa có thông báo nào
    </div>`;
    return;
  }

  list.innerHTML = notifs.map(n => {
    const icons = {application_status:'fa-file-alt', payment:'fa-credit-card'};
    const icon  = icons[n.type] || 'fa-bell';
    const unread = !n.read_at;
    const timeAgo = formatTime(n.created_at);
    return `
    <div onclick="readNotif(${n.id}, this)" style="padding:14px 16px;border-bottom:1px solid #f5f5f5;cursor:pointer;background:${unread ? '#f9fffe' : '#fff'};transition:.15s"
      onmouseover="this.style.background='var(--bg-gray)'" onmouseout="this.style.background='${unread ? '#f9fffe' : '#fff'}'">
      <div style="display:flex;gap:10px;align-items:flex-start">
        <div style="width:34px;height:34px;border-radius:50%;background:${unread ? 'var(--primary-light)' : 'var(--bg-gray)'};display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <i class="fas ${icon} fa-fw" style="color:${unread ? 'var(--primary)' : 'var(--text-secondary)'};font-size:13px"></i>
        </div>
        <div style="flex:1;min-width:0">
          <div style="font-size:13px;font-weight:${unread ? '600' : '400'};color:#1a1a1a;line-height:1.4">${n.title}</div>
          <div style="font-size:12px;color:#666;margin-top:3px;line-height:1.5">${n.body}</div>
          <div style="font-size:11px;color:#999;margin-top:4px">${timeAgo}</div>
        </div>
        ${unread ? '<div style="width:8px;height:8px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:4px"></div>' : ''}
      </div>
    </div>`;
  }).join('');
}

async function readNotif(id, el) {
  await fetch(`/notifications/${id}/read`, {method:'POST', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}});
  el.style.background = '#fff';
  const dot = el.querySelector('[style*="border-radius:50%"]');
  if (dot && dot.style.background.includes('primary')) dot.remove();
  await loadNotifications();
}

async function markAllRead() {
  await fetch('/notifications/read-all', {method:'POST', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}});
  loadNotifications();
}

function formatTime(dateStr) {
  const diff = (Date.now() - new Date(dateStr)) / 1000;
  if (diff < 60)     return 'Vừa xong';
  if (diff < 3600)   return Math.floor(diff/60) + ' phút trước';
  if (diff < 86400)  return Math.floor(diff/3600) + ' giờ trước';
  return Math.floor(diff/86400) + ' ngày trước';
}

// Auto-load badge count on page load
document.addEventListener('DOMContentLoaded', async () => {
  try {
    const res  = await fetch('/notifications');
    const data = await res.json();
    updateBadge(data.unread_count);
  } catch(e) {}
});
</script>
<?php endif; ?>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\web_timviec_final\website_timviec\resources\views/layouts/app.blade.php ENDPATH**/ ?>