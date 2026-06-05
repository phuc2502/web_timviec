@extends('layouts.app')

@section('title', 'Ứng tuyển — ' . ($listing->title ?? 'Công việc'))

@section('content')
@php
  $tokenRecord = \App\Models\UserToken::where('user_id', auth()->id())->first();
  $balance     = $tokenRecord?->balance ?? 0;
@endphp

<style>
.apply-wrap { max-width: 680px; margin: 0 auto; padding: 24px 16px 48px; }
.apply-job-card { display:flex; gap:14px; align-items:center; background:#fff; border:1px solid var(--border); border-radius: var(--radius-lg); padding:18px 20px; margin-bottom:20px; box-shadow:var(--shadow-sm); }
.apply-job-logo { width:52px; height:52px; border-radius:var(--radius-md); border:1px solid var(--border); background:#fafafa; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.apply-token-bar { border-radius:var(--radius-md); padding:14px 18px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; gap:12px; }
.apply-token-bar.ok   { background:var(--primary-light); border:1.5px solid var(--primary); }
.apply-token-bar.empty{ background:#FFF2EE; border:1.5px solid var(--danger); }
.apply-card { background:#fff; border:1px solid var(--border); border-radius:var(--radius-xl); box-shadow:var(--shadow-sm); overflow:hidden; }
.apply-card-head { padding:18px 24px; border-bottom:1px solid var(--border); background:var(--bg-gray); display:flex; align-items:center; gap:10px; }
.apply-card-body { padding:24px; }
.cv-option { border:2px solid var(--border); border-radius:var(--radius-md); padding:14px 16px; cursor:pointer; transition:var(--transition); margin-bottom:10px; }
.cv-option:hover, .cv-option.selected { border-color:var(--primary); background:var(--primary-light); }
.cv-option label { cursor:pointer; display:flex; align-items:center; gap:12px; margin:0; }
.cv-upload-zone { border:2px dashed var(--border); border-radius:var(--radius-md); padding:22px; background:var(--bg-gray); text-align:center; transition:var(--transition); }
.cv-upload-zone:hover { border-color:var(--primary); background:var(--primary-light); }
.cv-cta { background:linear-gradient(135deg,#1B2B4B 0%,#2d4a7a 100%); border-radius:var(--radius-md); padding:14px 18px; display:flex; align-items:center; justify-content:space-between; gap:14px; margin-top:10px; }
.cv-cta a { background:var(--pro-gold); color:var(--pro-navy); padding:8px 16px; border-radius:var(--radius-md); font-size:12px; font-weight:700; white-space:nowrap; text-decoration:none; flex-shrink:0; }
.apply-textarea { width:100%; border:1.5px solid var(--border); border-radius:var(--radius-md); padding:12px 14px; font-size:14px; font-family:inherit; resize:vertical; min-height:120px; transition:var(--transition); color:var(--text-body); background:#fff; }
.apply-textarea:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(0,177,79,0.1); }
.apply-checkbox-row { display:flex; align-items:flex-start; gap:10px; padding:14px; background:var(--bg-gray); border-radius:var(--radius-md); }
.apply-checkbox-row input[type=checkbox] { margin-top:2px; width:16px; height:16px; accent-color:var(--primary); flex-shrink:0; }
.apply-submit { width:100%; padding:14px; font-size:15px; font-weight:700; border-radius:var(--radius-md); border:none; cursor:pointer; transition:var(--transition); display:flex; align-items:center; justify-content:center; gap:8px; }
.apply-submit.active { background:var(--primary); color:#fff; }
.apply-submit.active:hover { background:var(--primary-dark); transform:translateY(-1px); box-shadow:0 4px 12px rgba(0,177,79,0.3); }
.apply-submit.disabled { background:var(--border); color:var(--text-disabled); cursor:not-allowed; }
.form-label-styled { font-size:13px; font-weight:600; color:var(--text-dark); margin-bottom:8px; display:block; }
.step-num { width:24px; height:24px; border-radius:50%; background:var(--primary); color:#fff; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
</style>

<div class="apply-wrap">

  {{-- Breadcrumb --}}
  <div class="flex gap-6 mb-16" style="align-items:center;font-size:12px;color:var(--text-secondary)">
    <a href="{{ url('/job') }}" style="color:var(--text-secondary);text-decoration:none">Tìm việc</a>
    <i class="fas fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <a href="{{ url('/job/show/'.$listing->slug) }}" style="color:var(--text-secondary);text-decoration:none;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $listing->title }}</a>
    <i class="fas fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span style="color:var(--text-dark);font-weight:600">Ứng tuyển</span>
  </div>

  {{-- Job card --}}
  <div class="apply-job-card">
    <div class="apply-job-logo">
      @if($listing->feature_image)
        <img src="{{ asset('storage/images/'.$listing->feature_image) }}" style="width:40px;height:40px;object-fit:contain">
      @else
        <i class="fas fa-building" style="color:var(--primary);font-size:20px"></i>
      @endif
    </div>
    <div style="flex:1;min-width:0">
      <div class="fw-700" style="font-size:16px;color:var(--secondary)">{{ $listing->title }}</div>
      <div class="fs-13 text-muted mt-4">
        {{ $listing->user->company_name ?? $listing->user->name }}
        <span style="margin:0 6px;opacity:.4">·</span>
        <i class="fas fa-map-marker-alt fa-fw" style="font-size:11px"></i> {{ $listing->address }}
        <span style="margin:0 6px;opacity:.4">·</span>
        <i class="fas fa-clock fa-fw" style="font-size:11px"></i> {{ $listing->job_type }}
      </div>
    </div>
    <a href="{{ url('/job/show/'.$listing->slug) }}" class="btn btn-outline btn-sm" style="flex-shrink:0">
      <i class="fas fa-arrow-left fa-fw"></i> Quay lại
    </a>
  </div>

  {{-- Token balance (always visible, không tự biến mất) --}}
  <div class="apply-token-bar {{ $balance > 0 ? 'ok' : 'empty' }}">
    <div class="flex gap-12" style="align-items:center">
      <div style="width:38px;height:38px;border-radius:50%;background:{{ $balance > 0 ? 'var(--primary)' : 'var(--danger)' }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="fas fa-ticket-alt" style="color:#fff;font-size:15px"></i>
      </div>
      <div>
        <div class="fw-700 fs-14" style="color:{{ $balance > 0 ? 'var(--primary-dark)' : 'var(--danger)' }}">
          {{ $balance }} lượt ứng tuyển còn lại
        </div>
        <div class="fs-12" style="color:{{ $balance > 0 ? 'var(--text-secondary)' : 'var(--danger)' }};margin-top:2px">
          {{ $balance > 0 ? 'Ứng tuyển sẽ trừ 1 lượt từ tài khoản của bạn' : 'Hết lượt — mua thêm để tiếp tục nộp đơn' }}
        </div>
      </div>
    </div>
    @if($balance === 0)
      <a href="{{ route('payment.token') }}" class="btn btn-sm" style="background:var(--danger);color:#fff;flex-shrink:0">
        <i class="fas fa-plus fa-fw"></i> Mua ngay
      </a>
    @else
      <a href="{{ route('payment.token') }}" class="btn btn-outline btn-sm" style="border-color:var(--primary);color:var(--primary);flex-shrink:0">
        <i class="fas fa-shopping-cart fa-fw"></i> Mua thêm
      </a>
    @endif
  </div>

  @if(session('error'))
    <div class="alert alert-danger mb-16"><i class="fas fa-exclamation-circle fa-fw"></i> {{ session('error') }}</div>
  @endif

  {{-- Main form card --}}
  <div class="apply-card">
    <div class="apply-card-head">
      <div style="width:32px;height:32px;border-radius:var(--radius-sm);background:var(--primary);display:flex;align-items:center;justify-content:center">
        <i class="fas fa-file-alt" style="color:#fff;font-size:14px"></i>
      </div>
      <div>
        <div class="fw-700 fs-15">Nộp đơn ứng tuyển</div>
        <div class="fs-12 text-muted">Điền đầy đủ thông tin bên dưới để gửi hồ sơ</div>
      </div>
    </div>

    <div class="apply-card-body">
      <form action="{{ route('apply.submit') }}" method="POST" enctype="multipart/form-data" id="apply-form">
        @csrf
        <input type="hidden" name="listing_id" value="{{ $listingId }}">

        {{-- STEP 1: CV --}}
        <div style="margin-bottom:24px">
          <div class="flex gap-10 mb-12" style="align-items:center">
            <div class="step-num">1</div>
            <label class="form-label-styled" style="margin:0">Chọn CV của bạn <span class="text-danger">*</span></label>
          </div>

          @if($suggestedCv)
            {{-- Option A: CV có sẵn --}}
            <div class="cv-option selected" id="opt-existing" onclick="selectCvOption('existing')">
              <label style="cursor:pointer;display:flex;align-items:center;gap:12px;margin:0">
                <input type="radio" name="cv_source" value="existing" checked
                  style="width:16px;height:16px;accent-color:var(--primary);flex-shrink:0">
                <div style="width:36px;height:36px;border-radius:var(--radius-md);background:var(--primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                  <i class="fas fa-file-alt" style="color:var(--primary)"></i>
                </div>
                <div style="flex:1">
                  <div class="fw-600 fs-13" style="color:var(--primary-dark)">
                    <i class="fas fa-check-circle fa-fw" style="color:var(--primary)"></i>
                    Dùng CV đã tải lên
                  </div>
                  <div class="fs-12 text-muted mt-4">{{ $suggestedCv->original_name }}</div>
                </div>
              </label>
              <input type="hidden" name="cv_id" value="{{ $suggestedCv->id }}">
            </div>

            {{-- Option B: Upload mới --}}
            <div class="cv-option" id="opt-new" onclick="selectCvOption('new')">
              <label style="cursor:pointer;display:flex;align-items:center;gap:12px;margin:0;margin-bottom:0">
                <input type="radio" name="cv_source" value="new"
                  style="width:16px;height:16px;accent-color:var(--primary);flex-shrink:0">
                <div style="width:36px;height:36px;border-radius:var(--radius-md);background:var(--bg-gray);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                  <i class="fas fa-upload" style="color:var(--text-secondary)"></i>
                </div>
                <div>
                  <div class="fw-600 fs-13">Upload CV mới</div>
                  <div class="fs-12 text-muted mt-4">PDF, DOC, DOCX · Tối đa 5MB</div>
                </div>
              </label>
              <div id="upload-field" style="display:none;margin-top:12px;padding-top:12px;border-top:1px dashed var(--border)">
                <input type="file" name="cv_file" accept=".pdf,.doc,.docx" class="form-control" style="font-size:13px">
              </div>
            </div>
          @else
            {{-- Chưa có CV - chỉ upload --}}
            <div class="cv-upload-zone">
              <i class="fas fa-cloud-upload-alt fa-2x" style="color:var(--primary);margin-bottom:8px;display:block"></i>
              <div class="fw-600 fs-13 mb-4">Tải lên CV của bạn</div>
              <div class="text-muted fs-12 mb-12">PDF, DOC, DOCX · Tối đa 5MB</div>
              <input type="file" name="cv_file" accept=".pdf,.doc,.docx" class="form-control" style="max-width:320px;margin:0 auto;font-size:13px">
            </div>
          @endif

          @error('cv_file') <div class="text-danger fs-12 mt-8"><i class="fas fa-exclamation-circle fa-fw"></i> {{ $message }}</div> @enderror
          @error('cv_id')   <div class="text-danger fs-12 mt-8"><i class="fas fa-exclamation-circle fa-fw"></i> {{ $message }}</div> @enderror

          {{-- CTA: Tạo CV online --}}
          <div class="cv-cta">
            <div>
              <div class="fw-600 fs-13" style="color:#fff">
                <i class="fas fa-magic fa-fw" style="color:var(--pro-gold)"></i>
                Tạo CV chuyên nghiệp miễn phí
              </div>
              <div class="fs-12" style="color:rgba(255,255,255,0.6);margin-top:3px">
                Đẹp · Hiện đại · Xuất PDF ngay lập tức
              </div>
            </div>
            <a href="{{ route('user.cv.create') }}" target="_blank">
              Tạo CV Online <i class="fas fa-arrow-right fa-fw"></i>
            </a>
          </div>
        </div>

        {{-- STEP 2: Cover letter --}}
        <div style="margin-bottom:24px">
          <div class="flex gap-10 mb-12" style="align-items:center">
            <div class="step-num">2</div>
            <label class="form-label-styled" style="margin:0">
              Thư giới thiệu
              <span class="text-muted fs-12" style="font-weight:400"> (tùy chọn)</span>
            </label>
          </div>
          <textarea name="cover_letter" class="apply-textarea"
            placeholder="Viết vài dòng giới thiệu bản thân, kinh nghiệm liên quan và lý do bạn muốn ứng tuyển vị trí này...&#10;&#10;Ví dụ: Tôi là sinh viên năm 4 CNTT có kinh nghiệm 1 năm thực tập trong lĩnh vực..."
            maxlength="3000">{{ old('cover_letter') }}</textarea>
          <div class="fs-12 text-muted mt-6" style="text-align:right">Tối đa 3.000 ký tự</div>
          @error('cover_letter') <div class="text-danger fs-12 mt-8">{{ $message }}</div> @enderror
        </div>

        {{-- STEP 3: Terms --}}
        <div style="margin-bottom:24px">
          <div class="flex gap-10 mb-12" style="align-items:center">
            <div class="step-num">3</div>
            <label class="form-label-styled" style="margin:0">Xác nhận</label>
          </div>
          <div class="apply-checkbox-row">
            <input type="checkbox" name="is_agreed_terms" id="terms-cb" value="1" {{ old('is_agreed_terms') ? 'checked' : '' }}>
            <label for="terms-cb" class="fs-13" style="cursor:pointer;color:var(--text-body);line-height:1.5">
              Tôi xác nhận thông tin trong CV là chính xác và đồng ý với
              <a href="#" style="color:var(--primary)">Điều khoản dịch vụ</a> và
              <a href="#" style="color:var(--primary)">Chính sách bảo mật</a> của {{ config('app.name') }}.
              <span class="text-danger">*</span>
            </label>
          </div>
          @error('is_agreed_terms') <div class="text-danger fs-12 mt-8">{{ $message }}</div> @enderror
        </div>

        {{-- Submit button --}}
        <button type="submit" id="submit-btn"
          class="apply-submit {{ $balance > 0 ? 'active' : 'disabled' }}"
          {{ $balance === 0 ? 'disabled' : '' }}>
          @if($balance > 0)
            <i class="fas fa-paper-plane"></i> Nộp đơn ứng tuyển
            <span style="background:rgba(255,255,255,0.2);padding:2px 10px;border-radius:20px;font-size:12px">tốn 1 lượt</span>
          @else
            <i class="fas fa-lock"></i> Hết lượt ứng tuyển
          @endif
        </button>

      </form>
    </div>
  </div>

</div>

<script>
function selectCvOption(type) {
  const optExisting = document.getElementById('opt-existing');
  const optNew      = document.getElementById('opt-new');
  const uploadField = document.getElementById('upload-field');

  if (!optExisting || !optNew) return;

  if (type === 'existing') {
    optExisting.classList.add('selected');
    optNew.classList.remove('selected');
    if (uploadField) uploadField.style.display = 'none';
  } else {
    optNew.classList.add('selected');
    optExisting.classList.remove('selected');
    if (uploadField) uploadField.style.display = 'block';
  }
}

// Textarea auto-grow
document.querySelector('.apply-textarea')?.addEventListener('input', function() {
  this.style.height = 'auto';
  this.style.height = Math.max(120, this.scrollHeight) + 'px';
});
</script>
@endsection
