@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.app')

@section('title', 'Chi tiết đơn ứng tuyển')

@section('content')
<div class="container section">

  <div class="flex-between mb-24">
    <div>
      <h1 class="fw-700 fs-22" style="color:var(--secondary)">📄 Chi tiết đơn ứng tuyển</h1>
      <p class="text-muted fs-13 mt-8">{{ $application->user->name }} → {{ $application->listing->title }}</p>
    </div>
    <div class="flex gap-8" style="align-items: center;">
      @if(auth()->check() && auth()->user()->isEmployer())
        <form action="{{ route('messages.start') }}" method="POST" style="margin: 0;">
          @csrf
          <input type="hidden" name="listing_id" value="{{ $application->listing_id }}">
          <input type="hidden" name="employee_id" value="{{ $application->user_id }}">
          <button type="submit" class="btn btn-outline btn-sm">
            <i class="fas fa-comment"></i> Nhắn tin với ứng viên
          </button>
        </form>
      @endif
      <a href="{{ route('employer.applicants', $application->listing_id) }}" class="btn btn-outline btn-sm">
        <i class="fas fa-arrow-left fa-fw"></i> Về danh sách ứng viên
      </a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success mb-16"><i class="fas fa-check-circle fa-fw"></i> {{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger mb-16"><i class="fas fa-exclamation-circle fa-fw"></i> {{ session('error') }}</div>
  @endif

  <div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">

    {{-- LEFT: Thông tin ứng viên --}}
    <div style="display:flex;flex-direction:column;gap:16px">

      <div class="card">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between">
          <span class="fw-700">👤 Thông tin ứng viên</span>
          @if($application->applicant_name)
            <span style="background:#e0f2fe;color:#0369a1;font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px">
              <i class="fas fa-sync-alt fa-fw"></i> Thông tin mới nhất
            </span>
          @endif
        </div>
        <div class="card-body">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;font-size:13px">
            <div>
              <div class="text-muted fs-12 mb-8">Họ tên</div>
              <div class="fw-600">{{ $application->applicant_name ?: $application->user->name }}</div>
            </div>
            <div>
              <div class="text-muted fs-12 mb-8">Email liên hệ</div>
              <div><a href="mailto:{{ $application->applicant_email ?: $application->user->email }}" style="color:var(--primary)">{{ $application->applicant_email ?: $application->user->email }}</a></div>
            </div>
            <div>
              <div class="text-muted fs-12 mb-8">Số điện thoại</div>
              <div>
                @if($application->applicant_phone)
                  <a href="tel:{{ $application->applicant_phone }}" style="color:var(--primary)">{{ $application->applicant_phone }}</a>
                @else
                  <span class="text-muted">—</span>
                @endif
              </div>
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

      @if($application->cover_letter)
        <div class="card">
          <div class="card-header"><span class="fw-700">✉️ Thư xin việc</span></div>
          <div class="card-body"><p style="font-size:14px;line-height:1.75;white-space:pre-line;color:var(--text-body)">{{ $application->cover_letter }}</p></div>
        </div>
      @endif

      @if($application->cv)
        <div class="card">
          <div class="card-header"><span class="fw-700">📎 File CV</span></div>
          <div class="card-body">
            <div class="flex gap-12" style="align-items:center">
              <div style="width:40px;height:40px;border-radius:var(--radius-md);background:#FFF2EE;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-file-alt" style="color:var(--danger)"></i>
              </div>
              <div style="flex:1"><div class="fw-600 fs-13">{{ $application->cv->original_name }}</div></div>
              <a href="{{ Storage::url($application->cv->file_path) }}" target="_blank" class="btn btn-primary btn-sm">
                <i class="fas fa-download fa-fw"></i> Tải xuống
              </a>
            </div>
          </div>
        </div>
      @endif

      @if($application->status === 'interviewing' && $application->interview_scheduled_at)
        <div class="card" style="border:1px solid var(--primary)">
          <div class="card-header" style="background:var(--primary-light)">
            <span class="fw-700" style="color:var(--primary-dark)">🗓️ Lịch phỏng vấn đã đặt</span>
          </div>
          <div class="card-body">
            <div class="fw-700" style="font-size:18px;color:var(--secondary)">
              {{ $application->interview_scheduled_at->format('H:i — d/m/Y') }}
            </div>
            <div class="text-muted fs-13 mt-8">
              {{ $application->interview_scheduled_at->diffForHumans() }}
            </div>
          </div>
        </div>
      @endif
    </div>

    {{-- RIGHT: Cập nhật trạng thái --}}
    <div style="display:flex;flex-direction:column;gap:12px">
      <div class="card">
        <div class="card-header"><span class="fw-700">🔄 Trạng thái</span></div>
        <div class="card-body">
          @php
            $statusConfig = [
              'submitted'    => ['label'=>'Đã nộp',       'class'=>'badge-warning'],
              'viewed'       => ['label'=>'Đã xem',       'class'=>'badge-primary', 'style'=>'background:#E6F4FF;color:#096DD9'],
              'approved'     => ['label'=>'Duyệt hồ sơ',  'class'=>'', 'style'=>'background:#f0f0ff;color:#4338ca'],
              'interviewing' => ['label'=>'Phỏng vấn',    'class'=>'badge-primary'],
              'rejected'     => ['label'=>'Chưa phù hợp', 'class'=>'badge-danger'],
            ];
            $s = $statusConfig[$application->status] ?? ['label'=>$application->status,'class'=>''];
            $allowedNext = \App\Models\Application::STATUS_TRANSITIONS[$application->status] ?? [];
            $isClosed = $application->isClosed();
          @endphp

          <div class="mb-16">
            <div class="text-muted fs-12 mb-8">Hiện tại</div>
            <span class="badge {{ $s['class'] ?? '' }}" style="{{ $s['style'] ?? '' }}">{{ $s['label'] }}</span>
            @if($application->status_updated_at)
              <div class="text-muted fs-12 mt-8">{{ $application->status_updated_at->diffForHumans() }}</div>
            @endif
          </div>

          @if($isClosed)
            <div class="alert" style="background:var(--bg-gray);border:1px solid var(--border);padding:10px 12px;border-radius:var(--radius-md);font-size:12px;color:var(--text-secondary);margin:0">
              <i class="fas fa-lock fa-fw"></i> Trạng thái đóng — không thể cập nhật thêm.
            </div>
          @elseif(count($allowedNext) > 0)
            <form action="{{ route('employer.application.status', $application->id) }}" method="POST" id="status-form">
              @csrf @method('PATCH')

              <div class="form-group" style="margin-bottom:12px">
                <label class="form-label" style="font-size:12px">Chuyển sang</label>
                <select name="status" id="status-select" class="form-control" style="font-size:13px" onchange="handleStatusChange(this.value)">
                  <option value="">— Chọn trạng thái —</option>
                  @foreach($allowedNext as $status)
                    @php $ns = $statusConfig[$status] ?? ['label'=>$status]; @endphp
                    <option value="{{ $status }}">{{ $ns['label'] }}</option>
                  @endforeach
                </select>
              </div>

              {{-- Interview datetime picker (hidden by default) --}}
              <div id="interview-datetime-wrap" style="display:none;margin-bottom:12px">
                <label class="form-label" style="font-size:12px">
                  📅 Ngày/Giờ phỏng vấn dự kiến <span class="text-danger">*</span>
                </label>
                <input type="datetime-local" name="interview_scheduled_at" id="interview-dt"
                  class="form-control" style="font-size:13px"
                  min="{{ now()->addHours(1)->format('Y-m-d\TH:i') }}">
                <div class="text-muted fs-12 mt-8">
                  <i class="fas fa-info-circle fa-fw"></i> Email mời phỏng vấn sẽ gửi tự động đến ứng viên
                </div>
              </div>

              <button type="submit" id="status-submit-btn" class="btn btn-primary btn-block btn-sm" disabled>
                <i class="fas fa-save fa-fw"></i> Cập nhật & Gửi thông báo
              </button>
            </form>
          @else
            <div class="text-muted fs-12" style="font-style:italic">Không có hành động nào khả dụng.</div>
          @endif
        </div>
      </div>
    </div>

  </div>
</div>

<script>
function handleStatusChange(val) {
  const dtWrap = document.getElementById('interview-datetime-wrap');
  const dtInput = document.getElementById('interview-dt');
  const submitBtn = document.getElementById('status-submit-btn');

  if (val === 'interviewing') {
    dtWrap.style.display = 'block';
    dtInput.required = true;
    submitBtn.disabled = !dtInput.value;
    dtInput.addEventListener('change', () => {
      submitBtn.disabled = !dtInput.value;
    });
  } else {
    dtWrap.style.display = 'none';
    dtInput.required = false;
    submitBtn.disabled = !val;
  }
}

// Validate before submit
document.getElementById('status-form')?.addEventListener('submit', function(e) {
  const status = document.getElementById('status-select').value;
  const dt = document.getElementById('interview-dt').value;
  if (!status) { e.preventDefault(); alert('Vui lòng chọn trạng thái.'); return; }
  if (status === 'interviewing' && !dt) { e.preventDefault(); alert('Vui lòng chọn ngày/giờ phỏng vấn.'); return; }
});
</script>
@endsection
