@extends('layouts.admin')
@section('title', 'Phân quyền dữ liệu & Quản lý sở hữu')

@section('content')
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-18 fw-800" style="color:var(--secondary)">Phân quyền dữ liệu (Data Authorization)</h1>
    <p class="text-muted fs-13 mt-2">Giám sát và điều phối quyền sở hữu tin tuyển dụng giữa các Doanh nghiệp.</p>
  </div>
  
  {{-- Tìm kiếm tin tuyển dụng --}}
  <form action="{{ url('/admin/permissions') }}" method="GET">
    <div class="flex-responsive">
      <input type="text" name="search" class="form-control" style="width:250px; font-size:13px;" placeholder="Tìm kiếm theo tiêu đề tin..." value="{{ request('search') }}">
      <button type="submit" class="btn btn-primary btn-sm" style="padding:0 14px; height:38px;"><i class="fas fa-search"></i> Lọc</button>
    </div>
  </form>
</div>

{{-- Thẻ cảnh báo giải thích phân quyền dữ liệu --}}
<div class="card mb-20" style="background: linear-gradient(135deg, #eff6ff, #f8fafc); border-left: 4px solid #1a73e8; border-radius: var(--radius-lg);">
  <div class="card-body" style="padding: 16px; font-size: 13px; line-height: 1.6; color: #1e293b;">
    <div class="fw-700 mb-6 text-primary-color"><i class="fas fa-shield-alt mr-6"></i> Cơ chế Bảo mật Phân quyền Dữ liệu (Row-Level Ownership Security)</div>
    <ul style="padding-left: 18px; margin: 0; display:flex; flex-direction:column; gap:6px;">
      <li><strong>Quyền của Doanh nghiệp:</strong> Mỗi doanh nghiệp (Employer) chỉ được phép xem, sửa, đóng/mở tin tuyển dụng và xem danh sách ứng viên nộp hồ sơ của **chính họ**. Việc truy cập trái phép dữ liệu của doanh nghiệp khác sẽ bị hệ thống chặn đứng với mã lỗi <strong>403 Forbidden</strong>.</li>
      <li><strong>Quyền của Quản trị viên:</strong> Admin có toàn quyền giám sát. Dưới đây, Admin có thể thực hiện <strong>Chuyển giao quyền sở hữu dữ liệu (Transfer Ownership)</strong> để di chuyển một tin tuyển dụng (kèm theo toàn bộ hồ sơ ứng tuyển của tin đó) từ doanh nghiệp này sang doanh nghiệp khác.</li>
    </ul>
  </div>
</div>

<div class="card shadow-sm" style="border-radius: var(--radius-lg); overflow: hidden;">
  <div class="table-responsive">
    <table class="table" style="vertical-align: middle;">
    <thead>
      <tr style="background: #f8fafc; border-bottom: 1px solid var(--border);">
        <th style="width: 50px;">ID</th>
        <th>Tin tuyển dụng</th>
        <th>Số ứng viên nộp</th>
        <th>Ngày đăng tin</th>
        <th style="width: 320px;">Doanh nghiệp sở hữu (Chuyển quyền sở hữu)</th>
      </tr>
    </thead>
    <tbody>
      @forelse($listings as $job)
        <tr>
          <td class="text-muted fs-12 fw-700">#{{ $job->id }}</td>
          <td>
            <div class="fw-700 fs-13" style="color: var(--secondary);">{{ $job->title }}</div>
            <div class="text-muted fs-12" style="margin-top: 2px;"><i class="fas fa-map-marker-alt"></i> {{ $job->address ?? 'Toàn quốc' }} | {{ $job->job_type }}</div>
          </td>
          <td>
            <span class="badge" style="background: var(--primary-light); color: var(--primary); padding: 4px 10px; border-radius: 12px; font-weight: 700;">
              {{ $job->users->count() }} lượt nộp
            </span>
          </td>
          <td class="text-muted fs-12">{{ $job->created_at->format('d/m/Y') }}</td>
          <td>
            {{-- Form chuyển giao quyền sở hữu dữ liệu --}}
            <form action="{{ url('/admin/permissions/transfer/'.$job->id) }}" method="POST" class="flex-responsive" style="margin: 0; align-items: center;">
              @csrf
              <select name="new_owner_id" class="form-control" style="font-size:12px; padding: 6px 10px; cursor: pointer; flex: 1;" required>
                @foreach($employers as $emp)
                  <option value="{{ $emp->id }}" {{ $job->user_id === $emp->id ? 'selected' : '' }}>
                    🏢 {{ $emp->company_name ?? $emp->name }} ({{ $emp->email }})
                  </option>
                @endforeach
              </select>
              <button type="submit" class="btn btn-sm btn-outline" 
                      style="border-color: var(--primary); color: var(--primary); font-size: 11px; padding: 6px 10px; white-space: nowrap;"
                      onclick="return confirm('Bạn có chắc chắn muốn chuyển giao quyền sở hữu tin tuyển dụng này? Nhà tuyển dụng cũ sẽ mất hoàn toàn quyền truy cập và Nhà tuyển dụng mới sẽ nhận toàn bộ quyền kiểm soát tin đăng cùng hồ sơ ứng viên.')">
                <i class="fas fa-exchange-alt"></i> Xác nhận
              </button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center text-muted" style="padding:32px">Không tìm thấy tin tuyển dụng nào phù hợp.</td></tr>
      @endforelse
    </tbody>
    </table>
  </div>

  {{-- Phân trang --}}
  @if($listings->hasPages())
    <div class="card-footer" style="background:#f8fafc; border-top:1px solid var(--border);">
      <div class="flex-between">
        <span class="text-muted fs-13">Đang xem {{ $listings->firstItem() }}–{{ $listings->lastItem() }} trong tổng số {{ $listings->total() }}</span>
        <div class="pagination">
          @if(!$listings->onFirstPage())<a href="{{ $listings->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>@endif
          @foreach($listings->getUrlRange(max(1,$listings->currentPage()-2), min($listings->lastPage(),$listings->currentPage()+2)) as $page => $url)
            @if ($page == $listings->currentPage())
              <span class="active" style="background:var(--primary); color:white;">{{ $page }}</span>
            @else
              <a href="{{ $url }}">{{ $page }}</a>
            @endif
          @endforeach
          @if($listings->hasMorePages())<a href="{{ $listings->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>@endif
        </div>
      </div>
    </div>
  @endif
</div>
@endsection
