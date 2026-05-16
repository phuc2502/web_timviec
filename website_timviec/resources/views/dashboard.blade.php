@extends('layouts.dashboard')
@section('title', 'Dashboard')

@section('content')

@if(auth()->user()->user_type === 'employer')
{{-- ===== EMPLOYER DASHBOARD ===== --}}

<div class="flex-between mb-20">
  <div>
    <h1 class="fs-20 fw-800" style="color:var(--secondary)">Xin chào, {{ auth()->user()->name }}! 👋</h1>
    <p class="text-muted fs-13 mt-4">Tổng quan hoạt động tuyển dụng của bạn</p>
  </div>
  <a href="{{ url('/job/create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Đăng tin mới</a>
</div>

{{-- TRIAL ALERT --}}
@if(auth()->user()->user_trial && auth()->user()->user_trial > now() && !auth()->user()->billing_ends)
  <div class="alert alert-warning mb-16">
    <i class="fas fa-clock"></i>
    <span>Bạn đang dùng thử miễn phí — còn <strong>{{ now()->diffInDays(auth()->user()->user_trial) }} ngày</strong>. <a href="{{ url('/subscribe') }}" class="fw-700" style="color:inherit;text-decoration:underline">Nâng cấp ngay</a> để không bị gián đoạn.</span>
  </div>
@endif

{{-- STATS --}}
<div class="grid-4 mb-20" style="gap:16px">
  <div class="stat-card">
    <div class="stat-card__icon stat-card__icon-green"><i class="fas fa-briefcase"></i></div>
    <div><div class="stat-card__num">{{ $totalJobs ?? 0 }}</div><div class="stat-card__label">Tin đăng</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card__icon stat-card__icon-blue"><i class="fas fa-users"></i></div>
    <div><div class="stat-card__num">{{ $totalApplicants ?? 0 }}</div><div class="stat-card__label">Tổng ứng viên</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card__icon stat-card__icon-orange"><i class="fas fa-star"></i></div>
    <div><div class="stat-card__num">{{ $shortlisted ?? 0 }}</div><div class="stat-card__label">Đã shortlist</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card__icon stat-card__icon-red"><i class="fas fa-fire"></i></div>
    <div><div class="stat-card__num">{{ $activeJobs ?? 0 }}</div><div class="stat-card__label">Đang mở</div></div>
  </div>
</div>

