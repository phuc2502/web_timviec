<?php $__env->startSection('title', 'Trạng thái gói Premium'); ?>

<?php $__env->startSection('content'); ?>
<div class="container section" style="max-width:640px">

  <div class="mb-24">
    <h1 class="fw-700 fs-24" style="color:var(--secondary)">👑 Trạng thái gói Premium</h1>
  </div>

  <?php if(session('success')): ?>
    <div class="alert alert-success mb-16">✅ <?php echo e(session('success')); ?></div>
  <?php endif; ?>

  <?php if($status['has_active']): ?>
    
    <div class="card">
      <div style="height:4px;background:var(--primary);border-radius:var(--radius-lg) var(--radius-lg) 0 0"></div>
      <div class="card-body" style="padding:32px">
        <div class="flex gap-16 mb-24" style="align-items:center">
          <div style="width:56px;height:56px;border-radius:50%;background:var(--primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="fas fa-crown" style="font-size:24px;color:var(--primary)"></i>
          </div>
          <div>
            <div class="fw-700 fs-20" style="color:var(--secondary)">Gói đang hoạt động</div>
            <div class="fs-13 text-muted mt-8">Tài khoản đang được hưởng đầy đủ tính năng Premium</div>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px">
          <div style="background:var(--bg-gray);border-radius:var(--radius-md);padding:16px">
            <div class="text-muted fs-12 mb-8">Loại gói</div>
            <div class="fw-700"><?php echo e(ucfirst($status['plan'])); ?></div>
          </div>
          <div style="background:var(--bg-gray);border-radius:var(--radius-md);padding:16px">
            <div class="text-muted fs-12 mb-8">Trạng thái</div>
            <span class="badge badge-primary"><i class="fas fa-circle fa-fw" style="font-size:8px"></i> Đang hoạt động</span>
          </div>
          <div style="background:var(--bg-gray);border-radius:var(--radius-md);padding:16px">
            <div class="text-muted fs-12 mb-8">Ngày hết hạn</div>
            <div class="fw-700"><?php echo e($status['billing_ends']); ?></div>
          </div>
          <div style="background:var(--bg-gray);border-radius:var(--radius-md);padding:16px">
            <div class="text-muted fs-12 mb-8">Còn lại</div>
            <div class="fw-700" style="color:var(--primary)"><?php echo e($status['days_remaining']); ?> ngày</div>
          </div>
        </div>

        
        <?php
          $totalDays = $status['plan'] === 'yearly' ? 365 : 30;
          $pct = max(5, min(100, round($status['days_remaining'] / $totalDays * 100)));
        ?>
        <div>
          <div class="flex-between mb-8" style="font-size:12px;color:var(--text-secondary)">
            <span>Thời gian sử dụng</span>
            <span class="fw-600" style="color:var(--primary)"><?php echo e($pct); ?>% còn lại</span>
          </div>
          <div style="height:8px;background:var(--border);border-radius:4px;overflow:hidden">
            <div style="height:100%;width:<?php echo e($pct); ?>%;background:var(--primary);border-radius:4px;transition:width .5s ease"></div>
          </div>
        </div>
      </div>
    </div>

  <?php else: ?>
    
    <div class="card">
      <div class="card-body text-center" style="padding:48px 32px">
        <div style="font-size:56px;margin-bottom:16px">
          <?php echo e($status['status'] === 'expired' ? '⏰' : '💡'); ?>

        </div>
        <div class="fw-700 fs-20 mb-8" style="color:var(--secondary)">
          <?php echo e($status['status'] === 'expired' ? 'Gói đã hết hạn' : 'Chưa có gói Premium'); ?>

        </div>

        <?php if($status['plan']): ?>
          <p class="text-muted fs-13 mb-8">
            Gói cuối: <strong><?php echo e(ucfirst($status['plan'])); ?></strong> — hết hạn ngày <?php echo e($status['billing_ends']); ?>

          </p>
        <?php endif; ?>

        <p class="text-muted fs-13 mb-24">Nâng cấp ngay để đăng tin và quản lý ứng viên không giới hạn.</p>

        <a href="<?php echo e(route('payment.subscription')); ?>" class="btn btn-primary btn-lg">
          <i class="fas fa-crown fa-fw"></i>
          <?php echo e($status['status'] === 'expired' ? 'Gia hạn gói Premium' : 'Mua gói Premium'); ?>

        </a>
      </div>
    </div>
  <?php endif; ?>

  <div class="text-center mt-16">
    <a href="<?php echo e(route('dashboard')); ?>" class="text-muted fs-13">
      <i class="fas fa-arrow-left fa-fw"></i> Về Dashboard
    </a>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\web_timviec_final\website_timviec\resources\views/payment/subscription-status.blade.php ENDPATH**/ ?>