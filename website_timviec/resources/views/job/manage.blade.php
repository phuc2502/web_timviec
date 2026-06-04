@extends('layouts.dashboard')
@section('title', 'Tin đăng của tôi')

@section('content')
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-20 fw-800" style="color:var(--secondary)">Tin tuyển dụng của tôi</h1>
    <p class="text-muted fs-13 mt-4">Quản lý tất cả tin đăng tuyển dụng</p>
  </div>
  <a href="{{ url('/job/create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Đăng tin mới</a>
</div>

@if($listings->isEmpty())
  <div class="card text-center" style="padding:56px 24px">
    <div style="font-size:52px;margin-bottom:16px">📋</div>
    <div class="fw-700 fs-16">Chưa có tin tuyển dụng nào</div>
    <p class="text-muted mt-8 fs-13">Bắt đầu đăng tin để tìm kiếm ứng viên tài năng</p>
    <a href="{{ url('/job/create') }}" class="btn btn-primary mt-16" style="display:inline-flex"><i class="fas fa-plus"></i> Đăng tin ngay</a>
  </div>
@else
  <div class="card">
    <table class="table">
      <thead>
        <tr>
          <th>Tin tuyển dụng</th>
          <th>Địa điểm</th>
          <th>Lương</th>
          <th>Ứng viên</th>
          <th>Hết hạn</th>
          <th>Trạng thái</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        @foreach($listings as $listing)
          <tr>
            <td>
              <a href="{{ url('/job/show/'.$listing->slug) }}" class="fw-600 fs-13" style="color:var(--secondary)" target="_blank">
                {{ Str::limit($listing->title, 40) }}
              </a>
            </td>
            <td><span class="tag tag-blue fs-12">{{ $listing->address }}</span></td>
            <td class="fw-600 text-primary-color fs-13">
              {{ $listing->salary == 0 ? 'Thỏa thuận' : number_format($listing->salary).'đ' }}
            </td>
            <td>
              <a href="{{ url('/applicants/'.$listing->slug) }}" class="flex gap-4" style="align-items:center;color:var(--text-secondary);font-size:13px">
                <i class="fas fa-users" style="color:var(--primary)"></i>
                <span class="fw-700" style="color:var(--primary)">{{ $listing->users->count() }}</span> người
              </a>
            </td>
            <td class="fs-13 text-muted">{{ \Carbon\Carbon::parse($listing->application_close_date)->format('d/m/Y') }}</td>
            <td>
              @if(\Carbon\Carbon::parse($listing->application_close_date)->isPast())
                <span class="status status-closed">Đã hết hạn</span>
              @else
                <span class="status status-open">Đang mở</span>
              @endif
            </td>
            <td>
              <div class="flex gap-6">
                <a href="{{ url('/job/'.$listing->id.'/edit') }}" class="btn btn-outline btn-sm" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                <a href="{{ url('/applicants/'.$listing->slug) }}" class="btn btn-primary btn-sm" title="Xem ứng viên"><i class="fas fa-users"></i></a>
                <a href="{{ url('/job/'.$listing->id.'/delete') }}"
                  onclick="return confirm('Xoá tin này?')"
                  class="btn btn-danger btn-sm" title="Xoá"><i class="fas fa-trash"></i></a>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif
@endsection
