@extends('layouts.dashboard')
@section('title', 'Thiết kế CV Online')

@section('content')
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-20 fw-800" style="color:var(--secondary)">Thiết kế CV chuyên nghiệp</h1>
    <p class="text-muted fs-13 mt-4">Điền đầy đủ thông tin để hệ thống tự động sinh CV theo template hiện đại của bạn.</p>
  </div>
  <a href="{{ route('user.cv') }}" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Quay lại</a>
</div>

<form action="{{ route('user.cv.save') }}" method="POST" enctype="multipart/form-data" id="cv-form" class="flex-col gap-20">
  @csrf

  <div class="grid" style="grid-template-columns: 2fr 1fr; gap: 20px; align-items: start;">
    
    {{-- Cột trái: Form nhập liệu --}}
    <div class="flex-col gap-20">
      
      {{-- AI Resume Parser Card --}}
      <div class="card shadow-sm" style="border-radius: var(--radius-lg); border: 2px dashed #8b5cf6; background: linear-gradient(135deg, #f5f3ff 0%, #ffffff 100%);">
        <div class="card-body" style="padding: 24px;">
          <div class="flex" style="gap: 16px; align-items: flex-start;">
            <div style="background: linear-gradient(135deg, #a78bfa 0%, #7c3aed 100%); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3); flex-shrink: 0;">
              <i class="fas fa-magic fa-lg"></i>
            </div>
            <div style="flex: 1;">
              <h3 class="fw-700 fs-15" style="color:#6d28d9; margin: 0 0 6px 0; display: flex; align-items: center; gap: 8px;">
                Tự động điền nhanh CV bằng AI ✨ 
                <span style="background: #ede9fe; color: #7c3aed; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase;">Beta</span>
              </h3>
              <p class="text-muted fs-12" style="margin: 0 0 16px 0; line-height: 1.5;">
                Bạn đã có CV sẵn? Tải lên file CV định dạng PDF, AI của chúng tôi sẽ tự động trích xuất thông tin học vấn, kinh nghiệm, dự án và điền vào form này giúp bạn tiết kiệm thời gian!
              </p>
              
              <div id="ai-upload-area" style="border: 2px dashed #c084fc; border-radius: 10px; padding: 20px; text-align: center; background: rgba(255, 255, 255, 0.6); cursor: pointer; transition: all 0.2s ease;"
                   onclick="document.getElementById('ai-cv-input').click()"
                   ondragover="handleAiDragOver(event)" ondragleave="handleAiDragLeave(event)" ondrop="handleAiDrop(event)">
                <input type="file" id="ai-cv-input" accept=".pdf" style="display: none;" onchange="handleAiFileSelect(event)">
                <i class="fas fa-cloud-upload-alt fa-2x mb-10" style="color: #a78bfa; display: block; margin: 0 auto 8px;"></i>
                <p class="fw-600 fs-13" style="color:#5b21b6; margin: 0 0 4px 0;">Chọn file hoặc kéo thả file PDF vào đây</p>
                <p class="text-muted fs-11" style="margin: 0;">Định dạng PDF (tối đa 5MB)</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      {{-- 1. Thông tin cá nhân --}}
      <div class="card shadow-sm" style="border-radius: var(--radius-lg);">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border);">
          <span class="fw-700 fs-15 text-primary"><i class="fas fa-user-circle mr-6"></i> 1. Thông tin cá nhân</span>
        </div>
        <div class="card-body" style="padding: 24px;">
          <div class="grid-2 gap-16 mb-16">
            <div class="form-group">
              <label class="form-label fw-600 fs-12 mb-6">Họ và tên <span class="required" style="color:var(--danger)">*</span></label>
              <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" 
                     value="{{ old('full_name', $cvData->full_name ?? auth()->user()->name) }}" required placeholder="Nguyễn Văn A">
              @error('full_name')
                <div class="invalid-feedback" style="color:var(--danger); font-size:11px; margin-top:4px;">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label class="form-label fw-600 fs-12 mb-6">Email liên hệ <span class="required" style="color:var(--danger)">*</span></label>
              <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                     value="{{ old('email', $cvData->email ?? auth()->user()->email) }}" required placeholder="nguyenvana@gmail.com">
              @error('email')
                <div class="invalid-feedback" style="color:var(--danger); font-size:11px; margin-top:4px;">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="grid-2 gap-16 mb-16">
            <div class="form-group">
              <label class="form-label fw-600 fs-12 mb-6">Số điện thoại</label>
              <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                     value="{{ old('phone', $cvData->phone ?? '') }}" placeholder="0901234567">
              @error('phone')
                <div class="invalid-feedback" style="color:var(--danger); font-size:11px; margin-top:4px;">{{ $message }}</div>
              @enderror
            </div>
            <div class="form-group">
              <label class="form-label fw-600 fs-12 mb-6">Địa chỉ</label>
              <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" 
                     value="{{ old('address', $cvData->address ?? '') }}" placeholder="Quận 1, TP. Hồ Chí Minh">
              @error('address')
                <div class="invalid-feedback" style="color:var(--danger); font-size:11px; margin-top:4px;">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="grid gap-16 mb-16" style="grid-template-columns: 1fr 2fr;">
            <div class="form-group">
              <label class="form-label fw-600 fs-12 mb-6">Ảnh thẻ hiện tại</label>
              <div style="width: 80px; height: 80px; border-radius: 8px; border: 1px solid var(--border); overflow: hidden; background: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                @if($cvData && $cvData->photo_path && Storage::disk('public')->exists($cvData->photo_path))
                  <img src="{{ asset('storage/' . $cvData->photo_path) }}" style="width:100%; height:100%; object-fit:cover;" id="photo-preview-img">
                @else
                  <i class="fas fa-user-tie fa-2x" style="color: #cbd5e1;" id="photo-preview-placeholder"></i>
                @endif
              </div>
            </div>
            <div class="form-group">
              <label class="form-label fw-600 fs-12 mb-6">Tải lên ảnh thẻ mới (Tỷ lệ 3x4 hoặc vuông)</label>
              <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*" onchange="previewPhoto(this)">
              <div class="text-muted fs-11 mt-4">Định dạng JPG, PNG, WEBP. Tối đa 2MB. Ảnh cũ sẽ tự động được dọn dẹp khỏi hệ thống.</div>
              @error('photo')
                <div class="invalid-feedback" style="color:var(--danger); font-size:11px; margin-top:4px;">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      {{-- 2. Mục tiêu nghề nghiệp --}}
      <div class="card shadow-sm" style="border-radius: var(--radius-lg);">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border);">
          <span class="fw-700 fs-15 text-primary"><i class="fas fa-bullseye mr-6"></i> 2. Mục tiêu nghề nghiệp</span>
        </div>
        <div class="card-body" style="padding: 24px;">
          <div class="form-group">
            <textarea name="objective" class="form-control" rows="4" placeholder="Khát khao học hỏi và mong muốn cống hiến cho công ty..." style="resize: vertical;">{{ old('objective', $cvData->objective ?? '') }}</textarea>
            <div class="text-muted fs-11 mt-4">Tóm tắt ngắn gọn thế mạnh của bản thân và định hướng công việc bạn hướng tới.</div>
          </div>
        </div>
      </div>

      {{-- 3. Kỹ năng chuyên môn --}}
      <div class="card shadow-sm" style="border-radius: var(--radius-lg);">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border);">
          <span class="fw-700 fs-15 text-primary"><i class="fas fa-tools mr-6"></i> 3. Kỹ năng chuyên môn</span>
        </div>
        <div class="card-body" style="padding: 24px;">
          <div class="form-group">
            <label class="form-label fw-600 fs-12 mb-6">Danh sách kỹ năng</label>
            <textarea name="skills_text" class="form-control" rows="3" placeholder="PHP, Laravel, MySQL, JavaScript, VueJS, Git, Docker, RESTful API..." style="resize: vertical;">{{ old('skills_text', $cvData->skills_text ?? '') }}</textarea>
            <div class="text-muted fs-11 mt-4">Ngăn cách các kỹ năng bằng dấu phẩy (,). Hệ thống sẽ tự động tách chúng thành các tag nhãn đẹp mắt.</div>
          </div>
        </div>
      </div>

      {{-- 4. Học vấn --}}
      <div class="card shadow-sm" style="border-radius: var(--radius-lg);">
        <div class="card-header flex-between" style="background: #f8fafc; border-bottom: 1px solid var(--border); padding: 12px 24px;">
          <span class="fw-700 fs-15 text-primary"><i class="fas fa-graduation-cap mr-6"></i> 4. Học vấn & Bằng cấp</span>
          <button type="button" class="btn btn-sm btn-outline" style="border-color:#1a73e8; color:#1a73e8; padding: 4px 10px;" onclick="addEducationRow()">
            <i class="fas fa-plus"></i> Thêm mới
          </button>
        </div>
        <div class="card-body" style="padding: 24px;">
          <div id="education-wrapper" class="flex-col gap-16">
            @php
              $education = old('education', $cvData->education ?? []);
            @endphp
            
            @forelse($education as $index => $edu)
              <div class="repeater-item" style="border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; position: relative; background: #fafafa;">
                <button type="button" class="btn-remove" onclick="removeRepeaterRow(this)" style="position: absolute; top: 12px; right: 12px; border: none; background: transparent; color: var(--danger); cursor: pointer;" title="Xóa dòng này">
                  <i class="fas fa-times-circle fa-lg"></i>
                </button>
                <div class="grid-2 gap-12 mb-12">
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Trường học / Trung tâm</label>
                    <input type="text" name="education[{{ $index }}][school]" class="form-control" value="{{ $edu['school'] ?? '' }}" placeholder="Đại học Bách Khoa Hà Nội" required>
                  </div>
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Ngành học / Bằng cấp</label>
                    <input type="text" name="education[{{ $index }}][degree]" class="form-control" value="{{ $edu['degree'] ?? '' }}" placeholder="Cử nhân Công nghệ thông tin" required>
                  </div>
                </div>
                <div class="grid-2 gap-12">
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Thời gian bắt đầu</label>
                    <input type="text" name="education[{{ $index }}][year_start]" class="form-control" value="{{ $edu['year_start'] ?? '' }}" placeholder="2018">
                  </div>
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Thời gian kết thúc</label>
                    <input type="text" name="education[{{ $index }}][year_end]" class="form-control" value="{{ $edu['year_end'] ?? '' }}" placeholder="2022 (Hoặc Hiện tại)">
                  </div>
                </div>
              </div>
            @empty
              {{-- Dòng mặc định nếu chưa có dữ liệu --}}
              <div class="repeater-item" style="border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; position: relative; background: #fafafa;">
                <button type="button" class="btn-remove" onclick="removeRepeaterRow(this)" style="position: absolute; top: 12px; right: 12px; border: none; background: transparent; color: var(--danger); cursor: pointer; display:none;" title="Xóa dòng này">
                  <i class="fas fa-times-circle fa-lg"></i>
                </button>
                <div class="grid-2 gap-12 mb-12">
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Trường học / Trung tâm</label>
                    <input type="text" name="education[0][school]" class="form-control" placeholder="Đại học Bách Khoa Hà Nội">
                  </div>
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Ngành học / Bằng cấp</label>
                    <input type="text" name="education[0][degree]" class="form-control" placeholder="Cử nhân Công nghệ thông tin">
                  </div>
                </div>
                <div class="grid-2 gap-12">
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Thời gian bắt đầu</label>
                    <input type="text" name="education[0][year_start]" class="form-control" placeholder="2018">
                  </div>
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Thời gian kết thúc</label>
                    <input type="text" name="education[0][year_end]" class="form-control" placeholder="2022">
                  </div>
                </div>
              </div>
            @endforelse
          </div>
        </div>
      </div>

      {{-- 5. Kinh nghiệm làm việc --}}
      <div class="card shadow-sm" style="border-radius: var(--radius-lg);">
        <div class="card-header flex-between" style="background: #f8fafc; border-bottom: 1px solid var(--border); padding: 12px 24px;">
          <span class="fw-700 fs-15 text-primary"><i class="fas fa-briefcase mr-6"></i> 5. Kinh nghiệm làm việc</span>
          <button type="button" class="btn btn-sm btn-outline" style="border-color:#1a73e8; color:#1a73e8; padding: 4px 10px;" onclick="addExperienceRow()">
            <i class="fas fa-plus"></i> Thêm mới
          </button>
        </div>
        <div class="card-body" style="padding: 24px;">
          <div id="experience-wrapper" class="flex-col gap-16">
            @php
              $experience = old('experience', $cvData->experience ?? []);
            @endphp
            
            @forelse($experience as $index => $exp)
              <div class="repeater-item" style="border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; position: relative; background: #fafafa;">
                <button type="button" class="btn-remove" onclick="removeRepeaterRow(this)" style="position: absolute; top: 12px; right: 12px; border: none; background: transparent; color: var(--danger); cursor: pointer;" title="Xóa dòng này">
                  <i class="fas fa-times-circle fa-lg"></i>
                </button>
                <div class="grid-2 gap-12 mb-12">
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Tên công ty</label>
                    <input type="text" name="experience[{{ $index }}][company]" class="form-control" value="{{ $exp['company'] ?? '' }}" placeholder="FPT Software" required>
                  </div>
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Vị trí / Chức vụ</label>
                    <input type="text" name="experience[{{ $index }}][role]" class="form-control" value="{{ $exp['role'] ?? '' }}" placeholder="Lập trình viên Backend PHP" required>
                  </div>
                </div>
                <div class="grid-2 gap-12 mb-12">
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Thời gian bắt đầu</label>
                    <input type="text" name="experience[{{ $index }}][year_start]" class="form-control" value="{{ $exp['year_start'] ?? '' }}" placeholder="06/2023">
                  </div>
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Thời gian kết thúc</label>
                    <input type="text" name="experience[{{ $index }}][year_end]" class="form-control" value="{{ $exp['year_end'] ?? '' }}" placeholder="Hiện tại">
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label fs-11 mb-4">Mô tả công việc & Thành tích</label>
                  <textarea name="experience[{{ $index }}][desc]" class="form-control" rows="3" placeholder="- Xây dựng RESTful API cho dự án mạng xã hội nội bộ.&#10;- Tối ưu hóa truy vấn SQL, giảm thời gian phản hồi API xuống 25%." style="resize:vertical;">{{ $exp['desc'] ?? '' }}</textarea>
                </div>
              </div>
            @empty
              <div class="repeater-item" style="border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; position: relative; background: #fafafa;">
                <button type="button" class="btn-remove" onclick="removeRepeaterRow(this)" style="position: absolute; top: 12px; right: 12px; border: none; background: transparent; color: var(--danger); cursor: pointer; display:none;" title="Xóa dòng này">
                  <i class="fas fa-times-circle fa-lg"></i>
                </button>
                <div class="grid-2 gap-12 mb-12">
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Tên công ty</label>
                    <input type="text" name="experience[0][company]" class="form-control" placeholder="FPT Software">
                  </div>
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Vị trí / Chức vụ</label>
                    <input type="text" name="experience[0][role]" class="form-control" placeholder="Lập trình viên Backend PHP">
                  </div>
                </div>
                <div class="grid-2 gap-12 mb-12">
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Thời gian bắt đầu</label>
                    <input type="text" name="experience[0][year_start]" class="form-control" placeholder="06/2023">
                  </div>
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Thời gian kết thúc</label>
                    <input type="text" name="experience[0][year_end]" class="form-control" placeholder="Hiện tại">
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label fs-11 mb-4">Mô tả công việc & Thành tích</label>
                  <textarea name="experience[0][desc]" class="form-control" rows="3" placeholder="- Xây dựng RESTful API cho dự án mạng xã hội nội bộ.&#10;- Tối ưu hóa truy vấn SQL, giảm thời gian phản hồi API xuống 25%." style="resize:vertical;"></textarea>
                </div>
              </div>
            @endforelse
          </div>
        </div>
      </div>

      {{-- 6. Dự án nổi bật --}}
      <div class="card shadow-sm" style="border-radius: var(--radius-lg);">
        <div class="card-header flex-between" style="background: #f8fafc; border-bottom: 1px solid var(--border); padding: 12px 24px;">
          <span class="fw-700 fs-15 text-primary"><i class="fas fa-project-diagram mr-6"></i> 6. Dự án nổi bật</span>
          <button type="button" class="btn btn-sm btn-outline" style="border-color:#1a73e8; color:#1a73e8; padding: 4px 10px;" onclick="addProjectRow()">
            <i class="fas fa-plus"></i> Thêm mới
          </button>
        </div>
        <div class="card-body" style="padding: 24px;">
          <div id="project-wrapper" class="flex-col gap-16">
            @php
              $projects = old('projects', $cvData->projects ?? []);
            @endphp
            
            @forelse($projects as $index => $proj)
              <div class="repeater-item" style="border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; position: relative; background: #fafafa;">
                <button type="button" class="btn-remove" onclick="removeRepeaterRow(this)" style="position: absolute; top: 12px; right: 12px; border: none; background: transparent; color: var(--danger); cursor: pointer;" title="Xóa dòng này">
                  <i class="fas fa-times-circle fa-lg"></i>
                </button>
                <div class="grid-2 gap-12 mb-12">
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Tên dự án</label>
                    <input type="text" name="projects[{{ $index }}][name]" class="form-control" value="{{ $proj['name'] ?? '' }}" placeholder="Website Thương mại Điện tử" required>
                  </div>
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Công nghệ sử dụng</label>
                    <input type="text" name="projects[{{ $index }}][tech]" class="form-control" value="{{ $proj['tech'] ?? '' }}" placeholder="Laravel, VueJS, MySQL, Docker">
                  </div>
                </div>
                <div class="form-group mb-12">
                  <label class="form-label fs-11 mb-4">Đường dẫn dự án / Github</label>
                  <input type="url" name="projects[{{ $index }}][url]" class="form-control" value="{{ $proj['url'] ?? '' }}" placeholder="https://github.com/nguyenvana/ecommerce">
                </div>
                <div class="form-group">
                  <label class="form-label fs-11 mb-4">Mô tả dự án & Vai trò của bạn</label>
                  <textarea name="projects[{{ $index }}][desc]" class="form-control" rows="3" placeholder="Website bán quần áo với đầy đủ tính năng giỏ hàng, thanh toán qua cổng VNPay, quản trị sản phẩm. Vai trò: Fullstack Developer thiết kế database và API thanh toán." style="resize:vertical;">{{ $proj['desc'] ?? '' }}</textarea>
                </div>
              </div>
            @empty
              <div class="repeater-item" style="border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; position: relative; background: #fafafa;">
                <button type="button" class="btn-remove" onclick="removeRepeaterRow(this)" style="position: absolute; top: 12px; right: 12px; border: none; background: transparent; color: var(--danger); cursor: pointer; display:none;" title="Xóa dòng này">
                  <i class="fas fa-times-circle fa-lg"></i>
                </button>
                <div class="grid-2 gap-12 mb-12">
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Tên dự án</label>
                    <input type="text" name="projects[0][name]" class="form-control" placeholder="Website Thương mại Điện tử">
                  </div>
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Công nghệ sử dụng</label>
                    <input type="text" name="projects[0][tech]" class="form-control" placeholder="Laravel, VueJS, MySQL, Docker">
                  </div>
                </div>
                <div class="form-group mb-12">
                  <label class="form-label fs-11 mb-4">Đường dẫn dự án / Github</label>
                  <input type="url" name="projects[0][url]" class="form-control" placeholder="https://github.com/nguyenvana/ecommerce">
                </div>
                <div class="form-group">
                  <label class="form-label fs-11 mb-4">Mô tả dự án & Vai trò của bạn</label>
                  <textarea name="projects[0][desc]" class="form-control" rows="3" placeholder="Website bán quần áo với đầy đủ tính năng giỏ hàng, thanh toán qua cổng VNPay, quản trị sản phẩm. Vai trò: Fullstack Developer thiết kế database và API thanh toán." style="resize:vertical;"></textarea>
                </div>
              </div>
            @endforelse
          </div>
        </div>
      </div>

      {{-- 7. Chứng chỉ --}}
      <div class="card shadow-sm" style="border-radius: var(--radius-lg);">
        <div class="card-header flex-between" style="background: #f8fafc; border-bottom: 1px solid var(--border); padding: 12px 24px;">
          <span class="fw-700 fs-15 text-primary"><i class="fas fa-award mr-6"></i> 7. Chứng chỉ</span>
          <button type="button" class="btn btn-sm btn-outline" style="border-color:#1a73e8; color:#1a73e8; padding: 4px 10px;" onclick="addCertificationRow()">
            <i class="fas fa-plus"></i> Thêm mới
          </button>
        </div>
        <div class="card-body" style="padding: 24px;">
          <div id="certification-wrapper" class="flex-col gap-16">
            @php
              $certifications = old('certifications', $cvData->certifications ?? []);
            @endphp
            
            @forelse($certifications as $index => $cert)
              <div class="repeater-item" style="border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; position: relative; background: #fafafa;">
                <button type="button" class="btn-remove" onclick="removeRepeaterRow(this)" style="position: absolute; top: 12px; right: 12px; border: none; background: transparent; color: var(--danger); cursor: pointer;" title="Xóa dòng này">
                  <i class="fas fa-times-circle fa-lg"></i>
                </button>
                <div class="grid gap-12" style="grid-template-columns: 2fr 2fr 1fr;">
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Tên chứng chỉ</label>
                    <input type="text" name="certifications[{{ $index }}][name]" class="form-control" value="{{ $cert['name'] ?? '' }}" placeholder="AWS Certified Solutions Architect" required>
                  </div>
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Tổ chức cấp</label>
                    <input type="text" name="certifications[{{ $index }}][issuer]" class="form-control" value="{{ $cert['issuer'] ?? '' }}" placeholder="Amazon Web Services">
                  </div>
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Năm nhận</label>
                    <input type="text" name="certifications[{{ $index }}][year]" class="form-control" value="{{ $cert['year'] ?? '' }}" placeholder="2023">
                  </div>
                </div>
              </div>
            @empty
              <div class="repeater-item" style="border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; position: relative; background: #fafafa;">
                <button type="button" class="btn-remove" onclick="removeRepeaterRow(this)" style="position: absolute; top: 12px; right: 12px; border: none; background: transparent; color: var(--danger); cursor: pointer; display:none;" title="Xóa dòng này">
                  <i class="fas fa-times-circle fa-lg"></i>
                </button>
                <div class="grid gap-12" style="grid-template-columns: 2fr 2fr 1fr;">
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Tên chứng chỉ</label>
                    <input type="text" name="certifications[0][name]" class="form-control" placeholder="AWS Certified Solutions Architect">
                  </div>
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Tổ chức cấp</label>
                    <input type="text" name="certifications[0][issuer]" class="form-control" placeholder="Amazon Web Services">
                  </div>
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Năm nhận</label>
                    <input type="text" name="certifications[0][year]" class="form-control" placeholder="2023">
                  </div>
                </div>
              </div>
            @endforelse
          </div>
        </div>
      </div>

      {{-- 8. Ngoại ngữ --}}
      <div class="card shadow-sm" style="border-radius: var(--radius-lg);">
        <div class="card-header flex-between" style="background: #f8fafc; border-bottom: 1px solid var(--border); padding: 12px 24px;">
          <span class="fw-700 fs-15 text-primary"><i class="fas fa-language mr-6"></i> 8. Ngoại ngữ</span>
          <button type="button" class="btn btn-sm btn-outline" style="border-color:#1a73e8; color:#1a73e8; padding: 4px 10px;" onclick="addLanguageRow()">
            <i class="fas fa-plus"></i> Thêm mới
          </button>
        </div>
        <div class="card-body" style="padding: 24px;">
          <div id="language-wrapper" class="flex-col gap-16">
            @php
              $languages = old('languages', $cvData->languages ?? []);
            @endphp
            
            @forelse($languages as $index => $lang)
              <div class="repeater-item" style="border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; position: relative; background: #fafafa;">
                <button type="button" class="btn-remove" onclick="removeRepeaterRow(this)" style="position: absolute; top: 12px; right: 12px; border: none; background: transparent; color: var(--danger); cursor: pointer;" title="Xóa dòng này">
                  <i class="fas fa-times-circle fa-lg"></i>
                </button>
                <div class="grid-2 gap-12">
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Ngoại ngữ</label>
                    <input type="text" name="languages[{{ $index }}][lang]" class="form-control" value="{{ $lang['lang'] ?? '' }}" placeholder="Tiếng Anh" required>
                  </div>
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Trình độ / Chứng chỉ</label>
                    <input type="text" name="languages[{{ $index }}][level]" class="form-control" value="{{ $lang['level'] ?? '' }}" placeholder="IELTS 7.0 / Giao tiếp trôi chảy" required>
                  </div>
                </div>
              </div>
            @empty
              <div class="repeater-item" style="border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; position: relative; background: #fafafa;">
                <button type="button" class="btn-remove" onclick="removeRepeaterRow(this)" style="position: absolute; top: 12px; right: 12px; border: none; background: transparent; color: var(--danger); cursor: pointer; display:none;" title="Xóa dòng này">
                  <i class="fas fa-times-circle fa-lg"></i>
                </button>
                <div class="grid-2 gap-12">
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Ngoại ngữ</label>
                    <input type="text" name="languages[0][lang]" class="form-control" placeholder="Tiếng Anh">
                  </div>
                  <div class="form-group">
                    <label class="form-label fs-11 mb-4">Trình độ / Chứng chỉ</label>
                    <input type="text" name="languages[0][level]" class="form-control" placeholder="IELTS 7.0 / Giao tiếp trôi chảy">
                  </div>
                </div>
              </div>
            @endforelse
          </div>
        </div>
      </div>

    </div>

    {{-- Cột phải: Cấu hình Template & Submit --}}
    <div class="flex-col gap-20" style="position: sticky; top: 20px;">
      
      {{-- Card Chọn Template --}}
      <div class="card shadow-sm" style="border-radius: var(--radius-lg); border-top: 3px solid #1a73e8;">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border);">
          <span class="fw-700 fs-15 text-primary"><i class="fas fa-palette mr-6"></i> Giao diện CV</span>
        </div>
        <div class="card-body" style="padding: 20px;">
          <div class="form-group mb-16">
            <label class="form-label fw-600 fs-12 mb-6">Chọn mẫu CV thiết kế <span class="required" style="color:var(--danger)">*</span></label>
            <select name="template" class="form-control @error('template') is-invalid @enderror" required style="cursor: pointer;">
              @foreach($templates as $tpl)
                <option value="{{ $tpl }}" {{ old('template', $cvData->template ?? 'default') === $tpl ? 'selected' : '' }}>
                  Mẫu {{ ucfirst($tpl) }}
                </option>
              @endforeach
            </select>
            @error('template')
              <div class="invalid-feedback" style="color:var(--danger); font-size:11px; margin-top:4px;">{{ $message }}</div>
            @enderror
          </div>

          <div style="border: 1px solid var(--border); border-radius: 8px; padding: 12px; background: #fafafa; font-size: 12px;" class="mb-16">
            <div class="fw-700 mb-6"><i class="fas fa-info-circle text-primary"></i> Chi tiết các mẫu:</div>
            <ul style="padding-left: 18px; margin: 0; display:flex; flex-direction:column; gap:6px;">
              <li><strong>Default:</strong> Thiết kế 1 cột cổ điển, phù hợp mọi ngành nghề, dễ đọc.</li>
              <li><strong>Modern:</strong> Thiết kế 2 cột hiện đại, tận dụng không gian, chuyên nghiệp.</li>
              <li><strong>Minimal:</strong> Thiết kế tối giản, tinh tế, thích hợp cho kỹ sư phát triển phần mềm.</li>
            </ul>
          </div>

          <button type="submit" class="btn btn-primary btn-block btn-lg" style="background:#1a73e8; border-color:#1a73e8; justify-content: center; font-size: 14px; font-weight:700;">
            <i class="fas fa-save mr-6"></i> Lưu thông tin & Xem Preview
          </button>
        </div>
      </div>

      {{-- Thẻ hướng dẫn điền nhanh --}}
      <div class="card shadow-sm" style="border-radius: var(--radius-lg); background: linear-gradient(to bottom, #ffffff, #f8fafc);">
        <div class="card-body" style="padding: 20px; font-size: 12px; line-height: 1.6;">
          <div class="fw-700 fs-13 mb-8 text-secondary"><i class="fas fa-lightbulb text-warning mr-6"></i> Mẹo điền nhanh</div>
          <p class="mb-8">Bạn có thể thêm nhiều mục học vấn, kinh nghiệm làm việc hoặc dự án tùy thích bằng cách ấn nút <strong>"Thêm mới"</strong> ở mỗi thẻ tương ứng.</p>
          <p class="mb-0">Hệ thống sẽ lưu lại thông tin này của bạn. Lần sau bạn chỉ cần chỉnh sửa các nội dung thay đổi mà không phải nhập lại từ đầu.</p>
        </div>
      </div>

    </div>

  </div>
