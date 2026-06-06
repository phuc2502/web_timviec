@extends('layouts.admin')
@section('title', 'Quản lý người dùng')

@section('content')
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-18 fw-800" style="color:var(--secondary)">Quản lý người dùng</h1>
    <p class="text-muted fs-13 mt-2">Tổng số: <strong>{{ $users->total() }}</strong> thành viên hệ thống</p>
  </div>
  <form action="{{ url('/admin/users') }}" method="GET">
    <div style="display:flex; gap:8px; align-items:center;">
      <input type="text" name="search" class="form-control" style="width:220px; font-size:13px;"
        placeholder="Tìm theo tên, email..." value="{{ request('search') }}">
      <select name="type" class="form-control" style="width:140px; font-size:13px; cursor:pointer;">
        <option value="">Tất cả vai trò</option>
        <option value="employee" {{ request('type') == 'employee' ? 'selected' : '' }}>Ứng viên</option>
        <option value="employer" {{ request('type') == 'employer' ? 'selected' : '' }}>Nhà tuyển dụng</option>
        <option value="admin"    {{ request('type') == 'admin'    ? 'selected' : '' }}>Admin</option>
      </select>
      <button type="submit" class="btn btn-primary btn-sm" style="padding:0 14px; height:38px;">
        <i class="fas fa-search"></i> Tìm
      </button>
      @if(request()->anyFilled(['search','type']))
        <a href="{{ url('/admin/users') }}" class="btn btn-light btn-sm" style="height:38px; padding:0 12px;">
          <i class="fas fa-times"></i>
        </a>
      @endif
    </div>
  </form>
</div>

