@extends('layouts.app')

@section('title', 'Danh sách ứng viên')

@section('content')
<div class="container section">

@php
  $isPremium     = auth()->user()->isPremium();
  $appCount      = $applications->total();
  $freeAppLimit  = 3;
  $limitReached  = !$isPremium && $appCount >= $freeAppLimit;
@endphp

  <div class="flex-between mb-20">
    <div>
      <h1 class="fw-700 fs-24" style="color:var(--secondary)">👥 Danh sách ứng viên</h1>
      <p class="text-muted fs-13 mt-8">{{ $listing->title ?? '' }}</p>
    </div>
    <a href="{{ route('job.manage') }}" class="btn btn-outline btn-sm">
      <i class="fas fa-arrow-left fa-fw"></i> Về quản lý việc làm
    </a>
  </div>

  {{-- Banner giới hạn Free --}}
  @if($limitReached)
  <div style="background:linear-gradient(135deg,#fff7ed,#fffbeb);border:1.5px solid #fcd34d;border-radius:12px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
    <div style="display:flex;align-items:center;gap:12px">
      <div style="width:42px;height:42px;background:#fef3c7;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="fas fa-crown" style="color:#f59e0b;font-size:18px"></i>
      </div>
      <div>
        <div class="fw-700 fs-14" style="color:#92400e">Đã nhận đủ {{ $freeAppLimit }} hồ sơ — Giới hạn tài khoản Free</div>
        <div class="fs-12" style="color:#b45309;margin-top:3px">Nâng cấp Premium để nhận không giới hạn ứng viên và mở khóa đầy đủ tính năng quản lý.</div>
      </div>
    </div>
    <a href="{{ route('payment.subscription') }}" class="btn btn-sm" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;white-space:nowrap;flex-shrink:0">
      <i class="fas fa-crown"></i> Nâng cấp Premium
    </a>
  </div>
  @elseif(!$isPremium)
  <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;gap:10px">
    <i class="fas fa-info-circle" style="color:#16a34a;font-size:15px"></i>
    <span class="fs-13" style="color:#15803d">Tài khoản Free — Đã nhận <strong>{{ $appCount }}/{{ $freeAppLimit }}</strong> ứng viên cho tin này.</span>
    <a href="{{ route('payment.subscription') }}" class="fs-12 fw-600" style="color:#16a34a;margin-left:auto;white-space:nowrap"><i class="fas fa-crown"></i> Nâng cấp không giới hạn</a>
  </div>
  @endif

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
                  'submitted'    => ['label'=>'Đã nộp',       'class'=>'badge-warning'],
                  'viewed'       => ['label'=>'Đã xem',       'class'=>'', 'style'=>'background:#E6F4FF;color:#096DD9'],
                  'approved'     => ['label'=>'Duyệt hồ sơ',  'class'=>'', 'style'=>'background:#f0f0ff;color:#4338ca'],
                  'interviewing' => ['label'=>'Phỏng vấn',    'class'=>'badge-primary'],
                  'rejected'     => ['label'=>'Chưa phù hợp', 'class'=>'badge-danger'],
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
