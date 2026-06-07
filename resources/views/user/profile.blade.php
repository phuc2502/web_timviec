@extends('layouts.dashboard')
@section('title', 'Thông tin cá nhân')

@section('content')
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-20 fw-800" style="color:var(--secondary)">Thông tin cá nhân</h1>
    <p class="text-muted fs-13 mt-4">Cập nhật thông tin hồ sơ của bạn</p>
  </div>
</div>

{{-- Flash messages --}}
@if(session('success'))
  <div class="alert alert-success mb-16" style="padding:12px 16px;background:#d1fae5;border:1px solid #6ee7b7;border-radius:8px;color:#065f46;font-size:13px">
    <i class="fas fa-check-circle" style="margin-right:6px"></i>{{ session('success') }}
  </div>
@endif
@if(session('error'))
  <div class="alert alert-danger mb-16" style="padding:12px 16px;background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;color:#991b1b;font-size:13px">
    <i class="fas fa-exclamation-circle" style="margin-right:6px"></i>{{ session('error') }}
  </div>
@endif

<div class="grid" style="grid-template-columns:1fr 2fr;gap:20px;align-items:start">

  {{-- LEFT: AVATAR + NOTIFICATION SETTINGS --}}
  <div class="flex-col gap-16">
    <div class="card">
      <div class="card-body" style="padding:24px;text-align:center">
        <div style="position:relative;display:inline-block;margin-bottom:16px" id="avatar-wrapper">
          @if(auth()->user()->profile_pic)
            <img id="avatar-preview" src="{{ asset('storage/images/'.auth()->user()->profile_pic) }}" class="avatar avatar-xl" style="border:3px solid var(--primary-light)" alt="">
          @else
            <div id="avatar-preview" class="avatar avatar-xl avatar-placeholder" style="font-size:36px;background:var(--primary-light);color:var(--primary);margin:0 auto">
              {{ strtoupper(substr(auth()->user()->name,0,1)) }}
            </div>
          @endif
          <label for="avatar-upload" style="position:absolute;bottom:0;right:0;width:28px;height:28px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;border:2px solid #fff">
            <i class="fas fa-camera" style="font-size:11px;color:#fff"></i>
          </label>
        </div>
        <div class="fw-700 fs-16">{{ auth()->user()->name }}</div>
        <div class="text-muted fs-12 mt-4">{{ auth()->user()->email }}</div>
        <div class="tag tag-green mt-12" style="display:inline-flex">
          {{ auth()->user()->user_type === 'employer' ? 'Nhà tuyển dụng' : 'Ứng viên' }}
        </div>
      </div>
    </div>

    {{-- Notification settings --}}
    <div class="card">
      <div class="card-header"><span class="fw-600 fs-14">Cài đặt thông báo</span></div>
      <div class="card-body" style="padding:16px">
        <form action="{{ route('profile.notification-settings') }}" method="POST" id="notif-settings-form">
          @csrf
          <div class="flex-col gap-12">
            {{-- Email toggle --}}
            <div class="flex-between">
              <div>
                <div class="fw-600 fs-13">Nhận email thông báo</div>
                <div class="text-muted fs-12">Bật/tắt tất cả email</div>
              </div>
              <label class="toggle-label">
                <input type="checkbox" name="mail" id="toggle-mail" {{ auth()->user()->mail ? 'checked' : '' }} onchange="document.getElementById('notif-settings-form').submit()">
                <span class="toggle-track"></span>
                <span class="toggle-thumb"></span>
              </label>
            </div>

            <hr style="border:none;border-top:1px solid var(--border);margin:4px 0">

            {{-- Shortlist notification --}}
            <div class="flex-between">
              <div>
                <div class="fw-600 fs-13">Thông báo shortlist</div>
                <div class="text-muted fs-12">Khi được nhà tuyển dụng shortlist</div>
              </div>
              <label class="toggle-label">
                <input type="checkbox" name="notify_shortlist" id="toggle-shortlist" {{ auth()->user()->notify_shortlist ? 'checked' : '' }} onchange="document.getElementById('notif-settings-form').submit()">
                <span class="toggle-track"></span>
                <span class="toggle-thumb"></span>
              </label>
            </div>

            {{-- App status notification --}}
            <div class="flex-between">
              <div>
                <div class="fw-600 fs-13">Trạng thái ứng tuyển</div>
                <div class="text-muted fs-12">Khi đơn được cập nhật</div>
              </div>
              <label class="toggle-label">
                <input type="checkbox" name="notify_app_status" id="toggle-app-status" {{ auth()->user()->notify_app_status ? 'checked' : '' }} onchange="document.getElementById('notif-settings-form').submit()">
                <span class="toggle-track"></span>
                <span class="toggle-thumb"></span>
              </label>
            </div>

            @if(auth()->user()->user_type === 'employee')
            {{-- Job alert notification (chỉ ứng viên) --}}
            <div class="flex-between">
              <div>
                <div class="fw-600 fs-13">Cảnh báo việc làm</div>
                <div class="text-muted fs-12">Thông báo việc mới phù hợp</div>
              </div>
              <label class="toggle-label">
                <input type="checkbox" name="notify_job_alert" id="toggle-job-alert" {{ auth()->user()->notify_job_alert ? 'checked' : '' }} onchange="document.getElementById('notif-settings-form').submit()">
                <span class="toggle-track"></span>
                <span class="toggle-thumb"></span>
              </label>
            </div>
            @endif
          </div>
        </form>
      </div>
    </div>

    {{-- Profile completeness --}}
    @if($completeness['percent'] < 100)
    <div class="card" style="border-left:3px solid var(--warning,#f59e0b)">
      <div class="card-body" style="padding:16px">
        <div class="fw-600 fs-13 mb-8">Hoàn thiện hồ sơ ({{ $completeness['percent'] }}%)</div>
        <div style="background:#f3f4f6;border-radius:8px;height:8px;overflow:hidden;margin-bottom:10px">
          <div style="height:100%;width:{{ $completeness['percent'] }}%;background:var(--primary);border-radius:8px;transition:.3s"></div>
        </div>
        <div class="fs-12 text-muted">Còn thiếu: {{ implode(', ', $completeness['missing']) }}</div>
      </div>
    </div>
    @endif
  </div>

  {{-- RIGHT: FORM --}}
  <div class="flex-col gap-16">
    {{-- Basic info + role-specific fields --}}
    <div class="card">
      <div class="card-header">
        <span class="fw-700 fs-15"><i class="fas fa-user" style="color:var(--primary);margin-right:8px"></i>Thông tin cơ bản</span>
      </div>
      <div class="card-body" style="padding:24px">

        {{-- FIX: action dynamically set based on user_type --}}
        @if(auth()->user()->user_type === 'employer')
          <form action="{{ route('profile.update.employer') }}" method="POST" enctype="multipart/form-data" id="profile-form">
        @else
          <form action="{{ route('profile.update.employee') }}" method="POST" enctype="multipart/form-data" id="profile-form">
        @endif
          @csrf
          <input type="file" id="avatar-upload" name="profile_pic" accept="image/*" style="display:none" onchange="previewAvatar(this)">

          <div class="flex-col gap-16">
            <div class="grid-2" style="gap:14px">
              <div class="form-group">
                <label class="form-label">Họ và tên <span class="required">*</span></label>
                <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" value="{{ old('name', auth()->user()->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="{{ auth()->user()->email }}" disabled style="background:#f8f9fa;color:var(--text-muted)">
                <div class="form-hint">Email không thể thay đổi</div>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Giới thiệu bản thân</label>
              <textarea name="about" class="form-control {{ $errors->has('about') ? 'is-invalid' : '' }}" rows="4" placeholder="Chia sẻ một chút về bản thân, kinh nghiệm và mục tiêu nghề nghiệp...">{{ old('about', auth()->user()->about) }}</textarea>
              @error('about')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @if(auth()->user()->user_type === 'employer')
              {{-- ── EMPLOYER FIELDS ── --}}
              <div class="divider"></div>
              <div class="fw-600 fs-13 text-muted" style="text-transform:uppercase;letter-spacing:.5px">Thông tin công ty</div>

              <div class="grid-2" style="gap:14px">
                <div class="form-group">
                  <label class="form-label">Tên công ty</label>
                  <input type="text" name="company_name" class="form-control {{ $errors->has('company_name') ? 'is-invalid' : '' }}" value="{{ old('company_name', auth()->user()->company_name ?? '') }}" placeholder="FPT Software...">
                  @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                  <label class="form-label">Website</label>
                  <input type="url" name="company_website" class="form-control {{ $errors->has('company_website') ? 'is-invalid' : '' }}" value="{{ old('company_website', auth()->user()->company_website ?? '') }}" placeholder="https://company.com">
                  @error('company_website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="grid-2" style="gap:14px">
                <div class="form-group">
                  <label class="form-label">Quy mô công ty</label>
                  <select name="company_size" class="form-control {{ $errors->has('company_size') ? 'is-invalid' : '' }}">
                    <option value="">Chọn quy mô...</option>
                    @foreach(['1-10','11-50','51-200','201-500','500+'] as $size)
                      <option value="{{ $size }}" {{ old('company_size', auth()->user()->company_size) === $size ? 'selected' : '' }}>{{ $size }} nhân viên</option>
                    @endforeach
                  </select>
                  @error('company_size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                  <label class="form-label">Logo công ty</label>
                  @if(auth()->user()->company_logo)
                    <div class="mb-8">
                      <img src="{{ asset('storage/images/'.auth()->user()->company_logo) }}" style="height:48px;border-radius:6px;border:1px solid var(--border)" alt="Logo">
                    </div>
                  @endif
                  <input type="file" name="company_logo" class="form-control {{ $errors->has('company_logo') ? 'is-invalid' : '' }}" accept="image/jpg,image/jpeg,image/png,image/webp">
                  <div class="form-hint">JPG, PNG, WebP. Tối đa 2MB.</div>
                  @error('company_logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

            @else
              {{-- ── EMPLOYEE FIELDS ── --}}
              <div class="divider"></div>
              <div class="fw-600 fs-13 text-muted" style="text-transform:uppercase;letter-spacing:.5px">Thông tin nghề nghiệp</div>

              <div class="grid-2" style="gap:14px">
                <div class="form-group">
                  <label class="form-label">Năm kinh nghiệm</label>
                  <select name="experience_years" class="form-control {{ $errors->has('experience_years') ? 'is-invalid' : '' }}">
                    <option value="">Chọn...</option>
                    @foreach([0=>'Chưa có kinh nghiệm', 1=>'1 năm', 2=>'2 năm', 3=>'3 năm', 5=>'5+ năm'] as $val => $label)
                      <option value="{{ $val }}" {{ (string)old('experience_years', auth()->user()->experience_years ?? '') === (string)$val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                  </select>
                  @error('experience_years')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                  <label class="form-label">Địa điểm</label>
                  <select name="location" class="form-control {{ $errors->has('location') ? 'is-invalid' : '' }}">
                    <option value="">Chọn...</option>
                    @foreach(['Hà Nội','Hồ Chí Minh','Đà Nẵng','Khác'] as $city)
                      <option value="{{ $city }}" {{ old('location', auth()->user()->location) === $city ? 'selected' : '' }}>{{ $city }}</option>
                    @endforeach
                  </select>
                  @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="grid-2" style="gap:14px">
                <div class="form-group">
                  <label class="form-label">Mức lương mong muốn (VNĐ/tháng)</label>
                  <input type="number" name="desired_salary" class="form-control {{ $errors->has('desired_salary') ? 'is-invalid' : '' }}" value="{{ old('desired_salary', auth()->user()->desired_salary ?? '') }}" min="0" placeholder="Ví dụ: 15000000">
                  @error('desired_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                  <label class="form-label">Loại công việc mong muốn</label>
                  <select name="job_type_pref" class="form-control {{ $errors->has('job_type_pref') ? 'is-invalid' : '' }}">
                    <option value="">Chọn...</option>
                    @foreach(['full-time'=>'Toàn thời gian','part-time'=>'Bán thời gian','remote'=>'Làm từ xa','freelance'=>'Freelance'] as $val => $label)
                      <option value="{{ $val }}" {{ old('job_type_pref', auth()->user()->job_type_pref) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                  </select>
                  @error('job_type_pref')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Kỹ năng (mỗi kỹ năng cách nhau bằng dấu phẩy)</label>
                <input type="text" name="skills_input" id="skills-input" class="form-control {{ $errors->has('skills') ? 'is-invalid' : '' }}"
                       value="{{ old('skills_input', is_array(auth()->user()->skills) ? implode(', ', auth()->user()->skills) : '') }}"
                       placeholder="PHP, Laravel, MySQL, JavaScript...">
                <div class="form-hint">Nhập các kỹ năng, cách nhau bằng dấu phẩy.</div>
                @error('skills')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

            @endif

            <div class="flex" style="justify-content:flex-end;gap:10px">
              <button type="reset" class="btn btn-outline">Huỷ</button>
              <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu thay đổi</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    {{-- Change password --}}
    <div class="card">
      <div class="card-header">
        <span class="fw-700 fs-15"><i class="fas fa-lock" style="color:var(--primary);margin-right:8px"></i>Đổi mật khẩu</span>
      </div>
      <div class="card-body" style="padding:24px">
        <form action="{{ route('profile.password') }}" method="POST">
          @csrf
          <div class="flex-col gap-14">
            @if(!is_null(auth()->user()->password))
            <div class="form-group">
              <label class="form-label">Mật khẩu hiện tại <span class="required">*</span></label>
              <input type="password" name="current_password" class="form-control {{ $errors->has('current_password') ? 'is-invalid' : '' }}" placeholder="Nhập mật khẩu hiện tại">
              @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            @else
            <div class="alert" style="padding:10px 14px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;font-size:13px;color:#1d4ed8">
              <i class="fas fa-info-circle" style="margin-right:6px"></i>Tài khoản OAuth chưa có mật khẩu. Bạn có thể đặt mật khẩu mới bên dưới.
            </div>
            @endif
            <div class="grid-2" style="gap:14px">
              <div class="form-group">
                <label class="form-label">Mật khẩu mới <span class="required">*</span></label>
                <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="Tối thiểu 8 ký tự">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="form-group">
                <label class="form-label">Xác nhận mật khẩu mới <span class="required">*</span></label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Nhập lại">
              </div>
            </div>
            <div style="text-align:right">
              <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Cập nhật mật khẩu</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
/* CSS toggle switch - thay thế inline style PHP (FIX toggle animation) */
.toggle-label {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
  cursor: pointer;
  flex-shrink: 0;
}
.toggle-label input { opacity: 0; width: 0; height: 0; position: absolute; }
.toggle-track {
  position: absolute;
  inset: 0;
  background: #ccc;
  border-radius: 24px;
  transition: background .3s;
}
.toggle-label input:checked ~ .toggle-track { background: var(--primary); }
.toggle-thumb {
  position: absolute;
  top: 2px; left: 2px;
  width: 20px; height: 20px;
  background: #fff;
  border-radius: 50%;
  transition: transform .3s;
  box-shadow: 0 1px 4px rgba(0,0,0,.2);
}
.toggle-label input:checked ~ .toggle-thumb { transform: translateX(20px); }
</style>
@endpush

@push('scripts')
<script>
function previewAvatar(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      // FIX: handle both img and placeholder div
      var wrapper = document.getElementById('avatar-wrapper');
      var existing = document.getElementById('avatar-preview');
      if (existing && existing.tagName === 'IMG') {
        existing.src = e.target.result;
      } else {
        // Replace placeholder div with img
        var img = document.createElement('img');
        img.id = 'avatar-preview';
        img.src = e.target.result;
        img.className = 'avatar avatar-xl';
        img.style = 'border:3px solid var(--primary-light)';
        img.alt = '';
        if (existing) existing.replaceWith(img);
      }
    };
    reader.readAsDataURL(input.files[0]);
    // Auto-submit form to save avatar
    document.getElementById('profile-form').submit();
  }
}

// FIX: convert skills comma-separated input to array fields before submit
document.getElementById('profile-form')?.addEventListener('submit', function(e) {
  var skillsInput = document.getElementById('skills-input');
  if (!skillsInput) return; // employer form has no skills field

  var val = skillsInput.value.trim();
  if (!val) return;

  // Remove the text input from submission
  skillsInput.removeAttribute('name');

  // Create hidden array inputs
  var skills = val.split(',').map(s => s.trim()).filter(s => s.length > 0);
  skills.forEach(function(skill) {
    var hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = 'skills[]';
    hidden.value = skill;
    document.getElementById('profile-form').appendChild(hidden);
  });
});
</script>
@endpush
@endsection
