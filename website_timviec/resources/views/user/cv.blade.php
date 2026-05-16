@extends('layouts.dashboard')
@section('title', 'Hồ sơ CV của tôi')

@section('content')
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-20 fw-800" style="color:var(--secondary)">Hồ sơ CV của tôi</h1>
    <p class="text-muted fs-13 mt-4">Upload CV hoặc tạo CV online chuyên nghiệp</p>
  </div>
</div>

<div class="grid-2 gap-16 mb-20">
  {{-- Upload CV --}}
  <div class="card" style="border-top:3px solid var(--primary)">
    <div class="card-body" style="padding:24px;text-align:center">
      <div style="width:64px;height:64px;background:var(--primary-light);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:24px;color:var(--primary)">
        <i class="fas fa-upload"></i>
      </div>
      <h3 class="fw-700 fs-16 mb-8">Upload CV có sẵn</h3>
      <p class="text-muted fs-13 mb-16">Tải lên file CV của bạn (PDF, DOC, DOCX)</p>

      @if(auth()->user()->resume)
        <div class="alert alert-success mb-12" style="font-size:12px;text-align:left">
          <i class="fas fa-file-pdf"></i> Đã có CV: <strong>{{ auth()->user()->resume }}</strong>
        </div>
        <a href="{{ url('/user/cv/view') }}" class="btn btn-outline btn-sm" target="_blank">
          <i class="fas fa-eye"></i> Xem CV hiện tại
        </a>
      @endif

      <form action="{{ url('/user/cv') }}" method="POST" enctype="multipart/form-data" class="mt-12">
        @csrf
        <div id="cv-drop-zone" style="border:2px dashed var(--border);border-radius:var(--radius-lg);padding:24px;cursor:pointer;transition:var(--transition);margin-bottom:12px"
          onclick="document.getElementById('cv-file').click()"
          ondragover="this.style.borderColor='var(--primary)';event.preventDefault()"
          ondragleave="this.style.borderColor='var(--border)'"
          ondrop="handleCvDrop(event)">
          <div id="cv-placeholder">
            <i class="fas fa-file-pdf fa-2x" style="color:var(--text-muted);margin-bottom:8px"></i>
            <div class="fs-13 fw-600">Kéo file vào đây hoặc click để chọn</div>
            <div class="text-muted fs-12 mt-4">PDF, DOC, DOCX — Tối đa 5MB</div>
          </div>
          <div id="cv-selected" style="display:none" class="text-primary-color fw-600 fs-13"></div>
        </div>
        <input type="file" id="cv-file" name="resume" accept=".pdf,.doc,.docx" style="display:none" onchange="showFileName(this)">
        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-upload"></i> Tải lên CV</button>
      </form>
    </div>
  </div>

  {{-- Create CV Online --}}
  <div class="card" style="border-top:3px solid #1a73e8">
    <div class="card-body" style="padding:24px;text-align:center">
      <div style="width:64px;height:64px;background:#e8f4fd;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:24px;color:#1a73e8">
        <i class="fas fa-magic"></i>
      </div>
      <h3 class="fw-700 fs-16 mb-8">Tạo CV Online</h3>
      <p class="text-muted fs-13 mb-16">Điền thông tin, hệ thống tự động tạo CV đẹp, xuất PDF ngay</p>
      <div class="flex-col gap-8">
        <a href="{{ url('/user/cv/create') }}" class="btn btn-primary" style="background:#1a73e8;justify-content:center">
          <i class="fas fa-plus"></i> Tạo CV mới
        </a>
        @if(auth()->user()->resume)
          <a href="{{ url('/user/cv/view') }}" class="btn btn-outline" style="border-color:#1a73e8;color:#1a73e8;justify-content:center" target="_blank">
            <i class="fas fa-eye"></i> Xem CV đã tạo
          </a>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- Tips --}}
<div class="card" style="background:linear-gradient(135deg,var(--primary-light),#e8f4fd)">
  <div class="card-body" style="padding:20px">
    <div class="fw-700 fs-14 mb-12"><i class="fas fa-lightbulb" style="color:var(--primary)"></i> Mẹo tạo CV ấn tượng</div>
    <div class="grid-2 gap-12">
      @foreach(['Dùng font chuyên nghiệp, cỡ chữ 11-12pt', 'Giới hạn CV trong 1-2 trang', 'Highlight kỹ năng nổi bật và thành tích cụ thể', 'Tailored CV cho từng vị trí ứng tuyển'] as $tip)
        <div class="flex gap-8" style="align-items:flex-start;font-size:13px">
          <i class="fas fa-check-circle" style="color:var(--primary);margin-top:1px;flex-shrink:0"></i>
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
    document.getElementById('cv-selected').innerHTML = '<i class="fas fa-file-check"></i> ' + input.files[0].name;
    document.getElementById('cv-drop-zone').style.borderColor = 'var(--primary)';
  }
}
function handleCvDrop(e) {
  e.preventDefault();
  var files = e.dataTransfer.files;
  if (files.length) { document.getElementById('cv-file').files = files; showFileName(document.getElementById('cv-file')); }
}
</script>
@endpush
@endsection
