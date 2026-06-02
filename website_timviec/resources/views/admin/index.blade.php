@extends('layouts.admin')
@section('title', 'Tổng quan hệ thống (Admin Dashboard)')

@section('content')
{{-- PANEL THÔNG TIN TỔNG QUAN --}}
<div class="grid-4 mb-24" style="gap:16px">
  <div class="stat-card" style="border-left: 4px solid #1a73e8;">
    <div class="stat-card__icon stat-card__icon-blue"><i class="fas fa-users"></i></div>
    <div>
      <div class="stat-card__num">{{ $totalUsers ?? 0 }}</div>
      <div class="stat-card__label">Tổng thành viên</div>
    </div>
  </div>
  <div class="stat-card" style="border-left: 4px solid #10b981;">
    <div class="stat-card__icon stat-card__icon-green"><i class="fas fa-briefcase"></i></div>
    <div>
      <div class="stat-card__num">{{ $totalJobs ?? 0 }}</div>
      <div class="stat-card__label">Tin tuyển dụng thật</div>
    </div>
  </div>
  <div class="stat-card" style="border-left: 4px solid #f57c00;">
    <div class="stat-card__icon stat-card__icon-orange"><i class="fas fa-file-invoice"></i></div>
    <div>
      <div class="stat-card__num">{{ $totalApplications ?? 0 }}</div>
      <div class="stat-card__label">Lượt nộp hồ sơ</div>
    </div>
  </div>
  <div class="stat-card" style="border-left: 4px solid #ef4444;">
    <div class="stat-card__icon stat-card__icon-red"><i class="fas fa-wallet"></i></div>
    <div>
      <div class="stat-card__num">{{ number_format($totalRevenue ?? 0) }}đ</div>
      <div class="stat-card__label">Doanh thu gói Premium</div>
    </div>
  </div>
</div>

