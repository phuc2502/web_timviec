@extends('layouts.app')
@section('title', 'Tin nhắn')

@section('content')
<div class="chat-layout">

  {{-- CONVERSATION LIST --}}
  <div class="chat-sidebar">
    <div style="padding:16px 16px 12px;border-bottom:1px solid var(--border)">
      <div class="fw-800 fs-16" style="color:var(--secondary)">Tin nhắn</div>
    </div>
    <div style="padding:10px 14px;border-bottom:1px solid var(--border)">
      <input type="text" class="form-control" style="font-size:13px" placeholder="🔍 Tìm cuộc trò chuyện...">
    </div>
    <div style="flex:1;overflow-y:auto">
      @foreach($conversations ?? [] as $conv)
        @php $other = auth()->user()->user_type === 'employer' ? $conv->employee : $conv->employer; @endphp
        <a href="{{ url('/messages/'.$conv->id) }}" class="conversation-item {{ $conversation->id == $conv->id ? 'active' : '' }}" style="text-decoration:none">
          <div class="avatar avatar-md avatar-placeholder" style="background:var(--primary-light);color:var(--primary);font-size:16px;font-weight:700;flex-shrink:0">
            {{ strtoupper(substr($other->name ?? 'U', 0, 1)) }}
          </div>
          <div style="flex:1;min-width:0">
            <div class="flex-between">
              <span class="conversation-item__name">{{ $other->name ?? 'Người dùng' }}</span>
              @if($conv->messages->last())
                <span class="text-muted" style="font-size:11px">{{ $conv->messages->last()->created_at->diffForHumans(null,true) }}</span>
              @endif
            </div>
            <div class="conversation-item__preview mt-2">
              {{ $conv->messages->last() ? Str::limit($conv->messages->last()->body, 35) : 'Chưa có tin nhắn' }}
            </div>
          </div>
        </a>
      @endforeach
    </div>
  </div>

  {{-- CHAT WINDOW --}}
  <div class="chat-main">
    {{-- Header --}}
    @php $other = auth()->user()->user_type === 'employer' ? $conversation->employee : $conversation->employer; @endphp
    <div class="chat-header">
      <div class="avatar avatar-md avatar-placeholder" style="background:var(--primary-light);color:var(--primary);font-size:16px;font-weight:700">
        {{ strtoupper(substr($other->name ?? 'U', 0, 1)) }}
      </div>
      <div>
        <div class="fw-700 fs-14">{{ $other->name }}</div>
        <div class="text-muted fs-12">
          @if($conversation->listing)
            <i class="fas fa-briefcase"></i> {{ $conversation->listing->title }}
          @else
            {{ $other->user_type === 'employer' ? 'Nhà tuyển dụng' : 'Ứng viên' }}
          @endif
        </div>
      </div>
      <div class="flex gap-8" style="margin-left:auto; align-items:center">
        @if($conversation->listing)
          <a href="{{ url('/job/show/'.$conversation->listing->slug) }}" class="btn btn-outline btn-sm" target="_blank">
            <i class="fas fa-external-link-alt"></i> Xem tin
          </a>
        @endif
        <form action="{{ route('messages.destroy', $conversation->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đoạn chat này không? Toàn bộ lịch sử trò chuyện của bạn sẽ bị ẩn.')" style="margin:0">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-outline-danger btn-sm">
            <i class="fas fa-trash-alt"></i> Xóa đoạn chat
          </button>
        </form>
      </div>
    </div>

    {{-- Messages --}}
    <div class="chat-messages" id="messages-container">
      @forelse($messages as $msg)
        <div class="msg-bubble {{ $msg->sender_id === auth()->id() ? 'sent' : 'received' }}">
          <div class="msg-bubble__text">{{ $msg->body }}</div>
          <div class="msg-bubble__time">{{ $msg->created_at->format('H:i · d/m/Y') }}</div>
        </div>
      @empty
        <div class="flex-center" style="height:100%;flex-direction:column;gap:8px;color:var(--text-muted)">
          <i class="fas fa-comment fa-2x"></i>
          <span class="fs-14">Bắt đầu cuộc trò chuyện!</span>
        </div>
      @endforelse
    </div>

    {{-- Input --}}
    <div class="chat-input">
      <form action="{{ url('/messages/'.$conversation->id.'/send') }}" method="POST" style="display:flex;gap:10px;width:100%;align-items:flex-end" id="msg-form">
        @csrf
        <textarea name="body" id="msg-input" class="form-control" placeholder="Nhập tin nhắn... (Enter để gửi)" rows="1"
          style="flex:1;border:1.5px solid var(--border);border-radius:var(--radius-lg);padding:10px 14px;font-family:inherit;font-size:14px;resize:none;max-height:120px"
          onkeydown="handleEnter(event)"></textarea>
        <button type="submit" class="btn btn-primary" style="flex-shrink:0;height:44px;width:44px;padding:0;justify-content:center;border-radius:50%">
          <i class="fas fa-paper-plane"></i>
        </button>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
// Scroll to bottom
var container = document.getElementById('messages-container');
if (container) container.scrollTop = container.scrollHeight;

// Enter to send
function handleEnter(e) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    document.getElementById('msg-form').submit();
  }
}

// Auto resize textarea
var textarea = document.getElementById('msg-input');
if (textarea) {
  textarea.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
  });
}
// Tự động refresh CSRF token mỗi 10 phút để tránh Page Expired
setInterval(function() {
    fetch('/sanctum/csrf-cookie').catch(() => {});
}, 600000);

// Khi form submit bị lỗi 419, tự reload trang
document.querySelector('form[action*="send"]')?.addEventListener('submit', function(e) {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) {
        document.querySelectorAll('input[name="_token"]').forEach(el => el.value = token);
    }
});
// Poll new messages
let lastId = {{ $messages->last()?->id ?? 0 }};
if (container) {
  setInterval(function () {
    fetch(`/messages/{{ $conversation->id }}/poll?after=${lastId}`)
      .then(r => r.json())
      .then(data => {
        if (!data.messages || data.messages.length === 0) return;
        data.messages.forEach(m => {
          const isSent = m.sender_id === {{ auth()->id() }};
          const div = document.createElement('div');
          div.className = 'msg-bubble ' + (isSent ? 'sent' : 'received');
          div.innerHTML = `<div class="msg-bubble__text"></div>
                           <div class="msg-bubble__time"></div>`;
          div.querySelector('.msg-bubble__text').textContent = m.body;
          div.querySelector('.msg-bubble__time').textContent = m.created_at;
          container.appendChild(div);
        });
        lastId = data.messages[data.messages.length - 1].id;
        container.scrollTop = container.scrollHeight;
      });
  }, 5000);
}
</script>
@endpush
@endsection
