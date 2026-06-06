@extends('layouts.app')
@section('title', 'Khôi phục mật khẩu — ITWorks')

@push('styles')
@include('auth._auth-styles')
<style>
.forgot-hero {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 28px;
}
.forgot-icon-wrap {
  width: 80px; height: 80px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  border-radius: 24px;
  display: flex; align-items: center; justify-content: center;
  font-size: 32px; color: #fff;
  box-shadow: 0 8px 24px rgba(16,185,129,.35);
  margin-bottom: 20px;
  position: relative;
}
.forgot-icon-wrap::after {
  content: '';
  position: absolute;
  inset: -6px;
  border-radius: 30px;
  border: 2px dashed rgba(16,185,129,.3);
  animation: spin-slow 10s linear infinite;
}
@keyframes spin-slow { to { transform: rotate(360deg); } }

.forgot-card {
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0,0,0,.1), 0 4px 16px rgba(0,0,0,.06);
  overflow: hidden;
}
.forgot-card-header {
  background: linear-gradient(135deg, #f0fdf7 0%, #e8f4fd 100%);
  padding: 32px 36px 28px;
  text-align: center;
  border-bottom: 1px solid rgba(16,185,129,.15);
}
.forgot-card-body { padding: 32px 36px; }
.forgot-card-footer {
  padding: 16px 36px;
  background: #fafafa;
  border-top: 1px solid #f0f0f0;
  text-align: center;
}

.steps-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-top: 20px;
}
.step-item {
  display: flex; align-items: flex-start; gap: 10px;
  background: #f8fafc; border-radius: 12px;
  padding: 12px; font-size: 12px; color: #64748b;
}
.step-num {
  width: 22px; height: 22px;
  background: linear-gradient(135deg, #10b981, #059669);
  color: #fff; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 700; flex-shrink: 0;
}

.input-wrap { position: relative; }
.input-icon {
  position: absolute; left: 14px; top: 50%;
  transform: translateY(-50%);
  color: #94a3b8; font-size: 14px; pointer-events: none;
}
.input-wrap .form-control { padding-left: 42px; }

.btn-send {
  display: flex; align-items: center; justify-content: center; gap: 10px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: #fff; border: none; border-radius: 12px;
  padding: 14px 24px; width: 100%;
  font-size: 15px; font-weight: 700; cursor: pointer;
  box-shadow: 0 4px 16px rgba(16,185,129,.35);
  transition: all .2s;
}
.btn-send:hover { transform: translateY(-1px); box-shadow: 0 6px 24px rgba(16,185,129,.45); }
.btn-send:active { transform: translateY(0); }

.alert-logout {
  display: flex; align-items: flex-start; gap: 12px;
  background: linear-gradient(135deg, #fffbeb, #fef3c7);
  border: 1px solid #fcd34d;
  border-radius: 12px; padding: 14px 16px;
  font-size: 13px; color: #92400e;
  margin-bottom: 24px;
}
.alert-logout i { margin-top: 1px; color: #f59e0b; font-size: 16px; flex-shrink: 0; }

.alert-success-custom {
  display: flex; align-items: flex-start; gap: 12px;
  background: linear-gradient(135deg, #f0fdf4, #dcfce7);
  border: 1px solid #86efac;
  border-radius: 12px; padding: 14px 16px;
  font-size: 13px; color: #166534;
  margin-bottom: 24px;
}
.alert-success-custom i { margin-top: 1px; color: #22c55e; font-size: 16px; flex-shrink: 0; }
</style>
@endpush

@section('content')
<div class="auth-page">
  <div class="auth-container" style="max-width:460px">

    {{-- LOGO + ICON --}}
    <div class="forgot-hero">
      <div class="forgot-icon-wrap">
        <i class="fas fa-key"></i>
      </div>
      <a href="{{ url('/') }}" class="navbar-brand" style="font-size:28px;justify-content:center">IT<span>Works</span></a>
    </div>

    {{-- CARD --}}
    <div class="forgot-card">

      {{-- Header --}}
      <div class="forgot-card-header">
        <h1 style="font-size:20px;font-weight:800;color:#0f172a;margin:0 0 6px">Khôi phục mật khẩu</h1>
        <p style="font-size:13px;color:#64748b;margin:0;line-height:1.6">
          Nhập email đã đăng ký — chúng tôi sẽ gửi link đặt lại mật khẩu ngay lập tức.
        </p>
      </div>

      {{-- Body --}}
      <div class="forgot-card-body">

        {{-- Flash: vừa đăng xuất --}}
        @if (session('info'))
          <div class="alert-logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>{{ session('info') }}</span>
          </div>
        @endif

        {{-- Flash: gửi thành công --}}
        @if (session('status'))
          <div class="alert-success-custom">
            <i class="fas fa-check-circle"></i>
            <div>
              <strong style="display:block;margin-bottom:2px">Email đã được gửi!</strong>
              {{ session('status') }}
            </div>
          </div>
        @endif

        {{-- Form --}}
        <form action="{{ route('password.email') }}" method="POST" id="forgot-form">
          @csrf

          <div style="margin-bottom:20px">
            <label class="form-label" style="font-weight:600;margin-bottom:8px;display:block">
              Địa chỉ email <span style="color:#ef4444">*</span>
            </label>
            <div class="input-wrap">
              <i class="fas fa-envelope input-icon"></i>
              <input type="email" name="email"
                class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                placeholder="your@email.com"
                value="{{ old('email') }}"
                required autofocus
                style="border-radius:12px;padding:12px 14px 12px 42px;font-size:14px">
            </div>
            @error('email')
              <div class="invalid-feedback" style="margin-top:6px;font-size:12px">
                <i class="fas fa-exclamation-circle"></i> {{ $message }}
              </div>
            @enderror
          </div>

          <button type="submit" class="btn-send" id="send-btn">
            <span class="btn-text"><i class="fas fa-paper-plane"></i> Gửi link khôi phục</span>
            <span class="btn-loading" style="display:none"><i class="fas fa-spinner fa-spin"></i> Đang gửi...</span>
          </button>
        </form>

        {{-- Steps --}}
        <div style="margin-top:24px">
          <p style="font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px">
            Các bước thực hiện
          </p>
          <div class="steps-grid">
            <div class="step-item">
              <div class="step-num">1</div>
              <span>Nhập email đã đăng ký vào ô trên</span>
            </div>
            <div class="step-item">
              <div class="step-num">2</div>
              <span>Kiểm tra hộp thư (cả Spam / Junk)</span>
            </div>
            <div class="step-item">
              <div class="step-num">3</div>
              <span>Nhấn vào link trong email để đặt lại</span>
            </div>
            <div class="step-item">
              <div class="step-num">4</div>
              <span>Đăng nhập bằng mật khẩu mới của bạn</span>
            </div>
          </div>
        </div>

      </div>

      {{-- Footer --}}
      <div class="forgot-card-footer">
        <a href="{{ route('login') }}" style="font-size:13px;color:#64748b;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:.2s"
           onmouseover="this.style.color='#10b981'" onmouseout="this.style.color='#64748b'">
          <i class="fas fa-arrow-left"></i> Quay lại đăng nhập
        </a>
        <span style="margin:0 12px;color:#e2e8f0">|</span>
        <a href="{{ route('register') }}" style="font-size:13px;color:#64748b;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:.2s"
           onmouseover="this.style.color='#10b981'" onmouseout="this.style.color='#64748b'">
          Tạo tài khoản mới <i class="fas fa-arrow-right"></i>
        </a>
      </div>

    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('forgot-form').addEventListener('submit', function () {
  const btn = document.getElementById('send-btn');
  btn.querySelector('.btn-text').style.display = 'none';
  btn.querySelector('.btn-loading').style.display = 'inline-flex';
  btn.disabled = true;
});
</script>
@endpush
