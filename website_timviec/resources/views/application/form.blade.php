@extends('layouts.app')

@section('title', 'Ứng tuyển — ' . ($listing->title ?? 'Công việc'))

@section('content')
<div class="container section" style="max-width:720px">

  {{-- Breadcrumb --}}
  <div class="flex gap-8 mb-16" style="align-items:center;font-size:13px;color:var(--text-secondary)">
    <a href="{{ url('/job') }}" style="color:var(--text-secondary)">Tìm việc</a>
    <i class="fas fa-chevron-right" style="font-size:10px"></i>
    <a href="{{ url('/job/show/'.$listing->slug) }}" style="color:var(--text-secondary)">{{ $listing->title }}</a>
    <i class="fas fa-chevron-right" style="font-size:10px"></i>
    <span style="color:var(--text-dark)">Ứng tuyển</span>
  </div>

  {{-- Job info banner --}}
  <div class="card mb-16" style="background:linear-gradient(135deg,#f0fdf4,#e8f8ee);border:1px solid var(--primary)">
    <div class="card-body" style="padding:16px 20px">
      <div class="flex gap-12" style="align-items:center">
        <div style="width:44px;height:44px;border-radius:var(--radius-md);background:#fff;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0">
          @if($listing->feature_image)
            <img src="{{ asset('storage/images/'.$listing->feature_image) }}" style="width:36px;height:36px;object-fit:contain">
          @else
            <i class="fas fa-building" style="color:var(--primary)"></i>
          @endif
        </div>
        <div style="flex:1">
          <div class="fw-700" style="color:var(--secondary);font-size:15px">{{ $listing->title }}</div>
          <div class="fs-13 text-muted">
            {{ $listing->user->company_name ?? $listing->user->name }}
            &nbsp;·&nbsp; {{ $listing->address }}
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Token balance - FIXED display (không tự biến mất) --}}
  @php
    $tokenRecord = \App\Models\UserToken::where('user_id', auth()->id())->first();
    $balance = $tokenRecord?->balance ?? 0;
  @endphp
  <div class="card mb-16" style="border:1px solid {{ $balance > 0 ? 'var(--primary)' : 'var(--danger)' }};background:{{ $balance > 0 ? 'var(--primary-light)' : '#FFF2EE' }}">
    <div class="card-body" style="padding:14px 20px">
      <div class="flex-between">
        <div class="flex gap-12" style="align-items:center">
          <div style="width:40px;height:40px;border-radius:50%;background:{{ $balance > 0 ? 'var(--primary)' : 'var(--danger)' }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="fas fa-ticket-alt" style="color:#fff;font-size:16px"></i>
          </div>
          <div>
            <div class="fw-700" style="color:{{ $balance > 0 ? 'var(--primary-dark)' : 'var(--danger)' }};font-size:15px">
              {{ $balance }} lượt ứng tuyển còn lại
            </div>
            <div class="fs-12" style="color:{{ $balance > 0 ? 'var(--text-secondary)' : 'var(--danger)' }};margin-top:2px">
              @if($balance > 0)
                Ứng tuyển sẽ trừ <strong>1 lượt</strong>
              @else
                Bạn đã hết lượt — cần mua thêm để ứng tuyển
              @endif
            </div>
          </div>
        </div>
        @if($balance === 0)
          <a href="{{ route('payment.token') }}" class="btn btn-danger btn-sm">
            <i class="fas fa-plus fa-fw"></i> Mua ngay
          </a>
        @else
          <a href="{{ route('payment.token') }}" class="btn btn-outline btn-sm" style="border-color:var(--primary);color:var(--primary)">
            <i class="fas fa-shopping-cart fa-fw"></i> Mua thêm
          </a>
        @endif
      </div>
    </div>
  </div>

  @if(session('error'))
    <div class="alert alert-danger mb-16">{{ session('error') }}</div>
  @endif

  {{-- Form --}}
  <div class="card">
    <div class="card-header">
      <span class="fw-700 fs-16"><i class="fas fa-file-alt fa-fw" style="color:var(--primary)"></i> Nộp đơn ứng tuyển</span>
    </div>
    <div class="card-body">
      <form action="{{ route('apply.submit') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="listing_id" value="{{ $listingId }}">

        {{-- CV --}}
        <div class="form-group">
          <label class="form-label">CV của bạn <span class="text-danger">*</span></label>

          @if($suggestedCv)
            <div style="border:2px solid var(--primary);border-radius:var(--radius-md);padding:14px 16px;background:var(--primary-light);margin-bottom:12px">
              <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                <input type="radio" name="cv_source" value="existing" checked style="accent-color:var(--primary);width:16px;height:16px">
                <div>
                  <div class="fw-600 fs-13" style="color:var(--primary-dark)">
                    <i class="fas fa-check-circle fa-fw"></i> Dùng CV đã tải lên
                  </div>
                  <div class="fs-12 text-muted mt-8">{{ $suggestedCv->original_name }}</div>
                </div>
              </label>
              <input type="hidden" name="cv_id" value="{{ $suggestedCv->id }}">
            </div>
          @endif

          <div style="border:2px dashed var(--border);border-radius:var(--radius-md);padding:20px;background:var(--bg-gray)">
            @if($suggestedCv)
              <label style="display:flex;align-items:center;gap:10px;cursor:pointer;justify-content:center;margin-bottom:12px">
                <input type="radio" name="cv_source" value="new" style="accent-color:var(--primary);width:16px;height:16px">
                <span class="fw-600 fs-13"><i class="fas fa-upload fa-fw" style="color:var(--primary)"></i> Upload CV mới</span>
              </label>
            @else
              <div class="fw-600 fs-13 mb-8 text-center">
                <i class="fas fa-upload fa-fw" style="color:var(--primary)"></i> Upload CV
              </div>
            @endif
            <input type="file" name="cv_file" accept=".pdf,.doc,.docx" class="form-control" style="max-width:380px;margin:0 auto">
            <div class="fs-12 text-muted mt-8 text-center">Chấp nhận PDF, DOC, DOCX · Tối đa 5MB</div>
            @error('cv_file') <div class="text-danger fs-12 mt-8">{{ $message }}</div> @enderror
            @error('cv_id')   <div class="text-danger fs-12 mt-8">{{ $message }}</div> @enderror
          </div>

          {{-- CTA tạo CV online --}}
          <div style="margin-top:12px;padding:14px 16px;border-radius:var(--radius-md);background:linear-gradient(135deg,#1B2B4B,#2d4a7a);display:flex;align-items:center;justify-content:space-between;gap:12px">
            <div>
              <div class="fw-600" style="color:#fff;font-size:13px">
                <i class="fas fa-magic fa-fw" style="color:var(--pro-gold)"></i>
                Tạo CV chuyên nghiệp ngay trên hệ thống
              </div>
              <div class="fs-12" style="color:rgba(255,255,255,0.7);margin-top:3px">
                Miễn phí · Đẹp · Tải PDF ngay lập tức
              </div>
            </div>
            <a href="{{ route('user.cv.create') }}" target="_blank"
               style="background:var(--pro-gold);color:var(--pro-navy);padding:8px 14px;border-radius:var(--radius-md);font-size:12px;font-weight:700;white-space:nowrap;text-decoration:none;flex-shrink:0">
              Tạo CV Online <i class="fas fa-arrow-right fa-fw"></i>
            </a>
          </div>
        </div>

        {{-- Cover letter --}}
        <div class="form-group">
          <label class="form-label">Thư xin việc <span class="text-muted fs-12">(tùy chọn)</span></label>
          <textarea name="cover_letter" rows="5" maxlength="3000"
            placeholder="Giới thiệu bản thân và lý do bạn phù hợp với vị trí này..."
            class="form-control" style="resize:vertical">{{ old('cover_letter') }}</textarea>
          @error('cover_letter') <div class="text-danger fs-12 mt-8">{{ $message }}</div> @enderror
        </div>

        {{-- Điều khoản --}}
        <div class="form-group" style="margin-bottom:24px">
          <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer">
            <input type="checkbox" name="is_agreed_terms" value="1"
              style="margin-top:3px;accent-color:var(--primary);width:16px;height:16px"
              {{ old('is_agreed_terms') ? 'checked' : '' }}>
            <span class="fs-13" style="color:var(--text-body)">
              Tôi đồng ý với <a href="#" style="color:var(--primary)">điều khoản sử dụng</a>
              và xác nhận thông tin trong CV là chính xác. <span class="text-danger">*</span>
            </span>
          </label>
          @error('is_agreed_terms') <div class="text-danger fs-12 mt-8">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg"
          {{ $balance === 0 ? 'disabled' : '' }}
          style="{{ $balance === 0 ? 'opacity:.5;cursor:not-allowed' : '' }}">
          <i class="fas fa-paper-plane fa-fw"></i>
          {{ $balance === 0 ? 'Hết lượt ứng tuyển' : 'Nộp đơn ứng tuyển (tốn 1 lượt)' }}
        </button>
      </form>
    </div>
  </div>

  <div class="text-center mt-16">
    <a href="{{ url('/job/show/'.$listing->slug) }}" class="text-muted fs-13">
      <i class="fas fa-arrow-left fa-fw"></i> Quay lại chi tiết công việc
    </a>
  </div>

</div>
@endsection
