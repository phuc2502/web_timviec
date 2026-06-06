@extends('layouts.admin')
@section('title', 'Admin Dashboard — Tổng quan hệ thống')

@section('content')

{{-- ══════════════════════════════════════════════════════════════════════
     KHU VỰC 1: WIDGET THỐNG KÊ TỔNG QUAN
     ══════════════════════════════════════════════════════════════════════ --}}

<div style="margin-bottom: 8px;">
  <h2 style="font-size:13px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:#94a3b8; margin:0 0 14px 0;">
    <i class="fas fa-chart-bar" style="margin-right:6px;"></i> Widget Thống kê Tổng quan
  </h2>

  <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:14px;">

    {{-- Tổng Users --}}
    <div class="stat-card" style="border-left:4px solid #1a73e8;">
      <div class="stat-card__icon stat-card__icon-blue"><i class="fas fa-users"></i></div>
      <div>
        <div class="stat-card__num">{{ $totalUsers }}</div>
        <div class="stat-card__label">Tổng thành viên</div>
      </div>
    </div>

    {{-- User hoạt động / bị khóa --}}
    <div class="stat-card" style="border-left:4px solid #10b981;">
      <div class="stat-card__icon stat-card__icon-green"><i class="fas fa-user-check"></i></div>
      <div>
        <div class="stat-card__num">
          <span style="color:#10b981;">{{ $activeUsers }}</span>
          <span style="font-size:13px; color:#94a3b8; font-weight:500;"> / </span>
          <span style="color:#ef4444; font-size:18px;">{{ $bannedUsers }}</span>
        </div>
        <div class="stat-card__label">User hoạt động / Bị khóa</div>
      </div>
    </div>

    {{-- Tin tuyển dụng --}}
    <div class="stat-card" style="border-left:4px solid #f57c00;">
      <div class="stat-card__icon stat-card__icon-orange"><i class="fas fa-briefcase"></i></div>
      <div>
        <div class="stat-card__num">{{ $totalJobs }}</div>
        <div class="stat-card__label">Tin tuyển dụng</div>
      </div>
    </div>

    {{-- Doanh thu gói Premium --}}
    <div class="stat-card" style="border-left:4px solid #8b5cf6;">
      <div class="stat-card__icon" style="background:#ede9fe; color:#8b5cf6; width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center;">
        <i class="fas fa-crown"></i>
      </div>
      <div>
        <div class="stat-card__num" style="font-size:16px;">{{ number_format($totalRevenue) }}đ</div>
        <div class="stat-card__label">Doanh thu gói Premium</div>
        <div style="font-size:11px; color:#8b5cf6; margin-top:2px; font-weight:600;">
          {{ $premiumUsers }} Premium · {{ $trialUsers }} Trial
        </div>
      </div>
    </div>

  </div>
</div>

{{-- Divider --}}
<hr style="border:none; border-top:1px solid var(--border); margin:20px 0;">

{{-- ══════════════════════════════════════════════════════════════════════
     KHU VỰC 2: PANEL QUẢN LÝ USER
     ══════════════════════════════════════════════════════════════════════ --}}

<div style="margin-bottom:20px;">

  {{-- PANEL: Quản lý User --}}
  <div class="card">
    <div class="card-header" style="background:#f8fafc; border-bottom:1px solid var(--border);">
      <span class="fw-800 fs-14 text-secondary">
        <i class="fas fa-users-cog text-primary mr-6"></i> Quản lý User & Phân quyền
      </span>
      <a href="{{ url('/admin/users') }}" class="btn btn-outline btn-sm">
        <i class="fas fa-cog"></i> Xem tất cả
      </a>
    </div>

    {{-- Danh sách Users — Phân quyền vai trò trực tiếp --}}
    <table class="table">
      <thead>
        <tr>
          <th>Thành viên</th>
          <th>Vai trò</th>
          <th>Trạng thái</th>
          <th style="text-align:center;">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        @forelse($recentUsers as $u)
          <tr>
            <td>
              <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:30px; height:30px; border-radius:50%; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; flex-shrink:0;">
                  {{ strtoupper(substr($u->name, 0, 1)) }}
                </div>
                <div>
                  <div class="fw-600 fs-13">{{ $u->name }}</div>
                  <div class="text-muted fs-11">{{ $u->email }}</div>
                </div>
              </div>
            </td>
            <td>
              @if($u->user_type === 'admin')
                <span class="tag fs-10" style="background:#fef2f2; color:#ef4444; border:1px solid #fee2e2;">Admin</span>
              @elseif($u->user_type === 'employer')
                <span class="tag fs-10" style="background:#fff7ed; color:#f97316; border:1px solid #ffedd5;">Doanh nghiệp</span>
              @else
                <span class="tag fs-10" style="background:#eff6ff; color:#3b82f6; border:1px solid #dbeafe;">Ứng viên</span>
              @endif
            </td>
            <td>
              @if($u->is_banned)
                <span class="tag fs-10" style="background:#fef2f2; color:#ef4444; border:1px solid #fee2e2;"><i class="fas fa-ban"></i> Bị khóa</span>
              @else
                <span class="tag fs-10" style="background:#ecfdf5; color:#10b981; border:1px solid #d1fae5;"><i class="fas fa-check-circle"></i> Hoạt động</span>
              @endif
            </td>
            <td style="text-align:center;">
              <div style="display:flex; gap:4px; justify-content:center; align-items:center; flex-wrap:wrap;">
                {{-- Đổi vai trò --}}
                <form action="{{ url('/admin/users/'.$u->id.'/role') }}" method="POST" style="display:inline;">
                  @csrf
                  <select name="user_type" onchange="this.form.submit()" style="font-size:11px; padding:2px 6px; border-radius:6px; border:1px solid var(--border); color:var(--secondary); background:#fff; cursor:pointer;">
                    <option value="employee" {{ $u->user_type === 'employee' ? 'selected' : '' }}>Ứng viên</option>
                    <option value="employer" {{ $u->user_type === 'employer' ? 'selected' : '' }}>NTD</option>
                    <option value="admin"    {{ $u->user_type === 'admin'    ? 'selected' : '' }}>Admin</option>
                  </select>
                </form>

                {{-- Khóa / Mở khóa bằng 1 click --}}
                <form action="{{ url('/admin/users/'.$u->id.'/ban') }}" method="POST" style="display:inline;">
                  @csrf
                  <button type="submit" title="{{ $u->is_banned ? 'Mở khóa tài khoản' : 'Khóa tài khoản' }}"
                    style="border:none; border-radius:6px; padding:3px 8px; font-size:11px; cursor:pointer; font-weight:600;
                      background:{{ $u->is_banned ? '#ecfdf5' : '#fef2f2' }};
                      color:{{ $u->is_banned ? '#10b981' : '#ef4444' }};">
                    <i class="fas {{ $u->is_banned ? 'fa-lock-open' : 'fa-lock' }}"></i>
                    {{ $u->is_banned ? 'Mở' : 'Khóa' }}
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="text-center text-muted" style="padding:24px;">Chưa có thành viên nào.</td></tr>
        @endforelse
      </tbody>
    </table>

    <div style="padding:10px 16px; background:#f8fafc; text-align:right; border-top:1px solid var(--border);">
      <a href="{{ url('/admin/users') }}" class="fs-12 fw-700 text-primary-color" style="text-decoration:none;">
        Quản lý đầy đủ phân quyền <i class="fas fa-chevron-right ml-4"></i>
      </a>
    </div>
  </div>

</div>

