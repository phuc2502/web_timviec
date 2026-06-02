<?php $__env->startSection('title', 'Ứng tuyển — ' . ($listing->title ?? 'Công việc')); ?>

<?php $__env->startSection('content'); ?>
<div class="container section" style="max-width:720px">

  
  <div class="flex gap-8 mb-16" style="align-items:center;font-size:13px;color:var(--text-secondary)">
    <a href="<?php echo e(url('/job')); ?>" style="color:var(--text-secondary)">Tìm việc</a>
    <i class="fas fa-chevron-right" style="font-size:10px"></i>
    <a href="<?php echo e(url('/job/show/'.$listing->slug)); ?>" style="color:var(--text-secondary)"><?php echo e($listing->title); ?></a>
    <i class="fas fa-chevron-right" style="font-size:10px"></i>
    <span style="color:var(--text-dark)">Ứng tuyển</span>
  </div>

  
  <div class="card mb-16" style="background:linear-gradient(135deg,#f0fdf4,#e8f8ee);border:1px solid var(--primary)">
    <div class="card-body" style="padding:16px 20px">
      <div class="flex gap-12" style="align-items:center">
        <div style="width:44px;height:44px;border-radius:var(--radius-md);background:#fff;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <?php if($listing->feature_image): ?>
            <img src="<?php echo e(asset('storage/images/'.$listing->feature_image)); ?>" style="width:36px;height:36px;object-fit:contain">
          <?php else: ?>
            <i class="fas fa-building" style="color:var(--primary)"></i>
          <?php endif; ?>
        </div>
        <div style="flex:1">
          <div class="fw-700" style="color:var(--secondary);font-size:15px"><?php echo e($listing->title); ?></div>
          <div class="fs-13 text-muted">
            <?php echo e($listing->user->company_name ?? $listing->user->name); ?>

            &nbsp;·&nbsp; <?php echo e($listing->address); ?>

          </div>
        </div>
      </div>
    </div>
  </div>

  
  <?php
    $tokenRecord = \App\Models\UserToken::where('user_id', auth()->id())->first();
    $balance = $tokenRecord?->balance ?? 0;
  ?>
  <div class="card mb-16" style="border:1px solid <?php echo e($balance > 0 ? 'var(--primary)' : 'var(--danger)'); ?>;background:<?php echo e($balance > 0 ? 'var(--primary-light)' : '#FFF2EE'); ?>">
    <div class="card-body" style="padding:14px 20px">
      <div class="flex-between">
        <div class="flex gap-12" style="align-items:center">
          <div style="width:40px;height:40px;border-radius:50%;background:<?php echo e($balance > 0 ? 'var(--primary)' : 'var(--danger)'); ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <i class="fas fa-ticket-alt" style="color:#fff;font-size:16px"></i>
          </div>
          <div>
            <div class="fw-700" style="color:<?php echo e($balance > 0 ? 'var(--primary-dark)' : 'var(--danger)'); ?>;font-size:15px">
              <?php echo e($balance); ?> lượt ứng tuyển còn lại
            </div>
            <div class="fs-12" style="color:<?php echo e($balance > 0 ? 'var(--text-secondary)' : 'var(--danger)'); ?>;margin-top:2px">
              <?php if($balance > 0): ?>
                Ứng tuyển sẽ trừ <strong>1 lượt</strong>
              <?php else: ?>
                Bạn đã hết lượt — cần mua thêm để ứng tuyển
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php if($balance === 0): ?>
          <a href="<?php echo e(route('payment.token')); ?>" class="btn btn-danger btn-sm">
            <i class="fas fa-plus fa-fw"></i> Mua ngay
          </a>
        <?php else: ?>
          <a href="<?php echo e(route('payment.token')); ?>" class="btn btn-outline btn-sm" style="border-color:var(--primary);color:var(--primary)">
            <i class="fas fa-shopping-cart fa-fw"></i> Mua thêm
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if(session('error')): ?>
    <div class="alert alert-danger mb-16"><?php echo e(session('error')); ?></div>
  <?php endif; ?>

  
  <div class="card">
    <div class="card-header">
      <span class="fw-700 fs-16"><i class="fas fa-file-alt fa-fw" style="color:var(--primary)"></i> Nộp đơn ứng tuyển</span>
    </div>
    <div class="card-body">
      <form action="<?php echo e(route('apply.submit')); ?>" method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="listing_id" value="<?php echo e($listingId); ?>">

        
        <div class="form-group">
          <label class="form-label">CV của bạn <span class="text-danger">*</span></label>

          <?php if($suggestedCv): ?>
            <div style="border:2px solid var(--primary);border-radius:var(--radius-md);padding:14px 16px;background:var(--primary-light);margin-bottom:12px">
              <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
                <input type="radio" name="cv_source" value="existing" checked style="accent-color:var(--primary);width:16px;height:16px">
                <div>
                  <div class="fw-600 fs-13" style="color:var(--primary-dark)">
                    <i class="fas fa-check-circle fa-fw"></i> Dùng CV đã tải lên
                  </div>
                  <div class="fs-12 text-muted mt-8"><?php echo e($suggestedCv->original_name); ?></div>
                </div>
              </label>
              <input type="hidden" name="cv_id" value="<?php echo e($suggestedCv->id); ?>">
            </div>
          <?php endif; ?>

          <div style="border:2px dashed var(--border);border-radius:var(--radius-md);padding:20px;background:var(--bg-gray)">
            <?php if($suggestedCv): ?>
              <label style="display:flex;align-items:center;gap:10px;cursor:pointer;justify-content:center;margin-bottom:12px">
                <input type="radio" name="cv_source" value="new" style="accent-color:var(--primary);width:16px;height:16px">
                <span class="fw-600 fs-13"><i class="fas fa-upload fa-fw" style="color:var(--primary)"></i> Upload CV mới</span>
              </label>
            <?php else: ?>
              <div class="fw-600 fs-13 mb-8 text-center">
                <i class="fas fa-upload fa-fw" style="color:var(--primary)"></i> Upload CV
              </div>
            <?php endif; ?>
            <input type="file" name="cv_file" accept=".pdf,.doc,.docx" class="form-control" style="max-width:380px;margin:0 auto">
            <div class="fs-12 text-muted mt-8 text-center">Chấp nhận PDF, DOC, DOCX · Tối đa 5MB</div>
            <?php $__errorArgs = ['cv_file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger fs-12 mt-8"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <?php $__errorArgs = ['cv_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>   <div class="text-danger fs-12 mt-8"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
          </div>

          
          <div style="margin-top:12px;padding:14px 16px;border-radius:var(--radius-md);background:linear-gradient(135deg,#1B2B4B,#2d4a7a);display:flex;align-items:center;justify-content:space-between;gap:12px">
            <div>
              <div class="fw-600" style="color:#fff;font-size:13px">
                <i class="fas fa-magic fa-fw" style="color:var(--pro-gold)"></i>
                Tạo CV chuyên nghiệp ngay trên hệ thống
              </div>
              <div class="fs-12" style="color:rgba(255,255,255,0.7);margin-top:3px">
                Miễn phí · Đẹp · Tải PDF ngay lập tức
              </div>
            </div>
            <a href="<?php echo e(route('user.cv.create')); ?>" target="_blank"
               style="background:var(--pro-gold);color:var(--pro-navy);padding:8px 14px;border-radius:var(--radius-md);font-size:12px;font-weight:700;white-space:nowrap;text-decoration:none;flex-shrink:0">
              Tạo CV Online <i class="fas fa-arrow-right fa-fw"></i>
            </a>
          </div>
        </div>

        
        <div class="form-group">
          <label class="form-label">Thư xin việc <span class="text-muted fs-12">(tùy chọn)</span></label>
          <textarea name="cover_letter" rows="5" maxlength="3000"
            placeholder="Giới thiệu bản thân và lý do bạn phù hợp với vị trí này..."
            class="form-control" style="resize:vertical"><?php echo e(old('cover_letter')); ?></textarea>
          <?php $__errorArgs = ['cover_letter'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger fs-12 mt-8"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        
        <div class="form-group" style="margin-bottom:24px">
          <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer">
            <input type="checkbox" name="is_agreed_terms" value="1"
              style="margin-top:3px;accent-color:var(--primary);width:16px;height:16px"
              <?php echo e(old('is_agreed_terms') ? 'checked' : ''); ?>>
            <span class="fs-13" style="color:var(--text-body)">
              Tôi đồng ý với <a href="#" style="color:var(--primary)">điều khoản sử dụng</a>
              và xác nhận thông tin trong CV là chính xác. <span class="text-danger">*</span>
            </span>
          </label>
          <?php $__errorArgs = ['is_agreed_terms'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger fs-12 mt-8"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg"
          <?php echo e($balance === 0 ? 'disabled' : ''); ?>

          style="<?php echo e($balance === 0 ? 'opacity:.5;cursor:not-allowed' : ''); ?>">
          <i class="fas fa-paper-plane fa-fw"></i>
          <?php echo e($balance === 0 ? 'Hết lượt ứng tuyển' : 'Nộp đơn ứng tuyển (tốn 1 lượt)'); ?>

        </button>
      </form>
    </div>
  </div>

  <div class="text-center mt-16">
    <a href="<?php echo e(url('/job/show/'.$listing->slug)); ?>" class="text-muted fs-13">
      <i class="fas fa-arrow-left fa-fw"></i> Quay lại chi tiết công việc
    </a>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\web_timviec_final\website_timviec\resources\views/application/form.blade.php ENDPATH**/ ?>