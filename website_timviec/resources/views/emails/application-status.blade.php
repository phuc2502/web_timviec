<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><title>Cập nhật trạng thái ứng tuyển</title></head>
<body>
<p>Xin chào <strong>{{ $application->user->name }}</strong>,</p>
<p>Đơn ứng tuyển vào vị trí <strong>{{ $application->listing->title }}</strong> của bạn đã được cập nhật:</p>
<ul>
    <li>Trạng thái cũ: <strong>{{ \App\Models\Application::STATUS_LABELS[$oldStatus] ?? $oldStatus }}</strong></li>
    <li>Trạng thái mới: <strong>{{ \App\Models\Application::STATUS_LABELS[$application->status] ?? $application->status }}</strong></li>
</ul>
<p>Trân trọng,<br>Đội ngũ Tìm Việc</p>
</body>
</html>