<div class="grid-2-1">

  {{-- NGƯỜI DÙNG MỚI NHẤT & PHÂN QUYỀN CHỨC NĂNG QUICK ACCESS --}}
  <div class="card">
    <div class="card-header" style="background:#f8fafc; border-bottom:1px solid var(--border);">
      <span class="fw-800 fs-14 text-secondary"><i class="fas fa-users-cog text-primary mr-6"></i> Danh sách thành viên đăng ký gần đây</span>
      <a href="{{ url('/admin/users') }}" class="btn btn-outline btn-sm"><i class="fas fa-cog"></i> Quản lý & Phân quyền</a>
    </div>
    <table class="table">
      <thead>
        <tr>
          <th>Họ tên & Email</th>
          <th>Vai trò</th>
          <th>Ngày tham gia</th>
          <th>Gói dịch vụ</th>
        </tr>
      </thead>
      <tbody>
        @forelse($recentUsers ?? [] as $user)
          <tr>
            <td>
              <div class="flex gap-10" style="align-items:center">
                <div class="avatar avatar-sm avatar-placeholder" style="background:var(--primary-light); color:var(--primary); font-size:12px; font-weight:700;">
                  {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                  <div class="fw-600 fs-13">{{ $user->name }}</div>
                  <div class="text-muted fs-12">{{ $user->email }}</div>
                </div>
              </div>
            </td>
            <td>
              @if($user->user_type === 'admin')
                <span class="tag tag-red fs-10" style="background:#fef2f2; color:#ef4444; border:1px solid #fee2e2;">Admin</span>
              @elseif($user->user_type === 'employer')
                <span class="tag tag-orange fs-10" style="background:#fff7ed; color:#f97316; border:1px solid #ffedd5;">Doanh nghiệp</span>
              @else
                <span class="tag tag-blue fs-10" style="background:#eff6ff; color:#3b82f6; border:1px solid #dbeafe;">Ứng viên</span>
              @endif
            </td>
            <td class="text-muted fs-12">{{ $user->created_at->format('d/m/Y H:i') }}</td>
            <td class="fs-12">
              @if($user->plan === 'premium')
                <span class="tag tag-green fs-10" style="background:#ecfdf5; color:#10b981; border:1px solid #d1fae5;"><i class="fas fa-crown text-warning"></i> Premium</span>
              @elseif($user->plan === 'trial')
                <span class="tag tag-gray fs-10" style="background:#f8fafc; color:#64748b; border:1px solid #e2e8f0;">Dùng thử</span>
              @else
                <span class="text-muted">Miễn phí</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="text-center text-muted" style="padding:24px">Chưa có người dùng nào.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- CỘT PHẢI: TIN ĐĂNG GẦN ĐÂY & BIỂU ĐỒ TỈ LỆ --}}
  <div class="flex-col gap-16">
    
    {{-- Tin tuyển dụng mới --}}
    <div class="card">
      <div class="card-header" style="background:#f8fafc; border-bottom:1px solid var(--border);">
        <span class="fw-800 fs-14 text-secondary"><i class="fas fa-briefcase text-primary mr-6"></i> Tin tuyển dụng mới nhất</span>
      </div>
      <div class="card-body" style="padding: 0;">
        @forelse($recentJobs ?? [] as $job)
          <div style="padding: 14px 16px; border-bottom: 1px solid var(--border); display: flex; flex-direction: column; gap: 4px;">
            <div class="fw-700 fs-13 text-secondary">{{ Str::limit($job->title, 40) }}</div>
            <div class="flex-between" style="align-items: center;">
              <span class="text-muted fs-11"><i class="fas fa-building text-muted"></i> {{ $job->user->company_name ?? $job->user->name }}</span>
              <span class="badge" style="background:var(--primary-light); color:var(--primary); padding: 2px 6px; border-radius:10px; font-size:10px; font-weight:700;">
                {{ $job->users->count() }} hồ sơ nộp
              </span>
            </div>
          </div>
        @empty
          <div class="text-center text-muted" style="padding:20px; font-size:12px;">Không có tin tuyển dụng nào.</div>
        @endforelse
      </div>
      <div class="card-footer" style="padding: 10px 16px; text-align: right; background: #f8fafc;">
        <a href="{{ url('/admin/jobs') }}" class="fs-12 fw-700 text-primary-color" style="text-decoration: none;">Xem tất cả tin tuyển dụng <i class="fas fa-chevron-right ml-4"></i></a>
      </div>
    </div>

    {{-- Thống kê tỉ lệ tài khoản thành viên --}}
    <div class="card">
      <div class="card-header" style="background:#f8fafc; border-bottom:1px solid var(--border);">
        <span class="fw-800 fs-14 text-secondary"><i class="fas fa-chart-pie text-primary mr-6"></i> Phân loại thành viên</span>
      </div>
      <div class="card-body" style="padding:16px">
        @php 
          $empPct = $totalUsers ? round(($totalEmployees ?? 0) / $totalUsers * 100) : 0; 
          $erPct  = $totalUsers ? round(($totalEmployers ?? 0) / $totalUsers * 100) : 0;
        @endphp
        
        <div class="flex-between mb-8 fs-13">
          <span class="text-muted fw-600">Ứng viên (Employee)</span>
          <span class="fw-700 text-primary-color">{{ $totalEmployees ?? 0 }} ({{ $empPct }}%)</span>
        </div>
        <div style="height:8px; background:var(--border); border-radius:8px; overflow:hidden; margin-bottom:16px">
          <div style="height:100%; width:{{ $empPct }}%; background:#3b82f6; border-radius:8px; transition: width 0.5s;"></div>
        </div>
        
        <div class="flex-between mb-8 fs-13">
          <span class="text-muted fw-600">Doanh nghiệp (Employer)</span>
          <span class="fw-700" style="color:#f97316;">{{ $totalEmployers ?? 0 }} ({{ $erPct }}%)</span>
        </div>
        <div style="height:8px; background:var(--border); border-radius:8px; overflow:hidden">
          <div style="height:100%; width:{{ $erPct }}%; background:#f97316; border-radius:8px; transition: width 0.5s;"></div>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection
