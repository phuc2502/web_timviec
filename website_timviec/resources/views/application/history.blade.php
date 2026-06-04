@extends('layouts.app')

@section('title', 'Lịch sử ứng tuyển')

@section('content')
<div class="container section">

  <div class="flex-between mb-24">
    <div>
      <h1 class="fw-700 fs-24" style="color:var(--secondary)">📂 Lịch sử ứng tuyển</h1>
      <p class="text-muted fs-13 mt-8">Theo dõi trạng thái tất cả đơn bạn đã nộp</p>
    </div>
    @php
      $tokenRecord = \App\Models\UserToken::where('user_id', auth()->id())->first();
      $balance = $tokenRecord?->balance ?? 0;
    @endphp
    <div class="flex gap-8" style="align-items:center">
      <div style="background:var(--primary-light);color:var(--primary-dark);padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600">
        <i class="fas fa-ticket-alt fa-fw"></i> {{ $balance }} lượt còn lại
      </div>
      <a href="{{ route('payment.token') }}" class="btn btn-outline btn-sm">Mua thêm</a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success mb-16">✅ {{ session('success') }}</div>
  @endif

  @if($applications->isEmpty())
    <div class="card">
      <div class="card-body text-center" style="padding:64px 24px">
        <div style="font-size:56px;margin-bottom:16px">📭</div>
        <div class="fw-700 fs-16 mb-8">Bạn chưa ứng tuyển công việc nào</div>
        <p class="text-muted fs-13 mb-16">Hãy tìm và ứng tuyển những công việc phù hợp ngay!</p>
        <a href="{{ url('/job') }}" class="btn btn-primary">
          <i class="fas fa-search fa-fw"></i> Tìm việc ngay
        </a>
      </div>
    </div>
  @else
    <div style="display:flex;flex-direction:column;gap:12px">
      @foreach($applications as $app)
        @php
          $statusConfig = [
            'submitted'    => ['label'=>'Đã nộp',       'color'=>'#D48806',           'bg'=>'#FFF7E6',            'icon'=>'fa-paper-plane'],
            'viewed'       => ['label'=>'Đã xem',       'color'=>'#096DD9',           'bg'=>'#E6F4FF',            'icon'=>'fa-eye'],
            'approved'     => ['label'=>'Duyệt hồ sơ',  'color'=>'#4338ca',           'bg'=>'#F0F0FF',            'icon'=>'fa-thumbs-up'],
            'interviewing' => ['label'=>'Phỏng vấn',    'color'=>'var(--primary-dark)','bg'=>'var(--primary-light)','icon'=>'fa-calendar-check'],
            'rejected'     => ['label'=>'Chưa phù hợp', 'color'=>'var(--danger)',      'bg'=>'#FFF2EE',            'icon'=>'fa-times-circle'],
          ];
          $s = $statusConfig[$app->status] ?? ['label'=>$app->status,'color'=>'var(--text-secondary)','bg'=>'var(--bg-gray)','icon'=>'fa-circle'];
        @endphp
        <div class="card" style="transition:var(--transition)"
          onmouseover="this.style.boxShadow='var(--shadow-lg)'"
          onmouseout="this.style.boxShadow=''">
          <div class="card-body" style="padding:16px 20px">
            <div class="flex gap-16" style="align-items:center">

              {{-- Icon --}}
              <div style="width:48px;height:48px;border-radius:var(--radius-md);border:1px solid var(--border);background:#fafafa;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-building" style="color:var(--primary)"></i>
              </div>

              {{-- Thông tin --}}
              <div style="flex:1;min-width:0">
                <div class="fw-700" style="color:var(--secondary);font-size:15px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                  {{ $app->listing->title ?? 'Công việc đã xóa' }}
                </div>
                <div class="fs-13 text-muted mt-8">
                  <i class="fas fa-building fa-fw"></i>
                  {{ $app->listing->user->company_name ?? $app->listing->user->name ?? '—' }}
                  &nbsp;·&nbsp;
                  <i class="fas fa-map-marker-alt fa-fw"></i>
                  {{ $app->listing->address ?? '—' }}
                  &nbsp;·&nbsp;
                  <i class="fas fa-calendar fa-fw"></i>
                  Nộp {{ $app->applied_at->format('d/m/Y') }}
                </div>
              </div>

              {{-- Status + actions --}}
              <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;flex-shrink:0">
                <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;background:{{ $s['bg'] }};color:{{ $s['color'] }}">
                  <i class="fas {{ $s['icon'] }} fa-fw"></i> {{ $s['label'] }}
                </span>
                {{-- CV đã nộp (SSOT từ bảng applications) --}}
                @if($app->cv)
                  <div class="fs-12" style="color:var(--text-secondary);max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    <i class="fas fa-paperclip fa-fw"></i> {{ $app->cv->original_name }}
                  </div>
                @endif
                <div class="flex gap-8">
                  <a href="{{ route('candidate.application.detail', $app->id) }}"
                     class="btn btn-outline btn-sm" style="font-size:12px;padding:4px 10px">
                    <i class="fas fa-file-alt fa-fw"></i> Đơn của tôi
                  </a>
                  @if($app->listing)
                    <a href="{{ url('/job/show/'.$app->listing->slug) }}"
                       class="btn btn-outline btn-sm" style="font-size:12px;padding:4px 10px" target="_blank">
                      <i class="fas fa-briefcase fa-fw"></i> Xem việc làm
                    </a>
                  @endif
                </div>
              </div>

            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="mt-24">{{ $applications->links() }}</div>
  @endif
</div>
@endsection
