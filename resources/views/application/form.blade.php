@extends('layouts.app')

@section('title', 'Ứng tuyển — ' . ($listing->title ?? 'Công việc'))

@section('content')
@php
  $tokenRecord    = \App\Models\UserToken::where('user_id', auth()->id())->first();
  $balance        = $tokenRecord?->balance ?? 0;
  $isReapply      = isset($existingApp) && $existingApp !== null;
  $applyCount     = $applyCount     ?? 0;
  $maxRounds      = $maxRounds      ?? \App\Models\Application::MAX_APPLY_ROUNDS;
  $isStatusLocked = $isStatusLocked ?? false;
  $submitDisabled = $balance <= 0 || $isStatusLocked;
@endphp

<style>
.apply-wrap{max-width:700px;margin:0 auto;padding:24px 16px 56px}
.apply-job-card{display:flex;gap:14px;align-items:center;background:#fff;border:1px solid var(--border);border-radius:var(--radius-lg);padding:18px 20px;margin-bottom:20px;box-shadow:var(--shadow-sm)}
.apply-token-bar{border-radius:var(--radius-md);padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:12px}
.apply-token-bar.ok{background:var(--primary-light);border:1.5px solid var(--primary)}
.apply-token-bar.empty{background:#FFF2EE;border:1.5px solid var(--danger)}
.apply-card{background:#fff;border:1px solid var(--border);border-radius:var(--radius-xl);box-shadow:var(--shadow-sm);overflow:hidden}
.apply-card-head{padding:18px 24px;border-bottom:1px solid var(--border);background:var(--bg-gray);display:flex;align-items:center;gap:10px}
.apply-card-body{padding:28px}
.step-num{width:26px;height:26px;border-radius:50%;background:var(--primary);color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.form-label-req{font-size:13px;font-weight:600;color:var(--text-dark);margin-bottom:8px;display:block}
.field-input{width:100%;border:1.5px solid var(--border);border-radius:var(--radius-md);padding:11px 14px;font-size:14px;font-family:inherit;transition:var(--transition);background:#fff;color:var(--text-body);box-sizing:border-box}
.field-input:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(0,177,79,0.1)}
.field-input.is-error{border-color:#ef4444 !important;box-shadow:0 0 0 3px rgba(239,68,68,0.1) !important;background:#fff8f8}
.field-error{display:none;font-size:12px;color:#ef4444;margin-top:5px;align-items:center;gap:4px}
.field-error.show{display:flex}
.cv-option{border:2px solid var(--border);border-radius:var(--radius-md);padding:14px 16px;cursor:pointer;transition:var(--transition);margin-bottom:10px}
.cv-option:hover,.cv-option.selected{border-color:var(--primary);background:var(--primary-light)}
.cv-upload-zone{border:2px dashed var(--border);border-radius:var(--radius-md);padding:22px;background:var(--bg-gray);text-align:center;transition:var(--transition)}
.cv-upload-zone.is-error{border-color:#ef4444;background:#fff8f8}
.apply-textarea{width:100%;border:1.5px solid var(--border);border-radius:var(--radius-md);padding:12px 14px;font-size:14px;font-family:inherit;resize:vertical;min-height:110px;transition:var(--transition);color:var(--text-body);background:#fff;box-sizing:border-box}
.apply-textarea:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(0,177,79,0.1)}
.apply-checkbox-row{display:flex;align-items:flex-start;gap:10px;padding:14px;background:var(--bg-gray);border-radius:var(--radius-md);border:1.5px solid transparent;transition:var(--transition)}
.apply-checkbox-row.is-error{border-color:#ef4444;background:#fff8f8}
.apply-submit{width:100%;padding:14px;font-size:15px;font-weight:700;border-radius:var(--radius-md);border:none;cursor:pointer;transition:var(--transition);display:flex;align-items:center;justify-content:center;gap:8px}
.apply-submit.active{background:var(--primary);color:#fff}
.apply-submit.active:hover{background:var(--primary-dark);transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,177,79,.3)}
.apply-submit.disabled-btn{background:var(--border);color:var(--text-disabled);cursor:not-allowed}
.info-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px}
@media(max-width:560px){.info-row{grid-template-columns:1fr}}
.autofill-badge{display:inline-flex;align-items:center;gap:5px;background:#e0f2fe;color:#0369a1;font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px;margin-left:8px}
.section-divider{border:none;border-top:1px solid var(--border);margin:24px 0}
</style>

<div class="apply-wrap">

  {{-- Breadcrumb --}}
  <div class="flex gap-6 mb-16" style="align-items:center;font-size:12px;color:var(--text-secondary)">
    <a href="{{ url('/job') }}" style="color:var(--text-secondary);text-decoration:none">Tìm việc</a>
    <i class="fas fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <a href="{{ url('/job/show/'.$listing->slug) }}" style="color:var(--text-secondary);text-decoration:none;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $listing->title }}</a>
    <i class="fas fa-chevron-right" style="font-size:9px;opacity:.5"></i>
    <span style="color:var(--text-dark);font-weight:600">{{ $isReapply ? 'Cập nhật hồ sơ' : 'Ứng tuyển' }}</span>
  </div>

  {{-- Job card --}}
  <div class="apply-job-card">
    <div style="width:52px;height:52px;border-radius:var(--radius-md);border:1px solid var(--border);background:#fafafa;display:flex;align-items:center;justify-content:center;flex-shrink:0">
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
        <i class="fas fa-map-marker-alt" style="font-size:11px"></i> {{ $listing->address }}
        <span style="margin:0 6px;opacity:.4">·</span>
        <i class="fas fa-clock" style="font-size:11px"></i> {{ $listing->job_type }}
      </div>
    </div>
    <a href="{{ url('/job/show/'.$listing->slug) }}" class="btn btn-outline btn-sm" style="flex-shrink:0">
      <i class="fas fa-arrow-left"></i> Quay lại
    </a>
  </div>

  {{-- Reapply notice --}}
  {{-- Banner khoá: hồ sơ đã được NTD xử lý --}}
  @if($isStatusLocked)
  @php
    $lockedLabel = \App\Models\Application::STATUS_LABELS[$existingApp->status] ?? $existingApp->status;
  @endphp
  <div style="background:#fff1f2;border:1.5px solid #fca5a5;border-radius:10px;padding:14px 18px;margin-bottom:18px;display:flex;align-items:center;gap:12px">
    <i class="fas fa-lock" style="color:#dc2626;font-size:18px;flex-shrink:0"></i>
    <div class="fs-13" style="color:#7f1d1d">
      <strong>Không thể ứng tuyển lại.</strong>
      Hồ sơ của bạn đang ở trạng thái <strong>{{ $lockedLabel }}</strong> — nhà tuyển dụng đã xử lý đơn này.
      Bạn có thể <a href="{{ route('candidate.history') }}" style="color:#dc2626;font-weight:600">xem lịch sử ứng tuyển</a> để biết thêm.
    </div>
  </div>
  @endif

  @if($isReapply)
  @php
    $isSubmittedStatus = $existingApp && $existingApp->status === 'submitted';
    // Lần ứng tuyển tiếp theo: nếu status=submitted thì vẫn là round cũ (update), ngược lại +1
    $nextRound = $isSubmittedStatus ? $applyCount : $applyCount + 1;
  @endphp
  @if($isSubmittedStatus)
    {{-- Status = submitted: sẽ UPDATE bản ghi cũ --}}
    <div style="background:#f0f9ff;border:1.5px solid #38bdf8;border-radius:10px;padding:12px 16px;margin-bottom:18px;display:flex;align-items:center;gap:10px">
      <i class="fas fa-sync-alt" style="color:#0284c7;font-size:16px;flex-shrink:0"></i>
      <div class="fs-13" style="color:#0c4a6e">
        <strong>Cập nhật hồ sơ lần {{ $applyCount }}/{{ $maxRounds }}.</strong>
        Đơn của bạn đang ở trạng thái <strong>Đã nộp</strong> — thông tin bên dưới sẽ được cập nhật trực tiếp lên bản ghi hiện có, NTD sẽ thấy phiên bản mới nhất.
      </div>
    </div>
  @else
    {{-- Status khác submitted: sẽ TẠO BẢN GHI MỚI --}}
    <div style="background:#fff7ed;border:1.5px solid #fb923c;border-radius:10px;padding:12px 16px;margin-bottom:18px;display:flex;align-items:center;gap:10px">
      <i class="fas fa-plus-circle" style="color:#ea580c;font-size:16px;flex-shrink:0"></i>
      <div class="fs-13" style="color:#7c2d12">
        <strong>Ứng tuyển lại — lần {{ $nextRound }}/{{ $maxRounds }}.</strong>
        Hồ sơ hiện tại của bạn đã được xử lý. Nộp đơn mới sẽ tạo thêm 1 bản ghi — NTD sẽ thấy cả hai trong danh sách ứng viên.
      </div>
    </div>
  @endif
  @endif

  {{-- Token bar --}}
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
          {{ $isReapply ? 'Cập nhật lại không trừ lượt' : ($balance > 0 ? 'Nộp đơn sẽ trừ 1 lượt' : 'Hết lượt — mua thêm để tiếp tục') }}
        </div>
      </div>
    </div>
    @if($balance === 0 && !$isReapply)
      <a href="{{ route('payment.token') }}" class="btn btn-sm" style="background:var(--danger);color:#fff;flex-shrink:0">
        <i class="fas fa-plus"></i> Mua ngay
      </a>
    @else
      <a href="{{ route('payment.token') }}" class="btn btn-outline btn-sm" style="border-color:var(--primary);color:var(--primary);flex-shrink:0">
        <i class="fas fa-shopping-cart"></i> Mua thêm
      </a>
    @endif
  </div>

  @if(session('error'))
    <div class="alert alert-danger mb-16"><i class="fas fa-exclamation-circle fa-fw"></i> {{ session('error') }}</div>
  @endif

  {{-- Main form --}}
  <div class="apply-card">
    <div class="apply-card-head">
      <div style="width:32px;height:32px;border-radius:var(--radius-sm);background:var(--primary);display:flex;align-items:center;justify-content:center">
        <i class="fas fa-file-alt" style="color:#fff;font-size:14px"></i>
      </div>
      <div>
        <div class="fw-700 fs-15">{{ $isReapply ? 'Cập nhật hồ sơ ứng tuyển' : 'Nộp đơn ứng tuyển' }}</div>
        <div class="fs-12 text-muted">Điền đầy đủ thông tin để gửi hồ sơ tốt nhất đến nhà tuyển dụng</div>
      </div>
    </div>

    <div class="apply-card-body">
      <form action="{{ route('apply.submit') }}" method="POST" enctype="multipart/form-data" id="apply-form" novalidate>
        @csrf
        <input type="hidden" name="listing_id" value="{{ $listingId }}">

        {{-- ── STEP 1: Thông tin cá nhân ─────────────────────────────── --}}
        <div style="margin-bottom:24px">
          <div class="flex gap-10 mb-16" style="align-items:center">
            <div class="step-num">1</div>
            <div>
              <div class="fw-700 fs-14">Thông tin liên hệ</div>
              <div class="fs-12 text-muted">
                Được tự động điền từ lần ứng tuyển gần nhất
                <span class="autofill-badge"><i class="fas fa-magic"></i> Autofill</span>
              </div>
            </div>
          </div>

          <div class="info-row">
            {{-- Họ tên --}}
            <div>
              <label class="form-label-req" for="f-name">
                Họ và tên <span class="text-danger">*</span>
              </label>
              <input type="text" id="f-name" name="fullname" data-validate="required"
                class="field-input {{ $errors->has('fullname') ? 'is-error' : '' }}"
                value="{{ old('fullname', $autofill['name'] ?? '') }}"
                placeholder="Nguyễn Văn A" autocomplete="name">
              <div class="field-error {{ $errors->has('fullname') ? 'show' : '' }}" id="err-name">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ $errors->first('fullname') ?: 'Vui lòng nhập họ và tên của bạn' }}</span>
              </div>
            </div>

            {{-- Số điện thoại --}}
            <div>
              <label class="form-label-req" for="f-phone">
                Số điện thoại <span class="text-danger">*</span>
              </label>
              <input type="tel" id="f-phone" name="phone" data-validate="required|phone"
                class="field-input {{ $errors->has('phone') ? 'is-error' : '' }}"
                value="{{ old('phone', $autofill['phone'] ?? '') }}"
                placeholder="0912 345 678" autocomplete="tel"
                inputmode="numeric"
                onkeydown="return /[\d\s\+\-]/.test(event.key) || ['Backspace','Delete','ArrowLeft','ArrowRight','Tab','Home','End'].includes(event.key)"
                oninput="this.value=this.value.replace(/[^\d\s\+\-]/g,'')">
              <div class="field-error {{ $errors->has('phone') ? 'show' : '' }}" id="err-phone">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ $errors->first('phone') ?: 'Vui lòng nhập số điện thoại hợp lệ (10–11 chữ số)' }}</span>
              </div>
            </div>
          </div>

          {{-- Email --}}
          <div style="margin-bottom:0">
            <label class="form-label-req" for="f-email">
              Email liên hệ <span class="text-danger">*</span>
            </label>
            <input type="email" id="f-email" name="email" data-validate="required|email"
              class="field-input {{ $errors->has('email') ? 'is-error' : '' }}"
              value="{{ old('email', $autofill['email'] ?? '') }}"
              placeholder="example@email.com" autocomplete="email">
            <div class="field-error {{ $errors->has('email') ? 'show' : '' }}" id="err-email">
              <i class="fas fa-exclamation-circle"></i>
              <span>{{ $errors->first('email') ?: 'Địa chỉ email không hợp lệ. Vui lòng kiểm tra lại' }}</span>
            </div>
          </div>
        </div>

        <hr class="section-divider">

        {{-- ── STEP 2: CV ────────────────────────────────────────────── --}}
        <div style="margin-bottom:24px">
          <div class="flex gap-10 mb-14" style="align-items:center">
            <div class="step-num">2</div>
            <div>
              <div class="fw-700 fs-14">Chọn CV <span class="text-danger">*</span></div>
              <div class="fs-12 text-muted">Sử dụng CV gần nhất hoặc tải lên file mới</div>
            </div>
          </div>

          @if($suggestedCv)
            {{-- Option A: CV gần nhất --}}
            <div class="cv-option selected" id="opt-existing" onclick="selectCvOption('existing')">
              <label style="cursor:pointer;display:flex;align-items:center;gap:12px;margin:0">
                <input type="radio" name="cv_source" value="existing" checked
                  style="width:16px;height:16px;accent-color:var(--primary);flex-shrink:0">
                <div style="width:36px;height:36px;border-radius:var(--radius-md);background:var(--primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                  <i class="fas fa-file-alt" style="color:var(--primary)"></i>
                </div>
                <div style="flex:1;min-width:0">
                  <div class="fw-600 fs-13" style="color:var(--primary-dark)">
                    <i class="fas fa-check-circle"></i>
                    @if($isReapply && $existingApp?->cv_id === $suggestedCv->id)
                      CV đã nộp trước đó
                    @else
                      CV gần nhất của bạn
                    @endif
                  </div>
                  <div class="fs-12 text-muted mt-4" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    <i class="fas fa-paperclip fa-fw"></i> {{ $suggestedCv->original_name }}
                  </div>
                </div>
              </label>
              <input type="hidden" name="cv_id" id="existing-cv-id" value="{{ $suggestedCv->id }}">
            </div>

            {{-- Option B: Upload mới --}}
            <div class="cv-option" id="opt-new" onclick="selectCvOption('new')">
              <label style="cursor:pointer;display:flex;align-items:center;gap:12px;margin:0">
                <input type="radio" name="cv_source" value="new"
                  style="width:16px;height:16px;accent-color:var(--primary);flex-shrink:0">
                <div style="width:36px;height:36px;border-radius:var(--radius-md);background:var(--bg-gray);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                  <i class="fas fa-upload" style="color:var(--text-secondary)"></i>
                </div>
                <div>
                  <div class="fw-600 fs-13">Tải lên CV mới</div>
                  <div class="fs-12 text-muted mt-4">PDF, DOC, DOCX · Tối đa 5MB</div>
                </div>
              </label>
              <div id="upload-field" style="display:none;margin-top:12px;padding-top:12px;border-top:1px dashed var(--border)">
                <label for="f-cv-file" id="cv-drop-zone"
                  style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;border:2px dashed var(--border);border-radius:10px;padding:20px;cursor:pointer;transition:var(--transition);text-align:center;background:var(--bg-gray)">
                  <i class="fas fa-cloud-upload-alt" style="font-size:26px;color:var(--primary)"></i>
                  <div class="fw-600 fs-13">Nhấn để chọn file hoặc kéo thả vào đây</div>
                  <div id="cv-filename" class="fs-12" style="color:var(--text-secondary)">PDF, DOC, DOCX · Tối đa 5MB</div>
                </label>
                <input type="file" id="f-cv-file" name="cv_file" accept=".pdf,.doc,.docx"
                  style="display:none" onchange="validateCvFile(this)">
                <div class="field-error" id="err-cv" style="margin-top:8px">
                  <i class="fas fa-exclamation-circle"></i>
                  <span id="err-cv-msg">Vui lòng tải lên file CV (PDF, DOC hoặc DOCX, tối đa 5MB)</span>
                </div>
              </div>
            </div>
          @else
            {{-- Chưa có CV nào --}}
            <label for="f-cv-file" id="cv-upload-zone"
              style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;border:2px dashed var(--border);border-radius:12px;padding:28px;cursor:pointer;transition:var(--transition);text-align:center;background:var(--bg-gray)">
              <i class="fas fa-cloud-upload-alt fa-2x" style="color:var(--primary)"></i>
              <div class="fw-600 fs-13">Nhấn để chọn file hoặc kéo thả vào đây</div>
              <div id="cv-filename" class="fs-12" style="color:var(--text-secondary)">PDF, DOC, DOCX · Tối đa 5MB</div>
            </label>
            <input type="file" id="f-cv-file" name="cv_file" accept=".pdf,.doc,.docx"
              style="display:none" onchange="validateCvFile(this)">
            <div class="field-error" id="err-cv" style="margin-top:8px">
              <i class="fas fa-exclamation-circle"></i>
              <span id="err-cv-msg">Vui lòng tải lên file CV của bạn (PDF, DOC hoặc DOCX, tối đa 5MB)</span>
            </div>
          @endif

          @error('cv_file')<div class="field-error show" style="margin-top:6px"><i class="fas fa-exclamation-circle"></i><span>{{ $message }}</span></div>@enderror
          @error('cv_id')<div class="field-error show" style="margin-top:6px"><i class="fas fa-exclamation-circle"></i><span>{{ $message }}</span></div>@enderror

          {{-- CTA tạo CV online --}}
          <div style="background:linear-gradient(135deg,#1B2B4B,#2d4a7a);border-radius:var(--radius-md);padding:12px 16px;display:flex;align-items:center;justify-content:space-between;gap:14px;margin-top:12px">
            <div>
              <div class="fw-600 fs-13" style="color:#fff"><i class="fas fa-magic fa-fw" style="color:#f59e0b"></i> Tạo CV chuyên nghiệp miễn phí</div>
              <div class="fs-12" style="color:rgba(255,255,255,.6);margin-top:3px">Đẹp · Hiện đại · Xuất PDF ngay lập tức</div>
            </div>
            <a href="{{ route('user.cv.create') }}" target="_blank" style="background:#f59e0b;color:#1B2B4B;padding:8px 14px;border-radius:var(--radius-md);font-size:12px;font-weight:700;white-space:nowrap;text-decoration:none;flex-shrink:0">
              Tạo CV Online <i class="fas fa-arrow-right"></i>
            </a>
          </div>
        </div>

        <hr class="section-divider">

        {{-- ── STEP 3: Cover letter ───────────────────────────────────── --}}
        <div style="margin-bottom:24px">
          <div class="flex gap-10 mb-12" style="align-items:center">
            <div class="step-num">3</div>
            <div>
              <div class="fw-700 fs-14">Thư giới thiệu <span class="text-muted fs-12" style="font-weight:400">(tùy chọn)</span></div>
              <div class="fs-12 text-muted">Một đoạn giới thiệu ngắn sẽ giúp bạn nổi bật hơn</div>
            </div>
          </div>
          <textarea name="cover_letter" id="f-cover" class="apply-textarea"
            placeholder="Giới thiệu bản thân, kinh nghiệm liên quan và lý do bạn muốn ứng tuyển vị trí này..."
            maxlength="3000">{{ old('cover_letter', $autofill['cover_letter'] ?? '') }}</textarea>
          <div class="flex-between mt-6">
            <div></div>
            <div class="fs-12 text-muted"><span id="cover-count">0</span>/3.000 ký tự</div>
          </div>
          @error('cover_letter')<div class="field-error show mt-6"><i class="fas fa-exclamation-circle"></i><span>{{ $message }}</span></div>@enderror
        </div>

        <hr class="section-divider">

        {{-- ── STEP 4: Xác nhận ──────────────────────────────────────── --}}
        <div style="margin-bottom:24px">
          <div class="flex gap-10 mb-12" style="align-items:center">
            <div class="step-num">4</div>
            <label class="fw-700 fs-14" style="margin:0">Xác nhận điều khoản</label>
          </div>
          <div class="apply-checkbox-row" id="terms-row">
            <input type="checkbox" name="is_agreed_terms" id="terms-cb" value="1"
              {{ old('is_agreed_terms') ? 'checked' : '' }}
              style="margin-top:2px;width:16px;height:16px;accent-color:var(--primary);flex-shrink:0">
            <label for="terms-cb" class="fs-13" style="cursor:pointer;color:var(--text-body);line-height:1.6">
              Tôi xác nhận thông tin trong CV là chính xác và đồng ý với
              <a href="#" onclick="openModal('terms-modal');return false;" style="color:var(--primary);font-weight:600">Điều khoản dịch vụ</a> và
              <a href="#" onclick="openModal('policy-modal');return false;" style="color:var(--primary);font-weight:600">Chính sách bảo mật</a> của {{ config('app.name') }}.
              <span class="text-danger">*</span>
            </label>
          </div>
          <div class="field-error" id="err-terms" style="margin-top:6px">
            <i class="fas fa-exclamation-circle"></i>
            <span>Vui lòng đồng ý với điều khoản dịch vụ để tiếp tục</span>
          </div>
          @error('is_agreed_terms')<div class="field-error show mt-6"><i class="fas fa-exclamation-circle"></i><span>{{ $message }}</span></div>@enderror
        </div>

        {{-- Submit --}}
        @if($balance > 0 || $isReapply)
          <button type="submit" id="submit-btn" class="apply-submit active">
            <i class="fas fa-paper-plane"></i>
            {{ $isReapply ? 'Cập nhật hồ sơ' : 'Nộp đơn ứng tuyển' }}
            <span style="background:rgba(255,255,255,.2);padding:2px 10px;border-radius:20px;font-size:12px">tốn 1 lượt</span>
          </button>
        @else
          <button type="button" class="apply-submit disabled-btn">
            <i class="fas fa-lock"></i> Hết lượt ứng tuyển — Mua thêm để tiếp tục
          </button>
        @endif

      </form>
    </div>
  </div>

</div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Điều khoản dịch vụ                                         --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<div id="terms-modal" style="display:none;position:fixed;inset:0;z-index:10000;background:rgba(0,0,0,0.55);align-items:center;justify-content:center;padding:16px">
  <div style="background:#fff;border-radius:16px;max-width:680px;width:100%;max-height:85vh;display:flex;flex-direction:column;box-shadow:0 24px 64px rgba(0,0,0,0.2)">
    <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
      <div class="fw-700 fs-17" style="color:var(--secondary)"><i class="fas fa-file-contract fa-fw" style="color:var(--primary)"></i> Điều khoản dịch vụ</div>
      <button onclick="closeModal('terms-modal')" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--text-secondary);line-height:1">&times;</button>
    </div>
    <div style="padding:24px;overflow-y:auto;font-size:13.5px;line-height:1.85;color:var(--text-body)">
      <p style="color:var(--text-secondary);font-size:12px;margin-bottom:20px">Cập nhật lần cuối: 01/01/2025 · Áp dụng cho tất cả người dùng {{ config('app.name') }}</p>

      <h3 style="font-size:15px;color:var(--secondary);margin:0 0 10px">1. Chấp thuận điều khoản</h3>
      <p>Bằng cách truy cập và sử dụng {{ config('app.name') }}, bạn xác nhận đã đọc, hiểu và đồng ý bị ràng buộc bởi các Điều khoản dịch vụ này. Nếu bạn không đồng ý với bất kỳ điều khoản nào, vui lòng không sử dụng dịch vụ.</p>

      <h3 style="font-size:15px;color:var(--secondary);margin:20px 0 10px">2. Tài khoản người dùng</h3>
      <p>Bạn chịu trách nhiệm duy trì tính bảo mật của tài khoản và mật khẩu. {{ config('app.name') }} không chịu trách nhiệm về bất kỳ thiệt hại nào phát sinh từ việc bạn không bảo vệ thông tin đăng nhập. Bạn phải thông báo ngay cho chúng tôi khi phát hiện bất kỳ hành vi sử dụng trái phép nào.</p>

      <h3 style="font-size:15px;color:var(--secondary);margin:20px 0 10px">3. Quy định ứng tuyển</h3>
      <ul style="padding-left:20px;margin:0 0 12px">
        <li style="margin-bottom:8px">Ứng viên chỉ được nộp hồ sơ khi thực sự có nhu cầu ứng tuyển vào vị trí đó.</li>
        <li style="margin-bottom:8px">Thông tin trong CV và hồ sơ phải trung thực, chính xác. Mọi hành vi gian lận sẽ bị xử lý theo quy định.</li>
        <li style="margin-bottom:8px">Mỗi lần ứng tuyển (bao gồm ứng tuyển lại) sẽ tiêu tốn 1 lượt ứng tuyển trong tài khoản.</li>
        <li style="margin-bottom:8px">{{ config('app.name') }} không đảm bảo kết quả tuyển dụng và không chịu trách nhiệm về quyết định của nhà tuyển dụng.</li>
      </ul>

      <h3 style="font-size:15px;color:var(--secondary);margin:20px 0 10px">4. Nội dung bị cấm</h3>
      <p>Người dùng không được đăng tải hoặc cung cấp nội dung vi phạm pháp luật, gây hại cho người khác, chứa thông tin sai lệch hoặc có tính chất lừa đảo, xúc phạm danh dự cá nhân hoặc tổ chức.</p>

      <h3 style="font-size:15px;color:var(--secondary);margin:20px 0 10px">5. Thanh toán và hoàn tiền</h3>
      <p>Các giao dịch mua lượt ứng tuyển và gói Premium đã hoàn tất sẽ không được hoàn trả, trừ trường hợp lỗi kỹ thuật từ phía {{ config('app.name') }}. Mọi khiếu nại cần được gửi trong vòng 7 ngày kể từ ngày giao dịch.</p>

      <h3 style="font-size:15px;color:var(--secondary);margin:20px 0 10px">6. Giới hạn trách nhiệm</h3>
      <p>{{ config('app.name') }} không chịu trách nhiệm về bất kỳ thiệt hại gián tiếp, đặc biệt hoặc hậu quả nào phát sinh từ việc sử dụng hoặc không thể sử dụng dịch vụ, bao gồm nhưng không giới hạn ở mất việc làm, mất doanh thu.</p>

      <h3 style="font-size:15px;color:var(--secondary);margin:20px 0 10px">7. Thay đổi điều khoản</h3>
      <p>{{ config('app.name') }} có quyền sửa đổi các Điều khoản này vào bất kỳ lúc nào. Phiên bản cập nhật sẽ có hiệu lực ngay khi được đăng tải. Việc tiếp tục sử dụng dịch vụ đồng nghĩa với việc bạn chấp nhận các điều khoản mới.</p>

      <h3 style="font-size:15px;color:var(--secondary);margin:20px 0 10px">8. Liên hệ</h3>
      <p>Mọi thắc mắc về Điều khoản dịch vụ, vui lòng liên hệ: <strong>support@{{ strtolower(str_replace(' ', '', config('app.name'))) }}.vn</strong></p>
    </div>
    <div style="padding:16px 24px;border-top:1px solid var(--border);flex-shrink:0;text-align:right">
      <button onclick="agreeAndClose('terms-modal')" class="btn btn-primary" style="min-width:140px">
        <i class="fas fa-check fa-fw"></i> Đã đọc & Đồng ý
      </button>
    </div>
  </div>
</div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Chính sách bảo mật                                        --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
<div id="policy-modal" style="display:none;position:fixed;inset:0;z-index:10000;background:rgba(0,0,0,0.55);align-items:center;justify-content:center;padding:16px">
  <div style="background:#fff;border-radius:16px;max-width:680px;width:100%;max-height:85vh;display:flex;flex-direction:column;box-shadow:0 24px 64px rgba(0,0,0,0.2)">
    <div style="padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
      <div class="fw-700 fs-17" style="color:var(--secondary)"><i class="fas fa-shield-alt fa-fw" style="color:var(--primary)"></i> Chính sách bảo mật</div>
      <button onclick="closeModal('policy-modal')" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--text-secondary);line-height:1">&times;</button>
    </div>
    <div style="padding:24px;overflow-y:auto;font-size:13.5px;line-height:1.85;color:var(--text-body)">
      <p style="color:var(--text-secondary);font-size:12px;margin-bottom:20px">Cập nhật lần cuối: 01/01/2025 · Tuân thủ Nghị định 13/2023/NĐ-CP về bảo vệ dữ liệu cá nhân</p>

      <h3 style="font-size:15px;color:var(--secondary);margin:0 0 10px">1. Thông tin chúng tôi thu thập</h3>
      <p>Khi bạn sử dụng {{ config('app.name') }}, chúng tôi thu thập các thông tin sau:</p>
      <ul style="padding-left:20px;margin:8px 0 12px">
        <li style="margin-bottom:6px"><strong>Thông tin cá nhân:</strong> họ tên, địa chỉ email, số điện thoại, địa chỉ.</li>
        <li style="margin-bottom:6px"><strong>Thông tin nghề nghiệp:</strong> CV, kinh nghiệm làm việc, trình độ học vấn, kỹ năng.</li>
        <li style="margin-bottom:6px"><strong>Dữ liệu sử dụng:</strong> lịch sử ứng tuyển, tin đã xem, hành vi trên nền tảng.</li>
        <li style="margin-bottom:6px"><strong>Thông tin kỹ thuật:</strong> địa chỉ IP, loại trình duyệt, thiết bị truy cập.</li>
      </ul>

      <h3 style="font-size:15px;color:var(--secondary);margin:20px 0 10px">2. Mục đích sử dụng thông tin</h3>
      <p>Thông tin của bạn được sử dụng để:</p>
      <ul style="padding-left:20px;margin:8px 0 12px">
        <li style="margin-bottom:6px">Kết nối ứng viên với nhà tuyển dụng phù hợp.</li>
        <li style="margin-bottom:6px">Gửi thông báo về trạng thái hồ sơ và cơ hội việc làm phù hợp.</li>
        <li style="margin-bottom:6px">Cải thiện trải nghiệm người dùng và phát triển tính năng mới.</li>
        <li style="margin-bottom:6px">Xử lý thanh toán và quản lý tài khoản.</li>
        <li style="margin-bottom:6px">Tuân thủ nghĩa vụ pháp lý theo quy định của pháp luật Việt Nam.</li>
      </ul>

      <h3 style="font-size:15px;color:var(--secondary);margin:20px 0 10px">3. Chia sẻ thông tin</h3>
      <p>{{ config('app.name') }} <strong>không bán</strong> thông tin cá nhân của bạn cho bên thứ ba. Chúng tôi chỉ chia sẻ thông tin trong các trường hợp:</p>
      <ul style="padding-left:20px;margin:8px 0 12px">
        <li style="margin-bottom:6px">Với nhà tuyển dụng khi bạn chủ động nộp hồ sơ ứng tuyển.</li>
        <li style="margin-bottom:6px">Với đối tác cung cấp dịch vụ kỹ thuật (lưu trữ, xử lý thanh toán) với cam kết bảo mật.</li>
        <li style="margin-bottom:6px">Khi có yêu cầu của cơ quan nhà nước có thẩm quyền theo quy định pháp luật.</li>
      </ul>

      <h3 style="font-size:15px;color:var(--secondary);margin:20px 0 10px">4. Bảo mật dữ liệu</h3>
      <p>Chúng tôi áp dụng các biện pháp bảo mật kỹ thuật và tổ chức phù hợp bao gồm mã hóa SSL/TLS, kiểm soát truy cập nghiêm ngặt và sao lưu dữ liệu định kỳ. Tuy nhiên, không có phương thức truyền dữ liệu qua internet nào là an toàn tuyệt đối.</p>

      <h3 style="font-size:15px;color:var(--secondary);margin:20px 0 10px">5. Quyền của bạn</h3>
      <p>Theo Nghị định 13/2023/NĐ-CP, bạn có quyền:</p>
      <ul style="padding-left:20px;margin:8px 0 12px">
        <li style="margin-bottom:6px"><strong>Truy cập:</strong> yêu cầu xem thông tin cá nhân chúng tôi đang lưu trữ.</li>
        <li style="margin-bottom:6px"><strong>Chỉnh sửa:</strong> cập nhật thông tin không chính xác.</li>
        <li style="margin-bottom:6px"><strong>Xóa:</strong> yêu cầu xóa tài khoản và dữ liệu liên quan.</li>
        <li style="margin-bottom:6px"><strong>Phản đối:</strong> từ chối một số hình thức xử lý dữ liệu nhất định.</li>
      </ul>

      <h3 style="font-size:15px;color:var(--secondary);margin:20px 0 10px">6. Cookie</h3>
      <p>Chúng tôi sử dụng cookie để duy trì phiên đăng nhập và cải thiện trải nghiệm người dùng. Bạn có thể tắt cookie trong cài đặt trình duyệt, tuy nhiên điều này có thể ảnh hưởng đến một số tính năng của trang.</p>

      <h3 style="font-size:15px;color:var(--secondary);margin:20px 0 10px">7. Liên hệ</h3>
      <p>Mọi yêu cầu về quyền riêng tư hoặc khiếu nại liên quan đến dữ liệu cá nhân, vui lòng liên hệ:<br>
      <strong>Email:</strong> privacy@{{ strtolower(str_replace(' ', '', config('app.name'))) }}.vn<br>
      <strong>Địa chỉ:</strong> Việt Nam</p>
    </div>
    <div style="padding:16px 24px;border-top:1px solid var(--border);flex-shrink:0;text-align:right">
      <button onclick="agreeAndClose('policy-modal')" class="btn btn-primary" style="min-width:140px">
        <i class="fas fa-check fa-fw"></i> Đã đọc & Đồng ý
      </button>
    </div>
  </div>
