<?php $__env->startSection('title', 'Xác thực Email — ITWorks'); ?>

<?php $__env->startSection('content'); ?>
<div class="auth-page">
  <div class="auth-container" style="max-width:480px">

    <div class="text-center mb-28">
      <a href="<?php echo e(url('/')); ?>" class="navbar-brand" style="font-size:28px;justify-content:center">IT<span>Works</span></a>
    </div>

    <div class="card" style="box-shadow:var(--shadow-lg)">
      <div class="card-body" style="padding:40px 32px;text-align:center">

        
        <div style="width:80px;height:80px;background:var(--primary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:32px;color:var(--primary)">
          <i class="fas fa-envelope-open-text"></i>
        </div>

        <h2 style="font-size:20px;font-weight:800;color:var(--secondary);margin-bottom:12px">
          Xác thực địa chỉ email
        </h2>

        <p class="text-muted" style="font-size:14px;line-height:1.7;margin-bottom:8px">
          Chúng tôi đã gửi email xác thực đến
          <strong style="color:var(--secondary)"><?php echo e(auth()->user()->email); ?></strong>.
        </p>
        <p class="text-muted" style="font-size:13px;line-height:1.6;margin-bottom:24px">
          Vui lòng kiểm tra hộp thư (kể cả thư mục <strong>Spam</strong>) và nhấn vào link xác thực để kích hoạt tài khoản.
        </p>

        
        <?php if(session('success')): ?>
          <div class="alert alert-success mb-20" style="text-align:left">
            <i class="fas fa-check-circle"></i> <?php echo e(session('success')); ?>

          </div>
        <?php endif; ?>
        <?php if(session('status')): ?>
          <div class="alert alert-info mb-20" style="text-align:left">
            <i class="fas fa-info-circle"></i> <?php echo e(session('status')); ?>

          </div>
        <?php endif; ?>

        
        <form action="<?php echo e(route('verification.send')); ?>" method="POST">
          <?php echo csrf_field(); ?>
          <button type="submit" class="btn btn-primary btn-block">
            <i class="fas fa-redo"></i> Gửi lại email xác thực
          </button>
        </form>

        
        <div style="background:#f8f9fa;border-radius:var(--radius);padding:16px;margin-top:24px;text-align:left">
          <div class="fw-600 fs-13 mb-10" style="color:var(--secondary)"><i class="fas fa-info-circle text-primary-color"></i> Không nhận được email?</div>
          <ol style="padding-left:18px;margin:0;font-size:12px;color:var(--text-muted);line-height:2">
            <li>Kiểm tra thư mục <strong>Spam / Junk</strong></li>
            <li>Đảm bảo địa chỉ email nhập đúng</li>
            <li>Chờ vài phút rồi thử lại</li>
            <li>Nhấn "Gửi lại" ở trên nếu vẫn chưa nhận được</li>
          </ol>
        </div>

        <div class="mt-20">
          <form action="<?php echo e(route('logout')); ?>" method="POST" style="display:inline">
            <?php echo csrf_field(); ?>
            <button type="submit" class="text-muted fs-13" style="background:none;border:none;cursor:pointer;text-decoration:underline">
              <i class="fas fa-sign-out-alt"></i> Đăng xuất
            </button>
          </form>
        </div>

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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\dl\website_timviec_v15 (1)\website_modified\resources\views/auth/verify-email.blade.php ENDPATH**/ ?>