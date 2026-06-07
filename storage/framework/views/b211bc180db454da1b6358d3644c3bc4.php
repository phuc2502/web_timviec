<?php $__env->startSection('title', 'Lịch sử giao dịch thanh toán'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-18 fw-800" style="color:var(--secondary)">Quản lý giao dịch</h1>
    <p class="text-muted fs-13 mt-2">Tổng số: <strong><?php echo e($transactions->total()); ?></strong> giao dịch thanh toán Premium qua VNPay</p>
  </div>
  
  
  <form action="<?php echo e(url('/admin/transactions')); ?>" method="GET">
    <div style="display:flex; gap:8px; align-items: center;">
      <input type="text" name="search" class="form-control" style="width:220px; font-size:13px;" placeholder="Mã GD, tên, email..." value="<?php echo e(request('search')); ?>">
      
      <select name="plan" class="form-control" style="width:130px; font-size:13px; cursor:pointer;">
        <option value="">Tất cả gói mua</option>
        <option value="monthly" <?php echo e(request('plan') == 'monthly' ? 'selected' : ''); ?>>Gói Tháng</option>
        <option value="yearly" <?php echo e(request('plan') == 'yearly' ? 'selected' : ''); ?>>Gói Năm</option>
      </select>

      <select name="status" class="form-control" style="width:140px; font-size:13px; cursor:pointer;">
        <option value="">Tất cả trạng thái</option>
        <option value="paid" <?php echo e(request('status') == 'paid' ? 'selected' : ''); ?>>✅ Thành công</option>
        <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>⏳ Chờ thanh toán</option>
        <option value="failed" <?php echo e(request('status') == 'failed' ? 'selected' : ''); ?>>❌ Thất bại</option>
        <option value="refunded" <?php echo e(request('status') == 'refunded' ? 'selected' : ''); ?>>↩️ Đã hoàn tiền</option>
      </select>

      <button type="submit" class="btn btn-primary btn-sm" style="padding:0 14px; height: 38px;"><i class="fas fa-search"></i> Lọc</button>
    </div>
  </form>
</div>

<div class="card shadow-sm" style="border-radius: var(--radius-lg); overflow: hidden;">
  <table class="table" style="vertical-align: middle;">
    <thead>
      <tr style="background: #f8fafc; border-bottom: 1px solid var(--border);">
        <th style="width: 50px;">ID</th>
        <th>Khách hàng</th>
        <th>Mã tham chiếu GD (VNPay)</th>
        <th style="text-align: right;">Số tiền</th>
        <th style="text-align: center;">Gói mua</th>
        <th style="text-align: center;">Trạng thái</th>
        <th>Thời gian thanh toán</th>
      </tr>
    </thead>
    <tbody>
      <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
          <td class="text-muted fs-12 fw-700">#<?php echo e($t->id); ?></td>
          <td>
            <?php if($t->user): ?>
              <div class="flex gap-10" style="align-items:center">
                <div class="avatar avatar-sm avatar-placeholder" style="background:var(--primary-light); color:var(--primary); font-size:12px; font-weight:700; flex-shrink:0;">
                  <?php echo e(strtoupper(substr($t->user->name, 0, 1))); ?>

                </div>
                <div style="min-width: 0; flex: 1;">
                  <div class="fw-700 fs-13" style="color:var(--secondary); text-overflow:ellipsis; overflow:hidden; white-space:nowrap;"><?php echo e($t->user->name); ?></div>
                  <div class="text-muted fs-12" style="text-overflow:ellipsis; overflow:hidden; white-space:nowrap;"><?php echo e($t->user->email); ?></div>
                </div>
              </div>
            <?php else: ?>
              <span class="text-muted fs-12 italic">Người dùng đã bị xóa</span>
            <?php endif; ?>
          </td>
          <td>
            <div class="fw-600 fs-12" style="color:#1e293b;">Ref: <?php echo e($t->vnp_txn_ref); ?></div>
            <?php if($t->vnp_transaction_no): ?>
              <div class="text-muted fs-11" style="margin-top: 2px;">VNPay No: <?php echo e($t->vnp_transaction_no); ?></div>
            <?php endif; ?>
          </td>
          <td style="text-align: right;" class="fw-700 text-secondary fs-13">
            <?php echo e(number_format($t->amount)); ?>đ
          </td>
          <td style="text-align: center;">
            <?php if($t->plan === 'yearly'): ?>
              <span class="tag fs-11" style="background:#f5f3ff; color:#7c3aed; border:1px solid #ddd6fe; padding:2px 8px; border-radius:4px; font-weight:600;">Năm (Yearly)</span>
            <?php else: ?>
              <span class="tag fs-11" style="background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; padding:2px 8px; border-radius:4px; font-weight:600;">Tháng (Monthly)</span>
            <?php endif; ?>
          </td>
          <td style="text-align: center;">
            <?php if($t->status === 'paid'): ?>
              <span class="status status-open" style="font-size:11px; font-weight:600; background:#ecfdf5; color:#10b981; border:1px solid #d1fae5; padding: 2px 8px; border-radius: 4px;">Thành công</span>
            <?php elseif($t->status === 'pending'): ?>
              <span class="status status-pending" style="font-size:11px; font-weight:600; background:#fffbeb; color:#d97706; border:1px solid #fef3c7; padding: 2px 8px; border-radius: 4px;">Chờ thanh toán</span>
            <?php elseif($t->status === 'failed'): ?>
              <span class="status status-closed" style="font-size:11px; font-weight:600; background:#fef2f2; color:#ef4444; border:1px solid #fee2e2; padding: 2px 8px; border-radius: 4px;">Thất bại</span>
            <?php elseif($t->status === 'refunded'): ?>
              <span class="status status-closed" style="font-size:11px; font-weight:600; background:#fff7ed; color:#ea580c; border:1px solid #ffedd5; padding: 2px 8px; border-radius: 4px;">Đã hoàn tiền</span>
            <?php endif; ?>
          </td>
          <td class="text-muted fs-12">
            <?php if($t->paid_at): ?>
              <?php echo e($t->paid_at->format('H:i d/m/Y')); ?>

            <?php else: ?>
              <?php echo e($t->created_at->format('H:i d/m/Y')); ?> <span class="text-muted fs-10">(Khởi tạo)</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="7" class="text-center text-muted" style="padding:32px">Không tìm thấy giao dịch nào thỏa mãn điều kiện tìm kiếm.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  
  <?php if($transactions->hasPages()): ?>
    <div class="card-footer" style="background:#f8fafc; border-top:1px solid var(--border);">
      <div class="flex-between">
        <span class="text-muted fs-13">Đang xem <?php echo e($transactions->firstItem()); ?>–<?php echo e($transactions->lastItem()); ?> trong tổng số <?php echo e($transactions->total()); ?></span>
        <div class="pagination">
          <?php if(!$transactions->onFirstPage()): ?><a href="<?php echo e($transactions->previousPageUrl()); ?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
          <?php $__currentLoopData = $transactions->getUrlRange(max(1,$transactions->currentPage()-2), min($transactions->lastPage(),$transactions->currentPage()+2)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($page == $transactions->currentPage()): ?>
              <span class="active" style="background:var(--primary); color:white;"><?php echo e($page); ?></span>
            <?php else: ?>
              <a href="<?php echo e($url); ?>"><?php echo e($page); ?></a>
            <?php endif; ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          <?php if($transactions->hasMorePages()): ?><a href="<?php echo e($transactions->nextPageUrl()); ?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\dl\web_timviec_updated\web_timviec\resources\views/admin/transactions.blade.php ENDPATH**/ ?>