</div>

<script>
// ── CV option toggle ──────────────────────────────────────────────────────
function selectCvOption(type) {
  const optExisting   = document.getElementById('opt-existing');
  const optNew        = document.getElementById('opt-new');
  const uploadField   = document.getElementById('upload-field');
  const existingCvId  = document.getElementById('existing-cv-id');
  const cvFileInput   = document.getElementById('f-cv-file');
  if (!optExisting || !optNew) return;
  if (type === 'existing') {
    optExisting.classList.add('selected');
    optNew.classList.remove('selected');
    if (uploadField)  uploadField.style.display = 'none';
    // Enable cv_id, disable file upload
    if (existingCvId) existingCvId.disabled = false;
    if (cvFileInput)  { cvFileInput.disabled = true; cvFileInput.value = ''; }
  } else {
    optNew.classList.add('selected');
    optExisting.classList.remove('selected');
    if (uploadField) uploadField.style.display = 'block';
    // Disable cv_id, enable file upload
    if (existingCvId) existingCvId.disabled = true;
    if (cvFileInput)  cvFileInput.disabled = false;
  }
}

// ── Cover letter char count ───────────────────────────────────────────────
const coverTA = document.getElementById('f-cover');
const coverCount = document.getElementById('cover-count');
if (coverTA && coverCount) {
  const updateCount = () => { coverCount.textContent = coverTA.value.length; };
  coverTA.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.max(110, this.scrollHeight) + 'px';
    updateCount();
  });
  updateCount();
}

