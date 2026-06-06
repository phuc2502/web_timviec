@extends('layouts.dashboard')
@section('title', 'Tất cả thông báo')

@section('content')
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-20 fw-800" style="color:var(--secondary)">Thông báo</h1>
    <p class="text-muted fs-13 mt-4">Tất cả thông báo của bạn</p>
  </div>
  @if($notifications->total() > 0)
  <form action="{{ route('notifications.read-all') }}" method="POST">
    @csrf
    <button type="submit" class="btn btn-outline btn-sm">
      <i class="fas fa-check-double"></i> Đọc tất cả
    </button>
  </form>
  @endif
</div>

{{-- Flash message --}}
@if(session('success'))
  <div class="alert alert-success mb-16" style="padding:12px 16px;background:#d1fae5;border:1px solid #6ee7b7;border-radius:8px;color:#065f46;font-size:13px">
    <i class="fas fa-check-circle" style="margin-right:6px"></i>{{ session('success') }}
  </div>
@endif

<div class="card">
  @if($notifications->isEmpty())
    <div style="padding:60px;text-align:center;color:var(--text-muted)">
      <i class="fas fa-bell-slash fs-36 mb-16" style="display:block;opacity:.3"></i>
      <div class="fw-600 fs-15">Chưa có thông báo nào</div>
      <div class="fs-13 mt-8">Các thông báo về ứng tuyển, shortlist và cập nhật sẽ hiển thị tại đây.</div>
    </div>
  @else
    @php
      $icons = [
        'shortlisted'        => '🎉',
        'application_status' => '📋',
        'new_application'    => '📥',
        'payment'            => '💳',
        'job_alert'          => '🔔',
        'profile_reminder'   => '📝',
      ];
    @endphp

    @foreach($notifications as $notif)
      @php $icon = $icons[$notif->type] ?? '🔔'; @endphp
      <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;gap:14px;align-items:flex-start;background:{{ $notif->isUnread() ? 'var(--primary-light,#eff6ff)' : '#fff' }};transition:background .2s"
           onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='{{ $notif->isUnread() ? 'var(--primary-light,#eff6ff)' : '#fff' }}'">

        {{-- Icon --}}
        <div style="font-size:28px;line-height:1;flex-shrink:0;width:40px;text-align:center">{{ $icon }}</div>

        {{-- Content --}}
        <div style="flex:1;min-width:0">
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
            <span class="fw-700 fs-14" style="color:var(--text-primary)">{{ $notif->title }}</span>
            @if($notif->isUnread())
              <span style="width:7px;height:7px;background:var(--primary);border-radius:50%;display:inline-block;flex-shrink:0"></span>
            @endif
          </div>
          <div class="fs-13" style="color:var(--text-secondary);line-height:1.6">{{ $notif->body }}</div>
          <div class="fs-12 text-muted mt-6">{{ $notif->created_at->diffForHumans() }}</div>
        </div>

        {{-- Mark read button --}}
        @if($notif->isUnread())
        <form action="{{ route('notifications.read', $notif->id) }}" method="POST" style="flex-shrink:0;margin-top:2px">
          @csrf
          <button type="submit" class="btn btn-outline btn-sm" style="font-size:11px;padding:4px 10px">
            <i class="fas fa-check"></i> Đã đọc
          </button>
        </form>
        @else
          <span class="fs-11 text-muted" style="flex-shrink:0;margin-top:4px;white-space:nowrap">Đã đọc</span>
        @endif
      </div>
    @endforeach

    {{-- Phân trang --}}
    @if($notifications->hasPages())
    <div style="padding:16px 20px;display:flex;justify-content:center">
      {{ $notifications->links() }}
    </div>
    @endif
  @endif
</div>
@endsection
