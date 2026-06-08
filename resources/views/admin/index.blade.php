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
        <div style="font-size:10px;color:#94a3b8;margin-top:2px;">
          {{ $totalEmployees }} ứng viên · {{ $totalEmployers }} NTD
        </div>
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
        <div class="stat-card__label">Hoạt động / Bị khóa</div>
      </div>
    </div>

    {{-- Tin tuyển dụng --}}
    <div class="stat-card" style="border-left:4px solid #f57c00;">
      <div class="stat-card__icon stat-card__icon-orange"><i class="fas fa-briefcase"></i></div>
      <div>
        <div class="stat-card__num">{{ $totalJobs }}</div>
        <div class="stat-card__label">Tin tuyển dụng</div>
        <div style="font-size:10px;color:#94a3b8;margin-top:2px;">
          <span style="color:#10b981;">{{ $openJobs }} mở</span> ·
          <span style="color:#94a3b8;">{{ $hiddenJobs }} ẩn</span> ·
          <span style="color:#ef4444;">{{ $closedJobs }} đóng</span>
        </div>
      </div>
    </div>

    {{-- Doanh thu gói Premium --}}
    <div class="stat-card" style="border-left:4px solid #8b5cf6;">
      <div class="stat-card__icon" style="background:#ede9fe; color:#8b5cf6; width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center;">
        <i class="fas fa-crown"></i>
      </div>
      <div>
        <div class="stat-card__num" style="font-size:16px;">{{ number_format($totalRevenue) }}đ</div>
        <div class="stat-card__label">Doanh thu (VNPay)</div>
        <div style="font-size:11px; color:#8b5cf6; margin-top:2px; font-weight:600;">
          {{ $premiumUsers }} Premium · {{ $trialUsers }} Trial
        </div>
      </div>
    </div>

  </div>

  {{-- Row 2: Ứng tuyển + Giao dịch --}}
  <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:14px;">
    <div class="stat-card" style="border-left:4px solid #06b6d4;">
      <div class="stat-card__icon" style="background:#ecfeff; color:#06b6d4; width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center;">
        <i class="fas fa-file-alt"></i>
      </div>
      <div>
        <div class="stat-card__num">{{ $totalApplicationsCount }}</div>
        <div class="stat-card__label">Đơn ứng tuyển</div>
      </div>
    </div>

    <div class="stat-card" style="border-left:4px solid #ec4899;">
      <div class="stat-card__icon" style="background:#fdf2f8; color:#ec4899; width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center;">
        <i class="fas fa-credit-card"></i>
      </div>
      <div>
        <div class="stat-card__num">{{ \App\Models\User::where('plan','premium')->count() }}</div>
        <div class="stat-card__label">Tài khoản Premium</div>
      </div>
    </div>

    <a href="{{ url('/admin/transactions') }}" style="text-decoration:none;">
      <div class="stat-card" style="border-left:4px solid #f59e0b; cursor:pointer; transition:transform .15s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="stat-card__icon" style="background:#fffbeb; color:#f59e0b; width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center;">
          <i class="fas fa-exchange-alt"></i>
        </div>
        <div>
          <div class="stat-card__num">{{ \Illuminate\Support\Facades\DB::table('payments')->where('status','success')->count() }}</div>
          <div class="stat-card__label">Giao dịch thành công</div>
        </div>
      </div>
    </a>
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
            <td style="text-align:center; vertical-align:middle;">
              <div style="display:flex; gap:6px; justify-content:center; align-items:center;">
                {{-- Vai trò (chỉ xem) --}}
                @if($u->user_type === 'admin')
                  <span class="tag" style="width:80px; justify-content:center; background:#fef2f2; color:#ef4444; border:1px solid #fee2e2;">
                    <i class="fas fa-shield-alt"></i> Admin
                  </span>
                @elseif($u->user_type === 'employer')
                  <span class="tag" style="width:80px; justify-content:center; background:#fff7ed; color:#f97316; border:1px solid #ffedd5;">NTD</span>
                @else
                  <span class="tag" style="width:80px; justify-content:center; background:#eff6ff; color:#3b82f6; border:1px solid #dbeafe;">Ứng viên</span>
                @endif

                {{-- Khóa / Mở khóa --}}
                @if($u->user_type !== 'admin')
                <form action="{{ url('/admin/users/'.$u->id.'/ban') }}" method="POST" style="display:inline; margin:0;">
                  @csrf
                  <button type="submit" title="{{ $u->is_banned ? 'Mở khóa tài khoản' : 'Khóa tài khoản' }}" class="tag"
                    style="border:none; cursor:pointer;
                      background:{{ $u->is_banned ? '#ecfdf5' : '#fef2f2' }};
                      color:{{ $u->is_banned ? '#10b981' : '#ef4444' }};
                      border:1px solid {{ $u->is_banned ? '#d1fae5' : '#fee2e2' }};">
                    <i class="fas {{ $u->is_banned ? 'fa-lock-open' : 'fa-lock' }}"></i>
                    {{ $u->is_banned ? 'Mở' : 'Khóa' }}
                  </button>
                </form>
                @endif
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
     KHU VỰC PHÍA DƯỚI: Thống kê tỉ lệ & Truy cập nhanh + Giao dịch gần đây
     ══════════════════════════════════════════════════════════════════════ --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">

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

      <a href="{{ url('/admin/jobs') }}" style="display:flex; align-items:center; gap:12px; padding:12px 14px; background:#fff7ed; border-radius:10px; text-decoration:none; border:1px solid #ffedd5;">
        <div style="width:34px; height:34px; background:#f97316; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
          <i class="fas fa-briefcase" style="color:#fff; font-size:14px;"></i>
        </div>
        <div>
          <div class="fw-700 fs-13" style="color:#7c2d12;">Quản lý tin tuyển dụng</div>
          <div style="font-size:11px; color:#64748b;">{{ $openJobs }} đang mở · {{ $totalJobs }} tổng</div>
        </div>
        <i class="fas fa-chevron-right" style="color:#f97316; margin-left:auto;"></i>
      </a>

      <a href="{{ url('/admin/transactions') }}" style="display:flex; align-items:center; gap:12px; padding:12px 14px; background:#f5f3ff; border-radius:10px; text-decoration:none; border:1px solid #ddd6fe;">
        <div style="width:34px; height:34px; background:#8b5cf6; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
          <i class="fas fa-credit-card" style="color:#fff; font-size:14px;"></i>
        </div>
        <div>
          <div class="fw-700 fs-13" style="color:#4c1d95;">Lịch sử giao dịch</div>
          <div style="font-size:11px; color:#64748b;">Thanh toán Premium qua VNPay</div>
        </div>
        <i class="fas fa-chevron-right" style="color:#8b5cf6; margin-left:auto;"></i>
      </a>

      <a href="{{ url('/admin/users') }}?status=banned" style="display:flex; align-items:center; gap:12px; padding:12px 14px; background:#fef2f2; border-radius:10px; text-decoration:none; border:1px solid #fee2e2;">
        <div style="width:34px; height:34px; background:#ef4444; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
          <i class="fas fa-ban" style="color:#fff; font-size:14px;"></i>
        </div>
        <div>
          <div class="fw-700 fs-13" style="color:#7f1d1d;">Tài khoản bị khóa</div>
          <div style="font-size:11px; color:#64748b;">{{ $bannedUsers }} tài khoản đang bị khóa</div>
        </div>
        <i class="fas fa-chevron-right" style="color:#ef4444; margin-left:auto;"></i>
      </a>
    </div>
  </div>

