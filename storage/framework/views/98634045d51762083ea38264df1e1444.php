<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <title><?php echo $__env->yieldContent('title', 'Admin'); ?> — ITWorks</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
  <?php echo $__env->yieldPushContent('styles'); ?>

  <style>
    /* ===== ADMIN SHELL ===== */
    .admin-shell {
      display: flex;
      min-height: 100vh;
      background: #f1f5f9;
    }

    /* ===== SIDEBAR ===== */
    .admin-sidebar {
      position: fixed;
      top: 0; left: 0; bottom: 0;
      width: 248px;
      background: linear-gradient(160deg, #0f172a 0%, #1e293b 100%);
      display: flex;
      flex-direction: column;
      transition: width 0.28s cubic-bezier(0.4, 0, 0.2, 1);
      z-index: 100;
      overflow: hidden;
      box-shadow: 4px 0 24px rgba(0,0,0,.18);
    }
    .admin-sidebar.collapsed {
      width: 64px;
    }

    /* Brand */
    .sidebar-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 20px 18px 16px;
      border-bottom: 1px solid rgba(255,255,255,.08);
      flex-shrink: 0;
      min-height: 64px;
    }
    .sidebar-brand__icon {
      width: 32px; height: 32px;
      background: linear-gradient(135deg, #00d97e, #00b368);
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      color: #fff;
      font-size: 14px;
      flex-shrink: 0;
    }
    .sidebar-brand__text {
      font-size: 15px;
      font-weight: 700;
      color: #fff;
      letter-spacing: .02em;
      white-space: nowrap;
      overflow: hidden;
      transition: opacity 0.2s, width 0.2s;
    }
    .admin-sidebar.collapsed .sidebar-brand__text {
      opacity: 0;
      width: 0;
    }

    /* Toggle button */
    .sidebar-toggle {
      position: absolute;
      top: 18px;
      right: -13px;
      width: 26px; height: 26px;
      background: #00d97e;
      border: 2px solid #f1f5f9;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      color: #fff;
      font-size: 10px;
      cursor: pointer;
      transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1), background 0.2s;
      z-index: 101;
      flex-shrink: 0;
    }
    .sidebar-toggle:hover { background: #00b368; }
    .admin-sidebar.collapsed .sidebar-toggle {
      transform: rotate(180deg);
    }

    /* Nav */
    .sidebar-nav {
      flex: 1;
      overflow-y: auto;
      overflow-x: hidden;
      padding: 10px 0;
      scrollbar-width: thin;
      scrollbar-color: rgba(255,255,255,.1) transparent;
    }
    .sidebar-nav::-webkit-scrollbar { width: 3px; }
    .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 3px; }

    /* Section label */
    .sidebar-section {
      font-size: 10px;
      font-weight: 700;
      letter-spacing: .1em;
      text-transform: uppercase;
      color: rgba(255,255,255,.35);
      padding: 12px 20px 6px;
      white-space: nowrap;
      overflow: hidden;
      transition: opacity 0.2s;
    }
    .admin-sidebar.collapsed .sidebar-section {
      opacity: 0;
      padding: 12px 0 6px;
    }

    /* Nav links */
    .sidebar-link {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 18px;
      color: rgba(255,255,255,.65);
      font-size: 13.5px;
      font-weight: 500;
      text-decoration: none;
      white-space: nowrap;
      transition: background 0.18s, color 0.18s, padding 0.28s;
      border-radius: 0;
      position: relative;
    }
    .sidebar-link:hover {
      background: rgba(255,255,255,.07);
      color: #fff;
    }
    .sidebar-link.active {
      background: rgba(0,217,126,.15);
      color: #00d97e;
      font-weight: 600;
    }
    .sidebar-link.active::before {
      content: '';
      position: absolute;
      left: 0; top: 6px; bottom: 6px;
      width: 3px;
      background: #00d97e;
      border-radius: 0 3px 3px 0;
    }
    .sidebar-link__icon {
      width: 20px;
      text-align: center;
      font-size: 15px;
      flex-shrink: 0;
    }
    .sidebar-link__label {
      overflow: hidden;
      transition: opacity 0.2s, width 0.28s;
    }
    .admin-sidebar.collapsed .sidebar-link__label {
      opacity: 0;
      width: 0;
    }

    /* Tooltip khi collapsed */
    .admin-sidebar.collapsed .sidebar-link {
      padding: 10px;
      justify-content: center;
    }
    .admin-sidebar.collapsed .sidebar-link:hover::after {
      content: attr(data-tooltip);
      position: absolute;
      left: 70px;
      background: #1e293b;
      color: #fff;
      padding: 5px 10px;
      border-radius: 6px;
      font-size: 12px;
      white-space: nowrap;
      pointer-events: none;
      box-shadow: 0 4px 12px rgba(0,0,0,.3);
      z-index: 200;
    }

    /* Logout button */
    .sidebar-logout {
      flex-shrink: 0;
      border-top: 1px solid rgba(255,255,255,.08);
      padding: 10px 0;
    }
    .sidebar-logout form { margin: 0; }
    .sidebar-logout-btn {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 18px;
      color: rgba(255,255,255,.55);
      font-size: 13.5px;
      font-weight: 500;
      width: 100%;
      border: none;
      background: none;
      cursor: pointer;
      font-family: inherit;
      transition: background 0.18s, color 0.18s, padding 0.28s;
      white-space: nowrap;
    }
    .sidebar-logout-btn:hover {
      background: rgba(239,68,68,.15);
      color: #f87171;
    }
    .sidebar-logout-btn__icon {
      width: 20px;
      text-align: center;
      font-size: 15px;
      flex-shrink: 0;
    }
    .sidebar-logout-btn__label {
      overflow: hidden;
      transition: opacity 0.2s, width 0.28s;
    }
    .admin-sidebar.collapsed .sidebar-logout-btn {
      padding: 10px;
      justify-content: center;
    }
    .admin-sidebar.collapsed .sidebar-logout-btn__label {
      opacity: 0;
      width: 0;
    }

    /* ===== MAIN CONTENT ===== */
    .admin-main {
      flex: 1;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      margin-left: 248px;
      transition: margin-left 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .admin-main.sidebar-collapsed {
      margin-left: 64px;
    }

    /* Topbar */
    .admin-topbar {
      position: sticky;
      top: 0;
      z-index: 90;
      height: 64px;
      background: #fff;
      border-bottom: 1px solid #e2e8f0;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 28px;
      box-shadow: 0 1px 4px rgba(0,0,0,.06);
    }
    .admin-topbar__title {
      font-size: 16px;
      font-weight: 700;
      color: #0f172a;
    }
    .admin-topbar__right {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .admin-topbar__username {
      font-size: 13px;
      color: #64748b;
      font-weight: 500;
    }

    /* Content area */
    .admin-content {
      flex: 1;
      padding: 28px;
    }

    /* Overlay mobile */
    .sidebar-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,.4);
      z-index: 99;
    }
    @media (max-width: 768px) {
      .admin-sidebar { transform: translateX(-100%); width: 248px !important; }
      .admin-sidebar.mobile-open { transform: translateX(0); }
      .admin-main { margin-left: 0 !important; }
      .sidebar-overlay.active { display: block; }
      .sidebar-toggle { display: none; }
      .admin-topbar__mobile-toggle { display: flex !important; }
    }
    .admin-topbar__mobile-toggle {
      display: none;
      align-items: center;
      justify-content: center;
      width: 36px; height: 36px;
      border: none;
      background: #f1f5f9;
      border-radius: 8px;
      color: #475569;
      cursor: pointer;
      font-size: 16px;
    }
  </style>