</form>

{{-- JS Templates dùng để nhân bản (Clone) các repeatable rows động --}}
<template id="education-template">
  <div class="repeater-item" style="border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; position: relative; background: #fafafa; margin-top: 12px; display: none;">
    <button type="button" class="btn-remove" onclick="removeRepeaterRow(this)" style="position: absolute; top: 12px; right: 12px; border: none; background: transparent; color: var(--danger); cursor: pointer;" title="Xóa dòng này">
      <i class="fas fa-times-circle fa-lg"></i>
    </button>
    <div class="grid-2 gap-12 mb-12">
      <div class="form-group">
        <label class="form-label fs-11 mb-4">Trường học / Trung tâm</label>
        <input type="text" name="education[__INDEX__][school]" class="form-control" placeholder="Đại học Bách Khoa Hà Nội" required>
      </div>
      <div class="form-group">
        <label class="form-label fs-11 mb-4">Ngành học / Bằng cấp</label>
        <input type="text" name="education[__INDEX__][degree]" class="form-control" placeholder="Cử nhân Công nghệ thông tin" required>
      </div>
    </div>
    <div class="grid-2 gap-12">
      <div class="form-group">
        <label class="form-label fs-11 mb-4">Thời gian bắt đầu</label>
        <input type="text" name="education[__INDEX__][year_start]" class="form-control" placeholder="2018">
      </div>
      <div class="form-group">
        <label class="form-label fs-11 mb-4">Thời gian kết thúc</label>
        <input type="text" name="education[__INDEX__][year_end]" class="form-control" placeholder="2022">
      </div>
    </div>
  </div>
