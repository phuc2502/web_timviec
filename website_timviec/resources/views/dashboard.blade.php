@extends('layouts.dashboard')
@section('title', 'Dashboard')

@section('content')

@if(auth()->user()->user_type === 'employer')
{{-- ===== EMPLOYER DASHBOARD (Doanh nghiệp tuyển dụng) ===== --}}

<div class="flex-between mb-20">
  <div>
    <h1 class="fs-20 fw-800" style="color:var(--secondary)">Xin chào, {{ auth()->user()->name }}! 👋</h1>
    <p class="text-muted fs-13 mt-4">Tổng quan hoạt động tuyển dụng thực tế của bạn</p>
  </div>
  <a href="{{ url('/job/create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Đăng tin mới</a>
</div>

{{-- TRIAL ALERT --}}
@if(auth()->user()->user_trial && auth()->user()->user_trial > now() && !auth()->user()->billing_ends)
  <div class="alert alert-warning mb-16">
    <i class="fas fa-clock"></i>
    <span>Bạn đang dùng thử miễn phí gói Doanh nghiệp — còn <strong>{{ now()->diffInDays(auth()->user()->user_trial) }} ngày</strong>. <a href="{{ url('/subscribe') }}" class="fw-700" style="color:inherit;text-decoration:underline">Nâng cấp Premium ngay</a> để nhận đầy đủ quyền lợi.</span>
  </div>
@endif

{{-- STATS --}}
<div class="grid-4 mb-20" style="gap:16px">
  <div class="stat-card">
    <div class="stat-card__icon stat-card__icon-green"><i class="fas fa-briefcase"></i></div>
    <div><div class="stat-card__num">{{ $totalJobs ?? 0 }}</div><div class="stat-card__label">Tin đăng của bạn</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card__icon stat-card__icon-blue"><i class="fas fa-users"></i></div>
    <div><div class="stat-card__num">{{ $totalApplicants ?? 0 }}</div><div class="stat-card__label">Ứng viên đã nộp</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card__icon stat-card__icon-orange"><i class="fas fa-star"></i></div>
    <div><div class="stat-card__num">{{ $shortlisted ?? 0 }}</div><div class="stat-card__label">Ứng viên Shortlist</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card__icon stat-card__icon-red"><i class="fas fa-fire"></i></div>
    <div><div class="stat-card__num">{{ $activeJobs ?? 0 }}</div><div class="stat-card__label">Tin đang mở tuyển</div></div>
  </div>
</div>

<div class="grid-2-1">

  {{-- RECENT JOBS --}}
  <div class="card">
    <div class="card-header">
      <span class="fw-700 fs-15">Tin đăng gần đây</span>
      <a href="{{ url('/job/manage') }}" class="btn btn-outline btn-sm">Quản lý tin đăng</a>
    </div>
    <table class="table">
      <thead>
        <tr>
          <th>Tên tin đăng</th>
          <th style="text-align: center;">Ứng viên nộp</th>
          <th>Ngày hết hạn</th>
          <th>Trạng thái</th>
        </tr>
      </thead>
      <tbody>
        @forelse($recentJobs ?? [] as $job)
          <tr>
            <td>
              <a href="{{ url('/job/show/'.$job->slug) }}" class="fw-600 fs-13 text-primary-color" target="_blank">
                {{ Str::limit($job->title, 40) }}
              </a>
            </td>
            <td style="text-align: center;">
              <span class="badge" style="background:var(--primary-light); color:var(--primary); padding: 4px 8px; border-radius:12px; font-weight:700;">
                {{ $job->users->count() }}
              </span>
            </td>
            <td class="text-muted fs-12">
              {{ $job->application_close_date ? $job->application_close_date->format('d/m/Y') : 'Không giới hạn' }}
            </td>
            <td>
              @if($job->application_close_date && $job->application_close_date->isPast())
                <span class="status status-closed">Hết hạn</span>
              @else
                <span class="status status-open">Đang mở</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="text-center text-muted" style="padding:24px">Bạn chưa đăng tin tuyển dụng nào thực tế.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- SIDEBAR --}}
  <div class="flex-col gap-16">
    {{-- Subscription Status --}}
    <div class="card" style="background:linear-gradient(135deg, var(--secondary), #2c3e50);color:#fff; border-radius: var(--radius-lg);">
      <div class="card-body" style="padding:20px">
        <div class="fw-700 fs-14 mb-12"><i class="fas fa-crown" style="color:gold"></i> Gói doanh nghiệp</div>
        @if(auth()->user()->billing_ends && auth()->user()->billing_ends > now())
          <div class="fw-800 fs-18" style="color:#7effc4">{{ ucfirst(auth()->user()->plan) }} (Premium)</div>
          <div style="font-size:12px;opacity:.75;margin-top:4px">Hạn dùng: {{ auth()->user()->billing_ends->format('d/m/Y') }}</div>
        @elseif(auth()->user()->user_trial && auth()->user()->user_trial > now())
          <div class="fw-700 fs-15" style="color:#ffc107">Dùng thử (Trial)</div>
          <div style="font-size:12px;opacity:.75;margin-top:4px">Còn lại: {{ now()->diffInDays(auth()->user()->user_trial) }} ngày</div>
        @else
          <div class="fw-700 fs-15" style="color:#dc3545">Chưa đăng ký / Hết hạn</div>
          <a href="{{ url('/subscribe') }}" class="btn btn-sm mt-12" style="background:var(--primary);color:#fff; width:100%; justify-content:center;">Nâng cấp gói ngay</a>
        @endif
      </div>
    </div>

    {{-- Quick Links --}}
    <div class="card">
      <div class="card-header"><span class="fw-700 fs-14">Thao tác nhanh</span></div>
      <div class="card-body" style="padding:12px">
        <div class="flex-col gap-8">
          <a href="{{ url('/job/create') }}" class="btn btn-primary btn-block"><i class="fas fa-plus mr-4"></i> Đăng tin mới</a>
          <a href="{{ url('/applicants') }}" class="btn btn-outline btn-block"><i class="fas fa-users mr-4"></i> Danh sách ứng viên</a>
          <a href="{{ url('/messages') }}" class="btn btn-outline btn-block"><i class="fas fa-comment-dots mr-4"></i> Tin nhắn trao đổi</a>
        </div>
      </div>
    </div>
  </div>
