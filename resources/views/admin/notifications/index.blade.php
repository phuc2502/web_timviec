@extends('layouts.admin')
@section('title', 'Quản lý Thông báo — Admin')

@section('content')

{{-- ── PAGE HEADER ─────────────────────────────────────────────────── --}}
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
  <div>
    <h1 style="font-size:18px; font-weight:800; color:var(--secondary); margin:0;">
      <i class="fas fa-bell" style="color:#f59e0b; margin-right:8px;"></i>Quản lý Thông báo
    </h1>
    <p style="color:#94a3b8; font-size:13px; margin:4px 0 0;">Xem, gửi và dọn dẹp thông báo toàn hệ thống</p>
  </div>
  <div style="display:flex; gap:8px; flex-wrap:wrap;">
    <button onclick="openBroadcastModal()"
      style="background:#f59e0b; color:#fff; border:none; border-radius:8px; padding:9px 16px; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px;">
      <i class="fas fa-bullhorn"></i> Gửi thông báo
    </button>
    <button onclick="openCleanupModal()"
      style="background:#ef4444; color:#fff; border:none; border-radius:8px; padding:9px 16px; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:6px;">
      <i class="fas fa-trash-alt"></i> Dọn dẹp
    </button>
  </div>
</div>

{{-- Flash --}}
@if(session('success'))
  <div style="background:#d1fae5; border:1px solid #6ee7b7; border-radius:8px; padding:12px 16px; margin-bottom:16px; color:#065f46; font-size:13px; display:flex; align-items:center; gap:8px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
  </div>
@endif
@if(session('error'))
  <div style="background:#fee2e2; border:1px solid #fca5a5; border-radius:8px; padding:12px 16px; margin-bottom:16px; color:#991b1b; font-size:13px; display:flex; align-items:center; gap:8px;">
    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
  </div>
@endif

{{-- ── STAT CARDS ──────────────────────────────────────────────────── --}}
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px;">

  <div style="background:#fff; border-radius:12px; padding:16px 18px; box-shadow:0 1px 6px rgba(0,0,0,.07); border-left:4px solid #f59e0b;">
    <div style="font-size:22px; font-weight:800; color:#f59e0b;">{{ number_format($stats['total']) }}</div>
    <div style="font-size:12px; color:#64748b; font-weight:600; margin-top:2px;">Tổng thông báo</div>
  </div>

  <div style="background:#fff; border-radius:12px; padding:16px 18px; box-shadow:0 1px 6px rgba(0,0,0,.07); border-left:4px solid #ef4444;">
    <div style="font-size:22px; font-weight:800; color:#ef4444;">{{ number_format($stats['unread']) }}</div>
    <div style="font-size:12px; color:#64748b; font-weight:600; margin-top:2px;">Chưa đọc</div>
  </div>

  <div style="background:#fff; border-radius:12px; padding:16px 18px; box-shadow:0 1px 6px rgba(0,0,0,.07); border-left:4px solid #10b981;">
    <div style="font-size:22px; font-weight:800; color:#10b981;">{{ number_format($stats['read']) }}</div>
    <div style="font-size:12px; color:#64748b; font-weight:600; margin-top:2px;">Đã đọc</div>
  </div>

  <div style="background:#fff; border-radius:12px; padding:16px 18px; box-shadow:0 1px 6px rgba(0,0,0,.07); border-left:4px solid #6366f1;">
    <div style="font-size:22px; font-weight:800; color:#6366f1;">{{ number_format($stats['today']) }}</div>
    <div style="font-size:12px; color:#64748b; font-weight:600; margin-top:2px;">Hôm nay</div>
  </div>

</div>

