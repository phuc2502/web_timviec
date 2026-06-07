{{--
  Notification Bell — nhúng vào navbar dashboard layout
  Dùng: @include('partials.notification-bell')
--}}
<div style="position:relative;margin-right:8px" id="notif-wrapper">
  {{-- Bell icon với badge --}}
  <button id="notif-btn" onclick="toggleNotifDropdown()" style="background:none;border:none;cursor:pointer;padding:6px;position:relative;color:var(--text-secondary)">
    <i class="fas fa-bell fs-18"></i>
    <span id="notif-badge" style="display:none;position:absolute;top:2px;right:2px;background:#ef4444;color:#fff;border-radius:50%;min-width:18px;height:18px;font-size:10px;font-weight:700;align-items:center;justify-content:center;padding:0 4px;line-height:1"></span>
  </button>

  {{-- Dropdown --}}
  <div id="notif-dropdown" style="display:none;position:absolute;right:0;top:44px;background:#fff;border:1px solid var(--border);border-radius:var(--radius-lg);box-shadow:var(--shadow-lg);width:340px;max-height:480px;z-index:300;overflow:hidden;flex-direction:column">
    {{-- Header --}}
    <div style="padding:12px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
      <span class="fw-700 fs-14">Thông báo</span>
      <button onclick="markAllRead()" style="background:none;border:none;cursor:pointer;font-size:12px;color:var(--primary)">Đọc tất cả</button>
    </div>

    {{-- List --}}
    <div id="notif-list" style="overflow-y:auto;flex:1">
      <div style="padding:32px;text-align:center;color:var(--text-muted);font-size:13px" id="notif-empty">
        <i class="fas fa-bell-slash fs-24 mb-8" style="display:block;opacity:.4"></i>
        Chưa có thông báo
      </div>
    </div>

    {{-- Footer --}}
    <div style="padding:10px 16px;border-top:1px solid var(--border);text-align:center">
      <a href="{{ route('notifications.index') }}" style="font-size:13px;color:var(--primary);font-weight:600">Xem tất cả thông báo</a>
    </div>
  </div>
</div>

@push('scripts')
<script>
(function() {
  const POLL_INTERVAL = 60000; // 60 giây
  let isOpen = false;

  // Fetch và render notifications
  async function fetchNotifications() {
    try {
      const res = await fetch('/notifications', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      });
      const data = await res.json();
      renderNotifications(data.notifications, data.unread_count);
    } catch (e) { console.error('Notification fetch failed', e); }
  }

  function renderNotifications(items, unreadCount) {
    // Badge
    const badge = document.getElementById('notif-badge');
    if (unreadCount > 0) {
      badge.style.display = 'inline-flex';
      badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
    } else {
      badge.style.display = 'none';
    }

    // List
    const list  = document.getElementById('notif-list');
    const empty = document.getElementById('notif-empty');

    if (!items || items.length === 0) {
      list.innerHTML = empty.outerHTML;
      return;
    }

    list.innerHTML = items.map(n => {
      const isUnread  = !n.read_at;
      const timeAgo   = formatTimeAgo(n.created_at);
      const icons     = { shortlisted:'🎉', application_status:'📋', new_application:'📥', payment:'💳', job_alert:'🔔', profile_reminder:'📝' };
      const icon      = icons[n.type] || '🔔';

      return `<div onclick="markRead(${n.id}, this)" style="padding:12px 16px;border-bottom:1px solid var(--border);cursor:pointer;background:${isUnread ? 'var(--primary-light)' : '#fff'};display:flex;gap:10px;align-items:flex-start" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='${isUnread ? 'var(--primary-light)' : '#fff'}'">
        <div style="font-size:22px;line-height:1;flex-shrink:0">${icon}</div>
        <div style="flex:1;min-width:0">
          <div class="notif-title" style="font-size:13px;font-weight:${isUnread ? '700' : '500'};color:var(--text-primary);margin-bottom:2px">${escapeHtml(n.title)}</div>
          <div style="font-size:12px;color:var(--text-muted);line-height:1.5;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${escapeHtml(n.body)}</div>
          <div style="font-size:11px;color:var(--text-muted);margin-top:4px">${timeAgo}</div>
        </div>
        ${isUnread ? '<div class="notif-unread-dot" style="width:8px;height:8px;background:var(--primary);border-radius:50%;flex-shrink:0;margin-top:4px"></div>' : ''}
      </div>`;
    }).join('');
  }

  async function markRead(id, el) {
    try {
      await fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
      });
      el.style.background = '#fff';
      // Update onmouseout so hover no longer restores unread background
      el.setAttribute('onmouseout', "this.style.background='#fff'");
      el.querySelector('.notif-unread-dot')?.remove();
      // Update font-weight of title to read state
      const titleEl = el.querySelector('.notif-title');
      if (titleEl) titleEl.style.fontWeight = '500';
      fetchNotifications(); // Refresh badge
    } catch (e) {}
  }

  async function markAllRead() {
    try {
      await fetch('/notifications/read-all', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
      });
      fetchNotifications();
    } catch (e) {}
  }

  function toggleNotifDropdown() {
    const d = document.getElementById('notif-dropdown');
    isOpen = d.style.display === 'none' || d.style.display === '';
    d.style.display = isOpen ? 'flex' : 'none';
    d.style.flexDirection = 'column';
    if (isOpen) fetchNotifications();
  }

  function formatTimeAgo(dateStr) {
    if (!dateStr) return '';
    const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
    if (diff < 60)   return 'Vừa xong';
    if (diff < 3600) return Math.floor(diff/60) + ' phút trước';
    if (diff < 86400) return Math.floor(diff/3600) + ' giờ trước';
    return Math.floor(diff/86400) + ' ngày trước';
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // Close on outside click
  document.addEventListener('click', function(e) {
    if (!e.target.closest('#notif-wrapper')) {
      document.getElementById('notif-dropdown').style.display = 'none';
      isOpen = false;
    }
  });

  // Expose to inline onclick handlers
  window.toggleNotifDropdown = toggleNotifDropdown;
  window.markAllRead = markAllRead;
  window.markRead = markRead;

  // Initial fetch + poll
  fetchNotifications();
  setInterval(fetchNotifications, POLL_INTERVAL);
})();
</script>
@endpush