</template>

<template id="experience-template">
  <div class="repeater-item" style="border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; position: relative; background: #fafafa; margin-top: 12px; display: none;">
    <button type="button" class="btn-remove" onclick="removeRepeaterRow(this)" style="position: absolute; top: 12px; right: 12px; border: none; background: transparent; color: var(--danger); cursor: pointer;" title="Xóa dòng này">
      <i class="fas fa-times-circle fa-lg"></i>
    </button>
    <div class="grid-2 gap-12 mb-12">
      <div class="form-group">
        <label class="form-label fs-11 mb-4">Tên công ty</label>
        <input type="text" name="experience[__INDEX__][company]" class="form-control" placeholder="FPT Software" required>
      </div>
      <div class="form-group">
        <label class="form-label fs-11 mb-4">Vị trí / Chức vụ</label>
        <input type="text" name="experience[__INDEX__][role]" class="form-control" placeholder="Lập trình viên Backend PHP" required>
      </div>
    </div>
    <div class="grid-2 gap-12 mb-12">
      <div class="form-group">
        <label class="form-label fs-11 mb-4">Thời gian bắt đầu</label>
        <input type="text" name="experience[__INDEX__][year_start]" class="form-control" placeholder="06/2023">
      </div>
      <div class="form-group">
        <label class="form-label fs-11 mb-4">Thời gian kết thúc</label>
        <input type="text" name="experience[__INDEX__][year_end]" class="form-control" placeholder="Hiện tại">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label fs-11 mb-4">Mô tả công việc & Thành tích</label>
      <textarea name="experience[__INDEX__][desc]" class="form-control" rows="3" placeholder="- Xây dựng RESTful API cho dự án mạng xã hội nội bộ.&#10;- Tối ưu hóa truy vấn SQL, giảm thời gian phản hồi API xuống 25%." style="resize:vertical;"></textarea>
    </div>
  </div>
