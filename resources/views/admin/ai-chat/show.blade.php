@extends('layouts.admin')
@section('title', 'Chi tiết cuộc trò chuyện AI - Auditor Mode')

@section('content')
<div class="mb-20">
  <a href="{{ route('admin.ai-chat.index') }}" class="btn btn-light btn-sm" style="margin-bottom: 12px; border-radius: var(--radius-sm);">
    <i class="fas fa-arrow-left"></i> Quay lại danh sách
  </a>
  <div class="flex-between">
    <div>
      <h1 class="fs-18 fw-800" style="color:var(--secondary)">Chi tiết cuộc trò chuyện AI</h1>
      <p class="text-muted fs-13 mt-2">ID cuộc trò chuyện: #{{ $conversation->id }}</p>
    </div>
    <form action="{{ route('admin.ai-chat.destroy', $conversation->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa cuộc trò chuyện này? Hành động này không thể phục hồi.')" style="margin: 0;">
      @csrf
      @method('DELETE')
      <button type="submit" class="btn btn-danger btn-sm" style="display: flex; align-items: center; gap: 6px; border-radius: var(--radius-sm);">
        <i class="fas fa-trash-alt"></i> Xóa dữ liệu trò chuyện
      </button>
    </form>
  </div>
</div>

{{-- AUDITOR DISCLAIMER --}}
<div class="alert alert-warning" style="border-radius: var(--radius-md); font-size: 13px; line-height: 1.6; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 12px;">
  <i class="fas fa-exclamation-triangle" style="font-size: 18px; margin-top: 2px;"></i>
  <div>
    <strong>Chế độ Giám sát (Auditor Mode):</strong> Nội dung trò chuyện dưới đây chỉ dành cho mục đích kiểm soát chi phí API, phát hiện lỗi hệ thống và ngăn chặn lạm dụng. Quản trị viên phải tuân thủ nghiêm ngặt chính sách bảo vệ dữ liệu cá nhân, không chia sẻ hoặc sử dụng thông tin này ngoài mục đích vận hành.
  </div>
</div>

