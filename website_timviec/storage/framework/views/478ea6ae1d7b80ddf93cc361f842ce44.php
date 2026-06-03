<?php $__env->startSection('title', 'Danh sách ứng viên'); ?>

<?php $__env->startSection('content'); ?>
<div class="container section">

  <div class="flex-between mb-24">
    <div>
      <h1 class="fw-700 fs-24" style="color:var(--secondary)">👥 Danh sách ứng viên</h1>
      <p class="text-muted fs-13 mt-8">Xem và quản lý hồ sơ ứng tuyển</p>
    </div>
    <a href="<?php echo e(route('job.manage')); ?>" class="btn btn-outline btn-sm">
      <i class="fas fa-arrow-left fa-fw"></i> Về quản lý việc làm
    </a>
  </div>

  <?php if($applications->isEmpty()): ?>
    <div class="card">
      <div class="card-body text-center" style="padding:64px 24px">
        <div style="font-size:56px;margin-bottom:16px">📭</div>
        <div class="fw-700 fs-16 mb-8">Chưa có ứng viên nào nộp đơn</div>
        <p class="text-muted fs-13">Hãy chia sẻ tin tuyển dụng để thu hút ứng viên!</p>
      </div>
    </div>
  <?php else: ?>
    <div class="card">
      <div class="card-body" style="padding:0">
        <table style="width:100%;border-collapse:collapse">
          <thead>
            <tr style="border-bottom:2px solid var(--border);background:var(--bg-gray)">
              <th style="padding:12px 20px;text-align:left;font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px">Ứng viên</th>
              <th style="padding:12px 20px;text-align:left;font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px">File CV</th>
              <th style="padding:12px 20px;text-align:left;font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px">Ngày nộp</th>
              <th style="padding:12px 20px;text-align:left;font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px">Trạng thái</th>
              <th style="padding:12px 20px;text-align:center;font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px">Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php $__currentLoopData = $applications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $app): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php
                $statusConfig = [
                  'submitted'    => ['label'=>'Đã nộp',       'class'=>'badge-warning'],
                  'viewed'       => ['label'=>'Đã xem',       'class'=>'', 'style'=>'background:#E6F4FF;color:#096DD9'],
                  'approved'     => ['label'=>'Duyệt hồ sơ',  'class'=>'', 'style'=>'background:#f0f0ff;color:#4338ca'],
                  'interviewing' => ['label'=>'Phỏng vấn',    'class'=>'badge-primary'],
                  'rejected'     => ['label'=>'Chưa phù hợp', 'class'=>'badge-danger'],
                ];
                $s = $statusConfig[$app->status] ?? ['label'=>$app->status,'class'=>''];
              ?>
              <tr style="border-bottom:1px solid var(--border)" onmouseover="this.style.background='var(--bg-gray)'" onmouseout="this.style.background=''">
                <td style="padding:14px 20px">
                  <div class="fw-600" style="color:var(--text-dark)"><?php echo e($app->user->name); ?></div>
                  <div class="fs-12 text-muted"><?php echo e($app->user->email); ?></div>
                </td>
                <td style="padding:14px 20px;font-size:13px;color:var(--text-secondary)">
                  <i class="fas fa-paperclip fa-fw"></i> <?php echo e($app->cv->original_name ?? '—'); ?>

                </td>
                <td style="padding:14px 20px;font-size:13px;color:var(--text-secondary)">
                  <?php echo e($app->applied_at->format('d/m/Y H:i')); ?>

                </td>
                <td style="padding:14px 20px">
                  <span class="badge <?php echo e($s['class'] ?? ''); ?>" style="<?php echo e($s['style'] ?? ''); ?>">
                    <?php echo e($s['label']); ?>

                  </span>
                </td>
                <td style="padding:14px 20px;text-align:center">
                  <a href="<?php echo e(route('employer.application.detail', $app->id)); ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-eye fa-fw"></i> Xem CV
                  </a>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="mt-24"><?php echo e($applications->links()); ?></div>
  <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\web_timviec_fixed\web_timviec_fixed\website_timviec\resources\views/application/applicant-list.blade.php ENDPATH**/ ?>