</template>

<template id="project-template">
  <div class="repeater-item" style="border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; position: relative; background: #fafafa; margin-top: 12px; display: none;">
    <button type="button" class="btn-remove" onclick="removeRepeaterRow(this)" style="position: absolute; top: 12px; right: 12px; border: none; background: transparent; color: var(--danger); cursor: pointer;" title="Xóa dòng này">
      <i class="fas fa-times-circle fa-lg"></i>
    </button>
    <div class="grid-2 gap-12 mb-12">
      <div class="form-group">
        <label class="form-label fs-11 mb-4">Tên dự án</label>
        <input type="text" name="projects[__INDEX__][name]" class="form-control" placeholder="Website Thương mại Điện tử" required>
      </div>
      <div class="form-group">
        <label class="form-label fs-11 mb-4">Công nghệ sử dụng</label>
        <input type="text" name="projects[__INDEX__][tech]" class="form-control" placeholder="Laravel, VueJS, MySQL, Docker">
      </div>
    </div>
    <div class="form-group mb-12">
      <label class="form-label fs-11 mb-4">Đường dẫn dự án / Github</label>
      <input type="url" name="projects[__INDEX__][url]" class="form-control" placeholder="https://github.com/nguyenvana/ecommerce">
    </div>
    <div class="form-group">
      <label class="form-label fs-11 mb-4">Mô tả dự án & Vai trò của bạn</label>
      <textarea name="projects[__INDEX__][desc]" class="form-control" rows="3" placeholder="Website bán quần áo với đầy đủ tính năng giỏ hàng, thanh toán qua cổng VNPay, quản trị sản phẩm. Vai trò: Fullstack Developer thiết kế database và API thanh toán." style="resize:vertical;"></textarea>
    </div>
  </div>