</head>
<body>


<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="admin-shell">

  
  <aside class="admin-sidebar" id="adminSidebar">

    
    <button class="sidebar-toggle" id="sidebarToggle" title="Thu gọn sidebar">
      <i class="fas fa-chevron-left"></i>
    </button>

    
    <div class="sidebar-brand">
      <div class="sidebar-brand__icon">
        <i class="fas fa-shield-alt"></i>
      </div>
      <span class="sidebar-brand__text">Admin Panel</span>
    </div>

    
    <nav class="sidebar-nav">

      <div class="sidebar-section">Tổng quan</div>

      <a href="<?php echo e(url('/admin')); ?>"
         data-tooltip="Dashboard"
         class="sidebar-link <?php echo e(request()->is('admin') ? 'active' : ''); ?>">
        <span class="sidebar-link__icon"><i class="fas fa-chart-bar fa-fw"></i></span>
        <span class="sidebar-link__label">Dashboard</span>
      </a>

      <div class="sidebar-section">Quản lý</div>

      <a href="<?php echo e(url('/admin/users')); ?>"
         data-tooltip="Người dùng"
         class="sidebar-link <?php echo e(request()->is('admin/users*') ? 'active' : ''); ?>">
        <span class="sidebar-link__icon"><i class="fas fa-users fa-fw"></i></span>
        <span class="sidebar-link__label">Người dùng</span>
      </a>

      <a href="<?php echo e(url('/admin/transactions')); ?>"
         data-tooltip="Giao dịch"
         class="sidebar-link <?php echo e(request()->is('admin/transactions*') ? 'active' : ''); ?>">
        <span class="sidebar-link__icon"><i class="fas fa-credit-card fa-fw"></i></span>
        <span class="sidebar-link__label">Giao dịch</span>
      </a>

      <a href="<?php echo e(url('/admin/jobs')); ?>"
         data-tooltip="Tin tuyển dụng"
         class="sidebar-link <?php echo e(request()->is('admin/jobs') ? 'active' : ''); ?>">
        <span class="sidebar-link__icon"><i class="fas fa-briefcase fa-fw"></i></span>
        <span class="sidebar-link__label">Tin tuyển dụng</span>
      </a>

      <?php $_pendingCount = \App\Models\Listing::where('status','pending')->orWhereNull('status')->count(); ?>
      <a href="<?php echo e(url('/admin/jobs/pending')); ?>"
         data-tooltip="Duyệt tin<?php echo e($_pendingCount > 0 ? ' ('.$_pendingCount.')' : ''); ?>"
         class="sidebar-link <?php echo e(request()->is('admin/jobs/pending*') ? 'active' : ''); ?>"
         style="position:relative;">
        <span class="sidebar-link__icon" style="position:relative;">
          <i class="fas fa-clipboard-check fa-fw"></i>
          <?php if($_pendingCount > 0): ?>
            <span style="position:absolute;top:-6px;right:-8px;min-width:16px;height:16px;padding:0 4px;background:#ef4444;color:#fff;border-radius:8px;font-size:9px;font-weight:700;display:flex;align-items:center;justify-content:center;line-height:1;"><?php echo e($_pendingCount); ?></span>
          <?php endif; ?>
        </span>
        <span class="sidebar-link__label">Duyệt tin
          <?php if($_pendingCount > 0): ?>
            <span style="display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;padding:0 5px;background:#ef4444;color:#fff;border-radius:9px;font-size:10px;font-weight:700;margin-left:6px;"><?php echo e($_pendingCount); ?></span>
          <?php endif; ?>
        </span>
      </a>

      <div class="sidebar-section">Hệ thống</div>

      <a href="<?php echo e(url('/')); ?>"
         target="_blank"
         data-tooltip="Xem website"
         class="sidebar-link">
        <span class="sidebar-link__icon"><i class="fas fa-external-link-alt fa-fw"></i></span>
        <span class="sidebar-link__label">Xem website</span>
      </a>

    </nav>

    
    <div class="sidebar-logout">
      <form action="<?php echo e(url('/logout')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <button type="submit" class="sidebar-logout-btn" data-tooltip="Đăng xuất">
          <span class="sidebar-logout-btn__icon"><i class="fas fa-sign-out-alt fa-fw"></i></span>
          <span class="sidebar-logout-btn__label">Đăng xuất</span>
        </button>
      </form>
    </div>

  </aside>

  
  <div class="admin-main" id="adminMain">

    
    <div class="admin-topbar">
      
      <button class="admin-topbar__mobile-toggle" id="mobileToggle">
        <i class="fas fa-bars"></i>
      </button>

      <div class="admin-topbar__title"><?php echo $__env->yieldContent('title', 'Dashboard'); ?></div>

      <div class="admin-topbar__right">
        <span class="admin-topbar__username"><?php echo e(auth()->user()->name); ?></span>
        <div class="avatar avatar-sm avatar-placeholder"
             style="background:var(--primary-light);color:var(--primary);font-size:13px;width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;">
          <?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?>

        </div>
      </div>
    </div>

    
    <div class="admin-content">
      <?php if(session('success')): ?>
        <div class="alert alert-success mb-16"><i class="fas fa-check-circle"></i> <?php echo e(session('success')); ?></div>
      <?php endif; ?>
      <?php if(session('error')): ?>
        <div class="alert alert-danger mb-16"><i class="fas fa-exclamation-circle"></i> <?php echo e(session('error')); ?></div>
      <?php endif; ?>

      <?php echo $__env->yieldContent('content'); ?>
    </div>

  </div>

</div>

<script>
(function () {
  const sidebar  = document.getElementById('adminSidebar');
  const main     = document.getElementById('adminMain');
  const toggle   = document.getElementById('sidebarToggle');
  const mToggle  = document.getElementById('mobileToggle');
  const overlay  = document.getElementById('sidebarOverlay');
  const KEY      = 'adminSidebarCollapsed';

  // Restore desktop state
  if (localStorage.getItem(KEY) === '1') {
    sidebar.classList.add('collapsed');
    main.classList.add('sidebar-collapsed');
  }

  // Desktop toggle
  toggle.addEventListener('click', () => {
    const isCollapsed = sidebar.classList.toggle('collapsed');
    main.classList.toggle('sidebar-collapsed', isCollapsed);
    localStorage.setItem(KEY, isCollapsed ? '1' : '0');
  });

  // Mobile toggle
  mToggle.addEventListener('click', () => {
    sidebar.classList.toggle('mobile-open');
    overlay.classList.toggle('active');
  });

  // Close on overlay click
  overlay.addEventListener('click', () => {
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('active');
  });
})();
</script>

<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH F:\dl\web_timviec_updated\web_timviec\resources\views/layouts/admin.blade.php ENDPATH**/ ?>