@extends('layouts.dashboard')
@section('title', 'Ứng viên — ' . $listing->title)

@section('content')
<div class="flex-between mb-20">
  <div>
    <a href="{{ url('/applicants') }}" class="text-muted fs-13"><i class="fas fa-arrow-left"></i> Quay lại</a>
    <h1 class="fs-20 fw-800 mt-4" style="color:var(--secondary)">{{ $listing->title }}</h1>
    <p class="text-muted fs-13 mt-2">
      <i class="fas fa-users"></i> {{ $applicants->total() }} ứng viên
      <span class="mx-8">·</span>
      <i class="fas fa-map-marker-alt"></i> {{ $listing->address }}
    </p>
  </div>
  <a href="{{ url('/job/'.$listing->id.'/edit') }}" class="btn btn-outline btn-sm"><i class="fas fa-edit"></i> Chỉnh sửa tin</a>
</div>

@if($applicants->isEmpty())
  <div class="card text-center" style="padding:56px 24px">
    <div style="font-size:48px;margin-bottom:14px">📭</div>
    <div class="fw-700 fs-16">Chưa có ứng viên nào</div>
    <p class="text-muted mt-8 fs-13">Chia sẻ tin đăng để thu hút nhiều ứng viên hơn</p>
  </div>
@else
  <div class="flex-col gap-12">
    @foreach($applicants as $applicant)
      <div class="card" style="{{ $applicant->pivot->shortlisted ? 'border-left:4px solid var(--primary)' : '' }}">
        <div class="card-body" style="padding:18px 20px">
          <div class="flex gap-16" style="align-items:center">
            {{-- Avatar --}}
            @if($applicant->profile_pic)
              <img src="{{ asset('storage/images/'.$applicant->profile_pic) }}" class="avatar avatar-md" alt="">
            @else
              <div class="avatar avatar-md avatar-placeholder" style="background:var(--primary-light);color:var(--primary);font-size:18px;font-weight:700">
                {{ strtoupper(substr($applicant->name, 0, 1)) }}
              </div>
            @endif

            {{-- Info --}}
            <div style="flex:1">
              <div class="flex gap-8" style="align-items:center">
                <span class="fw-700 fs-15">{{ $applicant->name }}</span>
                @if($applicant->pivot->shortlisted)
                  <span class="tag tag-green" style="font-size:11px"><i class="fas fa-star" style="margin-right:3px"></i>Shortlisted</span>
                @endif
              </div>
              <div class="text-muted fs-12 mt-4">
                <i class="fas fa-envelope fa-fw"></i> {{ $applicant->email }}
                @if($applicant->about)
                  <span class="mx-8">·</span>
                  {{ Str::limit($applicant->about, 60) }}
                @endif
              </div>
              <div class="flex gap-8 mt-6">
                <span class="text-muted fs-12">
                  <i class="fas fa-clock fa-fw"></i> Nộp: {{ $applicant->pivot->created_at->diffForHumans() }}
                </span>
              </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-8" style="flex-shrink:0">
              @if($applicant->resume)
                <a href="{{ asset('storage/resume/'.$applicant->resume) }}" target="_blank" class="btn btn-outline btn-sm">
                  <i class="fas fa-file-pdf"></i> Xem CV
                </a>
              @endif
              <form action="{{ route('employer.shortlist.toggle', [$listing->id, $applicant->id]) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm {{ $applicant->pivot->shortlisted ? 'btn-secondary' : 'btn-primary' }}">
                  <i class="fas {{ $applicant->pivot->shortlisted ? 'fa-times' : 'fa-star' }}"></i>
                  {{ $applicant->pivot->shortlisted ? 'Bỏ shortlist' : 'Shortlist' }}
                </button>
              </form>
              <a href="{{ url('/messages?to='.$applicant->id) }}" class="btn btn-outline btn-sm">
                <i class="fas fa-comment"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Pagination --}}
  @if($applicants->hasPages())
    <div class="flex-center mt-20">
      <div class="pagination">
        @if(!$applicants->onFirstPage())
          <a href="{{ $applicants->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>
        @endif
        @foreach($applicants->getUrlRange(1, $applicants->lastPage()) as $page => $url)
          @if($page == $applicants->currentPage())
            <span class="active">{{ $page }}</span>
          @else
            <a href="{{ $url }}">{{ $page }}</a>
          @endif
        @endforeach
        @if($applicants->hasMorePages())
          <a href="{{ $applicants->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>
        @endif
      </div>
    </div>
  @endif
@endif
@endsection
