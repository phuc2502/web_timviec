<?php $__env->startSection('title', 'Đăng tin tuyển dụng'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-20 fw-800" style="color:var(--secondary)">Đăng tin tuyển dụng</h1>
    <p class="text-muted fs-13 mt-4">Điền đầy đủ thông tin để thu hút ứng viên chất lượng</p>
  </div>
  <a href="<?php echo e(url('/job')); ?>" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Quay lại</a>
</div>

<form action="<?php echo e(url('/job/store')); ?>" method="POST" enctype="multipart/form-data">
  <?php echo csrf_field(); ?>
  <div class="grid" style="grid-template-columns:2fr 1fr;gap:20px;align-items:start">

    
    <div class="flex-col gap-16">
      <div class="card">
        <div class="card-header"><span class="fw-700 fs-15"><i class="fas fa-info-circle" style="color:var(--primary);margin-right:8px"></i>Thông tin cơ bản</span></div>
        <div class="card-body" style="padding:24px">
          <div class="flex-col gap-16">

            <div class="form-group">
              <label class="form-label">Tiêu đề tin tuyển dụng <span class="required">*</span></label>
              <input type="text" name="title" class="form-control <?php echo e($errors->has('title') ? 'is-invalid' : ''); ?>"
                placeholder="VD: Senior Backend Developer (Node.js)" value="<?php echo e(old('title')); ?>" required>
              <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
              <label class="form-label">Mô tả công việc <span class="required">*</span></label>
              <textarea name="description" id="description" class="form-control <?php echo e($errors->has('description') ? 'is-invalid' : ''); ?>"
                rows="8" placeholder="Mô tả chi tiết về công việc, dự án, môi trường làm việc..." required><?php echo e(old('description')); ?></textarea>
              <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
              <label class="form-label">Yêu cầu ứng viên <span class="required">*</span></label>
              <textarea name="roles" class="form-control <?php echo e($errors->has('roles') ? 'is-invalid' : ''); ?>"
                rows="6" placeholder="- Kinh nghiệm 2+ năm với Node.js&#10;- Thành thạo RESTful API&#10;- Biết Docker, CI/CD là lợi thế" required><?php echo e(old('roles')); ?></textarea>
              <?php $__errorArgs = ['roles'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
              <label class="form-label">Phúc lợi / Mô tả thêm</label>
              <textarea name="predes" class="form-control" rows="4"
                placeholder="- Lương thưởng cạnh tranh&#10;- 13 tháng lương&#10;- Review lương 2 lần/năm"><?php echo e(old('predes')); ?></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    
    <div class="flex-col gap-16">
      <div class="card">
        <div class="card-header"><span class="fw-700 fs-15"><i class="fas fa-cog" style="color:var(--primary);margin-right:8px"></i>Chi tiết tuyển dụng</span></div>
        <div class="card-body" style="padding:20px">
          <div class="flex-col gap-14">

            <div class="form-group">
              <label class="form-label">Mức lương (đ/tháng) <span class="required">*</span></label>
              <input type="number" name="salary" class="form-control <?php echo e($errors->has('salary') ? 'is-invalid' : ''); ?>"
                placeholder="0 = Thỏa thuận" value="<?php echo e(old('salary', 0)); ?>" min="0">
              <div class="form-hint">Nhập 0 nếu lương thỏa thuận</div>
              <?php $__errorArgs = ['salary'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
              <label class="form-label">Địa điểm làm việc <span class="required">*</span></label>
              <select name="address" class="form-control <?php echo e($errors->has('address') ? 'is-invalid' : ''); ?>" required>
                <option value="">-- Chọn địa điểm --</option>
                <?php $__currentLoopData = ['Hà Nội','Hồ Chí Minh','Đà Nẵng','Cần Thơ','Remote','Toàn quốc']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($loc); ?>" <?php echo e(old('address') == $loc ? 'selected' : ''); ?>><?php echo e($loc); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
              <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
              <label class="form-label">Loại hình công việc <span class="required">*</span></label>
              <select name="job_type" class="form-control <?php echo e($errors->has('job_type') ? 'is-invalid' : ''); ?>" required>
                <option value="">-- Chọn loại hình --</option>
                <?php $__currentLoopData = ['Full-time','Part-time','Remote','Freelance','Internship']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($type); ?>" <?php echo e(old('job_type') == $type ? 'selected' : ''); ?>><?php echo e($type); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
              <?php $__errorArgs = ['job_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
              <label class="form-label">Hạn nộp hồ sơ <span class="required">*</span></label>
              <input type="date" name="date" class="form-control <?php echo e($errors->has('date') ? 'is-invalid' : ''); ?>"
                value="<?php echo e(old('date')); ?>" min="<?php echo e(date('Y-m-d')); ?>" required>
              <?php $__errorArgs = ['date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
              <label class="form-label">Logo / Ảnh bìa <span class="required">*</span></label>
              <div id="upload-zone" style="border:2px dashed var(--border);border-radius:var(--radius-lg);padding:20px;text-align:center;cursor:pointer;transition:var(--transition)" onclick="document.getElementById('img-input').click()" ondragover="handleDragOver(event)" ondrop="handleDrop(event)">
                <img id="preview-img" src="" alt="" style="display:none;max-height:120px;margin:0 auto 8px;border-radius:var(--radius)">
                <div id="upload-placeholder">
                  <i class="fas fa-cloud-upload-alt fa-2x" style="color:var(--primary);margin-bottom:8px"></i>
                  <div class="fw-600 fs-13">Kéo thả hoặc click để tải ảnh</div>
                  <div class="text-muted fs-12 mt-4">PNG, JPG, GIF — Tối đa 2MB</div>
                </div>
              </div>
              <input type="file" id="img-input" name="feature_image" accept="image/*" style="display:none" onchange="previewImage(this)">
              <?php $__errorArgs = ['feature_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block btn-lg">
        <i class="fas fa-paper-plane"></i> Đăng tin ngay
      </button>
      <p class="text-center text-muted fs-12">Tin đăng sẽ được hiển thị ngay sau khi gửi</p>
    </div>
  </div>
</form>

<?php $__env->startPush('scripts'); ?>
<script>
function previewImage(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('preview-img').src = e.target.result;
      document.getElementById('preview-img').style.display = 'block';
      document.getElementById('upload-placeholder').style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
  }
}
function handleDragOver(e) { e.preventDefault(); document.getElementById('upload-zone').style.borderColor = 'var(--primary)'; }
function handleDrop(e) {
  e.preventDefault();
  var files = e.dataTransfer.files;
  if (files.length) { document.getElementById('img-input').files = files; previewImage(document.getElementById('img-input')); }
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\web_timviec_final\website_timviec\resources\views/job/create.blade.php ENDPATH**/ ?>