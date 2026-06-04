<?php $__env->startSection('title', 'Gói Premium Nhà tuyển dụng'); ?>

<?php $__env->startSection('content'); ?>
<div class="container section" style="max-width:860px">

  <div class="mb-24">
    <h1 class="fw-700 fs-24" style="color:var(--secondary)">👑 Gói Premium cho Nhà tuyển dụng</h1>
    <p class="text-muted fs-13 mt-8">Đăng tin không giới hạn và tiếp cận ứng viên chất lượng cao</p>
  </div>

  <?php if(session('error')): ?>
    <div class="alert alert-danger mb-16">⚠️ <?php echo e(session('error')); ?></div>
  <?php endif; ?>

  <?php if($status['has_active']): ?>
    
    <div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:2px solid #16a34a;border-radius:14px;padding:20px 24px;margin-bottom:28px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
      <div style="display:flex;align-items:center;gap:14px">
        <div style="width:48px;height:48px;background:#16a34a;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <i class="fas fa-crown" style="color:#fff;font-size:20px"></i>
        </div>
        <div>
          <div class="fw-700 fs-15" style="color:#14532d">
            <i class="fas fa-check-circle fa-fw" style="color:#16a34a"></i>
            Đang sử dụng gói <span style="text-transform:capitalize"><?php echo e($status['plan']); ?></span>
          </div>
          <div class="fs-13" style="color:#166534;margin-top:4px">
            Hết hạn: <strong><?php echo e($status['billing_ends']); ?></strong>
            &nbsp;·&nbsp;
            Còn <strong style="color:#15803d"><?php echo e($status['days_remaining']); ?> ngày</strong>
          </div>
        </div>
      </div>
      <a href="<?php echo e(route('employer.subscription.status')); ?>"
         class="btn btn-sm fw-600"
         style="background:#16a34a;color:#fff;border:none;white-space:nowrap;flex-shrink:0">
        <i class="fas fa-chart-bar fa-fw"></i> Xem chi tiết
      </a>
    </div>
    <div style="background:#fefce8;border:1.5px solid #fbbf24;border-radius:10px;padding:12px 16px;margin-bottom:24px;display:flex;align-items:center;gap:10px">
      <i class="fas fa-info-circle" style="color:#d97706;font-size:15px;flex-shrink:0"></i>
      <span class="fs-13" style="color:#92400e">Bạn đang có gói đang hoạt động. Có thể mua gia hạn sau khi gói hiện tại hết hạn.</span>
    </div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">

    
    <div style="border:2px solid var(--border);border-radius:var(--radius-xl);overflow:hidden;background:#fff;box-shadow:var(--shadow-md)">
      <div style="height:6px;background:var(--text-secondary)"></div>
      <div style="padding:28px">
        <div style="font-size:36px;margin-bottom:12px">📅</div>
        <div class="fw-700 fs-20" style="color:var(--secondary)">Gói Tháng</div>
        <div style="margin:12px 0">
          <span class="fw-700" style="font-size:32px;color:var(--primary)">299.000</span>
          <span class="text-muted fs-13">đ / tháng</span>
        </div>
        <div style="border-top:1px solid var(--border);padding-top:16px;margin-top:16px">
          <?php $__currentLoopData = ['Đăng tin tuyển dụng không giới hạn','Xem hồ sơ ứng viên đầy đủ','Quản lý ứng viên nâng cao','Hỗ trợ ưu tiên']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="fs-13 mb-8" style="display:flex;align-items:center;gap:8px">
              <i class="fas fa-check-circle" style="color:var(--primary)"></i> <?php echo e($feature); ?>

            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php if(!$status['has_active']): ?>
          <form action="<?php echo e(route('payment.subscription.initiate')); ?>" method="POST" class="mt-16">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="plan" value="monthly">
            <button type="submit" class="btn btn-outline btn-block">Mua gói tháng →</button>
          </form>
        <?php endif; ?>
      </div>
    </div>

    
    <div style="border:2px solid var(--primary);border-radius:var(--radius-xl);overflow:hidden;background:#fff;box-shadow:var(--shadow-lg);position:relative">
      <div style="background:var(--primary);color:#fff;text-align:center;font-size:12px;font-weight:600;padding:6px;letter-spacing:.5px">
        🔥 TIẾT KIỆM 17% — PHỔ BIẾN NHẤT
      </div>
      <div style="padding:28px">
        <div style="font-size:36px;margin-bottom:12px">🌟</div>
        <div class="fw-700 fs-20" style="color:var(--secondary)">Gói Năm</div>
        <div style="margin:12px 0">
          <span class="fw-700" style="font-size:32px;color:var(--primary)">2.990.000</span>
          <span class="text-muted fs-13">đ / năm</span>
        </div>
        <div class="fs-12 text-muted mb-8">≈ 249.167đ/tháng</div>
        <div style="border-top:1px solid var(--border);padding-top:16px;margin-top:16px">
          <?php $__currentLoopData = ['Tất cả tính năng gói Tháng','Ưu tiên hiển thị tin tuyển dụng','Báo cáo & thống kê chi tiết','Huy hiệu nhà tuyển dụng uy tín']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="fs-13 mb-8" style="display:flex;align-items:center;gap:8px">
              <i class="fas fa-check-circle" style="color:var(--primary)"></i> <?php echo e($feature); ?>

            </div>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php if(!$status['has_active']): ?>
          <form action="<?php echo e(route('payment.subscription.initiate')); ?>" method="POST" class="mt-16">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="plan" value="yearly">
            <button type="submit" class="btn btn-primary btn-block">Mua gói năm →</button>
          </form>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <div class="text-center mt-24" style="color:var(--text-secondary);font-size:12px">
    <i class="fas fa-lock fa-fw" style="color:var(--primary)"></i>
    Thanh toán an toàn qua <strong>VNPay</strong>. Gói được kích hoạt ngay sau khi thanh toán thành công.
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\web_timviec_fixed\web_timviec_fixed\web_timviec_fixed\website_timviec\resources\views/payment/subscription.blade.php ENDPATH**/ ?>