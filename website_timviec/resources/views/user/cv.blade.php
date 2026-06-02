@extends('layouts.dashboard')
@section('title', 'Hồ sơ CV của tôi')

@section('content')
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-20 fw-800" style="color:var(--secondary)">Hồ sơ CV của tôi</h1>
    <p class="text-muted fs-13 mt-4">Quản lý CV đã tải lên hoặc tự tay tạo CV online chuyên nghiệp chỉ trong vài phút.</p>
  </div>
</div>

<div class="grid-2 gap-16 mb-20">
  {{-- Upload CV --}}
  <div class="card" style="border-top: 3px solid var(--primary)">
    <div class="card-body" style="padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div style="width: 64px; height: 64px; background: var(--primary-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 24px; color: var(--primary)">
          <i class="fas fa-upload"></i>
        </div>
        <h3 class="fw-700 fs-16 mb-8 text-center">Upload CV có sẵn</h3>
        <p class="text-muted fs-13 mb-16 text-center">Tải lên file CV thiết kế sẵn của bạn để ứng tuyển nhanh chóng.</p>

        @if($user->resume)
          <div class="alert alert-success mb-16" style="font-size: 12px; text-align: left; background: var(--primary-light); color: var(--primary-dark); border: 1px solid var(--primary); padding: 12px; border-radius: var(--radius)">
            <div class="flex-between" style="align-items: center;">
              <div>
                <i class="fas fa-file-pdf"></i> CV hiện tại:<br>
                <strong style="word-break: break-all;">{{ basename($user->resume) }}</strong>
              </div>
              <a href="{{ route('user.cv.view') }}" class="btn btn-sm btn-primary" style="padding: 4px 10px; font-size: 11px; white-space: nowrap;" target="_blank">
                <i class="fas fa-eye"></i> Xem file
              </a>
            </div>
          </div>
        @else
          <div class="alert alert-warning mb-16 text-center" style="font-size: 12px; padding: 12px; border-radius: var(--radius)">
            <i class="fas fa-info-circle"></i> Bạn chưa tải lên file CV nào.
          </div>
        @endif
      </div>

      <form action="{{ route('user.cv.upload') }}" method="POST" enctype="multipart/form-data" class="mt-12">
        @csrf
        <div id="cv-drop-zone" style="border: 2px dashed var(--border); border-radius: var(--radius-lg); padding: 20px; cursor: pointer; transition: var(--transition); margin-bottom: 12px; text-align: center;"
          onclick="document.getElementById('cv-file').click()"
          ondragover="this.style.borderColor='var(--primary)'; event.preventDefault()"
          ondragleave="this.style.borderColor='var(--border)'"
          ondrop="handleCvDrop(event)">
          <div id="cv-placeholder">
            <i class="fas fa-cloud-upload-alt fa-2x" style="color: var(--text-muted); margin-bottom: 8px"></i>
            <div class="fs-13 fw-600">Kéo file vào đây hoặc click để chọn</div>
            <div class="text-muted fs-12 mt-4">PDF, DOC, DOCX — Tối đa 5MB</div>
          </div>
          <div id="cv-selected" style="display: none;" class="text-primary-color fw-600 fs-13"></div>
        </div>
        <input type="file" id="cv-file" name="cv_file" accept=".pdf,.doc,.docx" style="display: none;" onchange="showFileName(this)">
        
        @error('cv_file')
          <div style="color: var(--danger); font-size: 12px; margin-bottom: 10px; text-align: left;">{{ $message }}</div>
        @enderror

        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-upload"></i> Cập nhật / Tải lên CV</button>
      </form>
    </div>
  </div>

  {{-- Create CV Online --}}
  <div class="card" style="border-top: 3px solid #1a73e8">
    <div class="card-body" style="padding: 24px; display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
      <div>
        <div style="width: 64px; height: 64px; background: #e8f4fd; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 24px; color: #1a73e8">
          <i class="fas fa-magic"></i>
        </div>
        <h3 class="fw-700 fs-16 mb-8 text-center">Tạo CV Online</h3>
        <p class="text-muted fs-13 mb-16 text-center font-size: 13px">Hệ thống tạo CV chuyên nghiệp tự động, hỗ trợ tải PDF chuẩn tiếng Việt.</p>

        @if($cvData)
          <div class="alert alert-info mb-16" style="font-size: 12px; text-align: left; background: #e8f4fd; color: #1a73e8; border: 1px solid #1a73e8; padding: 12px; border-radius: var(--radius)">
            <div>
              <i class="fas fa-check-circle"></i> Đã tạo CV Online:<br>
              <strong>{{ $cvData->full_name }}</strong> (Mẫu: <span class="badge" style="background:#1a73e8; color:white; padding: 2px 6px; border-radius: 4px;">{{ ucfirst($cvData->template) }}</span>)
            </div>
            <div class="mt-4 text-muted fs-11">
              Cập nhật lần cuối: {{ $cvData->updated_at->format('d/m/Y H:i') }}
            </div>
          </div>

          <div class="grid-2" style="gap: 8px; margin-bottom: 12px;">
            <a href="{{ route('user.cv.preview') }}" class="btn btn-outline" style="border-color: #1a73e8; color: #1a73e8; justify-content: center; font-size: 13px;">
              <i class="fas fa-eye"></i> Xem & Tải PDF
            </a>
            <a href="{{ route('user.cv.create') }}" class="btn btn-primary" style="background: #1a73e8; border-color: #1a73e8; justify-content: center; font-size: 13px;">
              <i class="fas fa-edit"></i> Chỉnh sửa
            </a>
          </div>

          <form action="{{ route('user.cv.delete') }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa CV online này không? Hành động này sẽ xóa vĩnh viễn dữ liệu CV online của bạn trên hệ thống và không thể hoàn tác.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline btn-block" style="border-color: var(--danger); color: var(--danger); justify-content: center; width: 100%;">
              <i class="fas fa-trash-alt"></i> Xóa CV Online
            </button>
          </form>
        @else
          <div class="alert alert-warning mb-16 text-center" style="font-size: 12px; padding: 12px; border-radius: var(--radius)">
            <i class="fas fa-info-circle"></i> Bạn chưa tạo bản CV online nào.
          </div>

          <div style="text-align: center; margin-top: 20px;">
            <a href="{{ route('user.cv.create') }}" class="btn btn-primary btn-block" style="background: #1a73e8; border-color: #1a73e8; justify-content: center;">
              <i class="fas fa-plus"></i> Thiết kế CV Online ngay
            </a>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- Tips --}}
