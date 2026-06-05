@extends('layouts.app')
@section('title', 'Đăng nhập')

@section('content')
<div style="min-height:calc(100vh - 60px);display:flex;align-items:center;background:linear-gradient(135deg,#f0fdf7 0%,#e8f4fd 100%);padding:40px 16px">
  <div style="width:100%;max-width:420px;margin:0 auto">

    {{-- LOGO --}}
    <div class="text-center mb-24">
      <a href="{{ url('/') }}" class="navbar-brand" style="font-size:28px;justify-content:center">IT<span>Works</span></a>
      <p class="text-muted mt-8">Chào mừng trở lại! Đăng nhập để tiếp tục.</p>
    </div>

    {{-- CARD --}}
    <div class="card">
      <div class="card-body" style="padding:28px">
        <h2 style="font-size:20px;font-weight:800;margin-bottom:20px;color:var(--secondary)">Đăng nhập</h2>

        @if ($errors->any())
          <div class="alert alert-danger mb-16">
            <i class="fas fa-exclamation-circle"></i>
            {{ $errors->first() }}
          </div>
        @endif

        <form action="{{ url('/login') }}" method="POST" id="login-form">
          @csrf
          <div class="flex-col gap-16">

            <div class="form-group">
              <label class="form-label">Email <span class="required">*</span></label>
              <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                placeholder="your@email.com" value="{{ old('email') }}" required autofocus>
              @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
              <div class="flex-between">
                <label class="form-label">Mật khẩu <span class="required">*</span></label>
                <a href="{{ url('/forgot-password') }}" class="fs-12 text-primary-color">Quên mật khẩu?</a>
              </div>
              <div style="position:relative">
                <input type="password" name="password" id="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                  placeholder="Nhập mật khẩu" required>
                <button type="button" onclick="togglePass()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:14px">
                  <i class="fas fa-eye" id="eye-icon"></i>
                </button>
              </div>
              @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="flex-between">
              <label class="filter-option">
                <input type="checkbox" name="remember"> Ghi nhớ đăng nhập
              </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">
              <i class="fas fa-sign-in-alt"></i> Đăng nhập
            </button>
          </div>
        </form>

        <div style="position:relative;text-align:center;margin:20px 0">
          <div class="divider"></div>
          <span style="position:absolute;top:-10px;left:50%;transform:translateX(-50%);background:#fff;padding:0 12px;font-size:13px;color:var(--text-muted)">hoặc</span>
        </div>

        <a href="{{ url('/auth/google') }}" class="btn btn-outline btn-block" style="border-color:#dadce0;color:#333;font-size:14px">
          <img src="https://www.google.com/favicon.ico" style="width:16px;height:16px"> Đăng nhập với Google
        </a>
      </div>

      <div class="card-footer text-center">
        <span class="text-muted fs-13">Chưa có tài khoản?</span>
        <a href="{{ url('/register') }}" class="text-primary-color fw-600 fs-13"> Đăng ký ngay</a>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
function togglePass() {
  var p = document.getElementById('password');
  var i = document.getElementById('eye-icon');
  p.type = p.type === 'password' ? 'text' : 'password';
  i.className = p.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
</script>
@endpush
@endsection
