@extends('layouts.dashboard')
@section('title', 'Xem trước CV Online')

@section('content')
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-20 fw-800" style="color:var(--secondary)">Xem trước CV Online</h1>
    <p class="text-muted fs-13 mt-4">Kiểm tra thông tin và bố cục của CV trước khi xuất bản hoặc tải xuống bản PDF.</p>
  </div>
  
  <div class="flex gap-10">
    <a href="{{ route('user.cv') }}" class="btn btn-outline btn-sm">
      <i class="fas fa-arrow-left"></i> Danh sách CV
    </a>
    <a href="{{ route('user.cv.create') }}" class="btn btn-outline btn-sm" style="border-color: #1a73e8; color: #1a73e8;">
      <i class="fas fa-edit"></i> Chỉnh sửa
    </a>
    <a href="{{ route('user.cv.download') }}" class="btn btn-primary btn-sm" style="background: #10b981; border-color: #10b981;">
      <i class="fas fa-file-pdf"></i> Tải PDF tiếng Việt
    </a>
  </div>
</div>

{{-- Panel điều khiển trên di động --}}
<div class="card mb-20" style="background: #fafafa; border: 1px solid var(--border); border-radius: var(--radius-lg);">
  <div class="card-body" style="padding: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
    <div style="font-size: 13px;">
      <i class="fas fa-palette text-primary"></i> Đang hiển thị mẫu: 
      <span class="badge" style="background: var(--primary); color: white; padding: 4px 8px; border-radius: 4px; font-weight: 600;">
        {{ ucfirst($template) }}
      </span>
    </div>
    
    <div>
      <form action="{{ route('user.cv.delete') }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bản CV online hiện tại không? Hành động này không thể hoàn tác.')" style="display: inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-outline" style="border-color: var(--danger); color: var(--danger); font-size: 12px; padding: 6px 12px;">
          <i class="fas fa-trash-alt"></i> Xóa bản CV này
        </button>
      </form>
    </div>
  </div>
</div>

{{-- Container Preview - Mô phỏng trang A4 --}}
<div style="display: flex; justify-content: center; background: #e2e8f0; padding: 40px 20px; border-radius: var(--radius-lg); overflow-x: auto; box-shadow: inset 0 2px 8px rgba(0,0,0,0.06);">
  <div class="cv-preview-container" style="
    background: #ffffff;
    width: 210mm;
    min-height: 297mm;
    padding: 20mm;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    box-sizing: border-box;
    border-radius: 4px;
    position: relative;
    font-family: 'DejaVu Sans', sans-serif;
  ">
    
    {{-- Nhúng giao diện Template thực tế được chọn --}}
    @include($templateView, [
      'cvData'      => $cvData,
      'photoUrl'    => $photoUrl,
      'photoBase64' => $photoUrl, {{-- fallback preview --}}
      'isPdf'       => false
    ])
    
  </div>
</div>

<style>
/* Đảm bảo tỉ lệ hiển thị chuẩn A4 responsive trên màn hình */
@media (max-width: 900px) {
  .cv-preview-container {
    width: 100% !important;
    min-height: auto !important;
    padding: 10mm !important;
  }
}
</style>
@endsection