<div class="card shadow-sm" style="border-radius:var(--radius-lg); overflow:hidden;">
  <table class="table" style="vertical-align:middle;">
    <thead>
      <tr style="background:#f8fafc; border-bottom:1px solid var(--border);">
        <th style="width:50px;">ID</th>
        <th>Họ tên & Email</th>
        <th style="width:170px;">Vai trò</th>
        <th>Xác minh</th>
        <th style="width:170px;">Gói dịch vụ</th>
        <th>Ngày tham gia</th>
        <th style="text-align:center; width:120px;">Thao tác</th>
      </tr>
    </thead>
    <tbody>
      @forelse($users as $user)
        <tr>
          <td class="text-muted fs-12 fw-700">#{{ $user->id }}</td>
          <td>
            <div class="flex gap-10" style="align-items:center">
              <div class="avatar avatar-sm avatar-placeholder"
                style="background:var(--primary-light); color:var(--primary); font-size:12px; font-weight:700; flex-shrink:0;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
              </div>
              <div style="min-width:0; flex:1;">
                <div class="fw-700 fs-13" style="color:var(--secondary); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                  {{ $user->name }}
                </div>
                <div class="text-muted fs-12" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                  {{ $user->email }}
                </div>
              </div>
            </div>
          </td>
          <td>
            <form action="{{ url('/admin/users/'.$user->id.'/role') }}" method="POST" style="margin:0;">
              @csrf
              <select name="user_type" class="form-control"
                style="font-size:11px; padding:4px 8px; height:auto; cursor:pointer;" onchange="this.form.submit()">
                <option value="employee" {{ $user->user_type === 'employee' ? 'selected' : '' }}>👤 Ứng viên</option>
                <option value="employer" {{ $user->user_type === 'employer' ? 'selected' : '' }}>🏢 Doanh nghiệp</option>
                <option value="admin"    {{ $user->user_type === 'admin'    ? 'selected' : '' }}>🔑 Admin</option>
              </select>
            </form>
          </td>
          <td>
            @if($user->email_verified_at)
              <span style="font-size:11px; font-weight:600; background:#ecfdf5; color:#10b981; border:1px solid #d1fae5; padding:2px 8px; border-radius:4px;">Đã xác minh</span>
            @else
              <span style="font-size:11px; font-weight:600; background:#fffbeb; color:#d97706; border:1px solid #fef3c7; padding:2px 8px; border-radius:4px;">Chờ xác minh</span>
            @endif
          </td>
          <td>
            <form action="{{ url('/admin/users/'.$user->id.'/plan') }}" method="POST" style="margin:0;">
              @csrf
              <select name="plan" class="form-control"
                style="font-size:11px; padding:4px 8px; height:auto; cursor:pointer;" onchange="this.form.submit()">
                <option value="trial"   {{ $user->plan === 'trial'   ? 'selected' : '' }}>Dùng thử</option>
                <option value="premium" {{ $user->plan === 'premium' ? 'selected' : '' }}>👑 Premium</option>
              </select>
            </form>
          </td>
          <td class="text-muted fs-12">{{ $user->created_at->format('d/m/Y') }}</td>
          <td style="text-align:center;">
            <div style="display:flex; gap:6px; justify-content:center;">
              {{-- Nút xem chi tiết --}}
              <button onclick="openUserModal({{ $user->id }})" title="Xem chi tiết"
                style="border:none; background:#eff6ff; color:#3b82f6; border-radius:6px; padding:5px 10px; font-size:12px; cursor:pointer; font-weight:600; transition:all .15s;"
                onmouseover="this.style.background='#3b82f6';this.style.color='#fff';"
                onmouseout="this.style.background='#eff6ff';this.style.color='#3b82f6';">
                <i class="fas fa-eye"></i>
              </button>
              {{-- Nút khóa/mở --}}
              <form action="{{ url('/admin/users/'.$user->id.'/ban') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit"
                  onclick="return confirm('{{ $user->is_banned ? 'MỞ KHÓA' : 'KHÓA' }} tài khoản [{{ $user->name }}]?')"
                  title="{{ $user->is_banned ? 'Mở khóa' : 'Khóa tài khoản' }}"
                  style="border:none; background:{{ $user->is_banned ? '#ecfdf5' : '#fef2f2' }}; color:{{ $user->is_banned ? '#10b981' : '#ef4444' }}; border-radius:6px; padding:5px 10px; font-size:12px; cursor:pointer; font-weight:600; transition:all .15s;"
                  onmouseover="this.style.opacity='.75';" onmouseout="this.style.opacity='1';">
                  <i class="fas {{ $user->is_banned ? 'fa-lock-open' : 'fa-ban' }}"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="text-center text-muted" style="padding:40px;">
          <i class="fas fa-users" style="font-size:28px; opacity:.3; display:block; margin-bottom:8px;"></i>
          Không tìm thấy thành viên nào.
        </td></tr>
      @endforelse
    </tbody>
  </table>

  @if($users->hasPages())
    <div class="card-footer" style="background:#f8fafc; border-top:1px solid var(--border);">
      <div class="flex-between">
        <span class="text-muted fs-13">Đang xem {{ $users->firstItem() }}–{{ $users->lastItem() }} / {{ $users->total() }}</span>
        <div class="pagination">
          @if(!$users->onFirstPage())<a href="{{ $users->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>@endif
          @foreach($users->getUrlRange(max(1,$users->currentPage()-2), min($users->lastPage(),$users->currentPage()+2)) as $page => $url)
            @if($page == $users->currentPage())
              <span class="active" style="background:var(--primary); color:white;">{{ $page }}</span>
            @else
              <a href="{{ $url }}">{{ $page }}</a>
            @endif
          @endforeach
          @if($users->hasMorePages())<a href="{{ $users->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>@endif
        </div>
      </div>
    </div>
  @endif
</div>

