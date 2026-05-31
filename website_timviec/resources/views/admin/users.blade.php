@extends('layouts.admin')
@section('title', 'Quản lý người dùng & Phân quyền chức năng')

@section('content')
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-18 fw-800" style="color:var(--secondary)">Quản lý người dùng</h1>
    <p class="text-muted fs-13 mt-2">Tổng số: <strong>{{ $users->total() }}</strong> thành viên hệ thống</p>
  </div>
  
  {{-- Bộ lọc tìm kiếm --}}
  <form action="{{ url('/admin/users') }}" method="GET">
    <div style="display:flex; gap:8px;">
      <input type="text" name="search" class="form-control" style="width:220px; font-size:13px;" placeholder="Tìm theo tên, email..." value="{{ request('search') }}">
      <select name="type" class="form-control" style="width:140px; font-size:13px; cursor:pointer;">
        <option value="">Tất cả vai trò</option>
        <option value="employee" {{ request('type') == 'employee' ? 'selected' : '' }}>Ứng viên</option>
        <option value="employer" {{ request('type') == 'employer' ? 'selected' : '' }}>Nhà tuyển dụng</option>
        <option value="admin" {{ request('type') == 'admin' ? 'selected' : '' }}>Admin</option>
      </select>
      <button type="submit" class="btn btn-primary btn-sm" style="padding:0 14px;"><i class="fas fa-search"></i> Tìm</button>
    </div>
  </form>
</div>

<div class="card shadow-sm" style="border-radius: var(--radius-lg); overflow: hidden;">
  <table class="table" style="vertical-align: middle;">
    <thead>
      <tr style="background: #f8fafc; border-bottom: 1px solid var(--border);">
        <th style="width: 50px;">ID</th>
        <th>Họ tên & Email</th>
        <th style="width: 170px;">Phân quyền vai trò</th>
        <th>Trạng thái xác minh</th>
        <th style="width: 170px;">Gói Premium</th>
        <th>Ngày tham gia</th>
        <th style="width: 100px; text-align: center;">Khóa/Mở</th>
      </tr>
    </thead>
    <tbody>
      @forelse($users as $user)
        <tr>
          <td class="text-muted fs-12 fw-700">#{{ $user->id }}</td>
          <td>
            <div class="flex gap-10" style="align-items:center">
              <div class="avatar avatar-sm avatar-placeholder" style="background:var(--primary-light); color:var(--primary); font-size:12px; font-weight:700; flex-shrink:0;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
              </div>
              <div style="min-width: 0; flex: 1;">
                <div class="fw-700 fs-13" style="color:var(--secondary); text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">{{ $user->name }}</div>
                <div class="text-muted fs-12" style="text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">{{ $user->email }}</div>
              </div>
            </div>
          </td>
          <td>
            {{-- Form Thay đổi vai trò (Phân quyền chức năng) --}}
            <form action="{{ url('/admin/users/'.$user->id.'/role') }}" method="POST" class="flex gap-4" style="align-items: center; margin: 0;">
              @csrf
              <select name="user_type" class="form-control" style="font-size:11px; padding: 4px 8px; height: auto; cursor:pointer;" onchange="this.form.submit()">
                <option value="employee" {{ $user->user_type === 'employee' ? 'selected' : '' }}>👤 Ứng viên</option>
                <option value="employer" {{ $user->user_type === 'employer' ? 'selected' : '' }}>🏢 Doanh nghiệp</option>
                <option value="admin" {{ $user->user_type === 'admin' ? 'selected' : '' }}>🔑 Admin</option>
              </select>
            </form>
          </td>
          <td>
            @if($user->email_verified_at)
              <span class="status status-open" style="font-size:11px; font-weight:600; background:#ecfdf5; color:#10b981; border:1px solid #d1fae5; padding: 2px 8px; border-radius: 4px;">Đã xác minh</span>
            @else
              <span class="status status-pending" style="font-size:11px; font-weight:600; background:#fffbeb; color:#d97706; border:1px solid #fef3c7; padding: 2px 8px; border-radius: 4px;">Chờ xác minh</span>
            @endif
          </td>
          <td>
            {{-- Form thay đổi gói Premium (Phân quyền chức năng) --}}
            <form action="{{ url('/admin/users/'.$user->id.'/plan') }}" method="POST" class="flex gap-4" style="align-items: center; margin: 0;">
              @csrf
              <select name="plan" class="form-control" style="font-size:11px; padding: 4px 8px; height: auto; cursor:pointer;" onchange="this.form.submit()">
                <option value="free" {{ $user->plan === 'free' || !$user->plan ? 'selected' : '' }}>Miễn phí</option>
                <option value="trial" {{ $user->plan === 'trial' ? 'selected' : '' }}>Dùng thử</option>
                <option value="premium" {{ $user->plan === 'premium' ? 'selected' : '' }}>👑 Premium</option>
              </select>
            </form>
          </td>
          <td class="text-muted fs-12">{{ $user->created_at->format('d/m/Y') }}</td>
          <td style="text-align: center;">
            {{-- Form Khóa / Mở khóa tài khoản --}}
            <form action="{{ url('/admin/users/'.$user->id.'/ban') }}" method="POST" style="margin: 0; display: inline-block;">
              @csrf
              <button type="submit" class="btn btn-sm {{ $user->is_banned ? 'btn-primary' : 'btn-danger' }}"
                style="padding: 4px 10px; font-size:11px; min-width: 80px; justify-content: center; background: {{ $user->is_banned ? '#10b981' : '#ef4444' }}; border-color: {{ $user->is_banned ? '#10b981' : '#ef4444' }};"
                onclick="return confirm('Bạn có chắc chắn muốn {{ $user->is_banned ? 'MỞ KHÓA' : 'KHÓA' }} tài khoản của thành viên [{{ $user->name }}] không?')"
                title="{{ $user->is_banned ? 'Mở khóa' : 'Khóa tài khoản' }}">
                <i class="fas {{ $user->is_banned ? 'fa-lock-open' : 'fa-ban' }} mr-4"></i> {{ $user->is_banned ? 'Mở khóa' : 'Khóa' }}
              </button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="text-center text-muted" style="padding:32px">Không tìm thấy thành viên nào thỏa mãn điều kiện tìm kiếm.</td></tr>
      @endforelse
    </tbody>
  </table>

  {{-- Phân trang chuẩn --}}
  @if($users->hasPages())
    <div class="card-footer" style="background:#f8fafc; border-top:1px solid var(--border);">
      <div class="flex-between">
        <span class="text-muted fs-13">Đang xem {{ $users->firstItem() }}–{{ $users->lastItem() }} trong tổng số {{ $users->total() }}</span>
        <div class="pagination">
          @if(!$users->onFirstPage())<a href="{{ $users->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>@endif
          @foreach($users->getUrlRange(max(1,$users->currentPage()-2), min($users->lastPage(),$users->currentPage()+2)) as $page => $url)
            @if ($page == $users->currentPage())
              <span class="active" style="background:var(--primary); color:white;">{{ $page }}</span>
            @else
              <a href="{{ $url }}">{{ $page }}</a>
            @endif
          @endforeach
          @if($users->hasMorePages())<a href="{{ $users->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>@endif
        </div>
      </div>
    </div>
  @endif
</div>
@endsection
