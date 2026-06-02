@extends('layouts.dashboard')
@section('title', 'Thông tin cá nhân')

@section('content')
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-20 fw-800" style="color:var(--secondary)">Thông tin cá nhân</h1>
    <p class="text-muted fs-13 mt-4">Cập nhật thông tin hồ sơ của bạn</p>
  </div>
</div>

<div class="grid-1-2">

  {{-- LEFT: AVATAR --}}
  <div class="flex-col gap-16">
    <div class="card">
      <div class="card-body" style="padding:24px;text-align:center">
        <div style="position:relative;display:inline-block;margin-bottom:16px">
          @if(auth()->user()->profile_pic)
            <img src="{{ asset('storage/images/'.auth()->user()->profile_pic) }}" class="avatar avatar-xl" style="border:3px solid var(--primary-light)" alt="">
          @else
            <div class="avatar avatar-xl avatar-placeholder" style="font-size:36px;background:var(--primary-light);color:var(--primary);margin:0 auto">
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

    {{-- Notification toggle --}}
    <div class="card">
      <div class="card-header"><span class="fw-600 fs-14">Cài đặt thông báo</span></div>
      <div class="card-body" style="padding:16px">
        <form action="{{ url('/user/mail') }}" method="POST">
          @csrf
          <div class="flex-between">
            <div>
              <div class="fw-600 fs-13">Nhận email thông báo</div>
              <div class="text-muted fs-12">Khi có tin tức mới về ứng tuyển</div>
            </div>
            <label style="position:relative;display:inline-block;width:44px;height:24px;cursor:pointer">
              <input type="checkbox" name="mail" {{ auth()->user()->mail ? 'checked' : '' }} onchange="this.form.submit()" style="opacity:0;width:0;height:0">
              <span style="position:absolute;inset:0;background:{{ auth()->user()->mail ? 'var(--primary)' : '#ccc' }};border-radius:24px;transition:.3s"></span>
              <span style="position:absolute;top:2px;left:{{ auth()->user()->mail ? '22px' : '2px' }};width:20px;height:20px;background:#fff;border-radius:50%;transition:.3s;box-shadow:0 1px 4px rgba(0,0,0,.2)"></span>
            </label>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- RIGHT: FORM --}}
  <div class="flex-col gap-16">
    {{-- Basic info --}}
    <div class="card">
      <div class="card-header">
        <span class="fw-700 fs-15"><i class="fas fa-user" style="color:var(--primary);margin-right:8px"></i>Thông tin cơ bản</span>
      </div>
      <div class="card-body" style="padding:24px">
        <form action="{{ url('/user/profile') }}" method="POST" enctype="multipart/form-data" id="profile-form">
          @csrf
          <input type="file" id="avatar-upload" name="profile_pic" accept="image/*" style="display:none" onchange="previewAvatar(this)">

          <div class="flex-col gap-16">
            <div class="grid-2" style="gap:14px">
              <div class="form-group">
                <label class="form-label">Họ và tên <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ auth()->user()->name }}" required>
              </div>
              <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="{{ auth()->user()->email }}" disabled style="background:#f8f9fa;color:var(--text-muted)">
                <div class="form-hint">Email không thể thay đổi</div>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Giới thiệu bản thân</label>
              <textarea name="about" class="form-control" rows="4" placeholder="Chia sẻ một chút về bản thân, kinh nghiệm và mục tiêu nghề nghiệp của bạn...">{{ auth()->user()->about }}</textarea>
            </div>

            @if(auth()->user()->user_type === 'employer')
              <div class="divider"></div>
              <div class="fw-600 fs-13 text-muted" style="text-transform:uppercase;letter-spacing:.5px">Thông tin công ty</div>
              <div class="grid-2" style="gap:14px">
                <div class="form-group">
                  <label class="form-label">Tên công ty</label>
                  <input type="text" name="company_name" class="form-control" value="{{ old('company_name', auth()->user()->company_name ?? '') }}" placeholder="FPT Software...">
                </div>
                <div class="form-group">
                  <label class="form-label">Website</label>
                  <input type="url" name="company_website" class="form-control" value="{{ old('company_website', auth()->user()->company_website ?? '') }}" placeholder="https://company.com">
                </div>
              </div>
            @else
              <div class="divider"></div>
              <div class="fw-600 fs-13 text-muted" style="text-transform:uppercase;letter-spacing:.5px">Thông tin nghề nghiệp</div>
              <div class="grid-2" style="gap:14px">
                <div class="form-group">
                  <label class="form-label">Năm kinh nghiệm</label>
                  <select name="experience_years" class="form-control">
                    <option value="">Chọn...</option>
                    <option value="0" {{ (auth()->user()->experience_years ?? '') == '0' ? 'selected' : '' }}>Chưa có kinh nghiệm</option>
                    <option value="1" {{ (auth()->user()->experience_years ?? '') == '1' ? 'selected' : '' }}>1 năm</option>
                    <option value="2" {{ (auth()->user()->experience_years ?? '') == '2' ? 'selected' : '' }}>2 năm</option>
                    <option value="3" {{ (auth()->user()->experience_years ?? '') == '3' ? 'selected' : '' }}>3 năm</option>
                    <option value="5" {{ (auth()->user()->experience_years ?? '') == '5' ? 'selected' : '' }}>5+ năm</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Địa điểm</label>
                  <select name="location" class="form-control">
                    <option value="">Chọn...</option>
                    <option value="Hà Nội">Hà Nội</option>
                    <option value="Hồ Chí Minh">Hồ Chí Minh</option>
                    <option value="Đà Nẵng">Đà Nẵng</option>
                    <option value="Khác">Khác</option>
                  </select>
                </div>
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
        <form action="{{ url('/user/profile/password') }}" method="POST">
          @csrf
          <div class="flex-col gap-14">
            <div class="form-group">
              <label class="form-label">Mật khẩu hiện tại <span class="required">*</span></label>
              <input type="password" name="current_password" class="form-control {{ $errors->has('current_password') ? 'is-invalid' : '' }}" placeholder="Nhập mật khẩu hiện tại">
              @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
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

@push('scripts')
<script>
function previewAvatar(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      document.querySelector('.avatar-xl').src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
    document.getElementById('profile-form').submit();
  }
}
</script>
@endpush
@endsection
