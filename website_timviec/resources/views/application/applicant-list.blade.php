@extends('layouts.app')

@section('title', 'Danh sách ứng viên')

@section('content')
<div class="container section">

  <div class="flex-between mb-24">
    <div>
      <h1 class="fw-700 fs-24" style="color:var(--secondary)">👥 Danh sách ứng viên</h1>
      <p class="text-muted fs-13 mt-8">Xem và quản lý hồ sơ ứng tuyển</p>
    </div>
    <a href="{{ route('job.manage') }}" class="btn btn-outline btn-sm">
      <i class="fas fa-arrow-left fa-fw"></i> Về quản lý việc làm
    </a>
  </div>

  @if($applications->isEmpty())
    <div class="card">
      <div class="card-body text-center" style="padding:64px 24px">
        <div style="font-size:56px;margin-bottom:16px">📭</div>
        <div class="fw-700 fs-16 mb-8">Chưa có ứng viên nào nộp đơn</div>
        <p class="text-muted fs-13">Hãy chia sẻ tin tuyển dụng để thu hút ứng viên!</p>
      </div>
    </div>
  @else
    <div class="card">
      <div class="card-body" style="padding:0">
        <table style="width:100%;border-collapse:collapse">
          <thead>
            <tr style="border-bottom:2px solid var(--border);background:var(--bg-gray)">
              <th style="padding:12px 20px;text-align:left;font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px">Ứng viên</th>
              <th style="padding:12px 20px;text-align:left;font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px">File CV</th>
              <th style="padding:12px 20px;text-align:left;font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px">Ngày nộp</th>
              <th style="padding:12px 20px;text-align:left;font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px">Trạng thái</th>
              <th style="padding:12px 20px;text-align:center;font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px">Hành động</th>
            </tr>
          </thead>
          <tbody>
            @foreach($applications as $app)
              @php
                $statusConfig = [
                  'submitted'    => ['label'=>'Đã nộp',    'class'=>'badge-warning'],
                  'viewed'       => ['label'=>'Đã xem',    'class'=>'badge-primary'],
                  'interviewing' => ['label'=>'Phỏng vấn', 'style'=>'background:#f0f0ff;color:#5b21b6'],
                  'accepted'     => ['label'=>'Đã nhận',   'class'=>'badge-primary'],
                  'rejected'     => ['label'=>'Từ chối',   'class'=>'badge-danger'],
                ];
                $s = $statusConfig[$app->status] ?? ['label'=>$app->status,'class'=>''];
              @endphp
              <tr style="border-bottom:1px solid var(--border)" onmouseover="this.style.background='var(--bg-gray)'" onmouseout="this.style.background=''">
                <td style="padding:14px 20px">
                  <div class="fw-600" style="color:var(--text-dark)">{{ $app->user->name }}</div>
                  <div class="fs-12 text-muted">{{ $app->user->email }}</div>
                </td>
                <td style="padding:14px 20px;font-size:13px;color:var(--text-secondary)">
                  <i class="fas fa-paperclip fa-fw"></i> {{ $app->cv->original_name ?? '—' }}
                </td>
                <td style="padding:14px 20px;font-size:13px;color:var(--text-secondary)">
                  {{ $app->applied_at->format('d/m/Y H:i') }}
                </td>
                <td style="padding:14px 20px">
                  <span class="badge {{ $s['class'] ?? '' }}" style="{{ $s['style'] ?? '' }}">
                    {{ $s['label'] }}
                  </span>
                </td>
                <td style="padding:14px 20px;text-align:center">
                  <a href="{{ route('employer.application.detail', $app->id) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-eye fa-fw"></i> Xem CV
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <div class="mt-24">{{ $applications->links() }}</div>
  @endif

</div>
@endsection