{{-- ── FILTER BAR ──────────────────────────────────────────────────── --}}
<div style="background:#fff; border-radius:12px; padding:16px 20px; margin-bottom:16px; box-shadow:0 1px 6px rgba(0,0,0,.06);">
  <form method="GET" action="{{ url('/admin/notifications') }}" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">

    <div style="flex:1; min-width:180px;">
      <label style="font-size:12px; font-weight:600; color:#64748b; display:block; margin-bottom:5px;">Tìm theo user</label>
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Tên hoặc email..."
        style="width:100%; padding:8px 12px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px;">
    </div>

    <div style="min-width:160px;">
      <label style="font-size:12px; font-weight:600; color:#64748b; display:block; margin-bottom:5px;">Loại thông báo</label>
      <select name="type" style="width:100%; padding:8px 12px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; cursor:pointer;">
        <option value="">Tất cả loại</option>
        @foreach($types as $type)
          <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
        @endforeach
      </select>
    </div>

    <div style="min-width:140px;">
      <label style="font-size:12px; font-weight:600; color:#64748b; display:block; margin-bottom:5px;">Trạng thái</label>
      <select name="read_status" style="width:100%; padding:8px 12px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; cursor:pointer;">
        <option value="">Tất cả</option>
        <option value="unread" {{ request('read_status') == 'unread' ? 'selected' : '' }}>Chưa đọc</option>
        <option value="read"   {{ request('read_status') == 'read'   ? 'selected' : '' }}>Đã đọc</option>
      </select>
    </div>

    <div style="min-width:130px;">
      <label style="font-size:12px; font-weight:600; color:#64748b; display:block; margin-bottom:5px;">Từ ngày</label>
      <input type="date" name="date_from" value="{{ request('date_from') }}"
        style="width:100%; padding:8px 10px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px;">
    </div>

    <div style="min-width:130px;">
      <label style="font-size:12px; font-weight:600; color:#64748b; display:block; margin-bottom:5px;">Đến ngày</label>
      <input type="date" name="date_to" value="{{ request('date_to') }}"
        style="width:100%; padding:8px 10px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px;">
    </div>

    <div style="display:flex; gap:6px; align-items:flex-end; padding-bottom:1px;">
      <button type="submit"
        style="background:var(--primary,#10b981); color:#fff; border:none; border-radius:8px; padding:9px 16px; font-size:13px; font-weight:600; cursor:pointer;">
        <i class="fas fa-search"></i> Lọc
      </button>
      @if(request()->anyFilled(['search','type','read_status','date_from','date_to']))
        <a href="{{ url('/admin/notifications') }}"
          style="background:#f1f5f9; color:#475569; border-radius:8px; padding:9px 14px; font-size:13px; font-weight:600; text-decoration:none;">
          <i class="fas fa-times"></i>
        </a>
      @endif
    </div>

  </form>
</div>

