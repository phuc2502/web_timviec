@extends('layouts.dashboard')
@section('title', 'Dashboard — Nhà tuyển dụng')

@push('styles')
<style>
.hero-employer {
  background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
  border-radius: 16px;
  padding: 28px 32px;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 24px;
  position: relative;
  overflow: hidden;
}
.hero-employer::before {
  content: '';
  position: absolute; right: -40px; top: -40px;
  width: 200px; height: 200px;
  background: rgba(255,255,255,.06); border-radius: 50%;
}
.hero-employer::after {
  content: '';
  position: absolute; right: 80px; bottom: -70px;
  width: 240px; height: 240px;
  background: rgba(255,255,255,.04); border-radius: 50%;
}
.hero-employer h1 { font-size: 22px; font-weight: 800; margin: 0 0 4px; }
.hero-employer p  { font-size: 13px; opacity: .85; margin: 0; }
.hero-employer .btn-white {
  background: #fff; color: #1e40af;
  font-weight: 700; padding: 10px 20px;
  border-radius: 10px; text-decoration: none;
  font-size: 14px; white-space: nowrap;
  flex-shrink: 0; position: relative; z-index: 1;
  transition: box-shadow .2s;
}
.hero-employer .btn-white:hover { box-shadow: 0 4px 14px rgba(0,0,0,.2); text-decoration: none; }

.stat-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
@media(max-width:900px){ .stat-grid-4 { grid-template-columns: repeat(2,1fr); } }

