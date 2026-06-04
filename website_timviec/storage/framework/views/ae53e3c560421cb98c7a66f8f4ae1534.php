<?php $__env->startSection('title', 'Tìm việc làm IT — ITWorks'); ?>
<?php $__env->startSection('description', 'Hàng nghìn việc làm IT đang chờ bạn. Lập trình viên, DevOps, Data, AI...'); ?>

<?php $__env->startSection('content'); ?>


<section class="hero">
  <div class="container">
    <div style="max-width:700px">
      <h1>Nền tảng tuyển dụng IT<br><span style="color:#7effc4">hàng đầu Việt Nam</span></h1>
      <p>Kết nối <strong><?php echo e($totalJobs ?? '2,500+'); ?></strong> việc làm IT với hàng nghìn kỹ sư công nghệ xuất sắc</p>

      
      <form action="<?php echo e(url('/job')); ?>" method="GET">
        <div class="search-bar">
          <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="🔍 Tìm việc: Backend, Frontend, DevOps...">
          <div class="search-bar__divider"></div>
          <select name="address" style="padding:14px 14px;border:none;font-size:14px;font-family:inherit;color:var(--text-secondary);background:transparent;cursor:pointer;min-width:150px">
            <option value="">📍 Tất cả địa điểm</option>
            <option value="Hà Nội" <?php echo e(request('address') == 'Hà Nội' ? 'selected' : ''); ?>>Hà Nội</option>
            <option value="Hồ Chí Minh" <?php echo e(request('address') == 'Hồ Chí Minh' ? 'selected' : ''); ?>>Hồ Chí Minh</option>
            <option value="Đà Nẵng" <?php echo e(request('address') == 'Đà Nẵng' ? 'selected' : ''); ?>>Đà Nẵng</option>
            <option value="Remote" <?php echo e(request('address') == 'Remote' ? 'selected' : ''); ?>>Remote</option>
          </select>
          <button type="submit" class="btn btn-primary">Tìm kiếm</button>
        </div>
      </form>

      <div class="hero-stats">
        <div class="hero-stats__item"><div class="hero-stats__num"><?php echo e($totalJobs ?? '2.5K+'); ?></div><div class="hero-stats__label">Việc làm</div></div>
        <div class="hero-stats__item"><div class="hero-stats__num">500+</div><div class="hero-stats__label">Công ty</div></div>
        <div class="hero-stats__item"><div class="hero-stats__num">10K+</div><div class="hero-stats__label">Ứng viên</div></div>
      </div>
    </div>
  </div>
</section>


<div style="background:#fff;border-bottom:1px solid var(--border)">
  <div class="container" style="padding:14px 16px;display:flex;gap:8px;overflow-x:auto;-webkit-overflow-scrolling:touch">
    <?php $__currentLoopData = ['Backend', 'Frontend', 'Mobile', 'DevOps', 'Data / AI', 'QA/Tester', 'UI/UX', 'Blockchain', 'Game']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <a href="<?php echo e(url('/job?search='.urlencode($cat))); ?>" class="tag <?php echo e(request('search') == $cat ? 'tag-green' : 'tag-gray'); ?>" style="white-space:nowrap;font-size:13px;padding:6px 14px">
        <?php echo e($cat); ?>

      </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
</div>


