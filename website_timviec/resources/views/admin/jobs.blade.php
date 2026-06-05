@extends('layouts.admin')
@section('title', 'Quản lý tin tuyển dụng')

@section('content')
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-18 fw-800" style="color:var(--secondary)">Quản lý tin tuyển dụng</h1>
    <p class="text-muted fs-13 mt-2">Tổng: {{ $listings->total() }} tin đăng</p>
  </div>
  <form action="{{ url('/admin/jobs') }}" method="GET">
    <div class="flex gap-8">
      <input type="text" name="search" class="form-control" style="width:240px" placeholder="Tìm tiêu đề, công ty..." value="{{ request('search') }}">
      <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
    </div>
  </form>
</div>

<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th>#</th>
        <th>Tin tuyển dụng</th>
        <th>Nhà tuyển dụng</th>
        <th>Địa điểm</th>
        <th>Ứng viên</th>
        <th>Hết hạn</th>
        <th>Trạng thái</th>
        <th>Xoá</th>
      </tr>
    </thead>
    <tbody>
      @forelse($listings as $listing)
        <tr>
          <td class="text-muted fs-12">{{ $listing->id }}</td>
          <td>
            <a href="{{ url('/job/show/'.$listing->slug) }}" target="_blank" class="fw-600 fs-13" style="color:var(--secondary)">
              {{ Str::limit($listing->title, 35) }}
            </a>
            <div class="tag tag-gray fs-11 mt-4" style="display:inline-flex">{{ $listing->job_type }}</div>
          </td>
          <td>
            <div class="fs-13">{{ $listing->user->company_name ?? $listing->user->name }}</div>
            <div class="text-muted fs-12">{{ $listing->user->email }}</div>
          </td>
          <td><span class="tag tag-blue fs-11">{{ $listing->address }}</span></td>
          <td>
            <span class="fw-700 text-primary-color">{{ $listing->users->count() }}</span>
            <span class="text-muted fs-12"> UV</span>
          </td>
          <td class="text-muted fs-12">{{ \Carbon\Carbon::parse($listing->application_close_date)->format('d/m/Y') }}</td>
          <td>
            @if(\Carbon\Carbon::parse($listing->application_close_date)->isPast())
              <span class="status status-closed" style="font-size:12px">Hết hạn</span>
            @else
              <span class="status status-open" style="font-size:12px">Đang mở</span>
            @endif
          </td>
          <td>
            <form action="{{ url('/admin/jobs/'.$listing->id) }}" method="POST">
              @csrf @method('DELETE')
              <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Xoá tin này? Không thể hoàn tác!')">
                <i class="fas fa-trash"></i>
              </button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="8" class="text-center text-muted" style="padding:32px">Không có tin tuyển dụng nào</td></tr>
      @endforelse
    </tbody>
  </table>

  @if($listings->hasPages())
    <div class="card-footer">
      <div class="flex-between">
        <span class="text-muted fs-13">Hiển thị {{ $listings->firstItem() }}–{{ $listings->lastItem() }} / {{ $listings->total() }}</span>
        <div class="pagination">
          @if(!$listings->onFirstPage())<a href="{{ $listings->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>@endif
          @foreach($listings->getUrlRange(1, $listings->lastPage()) as $page => $url)
            @if($page == $listings->currentPage())
              <span class="active">{{ $page }}</span>
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
