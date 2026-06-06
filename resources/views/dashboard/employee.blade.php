@extends('layouts.dashboard')
@section('title', 'Dashboard — Ứng viên')

@push('styles')
<style>
.hero-greeting {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  border-radius: var(--r-lg);
  padding: 28px 32px;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
  position: relative;
  overflow: hidden;
}
.hero-greeting::before {
  content: '';
  position: absolute;
  right: -40px; top: -40px;
  width: 180px; height: 180px;
  background: rgba(255,255,255,.07);
  border-radius: 50%;
}
.hero-greeting::after {
  content: '';
  position: absolute;
  right: 60px; bottom: -60px;
  width: 220px; height: 220px;
  background: rgba(255,255,255,.05);
  border-radius: 50%;
}
.hero-greeting h1 { font-size: 22px; font-weight: 800; margin: 0 0 4px; }
.hero-greeting p  { font-size: 13px; opacity: .85; margin: 0; }
.hero-greeting .btn-white {
  background: #fff;
  color: #10b981;
  font-weight: 700;
  padding: 10px 20px;
  border-radius: var(--r-sm);
  text-decoration: none;
  font-size: 14px;
  white-space: nowrap;
  flex-shrink: 0;
  position: relative;
  z-index: 1;
  transition: box-shadow .2s;
}
.hero-greeting .btn-white:hover { box-shadow: 0 4px 14px rgba(0,0,0,.15); }

.stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
.stat-box {
  background: #fff;
  border-radius: var(--r-lg);
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 1px 6px rgba(0,0,0,.06);
  border: 1px solid #f1f5f9;
}
.stat-box__icon {
  width: 48px; height: 48px;
  border-radius: var(--r-md);
  display: flex; align-items: center; justify-content: center;
  font-size: 20px;
  flex-shrink: 0;
}
.stat-box__num  { font-size: 26px; font-weight: 800; color: #1e293b; line-height: 1; }
.stat-box__lbl  { font-size: 12px; color: #64748b; margin-top: 4px; }

.main-grid { display: grid; grid-template-columns: 1fr 320px; gap: 20px; align-items: start; }
@media(max-width:900px){ .main-grid { grid-template-columns: 1fr; } .stat-grid { grid-template-columns: 1fr 1fr; } }

.section-card {
  background: #fff;
  border-radius: var(--r-lg);
  border: 1px solid #f1f5f9;
  box-shadow: 0 1px 6px rgba(0,0,0,.06);
  overflow: hidden;
}
.section-card__header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #f1f5f9;
}
.section-card__title { font-size: 15px; font-weight: 700; color: #1e293b; }

.job-item {
  display: flex; align-items: center; gap: 14px;
  padding: 14px 20px;
  border-bottom: 1px solid #f8fafc;
  transition: background .15s;
}
.job-item:last-child { border-bottom: none; }
.job-item:hover { background: #fafcff; }
.job-icon {
  width: 42px; height: 42px;
  background: #f0fdf7; border-radius: var(--r-sm);
  display: flex; align-items: center; justify-content: center;
  color: #10b981; font-size: 16px; flex-shrink: 0;
}
.job-title { font-size: 14px; font-weight: 600; color: #1e293b; }
.job-company { font-size: 12px; color: #64748b; margin-top: 2px; }
.badge-shortlist { background: #f0fdf7; color: #10b981; font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 20px; white-space: nowrap; }
.badge-pending   { background: #f8fafc; color: #64748b; font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 20px; white-space: nowrap; }

.empty-state { padding: 40px 20px; text-align: center; }
.empty-state i { font-size: 36px; color: #cbd5e1; margin-bottom: 12px; display: block; }
.empty-state p { color: #94a3b8; font-size: 14px; }

.quick-btn {
  display: flex; align-items: center; gap: 10px;
  padding: 12px 16px;
  border-radius: var(--r-sm);
  border: 1.5px solid #e2e8f0;
  background: #fff;
  color: #334155;
  font-size: 14px;
  font-weight: 500;
  text-decoration: none;
  transition: all .15s;
  margin-bottom: 8px;
}
.quick-btn:last-child { margin-bottom: 0; }
.quick-btn:hover { border-color: #10b981; background: #f0fdf7; color: #10b981; text-decoration: none; }
.quick-btn i { width: 20px; text-align: center; color: #10b981; }
.quick-btn-primary {
  background: linear-gradient(135deg, #10b981, #059669);
  border-color: transparent;
  color: #fff;
}
.quick-btn-primary:hover { background: linear-gradient(135deg, #059669, #047857); color: #fff; }
.quick-btn-primary i { color: #fff; }

.progress-wrap { height: 8px; background: #f1f5f9; border-radius: 8px; overflow: hidden; margin: 8px 0 12px; }
.progress-bar  { height: 100%; background: linear-gradient(90deg, #10b981, #059669); border-radius: 8px; transition: width .6s; }
.check-item { display: flex; align-items: center; gap: 8px; font-size: 13px; padding: 4px 0; }
.check-item.done { color: #10b981; }
.check-item.todo { color: #94a3b8; }
</style>
@endpush

@section('content')

{{-- HERO --}}
<div class="hero-greeting">
  <div>
    <h1>Xin chào, {{ auth()->user()->name }}! 👋</h1>
    <p>Theo dõi quá trình ứng tuyển và phát triển sự nghiệp IT của bạn</p>
  </div>
  <a href="{{ url('/job') }}" class="btn-white"><i class="fas fa-search" style="margin-right:6px"></i>Tìm việc ngay</a>
</div>

{{-- CV ALERT --}}
@if(!auth()->user()->resume && !auth()->user()->cvData)
<div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:12px;padding:14px 20px;display:flex;align-items:center;gap:12px;margin-bottom:20px">
  <i class="fas fa-exclamation-triangle" style="color:#f59e0b;font-size:18px;flex-shrink:0"></i>
  <span style="font-size:14px;color:#92400e">Hồ sơ chưa có CV! Hãy <a href="{{ url('/user/cv') }}" style="color:#d97706;font-weight:700;text-decoration:underline">tải lên CV hoặc tạo CV online</a> để ứng tuyển ngay.</span>
</div>
@endif

{{-- STATS --}}
<div class="stat-grid">
  <div class="stat-box">
    <div class="stat-box__icon" style="background:#eff6ff;color:#3b82f6"><i class="fas fa-file-alt"></i></div>
    <div>
      <div class="stat-box__num">{{ $appliedJobs ? $appliedJobs->count() : 0 }}</div>
      <div class="stat-box__lbl">Việc đã nộp</div>
    </div>
  </div>
  <div class="stat-box">
    <div class="stat-box__icon" style="background:#fff7ed;color:#f59e0b"><i class="fas fa-star"></i></div>
    <div>
      <div class="stat-box__num">{{ $appliedJobs ? $appliedJobs->where('pivot.shortlisted', true)->count() : 0 }}</div>
      <div class="stat-box__lbl">Được Shortlist</div>
    </div>
  </div>
  <div class="stat-box">
    <div class="stat-box__icon" style="background:#f0fdf7;color:#10b981"><i class="fas fa-file-pdf"></i></div>
    <div>
      <div class="stat-box__num">{{ auth()->user()->resume ? 1 : 0 }}</div>
      <div class="stat-box__lbl">CV đã tải lên</div>
    </div>
  </div>
</div>

{{-- MAIN GRID --}}
<div class="main-grid">

  {{-- Applied Jobs --}}
  <div class="section-card">
    <div class="section-card__header">
      <span class="section-card__title"><i class="fas fa-briefcase" style="color:#10b981;margin-right:8px"></i>Việc làm đã ứng tuyển</span>
      <a href="{{ url('/applicants') }}" style="font-size:13px;color:#10b981;font-weight:600;text-decoration:none">Xem tất cả →</a>
    </div>
    @forelse($appliedJobs ?? [] as $job)
      <div class="job-item">
        <div class="job-icon"><i class="fas fa-building"></i></div>
        <div style="flex:1;min-width:0">
          <div class="job-title"><a href="{{ url('/job/show/'.$job->slug) }}" style="color:#1e293b;text-decoration:none">{{ Str::limit($job->title, 40) }}</a></div>
          <div class="job-company">{{ $job->user->company_name ?? $job->user->name }}</div>
        </div>
        @if($job->pivot->shortlisted)
          <span class="badge-shortlist"><i class="fas fa-star" style="margin-right:3px"></i>Shortlist</span>
        @else
          <span class="badge-pending">Đang xét</span>
        @endif
      </div>
    @empty
      <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <p>Bạn chưa nộp đơn vào công việc nào.<br><a href="{{ url('/job') }}" style="color:#10b981;font-weight:600">Tìm việc ngay →</a></p>
      </div>
    @endforelse
  </div>

  {{-- Sidebar --}}
  <div style="display:flex;flex-direction:column;gap:16px">

    {{-- Quick Actions --}}
    <div class="section-card">
      <div class="section-card__header">
        <span class="section-card__title">Thao tác nhanh</span>
      </div>
      <div style="padding:14px">
        <a href="{{ url('/user/cv/create') }}" class="quick-btn quick-btn-primary"><i class="fas fa-magic"></i> Tạo CV online mới</a>
        <a href="{{ url('/user/cv') }}"        class="quick-btn"><i class="fas fa-upload"></i> Quản lý & Tải lên CV</a>
        <a href="{{ url('/job') }}"            class="quick-btn"><i class="fas fa-search"></i> Tìm kiếm việc làm IT</a>
        <a href="{{ url('/messages') }}"       class="quick-btn"><i class="fas fa-comment-dots"></i> Hộp thư trao đổi</a>
      </div>
    </div>

    {{-- Profile Completion --}}
    <div class="section-card">
      <div class="section-card__header">
        <span class="section-card__title">Độ hoàn thiện hồ sơ</span>
      </div>
      <div style="padding:16px">
        @php
          $score = 0;
          if(auth()->user()->name) $score += 25;
          if(auth()->user()->about) $score += 25;
          if(auth()->user()->profile_pic) $score += 25;
          if(auth()->user()->resume || auth()->user()->cvData) $score += 25;
        @endphp
        <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px;font-weight:700">
          <span style="color:#1e293b">{{ $score }}% hoàn thành</span>
          @if($score < 100)
            <a href="{{ url('/user/profile') }}" style="color:#10b981;font-size:12px">Cải thiện →</a>
          @else
            <span style="color:#10b981">🎉 Đầy đủ!</span>
          @endif
        </div>
        <div class="progress-wrap"><div class="progress-bar" style="width:{{ $score }}%"></div></div>
        <div>
          <div class="check-item {{ auth()->user()->name ? 'done' : 'todo' }}">
            <i class="fas {{ auth()->user()->name ? 'fa-check-circle' : 'fa-circle' }}"></i> Tên hiển thị cá nhân
          </div>
          <div class="check-item {{ auth()->user()->about ? 'done' : 'todo' }}">
            <i class="fas {{ auth()->user()->about ? 'fa-check-circle' : 'fa-circle' }}"></i> Thông tin giới thiệu ngắn
          </div>
          <div class="check-item {{ auth()->user()->profile_pic ? 'done' : 'todo' }}">
            <i class="fas {{ auth()->user()->profile_pic ? 'fa-check-circle' : 'fa-circle' }}"></i> Ảnh chân dung đại diện
          </div>
          <div class="check-item {{ (auth()->user()->resume || auth()->user()->cvData) ? 'done' : 'todo' }}">
            <i class="fas {{ (auth()->user()->resume || auth()->user()->cvData) ? 'fa-check-circle' : 'fa-circle' }}"></i> Hồ sơ CV (Upload/Online)
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

@endsection