<div class="grid" style="grid-template-columns: 1fr 2fr; gap: 20px; align-items: start; margin-bottom: 30px;">
  
  {{-- THÔNG TIN CHI TIẾT --}}
  <div class="card" style="padding: 20px; display: flex; flex-direction: column; gap: 16px;">
    <h3 class="fw-700 fs-14 text-secondary" style="border-bottom: 1px solid var(--border); padding-bottom: 8px; margin: 0;">
      <i class="fas fa-info-circle mr-6 text-primary"></i> Thông tin cuộc trò chuyện
    </h3>
    
    <div>
      <div style="font-size: 11px; color: var(--text-secondary); text-transform: uppercase; font-weight: 700;">Người tham gia</div>
      <div class="fw-600 fs-13 mt-4" style="color: var(--secondary);">{{ $conversation->user->name ?? 'Người dùng đã xóa' }}</div>
      <div class="text-muted fs-12 mt-2">{{ $conversation->user->email ?? '' }}</div>
      <div class="mt-4">
        @if(isset($conversation->user))
          @if($conversation->user->user_type === 'admin')
            <span class="tag tag-red fs-10" style="background:#fef2f2; color:#ef4444; border:1px solid #fee2e2; padding: 2px 6px; border-radius: 4px;">Admin</span>
          @elseif($conversation->user->user_type === 'employer')
            <span class="tag tag-orange fs-10" style="background:#fff7ed; color:#f97316; border:1px solid #ffedd5; padding: 2px 6px; border-radius: 4px;">Doanh nghiệp</span>
          @else
            <span class="tag tag-blue fs-10" style="background:#eff6ff; color:#3b82f6; border:1px solid #dbeafe; padding: 2px 6px; border-radius: 4px;">Ứng viên</span>
          @endif
        @endif
      </div>
    </div>

    <div>
      <div style="font-size: 11px; color: var(--text-secondary); text-transform: uppercase; font-weight: 700;">Tiêu đề cuộc trò chuyện</div>
      <div class="fw-600 fs-13 mt-4" style="color: var(--secondary); line-height: 1.4;">{{ $conversation->title ?? 'Không có tiêu đề' }}</div>
    </div>

    <div class="flex-between">
      <div>
        <div style="font-size: 11px; color: var(--text-secondary); text-transform: uppercase; font-weight: 700;">Số tin nhắn</div>
        <div class="fw-700 fs-14 mt-4" style="color: var(--primary);">{{ count($conversation->messages ?? []) }} tin nhắn</div>
      </div>
      <div>
        <div style="font-size: 11px; color: var(--text-secondary); text-transform: uppercase; font-weight: 700;">Chi phí ước tính</div>
        <div class="fw-700 fs-14 mt-4" style="color: var(--warning);">
          {{ count($conversation->messages ?? []) * 250 }} Tokens
        </div>
      </div>
    </div>

    <div>
      <div style="font-size: 11px; color: var(--text-secondary); text-transform: uppercase; font-weight: 700;">Thời gian tạo</div>
      <div class="text-muted fs-12 mt-4">{{ $conversation->created_at->format('H:i d/m/Y') }}</div>
    </div>

    <div>
      <div style="font-size: 11px; color: var(--text-secondary); text-transform: uppercase; font-weight: 700;">Thời gian cập nhật cuối</div>
      <div class="text-muted fs-12 mt-4">{{ $conversation->updated_at->format('H:i d/m/Y') }}</div>
    </div>
  </div>

  {{-- NỘI DUNG LỊCH SỬ CHAT --}}
  <div class="card" style="display: flex; flex-direction: column; height: 500px;">
    <div style="padding: 14px 20px; border-bottom: 1px solid var(--border); background: #fafafa; font-weight: 700; color: var(--secondary); font-size: 13px;">
      <i class="fas fa-history mr-6 text-primary"></i> Lịch sử hội thoại (Chỉ đọc)
    </div>
    
    <div style="flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 16px; background: #f8fafc;">
      @php
        $messagesList = $conversation->messages ?? [];
      @endphp
      
      @forelse($messagesList as $msg)
        @if(($msg['role'] ?? '') === 'user')
          {{-- USER BUBBLE --}}
          <div style="display: flex; flex-direction: column; align-items: flex-end; align-self: flex-end; max-width: 80%;">
            <div style="font-size: 10px; color: var(--text-secondary); margin-bottom: 4px; margin-right: 4px; font-weight: 600;">
              {{ $conversation->user->name ?? 'User' }}
            </div>
            <div style="background: var(--primary); color: #fff; padding: 10px 14px; border-radius: 12px 12px 2px 12px; font-size: 13px; line-height: 1.5; box-shadow: var(--shadow-sm); white-space: pre-wrap; word-break: break-word;">
              {{ $msg['content'] }}
            </div>
            <span style="font-size: 9px; color: var(--text-secondary); margin-top: 4px; margin-right: 4px;">
              {{ isset($msg['created_at']) ? \Carbon\Carbon::parse($msg['created_at'])->format('H:i, d/m/Y') : '' }}
            </span>
          </div>
        @else
          {{-- AI BUBBLE --}}
          <div style="display: flex; flex-direction: column; align-items: flex-start; align-self: flex-start; max-width: 80%;">
            <div style="font-size: 10px; color: var(--text-secondary); margin-bottom: 4px; margin-left: 4px; font-weight: 600; display: flex; align-items: center; gap: 4px;">
              <i class="fas fa-robot text-primary"></i> Trợ lý AI (Gemini)
            </div>
            <div class="ai-markdown-content" style="background: #fff; color: var(--text-dark); padding: 10px 14px; border-radius: 2px 12px 12px 12px; font-size: 13px; line-height: 1.5; box-shadow: var(--shadow-sm); border: 1px solid var(--border); word-break: break-word;">
              {!! \Illuminate\Support\Str::markdown(e($msg['content'])) !!}
            </div>
            <span style="font-size: 9px; color: var(--text-secondary); margin-top: 4px; margin-left: 4px;">
              {{ isset($msg['created_at']) ? \Carbon\Carbon::parse($msg['created_at'])->format('H:i, d/m/Y') : '' }}
            </span>
          </div>
        @endif
      @empty
        <div style="margin: auto; text-align: center; color: var(--text-secondary); font-size: 13px; padding: 40px;">
          <i class="fas fa-comments fa-2x mb-8" style="opacity: 0.2;"></i>
          <p>Không có tin nhắn nào trong cuộc hội thoại này.</p>
        </div>
      @endforelse
    </div>
  </div>

</div>

<style>
/* CSS cho phần Markdown nội dung từ AI */
.ai-markdown-content p {
  margin-bottom: 8px;
}
.ai-markdown-content p:last-child {
  margin-bottom: 0;
}
.ai-markdown-content ul, .ai-markdown-content ol {
  margin-bottom: 8px;
  padding-left: 20px;
}
.ai-markdown-content ul {
  list-style-type: disc;
}
.ai-markdown-content ol {
  list-style-type: decimal;
}
.ai-markdown-content li {
  margin-bottom: 4px;
}
.ai-markdown-content strong {
  font-weight: 700;
  color: var(--text-dark);
}
.ai-markdown-content code {
  background: rgba(0,0,0,0.06);
  padding: 2px 4px;
  border-radius: 4px;
  font-family: monospace;
  font-size: 11px;
}
.ai-markdown-content pre {
  background: #1e293b;
  color: #f8fafc;
  padding: 10px;
  border-radius: 6px;
  overflow-x: auto;
  margin-bottom: 8px;
}
.ai-markdown-content pre code {
  background: transparent;
  color: inherit;
  padding: 0;
  font-size: 11px;
}
</style>
@endsection