// ── Custom validation ─────────────────────────────────────────────────────
function showError(inputEl, errorEl) {
  inputEl.classList.add('is-error');
  errorEl.classList.add('show');
}
function clearError(inputEl, errorEl) {
  inputEl.classList.remove('is-error');
  errorEl.classList.remove('show');
}

document.getElementById('apply-form').addEventListener('submit', function(e) {
  let hasError = false;

  // Họ tên
  const nameEl  = document.getElementById('f-name');
  const errName = document.getElementById('err-name');
  if (nameEl) {
    if (!nameEl.value.trim() || nameEl.value.trim().length < 2) {
      showError(nameEl, errName); hasError = true;
    } else { clearError(nameEl, errName); }
  }

  // Số điện thoại
  const phoneEl  = document.getElementById('f-phone');
  const errPhone = document.getElementById('err-phone');
  if (phoneEl) {
    const phoneVal = phoneEl.value.replace(/\s/g, '');
    if (!phoneVal || !/^(0|\+84)[0-9]{9,10}$/.test(phoneVal)) {
      showError(phoneEl, errPhone); hasError = true;
    } else { clearError(phoneEl, errPhone); }
  }

  // Email
  const emailEl  = document.getElementById('f-email');
  const errEmail = document.getElementById('err-email');
  if (emailEl) {
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailEl.value.trim() || !emailRe.test(emailEl.value.trim())) {
      showError(emailEl, errEmail); hasError = true;
    } else { clearError(emailEl, errEmail); }
  }

  // CV: chỉ validate khi không có CV gần nhất (phải upload)
  const cvFileEl  = document.getElementById('f-cv-file');
  const errCv     = document.getElementById('err-cv');
  const optNew    = document.getElementById('opt-new');
  const uploadZone = document.getElementById('cv-upload-zone');
  if (cvFileEl && errCv) {
    const needUpload = (optNew && optNew.classList.contains('selected')) || uploadZone;
    if (needUpload && cvFileEl.files.length === 0) {
      errCv.classList.add('show');
      if (uploadZone) uploadZone.classList.add('is-error');
      hasError = true;
    } else {
      errCv.classList.remove('show');
      if (uploadZone) uploadZone.classList.remove('is-error');
    }
  }

  // Điều khoản
  const termsCb  = document.getElementById('terms-cb');
  const errTerms = document.getElementById('err-terms');
  const termsRow = document.getElementById('terms-row');
  if (termsCb && !termsCb.checked) {
    errTerms.classList.add('show');
    if (termsRow) termsRow.classList.add('is-error');
    hasError = true;
  } else if (termsCb) {
    errTerms.classList.remove('show');
    if (termsRow) termsRow.classList.remove('is-error');
  }

  if (hasError) {
    e.preventDefault();
    // Scroll đến lỗi đầu tiên
    const firstError = document.querySelector('.is-error, .apply-checkbox-row.is-error');
    if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
});