{{-- ── NOTIFICATION TABLE ──────────────────────────────────────────── --}}
<div style="background:#fff; border-radius:12px; box-shadow:0 1px 6px rgba(0,0,0,.07); overflow:hidden;">

  @php
    $typeIcons = [
      'shortlisted'        => ['icon'=>'🎉','bg'=>'#fef3c7','color'=>'#d97706'],
      'application_status' => ['icon'=>'📋','bg'=>'#eff6ff','color'=>'#3b82f6'],
      'new_application'    => ['icon'=>'📥','bg'=>'#f0fdf4','color'=>'#16a34a'],
      'payment'            => ['icon'=>'💳','bg'=>'#fdf4ff','color'=>'#9333ea'],
      'job_alert'          => ['icon'=>'🔔','bg'=>'#fff7ed','color'=>'#ea580c'],
      'profile_reminder'   => ['icon'=>'📝','bg'=>'#f8fafc','color'=>'#475569'],
      'admin_broadcast'    => ['icon'=>'📢','bg'=>'#fef2f2','color'=>'#dc2626'],
    ];
  @endphp

  <table style="width:100%; border-collapse:collapse;">
    <thead>
      <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
        <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-align:left; width:50px;">ID</th>
        <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-align:left;">Người nhận</th>
        <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-align:left; width:140px;">Loại</th>
        <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-align:left;">Nội dung</th>
        <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-align:center; width:100px;">Trạng thái</th>
        <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-align:left; width:130px;">Thời gian</th>
        <th style="padding:12px 16px; font-size:12px; font-weight:700; color:#64748b; text-align:center; width:80px;">Thao tác</th>
      </tr>
    </thead>
    <tbody>
      @forelse($notifications as $notif)
        @php $ti = $typeIcons[$notif->type] ?? ['icon'=>'🔔','bg'=>'#f1f5f9','color'=>'#475569']; @endphp
        <tr style="border-bottom:1px solid #f1f5f9; transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='{{ $notif->isUnread() ? '#fffbeb' : '#fff' }}'">
          <td style="padding:12px 16px; font-size:12px; color:#94a3b8; font-weight:700;">#{{ $notif->id }}</td>
          <td style="padding:12px 16px;">
            @if($notif->user)
              <a href="{{ url('/admin/users/'.$notif->user->id) }}"
                style="font-weight:600; font-size:13px; color:var(--secondary); text-decoration:none;">
                {{ $notif->user->name }}
              </a>
              <div style="font-size:11px; color:#94a3b8;">{{ $notif->user->email }}</div>
            @else
              <span style="font-size:12px; color:#94a3b8;">—</span>
            @endif
          </td>
          <td style="padding:12px 16px;">
            <span style="display:inline-flex; align-items:center; gap:5px; background:{{ $ti['bg'] }}; color:{{ $ti['color'] }}; font-size:11px; font-weight:600; padding:3px 9px; border-radius:6px;">
              {{ $ti['icon'] }} {{ $notif->type }}
            </span>
          </td>
          <td style="padding:12px 16px;">
            <div style="font-weight:600; font-size:13px; color:#1e293b; margin-bottom:2px;">{{ $notif->title }}</div>
            <div style="font-size:12px; color:#64748b; line-height:1.5; max-width:360px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $notif->body }}</div>
          </td>
          <td style="padding:12px 16px; text-align:center;">
            @if($notif->isUnread())
              <span style="background:#fef3c7; color:#d97706; font-size:11px; font-weight:600; padding:3px 9px; border-radius:6px;">Chưa đọc</span>
            @else
              <span style="background:#ecfdf5; color:#10b981; font-size:11px; font-weight:600; padding:3px 9px; border-radius:6px;">Đã đọc</span>
            @endif
          </td>
          <td style="padding:12px 16px; font-size:12px; color:#64748b;">
            {{ $notif->created_at->format('d/m/Y') }}<br>
            <span style="color:#94a3b8;">{{ $notif->created_at->format('H:i') }}</span>
          </td>
          <td style="padding:12px 16px; text-align:center;">
            <form action="{{ url('/admin/notifications/'.$notif->id) }}" method="POST"
              onsubmit="return confirm('Xóa thông báo này?')">
              @csrf @method('DELETE')
              <button type="submit"
                style="background:#fee2e2; color:#ef4444; border:none; border-radius:6px; padding:5px 10px; font-size:12px; cursor:pointer; font-weight:600;"
                onmouseover="this.style.background='#ef4444';this.style.color='#fff'"
                onmouseout="this.style.background='#fee2e2';this.style.color='#ef4444'">
                <i class="fas fa-trash"></i>
              </button>
            </form>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" style="padding:60px; text-align:center; color:#94a3b8;">
            <i class="fas fa-bell-slash" style="font-size:32px; opacity:.3; display:block; margin-bottom:12px;"></i>
            Không có thông báo nào.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>

  @if($notifications->hasPages())
    <div style="padding:14px 20px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
      <span style="font-size:13px; color:#64748b;">
        Đang xem {{ $notifications->firstItem() }}–{{ $notifications->lastItem() }} / {{ $notifications->total() }}
      </span>
      <div style="display:flex; gap:4px;">
        @if(!$notifications->onFirstPage())
          <a href="{{ $notifications->previousPageUrl() }}" style="padding:6px 12px; background:#fff; border:1px solid #e2e8f0; border-radius:6px; font-size:13px; text-decoration:none; color:#475569;">‹</a>
        @endif
        @foreach($notifications->getUrlRange(max(1,$notifications->currentPage()-2), min($notifications->lastPage(),$notifications->currentPage()+2)) as $page => $url)
          @if($page == $notifications->currentPage())
            <span style="padding:6px 12px; background:var(--primary,#10b981); color:#fff; border-radius:6px; font-size:13px; font-weight:600;">{{ $page }}</span>
          @else
            <a href="{{ $url }}" style="padding:6px 12px; background:#fff; border:1px solid #e2e8f0; border-radius:6px; font-size:13px; text-decoration:none; color:#475569;">{{ $page }}</a>
          @endif
        @endforeach
        @if($notifications->hasMorePages())
          <a href="{{ $notifications->nextPageUrl() }}" style="padding:6px 12px; background:#fff; border:1px solid #e2e8f0; border-radius:6px; font-size:13px; text-decoration:none; color:#475569;">›</a>
        @endif
      </div>
    </div>
  @endif
</div>