</div>

@else
{{-- ===== EMPLOYEE DASHBOARD (Người tìm việc) ===== --}}

<div class="flex-between mb-20">
  <div>
    <h1 class="fs-20 fw-800" style="color:var(--secondary)">Xin chào, {{ auth()->user()->name }}! 👋</h1>
    <p class="text-muted fs-13 mt-4">Theo dõi quá trình ứng tuyển công việc thực tế của bạn</p>
  </div>
  <a href="{{ url('/job') }}" class="btn btn-primary"><i class="fas fa-search"></i> Tìm kiếm việc làm</a>
</div>

<div class="grid-3 mb-20" style="gap:16px">
  <div class="stat-card">
    <div class="stat-card__icon stat-card__icon-blue"><i class="fas fa-file-alt"></i></div>
    <div><div class="stat-card__num">{{ $appliedJobs ? $appliedJobs->count() : 0 }}</div><div class="stat-card__label">Việc làm đã nộp</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card__icon stat-card__icon-orange"><i class="fas fa-star"></i></div>
    <div><div class="stat-card__num">{{ $appliedJobs ? $appliedJobs->where('pivot.shortlisted', true)->count() : 0 }}</div><div class="stat-card__label">Lượt được Shortlist</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card__icon stat-card__icon-green"><i class="fas fa-file-pdf"></i></div>
    <div><div class="stat-card__num">{{ auth()->user()->resume ? 1 : 0 }}</div><div class="stat-card__label">CV đã tải lên</div></div>
  </div>
</div>

{{-- CV ALERT --}}
@if(!auth()->user()->resume && !auth()->user()->cvData)
  <div class="alert alert-warning mb-16">
    <i class="fas fa-exclamation-triangle"></i>
    <span>Hồ sơ ứng tuyển chưa có CV! Hãy <a href="{{ url('/user/cv') }}" class="fw-700" style="color:inherit;text-decoration:underline">Tải lên CV hoặc tạo CV online</a> để ứng tuyển ngay.</span>
  </div>
@endif

