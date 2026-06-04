<?php $__env->startSection('title', 'Quên mật khẩu — ITWorks'); ?>

<?php $__env->startSection('content'); ?>
<div class="auth-page">
  <div class="auth-container">

    <div class="text-center mb-28">
      <a href="<?php echo e(url('/')); ?>" class="navbar-brand" style="font-size:28px;justify-content:center">IT<span>Works</span></a>
      <p class="text-muted mt-8 fs-13">Nhập email để nhận link đặt lại mật khẩu</p>
    </div>

    <div class="card" style="box-shadow:var(--shadow-lg)">
      <div class="card-body" style="padding:32px">

        <div class="text-center mb-24">
          <div style="width:60px;height:60px;background:var(--primary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:24px;color:var(--primary)">
            <i class="fas fa-key"></i>
          </div>
          <h2 style="font-size:20px;font-weight:800;color:var(--secondary)">Quên mật khẩu?</h2>
          <p class="text-muted fs-13 mt-6">Không sao! Nhập email đã đăng ký, chúng tôi sẽ gửi link đặt lại ngay.</p>
        </div>

        <?php if(session('status')): ?>
          <div class="alert alert-success mb-20">
            <i class="fas fa-check-circle"></i> <?php echo e(session('status')); ?>

          </div>
        <?php endif; ?>

        <form action="<?php echo e(route('password.email')); ?>" method="POST" id="forgot-form">
          <?php echo csrf_field(); ?>

          <div class="form-group mb-20">
            <label class="form-label">Địa chỉ email <span class="required">*</span></label>
            <div style="position:relative">
              <i class="fas fa-envelope" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px;pointer-events:none"></i>
              <input type="email" name="email"
                class="form-control <?php echo e($errors->has('email') ? 'is-invalid' : ''); ?>"
                style="padding-left:36px"
                placeholder="your@email.com"
                value="<?php echo e(old('email')); ?>"
                required autofocus>
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

          <button type="submit" class="btn btn-primary btn-block btn-lg" id="send-btn">
            <span class="btn-text"><i class="fas fa-paper-plane"></i> Gửi link đặt lại mật khẩu</span>
            <span class="btn-loading" style="display:none"><i class="fas fa-spinner fa-spin"></i> Đang gửi...</span>
          </button>
        </form>

      </div>
      <div class="card-footer text-center" style="padding:14px 32px">
        <a href="<?php echo e(route('login')); ?>" class="text-muted fs-13">
          <i class="fas fa-arrow-left"></i> Quay lại đăng nhập
        </a>
      </div>
    </div>

  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.auth-page {
  min-height: calc(100vh - 60px);
  display: flex; align-items: center;
  background: linear-gradient(135deg, #f0fdf7 0%, #e8f4fd 100%);
  padding: 40px 16px;
}
.auth-container { width: 100%; max-width: 420px; margin: 0 auto; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('forgot-form').addEventListener('submit', function () {
  const btn = document.getElementById('send-btn');
  btn.querySelector('.btn-text').style.display = 'none';
  btn.querySelector('.btn-loading').style.display = 'inline';
  btn.disabled = true;
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\dl\website_timviec_v13_social_verified\website_output\resources\views/auth/forgot-password.blade.php ENDPATH**/ ?>