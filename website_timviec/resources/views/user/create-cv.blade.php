@extends('layouts.dashboard')
@section('title', 'Tạo CV Online')

@section('content')
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-20 fw-800" style="color:var(--secondary)">Tạo CV Online</h1>
    <p class="text-muted fs-13 mt-4">Điền thông tin và xem preview CV của bạn</p>
  </div>
  <a href="{{ url('/user/cv') }}" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Quay lại</a>
</div>

<form action="{{ url('/user/cv/preview') }}" method="GET" id="cv-form">
  <div class="grid" style="grid-template-columns:1fr 1fr;gap:20px;align-items:start">

    {{-- LEFT COLUMN --}}
    <div class="flex-col gap-16">

      {{-- Personal Info --}}
      <div class="card">
        <div class="card-header"><span class="fw-700 fs-14"><i class="fas fa-user" style="color:var(--primary);margin-right:6px"></i>Thông tin cá nhân</span></div>
        <div class="card-body" style="padding:20px">
          <div class="flex-col gap-12">
            <div class="form-group">
              <label class="form-label">Họ và tên <span class="required">*</span></label>
              <input type="text" name="name" class="form-control" value="{{ auth()->user()->name }}" required>
            </div>
            <div class="grid-2" style="gap:10px">
              <div class="form-group">
                <label class="form-label">Email <span class="required">*</span></label>
                <input type="email" name="email" class="form-control" value="{{ auth()->user()->email }}" required>
              </div>
              <div class="form-group">
                <label class="form-label">Số điện thoại</label>
                <input type="tel" name="phone" class="form-control" placeholder="0901 234 567">
              </div>
            </div>
            <div class="grid-2" style="gap:10px">
              <div class="form-group">
                <label class="form-label">Địa chỉ</label>
                <input type="text" name="address" class="form-control" placeholder="Hà Nội, Việt Nam">
              </div>
              <div class="form-group">
                <label class="form-label">LinkedIn / GitHub</label>
                <input type="url" name="linkedin" class="form-control" placeholder="https://linkedin.com/in/...">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Ảnh đại diện</label>
              <input type="file" name="avatar" class="form-control" accept="image/*">
            </div>
          </div>
        </div>
      </div>

      {{-- Objective --}}
      <div class="card">
        <div class="card-header"><span class="fw-700 fs-14"><i class="fas fa-target" style="color:var(--primary);margin-right:6px"></i>Mục tiêu nghề nghiệp</span></div>
        <div class="card-body" style="padding:20px">
          <textarea name="objective" class="form-control" rows="4"
            placeholder="Tôi là lập trình viên với 3 năm kinh nghiệm về Backend. Mục tiêu của tôi là...">{{ auth()->user()->about }}</textarea>
        </div>
      </div>

      {{-- Skills --}}
      <div class="card">
        <div class="card-header"><span class="fw-700 fs-14"><i class="fas fa-code" style="color:var(--primary);margin-right:6px"></i>Kỹ năng</span></div>
        <div class="card-body" style="padding:20px">
          <div class="form-group">
            <label class="form-label">Kỹ năng chuyên môn</label>
            <textarea name="skills" class="form-control" rows="3"
              placeholder="PHP, Laravel, MySQL, Redis, Docker, Git..."></textarea>
            <div class="form-hint">Ngăn cách bằng dấu phẩy</div>
          </div>
          <div class="form-group mt-12">
            <label class="form-label">Kỹ năng mềm</label>
            <textarea name="soft_skills" class="form-control" rows="2"
              placeholder="Làm việc nhóm, Giao tiếp, Quản lý thời gian..."></textarea>
          </div>
        </div>
      </div>
    </div>

    {{-- RIGHT COLUMN --}}
    <div class="flex-col gap-16">

      {{-- Experience --}}
      <div class="card">
        <div class="card-header"><span class="fw-700 fs-14"><i class="fas fa-briefcase" style="color:var(--primary);margin-right:6px"></i>Kinh nghiệm làm việc</span></div>
        <div class="card-body" style="padding:20px">
          <div id="exp-container" class="flex-col gap-14">
            <div class="exp-item" style="border:1px solid var(--border);border-radius:var(--radius);padding:14px">
              <div class="grid-2" style="gap:10px;margin-bottom:10px">
                <div class="form-group">
                  <label class="form-label" style="font-size:12px">Vị trí</label>
                  <input type="text" name="exp_title[]" class="form-control" placeholder="Backend Developer">
                </div>
                <div class="form-group">
                  <label class="form-label" style="font-size:12px">Công ty</label>
                  <input type="text" name="exp_company[]" class="form-control" placeholder="FPT Software">
                </div>
              </div>
              <div class="grid-2" style="gap:10px;margin-bottom:10px">
                <div class="form-group">
                  <label class="form-label" style="font-size:12px">Từ tháng/năm</label>
                  <input type="month" name="exp_from[]" class="form-control">
                </div>
                <div class="form-group">
                  <label class="form-label" style="font-size:12px">Đến (để trống = hiện tại)</label>
                  <input type="month" name="exp_to[]" class="form-control">
                </div>
              </div>
              <div class="form-group">
                <label class="form-label" style="font-size:12px">Mô tả công việc</label>
                <textarea name="exp_desc[]" class="form-control" rows="3" placeholder="- Phát triển API RESTful với Laravel&#10;- Tối ưu hiệu suất database..."></textarea>
              </div>
            </div>
          </div>
          <button type="button" onclick="addExp()" class="btn btn-outline btn-sm mt-12">
            <i class="fas fa-plus"></i> Thêm kinh nghiệm
          </button>
        </div>
      </div>

      {{-- Education --}}
      <div class="card">
        <div class="card-header"><span class="fw-700 fs-14"><i class="fas fa-graduation-cap" style="color:var(--primary);margin-right:6px"></i>Học vấn</span></div>
        <div class="card-body" style="padding:20px">
          <div class="flex-col gap-10">
            <div class="form-group">
              <label class="form-label">Trường học</label>
              <input type="text" name="edu_school" class="form-control" placeholder="Đại học Bách Khoa Hà Nội">
            </div>
            <div class="form-group">
              <label class="form-label">Ngành học</label>
              <input type="text" name="edu_major" class="form-control" placeholder="Công nghệ thông tin">
            </div>
            <div class="grid-2" style="gap:10px">
              <div class="form-group">
                <label class="form-label">Năm tốt nghiệp</label>
                <input type="number" name="edu_year" class="form-control" placeholder="2022" min="1990" max="2030">
              </div>
              <div class="form-group">
                <label class="form-label">GPA / Xếp loại</label>
                <input type="text" name="edu_gpa" class="form-control" placeholder="3.5/4.0 — Giỏi">
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Projects --}}
      <div class="card">
        <div class="card-header"><span class="fw-700 fs-14"><i class="fas fa-project-diagram" style="color:var(--primary);margin-right:6px"></i>Dự án nổi bật</span></div>
        <div class="card-body" style="padding:20px">
          <div class="flex-col gap-10">
            <div class="form-group">
              <label class="form-label">Tên dự án</label>
              <input type="text" name="proj_name" class="form-control" placeholder="E-commerce Platform">
            </div>
            <div class="form-group">
              <label class="form-label">Mô tả dự án</label>
              <textarea name="proj_desc" class="form-control" rows="3" placeholder="Hệ thống thương mại điện tử phục vụ 50,000+ người dùng..."></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">Link dự án / GitHub</label>
              <input type="url" name="proj_link" class="form-control" placeholder="https://github.com/...">
            </div>
          </div>
        </div>
      </div>

      <div class="flex gap-10">
        <button type="submit" class="btn btn-primary btn-lg" style="flex:1"><i class="fas fa-eye"></i> Xem preview & Xuất PDF</button>
      </div>
    </div>
  </div>
</form>

@push('scripts')
<script>
function addExp() {
  var container = document.getElementById('exp-container');
  var item = container.querySelector('.exp-item').cloneNode(true);
  item.querySelectorAll('input, textarea').forEach(function(el) { el.value = ''; });
  container.appendChild(item);
}
</script>
@endpush
@endsection
