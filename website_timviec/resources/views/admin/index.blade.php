@extends('layouts.admin')
@section('title', 'Admin Dashboard')

@section('content')
<div class="grid-4 mb-24" style="gap:16px">
  <div class="stat-card">
    <div class="stat-card__icon stat-card__icon-blue"><i class="fas fa-users"></i></div>
    <div><div class="stat-card__num">{{ $totalUsers ?? 0 }}</div><div class="stat-card__label">Tổng người dùng</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card__icon stat-card__icon-green"><i class="fas fa-briefcase"></i></div>
    <div><div class="stat-card__num">{{ $totalJobs ?? 0 }}</div><div class="stat-card__label">Tin tuyển dụng</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card__icon stat-card__icon-orange"><i class="fas fa-file-alt"></i></div>
    <div><div class="stat-card__num">{{ $totalApplications ?? 0 }}</div><div class="stat-card__label">Đơn ứng tuyển</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card__icon stat-card__icon-red"><i class="fas fa-credit-card"></i></div>
    <div><div class="stat-card__num">{{ $totalRevenue ?? 0 }}</div><div class="stat-card__label">Đơn Premium</div></div>
  </div>
</div>

<div class="grid" style="grid-template-columns:2fr 1fr;gap:20px;align-items:start">

  {{-- RECENT USERS --}}
  <div class="card">
    <div class="card-header">
      <span class="fw-700 fs-15">Người dùng mới nhất</span>
      <a href="{{ url('/admin/users') }}" class="btn btn-outline btn-sm">Xem tất cả</a>
    </div>
    <table class="table">
      <thead>
        <tr>
          <th>Người dùng</th>
          <th>Loại</th>
          <th>Đăng ký</th>
          <th>Trạng thái</th>
        </tr>
      </thead>
      <tbody>
        @forelse($recentUsers ?? [] as $user)
          <tr>
            <td>
              <div class="flex gap-10" style="align-items:center">
                <div class="avatar avatar-sm avatar-placeholder" style="background:var(--primary-light);color:var(--primary);font-size:12px">
                  {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                  <div class="fw-600 fs-13">{{ $user->name }}</div>
                  <div class="text-muted fs-12">{{ $user->email }}</div>
                </div>
              </div>
            </td>
            <td><span class="tag {{ $user->user_type === 'employer' ? 'tag-orange' : 'tag-blue' }} fs-11">{{ $user->user_type === 'employer' ? 'NTD' : 'UV' }}</span></td>
            <td class="text-muted fs-12">{{ $user->created_at->format('d/m/Y') }}</td>
            <td>
              @if($user->email_verified_at)
                <span class="status status-open" style="font-size:12px">Đã xác minh</span>
              @else
                <span class="status status-pending" style="font-size:12px">Chưa xác minh</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="text-center text-muted" style="padding:24px">Không có dữ liệu</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- RECENT JOBS + QUICK STATS --}}
  <div class="flex-col gap-16">
    <div class="card">
      <div class="card-header">
        <span class="fw-700 fs-14">Tin đăng mới nhất</span>
        <a href="{{ url('/admin/jobs') }}" class="btn btn-outline btn-sm">Xem tất cả</a>
      </div>
      <div class="card-body" style="padding:0">
        @forelse($recentJobs ?? [] as $job)
          <div style="padding:12px 16px;border-bottom:1px solid var(--border)">
            <div class="fw-600 fs-13">{{ Str::limit($job->title, 30) }}</div>
            <div class="flex-between mt-4">
              <span class="text-muted fs-12">{{ $job->user->name }}</span>
              <span class="fs-12 text-primary-color fw-600">{{ $job->users->count() }} UV</span>
            </div>
          </div>
        @empty
          <div class="text-center text-muted" style="padding:20px;font-size:13px">Không có dữ liệu</div>
        @endforelse
      </div>
    </div>

    {{-- Employer breakdown --}}
    <div class="card">
      <div class="card-header"><span class="fw-700 fs-14">Phân loại người dùng</span></div>
      <div class="card-body" style="padding:16px">
        <div class="flex-between mb-8 fs-13">
          <span class="text-muted">Ứng viên</span>
          <span class="fw-700 text-primary-color">{{ $totalEmployees ?? 0 }}</span>
        </div>
        <div style="height:6px;background:var(--border);border-radius:6px;overflow:hidden;margin-bottom:12px">
          @php $empPct = $totalUsers ? round(($totalEmployees ?? 0) / $totalUsers * 100) : 0; @endphp
          <div style="height:100%;width:{{ $empPct }}%;background:#1a73e8;border-radius:6px"></div>
        </div>
        <div class="flex-between mb-8 fs-13">
          <span class="text-muted">Nhà tuyển dụng</span>
          <span class="fw-700" style="color:#f57c00">{{ $totalEmployers ?? 0 }}</span>
        </div>
        <div style="height:6px;background:var(--border);border-radius:6px;overflow:hidden">
          @php $erPct = $totalUsers ? round(($totalEmployers ?? 0) / $totalUsers * 100) : 0; @endphp
          <div style="height:100%;width:{{ $erPct }}%;background:#f57c00;border-radius:6px"></div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
