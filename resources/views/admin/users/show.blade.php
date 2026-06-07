@extends('layouts.admin')
@section('title', 'Chi tiết tài khoản — ' . $user->name)

@section('content')

{{-- ── BREADCRUMB ──────────────────────────────────────────────────── --}}
<div style="display:flex; align-items:center; gap:6px; margin-bottom:16px; font-size:13px; color:#94a3b8;">
  <a href="{{ url('/admin/users') }}" style="color:#10b981; text-decoration:none; font-weight:600;">Người dùng</a>
  <i class="fas fa-chevron-right" style="font-size:10px;"></i>
  <span style="color:#374151; font-weight:600;">{{ $user->name }}</span>
</div>

{{-- ── HEADER ──────────────────────────────────────────────────────── --}}
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
  <div style="display:flex; align-items:center; gap:14px;">
    <div style="width:52px; height:52px; border-radius:14px; background:linear-gradient(135deg,#10b981,#3b82f6); display:flex; align-items:center; justify-content:center; color:#fff; font-size:20px; font-weight:800; flex-shrink:0;">
      {{ strtoupper(mb_substr($user->name, 0, 1)) }}
    </div>
    <div>
      <h1 style="font-size:18px; font-weight:800; color:#1e293b; margin:0;">{{ $user->name }}</h1>
      <p style="font-size:13px; color:#64748b; margin:3px 0 0;">{{ $user->email }}</p>
    </div>
    @if($user->is_banned)
      <span style="background:#fee2e2; color:#dc2626; font-size:12px; font-weight:700; padding:4px 12px; border-radius:20px;">🔒 Bị khóa</span>
    @endif
  </div>
  <div style="display:flex; gap:8px; flex-wrap:wrap;">
    {{-- Khóa / Mở khóa --}}
    <form action="{{ url('/admin/users/'.$user->id.'/ban') }}" method="POST">
      @csrf
      <button type="submit" onclick="return confirm('{{ $user->is_banned ? 'Mở khóa' : 'Khóa' }} tài khoản {{ $user->name }}?')"
        style="background:{{ $user->is_banned ? '#ecfdf5' : '#fee2e2' }}; color:{{ $user->is_banned ? '#10b981' : '#ef4444' }}; border:1px solid {{ $user->is_banned ? '#6ee7b7' : '#fca5a5' }}; border-radius:8px; padding:9px 16px; font-size:13px; font-weight:600; cursor:pointer;">
        <i class="fas {{ $user->is_banned ? 'fa-lock-open' : 'fa-ban' }}"></i>
        {{ $user->is_banned ? 'Mở khóa' : 'Khóa tài khoản' }}
      </button>
    </form>
    {{-- Xóa --}}
    @if($user->user_type !== 'admin' && !$user->is_admin)
    <form action="{{ url('/admin/users/'.$user->id) }}" method="POST"
      onsubmit="return confirm('Xóa vĩnh viễn tài khoản {{ $user->name }}? Hành động này không thể hoàn tác!')">
      @csrf @method('DELETE')
      <button type="submit"
        style="background:#fee2e2; color:#dc2626; border:1px solid #fca5a5; border-radius:8px; padding:9px 16px; font-size:13px; font-weight:600; cursor:pointer;">
        <i class="fas fa-trash-alt"></i> Xóa tài khoản
      </button>
    </form>
    @endif
  </div>
</div>

{{-- Flash --}}
@if(session('success'))
  <div style="background:#d1fae5; border:1px solid #6ee7b7; border-radius:8px; padding:12px 16px; margin-bottom:16px; color:#065f46; font-size:13px;">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
  </div>
@endif

{{-- ── 2-COLUMN LAYOUT ─────────────────────────────────────────────── --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">

  {{-- ── CỘT TRÁI ─────────────────────────────────────────────── --}}
  <div style="display:flex; flex-direction:column; gap:16px;">

    {{-- Thông tin cơ bản --}}
    <div style="background:#fff; border-radius:14px; padding:22px; box-shadow:0 1px 6px rgba(0,0,0,.07);">
      <h3 style="font-size:14px; font-weight:700; color:#374151; margin:0 0 16px; display:flex; align-items:center; gap:7px;">
        <i class="fas fa-id-card" style="color:#6366f1;"></i> Thông tin cơ bản
      </h3>
      <div style="display:flex; flex-direction:column; gap:11px;">
        @php
          $info = [
            ['label'=>'ID', 'value'=>'#'.$user->id],
            ['label'=>'Loại tài khoản', 'value'=> ['employee'=>'👤 Ứng viên','employer'=>'🏢 Nhà tuyển dụng','admin'=>'🔑 Admin'][$user->user_type] ?? $user->user_type],
            ['label'=>'Email', 'value'=>$user->email],
            ['label'=>'Xác minh email', 'value'=>$user->email_verified_at ? '✅ Đã xác minh ('.($user->email_verified_at->format('d/m/Y')).')' : '⏳ Chưa xác minh'],
            ['label'=>'Ngày tham gia', 'value'=>$user->created_at->format('d/m/Y H:i')],
            ['label'=>'Đăng nhập gần nhất', 'value'=>'—'],
          ];
        @endphp
        @foreach($info as $row)
          <div style="display:flex; gap:10px; font-size:13px;">
            <span style="min-width:150px; color:#94a3b8; font-weight:600;">{{ $row['label'] }}</span>
            <span style="color:#1e293b; font-weight:500;">{{ $row['value'] }}</span>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Gói dịch vụ & Vai trò --}}
    <div style="background:#fff; border-radius:14px; padding:22px; box-shadow:0 1px 6px rgba(0,0,0,.07);">
      <h3 style="font-size:14px; font-weight:700; color:#374151; margin:0 0 16px; display:flex; align-items:center; gap:7px;">
        <i class="fas fa-crown" style="color:#f59e0b;"></i> Gói dịch vụ & Vai trò
      </h3>

      {{-- Đổi role --}}
      <form action="{{ url('/admin/users/'.$user->id.'/role') }}" method="POST" style="margin-bottom:14px;">
        @csrf
        <label style="font-size:12px; font-weight:600; color:#64748b; display:block; margin-bottom:6px;">Vai trò</label>
        <div style="display:flex; gap:8px;">
          <select name="user_type" style="flex:1; padding:9px 12px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; cursor:pointer;">
            <option value="employee" {{ $user->user_type == 'employee' ? 'selected' : '' }}>👤 Ứng viên</option>
            <option value="employer" {{ $user->user_type == 'employer' ? 'selected' : '' }}>🏢 Nhà tuyển dụng</option>
            <option value="admin"    {{ $user->user_type == 'admin'    ? 'selected' : '' }}>🔑 Admin</option>
          </select>
          <button type="submit" style="background:#6366f1; color:#fff; border:none; border-radius:8px; padding:9px 14px; font-size:13px; font-weight:600; cursor:pointer;">Cập nhật</button>
        </div>
      </form>

      {{-- Đổi plan --}}
      <form action="{{ url('/admin/users/'.$user->id.'/plan') }}" method="POST">
        @csrf
        <label style="font-size:12px; font-weight:600; color:#64748b; display:block; margin-bottom:6px;">Gói dịch vụ</label>
        <div style="display:flex; gap:8px;">
          <select name="plan" style="flex:1; padding:9px 12px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; cursor:pointer;">
            <option value="free"    {{ ($user->plan ?? 'free') == 'free'    ? 'selected' : '' }}>Miễn phí (Free)</option>
            <option value="trial"   {{ ($user->plan ?? 'free') == 'trial'   ? 'selected' : '' }}>Dùng thử (Trial)</option>
            <option value="premium" {{ ($user->plan ?? 'free') == 'premium' ? 'selected' : '' }}>👑 Premium</option>
          </select>
          <button type="submit" style="background:#f59e0b; color:#fff; border:none; border-radius:8px; padding:9px 14px; font-size:13px; font-weight:600; cursor:pointer;">Cập nhật</button>
        </div>
        @if($user->billing_ends)
          <p style="font-size:11px; color:#9333ea; margin:6px 0 0;"><i class="fas fa-calendar-check"></i> Hết hạn: {{ $user->billing_ends->format('d/m/Y') }}</p>
        @elseif($user->user_trial)
          <p style="font-size:11px; color:#d97706; margin:6px 0 0;"><i class="fas fa-clock"></i> Trial đến: {{ $user->user_trial->format('d/m/Y') }}</p>
        @endif
      </form>
    </div>

    {{-- Hồ sơ chi tiết --}}
    <div style="background:#fff; border-radius:14px; padding:22px; box-shadow:0 1px 6px rgba(0,0,0,.07);">
      <h3 style="font-size:14px; font-weight:700; color:#374151; margin:0 0 14px; display:flex; align-items:center; gap:7px;">
        <i class="fas fa-user-circle" style="color:#10b981;"></i> Hồ sơ chi tiết
      </h3>

      {{-- Completeness bar --}}
      @php $pct = $completeness['percent']; @endphp
      <div style="margin-bottom:16px;">
        <div style="display:flex; justify-content:space-between; font-size:12px; color:#64748b; margin-bottom:5px;">
          <span>Độ hoàn thiện hồ sơ</span>
          <span style="font-weight:700; color:{{ $pct >= 80 ? '#10b981' : ($pct >= 50 ? '#f59e0b' : '#ef4444') }}">{{ $pct }}%</span>
        </div>
        <div style="height:8px; background:#f1f5f9; border-radius:4px; overflow:hidden;">
          <div style="height:100%; width:{{ $pct }}%; background:{{ $pct >= 80 ? '#10b981' : ($pct >= 50 ? '#f59e0b' : '#ef4444') }}; border-radius:4px; transition:width .4s;"></div>
        </div>
        @if(!empty($completeness['missing']))
          <p style="font-size:11px; color:#94a3b8; margin:5px 0 0;">Còn thiếu: {{ implode(', ', $completeness['missing']) }}</p>
        @endif
      </div>

      @if($user->user_type === 'employer')
        <div style="display:flex; flex-direction:column; gap:10px;">
          @foreach([
            ['label'=>'Tên công ty', 'value'=>$user->company_name ?? '—'],
            ['label'=>'Website', 'value'=>$user->company_website ?? '—'],
            ['label'=>'Quy mô', 'value'=>$user->company_size ?? '—'],
            ['label'=>'Giới thiệu', 'value'=>Str::limit($user->about ?? '—', 120)],
          ] as $r)
            <div style="display:flex; gap:10px; font-size:13px;">
              <span style="min-width:120px; color:#94a3b8; font-weight:600;">{{ $r['label'] }}</span>
              <span style="color:#1e293b;">{{ $r['value'] }}</span>
            </div>
          @endforeach
        </div>
      @else
        <div style="display:flex; flex-direction:column; gap:10px;">
          @foreach([
            ['label'=>'Kỹ năng', 'value'=>is_array($user->skills) ? implode(', ', $user->skills) : ($user->skills ?? '—')],
            ['label'=>'Kinh nghiệm', 'value'=>$user->experience_years !== null ? $user->experience_years.' năm' : '—'],
            ['label'=>'Mức lương', 'value'=>$user->desired_salary ?? '—'],
            ['label'=>'Địa điểm', 'value'=>$user->location ?? '—'],
            ['label'=>'Loại công việc', 'value'=>$user->job_type_pref ?? '—'],
          ] as $r)
            <div style="display:flex; gap:10px; font-size:13px;">
              <span style="min-width:120px; color:#94a3b8; font-weight:600;">{{ $r['label'] }}</span>
              <span style="color:#1e293b;">{{ $r['value'] }}</span>
            </div>
          @endforeach
        </div>
      @endif
    </div>

  </div>{{-- end cột trái --}}

  {{-- ── CỘT PHẢI ─────────────────────────────────────────────── --}}
  <div style="display:flex; flex-direction:column; gap:16px;">

    {{-- Stats nhanh --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
      @php
        $qs = [
          ['icon'=>'fa-briefcase','bg'=>'#eff6ff','color'=>'#3b82f6','num'=>$user->listings_count ?? 0,'label'=>'Tin đã đăng'],
          ['icon'=>'fa-file-alt','bg'=>'#f0fdf4','color'=>'#16a34a','num'=>$user->applications_count ?? 0,'label'=>'Lần ứng tuyển'],
          ['icon'=>'fa-bell','bg'=>'#fff7ed','color'=>'#ea580c','num'=>$user->app_notifications_count ?? 0,'label'=>'Thông báo'],
          ['icon'=>'fa-bell-slash','bg'=>'#fef2f2','color'=>'#dc2626','num'=>$unreadCount,'label'=>'Chưa đọc'],
        ];
      @endphp
      @foreach($qs as $q)
        <div style="background:#fff; border-radius:12px; padding:14px 16px; box-shadow:0 1px 6px rgba(0,0,0,.06); display:flex; align-items:center; gap:12px;">
          <div style="width:38px; height:38px; border-radius:10px; background:{{ $q['bg'] }}; color:{{ $q['color'] }}; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <i class="fas {{ $q['icon'] }}"></i>
          </div>
          <div>
            <div style="font-size:20px; font-weight:800; color:{{ $q['color'] }};">{{ $q['num'] }}</div>
            <div style="font-size:11px; color:#94a3b8; font-weight:600;">{{ $q['label'] }}</div>
          </div>
        </div>
      @endforeach
    </div>

    {{-- Cài đặt thông báo --}}
    <div style="background:#fff; border-radius:14px; padding:22px; box-shadow:0 1px 6px rgba(0,0,0,.07);">
      <h3 style="font-size:14px; font-weight:700; color:#374151; margin:0 0 16px; display:flex; align-items:center; gap:7px;">
        <i class="fas fa-bell" style="color:#f59e0b;"></i> Cài đặt thông báo
      </h3>
      <form action="{{ url('/admin/users/'.$user->id.'/notification-settings') }}" method="POST">
        @csrf
        @php
          $toggles = [
            ['name'=>'mail',              'label'=>'Nhận email thông báo',     'desc'=>'Bật/tắt toàn bộ email từ hệ thống'],
            ['name'=>'notify_shortlist',  'label'=>'Thông báo shortlist',       'desc'=>'Khi nhà tuyển dụng shortlist hồ sơ'],
            ['name'=>'notify_app_status', 'label'=>'Cập nhật trạng thái đơn',  'desc'=>'Khi đơn ứng tuyển thay đổi trạng thái'],
            ['name'=>'notify_job_alert',  'label'=>'Cảnh báo việc làm mới',    'desc'=>'Email tuần với việc phù hợp kỹ năng'],
          ];
        @endphp
        @foreach($toggles as $t)
          <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid #f1f5f9;">
            <div>
              <div style="font-size:13px; font-weight:600; color:#374151;">{{ $t['label'] }}</div>
              <div style="font-size:11px; color:#94a3b8; margin-top:2px;">{{ $t['desc'] }}</div>
            </div>
            <label class="admin-toggle-label">
              <input type="checkbox" name="{{ $t['name'] }}" value="1" {{ $user->{$t['name']} ? 'checked' : '' }}
                onchange="this.form.submit()">
              <span class="admin-toggle-track"></span>
              <span class="admin-toggle-thumb"></span>
            </label>
          </div>
        @endforeach
        <button type="submit" style="display:none;"></button>
      </form>
    </div>

    {{-- Thông báo gần đây --}}
    <div style="background:#fff; border-radius:14px; padding:22px; box-shadow:0 1px 6px rgba(0,0,0,.07);">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
        <h3 style="font-size:14px; font-weight:700; color:#374151; margin:0; display:flex; align-items:center; gap:7px;">
          <i class="fas fa-history" style="color:#6366f1;"></i> Thông báo gần đây
        </h3>
        <a href="{{ url('/admin/notifications?search='.$user->email) }}"
          style="font-size:12px; color:#10b981; text-decoration:none; font-weight:600;">Xem tất cả →</a>
      </div>
      @forelse($recentNotifications as $notif)
        <div style="padding:10px 0; border-bottom:1px solid #f1f5f9; display:flex; gap:10px; align-items:flex-start;">
          <span style="font-size:18px; flex-shrink:0;">
            {{ ['shortlisted'=>'🎉','application_status'=>'📋','new_application'=>'📥','payment'=>'💳','job_alert'=>'🔔','profile_reminder'=>'📝','admin_broadcast'=>'📢'][$notif->type] ?? '🔔' }}
          </span>
          <div style="flex:1; min-width:0;">
            <div style="font-size:13px; font-weight:600; color:#1e293b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $notif->title }}</div>
            <div style="font-size:11px; color:#94a3b8; margin-top:2px;">{{ $notif->created_at->diffForHumans() }}</div>
          </div>
          @if($notif->isUnread())
            <span style="width:7px; height:7px; background:#f59e0b; border-radius:50%; flex-shrink:0; margin-top:5px;"></span>
          @endif
        </div>
      @empty
        <p style="font-size:13px; color:#94a3b8; text-align:center; padding:20px 0;">Chưa có thông báo nào.</p>
      @endforelse
    </div>

    {{-- Ứng tuyển / Tin đăng gần đây --}}
    @if($user->user_type === 'employee' && $recentApplications->isNotEmpty())
    <div style="background:#fff; border-radius:14px; padding:22px; box-shadow:0 1px 6px rgba(0,0,0,.07);">
      <h3 style="font-size:14px; font-weight:700; color:#374151; margin:0 0 14px; display:flex; align-items:center; gap:7px;">
        <i class="fas fa-file-alt" style="color:#10b981;"></i> Ứng tuyển gần đây
      </h3>
      @foreach($recentApplications as $app)
        <div style="padding:10px 0; border-bottom:1px solid #f1f5f9; font-size:13px;">
          <div style="font-weight:600; color:#1e293b;">{{ $app->listing->title ?? '—' }}</div>
          <div style="font-size:11px; color:#94a3b8; margin-top:2px; display:flex; gap:8px;">
            <span>{{ $app->created_at->format('d/m/Y') }}</span>
            <span style="background:#eff6ff; color:#3b82f6; padding:1px 7px; border-radius:4px; font-weight:600;">{{ ucfirst($app->status ?? 'pending') }}</span>
          </div>
        </div>
      @endforeach
    </div>
    @endif

    @if($user->user_type === 'employer' && $recentListings->isNotEmpty())
    <div style="background:#fff; border-radius:14px; padding:22px; box-shadow:0 1px 6px rgba(0,0,0,.07);">
      <h3 style="font-size:14px; font-weight:700; color:#374151; margin:0 0 14px; display:flex; align-items:center; gap:7px;">
        <i class="fas fa-briefcase" style="color:#6366f1;"></i> Tin đã đăng gần đây
      </h3>
      @foreach($recentListings as $listing)
        <div style="padding:10px 0; border-bottom:1px solid #f1f5f9; font-size:13px;">
          <div style="font-weight:600; color:#1e293b;">{{ $listing->title }}</div>
          <div style="font-size:11px; color:#94a3b8; margin-top:2px; display:flex; gap:8px;">
            <span>{{ $listing->created_at->format('d/m/Y') }}</span>
            @php
              $sc = ['open'=>['Đang mở','#ecfdf5','#10b981'],'hidden'=>['Ẩn','#f1f5f9','#64748b'],'pending'=>['Chờ duyệt','#fffbeb','#d97706'],'closed'=>['Đóng','#fee2e2','#ef4444']];
              $s = $sc[$listing->status ?? 'open'] ?? ['—','#f1f5f9','#94a3b8'];
            @endphp
            <span style="background:{{ $s[1] }}; color:{{ $s[2] }}; padding:1px 7px; border-radius:4px; font-weight:600;">{{ $s[0] }}</span>
          </div>
        </div>
      @endforeach
    </div>
    @endif

  </div>{{-- end cột phải --}}

</div>{{-- end grid --}}


@push('styles')
<style>
.admin-toggle-label { position:relative; display:inline-block; width:44px; height:24px; flex-shrink:0; cursor:pointer; }
.admin-toggle-label input { opacity:0; width:0; height:0; position:absolute; }
.admin-toggle-track { position:absolute; inset:0; background:#e2e8f0; border-radius:24px; transition:background .2s; }
.admin-toggle-label input:checked ~ .admin-toggle-track { background:#10b981; }
.admin-toggle-thumb { position:absolute; top:3px; left:3px; width:18px; height:18px; background:#fff; border-radius:50%; box-shadow:0 1px 3px rgba(0,0,0,.15); transition:transform .2s; }
.admin-toggle-label input:checked ~ .admin-toggle-thumb { transform:translateX(20px); }
</style>
@endpush

@endsection
