<?php $__env->startSection('title', 'Khôi phục mật khẩu — ITWorks'); ?>

<?php $__env->startPush('styles'); ?>
<?php echo $__env->make('auth._auth-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="auth-page">
  <div class="auth-container" style="max-width:460px">

    
    <div class="forgot-hero">
      <div class="forgot-icon-wrap">
        <i class="fas fa-key"></i>
      </div>
      <a href="<?php echo e(url('/')); ?>" class="navbar-brand" style="font-size:28px;justify-content:center">IT<span>Works</span></a>
    </div>

    
    <div class="forgot-card">

      
      <div class="forgot-card-header">
        <h1 style="font-size:20px;font-weight:800;color:#0f172a;margin:0 0 6px">Khôi phục mật khẩu</h1>
        <p style="font-size:13px;color:#64748b;margin:0;line-height:1.6">
          Nhập email đã đăng ký — chúng tôi sẽ gửi link đặt lại mật khẩu ngay lập tức.
        </p>
      </div>

      
      <div class="forgot-card-body">

        
        <?php if(session('info')): ?>
          <div class="alert-logout">
            <i class="fas fa-sign-out-alt"></i>
            <span><?php echo e(session('info')); ?></span>
          </div>
        <?php endif; ?>

        
        <?php if(session('status')): ?>
          <div class="alert-success-custom">
            <i class="fas fa-check-circle"></i>
            <div>
              <strong style="display:block;margin-bottom:2px">Email đã được gửi!</strong>
              <?php echo e(session('status')); ?>

            </div>
          </div>
        <?php endif; ?>

        
        <form action="<?php echo e(route('password.email')); ?>" method="POST" id="forgot-form">
          <?php echo csrf_field(); ?>

          <div style="margin-bottom:20px">
            <label class="form-label" style="font-weight:600;margin-bottom:8px;display:block">
              Địa chỉ email <span style="color:#ef4444">*</span>
            </label>
            <div class="input-wrap">
              <i class="fas fa-envelope input-icon"></i>
              <input type="email" name="email"
                class="form-control <?php echo e($errors->has('email') ? 'is-invalid' : ''); ?>"
                placeholder="your@email.com"
                value="<?php echo e(old('email')); ?>"
                required autofocus
                style="border-radius:12px;padding:12px 14px 12px 42px;font-size:14px">
            </div>
            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
              <div class="invalid-feedback" style="margin-top:6px;font-size:12px">
                <i class="fas fa-exclamation-circle"></i> <?php echo e($message); ?>

              </div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>

          <button type="submit" class="btn-send" id="send-btn">
            <span class="btn-text"><i class="fas fa-paper-plane"></i> Gửi link khôi phục</span>
            <span class="btn-loading" style="display:none"><i class="fas fa-spinner fa-spin"></i> Đang gửi...</span>
          </button>
        </form>

        
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

      
      <div class="forgot-card-footer">
        <a href="<?php echo e(route('login')); ?>" style="font-size:13px;color:#64748b;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:.2s"
           onmouseover="this.style.color='#10b981'" onmouseout="this.style.color='#64748b'">
          <i class="fas fa-arrow-left"></i> Quay lại đăng nhập
        </a>
        <span style="margin:0 12px;color:#e2e8f0">|</span>
        <a href="<?php echo e(route('register')); ?>" style="font-size:13px;color:#64748b;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:.2s"
           onmouseover="this.style.color='#10b981'" onmouseout="this.style.color='#64748b'">
          Tạo tài khoản mới <i class="fas fa-arrow-right"></i>
        </a>
      </div>

    </div>

  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('forgot-form').addEventListener('submit', function () {
  const btn = document.getElementById('send-btn');
  btn.querySelector('.btn-text').style.display = 'none';
  btn.querySelector('.btn-loading').style.display = 'inline-flex';
  btn.disabled = true;
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\dl\website_timviec_v15 (1)\website_timviec_v15 (1)\website_timviec\resources\views/auth/forgot-password.blade.php ENDPATH**/ ?>