{{-- ══════════════════════════════════════════════════════════════════════
     KHU VỰC PHÍA DƯỚI: Thống kê tỉ lệ & Truy cập nhanh
     ══════════════════════════════════════════════════════════════════════ --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

  {{-- Phân loại thành viên --}}
  <div class="card">
    <div class="card-header" style="background:#f8fafc; border-bottom:1px solid var(--border);">
      <span class="fw-800 fs-14 text-secondary"><i class="fas fa-chart-pie text-primary mr-6"></i> Phân loại thành viên</span>
    </div>
    <div style="padding:16px; display:flex; flex-direction:column; gap:16px;">
      @php
        $empPct    = $totalUsers ? round($totalEmployees / $totalUsers * 100) : 0;
        $erPct     = $totalUsers ? round($totalEmployers / $totalUsers * 100) : 0;
        $bannedPct = $totalUsers ? round($bannedUsers / $totalUsers * 100) : 0;
        $premPct   = $totalUsers ? round($premiumUsers / $totalUsers * 100) : 0;
      @endphp

      <div>
        <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:5px;">
          <span class="text-muted fw-600">Ứng viên (Employee)</span>
          <span class="fw-700 text-primary-color">{{ $totalEmployees }} ({{ $empPct }}%)</span>
        </div>
        <div style="height:7px; background:var(--border); border-radius:8px; overflow:hidden;">
          <div style="height:100%; width:{{ $empPct }}%; background:#3b82f6; border-radius:8px;"></div>
        </div>
      </div>

      <div>
        <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:5px;">
          <span class="text-muted fw-600">Nhà tuyển dụng (Employer)</span>
          <span class="fw-700" style="color:#f97316;">{{ $totalEmployers }} ({{ $erPct }}%)</span>
        </div>
        <div style="height:7px; background:var(--border); border-radius:8px; overflow:hidden;">
          <div style="height:100%; width:{{ $erPct }}%; background:#f97316; border-radius:8px;"></div>
        </div>
      </div>

      <div>
        <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:5px;">
          <span class="text-muted fw-600">Tài khoản bị khóa</span>
          <span class="fw-700" style="color:#ef4444;">{{ $bannedUsers }} ({{ $bannedPct }}%)</span>
        </div>
        <div style="height:7px; background:var(--border); border-radius:8px; overflow:hidden;">
          <div style="height:100%; width:{{ $bannedPct }}%; background:#ef4444; border-radius:8px;"></div>
        </div>
      </div>

      <div>
        <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:5px;">
          <span class="text-muted fw-600">Gói Premium đang hoạt động</span>
          <span class="fw-700" style="color:#8b5cf6;">{{ $premiumUsers }} ({{ $premPct }}%)</span>
        </div>
        <div style="height:7px; background:var(--border); border-radius:8px; overflow:hidden;">
          <div style="height:100%; width:{{ $premPct }}%; background:#8b5cf6; border-radius:8px;"></div>
        </div>
      </div>
    </div>
  </div>

  {{-- Truy cập nhanh --}}
  <div class="card">
    <div class="card-header" style="background:#f8fafc; border-bottom:1px solid var(--border);">
      <span class="fw-800 fs-14 text-secondary"><i class="fas fa-rocket text-primary mr-6"></i> Truy cập nhanh</span>
    </div>
    <div style="padding:16px; display:flex; flex-direction:column; gap:10px;">

      <a href="{{ url('/admin/users') }}" style="display:flex; align-items:center; gap:12px; padding:12px 14px; background:#eff6ff; border-radius:10px; text-decoration:none; border:1px solid #dbeafe;">
        <div style="width:34px; height:34px; background:#3b82f6; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
          <i class="fas fa-users" style="color:#fff; font-size:14px;"></i>
        </div>
        <div>
          <div class="fw-700 fs-13" style="color:#1e3a5f;">Danh sách Users</div>
          <div style="font-size:11px; color:#64748b;">Phân quyền vai trò trực tiếp</div>
        </div>
        <i class="fas fa-chevron-right" style="color:#3b82f6; margin-left:auto;"></i>
      </a>

      <a href="{{ url('/admin/users') }}?action=ban" style="display:flex; align-items:center; gap:12px; padding:12px 14px; background:#fef2f2; border-radius:10px; text-decoration:none; border:1px solid #fee2e2;">
        <div style="width:34px; height:34px; background:#ef4444; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
          <i class="fas fa-ban" style="color:#fff; font-size:14px;"></i>
        </div>
        <div>
          <div class="fw-700 fs-13" style="color:#7f1d1d;">Khóa / Mở khóa tài khoản</div>
          <div style="font-size:11px; color:#64748b;">{{ $bannedUsers }} tài khoản đang bị khóa</div>
        </div>
        <i class="fas fa-chevron-right" style="color:#ef4444; margin-left:auto;"></i>
      </a>





    </div>
  </div>

</div>

@endsection
