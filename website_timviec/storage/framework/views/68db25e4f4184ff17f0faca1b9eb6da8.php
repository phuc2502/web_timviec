<?php $__env->startSection('title', 'Đặt lại mật khẩu — ITWorks'); ?>

<?php $__env->startSection('content'); ?>
<div class="auth-page">
  <div class="auth-container">

    <div class="text-center mb-28">
      <a href="<?php echo e(url('/')); ?>" class="navbar-brand" style="font-size:28px;justify-content:center">IT<span>Works</span></a>
      <p class="text-muted mt-8 fs-13">Tạo mật khẩu mới cho tài khoản của bạn</p>
    </div>

    <div class="card" style="box-shadow:var(--shadow-lg)">
      <div class="card-body" style="padding:32px">

        <div class="text-center mb-24">
          <div style="width:60px;height:60px;background:var(--primary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:24px;color:var(--primary)">
            <i class="fas fa-lock-open"></i>
          </div>
          <h2 style="font-size:20px;font-weight:800;color:var(--secondary)">Đặt lại mật khẩu</h2>
        </div>

        <form action="<?php echo e(route('password.update')); ?>" method="POST" id="reset-form">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="token" value="<?php echo e($token); ?>">

          <div style="display:flex;flex-direction:column;gap:16px">

            
            <div class="form-group">
              <label class="form-label">Email</label>
              <div style="position:relative">
                <i class="fas fa-envelope" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px;pointer-events:none"></i>
                <input type="email" name="email"
                  class="form-control <?php echo e($errors->has('email') ? 'is-invalid' : ''); ?>"
                  style="padding-left:36px"
                  value="<?php echo e($email ?? old('email')); ?>"
                  required readonly>
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

            
            <div class="form-group">
              <label class="form-label">Mật khẩu mới <span class="required">*</span></label>
              <div style="position:relative">
                <i class="fas fa-lock" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px;pointer-events:none"></i>
                <input type="password" name="password" id="password"
                  class="form-control <?php echo e($errors->has('password') ? 'is-invalid' : ''); ?>"
                  style="padding-left:36px;padding-right:40px"
                  placeholder="Tối thiểu 8 ký tự, gồm chữ và số"
                  required autocomplete="new-password">
                <button type="button" onclick="togglePass('password','eye1')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);background:none;border:none;cursor:pointer;padding:4px">
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
              <label class="form-label">Xác nhận mật khẩu mới <span class="required">*</span></label>
              <div style="position:relative">
                <i class="fas fa-lock" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:13px;pointer-events:none"></i>
                <input type="password" name="password_confirmation" id="password2"
                  class="form-control"
                  style="padding-left:36px;padding-right:40px"
                  placeholder="Nhập lại mật khẩu mới"
                  required autocomplete="new-password">
                <button type="button" onclick="togglePass('password2','eye2')" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);background:none;border:none;cursor:pointer;padding:4px">
                  <i class="fas fa-eye" id="eye2"></i>
                </button>
              </div>
              <div id="match-msg" class="fs-11 mt-4" style="display:none"></div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg mt-4" id="reset-btn">
              <span class="btn-text"><i class="fas fa-save"></i> Cập nhật mật khẩu</span>
              <span class="btn-loading" style="display:none"><i class="fas fa-spinner fa-spin"></i> Đang xử lý...</span>
            </button>

          </div>
        </form>

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
.password-strength {
  height: 4px; border-radius: 2px; background: #e9ecef; overflow: hidden;
}
.password-strength::after {
  content: ''; display: block; height: 100%;
  width: var(--strength, 0%); background: var(--strength-color, #e9ecef);
  transition: width .3s, background .3s; border-radius: 2px;
}
.fs-11 { font-size: 11px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function togglePass(i, e) {
  const p = document.getElementById(i), ic = document.getElementById(e);
  p.type = p.type === 'password' ? 'text' : 'password';
  ic.className = p.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

const pwdInput = document.getElementById('password');
const strengthBar = document.getElementById('strength-bar');
pwdInput.addEventListener('input', function () {
  const v = this.value;
  let s = 0;
  if (v.length >= 8) s++;
  if (/[A-Z]/.test(v)) s++;
  if (/[0-9]/.test(v)) s++;
  if (/[^A-Za-z0-9]/.test(v)) s++;
  const lvs = [
    { p: '0%', c: '#e9ecef' }, { p: '25%', c: '#dc3545' },
    { p: '50%', c: '#fd7e14' }, { p: '75%', c: '#ffc107' },
    { p: '100%', c: '#28a745' }
  ];
  const lv = lvs[s] || lvs[0];
  strengthBar.style.setProperty('--strength', lv.p);
  strengthBar.style.setProperty('--strength-color', lv.c);
});

const pwd2 = document.getElementById('password2');
const matchMsg = document.getElementById('match-msg');
pwd2.addEventListener('input', function () {
  const match = this.value === pwdInput.value;
  matchMsg.style.display = this.value ? 'block' : 'none';
  matchMsg.style.color = match ? '#28a745' : '#dc3545';
  matchMsg.innerHTML = match
    ? '<i class="fas fa-check-circle"></i> Mật khẩu khớp'
    : '<i class="fas fa-times-circle"></i> Mật khẩu chưa khớp';
});

document.getElementById('reset-form').addEventListener('submit', function () {
  const btn = document.getElementById('reset-btn');
  btn.querySelector('.btn-text').style.display = 'none';
  btn.querySelector('.btn-loading').style.display = 'inline';
  btn.disabled = true;
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\dl\website_timviec_v15 (1)\website_timviec_v15 (1)\website_timviec\resources\views/auth/reset-password.blade.php ENDPATH**/ ?>