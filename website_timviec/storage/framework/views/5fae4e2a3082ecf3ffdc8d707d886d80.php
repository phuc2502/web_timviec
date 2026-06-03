<?php $__env->startSection('title', 'Đăng ký Nhà tuyển dụng'); ?>

<?php $__env->startSection('content'); ?>
<div style="min-height:calc(100vh - 60px);display:flex;align-items:center;background:linear-gradient(135deg,#f0fdf7 0%,#e8f4fd 100%);padding:40px 16px">
  <div style="width:100%;max-width:520px;margin:0 auto">

    <div class="text-center mb-24">
      <a href="<?php echo e(url('/')); ?>" class="navbar-brand" style="font-size:28px;justify-content:center">IT<span>Works</span></a>
      <h1 style="font-size:20px;font-weight:800;margin-top:12px;color:var(--secondary)">Đăng ký tài khoản Nhà tuyển dụng</h1>
      <p class="text-muted mt-4 fs-13">Dùng thử miễn phí 7 ngày — Không cần thẻ tín dụng</p>
    </div>

    <div class="alert alert-info mb-16" style="font-size:13px">
      <i class="fas fa-gift"></i> <strong>Ưu đãi:</strong> Đăng ký ngay, nhận 7 ngày dùng thử Premium miễn phí!
    </div>

    <div class="card">
      <div class="card-body" style="padding:28px">
        <?php if($errors->any()): ?>
          <div class="alert alert-danger mb-16">
            <i class="fas fa-exclamation-circle"></i> <?php echo e($errors->first()); ?>

          </div>
        <?php endif; ?>

        <form action="<?php echo e(url('/register/employer')); ?>" method="POST">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="user_type" value="employer">
          <div class="flex-col gap-14">

            <div class="grid-2" style="gap:12px">
              <div class="form-group">
                <label class="form-label">Họ tên <span class="required">*</span></label>
                <input type="text" name="name" class="form-control <?php echo e($errors->has('name') ? 'is-invalid' : ''); ?>"
                  placeholder="Nguyễn Văn A" value="<?php echo e(old('name')); ?>" required>
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
                <input type="email" name="email" class="form-control <?php echo e($errors->has('email') ? 'is-invalid' : ''); ?>"
                  placeholder="hr@company.com" value="<?php echo e(old('email')); ?>" required>
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
              <input type="text" name="company_name" class="form-control <?php echo e($errors->has('company_name') ? 'is-invalid' : ''); ?>"
                placeholder="VD: FPT Software, VNG Corporation..." value="<?php echo e(old('company_name')); ?>">
              <?php $__errorArgs = ['company_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="grid-2" style="gap:12px">
              <div class="form-group">
                <label class="form-label">Mật khẩu <span class="required">*</span></label>
                <div style="position:relative">
                  <input type="password" name="password" id="password" class="form-control <?php echo e($errors->has('password') ? 'is-invalid' : ''); ?>"
                    placeholder="Tối thiểu 8 ký tự" required>
                  <button type="button" onclick="togglePass('password','eye1')" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);color:var(--text-muted)">
                    <i class="fas fa-eye" id="eye1"></i>
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
              <div class="form-group">
                <label class="form-label">Xác nhận mật khẩu <span class="required">*</span></label>
                <div style="position:relative">
                  <input type="password" name="password_confirmation" id="password2" class="form-control" placeholder="Nhập lại" required>
                  <button type="button" onclick="togglePass('password2','eye2')" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);color:var(--text-muted)">
                    <i class="fas fa-eye" id="eye2"></i>
                  </button>
                </div>
              </div>
            </div>

            <label class="filter-option" style="font-size:13px">
              <input type="checkbox" required>
              Tôi đồng ý với <a href="#" class="text-primary-color">Điều khoản</a> và <a href="#" class="text-primary-color">Chính sách bảo mật</a>
            </label>

            <button type="submit" class="btn btn-secondary btn-block btn-lg">
              <i class="fas fa-building"></i> Đăng ký nhà tuyển dụng
            </button>
          </div>
        </form>
      </div>
      <div class="card-footer text-center">
        <a href="<?php echo e(url('/register')); ?>" class="fs-12 text-muted"><i class="fas fa-arrow-left"></i> Chọn lại</a>
        <span class="text-muted fs-12 mx-8">|</span>
        <span class="text-muted fs-12">Đã có tài khoản? <a href="<?php echo e(url('/login')); ?>" class="text-primary-color fw-600">Đăng nhập</a></span>
      </div>
    </div>
  </div>
</div>
<?php $__env->startPush('scripts'); ?>
<script>
function togglePass(id, iconId) {
  var p = document.getElementById(id); var i = document.getElementById(iconId);
  p.type = p.type === 'password' ? 'text' : 'password';
  i.className = p.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\web_timviec_final\website_timviec\resources\views/user/employer-register.blade.php ENDPATH**/ ?>