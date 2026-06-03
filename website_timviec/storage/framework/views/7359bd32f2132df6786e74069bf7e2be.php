<?php $__env->startSection('title', $listing->title . ' — ITWorks'); ?>
<?php $__env->startSection('description', Str::limit(strip_tags($listing->description), 160)); ?>

<?php $__env->startSection('content'); ?>
<div class="container section">
  <div class="flex gap-24" style="align-items:flex-start">

    
    <div style="flex:1;min-width:0">

      
      <div class="card mb-16">
        <div class="card-body" style="padding:28px">
          <div class="flex gap-16" style="align-items:flex-start">
            <div style="width:80px;height:80px;border:1px solid var(--border);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;flex-shrink:0;background:#fafafa;overflow:hidden">
              <?php if($listing->feature_image): ?>
                <img src="<?php echo e(asset('storage/images/'.$listing->feature_image)); ?>" alt="" style="width:72px;height:72px;object-fit:contain">
              <?php else: ?>
                <i class="fas fa-building fa-2x" style="color:var(--primary)"></i>
              <?php endif; ?>
            </div>
            <div style="flex:1">
              <h1 style="font-size:22px;font-weight:800;color:var(--secondary);line-height:1.3"><?php echo e($listing->title); ?></h1>
              <div class="flex gap-16 mt-8 flex-wrap" style="font-size:13px;color:var(--text-secondary)">
                <span><i class="fas fa-building fa-fw"></i> <?php echo e($listing->user->company_name ?? $listing->user->name); ?></span>
                <span><i class="fas fa-map-marker-alt fa-fw"></i> <?php echo e($listing->address); ?></span>
                <span><i class="fas fa-clock fa-fw"></i> <?php echo e($listing->job_type); ?></span>
              </div>
              <div class="flex gap-8 mt-12 flex-wrap">
                <span class="tag tag-green fs-13" style="padding:5px 14px">
                  <i class="fas fa-money-bill-wave" style="margin-right:5px"></i>
                  <?php echo e($listing->salary == 0 ? 'Thỏa thuận' : number_format($listing->salary).' đ/tháng'); ?>

                </span>
                <span class="tag tag-blue fs-13" style="padding:5px 14px">
                  <i class="fas fa-calendar-times" style="margin-right:5px"></i>
                  Hết hạn: <?php echo e(\Carbon\Carbon::parse($listing->application_close_date)->format('d/m/Y')); ?>

                </span>
                <span class="tag tag-gray fs-13" style="padding:5px 14px">
                  <i class="fas fa-users" style="margin-right:5px"></i>
                  <?php echo e($listing->users->count()); ?> ứng viên
                </span>
              </div>
            </div>
          </div>

          <div class="divider mt-20"></div>

          
          <?php if(auth()->guard()->check()): ?>
            <?php if(auth()->user()->user_type === 'employee' && $existingApplication): ?>
              <?php
                $sl = \App\Models\Application::STATUS_LABELS[$existingApplication->status] ?? $existingApplication->status;
                $scMap = ['submitted'=>'#D48806','viewed'=>'var(--primary)','approved'=>'#4338ca','interviewing'=>'#4338ca','rejected'=>'var(--danger)'];
                $scColor = $scMap[$existingApplication->status] ?? 'var(--text-secondary)';
              ?>
              
              <div style="background:var(--bg-gray);border-radius:var(--radius-md);padding:12px 16px;margin-bottom:12px;font-size:13px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
                <div>
                  <span style="color:var(--text-secondary)"><i class="fas fa-check-circle fa-fw" style="color:var(--primary)"></i> Đã nộp ngày <?php echo e($existingApplication->applied_at->format('d/m/Y')); ?></span>
                  &nbsp;·&nbsp;
                  <span style="color:<?php echo e($scColor); ?>;font-weight:600"><?php echo e($sl); ?></span>
                </div>
                <a href="<?php echo e(route('candidate.application.detail', $existingApplication->id)); ?>" class="btn btn-outline btn-sm">
                  <i class="fas fa-file-alt fa-fw"></i> Xem đơn của tôi
                </a>
              </div>
          <?php endif; ?>
          <?php endif; ?>

          <div class="flex gap-10" style="align-items:center">
            <?php if(auth()->guard()->check()): ?>
              <?php if(auth()->user()->user_type === 'employee'): ?>
                <?php if($existingApplication): ?>
                  <a href="<?php echo e(route('apply.form', ['listingId' => $listing->id])); ?>" class="btn btn-outline btn-lg">
                    <i class="fas fa-redo fa-fw"></i> Ứng tuyển lại
                  </a>
                <?php else: ?>
                  <a href="<?php echo e(route('apply.form', ['listingId' => $listing->id])); ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-paper-plane fa-fw"></i> Ứng tuyển ngay
                  </a>
                <?php endif; ?>
              <?php endif; ?>
            <?php else: ?>
              <a href="<?php echo e(url('/login')); ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-paper-plane fa-fw"></i> Đăng nhập để ứng tuyển
              </a>
            <?php endif; ?>
            <button class="btn btn-outline" onclick="shareJob()"><i class="fas fa-share-alt"></i> Chia sẻ</button>
          </div>
        </div>
      </div>

      
      <div class="card mb-16">
        <div class="card-header"><span class="fw-700 fs-15">Mô tả công việc</span></div>
        <div class="card-body" style="padding:24px;line-height:1.8;font-size:14px">
          <?php echo nl2br(e($listing->description)); ?>

        </div>
      </div>

      
      <?php if($listing->roles): ?>
        <div class="card mb-16">
          <div class="card-header"><span class="fw-700 fs-15">Yêu cầu công việc</span></div>
          <div class="card-body" style="padding:24px;line-height:1.8;font-size:14px">
            <?php echo nl2br(e($listing->roles)); ?>

          </div>
        </div>
      <?php endif; ?>

      <?php if($listing->predes): ?>
        <div class="card">
          <div class="card-header"><span class="fw-700 fs-15">Mô tả thêm</span></div>
          <div class="card-body" style="padding:24px;line-height:1.8;font-size:14px">
            <?php echo nl2br(e($listing->predes)); ?>

          </div>
        </div>
      <?php endif; ?>
    </div>

    
    <div class="sidebar">
      
      <div class="sidebar-card">
        <div class="sidebar-card__title">Thông tin chung</div>
        <div class="sidebar-card__body">
          <div class="flex-col gap-12" style="font-size:13px">
            <div class="flex-between">
              <span class="text-muted"><i class="fas fa-briefcase fa-fw"></i> Loại hình</span>
              <span class="fw-600"><?php echo e($listing->job_type); ?></span>
            </div>
            <div class="divider" style="margin:0"></div>
            <div class="flex-between">
              <span class="text-muted"><i class="fas fa-map-marker-alt fa-fw"></i> Địa điểm</span>
              <span class="fw-600"><?php echo e($listing->address); ?></span>
            </div>
            <div class="divider" style="margin:0"></div>
            <div class="flex-between">
              <span class="text-muted"><i class="fas fa-money-bill fa-fw"></i> Mức lương</span>
              <span class="fw-600 text-primary-color"><?php echo e($listing->salary == 0 ? 'Thỏa thuận' : number_format($listing->salary).'đ'); ?></span>
            </div>
            <div class="divider" style="margin:0"></div>
            <div class="flex-between">
              <span class="text-muted"><i class="fas fa-calendar fa-fw"></i> Hết hạn</span>
              <span class="fw-600"><?php echo e(\Carbon\Carbon::parse($listing->application_close_date)->format('d/m/Y')); ?></span>
            </div>
          </div>
        </div>
      </div>

      
      <div class="sidebar-card">
        <div class="sidebar-card__title">Về nhà tuyển dụng</div>
        <div class="sidebar-card__body" style="text-align:center">
          <div style="width:64px;height:64px;background:var(--primary-light);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;margin:0 auto 10px;font-size:24px;color:var(--primary)">
            <i class="fas fa-building"></i>
          </div>
          <div class="fw-700 fs-14"><?php echo e($listing->user->company_name ?? $listing->user->name); ?></div>
          <?php if($listing->user->about): ?>
            <p class="text-muted fs-12 mt-8" style="line-height:1.6"><?php echo e(Str::limit($listing->user->about, 100)); ?></p>
          <?php endif; ?>
        </div>
      </div>

      
      <?php if(auth()->guard()->check()): ?>
        <?php if(auth()->user()->id === ($listing->user_id ?? ($listing->user->id ?? null))): ?>
          <div class="sidebar-card">
            <div class="sidebar-card__title">Quản lý tin đăng</div>
            <div class="sidebar-card__body">
              <a href="<?php echo e(url('/job/'.$listing->id.'/edit')); ?>" class="btn btn-outline btn-block mb-8"><i class="fas fa-edit"></i> Chỉnh sửa</a>
              <a href="<?php echo e(url('/applicants/'.$listing->slug)); ?>" class="btn btn-primary btn-block"><i class="fas fa-users"></i> Xem ứng viên</a>
            </div>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function shareJob() {
  if (navigator.share) {
    navigator.share({ title: '<?php echo e($listing->title); ?>', url: window.location.href });
  } else {
    navigator.clipboard.writeText(window.location.href);
    alert('Đã copy link vào clipboard!');
  }
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\web_timviec_fixed\web_timviec_fixed\website_timviec\resources\views/job/show.blade.php ENDPATH**/ ?>