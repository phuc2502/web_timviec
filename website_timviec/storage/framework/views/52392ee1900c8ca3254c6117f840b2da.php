<?php $__env->startSection('title', 'Lịch sử ứng tuyển'); ?>

<?php $__env->startSection('content'); ?>
<div class="container section">

  <div class="flex-between mb-24">
    <div>
      <h1 class="fw-700 fs-24" style="color:var(--secondary)">📂 Lịch sử ứng tuyển</h1>
      <p class="text-muted fs-13 mt-8">Theo dõi trạng thái tất cả đơn bạn đã nộp</p>
    </div>
    <?php
      $tokenRecord = \App\Models\UserToken::where('user_id', auth()->id())->first();
      $balance = $tokenRecord?->balance ?? 0;
    ?>
    <div class="flex gap-8" style="align-items:center">
      <div style="background:var(--primary-light);color:var(--primary-dark);padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600">
        <i class="fas fa-ticket-alt fa-fw"></i> <?php echo e($balance); ?> lượt còn lại
      </div>
      <a href="<?php echo e(route('payment.token')); ?>" class="btn btn-outline btn-sm">Mua thêm</a>
    </div>
  </div>

  <?php if(session('success')): ?>
    <div class="alert alert-success mb-16">✅ <?php echo e(session('success')); ?></div>
  <?php endif; ?>

  <?php if($applications->isEmpty()): ?>
    <div class="card">
      <div class="card-body text-center" style="padding:64px 24px">
        <div style="font-size:56px;margin-bottom:16px">📭</div>
        <div class="fw-700 fs-16 mb-8">Bạn chưa ứng tuyển công việc nào</div>
        <p class="text-muted fs-13 mb-16">Hãy tìm và ứng tuyển những công việc phù hợp ngay!</p>
        <a href="<?php echo e(url('/job')); ?>" class="btn btn-primary">
          <i class="fas fa-search fa-fw"></i> Tìm việc ngay
        </a>
      </div>
    </div>
  <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:12px">
      <?php $__currentLoopData = $applications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $app): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
          $statusConfig = [
            'submitted'    => ['label'=>'Đã nộp',    'color'=>'#D48806','bg'=>'#FFF7E6','icon'=>'fa-paper-plane'],
            'viewed'       => ['label'=>'Đã xem',    'color'=>'#096DD9','bg'=>'#E6F4FF','icon'=>'fa-eye'],
            'interviewing' => ['label'=>'Phỏng vấn', 'color'=>'#4338ca','bg'=>'#F0F0FF','icon'=>'fa-calendar-check'],
            'accepted'     => ['label'=>'Đã nhận',   'color'=>'var(--primary-dark)','bg'=>'var(--primary-light)','icon'=>'fa-check-circle'],
            'rejected'     => ['label'=>'Từ chối',   'color'=>'var(--danger)','bg'=>'#FFF2EE','icon'=>'fa-times-circle'],
          ];
          $s = $statusConfig[$app->status] ?? ['label'=>$app->status,'color'=>'var(--text-secondary)','bg'=>'var(--bg-gray)','icon'=>'fa-circle'];
        ?>
        <div class="card" style="transition:var(--transition)"
          onmouseover="this.style.boxShadow='var(--shadow-lg)'"
          onmouseout="this.style.boxShadow=''">
          <div class="card-body" style="padding:16px 20px">
            <div class="flex gap-16" style="align-items:center">

              
              <div style="width:48px;height:48px;border-radius:var(--radius-md);border:1px solid var(--border);background:#fafafa;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas fa-building" style="color:var(--primary)"></i>
              </div>

              
              <div style="flex:1;min-width:0">
                <div class="fw-700" style="color:var(--secondary);font-size:15px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                  <?php echo e($app->listing->title ?? 'Công việc đã xóa'); ?>

                </div>
                <div class="fs-13 text-muted mt-8">
                  <i class="fas fa-building fa-fw"></i>
                  <?php echo e($app->listing->user->company_name ?? $app->listing->user->name ?? '—'); ?>

                  &nbsp;·&nbsp;
                  <i class="fas fa-map-marker-alt fa-fw"></i>
                  <?php echo e($app->listing->address ?? '—'); ?>

                  &nbsp;·&nbsp;
                  <i class="fas fa-calendar fa-fw"></i>
                  Nộp <?php echo e($app->applied_at->format('d/m/Y')); ?>

                </div>
              </div>

              
              <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;flex-shrink:0">
                <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;background:<?php echo e($s['bg']); ?>;color:<?php echo e($s['color']); ?>">
                  <i class="fas <?php echo e($s['icon']); ?> fa-fw"></i> <?php echo e($s['label']); ?>

                </span>
                <div class="flex gap-8">
                  
                  <a href="<?php echo e(route('candidate.application.detail', $app->id)); ?>"
                     class="btn btn-outline btn-sm" style="font-size:12px;padding:4px 10px">
                    <i class="fas fa-file-alt fa-fw"></i> Đơn của tôi
                  </a>
                  
                  <?php if($app->listing): ?>
                    <a href="<?php echo e(url('/job/show/'.$app->listing->slug)); ?>"
                       class="btn btn-outline btn-sm" style="font-size:12px;padding:4px 10px" target="_blank">
                      <i class="fas fa-briefcase fa-fw"></i> Xem việc làm
                    </a>
                  <?php endif; ?>
                </div>
              </div>

            </div>
          </div>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="mt-24"><?php echo e($applications->links()); ?></div>
  <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\web_timviec_final\website_timviec\resources\views/application/history.blade.php ENDPATH**/ ?>