@extends('layouts.app')
@section('title', 'Đăng ký Ứng viên')

@section('content')
<div style="min-height:calc(100vh - 60px);display:flex;align-items:center;background:linear-gradient(135deg,#f0fdf7 0%,#e8f4fd 100%);padding:40px 16px">
  <div style="width:100%;max-width:480px;margin:0 auto">

    <div class="text-center mb-24">
      <a href="{{ url('/') }}" class="navbar-brand" style="font-size:28px;justify-content:center">IT<span>Works</span></a>
      <h1 style="font-size:20px;font-weight:800;margin-top:12px;color:var(--secondary)">Đăng ký tài khoản Ứng viên</h1>
      <p class="text-muted mt-4 fs-13">Miễn phí — Tìm việc làm IT ngay hôm nay</p>
    </div>

    <div class="card">
      <div class="card-body" style="padding:28px">
        @if ($errors->any())
          <div class="alert alert-danger mb-16">
            <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
          </div>
        @endif

        <form action="{{ url('/register/tim') }}" method="POST">
          @csrf
          <input type="hidden" name="user_type" value="employee">
          <div class="flex-col gap-16">

            <div class="form-group">
              <label class="form-label">Họ và tên <span class="required">*</span></label>
              <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                placeholder="Nguyễn Văn A" value="{{ old('name') }}" required>
              @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
              <label class="form-label">Email <span class="required">*</span></label>
              <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                placeholder="your@email.com" value="{{ old('email') }}" required>
              @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
              <label class="form-label">Mật khẩu <span class="required">*</span></label>
              <div style="position:relative">
                <input type="password" name="password" id="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                  placeholder="Tối thiểu 8 ký tự" required>
                <button type="button" onclick="togglePass('password','eye1')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:var(--text-muted)">
                  <i class="fas fa-eye" id="eye1"></i>
                </button>
              </div>
              @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
              <label class="form-label">Xác nhận mật khẩu <span class="required">*</span></label>
              <div style="position:relative">
                <input type="password" name="password_confirmation" id="password2" class="form-control"
                  placeholder="Nhập lại mật khẩu" required>
                <button type="button" onclick="togglePass('password2','eye2')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:var(--text-muted)">
                  <i class="fas fa-eye" id="eye2"></i>
                </button>
              </div>
            </div>

            <label class="filter-option" style="font-size:13px">
              <input type="checkbox" required>
              Tôi đồng ý với <a href="#" class="text-primary-color">Điều khoản dịch vụ</a> và <a href="#" class="text-primary-color">Chính sách bảo mật</a>
            </label>

            <button type="submit" class="btn btn-primary btn-block btn-lg">
              <i class="fas fa-user-plus"></i> Đăng ký miễn phí
            </button>
          </div>
        </form>

        <div style="position:relative;text-align:center;margin:20px 0">
          <div class="divider"></div>
          <span style="position:absolute;top:-10px;left:50%;transform:translateX(-50%);background:#fff;padding:0 12px;font-size:13px;color:var(--text-muted)">hoặc</span>
        </div>

        <a href="{{ url('/auth/google') }}" class="btn btn-outline btn-block" style="border-color:#dadce0;color:#333">
          <img src="https://www.google.com/favicon.ico" style="width:16px;height:16px"> Đăng ký với Google
        </a>
      </div>
      <div class="card-footer text-center">
        <a href="{{ url('/register') }}" class="fs-12 text-muted"><i class="fas fa-arrow-left"></i> Chọn lại loại tài khoản</a>
        <span class="text-muted fs-12 mx-8">|</span>
        <span class="text-muted fs-12">Đã có tài khoản? <a href="{{ url('/login') }}" class="text-primary-color fw-600">Đăng nhập</a></span>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
function togglePass(id, iconId) {
  var p = document.getElementById(id);
  var i = document.getElementById(iconId);
  p.type = p.type === 'password' ? 'text' : 'password';
  i.className = p.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
</script>
@endpush
@endsection
