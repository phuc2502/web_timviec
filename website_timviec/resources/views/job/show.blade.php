@extends('layouts.app')
@section('title', $listing->title . ' — ITWorks')
@section('description', Str::limit(strip_tags($listing->description), 160))

@section('content')
<div class="container section">
  <div class="main-layout main-layout--reverse">

    {{-- MAIN --}}
    <div style="flex:1;min-width:0">

      {{-- JOB HEADER --}}
      <div class="card mb-16">
        <div class="card-body" style="padding:28px">
          <div class="flex gap-16" style="align-items:flex-start">
            <div style="width:80px;height:80px;border:1px solid var(--border);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;flex-shrink:0;background:#fafafa;overflow:hidden">
              @if($listing->feature_image)
                <img src="{{ asset('storage/images/'.$listing->feature_image) }}" alt="" style="width:72px;height:72px;object-fit:contain">
              @else
                <i class="fas fa-building fa-2x" style="color:var(--primary)"></i>
              @endif
            </div>
            <div style="flex:1">
              <h1 style="font-size:22px;font-weight:800;color:var(--secondary);line-height:1.3">{{ $listing->title }}</h1>
              <div class="flex gap-16 mt-8 flex-wrap" style="font-size:13px;color:var(--text-secondary)">
                <span><i class="fas fa-building fa-fw"></i> {{ $listing->user->company_name ?? $listing->user->name }}</span>
                <span><i class="fas fa-map-marker-alt fa-fw"></i> {{ $listing->address }}</span>
                <span><i class="fas fa-clock fa-fw"></i> {{ $listing->job_type }}</span>
              </div>
              <div class="flex gap-8 mt-12 flex-wrap">
                <span class="tag tag-green fs-13" style="padding:5px 14px">
                  <i class="fas fa-money-bill-wave" style="margin-right:5px"></i>
                  {{ $listing->salary == 0 ? 'Thỏa thuận' : number_format($listing->salary).' đ/tháng' }}
                </span>
                <span class="tag tag-blue fs-13" style="padding:5px 14px">
                  <i class="fas fa-calendar-times" style="margin-right:5px"></i>
                  Hết hạn: {{ \Carbon\Carbon::parse($listing->application_close_date)->format('d/m/Y') }}
                </span>
                <span class="tag tag-gray fs-13" style="padding:5px 14px">
                  <i class="fas fa-users" style="margin-right:5px"></i>
                  {{ $listing->users->count() }} ứng viên
                </span>
              </div>
            </div>
          </div>

          <div class="divider mt-20"></div>

          <div class="flex gap-10">
            @auth
              @if(auth()->user()->user_type === 'employee')
                @if($listing->users->contains(auth()->id()))
                  <button class="btn btn-outline" disabled style="cursor:not-allowed;opacity:.6">
                    <i class="fas fa-check-circle"></i> Đã ứng tuyển
                  </button>
                @else
                  <form action="{{ url('/application/'.$listing->id.'/submit') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-lg">
                      <i class="fas fa-paper-plane"></i> Ứng tuyển ngay
                    </button>
                  </form>
                @endif
              @endif
            @else
              <a href="{{ url('/login') }}" class="btn btn-primary btn-lg">
                <i class="fas fa-paper-plane"></i> Đăng nhập để ứng tuyển
              </a>
            @endauth
            <button class="btn btn-outline" onclick="shareJob()"><i class="fas fa-share-alt"></i> Chia sẻ</button>
          </div>
        </div>
      </div>

      {{-- JOB DESCRIPTION --}}
      <div class="card mb-16">
        <div class="card-header"><span class="fw-700 fs-15">Mô tả công việc</span></div>
        <div class="card-body" style="padding:24px;line-height:1.8;font-size:14px">
          {!! nl2br(e($listing->description)) !!}
        </div>
      </div>

      {{-- REQUIREMENTS --}}
      @if($listing->roles)
        <div class="card mb-16">
          <div class="card-header"><span class="fw-700 fs-15">Yêu cầu công việc</span></div>
          <div class="card-body" style="padding:24px;line-height:1.8;font-size:14px">
            {!! nl2br(e($listing->roles)) !!}
          </div>
        </div>
      @endif

      @if($listing->predes)
        <div class="card">
          <div class="card-header"><span class="fw-700 fs-15">Mô tả thêm</span></div>
          <div class="card-body" style="padding:24px;line-height:1.8;font-size:14px">
            {!! nl2br(e($listing->predes)) !!}
          </div>
        </div>
      @endif
    </div>

    {{-- SIDEBAR --}}
    <div class="sidebar">
      {{-- Quick Info --}}
      <div class="sidebar-card">
        <div class="sidebar-card__title">Thông tin chung</div>
        <div class="sidebar-card__body">
          <div class="flex-col gap-12" style="font-size:13px">
            <div class="flex-between">
              <span class="text-muted"><i class="fas fa-briefcase fa-fw"></i> Loại hình</span>
              <span class="fw-600">{{ $listing->job_type }}</span>
            </div>
            <div class="divider" style="margin:0"></div>
            <div class="flex-between">
              <span class="text-muted"><i class="fas fa-map-marker-alt fa-fw"></i> Địa điểm</span>
              <span class="fw-600">{{ $listing->address }}</span>
            </div>
            <div class="divider" style="margin:0"></div>
            <div class="flex-between">
              <span class="text-muted"><i class="fas fa-money-bill fa-fw"></i> Mức lương</span>
              <span class="fw-600 text-primary-color">{{ $listing->salary == 0 ? 'Thỏa thuận' : number_format($listing->salary).'đ' }}</span>
            </div>
            <div class="divider" style="margin:0"></div>
            <div class="flex-between">
              <span class="text-muted"><i class="fas fa-calendar fa-fw"></i> Hết hạn</span>
              <span class="fw-600">{{ \Carbon\Carbon::parse($listing->application_close_date)->format('d/m/Y') }}</span>
            </div>
          </div>
        </div>
      </div>

      {{-- Employer Info --}}
      <div class="sidebar-card">
        <div class="sidebar-card__title">Về nhà tuyển dụng</div>
        <div class="sidebar-card__body" style="text-align:center">
          <div style="width:64px;height:64px;background:var(--primary-light);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;margin:0 auto 10px;font-size:24px;color:var(--primary)">
            <i class="fas fa-building"></i>
          </div>
          <div class="fw-700 fs-14">{{ $listing->user->company_name ?? $listing->user->name }}</div>
          @if($listing->user->about)
            <p class="text-muted fs-12 mt-8" style="line-height:1.6">{{ Str::limit($listing->user->about, 100) }}</p>
          @endif
        </div>
      </div>

      {{-- OWNER ACTIONS --}}
      @auth
        @if(auth()->user()->id === ($listing->user_id ?? ($listing->user->id ?? null)))
          <div class="sidebar-card">
            <div class="sidebar-card__title">Quản lý tin đăng</div>
            <div class="sidebar-card__body">
              <a href="{{ url('/job/'.$listing->id.'/edit') }}" class="btn btn-outline btn-block mb-8"><i class="fas fa-edit"></i> Chỉnh sửa</a>
              <a href="{{ url('/applicants/'.$listing->slug) }}" class="btn btn-primary btn-block"><i class="fas fa-users"></i> Xem ứng viên</a>
            </div>
          </div>
        @endif
      @endauth
    </div>
  </div>
</div>

@push('scripts')
<script>
function shareJob() {
  if (navigator.share) {
    navigator.share({ title: '{{ $listing->title }}', url: window.location.href });
  } else {
    navigator.clipboard.writeText(window.location.href);
    alert('Đã copy link vào clipboard!');
  }
}
</script>
@endpush
@endsection
