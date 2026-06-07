@extends('layouts.admin')
@section('title', 'Quản lý cuộc hội thoại AI')

@section('content')
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-18 fw-800" style="color:var(--secondary)">Quản lý cuộc hội thoại AI</h1>
    <p class="text-muted fs-13 mt-2">Tổng: {{ $conversations->total() }} cuộc trò chuyện</p>
  </div>
  <form action="{{ route('admin.ai-chat.index') }}" method="GET">
    <div class="flex gap-8">
      <input type="text" name="search" class="form-control" style="width:280px" placeholder="Tìm tên, email, tiêu đề..." value="{{ request('search') }}">
      <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
    </div>
  </form>
</div>

<div class="card">
  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Người dùng</th>
        <th>Vai trò</th>
        <th>Tiêu đề cuộc hội thoại</th>
        <th style="text-align: center;">Số tin nhắn</th>
        <th>Cập nhật cuối</th>
        <th>Hành động</th>
      </tr>
    </thead>
    <tbody>
      @forelse($conversations as $c)
        <tr>
          <td class="text-muted fs-12">{{ $c->id }}</td>
          <td>
            <div class="flex gap-10" style="align-items: center;">
              <div class="avatar avatar-sm avatar-placeholder" style="background: var(--primary-light); color: var(--primary); font-size: 11px; font-weight: 700; width: 28px; height: 28px;">
                {{ strtoupper(substr($c->user->name ?? 'U', 0, 1)) }}
              </div>
              <div>
                <div class="fw-600 fs-13" style="color: var(--secondary);">{{ $c->user->name ?? 'Người dùng đã xóa' }}</div>
                <div class="text-muted fs-12">{{ $c->user->email ?? '' }}</div>
              </div>
            </div>
          </td>
          <td>
            @if(isset($c->user))
              @if($c->user->user_type === 'admin')
                <span class="tag tag-red fs-10" style="background:#fef2f2; color:#ef4444; border:1px solid #fee2e2; padding: 2px 6px; border-radius: 4px;">Admin</span>
              @elseif($c->user->user_type === 'employer')
                <span class="tag tag-orange fs-10" style="background:#fff7ed; color:#f97316; border:1px solid #ffedd5; padding: 2px 6px; border-radius: 4px;">Doanh nghiệp</span>
              @else
                <span class="tag tag-blue fs-10" style="background:#eff6ff; color:#3b82f6; border:1px solid #dbeafe; padding: 2px 6px; border-radius: 4px;">Ứng viên</span>
              @endif
            @else
              <span class="text-muted fs-12">-</span>
            @endif
          </td>
          <td>
            <a href="{{ route('admin.ai-chat.show', $c->id) }}" class="fw-600 fs-13" style="color: var(--primary);">
              {{ Str::limit($c->title ?? 'Không có tiêu đề', 45) }}
            </a>
          </td>
          <td style="text-align: center;">
            <span class="badge badge-primary fw-700" style="padding: 2px 8px; border-radius: 10px;">
              {{ count($c->messages ?? []) }}
            </span>
          </td>
          <td class="text-muted fs-12">
            {{ $c->updated_at->format('H:i d/m/Y') }}
          </td>
          <td>
            <div class="flex gap-8" style="align-items: center;">
              <a href="{{ route('admin.ai-chat.show', $c->id) }}" class="btn btn-light btn-sm" style="padding: 4px 8px; border-radius: var(--radius-sm);" title="Xem chi tiết">
                <i class="fas fa-eye text-primary"></i>
              </a>
              <form action="{{ route('admin.ai-chat.destroy', $c->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa cuộc hội thoại này?')" style="margin: 0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm" style="padding: 4px 8px; border-radius: var(--radius-sm);" title="Xóa">
                  <i class="fas fa-trash-alt"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="text-center text-muted" style="padding: 32px;">
            Không có cuộc trò chuyện AI nào được tìm thấy.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>

  @if($conversations->hasPages())
    <div class="card-footer">
      <div class="flex-between">
        <span class="text-muted fs-13">Hiển thị {{ $conversations->firstItem() }}–{{ $conversations->lastItem() }} / {{ $conversations->total() }}</span>
        <div class="pagination" style="display: flex; gap: 4px;">
          @if(!$conversations->onFirstPage())
            <a href="{{ $conversations->previousPageUrl() }}" style="padding: 4px 8px; border: 1px solid var(--border); border-radius: 4px; color: var(--text-body);"><i class="fas fa-chevron-left"></i></a>
          @endif
          @foreach($conversations->getUrlRange(1, $conversations->lastPage()) as $page => $url)
            @if($page == $conversations->currentPage())
              <span class="active" style="padding: 4px 10px; border: 1px solid var(--primary); background: var(--primary); color: #fff; border-radius: 4px; font-weight: 700;">{{ $page }}</span>
            @else
              <a href="{{ $url }}" style="padding: 4px 10px; border: 1px solid var(--border); border-radius: 4px; color: var(--text-body);">{{ $page }}</a>
            @endif
          @endforeach
          @if($conversations->hasMorePages())
            <a href="{{ $conversations->nextPageUrl() }}" style="padding: 4px 8px; border: 1px solid var(--border); border-radius: 4px; color: var(--text-body);"><i class="fas fa-chevron-right"></i></a>
          @endif
        </div>
      </div>
    </div>
  @endif
</div>

<style>
.pagination a {
  text-decoration: none;
  transition: var(--transition);
}
.pagination a:hover {
  border-color: var(--primary) !important;
  color: var(--primary) !important;
  background: var(--primary-light);
}
</style>
@endsection
