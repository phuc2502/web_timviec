<?php $__env->startSection('title', 'Đăng nhập — ITWorks'); ?>

<?php $__env->startSection('content'); ?>
<div class="auth-page">
  <div class="auth-container">

    
    <div class="text-center mb-28">
      <a href="<?php echo e(url('/')); ?>" class="navbar-brand" style="font-size:32px;justify-content:center">IT<span>Works</span></a>
      <p class="text-muted mt-8" style="font-size:14px">Chào mừng trở lại! Đăng nhập để tiếp tục.</p>
    </div>

    
    <div class="auth-card">

      
      <?php if(session('success')): ?>
        <div class="auth-alert auth-alert-success"><i class="fas fa-check-circle"></i> <?php echo e(session('success')); ?></div>
      <?php endif; ?>
      <?php if(session('info')): ?>
        <div class="auth-alert auth-alert-info"><i class="fas fa-info-circle"></i> <?php echo e(session('info')); ?></div>
      <?php endif; ?>

      <h2 class="auth-title">Đăng nhập</h2>

      
      <form action="<?php echo e(route('login')); ?>" method="POST" id="login-form">
        <?php echo csrf_field(); ?>

        <div class="form-group">
          <label class="form-label">Email <span class="required">*</span></label>
          <div class="input-wrap">
            <i class="fas fa-envelope input-icon"></i>
            <input type="email" name="email"
              class="form-control <?php echo e($errors->has('email') ? 'is-invalid' : ''); ?>"
              placeholder="your@email.com"
              value="<?php echo e(old('email')); ?>" required autofocus autocomplete="email">
          </div>
          <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> <?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="form-group">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
            <label class="form-label" style="margin:0">Mật khẩu <span class="required">*</span></label>
            <a href="<?php echo e(route('password.request')); ?>" style="font-size:12px;color:var(--primary)">Quên mật khẩu?</a>
          </div>
          <div class="input-wrap">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" name="password" id="password"
              class="form-control <?php echo e($errors->has('password') ? 'is-invalid' : ''); ?>"
              placeholder="Nhập mật khẩu" required autocomplete="current-password">
            <button type="button" class="eye-btn" onclick="togglePass('password','eye1')">
              <i class="fas fa-eye" id="eye1"></i>
            </button>
          </div>
          <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> <?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px">
          <input type="checkbox" name="remember" id="remember" <?php echo e(old('remember') ? 'checked' : ''); ?> style="width:16px;height:16px;accent-color:var(--primary)">
          <label for="remember" style="font-size:13px;color:#64748b;cursor:pointer">Ghi nhớ đăng nhập</label>
        </div>

        <button type="submit" class="btn-submit" id="login-btn">
          <span class="btn-text"><i class="fas fa-sign-in-alt" style="margin-right:8px"></i>Đăng nhập</span>
          <span class="btn-loading" style="display:none"><i class="fas fa-spinner fa-spin" style="margin-right:8px"></i>Đang xử lý...</span>
        </button>
      </form>

      
      <div class="auth-divider" style="margin-top:20px"><span>hoặc đăng nhập bằng</span></div>
      <div class="social-row">
        <a href="<?php echo e(route('auth.google')); ?>" class="social-btn social-btn-google">
          <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
            <path d="M17.64 9.205c0-.639-.057-1.252-.164-1.841H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
            <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
            <path d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
            <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
          </svg>
          Google
        </a>
        <a href="<?php echo e(route('auth.github')); ?>" class="social-btn social-btn-github">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
          </svg>
          GitHub
        </a>
      </div>

      <div class="auth-footer">
        <span style="color:#94a3b8">Chưa có tài khoản?</span>
        <a href="<?php echo e(route('register')); ?>" style="color:var(--primary);font-weight:700;margin-left:6px">Đăng ký ngay →</a>
      </div>
    </div>

  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