<div class="grid-2-1">
  
  {{-- Applied Jobs --}}
  <div class="card">
    <div class="card-header">
      <span class="fw-700 fs-15">Việc làm bạn đã ứng tuyển</span>
    </div>
    <div class="card-body" style="padding:0">
      @forelse($appliedJobs ?? [] as $job)
        <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px">
          <div style="width:40px;height:40px;background:var(--primary-light);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--primary)">
            <i class="fas fa-building"></i>
          </div>
          <div style="flex:1">
            <a href="{{ url('/job/show/'.$job->slug) }}" class="fw-600 fs-14" style="color:var(--secondary)">{{ Str::limit($job->title, 40) }}</a>
            <div class="text-muted fs-12 mt-2">{{ $job->user->company_name ?? $job->user->name }}</div>
          </div>
          @if($job->pivot->shortlisted)
            <span class="tag tag-green fs-11" style="background:#e8fdf4; color:#10b981; padding: 4px 8px; border-radius: 4px;"><i class="fas fa-star" style="margin-right:3px"></i>Đã Shortlist</span>
          @else
            <span class="tag tag-gray fs-11" style="background:#f1f5f9; color:#64748b; padding: 4px 8px; border-radius: 4px;">Đang xét tuyển</span>
          @endif
        </div>
      @empty
        <div class="text-center text-muted" style="padding:32px">
          <i class="fas fa-inbox fa-2x mb-8" style="display:block;color:var(--text-muted)"></i>
          Bạn chưa nộp đơn vào công việc nào.
        </div>
      @endforelse
    </div>
  </div>

  {{-- Quick Actions --}}
  <div class="flex-col gap-16">
    <div class="card">
      <div class="card-header"><span class="fw-700 fs-14">Hành động nhanh</span></div>
      <div class="card-body" style="padding:12px">
        <div class="flex-col gap-8">
          <a href="{{ url('/user/cv/create') }}" class="btn btn-primary btn-block"><i class="fas fa-magic"></i> Tạo CV online mới</a>
          <a href="{{ url('/user/cv') }}" class="btn btn-outline btn-block"><i class="fas fa-upload"></i> Quản lý & Tải lên CV</a>
          <a href="{{ url('/job') }}" class="btn btn-outline btn-block"><i class="fas fa-search"></i> Tìm kiếm việc làm IT</a>
          <a href="{{ url('/messages') }}" class="btn btn-outline btn-block"><i class="fas fa-comment-dots"></i> Hộp thư trao đổi</a>
        </div>
      </div>
    </div>

    {{-- Profile completion --}}
    <div class="card">
      <div class="card-header"><span class="fw-700 fs-14">Độ hoàn thiện hồ sơ</span></div>
      <div class="card-body" style="padding:16px">
        @php
          $score = 0;
          if(auth()->user()->name) $score += 25;
          if(auth()->user()->about) $score += 25;
          if(auth()->user()->profile_pic) $score += 25;
          if(auth()->user()->resume || auth()->user()->cvData) $score += 25;
        @endphp
        <div class="flex-between mb-8">
          <span class="fs-13 fw-600">{{ $score }}% hoàn thành</span>
          <span class="fs-12 text-primary-color">{{ $score < 100 ? 'Cải thiện hồ sơ' : '🎉 Đầy đủ!' }}</span>
        </div>
        <div style="height:8px;background:var(--border);border-radius:8px;overflow:hidden">
          <div style="height:100%;width:{{ $score }}%;background:var(--primary);border-radius:8px;transition:.5s"></div>
        </div>
        <div class="flex-col gap-8 mt-12">
          <div class="flex gap-6 fs-12" style="align-items:center;color:{{ auth()->user()->name ? 'var(--primary)' : 'var(--text-muted)' }}">
            <i class="fas {{ auth()->user()->name ? 'fa-check-circle' : 'fa-circle' }}"></i> Tên hiển thị cá nhân
          </div>
          <div class="flex gap-6 fs-12" style="align-items:center;color:{{ auth()->user()->about ? 'var(--primary)' : 'var(--text-muted)' }}">
            <i class="fas {{ auth()->user()->about ? 'fa-check-circle' : 'fa-circle' }}"></i> Thông tin giới thiệu ngắn
          </div>
          <div class="flex gap-6 fs-12" style="align-items:center;color:{{ auth()->user()->profile_pic ? 'var(--primary)' : 'var(--text-muted)' }}">
            <i class="fas {{ auth()->user()->profile_pic ? 'fa-check-circle' : 'fa-circle' }}"></i> Ảnh chân dung đại diện
          </div>
          <div class="flex gap-6 fs-12" style="align-items:center;color:{{ (auth()->user()->resume || auth()->user()->cvData) ? 'var(--primary)' : 'var(--text-muted)' }}">
            <i class="fas {{ (auth()->user()->resume || auth()->user()->cvData) ? 'fa-check-circle' : 'fa-circle' }}"></i> Hồ sơ CV (Upload/Online)
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

@endsection
