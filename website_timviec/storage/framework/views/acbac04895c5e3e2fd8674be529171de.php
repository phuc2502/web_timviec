<?php $__env->startSection('title', 'Đăng ký Nhà tuyển dụng — ITWorks'); ?>

<?php $__env->startSection('content'); ?>
<div class="auth-page">
  <div class="auth-container" style="max-width:520px">

    
    <div class="text-center mb-20">
      <a href="<?php echo e(url('/')); ?>" class="navbar-brand" style="font-size:28px;justify-content:center">IT<span>Works</span></a>
      <div class="role-badge mt-12" style="background:#fff3e0;color:#f57c00;border-color:#ffe0b2">
        <i class="fas fa-building"></i> Tài khoản Nhà tuyển dụng
      </div>
    </div>

    
    <div class="alert mb-16" style="background:linear-gradient(135deg,#fff3e0,#fce4d6);border:1px solid #ffcc80;border-radius:var(--radius-lg);padding:14px 18px">
      <div class="flex gap-10" style="align-items:center">
        <i class="fas fa-gift" style="color:#f57c00;font-size:20px"></i>
        <div>
          <div class="fw-700 fs-14" style="color:#e65100">Ưu đãi đặc biệt!</div>
          <div class="fs-12 text-muted">Đăng ký ngay — nhận <strong>7 ngày dùng thử Premium miễn phí</strong>, không cần thẻ tín dụng.</div>
        </div>
      </div>
    </div>

    <div class="auth-divider"><span>hoặc đăng ký bằng email</span></div>

    
    <div class="card auth-card">
      <div class="card-body" style="padding:28px">

        <?php if($errors->any()): ?>
          <div class="alert alert-danger mb-20">
            <i class="fas fa-exclamation-circle"></i> <?php echo e($errors->first()); ?>

          </div>
        <?php endif; ?>

        <form action="<?php echo e(route('register.employer.submit')); ?>" method="POST" id="register-form">
          <?php echo csrf_field(); ?>
          <div style="display:flex;flex-direction:column;gap:14px">

            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
              <div class="form-group">
                <label class="form-label">Họ tên <span class="required">*</span></label>
                <div class="input-icon-wrap">
                  <i class="fas fa-user input-icon"></i>
                  <input type="text" name="name"
                    class="form-control input-with-icon <?php echo e($errors->has('name') ? 'is-invalid' : ''); ?>"
                    placeholder="Nguyễn Văn A"
                    value="<?php echo e(old('name')); ?>" required>
                </div>
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
              </div>
              <div class="form-group">
                <label class="form-label">Email <span class="required">*</span></label>
                <div class="input-icon-wrap">
                  <i class="fas fa-envelope input-icon"></i>
                  <input type="email" name="email"
                    class="form-control input-with-icon <?php echo e($errors->has('email') ? 'is-invalid' : ''); ?>"
                    placeholder="hr@company.com"
                    value="<?php echo e(old('email')); ?>" required>
                </div>
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
              </div>
            </div>

            
            <div class="form-group">
              <label class="form-label">Tên công ty <span class="required">*</span></label>
              <div class="input-icon-wrap">
                <i class="fas fa-building input-icon"></i>
                <input type="text" name="company_name"
                  class="form-control input-with-icon <?php echo e($errors->has('company_name') ? 'is-invalid' : ''); ?>"
                  placeholder="VD: FPT Software, VNG Corporation..."
                  value="<?php echo e(old('company_name')); ?>" required>
              </div>
              <?php $__errorArgs = ['company_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div class="form-group">
              <label class="form-label">Mật khẩu <span class="required">*</span></label>
              <div class="input-icon-wrap">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" name="password" id="password"
                  class="form-control input-with-icon <?php echo e($errors->has('password') ? 'is-invalid' : ''); ?>"
                  placeholder="Tối thiểu 8 ký tự, gồm chữ và số"
                  required autocomplete="new-password">
                <button type="button" class="toggle-pass-btn" onclick="togglePass('password','eye1')">
                  <i class="fas fa-eye" id="eye1"></i>
                </button>
              </div>
              <div class="password-strength mt-6" id="strength-bar"></div>
              <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div class="form-group">
              <label class="form-label">Xác nhận mật khẩu <span class="required">*</span></label>
              <div class="input-icon-wrap">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" name="password_confirmation" id="password2"
                  class="form-control input-with-icon"
                  placeholder="Nhập lại mật khẩu"
                  required autocomplete="new-password">
                <button type="button" class="toggle-pass-btn" onclick="togglePass('password2','eye2')">
                  <i class="fas fa-eye" id="eye2"></i>
                </button>
              </div>
              <div id="match-msg" class="fs-11 mt-4" style="display:none"></div>
            </div>

            
            <div class="form-group">
              <label class="filter-option" style="font-size:13px;gap:8px;align-items:flex-start">
                <input type="checkbox" name="terms" id="terms-checkbox" value="1" <?php echo e(old('terms') ? 'checked' : ''); ?> required style="margin-top:2px">
                <span>Tôi đồng ý với <a href="<?php echo e(route('terms')); ?>" target="_blank" class="text-primary-color">Điều khoản sử dụng</a> và <a href="<?php echo e(route('privacy')); ?>" target="_blank" class="text-primary-color">Chính sách bảo mật</a> của ITWorks.</span>
              </label>
              <?php $__errorArgs = ['terms'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback" style="display:block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <button type="submit" id="submit-btn" disabled style="
              width:100%; padding:14px; margin-top:8px;
              background: linear-gradient(135deg, #e65100, #bf360c);
              color:#fff; border:none; border-radius:12px;
              font-size:15px; font-weight:700; cursor:not-allowed;
              display:flex; align-items:center; justify-content:center; gap:8px;
              transition: all .2s; box-shadow: 0 4px 14px rgba(230,81,0,.3);
              opacity: 0.5;
            ">
              <span class="btn-text"><i class="fas fa-user-plus"></i> Đăng ký</span>
              <span class="btn-loading" style="display:none"><i class="fas fa-spinner fa-spin"></i> Đang xử lý...</span>
            </button>

            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:4px">
              <a href="<?php echo e(route('auth.google.register', 'employer')); ?>" class="btn btn-google" style="justify-content:center">
                <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0">
                  <path d="M17.64 9.205c0-.639-.057-1.252-.164-1.841H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
                  <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
                  <path d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
                  <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
                </svg>
                Đăng ký với Google
              </a>
              <a href="<?php echo e(route('auth.github.register', 'employer')); ?>" class="btn" style="
                display:flex;align-items:center;justify-content:center;gap:8px;
                background:#24292e;color:#fff;border:none;border-radius:8px;
                font-size:14px;font-weight:500;padding:10px 16px;text-decoration:none;
              ">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
                Đăng ký với GitHub
              </a>
            </div>

          </div>
        </form>

      </div>
      <div class="card-footer text-center" style="padding:14px 28px">
        <span class="text-muted fs-13">Đã có tài khoản?</span>
        <a href="<?php echo e(route('login')); ?>" class="text-primary-color fw-600 fs-13 ml-4">Đăng nhập</a>
        <span class="text-muted fs-13 ml-8">•</span>
        <a href="<?php echo e(route('register')); ?>" class="text-muted fs-13 ml-8">Đổi loại tài khoản</a>
      </div>
    </div>

  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('auth._auth-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo $__env->make('auth._auth-scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\dl\website_timviec_v15 (1)\website_modified\resources\views/auth/register-employer.blade.php ENDPATH**/ ?>