{{-- ══ MODAL: GỬI THÔNG BÁO ══════════════════════════════════════════ --}}
<div id="broadcastModal" onclick="if(event.target===this)closeBroadcastModal()"
  style="display:none; position:fixed; inset:0; background:rgba(15,23,42,.55); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center; padding:20px;">
  <div style="background:#fff; border-radius:16px; width:100%; max-width:520px; padding:28px; box-shadow:0 24px 60px rgba(0,0,0,.2);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
      <h2 style="font-size:16px; font-weight:800; color:#1e293b; margin:0; display:flex; align-items:center; gap:8px;">
        <span style="font-size:20px;">📢</span> Gửi thông báo hàng loạt
      </h2>
      <button onclick="closeBroadcastModal()" style="border:none; background:#f1f5f9; border-radius:8px; width:32px; height:32px; cursor:pointer; font-size:16px; color:#64748b;">✕</button>
    </div>

    <form action="{{ url('/admin/notifications/broadcast') }}" method="POST">
      @csrf

      <div style="margin-bottom:14px;">
        <label style="font-size:13px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Tiêu đề</label>
        <input type="text" name="title" required maxlength="200" placeholder="Tiêu đề thông báo..."
          style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:14px; box-sizing:border-box;">
      </div>

      <div style="margin-bottom:14px;">
        <label style="font-size:13px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Nội dung</label>
        <textarea name="body" required maxlength="1000" rows="4" placeholder="Nội dung thông báo..."
          style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:14px; resize:vertical; box-sizing:border-box;"></textarea>
      </div>

      <div style="margin-bottom:14px;">
        <label style="font-size:13px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Gửi đến</label>
        <select name="target" id="broadcastTarget" onchange="toggleUserPicker(this.value)"
          style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:14px; cursor:pointer;">
          <option value="all">🌐 Tất cả người dùng</option>
          <option value="employee">👤 Chỉ ứng viên</option>
          <option value="employer">🏢 Chỉ nhà tuyển dụng</option>
          <option value="specific">🎯 Người dùng cụ thể</option>
        </select>
      </div>

      <div id="userPickerBox" style="display:none; margin-bottom:14px;">
        <label style="font-size:13px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">User ID hoặc email</label>
        <input type="number" name="user_id" placeholder="Nhập ID người dùng..."
          style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:14px; box-sizing:border-box;">
        <p style="font-size:11px; color:#94a3b8; margin:5px 0 0;">Xem ID tại trang Quản lý người dùng.</p>
      </div>

      <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
        <button type="button" onclick="closeBroadcastModal()"
          style="background:#f1f5f9; color:#475569; border:none; border-radius:8px; padding:10px 18px; font-size:13px; font-weight:600; cursor:pointer;">
          Hủy
        </button>
        <button type="submit"
          style="background:#f59e0b; color:#fff; border:none; border-radius:8px; padding:10px 18px; font-size:13px; font-weight:600; cursor:pointer;">
          <i class="fas fa-paper-plane"></i> Gửi ngay
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ══ MODAL: DỌN DẸP ════════════════════════════════════════════════ --}}
<div id="cleanupModal" onclick="if(event.target===this)closeCleanupModal()"
  style="display:none; position:fixed; inset:0; background:rgba(15,23,42,.55); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center; padding:20px;">
  <div style="background:#fff; border-radius:16px; width:100%; max-width:440px; padding:28px; box-shadow:0 24px 60px rgba(0,0,0,.2);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
      <h2 style="font-size:16px; font-weight:800; color:#1e293b; margin:0; display:flex; align-items:center; gap:8px;">
        <span style="font-size:20px;">🗑️</span> Dọn dẹp thông báo cũ
      </h2>
      <button onclick="closeCleanupModal()" style="border:none; background:#f1f5f9; border-radius:8px; width:32px; height:32px; cursor:pointer; font-size:16px; color:#64748b;">✕</button>
    </div>

    <form action="{{ url('/admin/notifications/cleanup') }}" method="POST"
      onsubmit="return confirm('Xác nhận xóa các thông báo cũ? Hành động này không thể hoàn tác.')">
      @csrf

      <div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; padding:12px 14px; margin-bottom:16px;">
        <p style="font-size:13px; color:#9a3412; margin:0;">
          <i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i>
          Thao tác này sẽ <strong>xóa vĩnh viễn</strong> các thông báo cũ khỏi hệ thống.
        </p>
      </div>

      <div style="margin-bottom:14px;">
        <label style="font-size:13px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Xóa thông báo cũ hơn (ngày)</label>
        <select name="older_than_days" style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:14px; cursor:pointer;">
          <option value="30">30 ngày</option>
          <option value="60">60 ngày</option>
          <option value="90" selected>90 ngày</option>
          <option value="180">6 tháng</option>
          <option value="365">1 năm</option>
        </select>
      </div>

      <div style="margin-bottom:20px; display:flex; align-items:center; gap:10px;">
        <input type="checkbox" name="only_read" value="1" id="onlyRead" style="width:16px; height:16px; cursor:pointer;">
        <label for="onlyRead" style="font-size:13px; color:#374151; cursor:pointer;">Chỉ xóa thông báo <strong>đã đọc</strong></label>
      </div>

      <div style="display:flex; gap:10px; justify-content:flex-end;">
        <button type="button" onclick="closeCleanupModal()"
          style="background:#f1f5f9; color:#475569; border:none; border-radius:8px; padding:10px 18px; font-size:13px; font-weight:600; cursor:pointer;">
          Hủy
        </button>
        <button type="submit"
          style="background:#ef4444; color:#fff; border:none; border-radius:8px; padding:10px 18px; font-size:13px; font-weight:600; cursor:pointer;">
          <i class="fas fa-trash-alt"></i> Xóa ngay
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openBroadcastModal()  { document.getElementById('broadcastModal').style.display='flex'; }
function closeBroadcastModal() { document.getElementById('broadcastModal').style.display='none'; }
function openCleanupModal()    { document.getElementById('cleanupModal').style.display='flex'; }
function closeCleanupModal()   { document.getElementById('cleanupModal').style.display='none'; }

function toggleUserPicker(val) {
  document.getElementById('userPickerBox').style.display = val === 'specific' ? 'block' : 'none';
  document.querySelector('[name=user_id]').required = (val === 'specific');
}
</script>

@endsection