<div class="grid" style="grid-template-columns:2fr 1fr;gap:20px;align-items:start">

  {{-- RECENT JOBS --}}
  <div class="card">
    <div class="card-header">
      <span class="fw-700 fs-15">Tin đăng gần đây</span>
      <a href="{{ url('/job') }}" class="btn btn-outline btn-sm">Xem tất cả</a>
    </div>
    <table class="table">
      <thead>
        <tr>
          <th>Tên tin</th>
          <th>Ứng viên</th>
          <th>Hết hạn</th>
          <th>Trạng thái</th>
        </tr>
      </thead>
      <tbody>
        @forelse($recentJobs ?? [] as $job)
          <tr>
            <td>
              <a href="{{ url('/job/show/'.$job->slug) }}" class="fw-600 fs-13" style="color:var(--secondary)" target="_blank">
                {{ Str::limit($job->title, 35) }}
              </a>
            </td>
            <td><span class="fw-700 text-primary-color">{{ $job->users->count() }}</span></td>
            <td class="text-muted fs-12">{{ \Carbon\Carbon::parse($job->application_close_date)->format('d/m/Y') }}</td>
            <td>
              @if(\Carbon\Carbon::parse($job->application_close_date)->isPast())
                <span class="status status-closed">Hết hạn</span>
              @else
                <span class="status status-open">Đang mở</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="4" class="text-center text-muted" style="padding:24px">Chưa có tin đăng nào</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- SIDEBAR --}}
  <div class="flex-col gap-16">
    {{-- Subscription Status --}}
    <div class="card" style="background:linear-gradient(135deg,var(--secondary),#2c3e50);color:#fff">
      <div class="card-body" style="padding:20px">
        <div class="fw-700 fs-14 mb-12"><i class="fas fa-crown" style="color:gold"></i> Gói đăng ký</div>
        @if(auth()->user()->billing_ends && auth()->user()->billing_ends > now())
          <div class="fw-800 fs-18" style="color:#7effc4">{{ ucfirst(auth()->user()->plan) }}</div>
          <div style="font-size:12px;opacity:.75;margin-top:4px">Hết hạn: {{ \Carbon\Carbon::parse(auth()->user()->billing_ends)->format('d/m/Y') }}</div>
        @elseif(auth()->user()->user_trial && auth()->user()->user_trial > now())
          <div class="fw-700 fs-15" style="color:#ffc107">Dùng thử</div>
          <div style="font-size:12px;opacity:.75;margin-top:4px">Còn {{ now()->diffInDays(auth()->user()->user_trial) }} ngày</div>
          <a href="{{ url('/subscribe') }}" class="btn btn-sm mt-12" style="background:var(--primary);color:#fff">Nâng cấp</a>
        @else
          <div class="fw-700 fs-15" style="color:#dc3545">Đã hết hạn</div>
          <a href="{{ url('/subscribe') }}" class="btn btn-sm mt-12" style="background:var(--primary);color:#fff">Mua gói</a>
        @endif
      </div>
    </div>

    {{-- Quick Links --}}
    <div class="card">
      <div class="card-header"><span class="fw-700 fs-14">Thao tác nhanh</span></div>
      <div class="card-body" style="padding:12px">
        <div class="flex-col gap-8">
          <a href="{{ url('/job/create') }}" class="btn btn-primary btn-block"><i class="fas fa-plus"></i> Đăng tin mới</a>
          <a href="{{ url('/applicants') }}" class="btn btn-outline btn-block"><i class="fas fa-users"></i> Xem ứng viên</a>
          <a href="{{ url('/messages') }}" class="btn btn-outline btn-block"><i class="fas fa-comment-dots"></i> Tin nhắn</a>
        </div>
      </div>
    </div>
  </div>
</div>

@else
{{-- ===== EMPLOYEE DASHBOARD ===== --}}

<div class="flex-between mb-20">
  <div>
    <h1 class="fs-20 fw-800" style="color:var(--secondary)">Xin chào, {{ auth()->user()->name }}! 👋</h1>
    <p class="text-muted fs-13 mt-4">Theo dõi quá trình ứng tuyển của bạn</p>
  </div>
  <a href="{{ url('/job/search') }}" class="btn btn-primary"><i class="fas fa-search"></i> Tìm việc làm</a>
</div>

<div class="grid-3 mb-20" style="gap:16px">
  <div class="stat-card">
    <div class="stat-card__icon stat-card__icon-blue"><i class="fas fa-file-alt"></i></div>
    <div><div class="stat-card__num">{{ auth()->user()->listings->count() ?? 0 }}</div><div class="stat-card__label">Đã ứng tuyển</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card__icon stat-card__icon-orange"><i class="fas fa-star"></i></div>
    <div><div class="stat-card__num">{{ auth()->user()->listings->where('pivot.shortlisted',true)->count() ?? 0 }}</div><div class="stat-card__label">Được shortlist</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card__icon stat-card__icon-green"><i class="fas fa-file-pdf"></i></div>
    <div><div class="stat-card__num">{{ auth()->user()->resume ? 1 : 0 }}</div><div class="stat-card__label">CV đã upload</div></div>
  </div>
</div>

{{-- CV ALERT --}}
@if(!auth()->user()->resume)
  <div class="alert alert-warning mb-16">
    <i class="fas fa-exclamation-triangle"></i>
    Hồ sơ của bạn chưa có CV! <a href="{{ url('/user/cv') }}" class="fw-700" style="color:inherit;text-decoration:underline">Upload hoặc tạo CV ngay</a> để tăng cơ hội được nhà tuyển dụng chú ý.
  </div>
@endif