<div class="card" style="background: linear-gradient(135deg, var(--primary-light), #e8f4fd)">
  <div class="card-body" style="padding: 20px">
    <div class="fw-700 fs-14 mb-12"><i class="fas fa-lightbulb" style="color: var(--primary)"></i> Mẹo tạo CV ấn tượng thu hút nhà tuyển dụng</div>
    <div class="grid-2 gap-12">
      @foreach([
        'Dùng font chuyên nghiệp, trình bày rõ ràng, mạch lạc.',
        'Tập trung vào thành tích định lượng (ví dụ: tăng 30% doanh số, cải thiện 50% tốc độ tải trang).',
        'Tóm tắt mục tiêu nghề nghiệp ngắn gọn nhưng súc tích.',
        'Cung cấp thông tin liên hệ chính xác và chuyên nghiệp (Email, Số điện thoại, GitHub, LinkedIn).'
      ] as $tip)
        <div class="flex gap-8" style="align-items: flex-start; font-size: 13px">
          <i class="fas fa-check-circle" style="color: var(--primary); margin-top: 1px; flex-shrink: 0"></i>
          <span>{{ $tip }}</span>
        </div>
      @endforeach
    </div>
  </div>
</div>

@push('scripts')
<script>
function showFileName(input) {
  if (input.files && input.files[0]) {
    document.getElementById('cv-placeholder').style.display = 'none';
    document.getElementById('cv-selected').style.display = 'block';
    document.getElementById('cv-selected').innerHTML = '<div style="color:var(--primary); font-weight:600;"><i class="fas fa-file-check"></i> ' + input.files[0].name + '</div>';
    document.getElementById('cv-drop-zone').style.borderColor = 'var(--primary)';
  }
}
function handleCvDrop(e) {
  e.preventDefault();
  var files = e.dataTransfer.files;
  if (files.length) { 
    document.getElementById('cv-file').files = files; 
    showFileName(document.getElementById('cv-file')); 
  }
}
</script>
@endpush
@endsection
