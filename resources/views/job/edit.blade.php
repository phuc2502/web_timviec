@extends('layouts.dashboard')
@section('title', 'Chỉnh sửa tin tuyển dụng')

@section('content')
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-20 fw-800" style="color:var(--secondary)">Chỉnh sửa tin tuyển dụng</h1>
    <p class="text-muted fs-13 mt-4">{{ $listing->title }}</p>
  </div>
  <div class="flex gap-8">
    <a href="{{ url('/job/show/'.$listing->slug) }}" target="_blank" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> Xem trước</a>
    <a href="{{ url('/job') }}" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Quay lại</a>
  </div>
</div>

<form action="{{ url('/job/'.$listing->id.'/update') }}" method="POST" enctype="multipart/form-data">
  @csrf @method('PUT')
  <div class="grid" style="grid-template-columns:2fr 1fr;gap:20px;align-items:start">
    <div class="flex-col gap-16">
      <div class="card">
        <div class="card-header"><span class="fw-700 fs-15"><i class="fas fa-edit" style="color:var(--primary);margin-right:8px"></i>Nội dung tin đăng</span></div>
        <div class="card-body" style="padding:24px">
          <div class="flex-col gap-16">
            <div class="form-group">
              <label class="form-label">Tiêu đề <span class="required">*</span></label>
              <input type="text" name="title" class="form-control" value="{{ old('title', $listing->title) }}" required>
            </div>
            <div class="form-group">
              <label class="form-label">Mô tả công việc <span class="required">*</span></label>
              <textarea name="description" class="form-control" rows="8" required>{{ old('description', $listing->description) }}</textarea>
            </div>
            <div class="form-group">
              <label class="form-label">Yêu cầu ứng viên <span class="required">*</span></label>
              <textarea name="roles" class="form-control" rows="6" required>{{ old('roles', $listing->roles) }}</textarea>
            </div>
            <div class="form-group">
              <label class="form-label">Phúc lợi / Mô tả thêm</label>
              <textarea name="predes" class="form-control" rows="4">{{ old('predes', $listing->predes) }}</textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="flex-col gap-16">
      <div class="card">
        <div class="card-header"><span class="fw-700 fs-15"><i class="fas fa-cog" style="color:var(--primary);margin-right:8px"></i>Chi tiết</span></div>
        <div class="card-body" style="padding:20px">
          <div class="flex-col gap-14">
            <div class="form-group">
              <label class="form-label">Mức lương (đ/tháng)</label>
              <input type="number" name="salary" class="form-control" value="{{ old('salary', $listing->salary) }}" min="0">
            </div>
            <div class="form-group">
              <label class="form-label">Địa điểm</label>
              <select name="address" class="form-control" required>
                @foreach(['Hà Nội','Hồ Chí Minh','Đà Nẵng','Cần Thơ','Remote','Toàn quốc'] as $loc)
                  <option value="{{ $loc }}" {{ old('address', $listing->address) == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Loại hình</label>
              <select name="job_type" class="form-control" required>
                @foreach(['Full-time','Part-time','Remote','Freelance','Internship'] as $type)
                  <option value="{{ $type }}" {{ old('job_type', $listing->job_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Hạn nộp hồ sơ</label>
              <input type="date" name="application_close_date" class="form-control"
                value="{{ old('application_close_date', optional($listing->application_close_date)->format('Y-m-d')) }}" min="{{ date('Y-m-d') }}">
            </div>
            <div class="form-group">
              <label class="form-label">Ảnh bìa hiện tại</label>
              @if($listing->feature_image)
                <img src="{{ asset('storage/images/'.$listing->feature_image) }}" style="width:100%;height:100px;object-fit:contain;border-radius:var(--radius);border:1px solid var(--border);background:#fafafa;padding:8px" alt="">
              @endif
              <input type="file" name="feature_image" class="form-control mt-8" accept="image/*">
              <div class="form-hint">Để trống nếu không muốn thay đổi ảnh</div>
            </div>
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block btn-lg"><i class="fas fa-save"></i> Lưu thay đổi</button>

      <div class="card" style="border-color:#dc3545">
        <div class="card-body" style="padding:16px">
          <div class="fw-600 fs-13 mb-8" style="color:#dc3545"><i class="fas fa-exclamation-triangle"></i> Vùng nguy hiểm</div>
          <a href="{{ url('/job/'.$listing->id.'/delete') }}"
            onclick="return confirm('Xoá tin này? Hành động này không thể hoàn tác!')"
            class="btn btn-danger btn-block btn-sm">
            <i class="fas fa-trash"></i> Xoá tin tuyển dụng
          </a>
        </div>
      </div>
    </div>
  </div>
</form>
@endsection