:root { --primary:#10b981; --primary-dark:#059669; }
.auth-page {
  min-height: calc(100vh - 60px);
  display: flex; align-items: center;
  background: linear-gradient(135deg, #f0fdf7 0%, #e8f4fd 50%, #fdf4ff 100%);
  padding: 40px 16px;
}
.auth-container { width: 100%; max-width: 420px; margin: 0 auto; }
.auth-card {
  background: #fff;
  border-radius: 20px;
  padding: 36px;
  box-shadow: 0 4px 24px rgba(0,0,0,.08), 0 1px 4px rgba(0,0,0,.04);
  border: 1px solid rgba(255,255,255,.8);
}
.auth-title { font-size: 20px; font-weight: 800; color: #1e293b; margin: 0 0 24px; }
.auth-alert {
  border-radius: 10px; padding: 12px 16px;
  font-size: 14px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;
}
.auth-alert-success { background:#f0fdf7; color:#065f46; border:1px solid #a7f3d0; }
.auth-alert-info    { background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; }

.social-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px; }
.social-btn {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  padding: 11px 16px; border-radius: 10px;
  font-size: 14px; font-weight: 600; text-decoration: none;
  transition: all .15s; border: 1.5px solid;
}
.social-btn-google {
  background: #fff; border-color: #dadce0; color: #3c4043;
}
.social-btn-google:hover { background: #f8f9fa; box-shadow: 0 2px 8px rgba(0,0,0,.1); text-decoration: none; color: #3c4043; }
.social-btn-github {
  background: #24292e; border-color: #24292e; color: #fff;
}
.social-btn-github:hover { background: #1a1f24; box-shadow: 0 2px 8px rgba(0,0,0,.2); text-decoration: none; color: #fff; }

.auth-divider {
  position: relative; text-align: center; margin: 0 0 24px;
}
.auth-divider::before {
  content: ''; position: absolute; top: 50%; left: 0; right: 0;
  height: 1px; background: #f1f5f9;
}
.auth-divider span {
  position: relative; background: #fff;
  padding: 0 14px; font-size: 12px; color: #94a3b8;
}

.form-group { margin-bottom: 18px; }
.form-label { font-size: 14px; font-weight: 600; color: #374151; display: block; margin-bottom: 6px; }
.required { color: #ef4444; }
.input-wrap { position: relative; }
.input-icon {
  position: absolute; left: 13px; top: 50%;
  transform: translateY(-50%);
  color: #94a3b8; font-size: 14px; pointer-events: none;
}
.form-control {
  width: 100%; padding: 11px 42px 11px 38px;
  border: 1.5px solid #e2e8f0; border-radius: 10px;
  font-size: 14px; color: #1e293b;
  background: #fafafa;
  transition: border-color .15s, box-shadow .15s;
  box-sizing: border-box;
}
.form-control:focus {
  outline: none; border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(16,185,129,.1);
  background: #fff;
}
.form-control.is-invalid { border-color: #ef4444; }
.invalid-feedback { font-size: 12px; color: #ef4444; margin-top: 6px; display: flex; align-items: center; gap: 4px; }
.eye-btn {
  position: absolute; right: 12px; top: 50%;
  transform: translateY(-50%);
  background: none; border: none; cursor: pointer;
  color: #94a3b8; font-size: 14px; padding: 4px;
}
.eye-btn:hover { color: #64748b; }

.btn-submit {
  width: 100%; padding: 13px;
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: #fff; border: none; border-radius: 10px;
  font-size: 15px; font-weight: 700; cursor: pointer;
  transition: all .2s;
}
.btn-submit:hover { box-shadow: 0 4px 14px rgba(16,185,129,.35); transform: translateY(-1px); }
.btn-submit:active { transform: none; }

.auth-footer {
  text-align: center; margin-top: 24px;
  padding-top: 20px; border-top: 1px solid #f1f5f9;
  font-size: 14px;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function togglePass(inputId, iconId) {
  const p = document.getElementById(inputId);
  const i = document.getElementById(iconId);
  p.type = p.type === 'password' ? 'text' : 'password';
  i.className = p.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
document.getElementById('login-form').addEventListener('submit', function() {
  const btn = document.getElementById('login-btn');
  btn.querySelector('.btn-text').style.display = 'none';
  btn.querySelector('.btn-loading').style.display = 'inline';
  btn.disabled = true;
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\dl\website_timviec_v15 (1)\website_timviec_v15 (1)\website_modified\resources\views/auth/login.blade.php ENDPATH**/ ?>