</div>

{{-- Giao dịch gần đây --}}
@if($recentTransactions->count() > 0)
<div class="card" style="margin-bottom:20px;">
  <div class="card-header" style="background:#f8fafc; border-bottom:1px solid var(--border);">
    <span class="fw-800 fs-14 text-secondary"><i class="fas fa-history text-primary mr-6"></i> Giao dịch gần đây</span>
    <a href="{{ url('/admin/transactions') }}" class="btn btn-outline btn-sm">Xem tất cả</a>
  </div>
  <table class="table">
    <thead>
      <tr style="background:#f8fafc;">
        <th>Khách hàng</th>
        <th>Gói mua</th>
        <th style="text-align:right;">Số tiền</th>
        <th style="text-align:center;">Trạng thái</th>
        <th>Thời gian</th>
      </tr>
    </thead>
    <tbody>
      @foreach($recentTransactions as $txn)
        <tr>
          <td>
            <div class="fw-600 fs-13">{{ $txn->user_name }}</div>
            <div class="text-muted fs-11">{{ $txn->user_email }}</div>
          </td>
          <td>
            @if($txn->plan === 'yearly')
              <span class="tag fs-11" style="background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe;">Năm</span>
            @elseif($txn->plan === 'monthly')
              <span class="tag fs-11" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;">Tháng</span>
            @else
              <span class="tag fs-11" style="background:#f1f5f9;color:#475569;">Token</span>
            @endif
          </td>
          <td style="text-align:right;" class="fw-700 fs-13">{{ number_format($txn->amount) }}đ</td>
          <td style="text-align:center;">
            @if($txn->status === 'success' || $txn->status === 'paid')
              <span style="font-size:11px;font-weight:600;background:#ecfdf5;color:#10b981;border:1px solid #d1fae5;padding:2px 8px;border-radius:4px;">✅ Thành công</span>
            @elseif($txn->status === 'pending')
              <span style="font-size:11px;font-weight:600;background:#fffbeb;color:#d97706;border:1px solid #fef3c7;padding:2px 8px;border-radius:4px;">⏳ Chờ</span>
            @else
              <span style="font-size:11px;font-weight:600;background:#fef2f2;color:#ef4444;border:1px solid #fee2e2;padding:2px 8px;border-radius:4px;">❌ Thất bại</span>
            @endif
          </td>
          <td class="text-muted fs-12">{{ \Carbon\Carbon::parse($txn->created_at)->format('H:i d/m/Y') }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endif

@endsection
