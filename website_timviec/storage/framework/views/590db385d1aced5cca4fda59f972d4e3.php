<?php $__env->startSection('title', 'Đăng nhập'); ?>

<?php $__env->startSection('content'); ?>
<div style="min-height:calc(100vh - 60px);display:flex;align-items:center;background:linear-gradient(135deg,#f0fdf7 0%,#e8f4fd 100%);padding:40px 16px">
  <div style="width:100%;max-width:420px;margin:0 auto">

    
    <div class="text-center mb-24">
      <a href="<?php echo e(url('/')); ?>" class="navbar-brand" style="font-size:28px;justify-content:center">IT<span>Works</span></a>
      <p class="text-muted mt-8">Chào mừng trở lại! Đăng nhập để tiếp tục.</p>
    </div>

    
    <div class="card">
      <div class="card-body" style="padding:28px">
        <h2 style="font-size:20px;font-weight:800;margin-bottom:20px;color:var(--secondary)">Đăng nhập</h2>

        <?php if($errors->any()): ?>
          <div class="alert alert-danger mb-16">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo e($errors->first()); ?>

          </div>
        <?php endif; ?>

        <form action="<?php echo e(url('/login')); ?>" method="POST" id="login-form">
          <?php echo csrf_field(); ?>
          <div class="flex-col gap-16">

            <div class="form-group">
              <label class="form-label">Email <span class="required">*</span></label>
              <input type="email" name="email" class="form-control <?php echo e($errors->has('email') ? 'is-invalid' : ''); ?>"
                placeholder="your@email.com" value="<?php echo e(old('email')); ?>" required autofocus>
              <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
              <div class="flex-between">
                <label class="form-label">Mật khẩu <span class="required">*</span></label>
                <a href="<?php echo e(url('/forgot-password')); ?>" class="fs-12 text-primary-color">Quên mật khẩu?</a>
              </div>
              <div style="position:relative">
                <input type="password" name="password" id="password" class="form-control <?php echo e($errors->has('password') ? 'is-invalid' : ''); ?>"
                  placeholder="Nhập mật khẩu" required>
                <button type="button" onclick="togglePass()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:14px">
                  <i class="fas fa-eye" id="eye-icon"></i>
                </button>
              </div>
              <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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

        <a href="<?php echo e(url('/auth/google')); ?>" class="btn btn-outline btn-block" style="border-color:#dadce0;color:#333;font-size:14px">
          <img src="https://www.google.com/favicon.ico" style="width:16px;height:16px"> Đăng nhập với Google
        </a>
      </div>

      <div class="card-footer text-center">
        <span class="text-muted fs-13">Chưa có tài khoản?</span>
        <a href="<?php echo e(url('/register')); ?>" class="text-primary-color fw-600 fs-13"> Đăng ký ngay</a>
      </div>
    </div>
  </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function togglePass() {
  var p = document.getElementById('password');
  var i = document.getElementById('eye-icon');
  p.type = p.type === 'password' ? 'text' : 'password';
  i.className = p.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\web_timviec_fixed\web_timviec_fixed\website_timviec\resources\views/user/login.blade.php ENDPATH**/ ?>