// Xóa error khi user nhập lại
['f-name','f-phone','f-email'].forEach(id => {
  const el = document.getElementById(id);
  if (!el) return;
  const errId = { 'f-name': 'err-name', 'f-phone': 'err-phone', 'f-email': 'err-email' }[id];
  el.addEventListener('input', () => {
    el.classList.remove('is-error');
    document.getElementById(errId)?.classList.remove('show');
  });
});
document.getElementById('terms-cb')?.addEventListener('change', function() {
  document.getElementById('err-terms')?.classList.remove('show');
  document.getElementById('terms-row')?.classList.remove('is-error');
});

// ── Realtime CV file validation ───────────────────────────────────────────
function validateCvFile(input) {
  const maxSize   = 5 * 1024 * 1024; // 5MB
  const allowed   = ['pdf', 'doc', 'docx'];
  const errEl     = document.getElementById('err-cv');
  const errMsgEl  = document.getElementById('err-cv-msg');
  const filenameEl = document.getElementById('cv-filename');
  const dropZone  = document.getElementById('cv-drop-zone') || document.getElementById('cv-upload-zone');

  if (input.files.length === 0) return;

  const file = input.files[0];
  const ext  = file.name.split('.').pop().toLowerCase();

  // Xóa lỗi cũ trước
  errEl?.classList.remove('show');
  if (dropZone) dropZone.style.borderColor = 'var(--border)';

  if (!allowed.includes(ext)) {
    if (errMsgEl) errMsgEl.textContent = 'Định dạng file không hợp lệ. Chỉ chấp nhận PDF, DOC hoặc DOCX.';
    errEl?.classList.add('show');
    if (dropZone) dropZone.style.borderColor = '#ef4444';
    input.value = '';
    if (filenameEl) filenameEl.textContent = 'PDF, DOC, DOCX · Tối đa 5MB';
    return;
  }

  if (file.size > maxSize) {
    const sizeMB = (file.size / 1024 / 1024).toFixed(1);
    if (errMsgEl) errMsgEl.textContent = `File quá lớn (${sizeMB}MB). Vui lòng chọn file nhỏ hơn 5MB.`;
    errEl?.classList.add('show');
    if (dropZone) dropZone.style.borderColor = '#ef4444';
    input.value = '';
    if (filenameEl) filenameEl.textContent = 'PDF, DOC, DOCX · Tối đa 5MB';
    return;
  }

  // File hợp lệ
  if (filenameEl) {
    const sizeKB = (file.size / 1024).toFixed(0);
    filenameEl.innerHTML = `<i class="fas fa-check-circle" style="color:var(--primary)"></i> ${file.name} <span style="opacity:.6">(${sizeKB}KB)</span>`;
  }
  if (dropZone) {
    dropZone.style.borderColor  = 'var(--primary)';
    dropZone.style.background   = 'var(--primary-light)';
  }
}