<div class="container section">
  <div class="flex gap-24" style="align-items:flex-start">

    
    <aside class="sidebar" style="display:none" id="sidebar">
      <form action="<?php echo e(url('/job')); ?>" method="GET">
        <input type="hidden" name="search" value="<?php echo e(request('search')); ?>">

        <div class="sidebar-card">
          <div class="sidebar-card__title"><i class="fas fa-filter" style="color:var(--primary);margin-right:6px"></i>Lọc kết quả</div>
          <div class="sidebar-card__body">
            
            <div class="filter-group">
              <div class="filter-group__label">Loại hình công việc</div>
              <?php $__currentLoopData = ['Full-time','Part-time','Remote','Freelance','Internship']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="filter-option">
                  <input type="radio" name="job_type" value="<?php echo e($type); ?>" <?php echo e(request('job_type') == $type ? 'checked' : ''); ?>>
                  <?php echo e($type); ?>

                </label>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="divider"></div>
            
            <div class="filter-group">
              <div class="filter-group__label">Mức lương</div>
              <?php $__currentLoopData = ['Thỏa Thuận','Dưới 5 triệu','5 - 10 triệu','10 - 15 triệu','Trên 15 triệu']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $range): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="filter-option">
                  <input type="radio" name="salary_range" value="<?php echo e($range); ?>" <?php echo e(request('salary_range') == $range ? 'checked' : ''); ?>>
                  <?php echo e($range); ?>

                </label>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="divider"></div>
            
            <div class="filter-group">
              <div class="filter-group__label">Địa điểm</div>
              <?php $__currentLoopData = ['Hà Nội','Hồ Chí Minh','Đà Nẵng','Remote']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="filter-option">
                  <input type="radio" name="address" value="<?php echo e($loc); ?>" <?php echo e(request('address') == $loc ? 'checked' : ''); ?>>
                  <?php echo e($loc); ?>

                </label>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <button type="submit" class="btn btn-primary btn-block mt-12">Áp dụng</button>
            <a href="<?php echo e(url('/')); ?>" class="btn btn-outline btn-block mt-8" style="font-size:13px">Xoá lọc</a>
          </div>
        </div>
      </form>
    </aside>

    
    <div style="flex:1;min-width:0">
      <div class="flex-between mb-16">
        <div>
          <span class="fw-700 fs-16" style="color:var(--secondary)">Việc làm mới nhất</span>
          <span class="text-muted fs-13 ml-8">(<?php echo e(method_exists($listings, 'total') ? $listings->total() : $listings->count()); ?> kết quả)</span>
        </div>
        <div class="flex gap-8">
          <select class="form-control" style="width:auto;padding:6px 12px;font-size:13px" onchange="location='?sort='+this.value">
            <option value="newest">Mới nhất</option>
            <option value="salary">Lương cao nhất</option>
          </select>
          <button onclick="document.getElementById('sidebar').style.display=document.getElementById('sidebar').style.display==='none'?'block':'none'" class="btn btn-outline btn-sm"><i class="fas fa-filter"></i> Lọc</button>
        </div>
      </div>

      <?php $__empty_1 = true; $__currentLoopData = $listings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $listing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="job-card mb-12">
          <div class="job-card__header">
            <div class="job-card__logo" style="display:flex;align-items:center;justify-content:center;font-size:20px;color:var(--primary)">
              <?php if($listing->feature_image): ?>
                <img src="<?php echo e(asset('storage/images/'.$listing->feature_image)); ?>" alt="<?php echo e($listing->title); ?>" style="width:48px;height:48px;object-fit:contain">
              <?php else: ?>
                <i class="fas fa-building"></i>
              <?php endif; ?>
            </div>
            <div class="job-card__info">
              <a href="<?php echo e(url('/job/show/'.$listing->slug)); ?>" class="job-card__title"><?php echo e($listing->title); ?></a>
              <div class="job-card__company">
                <i class="fas fa-building fa-fw" style="color:var(--text-muted)"></i>
                <?php echo e($listing->user->company_name ?? $listing->user->name); ?>

              </div>
            </div>
            <?php if(auth()->guard()->check()): ?>
              <button class="btn btn-outline btn-sm" style="flex-shrink:0" onclick="saveJob(<?php echo e($listing->id); ?>)">
                <i class="far fa-bookmark"></i>
              </button>
            <?php endif; ?>
          </div>

          <div class="job-card__tags">
            <span class="tag tag-green"><i class="fas fa-money-bill-wave" style="margin-right:4px"></i>
              <?php echo e($listing->salary == 0 ? 'Thỏa thuận' : number_format($listing->salary).' đ'); ?>

            </span>
            <span class="tag tag-blue"><i class="fas fa-map-marker-alt" style="margin-right:4px"></i><?php echo e($listing->address); ?></span>
            <span class="tag tag-gray"><?php echo e($listing->job_type); ?></span>
          </div>

          <div class="job-card__footer">
            <span class="job-card__deadline">
              <i class="fas fa-clock fa-fw"></i> Hết hạn: <?php echo e(\Carbon\Carbon::parse($listing->application_close_date)->format('d/m/Y')); ?>

            </span>
            <a href="<?php echo e(url('/job/show/'.$listing->slug)); ?>" class="btn btn-primary btn-sm">Xem chi tiết</a>
          </div>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="card text-center" style="padding:48px 24px">
          <div style="font-size:48px;margin-bottom:12px">🔍</div>
          <div class="fw-700 fs-16">Không tìm thấy việc làm nào</div>
          <p class="text-muted mt-8 fs-13">Thử tìm với từ khóa khác hoặc xoá bộ lọc</p>
          <a href="<?php echo e(url('/')); ?>" class="btn btn-primary mt-16" style="display:inline-flex">Xem tất cả việc làm</a>
        </div>
      <?php endif; ?>

      
      <?php if(isset($listings) && method_exists($listings, 'hasPages') && $listings->hasPages()): ?>
        <div class="flex-center mt-24">
          <div class="pagination">
            <?php if($listings->onFirstPage()): ?>
              <span class="disabled"><i class="fas fa-chevron-left"></i></span>
            <?php else: ?>
              <a href="<?php echo e($listings->previousPageUrl()); ?>"><i class="fas fa-chevron-left"></i></a>
            <?php endif; ?>

            <?php $__currentLoopData = $listings->getUrlRange(1, $listings->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php if($page == $listings->currentPage()): ?>
                <span class="active"><?php echo e($page); ?></span>
              <?php else: ?>
                <a href="<?php echo e($url); ?>"><?php echo e($page); ?></a>
              <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($listings->hasMorePages()): ?>
              <a href="<?php echo e($listings->nextPageUrl()); ?>"><i class="fas fa-chevron-right"></i></a>
            <?php else: ?>
              <span class="disabled"><i class="fas fa-chevron-right"></i></span>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\dl\website_timviec_v15 (1)\website_timviec_v15 (1)\website_modified\resources\views/job/index.blade.php ENDPATH**/ ?>