</template>

<template id="certification-template">
  <div class="repeater-item" style="border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; position: relative; background: #fafafa; margin-top: 12px; display: none;">
    <button type="button" class="btn-remove" onclick="removeRepeaterRow(this)" style="position: absolute; top: 12px; right: 12px; border: none; background: transparent; color: var(--danger); cursor: pointer;" title="Xóa dòng này">
      <i class="fas fa-times-circle fa-lg"></i>
    </button>
    <div class="grid gap-12" style="grid-template-columns: 2fr 2fr 1fr;">
      <div class="form-group">
        <label class="form-label fs-11 mb-4">Tên chứng chỉ</label>
        <input type="text" name="certifications[__INDEX__][name]" class="form-control" placeholder="AWS Certified Solutions Architect" required>
      </div>
      <div class="form-group">
        <label class="form-label fs-11 mb-4">Tổ chức cấp</label>
        <input type="text" name="certifications[__INDEX__][issuer]" class="form-control" placeholder="Amazon Web Services">
      </div>
      <div class="form-group">
        <label class="form-label fs-11 mb-4">Năm nhận</label>
        <input type="text" name="certifications[__INDEX__][year]" class="form-control" placeholder="2023">
      </div>
    </div>
  </div>
</template>

<template id="language-template">
  <div class="repeater-item" style="border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; position: relative; background: #fafafa; margin-top: 12px; display: none;">
    <button type="button" class="btn-remove" onclick="removeRepeaterRow(this)" style="position: absolute; top: 12px; right: 12px; border: none; background: transparent; color: var(--danger); cursor: pointer;" title="Xóa dòng này">
      <i class="fas fa-times-circle fa-lg"></i>
    </button>
    <div class="grid-2 gap-12">
      <div class="form-group">
        <label class="form-label fs-11 mb-4">Ngoại ngữ</label>
        <input type="text" name="languages[__INDEX__][lang]" class="form-control" placeholder="Tiếng Anh" required>
      </div>
      <div class="form-group">
        <label class="form-label fs-11 mb-4">Trình độ / Chứng chỉ</label>
        <input type="text" name="languages[__INDEX__][level]" class="form-control" placeholder="IELTS 7.0 / Giao tiếp trôi chảy" required>
      </div>
    </div>
  </div>
