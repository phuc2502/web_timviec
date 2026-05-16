@extends('layouts.admin')
@section('title', 'Quản lý người dùng')

@section('content')
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-18 fw-800" style="color:var(--secondary)">Quản lý người dùng</h1>
    <p class="text-muted fs-13 mt-2">Tổng: {{ $users->total() }} người dùng</p>
  </div>
  {{-- Search --}}
  <form action="{{ url('/admin/users') }}" method="GET">
    <div style="display:flex;gap:8px">
      <input type="text" name="search" class="form-control" style="width:220px" placeholder="Tìm theo tên, email..." value="{{ request('search') }}">
      <select name="type" class="form-control" style="width:140px">
        <option value="">Tất cả</option>
        <option value="employee" {{ request('type') == 'employee' ? 'selected' : '' }}>Ứng viên</option>
        <option value="employer" {{ request('type') == 'employer' ? 'selected' : '' }}>Nhà tuyển dụng</option>
      </select>
      <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
    </div>
  </form>
</div>

<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th>#</th>
        <th>Người dùng</th>
        <th>Loại TK</th>
        <th>Trạng thái</th>
        <th>Gói</th>
        <th>Đăng ký</th>
        <th>Thao tác</th>
      </tr>
    </thead>
    <tbody>
      @forelse($users as $user)
        <tr>
          <td class="text-muted fs-12">{{ $user->id }}</td>
          <td>
            <div class="flex gap-10" style="align-items:center">
              <div class="avatar avatar-sm avatar-placeholder" style="background:var(--primary-light);color:var(--primary);font-size:12px">
                {{ strtoupper(substr($user->name,0,1)) }}
              </div>
              <div>
                <div class="fw-600 fs-13">{{ $user->name }}</div>
                <div class="text-muted fs-12">{{ $user->email }}</div>
              </div>
            </div>
          </td>
          <td>
            <span class="tag {{ $user->user_type === 'employer' ? 'tag-orange' : 'tag-blue' }} fs-11">
              {{ $user->user_type === 'employer' ? '🏢 NTD' : '👤 UV' }}
            </span>
          </td>
          <td>
            @if($user->email_verified_at)
              <span class="status status-open" style="font-size:12px">Đã xác minh</span>
            @else
              <span class="status status-pending" style="font-size:12px">Chờ xác minh</span>
            @endif
          </td>
          <td class="fs-12">
            @if($user->billing_ends && $user->billing_ends > now())
              <span class="tag tag-green fs-11"><i class="fas fa-crown"></i> {{ ucfirst($user->plan) }}</span>
            @elseif($user->user_trial && $user->user_trial > now())
              <span class="tag tag-gray fs-11">Dùng thử</span>
            @else
              <span class="text-muted">—</span>
            @endif
          </td>
          <td class="text-muted fs-12">{{ $user->created_at->format('d/m/Y') }}</td>
          <td>
            <div class="flex gap-6">
              <form action="{{ url('/admin/users/'.$user->id.'/ban') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm {{ $user->is_banned ? 'btn-outline' : 'btn-danger' }}"
                  onclick="return confirm('{{ $user->is_banned ? 'Mở khoá' : 'Khoá' }} tài khoản này?')"
                  title="{{ $user->is_banned ? 'Mở khoá' : 'Khoá' }}">
                  <i class="fas {{ $user->is_banned ? 'fa-lock-open' : 'fa-ban' }}"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="text-center text-muted" style="padding:32px">Không tìm thấy người dùng nào</td></tr>
      @endforelse
    </tbody>
  </table>

  @if($users->hasPages())
    <div class="card-footer">
      <div class="flex-between">
        <span class="text-muted fs-13">Hiển thị {{ $users->firstItem() }}–{{ $users->lastItem() }} / {{ $users->total() }}</span>
        <div class="pagination">
          @if(!$users->onFirstPage())<a href="{{ $users->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>@endif
          @foreach($users->getUrlRange(max(1,$users->currentPage()-2), min($users->lastPage(),$users->currentPage()+2)) as $page => $url)
            <{{ $page == $users->currentPage() ? 'span class="active"' : 'a href="'.$url.'"' }}>{{ $page }}</{{ $page == $users->currentPage() ? 'span' : 'a' }}>
          @endforeach
          @if($users->hasMorePages())<a href="{{ $users->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>@endif
        </div>
      </div>
    </div>
  @endif
</div>
@endsection
