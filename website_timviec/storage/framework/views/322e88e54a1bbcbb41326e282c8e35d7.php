<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><title>Cập nhật trạng thái ứng tuyển</title></head>
<body>
<p>Xin chào <strong><?php echo e($application->user->name); ?></strong>,</p>
<p>Đơn ứng tuyển vào vị trí <strong><?php echo e($application->listing->title); ?></strong> của bạn đã được cập nhật:</p>
<ul>
    <li>Trạng thái cũ: <strong><?php echo e(\App\Models\Application::STATUS_LABELS[$oldStatus] ?? $oldStatus); ?></strong></li>
    <li>Trạng thái mới: <strong><?php echo e(\App\Models\Application::STATUS_LABELS[$application->status] ?? $application->status); ?></strong></li>
</ul>
<p>Trân trọng,<br>Đội ngũ Tìm Việc</p>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\web_timviec_final\website_timviec\resources\views/emails/application-status.blade.php ENDPATH**/ ?>