</template>

@push('scripts')
<script>
// Các biến theo dõi số lượng phần tử hiện tại để cấp index duy nhất
let counters = {
  education: {{ count($education) > 0 ? count($education) : 1 }},
  experience: {{ count($experience) > 0 ? count($experience) : 1 }},
  projects: {{ count($projects) > 0 ? count($projects) : 1 }},
  certifications: {{ count($certifications) > 0 ? count($certifications) : 1 }},
  languages: {{ count($languages) > 0 ? count($languages) : 1 }}
};

// Hàm xem trước ảnh thẻ khi chọn tệp
function previewPhoto(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      // Tìm hoặc tạo thẻ img preview
      let img = document.getElementById('photo-preview-img');
      const placeholder = document.getElementById('photo-preview-placeholder');
      
      if (!img) {
        img = document.createElement('img');
        img.id = 'photo-preview-img';
        img.style.width = '100%';
        img.style.height = '100%';
        img.style.objectFit = 'cover';
        placeholder.parentNode.appendChild(img);
      }
      
      img.src = e.target.result;
      img.style.display = 'block';
      if (placeholder) {
        placeholder.style.display = 'none';
      }
    }
    reader.readAsDataURL(input.files[0]);
  }
}

// Hàm thêm dòng cho Học vấn
function addEducationRow() {
  cloneRow('education', 'education-wrapper', 'education-template');
}

// Hàm thêm dòng cho Kinh nghiệm
function addExperienceRow() {
  cloneRow('experience', 'experience-wrapper', 'experience-template');
}

// Hàm thêm dòng cho Dự án
function addProjectRow() {
  cloneRow('projects', 'project-wrapper', 'project-template');
}

// Hàm thêm dòng cho Chứng chỉ
function addCertificationRow() {
  cloneRow('certifications', 'certification-wrapper', 'certification-template');
}

// Hàm thêm dòng cho Ngoại ngữ
function addLanguageRow() {
  cloneRow('languages', 'language-wrapper', 'language-template');
}

// Hàm bổ trợ nhân bản phần tử và gán index thích hợp
function cloneRow(type, wrapperId, templateId) {
  const wrapper = document.getElementById(wrapperId);
  const template = document.getElementById(templateId);
  
  if (!wrapper || !template) return;
  
  const clone = template.content.cloneNode(true);
  const item = clone.querySelector('.repeater-item');
  
  // Thay thế chuỗi __INDEX__ bằng chỉ số tăng dần
  const index = counters[type]++;
  item.innerHTML = item.innerHTML.replace(/__INDEX__/g, index);
  
  // Hiển thị phần tử
  item.style.display = 'block';
  
  // Hiển thị nút xóa
  const removeBtn = item.querySelector('.btn-remove');
  if (removeBtn) {
    removeBtn.style.display = 'block';
  }
  
  // Thêm vào wrapper
  wrapper.appendChild(item);
  
  // Hiển thị nút xóa của các phần tử hiện có nếu trước đó chỉ có 1 phần tử ẩn nút xóa
  updateRemoveButtons(wrapper);
}

