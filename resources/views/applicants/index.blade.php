@extends('layouts.dashboard')
@section('title', 'Quản lý ứng viên')

@section('content')
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-20 fw-800" style="color:var(--secondary)">Quản lý ứng viên</h1>
    <p class="text-muted fs-13 mt-4">Danh sách tin đăng và số lượng ứng viên</p>
  </div>
</div>

@if($listings->isEmpty())
  <div class="card text-center" style="padding:56px 24px">
    <div style="font-size:48px;margin-bottom:14px">👥</div>
    <div class="fw-700 fs-16">Chưa có tin tuyển dụng nào</div>
    <a href="{{ url('/job/create') }}" class="btn btn-primary mt-16" style="display:inline-flex"><i class="fas fa-plus"></i> Đăng tin ngay</a>
  </div>
@else
  <div class="flex-col gap-12">
    @foreach($listings as $listing)
      <div class="card">
        <div class="card-body" style="padding:20px">
          <div class="flex gap-16" style="align-items:center">
            <div style="flex:1">
              <a href="{{ url('/job/show/'.$listing->slug) }}" class="fw-700 fs-15" style="color:var(--secondary)" target="_blank">
                {{ $listing->title }}
              </a>
              <div class="flex gap-12 mt-6" style="font-size:12px;color:var(--text-muted)">
                <span><i class="fas fa-map-marker-alt fa-fw"></i>{{ $listing->address }}</span>
                <span><i class="fas fa-clock fa-fw"></i>Hết hạn: {{ \Carbon\Carbon::parse($listing->application_close_date)->format('d/m/Y') }}</span>
                @if(\Carbon\Carbon::parse($listing->application_close_date)->isPast())
                  <span class="status status-closed">Đã hết hạn</span>
                @else
                  <span class="status status-open">Đang mở</span>
                @endif
              </div>
            </div>
            <div class="text-center" style="flex-shrink:0">
              <div class="fw-800 fs-24" style="color:var(--primary)">{{ $listing->users->count() }}</div>
              <div class="text-muted fs-12">ứng viên</div>
            </div>
            <div style="flex-shrink:0">
              <a href="{{ url('/applicants/'.$listing->slug) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-users"></i> Xem ứng viên
              </a>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>
@endif
@endsection