// Drag-and-drop support
['cv-drop-zone', 'cv-upload-zone'].forEach(id => {
  const zone = document.getElementById(id);
  if (!zone) return;
  zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.borderColor = 'var(--primary)'; });
  zone.addEventListener('dragleave', () => zone.style.borderColor = 'var(--border)');
  zone.addEventListener('drop', e => {
    e.preventDefault();
    const fileInput = document.getElementById('f-cv-file');
    if (!fileInput || !e.dataTransfer.files.length) return;
    const dt = new DataTransfer();
    dt.items.add(e.dataTransfer.files[0]);
    fileInput.files = dt.files;
    validateCvFile(fileInput);
  });
});

// ── Modal helpers ─────────────────────────────────────────────────────────
function openModal(id) {
  const el = document.getElementById(id);
  if (el) { el.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
}
function closeModal(id) {
  const el = document.getElementById(id);
  if (el) { el.style.display = 'none'; document.body.style.overflow = ''; }
}
function agreeAndClose(id) {
  closeModal(id);
  // Tự tick checkbox sau khi đọc xong cả 2 modal
  const cb = document.getElementById('terms-cb');
  if (cb) { cb.checked = true; }
  document.getElementById('err-terms')?.classList.remove('show');
  document.getElementById('terms-row')?.classList.remove('is-error');
}
// Đóng modal khi click vào overlay
['terms-modal','policy-modal'].forEach(id => {
  document.getElementById(id)?.addEventListener('click', function(e) {
    if (e.target === this) closeModal(id);
  });
});
// Đóng bằng ESC
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') { closeModal('terms-modal'); closeModal('policy-modal'); }
});
</script>
@endsection