// Hàm xóa dòng
function removeRepeaterRow(button) {
  const item = button.closest('.repeater-item');
  const wrapper = item.parentNode;
  
  if (!wrapper) return;
  
  // Xóa phần tử
  item.remove();
  
  // Cập nhật lại trạng thái nút xóa
  updateRemoveButtons(wrapper);
}

// Quản lý việc hiển thị nút xóa: không cho xóa nếu chỉ còn 1 phần tử
function updateRemoveButtons(wrapper) {
  const items = wrapper.querySelectorAll('.repeater-item');
  if (items.length <= 1) {
    items.forEach(item => {
      const btn = item.querySelector('.btn-remove');
      if (btn) btn.style.display = 'none';
    });
  } else {
    items.forEach(item => {
      const btn = item.querySelector('.btn-remove');
      if (btn) btn.style.display = 'block';
    });
  }
}

// Khi tải trang, cập nhật nút xóa ban đầu
document.addEventListener('DOMContentLoaded', function() {
  updateRemoveButtons(document.getElementById('education-wrapper'));
  updateRemoveButtons(document.getElementById('experience-wrapper'));
  updateRemoveButtons(document.getElementById('project-wrapper'));
  updateRemoveButtons(document.getElementById('certification-wrapper'));
  updateRemoveButtons(document.getElementById('language-wrapper'));
});

// ──────────────────────────────────────────────────────────────
// AI CV PARSER JS LOGIC
// ──────────────────────────────────────────────────────────────
let aiAbortController = null;
let parsedCvData = null;

function handleAiDragOver(e) {
  e.preventDefault();
  document.getElementById('ai-upload-area').classList.add('dragover');
}

function handleAiDragLeave(e) {
  e.preventDefault();
  document.getElementById('ai-upload-area').classList.remove('dragover');
}

function handleAiDrop(e) {
  e.preventDefault();
  document.getElementById('ai-upload-area').classList.remove('dragover');
  const files = e.dataTransfer.files;
  if (files.length > 0) {
    uploadAiCv(files[0]);
  }
}

function handleAiFileSelect(e) {
  const file = e.target.files[0];
  if (file) {
    uploadAiCv(file);
  }
}

function uploadAiCv(file) {
  if (file.type !== 'application/pdf') {
    alert('Hệ thống chỉ hỗ trợ file định dạng PDF.');
    return;
  }
  
  if (file.size > 5 * 1024 * 1024) {
    alert('Dung lượng file tối đa là 5MB.');
    return;
  }

  aiAbortController = new AbortController();
  const signal = aiAbortController.signal;

  document.getElementById('ai-loading-modal').classList.add('active');

  const formData = new FormData();
  formData.append('cv_file', file);

  fetch('{{ route('user.cv.ai-parse') }}', {
    method: 'POST',
    body: formData,
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json'
    },
    signal: signal
  })
  .then(response => {
    if (!response.ok) {
      return response.json().then(err => { throw err; });
    }
    return response.json();
  })
  .then(res => {
    document.getElementById('ai-loading-modal').classList.remove('active');
    if (res.success && res.data) {
      parsedCvData = res.data;
      document.getElementById('ai-confirm-modal').classList.add('active');
    } else {
      alert(res.error || 'Không thể phân tích dữ liệu CV.');
    }
  })
  .catch(err => {
    document.getElementById('ai-loading-modal').classList.remove('active');
    if (err.name === 'AbortError') {
      console.log('Quá trình phân tích đã bị người dùng hủy.');
    } else {
      alert(err.error || err.message || 'Đã xảy ra lỗi trong quá trình xử lý.');
    }
  })
  .finally(() => {
    document.getElementById('ai-cv-input').value = '';
  });
}

function abortAiParsing() {
  if (aiAbortController) {
    aiAbortController.abort();
  }
  document.getElementById('ai-loading-modal').classList.remove('active');
}

function closeConfirmModal() {
  document.getElementById('ai-confirm-modal').classList.remove('active');
  parsedCvData = null;
}

