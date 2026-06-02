<?php use Illuminate\Support\Facades\Storage; ?>


<?php $__env->startSection('title', 'Chi tiết đơn ứng tuyển'); ?>

<?php $__env->startSection('content'); ?>
<div class="container section" style="max-width:760px">

  <div class="flex-between mb-24">
    <div>
      <h1 class="fw-700 fs-22" style="color:var(--secondary)">📄 Chi tiết đơn ứng tuyển</h1>
      <p class="text-muted fs-13 mt-8"><?php echo e($application->listing->title); ?></p>
    </div>
    <a href="<?php echo e(route('candidate.history')); ?>" class="btn btn-outline btn-sm">
      <i class="fas fa-arrow-left fa-fw"></i> Về lịch sử
    </a>
  </div>

  
  <?php
    $statusConfig = [
      'submitted'    => ['label'=>'Đã nộp',    'bg'=>'#FFF7E6','border'=>'#FAAD14','color'=>'#D48806','icon'=>'fa-paper-plane'],
      'viewed'       => ['label'=>'Đã xem',    'bg'=>'#E6F4FF','border'=>'#1890FF','color'=>'#096DD9','icon'=>'fa-eye'],
      'interviewing' => ['label'=>'Phỏng vấn', 'bg'=>'#F0F0FF','border'=>'#6366f1','color'=>'#4338ca','icon'=>'fa-calendar-check'],
      'accepted'     => ['label'=>'Đã nhận',   'bg'=>'var(--primary-light)','border'=>'var(--primary)','color'=>'var(--primary-dark)','icon'=>'fa-check-circle'],
      'rejected'     => ['label'=>'Từ chối',   'bg'=>'#FFF2EE','border'=>'var(--danger)','color'=>'var(--danger)','icon'=>'fa-times-circle'],
    ];
    $s = $statusConfig[$application->status] ?? ['label'=>$application->status,'bg'=>'#f5f5f5','border'=>'var(--border)','color'=>'var(--text-secondary)','icon'=>'fa-circle'];
  ?>

  <div style="border:2px solid <?php echo e($s['border']); ?>;border-radius:var(--radius-lg);background:<?php echo e($s['bg']); ?>;padding:16px 20px;margin-bottom:20px">
    <div class="flex-between">
      <div class="flex gap-12" style="align-items:center">
        <i class="fas <?php echo e($s['icon']); ?> fa-lg" style="color:<?php echo e($s['color']); ?>"></i>
        <div>
          <div class="fw-700" style="color:<?php echo e($s['color']); ?>;font-size:15px"><?php echo e($s['label']); ?></div>
          <?php if($application->status_updated_at): ?>
            <div class="fs-12" style="color:var(--text-secondary);margin-top:2px">
              Cập nhật <?php echo e($application->status_updated_at->format('d/m/Y H:i')); ?>

            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="fs-12 text-muted">
        Nộp ngày <?php echo e($application->applied_at->format('d/m/Y H:i')); ?>

      </div>
    </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:16px">

    
    <div class="card">
      <div class="card-header"><span class="fw-700">🏢 Thông tin công việc đã ứng tuyển</span></div>
      <div class="card-body">
        <div class="flex gap-14" style="align-items:center">
          <div style="width:52px;height:52px;border-radius:var(--radius-md);border:1px solid var(--border);background:#fafafa;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="fas fa-building fa-lg" style="color:var(--primary)"></i>
          </div>
          <div>
            <div class="fw-700" style="color:var(--secondary);font-size:16px"><?php echo e($application->listing->title); ?></div>
            <div class="fs-13 text-muted mt-8">
              <?php echo e($application->listing->user->company_name ?? $application->listing->user->name); ?>

              &nbsp;·&nbsp; <?php echo e($application->listing->address); ?>

            </div>
            <?php if($application->listing->salary): ?>
              <div class="fs-13 mt-8" style="color:var(--primary);font-weight:600">
                <i class="fas fa-money-bill-wave fa-fw"></i>
                <?php echo e(number_format($application->listing->salary)); ?>đ/tháng
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border)">
          <a href="<?php echo e(url('/job/show/'.$application->listing->slug)); ?>" class="btn btn-outline btn-sm" target="_blank">
            <i class="fas fa-external-link-alt fa-fw"></i> Xem tin tuyển dụng
          </a>
        </div>
      </div>
    </div>

    
    <?php if($application->cv): ?>
      <div class="card">
        <div class="card-header"><span class="fw-700">📎 CV đã nộp</span></div>
        <div class="card-body">
          <div class="flex gap-12" style="align-items:center">
            <div style="width:44px;height:44px;border-radius:var(--radius-md);background:#FFF2EE;display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <i class="fas fa-file-alt fa-lg" style="color:var(--danger)"></i>
            </div>
            <div style="flex:1">
              <div class="fw-600"><?php echo e($application->cv->original_name); ?></div>
              <div class="text-muted fs-12 mt-8">File CV đã nộp</div>
            </div>
            <a href="<?php echo e(Storage::url($application->cv->file_path)); ?>" target="_blank" class="btn btn-outline btn-sm">
              <i class="fas fa-download fa-fw"></i> Tải xuống
            </a>
          </div>
        </div>
      </div>
    <?php endif; ?>

    
    <?php if($application->cover_letter): ?>
      <div class="card">
        <div class="card-header"><span class="fw-700">✉️ Thư giới thiệu đã gửi</span></div>
        <div class="card-body">
          <p style="font-size:14px;line-height:1.8;color:var(--text-body);white-space:pre-line"><?php echo e($application->cover_letter); ?></p>
        </div>
      </div>
    <?php else: ?>
      <div class="card">
        <div class="card-body" style="text-align:center;padding:24px;color:var(--text-secondary)">
          <i class="fas fa-envelope-open fa-2x mb-8" style="opacity:.3"></i>
          <p class="fs-13">Bạn không gửi kèm thư giới thiệu cho đơn này.</p>
        </div>
      </div>
    <?php endif; ?>

  </div>

  
  <?php if(!in_array($application->status, ['accepted', 'rejected'])): ?>
    <div class="text-center mt-20">
      <a href="<?php echo e(route('apply.form', ['listingId' => $application->listing_id])); ?>" class="btn btn-outline">
        <i class="fas fa-redo fa-fw"></i> Ứng tuyển lại / Cập nhật hồ sơ
      </a>
    </div>
  <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\web_timviec_final\website_timviec\resources\views/application/candidate-detail.blade.php ENDPATH**/ ?>