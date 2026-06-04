<?php $__env->startSection('title', 'Quản lý ứng viên'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-20 fw-800" style="color:var(--secondary)">Quản lý ứng viên</h1>
    <p class="text-muted fs-13 mt-4">Danh sách tin đăng và số lượng ứng viên</p>
  </div>
</div>

<?php if($listings->isEmpty()): ?>
  <div class="card text-center" style="padding:56px 24px">
    <div style="font-size:48px;margin-bottom:14px">👥</div>
    <div class="fw-700 fs-16">Chưa có tin tuyển dụng nào</div>
    <a href="<?php echo e(url('/job/create')); ?>" class="btn btn-primary mt-16" style="display:inline-flex"><i class="fas fa-plus"></i> Đăng tin ngay</a>
  </div>
<?php else: ?>
  <div class="flex-col gap-12">
    <?php $__currentLoopData = $listings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $listing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="card">
        <div class="card-body" style="padding:20px">
          <div class="flex gap-16" style="align-items:center">
            <div style="flex:1">
              <a href="<?php echo e(url('/job/show/'.$listing->slug)); ?>" class="fw-700 fs-15" style="color:var(--secondary)" target="_blank">
                <?php echo e($listing->title); ?>

              </a>
              <div class="flex gap-12 mt-6" style="font-size:12px;color:var(--text-muted)">
                <span><i class="fas fa-map-marker-alt fa-fw"></i><?php echo e($listing->address); ?></span>
                <span><i class="fas fa-clock fa-fw"></i>Hết hạn: <?php echo e(\Carbon\Carbon::parse($listing->application_close_date)->format('d/m/Y')); ?></span>
                <?php if(\Carbon\Carbon::parse($listing->application_close_date)->isPast()): ?>
                  <span class="status status-closed">Đã hết hạn</span>
                <?php else: ?>
                  <span class="status status-open">Đang mở</span>
                <?php endif; ?>
              </div>
            </div>
            <div class="text-center" style="flex-shrink:0">
              <div class="fw-800 fs-24" style="color:var(--primary)"><?php echo e($listing->users->count()); ?></div>
              <div class="text-muted fs-12">ứng viên</div>
            </div>
            <div style="flex-shrink:0">
              <a href="<?php echo e(url('/applicants/'.$listing->slug)); ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-users"></i> Xem ứng viên
              </a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\dl\website_timviec_v15 (1)\website_timviec_v15 (1)\website_timviec\resources\views/applicants/index.blade.php ENDPATH**/ ?>