function applyParsedCvData() {
  if (!parsedCvData) return;

  const data = parsedCvData;

  // 1. Điền các trường đơn giản
  if (data.full_name) {
    const input = document.querySelector('input[name="full_name"]');
    if (input) input.value = data.full_name;
  }
  if (data.email) {
    const input = document.querySelector('input[name="email"]');
    if (input) input.value = data.email;
  }
  if (data.phone) {
    const input = document.querySelector('input[name="phone"]');
    if (input) input.value = data.phone;
  }
  if (data.address) {
    const input = document.querySelector('input[name="address"]');
    if (input) input.value = data.address;
  }
  if (data.objective) {
    const textarea = document.querySelector('textarea[name="objective"]');
    if (textarea) textarea.value = data.objective;
  }
  if (data.skills_text) {
    const textarea = document.querySelector('textarea[name="skills_text"]');
    if (textarea) textarea.value = data.skills_text;
  }

  // 2. Điền các repeater
  // 2.1 Học vấn (education)
  clearWrapper('education-wrapper');
  if (data.education && data.education.length > 0) {
    data.education.forEach(edu => {
      cloneRow('education', 'education-wrapper', 'education-template');
      const lastRow = document.getElementById('education-wrapper').lastElementChild;
      
      const schoolInput = lastRow.querySelector('input[name^="education["][name$="][school]"]');
      const degreeInput = lastRow.querySelector('input[name^="education["][name$="][degree]"]');
      const startInput = lastRow.querySelector('input[name^="education["][name$="][year_start]"]');
      const endInput = lastRow.querySelector('input[name^="education["][name$="][year_end]"]');
      
      if (schoolInput) schoolInput.value = edu.school || '';
      if (degreeInput) degreeInput.value = edu.degree || '';
      if (startInput) startInput.value = edu.year_start || '';
      if (endInput) endInput.value = edu.year_end || '';
    });
  } else {
    cloneRow('education', 'education-wrapper', 'education-template');
  }
  updateRemoveButtons(document.getElementById('education-wrapper'));

  // 2.2 Kinh nghiệm (experience)
  clearWrapper('experience-wrapper');
  if (data.experience && data.experience.length > 0) {
    data.experience.forEach(exp => {
      cloneRow('experience', 'experience-wrapper', 'experience-template');
      const lastRow = document.getElementById('experience-wrapper').lastElementChild;
      
      const companyInput = lastRow.querySelector('input[name^="experience["][name$="][company]"]');
      const roleInput = lastRow.querySelector('input[name^="experience["][name$="][role]"]');
      const startInput = lastRow.querySelector('input[name^="experience["][name$="][year_start]"]');
      const endInput = lastRow.querySelector('input[name^="experience["][name$="][year_end]"]');
      const descText = lastRow.querySelector('textarea[name^="experience["][name$="][desc]"]');
      
      if (companyInput) companyInput.value = exp.company || '';
      if (roleInput) roleInput.value = exp.role || '';
      if (startInput) startInput.value = exp.year_start || '';
      if (endInput) endInput.value = exp.year_end || '';
      if (descText) descText.value = exp.desc || '';
    });
  } else {
    cloneRow('experience', 'experience-wrapper', 'experience-template');
  }
  updateRemoveButtons(document.getElementById('experience-wrapper'));

  // 2.3 Dự án (projects)
  clearWrapper('project-wrapper');
  if (data.projects && data.projects.length > 0) {
    data.projects.forEach(proj => {
      cloneRow('projects', 'project-wrapper', 'project-template');
      const lastRow = document.getElementById('project-wrapper').lastElementChild;
      
      const nameInput = lastRow.querySelector('input[name^="projects["][name$="][name]"]');
      const techInput = lastRow.querySelector('input[name^="projects["][name$="][tech]"]');
      const urlInput = lastRow.querySelector('input[name^="projects["][name$="][url]"]');
      const descText = lastRow.querySelector('textarea[name^="projects["][name$="][desc]"]');
      
      if (nameInput) nameInput.value = proj.name || '';
      if (techInput) techInput.value = proj.tech || '';
      if (urlInput) urlInput.value = proj.url || '';
      if (descText) descText.value = proj.desc || '';
    });
  } else {
    cloneRow('projects', 'project-wrapper', 'project-template');
  }
  updateRemoveButtons(document.getElementById('project-wrapper'));

  // 2.4 Chứng chỉ (certifications)
  clearWrapper('certification-wrapper');
  if (data.certifications && data.certifications.length > 0) {
    data.certifications.forEach(cert => {
      cloneRow('certifications', 'certification-wrapper', 'certification-template');
      const lastRow = document.getElementById('certification-wrapper').lastElementChild;
      
      const nameInput = lastRow.querySelector('input[name^="certifications["][name$="][name]"]');
      const issuerInput = lastRow.querySelector('input[name^="certifications["][name$="][issuer]"]');
      const yearInput = lastRow.querySelector('input[name^="certifications["][name$="][year]"]');
      
      if (nameInput) nameInput.value = cert.name || '';
      if (issuerInput) issuerInput.value = cert.issuer || '';
      if (yearInput) yearInput.value = cert.year || '';
    });
  } else {
    cloneRow('certifications', 'certification-wrapper', 'certification-template');
  }
  updateRemoveButtons(document.getElementById('certification-wrapper'));

  // 2.5 Ngoại ngữ (languages)
  clearWrapper('language-wrapper');
  if (data.languages && data.languages.length > 0) {
    data.languages.forEach(lang => {
      cloneRow('languages', 'language-wrapper', 'language-template');
      const lastRow = document.getElementById('language-wrapper').lastElementChild;
      
      const langInput = lastRow.querySelector('input[name^="languages["][name$="][lang]"]');
      const levelInput = lastRow.querySelector('input[name^="languages["][name$="][level]"]');
      
      if (langInput) langInput.value = lang.lang || '';
      if (levelInput) levelInput.value = lang.level || '';
    });
  } else {
    cloneRow('languages', 'language-wrapper', 'language-template');
  }
  updateRemoveButtons(document.getElementById('language-wrapper'));

  closeConfirmModal();
  alert('Đã tự động điền dữ liệu thành công! Vui lòng kiểm tra lại form.');
}

function clearWrapper(wrapperId) {
  const wrapper = document.getElementById(wrapperId);
  if (wrapper) {
    wrapper.innerHTML = '';
  }
}
</script>
@endpush

@push('styles')
<style>
#ai-upload-area:hover, #ai-upload-area.dragover {
  background: #ede9fe !important;
  border-color: #7c3aed !important;
  box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15);
}
.ai-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.3s ease;
}
.ai-modal-overlay.active {
  opacity: 1;
  pointer-events: auto;
}
.ai-modal-card {
  background: white;
  border-radius: var(--radius-lg);
  width: 90%;
  max-width: 500px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  padding: 30px;
  text-align: center;
  transform: scale(0.95);
  transition: transform 0.3s ease;
}
.ai-modal-overlay.active .ai-modal-card {
  transform: scale(1);
}
.ai-spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #ede9fe;
  border-top: 4px solid #7c3aed;
  border-radius: 50%;
  animation: ai-spin 1s linear infinite;
  margin: 0 auto 20px;
}
@keyframes ai-spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
.ai-btn-cancel {
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #cbd5e1;
  padding: 8px 16px;
  border-radius: var(--radius);
  font-weight: 600;
  cursor: pointer;
  margin-top: 15px;
  transition: all 0.2s;
}
.ai-btn-cancel:hover {
  background: #e2e8f0;
  color: #334155;
}
</style>
@endpush

{{-- Loading Modal --}}
<div class="ai-modal-overlay" id="ai-loading-modal">
  <div class="ai-modal-card">
    <div class="ai-spinner"></div>
    <h3 class="fw-700 fs-16 mb-10" style="color:var(--text-dark)">Đang phân tích CV của bạn...</h3>
    <p class="text-muted fs-13 mb-20" style="line-height:1.5;">
      Gemini AI đang trích xuất thông tin học vấn, kinh nghiệm làm việc và các thông tin khác từ file CV của bạn. Quá trình này có thể mất từ 10 - 20 giây.
    </p>
    <button type="button" class="ai-btn-cancel" onclick="abortAiParsing()">Hủy quá trình</button>
  </div>
</div>

{{-- Confirmation Modal --}}
<div class="ai-modal-overlay" id="ai-confirm-modal">
  <div class="ai-modal-card" style="max-width: 450px;">
    <div style="background: #e0f2fe; color: #0284c7; width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
      <i class="fas fa-question-circle fa-2x"></i>
    </div>
    <h3 class="fw-700 fs-16 mb-10" style="color:var(--text-dark)">Xác nhận điền dữ liệu bằng AI</h3>
    <p class="text-muted fs-13 mb-20" style="line-height:1.5;">
      AI đã trích xuất thành công thông tin từ CV của bạn. Bạn có muốn tự động điền các thông tin này vào form?
      <br>
      <strong style="color:var(--danger)">Lưu ý:</strong> Hành động này sẽ thay thế (ghi đè) toàn bộ thông tin bạn đang nhập trên form hiện tại.
    </p>
    <div style="display:flex; justify-content:center; gap:10px;">
      <button type="button" class="btn btn-outline" onclick="closeConfirmModal()">Hủy</button>
      <button type="button" class="btn btn-primary" onclick="applyParsedCvData()" style="background:linear-gradient(135deg, #a78bfa 0%, #7c3aed 100%); border:none; box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2); color:white;">Đồng ý & Điền đè</button>
    </div>
  </div>
</div>
@endsection
