<?php $__env->startSection('title', 'Chọn vai trò — ITWorks'); ?>

<?php $__env->startSection('content'); ?>
<div class="reg-page">
  <div class="reg-wrap">

    
    <div class="text-center" style="margin-bottom:32px">
      <a href="<?php echo e(url('/')); ?>" class="navbar-brand" style="font-size:32px;justify-content:center">IT<span>Works</span></a>
      <h1 style="font-size:22px;font-weight:800;margin:16px 0 6px;color:#1e293b">Bạn muốn sử dụng với tư cách nào?</h1>
      <p style="font-size:14px;color:#94a3b8;margin:0">Chọn vai trò phù hợp để hoàn tất đăng ký qua <strong><?php echo e($provider === 'github' ? 'GitHub' : 'Google'); ?></strong></p>
    </div>

    
    <div class="role-grid">

      
      <a href="<?php echo e(route('auth.social.role', ['provider' => $provider, 'role' => 'employee'])); ?>" class="role-card role-card-employee">
        <div class="role-card__icon" style="background:#f0fdf7;color:#10b981">
          <i class="fas fa-user-tie"></i>
        </div>
        <h3 class="role-card__title" style="color:#10b981">Ứng viên</h3>
        <p class="role-card__desc">Tìm việc làm IT phù hợp, xây dựng CV chuyên nghiệp và kết nối với nhà tuyển dụng hàng đầu.</p>
        <ul class="role-card__list">
          <li><i class="fas fa-check"></i> Xây dựng CV online</li>
          <li><i class="fas fa-check"></i> Nộp hồ sơ không giới hạn</li>
          <li><i class="fas fa-check"></i> Nhận thông báo việc làm</li>
        </ul>
        <div class="role-card__cta" style="background:#10b981">
          <i class="fas fa-search" style="margin-right:8px"></i>Tìm việc làm
        </div>
      </a>

      
      <a href="<?php echo e(route('auth.social.role', ['provider' => $provider, 'role' => 'employer'])); ?>" class="role-card role-card-employer">
        <div class="role-card__icon" style="background:#fff3e0;color:#e65100">
          <i class="fas fa-building"></i>
        </div>
        <h3 class="role-card__title" style="color:#e65100">Nhà tuyển dụng</h3>
        <p class="role-card__desc">Đăng tin tuyển dụng, tìm kiếm nhân tài IT xuất sắc cho công ty một cách nhanh chóng và hiệu quả.</p>
        <ul class="role-card__list">
          <li><i class="fas fa-check" style="color:#e65100"></i> Đăng tin không giới hạn</li>
          <li><i class="fas fa-check" style="color:#e65100"></i> Quản lý ứng viên dễ dàng</li>
          <li><i class="fas fa-check" style="color:#e65100"></i> Nhắn tin trực tiếp</li>
        </ul>
        <div class="role-card__cta" style="background:#e65100">
          <i class="fas fa-rocket" style="margin-right:8px"></i>Bắt đầu tuyển dụng
        </div>
      </a>

    </div>

    <div style="text-align:center;padding:16px 0">
      <p style="font-size:13px;color:#94a3b8;margin:0">
        Đã có tài khoản? <a href="<?php echo e(route('login')); ?>" style="color:#10b981;font-weight:700">Đăng nhập ngay →</a>
      </p>
    </div>

  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.reg-page {
  min-height: calc(100vh - 60px);
  background: linear-gradient(135deg, #f0fdf7 0%, #e8f4fd 50%, #fdf4ff 100%);
  display: flex; align-items: center; padding: 40px 16px;
}
.reg-wrap { width: 100%; max-width: 780px; margin: 0 auto; }

.role-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 28px; }
@media(max-width:640px){ .role-grid { grid-template-columns: 1fr; } }

.role-card {
  background: #fff; border-radius: 20px;
  padding: 28px 24px 24px;
  border: 2.5px solid #f1f5f9;
  text-decoration: none; color: inherit;
  display: flex; flex-direction: column; gap: 12px;
  position: relative; overflow: hidden;
  transition: all .2s; cursor: pointer;
  box-shadow: 0 2px 12px rgba(0,0,0,.05);
}
.role-card:hover { transform: translateY(-4px); text-decoration: none; color: inherit; }
.role-card-employee:hover { border-color: #10b981; box-shadow: 0 8px 32px rgba(16,185,129,.15); }
.role-card-employer:hover { border-color: #e65100; box-shadow: 0 8px 32px rgba(230,81,0,.15); }

.role-card__badge {
  display: inline-flex; align-items: center;
  border: 1px solid; font-size: 11px; font-weight: 700;
  padding: 4px 10px; border-radius: 20px; width: fit-content;
}
.role-card__icon {
  width: 60px; height: 60px; border-radius: 16px;
  display: flex; align-items: center; justify-content: center; font-size: 26px;
}
.role-card__title { font-size: 20px; font-weight: 800; margin: 0; }
.role-card__desc  { font-size: 13px; color: #64748b; line-height: 1.6; margin: 0; }
.role-card__list  { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px; flex: 1; }
.role-card__list li { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #475569; }
.role-card__list li i { font-size: 12px; flex-shrink: 0; }
.role-card__cta {
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 14px; font-weight: 700;
  padding: 12px; border-radius: 12px; margin-top: 4px; transition: opacity .2s;
}
.role-card:hover .role-card__cta { opacity: .9; }
</style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH F:\dl\web_timviec_updated\web_timviec\resources\views/auth/social-choose-role.blade.php ENDPATH**/ ?>