.stat-box {
  background: #fff; border-radius: 14px;
  padding: 20px; display: flex; align-items: center; gap: 14px;
  box-shadow: 0 1px 6px rgba(0,0,0,.06); border: 1px solid #f1f5f9;
}
.stat-box__icon {
  width: 48px; height: 48px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  font-size: 20px; flex-shrink: 0;
}
.stat-box__num { font-size: 26px; font-weight: 800; color: #1e293b; line-height: 1; }
.stat-box__lbl { font-size: 12px; color: #64748b; margin-top: 4px; }

.main-grid { display: grid; grid-template-columns: 1fr 300px; gap: 20px; align-items: start; }
@media(max-width:900px){ .main-grid { grid-template-columns: 1fr; } }

.section-card {
  background: #fff; border-radius: 14px;
  border: 1px solid #f1f5f9;
  box-shadow: 0 1px 6px rgba(0,0,0,.06); overflow: hidden;
}
.section-card__header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 20px; border-bottom: 1px solid #f1f5f9;
}
.section-card__title { font-size: 15px; font-weight: 700; color: #1e293b; }

.jobs-table { width: 100%; border-collapse: collapse; }
.jobs-table th {
  font-size: 12px; font-weight: 600; color: #94a3b8;
  text-transform: uppercase; letter-spacing: .5px;
  padding: 12px 20px; background: #f8fafc;
  border-bottom: 1px solid #f1f5f9; text-align: left;
}
.jobs-table td { padding: 14px 20px; border-bottom: 1px solid #f8fafc; font-size: 14px; }
.jobs-table tr:last-child td { border-bottom: none; }
.jobs-table tr:hover td { background: #fafcff; }

.status-open   { background: #f0fdf7; color: #10b981; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; }
.status-closed { background: #fef2f2; color: #ef4444; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; }

.count-badge {
  background: #eff6ff; color: #3b82f6;
  font-size: 12px; font-weight: 700;
  padding: 4px 10px; border-radius: 20px; display: inline-block;
}

.plan-card {
  border-radius: 14px; overflow: hidden;
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
  color: #fff; margin-bottom: 16px;
}
.plan-card__body { padding: 20px; }
.plan-card__label { font-size: 12px; opacity: .6; margin-bottom: 6px; }
.plan-card__name  { font-size: 20px; font-weight: 800; }
.plan-card__meta  { font-size: 12px; opacity: .7; margin-top: 4px; }
.plan-card__btn   {
  display: block; text-align: center;
  background: #3b82f6; color: #fff;
  padding: 10px; font-size: 13px; font-weight: 700;
  text-decoration: none; margin: 0 20px 20px;
  border-radius: 10px; transition: background .2s;
}
.plan-card__btn:hover { background: #2563eb; text-decoration: none; color: #fff; }

.quick-btn {
  display: flex; align-items: center; gap: 10px;
  padding: 12px 16px; border-radius: 10px;
  border: 1.5px solid #e2e8f0; background: #fff;
  color: #334155; font-size: 14px; font-weight: 500;
  text-decoration: none; transition: all .15s;
  margin-bottom: 8px;
}
.quick-btn:last-child { margin-bottom: 0; }
.quick-btn:hover { border-color: #3b82f6; background: #eff6ff; color: #3b82f6; text-decoration: none; }
.quick-btn i { width: 20px; text-align: center; color: #3b82f6; }
.quick-btn-primary {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  border-color: transparent; color: #fff;
}
.quick-btn-primary:hover { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border-color: transparent; }
.quick-btn-primary i { color: #fff; }

.empty-state { padding: 40px 20px; text-align: center; }
.empty-state i { font-size: 36px; color: #cbd5e1; margin-bottom: 12px; display: block; }
.empty-state p { color: #94a3b8; font-size: 14px; }

.trial-alert {
  background: linear-gradient(135deg, #fef3c7, #fde68a);
  border: 1px solid #fcd34d; border-radius: 12px;
  padding: 14px 20px; display: flex; align-items: center;
  gap: 12px; margin-bottom: 20px;
}
</style>
@endpush

@section('content')

{{-- HERO --}}
<div class="hero-employer">
  <div>
    <h1>Xin chào, {{ auth()->user()->name }}! 🏢</h1>
    <p>Tổng quan hoạt động tuyển dụng của {{ auth()->user()->company_name ?? 'doanh nghiệp bạn' }}</p>
  </div>
  <a href="{{ url('/job/create') }}" class="btn-white"><i class="fas fa-plus" style="margin-right:6px"></i>Đăng tin mới</a>
</div>

{{-- TRIAL ALERT --}}
@if(auth()->user()->user_trial && auth()->user()->user_trial > now() && !auth()->user()->billing_ends)
<div class="trial-alert">
  <i class="fas fa-clock" style="color:#d97706;font-size:18px;flex-shrink:0"></i>
  <span style="font-size:14px;color:#92400e">
    Bạn đang dùng thử miễn phí — còn <strong>{{ now()->diffInDays(auth()->user()->user_trial) }} ngày</strong>.
    <a href="{{ url('/subscribe') }}" style="color:#d97706;font-weight:700;text-decoration:underline">Nâng cấp Premium ngay →</a>
  </span>
</div>
@endif

{{-- STATS --}}
<div class="stat-grid-4">
  <div class="stat-box">
    <div class="stat-box__icon" style="background:#f0fdf7;color:#10b981"><i class="fas fa-briefcase"></i></div>
    <div><div class="stat-box__num">{{ $totalJobs ?? 0 }}</div><div class="stat-box__lbl">Tin đăng</div></div>
  </div>
  <div class="stat-box">
    <div class="stat-box__icon" style="background:#eff6ff;color:#3b82f6"><i class="fas fa-users"></i></div>
    <div><div class="stat-box__num">{{ $totalApplicants ?? 0 }}</div><div class="stat-box__lbl">Ứng viên nộp</div></div>
  </div>
  <div class="stat-box">
    <div class="stat-box__icon" style="background:#fff7ed;color:#f59e0b"><i class="fas fa-star"></i></div>
    <div><div class="stat-box__num">{{ $shortlisted ?? 0 }}</div><div class="stat-box__lbl">Shortlisted</div></div>
  </div>
  <div class="stat-box">
    <div class="stat-box__icon" style="background:#fef2f2;color:#ef4444"><i class="fas fa-fire"></i></div>
    <div><div class="stat-box__num">{{ $activeJobs ?? 0 }}</div><div class="stat-box__lbl">Đang tuyển</div></div>
  </div>
</div>

{{-- MAIN GRID --}}
<div class="main-grid">

  {{-- Jobs Table --}}
  <div class="section-card">
    <div class="section-card__header">
      <span class="section-card__title"><i class="fas fa-list-alt" style="color:#3b82f6;margin-right:8px"></i>Tin đăng gần đây</span>
      <a href="{{ url('/job/manage') }}" style="font-size:13px;color:#3b82f6;font-weight:600;text-decoration:none">Quản lý tất cả →</a>
    </div>
    @if(($recentJobs ?? collect())->isNotEmpty())
    <table class="jobs-table">
      <thead>
        <tr>
          <th>Tên tin đăng</th>
          <th style="text-align:center">Ứng viên</th>
          <th>Hết hạn</th>
          <th>Trạng thái</th>
        </tr>
      </thead>
      <tbody>
        @foreach($recentJobs as $job)
        <tr>
          <td>
            <a href="{{ url('/job/show/'.$job->slug) }}" target="_blank"
               style="font-weight:600;color:#1e293b;text-decoration:none">
              {{ Str::limit($job->title, 38) }}
            </a>
          </td>
          <td style="text-align:center">
            <span class="count-badge">{{ $job->users->count() }}</span>
          </td>
          <td style="color:#64748b;font-size:13px">
            {{ $job->application_close_date ? $job->application_close_date->format('d/m/Y') : '∞ Không giới hạn' }}
          </td>
          <td>
            @if($job->application_close_date && $job->application_close_date->isPast())
              <span class="status-closed">Hết hạn</span>
            @else
              <span class="status-open">Đang mở</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @else
    <div class="empty-state">
      <i class="fas fa-inbox"></i>
      <p>Bạn chưa đăng tin tuyển dụng nào.<br>
        <a href="{{ url('/job/create') }}" style="color:#3b82f6;font-weight:600">Đăng tin đầu tiên →</a>
      </p>
    </div>
    @endif
  </div>

  {{-- Sidebar --}}
  <div>
    {{-- Plan Card --}}
    <div class="plan-card">
      <div class="plan-card__body">
        <div class="plan-card__label"><i class="fas fa-crown" style="color:gold;margin-right:4px"></i> Gói doanh nghiệp</div>
        @if(auth()->user()->billing_ends && auth()->user()->billing_ends > now())
          <div class="plan-card__name" style="color:#7effc4">{{ ucfirst(auth()->user()->plan) }} Premium</div>
          <div class="plan-card__meta">Hạn dùng: {{ auth()->user()->billing_ends->format('d/m/Y') }}</div>
        @elseif(auth()->user()->user_trial && auth()->user()->user_trial > now())
          <div class="plan-card__name" style="color:#fcd34d">Dùng thử (Trial)</div>
          <div class="plan-card__meta">Còn lại: {{ now()->diffInDays(auth()->user()->user_trial) }} ngày</div>
        @else
          <div class="plan-card__name" style="color:#fca5a5">Chưa đăng ký</div>
          <div class="plan-card__meta">Nâng cấp để đăng tin không giới hạn</div>
        @endif
      </div>
      @if(!auth()->user()->billing_ends || auth()->user()->billing_ends <= now())
      <a href="{{ url('/subscribe') }}" class="plan-card__btn"><i class="fas fa-rocket" style="margin-right:6px"></i>Nâng cấp Premium</a>
      @endif
    </div>

    {{-- Quick Actions --}}
    <div class="section-card">
      <div class="section-card__header">
        <span class="section-card__title">Thao tác nhanh</span>
      </div>
      <div style="padding:14px">
        <a href="{{ url('/job/create') }}"  class="quick-btn quick-btn-primary"><i class="fas fa-plus"></i> Đăng tin tuyển dụng</a>
        <a href="{{ url('/applicants') }}"  class="quick-btn"><i class="fas fa-users"></i> Danh sách ứng viên</a>
        <a href="{{ url('/job/manage') }}"  class="quick-btn"><i class="fas fa-cog"></i> Quản lý tin đăng</a>
        <a href="{{ url('/messages') }}"    class="quick-btn"><i class="fas fa-comment-dots"></i> Tin nhắn</a>
        <a href="{{ url('/user/profile') }}" class="quick-btn"><i class="fas fa-building"></i> Hồ sơ công ty</a>
      </div>
    </div>
  </div>

</div>
@endsection