<div class="grid" style="grid-template-columns:2fr 1fr;gap:20px;align-items:start">
  {{-- Applied Jobs --}}
  <div class="card">
    <div class="card-header">
      <span class="fw-700 fs-15">Việc đã ứng tuyển</span>
      <a href="{{ url('/applicants') }}" class="btn btn-outline btn-sm">Xem tất cả</a>
    </div>
    <div class="card-body" style="padding:0">
      @forelse(auth()->user()->listings()->latest('listing_user.created_at')->take(5)->get() ?? [] as $job)
        <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px">
          <div style="width:40px;height:40px;background:var(--primary-light);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--primary)">
            <i class="fas fa-building"></i>
          </div>
          <div style="flex:1">
            <a href="{{ url('/job/show/'.$job->slug) }}" class="fw-600 fs-14" style="color:var(--secondary)">{{ Str::limit($job->title, 40) }}</a>
            <div class="text-muted fs-12 mt-2">{{ $job->user->company_name ?? $job->user->name }}</div>
          </div>
          @if($job->pivot->shortlisted)
            <span class="tag tag-green fs-11"><i class="fas fa-star" style="margin-right:3px"></i>Shortlisted</span>
          @else
            <span class="tag tag-gray fs-11">Đang xét</span>
          @endif
        </div>
      @empty
        <div class="text-center text-muted" style="padding:32px">
          <i class="fas fa-inbox fa-2x mb-8" style="display:block;color:var(--text-muted)"></i>
          Chưa ứng tuyển việc làm nào
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
          <a href="{{ url('/user/cv/create') }}" class="btn btn-primary btn-block"><i class="fas fa-magic"></i> Tạo CV online</a>
          <a href="{{ url('/user/cv') }}" class="btn btn-outline btn-block"><i class="fas fa-upload"></i> Upload CV</a>
          <a href="{{ url('/job/search') }}" class="btn btn-outline btn-block"><i class="fas fa-search"></i> Tìm việc làm</a>
          <a href="{{ url('/messages') }}" class="btn btn-outline btn-block"><i class="fas fa-comment-dots"></i> Tin nhắn</a>
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
          if(auth()->user()->resume) $score += 25;
        @endphp
        <div class="flex-between mb-8">
          <span class="fs-13 fw-600">{{ $score }}% hoàn thiện</span>
          <span class="fs-12 text-primary-color">{{ $score < 100 ? 'Cải thiện hồ sơ' : '🎉 Hoàn hảo!' }}</span>
        </div>
        <div style="height:8px;background:var(--border);border-radius:8px;overflow:hidden">
          <div style="height:100%;width:{{ $score }}%;background:var(--primary);border-radius:8px;transition:.5s"></div>
        </div>
        <div class="flex-col gap-8 mt-12">
          <div class="flex gap-6 fs-12" style="align-items:center;color:{{ auth()->user()->name ? 'var(--primary)' : 'var(--text-muted)' }}">
            <i class="fas {{ auth()->user()->name ? 'fa-check-circle' : 'fa-circle' }}"></i> Tên đầy đủ
          </div>
          <div class="flex gap-6 fs-12" style="align-items:center;color:{{ auth()->user()->about ? 'var(--primary)' : 'var(--text-muted)' }}">
            <i class="fas {{ auth()->user()->about ? 'fa-check-circle' : 'fa-circle' }}"></i> Giới thiệu bản thân
          </div>
          <div class="flex gap-6 fs-12" style="align-items:center;color:{{ auth()->user()->profile_pic ? 'var(--primary)' : 'var(--text-muted)' }}">
            <i class="fas {{ auth()->user()->profile_pic ? 'fa-check-circle' : 'fa-circle' }}"></i> Ảnh đại diện
          </div>
          <div class="flex gap-6 fs-12" style="align-items:center;color:{{ auth()->user()->resume ? 'var(--primary)' : 'var(--text-muted)' }}">
            <i class="fas {{ auth()->user()->resume ? 'fa-check-circle' : 'fa-circle' }}"></i> Upload CV
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

@endsection