{{-- ══════════════════════════════════════════════
     MODAL CHI TIẾT NGƯỜI DÙNG
══════════════════════════════════════════════ --}}
<div id="userModal" onclick="if(event.target===this)closeUserModal()"
  style="display:none; position:fixed; inset:0; background:rgba(15,23,42,.55); backdrop-filter:blur(4px);
         z-index:9999; align-items:center; justify-content:center; padding:20px;">
  <div style="background:#fff; border-radius:16px; width:100%; max-width:560px; max-height:90vh;
              overflow-y:auto; box-shadow:0 24px 60px rgba(0,0,0,.25); animation:modalIn .2s ease;">

    {{-- Header modal --}}
    <div id="uModalHeader"
      style="padding:24px 24px 0; display:flex; align-items:center; gap:16px; position:relative;">
      <img id="uAvatar" src="" alt="avatar"
        style="width:64px; height:64px; border-radius:50%; object-fit:cover; flex-shrink:0; box-shadow:0 4px 12px rgba(0,0,0,.15);">
      <div style="flex:1; min-width:0;">
        <div id="uName" style="font-size:18px; font-weight:800; color:#0f172a; margin-bottom:4px;"></div>
        <div id="uEmail" style="font-size:13px; color:#64748b;"></div>
        <div style="margin-top:6px; display:flex; gap:6px; flex-wrap:wrap;">
          <span id="uRoleBadge" style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px;"></span>
          <span id="uPlanBadge" style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px;"></span>
          <span id="uVerifyBadge" style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px;"></span>
          <span id="uBanBadge" style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px; display:none;"></span>
        </div>
      </div>
      <button onclick="closeUserModal()"
        style="position:absolute; top:16px; right:16px; border:none; background:#f1f5f9; border-radius:50%; width:32px; height:32px;
               cursor:pointer; font-size:16px; color:#64748b; display:flex; align-items:center; justify-content:center;"
        onmouseover="this.style.background='#e2e8f0';" onmouseout="this.style.background='#f1f5f9';">
        <i class="fas fa-times"></i>
      </button>
    </div>

    {{-- Body --}}
    <div id="uModalBody" style="padding:20px 24px 24px;">
      {{-- Loading state --}}
      <div id="uLoading" style="text-align:center; padding:40px; color:#94a3b8;">
        <i class="fas fa-circle-notch fa-spin" style="font-size:28px; margin-bottom:8px; display:block;"></i>
        Đang tải...
      </div>

      {{-- Content (hidden until loaded) --}}
      <div id="uContent" style="display:none;">
        {{-- Thống kê nhanh --}}
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:20px;">
          <div style="background:#f8fafc; border-radius:10px; padding:14px; text-align:center;">
            <div id="uListingsCount" style="font-size:22px; font-weight:800; color:#3b82f6;"></div>
            <div style="font-size:11px; color:#64748b; margin-top:2px;">Tin đã đăng</div>
          </div>
          <div style="background:#f8fafc; border-radius:10px; padding:14px; text-align:center;">
            <div id="uJoinedAt" style="font-size:14px; font-weight:700; color:#0f172a;"></div>
            <div style="font-size:11px; color:#64748b; margin-top:2px;">Ngày tham gia</div>
          </div>
          <div style="background:#f8fafc; border-radius:10px; padding:14px; text-align:center;">
            <div id="uBillingEnds" style="font-size:14px; font-weight:700; color:#0f172a;"></div>
            <div style="font-size:11px; color:#64748b; margin-top:2px;">Hết hạn gói</div>
          </div>
        </div>

        {{-- Thông tin cá nhân (employee) --}}
        <div id="uEmployeeInfo" style="display:none;">
          <div style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; margin-bottom:10px;">
            Thông tin ứng viên
          </div>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:16px;">
            <div class="uInfoRow"><span class="uInfoLabel"><i class="fas fa-map-marker-alt"></i> Địa điểm</span><span id="uLocation" class="uInfoVal"></span></div>
            <div class="uInfoRow"><span class="uInfoLabel"><i class="fas fa-briefcase"></i> Kinh nghiệm</span><span id="uExpYears" class="uInfoVal"></span></div>
            <div class="uInfoRow"><span class="uInfoLabel"><i class="fas fa-money-bill-wave"></i> Mức lương</span><span id="uSalary" class="uInfoVal"></span></div>
          </div>
          <div id="uAboutWrap" style="display:none; background:#f8fafc; border-radius:8px; padding:12px; margin-bottom:16px;">
            <div style="font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:6px;">GIỚI THIỆU BẢN THÂN</div>
            <p id="uAbout" style="font-size:13px; color:#334155; margin:0; line-height:1.6;"></p>
          </div>
        </div>

        {{-- Thông tin công ty (employer) --}}
        <div id="uEmployerInfo" style="display:none;">
          <div style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; margin-bottom:10px;">
            Thông tin doanh nghiệp
          </div>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div class="uInfoRow"><span class="uInfoLabel"><i class="fas fa-building"></i> Tên công ty</span><span id="uCompanyName" class="uInfoVal"></span></div>
            <div class="uInfoRow"><span class="uInfoLabel"><i class="fas fa-users"></i> Quy mô</span><span id="uCompanySize" class="uInfoVal"></span></div>
            <div class="uInfoRow" style="grid-column:1/-1;"><span class="uInfoLabel"><i class="fas fa-globe"></i> Website</span><span id="uCompanyWebsite" class="uInfoVal"></span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
