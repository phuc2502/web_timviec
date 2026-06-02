@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')

@section('title', 'Chi tiết đơn ứng tuyển')

@section('content')
<div class="container section">

  <div class="flex-between mb-24">
    <div>
      <h1 class="fw-700 fs-24" style="color:var(--secondary)">📄 Chi tiết đơn ứng tuyển</h1>
      <p class="text-muted fs-13 mt-8">{{ $application->user->name }} → {{ $application->listing->title }}</p>
    </div>
    <a href="{{ route('employer.applicants', $application->listing_id) }}" class="btn btn-outline btn-sm">
      <i class="fas fa-arrow-left fa-fw"></i> Về danh sách ứng viên
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success mb-16"><i class="fas fa-check-circle fa-fw"></i> {{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger mb-16"><i class="fas fa-exclamation-circle fa-fw"></i> {{ session('error') }}</div>
  @endif

  <div class="flex gap-24" style="align-items:flex-start">

    {{-- Cột trái: Thông tin ứng viên --}}
    <div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:16px">

      {{-- Thông tin cơ bản --}}
      <div class="card">
        <div class="card-header"><span class="fw-700">👤 Thông tin ứng viên</span></div>
        <div class="card-body">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;font-size:13px">
            <div>
              <div class="text-muted fs-12 mb-8">Họ tên</div>
              <div class="fw-600">{{ $application->user->name }}</div>
            </div>
            <div>
              <div class="text-muted fs-12 mb-8">Email</div>
              <div>{{ $application->user->email }}</div>
            </div>
            <div>
              <div class="text-muted fs-12 mb-8">Vị trí ứng tuyển</div>
              <div class="fw-600" style="color:var(--primary)">{{ $application->listing->title }}</div>
            </div>
            <div>
              <div class="text-muted fs-12 mb-8">Ngày nộp</div>
              <div>{{ $application->applied_at->format('d/m/Y H:i') }}</div>
            </div>
          </div>
        </div>
      </div>

      {{-- Thư xin việc --}}
      @if($application->cover_letter)
        <div class="card">
          <div class="card-header"><span class="fw-700">✉️ Thư xin việc</span></div>
          <div class="card-body">
            <p style="font-size:14px;line-height:1.7;color:var(--text-body);white-space:pre-line">{{ $application->cover_letter }}</p>
          </div>
        </div>
      @endif

      {{-- File CV --}}
      @if($application->cv)
        <div class="card">
          <div class="card-header"><span class="fw-700">📎 File CV</span></div>
          <div class="card-body">
            <div class="flex gap-12" style="align-items:center">
              <div style="width:40px;height:40px;border-radius:var(--radius-md);background:#FFF2EE;display:flex;align-items:center;justify-content:center">
                <i class="fas fa-file-pdf" style="color:var(--danger)"></i>
              </div>
              <div style="flex:1">
                <div class="fw-600 fs-13">{{ $application->cv->original_name }}</div>
                <div class="text-muted fs-12">CV đã tải lên</div>
              </div>
              <a href="{{ Storage::url($application->cv->file_path) }}" target="_blank" class="btn btn-primary btn-sm">
                <i class="fas fa-download fa-fw"></i> Tải xuống
              </a>
            </div>
          </div>
        </div>
      @endif

    </div>

    {{-- Cột phải: Cập nhật trạng thái --}}
    <div style="width:280px;flex-shrink:0;display:flex;flex-direction:column;gap:16px">

      {{-- Trạng thái hiện tại --}}
      <div class="card">
        <div class="card-header"><span class="fw-700">🔄 Trạng thái</span></div>
        <div class="card-body">
          @php
            $statusConfig = [
              'submitted'    => ['label'=>'Đã nộp',    'class'=>'badge-warning'],
              'viewed'       => ['label'=>'Đã xem',    'class'=>'badge-primary'],
              'interviewing' => ['label'=>'Phỏng vấn', 'style'=>'background:#f0f0ff;color:#5b21b6'],
              'accepted'     => ['label'=>'Đã nhận',   'class'=>'badge-primary'],
              'rejected'     => ['label'=>'Từ chối',   'class'=>'badge-danger'],
            ];
            $s = $statusConfig[$application->status] ?? ['label'=>$application->status,'class'=>''];
            $allowedNext = \App\Models\Application::STATUS_TRANSITIONS[$application->status] ?? [];
          @endphp

          <div class="mb-16">
            <div class="text-muted fs-12 mb-8">Hiện tại</div>
            <span class="badge {{ $s['class'] ?? '' }}" style="{{ $s['style'] ?? '' }}">
              {{ $s['label'] }}
            </span>
            @if($application->status_updated_at)
              <div class="text-muted fs-12 mt-8">Cập nhật {{ $application->status_updated_at->diffForHumans() }}</div>
            @endif
          </div>

          @if(count($allowedNext) > 0)
            <form action="{{ route('employer.application.status', $application->id) }}" method="POST">
              @csrf
              @method('PATCH')
              <div class="form-group" style="margin-bottom:12px">
                <label class="form-label" style="font-size:12px">Chuyển sang trạng thái</label>
                <select name="status" class="form-control" style="font-size:13px">
                  @foreach($allowedNext as $status)
                    @php $ns = $statusConfig[$status] ?? ['label'=>$status]; @endphp
                    <option value="{{ $status }}">{{ $ns['label'] }}</option>
                  @endforeach
                </select>
              </div>
              <button type="submit" class="btn btn-primary btn-block btn-sm">
                <i class="fas fa-save fa-fw"></i> Cập nhật & Gửi email
              </button>
            </form>
          @else
            <div class="alert alert-info" style="font-size:12px;padding:8px 12px;margin:0">
              <i class="fas fa-info-circle fa-fw"></i> Đơn đã ở trạng thái cuối.
            </div>
          @endif
        </div>
      </div>

    </div>
  </div>

</div>
@endsection