@keyframes modalIn { from { opacity:0; transform:scale(.95) translateY(10px); } to { opacity:1; transform:scale(1) translateY(0); } }
.uInfoRow { display:flex; flex-direction:column; gap:2px; background:#f8fafc; border-radius:8px; padding:10px 12px; }
.uInfoLabel { font-size:11px; color:#94a3b8; font-weight:600; }
.uInfoLabel i { width:14px; }
.uInfoVal { font-size:13px; font-weight:600; color:#0f172a; }
</style>

@push('scripts')
<script>
const USER_DETAIL_URL = '{{ url("/admin/users") }}';

function openUserModal(id) {
  const modal = document.getElementById('userModal');
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';

  // Reset to loading state
  document.getElementById('uLoading').style.display = 'block';
  document.getElementById('uContent').style.display = 'none';

  fetch(`${USER_DETAIL_URL}/${id}/detail`, {
    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
  })
  .then(r => r.json())
  .then(u => {
    // Avatar & basic info
    document.getElementById('uAvatar').src = u.avatar_url;
    document.getElementById('uName').textContent = u.name;
    document.getElementById('uEmail').textContent = u.email;

    // Role badge
    const roleMap = { employee: ['👤 Ứng viên','#eff6ff','#1d4ed8'], employer: ['🏢 Doanh nghiệp','#f5f3ff','#7c3aed'], admin: ['🔑 Admin','#fef3c7','#92400e'] };
    const [rLabel, rBg, rColor] = roleMap[u.user_type] || ['—','#f1f5f9','#475569'];
    const rBadge = document.getElementById('uRoleBadge');
    rBadge.textContent = rLabel; rBadge.style.background = rBg; rBadge.style.color = rColor;

    // Plan badge
    const pBadge = document.getElementById('uPlanBadge');
    if (u.plan === 'premium') { pBadge.textContent='👑 Premium'; pBadge.style.background='#fef9c3'; pBadge.style.color='#854d0e'; }
    else { pBadge.textContent='Dùng thử'; pBadge.style.background='#f1f5f9'; pBadge.style.color='#475569'; }

    // Verify badge
    const vBadge = document.getElementById('uVerifyBadge');
    if (u.email_verified) { vBadge.textContent='✓ Đã xác minh'; vBadge.style.background='#ecfdf5'; vBadge.style.color='#10b981'; }
    else { vBadge.textContent='⚠ Chưa xác minh'; vBadge.style.background='#fffbeb'; vBadge.style.color='#d97706'; }

    // Ban badge
    const banBadge = document.getElementById('uBanBadge');
    if (u.is_banned) { banBadge.textContent='🚫 Đang bị khóa'; banBadge.style.background='#fef2f2'; banBadge.style.color='#ef4444'; banBadge.style.display='inline-block'; }
    else { banBadge.style.display='none'; }

    // Stats
    document.getElementById('uListingsCount').textContent = u.listings_count;
    document.getElementById('uJoinedAt').textContent = u.created_at.split(' ')[0];
    document.getElementById('uBillingEnds').textContent = u.billing_ends || '—';

    // Role-specific info
    document.getElementById('uEmployeeInfo').style.display = (u.user_type === 'employee') ? 'block' : 'none';
    document.getElementById('uEmployerInfo').style.display = (u.user_type === 'employer') ? 'block' : 'none';

    if (u.user_type === 'employee') {
      document.getElementById('uLocation').textContent = u.location || '—';
      document.getElementById('uExpYears').textContent = u.experience_years ? u.experience_years + ' năm' : '—';
      document.getElementById('uSalary').textContent = u.desired_salary || '—';
      const aboutWrap = document.getElementById('uAboutWrap');
      if (u.about) { aboutWrap.style.display='block'; document.getElementById('uAbout').textContent = u.about; }
      else { aboutWrap.style.display='none'; }
    } else if (u.user_type === 'employer') {
      document.getElementById('uCompanyName').textContent = u.company_name || '—';
      document.getElementById('uCompanySize').textContent = u.company_size || '—';
      const ws = document.getElementById('uCompanyWebsite');
      if (u.company_website) { ws.innerHTML = `<a href="${u.company_website}" target="_blank" style="color:#3b82f6;">${u.company_website}</a>`; }
      else { ws.textContent = '—'; }
    }

    document.getElementById('uLoading').style.display = 'none';
    document.getElementById('uContent').style.display = 'block';
  })
  .catch(() => {
    document.getElementById('uLoading').innerHTML = '<i class="fas fa-exclamation-circle" style="color:#ef4444; font-size:24px; display:block; margin-bottom:8px;"></i>Không thể tải dữ liệu.';
  });
}

function closeUserModal() {
  document.getElementById('userModal').style.display = 'none';
  document.body.style.overflow = '';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeUserModal(); });
</script>
@endpush
@endsection
