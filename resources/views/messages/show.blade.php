@extends('layouts.app')
@section('title', 'Tin nhắn')

@section('content')
<div class="chat-layout" style="position: relative;">

  {{-- CONVERSATION LIST --}}
  <div class="chat-sidebar">
    <div style="padding:16px 16px 12px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
      <div class="fw-800 fs-16" style="color:var(--secondary)">Tin nhắn</div>
      @if(isset($tab) && $tab === 'archive')
        <a href="{{ url('/messages') }}" class="btn btn-sm btn-outline-secondary" style="font-size:11px;padding:3px 6px;display:flex;align-items:center;gap:4px">
          <i class="fas fa-comment"></i> Quay lại
        </a>
      @else
        <a href="{{ url('/messages?tab=archive') }}" class="btn btn-sm btn-outline-danger" style="font-size:11px;padding:3px 6px;color:#dc3545;border-color:#dc3545;display:flex;align-items:center;gap:4px" title="Đoạn chat đã ẩn">
          <i class="fas fa-trash-alt"></i> Đã ẩn
        </a>
      @endif
    </div>
    <div style="padding:10px 14px;border-bottom:1px solid var(--border)">
      <input type="text" id="search-input" class="form-control" style="font-size:13px" placeholder="🔍 Tìm cuộc trò chuyện...">
    </div>
    <div style="flex:1;overflow-y:auto">
      @foreach($conversations ?? [] as $conv)
        @php 
          $cOther = auth()->user()->user_type === 'employer' ? $conv->employee : $conv->employer; 
          $cOnline = $cOther ? $cOther->isOnline() : false;
        @endphp
        <div class="conversation-wrapper" 
             data-search="{{ Str::lower(($cOther->name ?? '') . ' ' . ($conv->messages->last() ? $conv->messages->last()->body : '') . ' ' . ($conv->listing ? $conv->listing->title : '')) }}"
             style="position:relative; display:flex; align-items:center; width:100%">
          <a href="{{ url('/messages/'.$conv->id.(isset($tab) && $tab === 'archive' ? '?tab=archive' : '')) }}" class="conversation-item {{ $conversation->id == $conv->id ? 'active' : '' }}" style="text-decoration:none; flex:1">
            <div style="position:relative; flex-shrink:0;">
              <div class="avatar avatar-md avatar-placeholder" style="background:var(--primary-light);color:var(--primary);font-size:16px;font-weight:700">
                {{ strtoupper(substr($cOther->name ?? 'U', 0, 1)) }}
              </div>
              @if($cOnline)
                <div style="position:absolute;bottom:0;right:0;width:12px;height:12px;border-radius:50%;background:#28a745;border:2px solid white" title="Online"></div>
              @endif
            </div>
            <div style="flex:1;min-width:0;margin-left:10px;padding-right:24px">
              <div class="flex-between">
                <span class="conversation-item__name">{{ $cOther->name ?? 'Người dùng' }}</span>
                @if($conv->messages->last())
                  <span class="text-muted" style="font-size:11px">{{ $conv->messages->last()->created_at->diffForHumans(null,true) }}</span>
                @endif
              </div>
              <div class="conversation-item__preview mt-2">
                {{ $conv->messages->last() ? Str::limit($conv->messages->last()->body, 35) : 'Chưa có tin nhắn' }}
              </div>
            </div>
          </a>
          @if(isset($tab) && $tab === 'archive')
            <form action="{{ route('messages.restore', $conv->id) }}" method="POST" style="position:absolute; right:16px; top:50%; transform:translateY(-50%); z-index:10; margin:0">
              @csrf
              <button type="submit" class="btn btn-success" style="height:28px;width:28px;padding:0;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;border:none;background:#28a745;color:white" title="Khôi phục cuộc trò chuyện">
                <i class="fas fa-undo"></i>
              </button>
            </form>
          @endif
        </div>
      @endforeach
    </div>
  </div>

  {{-- CHAT WINDOW --}}
  <div class="chat-main" style="position: relative;">
    {{-- Header --}}
    @php 
      $other = auth()->user()->user_type === 'employer' ? $conversation->employee : $conversation->employer; 
      $isOnline = $other ? $other->isOnline() : false;
    @endphp
    <div class="chat-header">
      <div style="position:relative;">
        <div class="avatar avatar-md avatar-placeholder" style="background:var(--primary-light);color:var(--primary);font-size:16px;font-weight:700">
          {{ strtoupper(substr($other->name ?? 'U', 0, 1)) }}
        </div>
        @if($isOnline)
          <div style="position:absolute;bottom:0;right:0;width:12px;height:12px;border-radius:50%;background:#28a745;border:2px solid white"></div>
        @endif
      </div>
      <div style="margin-left: 10px;">
        <div style="display:flex;align-items:center;gap:6px">
          <div class="fw-700 fs-14">{{ $other->name }}</div>
          <span class="badge {{ $isOnline ? 'bg-success' : 'bg-secondary' }}" style="font-size:9px;padding:2px 6px;color:white;border-radius:10px">
            {{ $isOnline ? 'Online' : 'Offline' }}
          </span>
        </div>
        <div class="text-muted fs-12">
          @if($conversation->listing)
            <i class="fas fa-briefcase"></i> {{ $conversation->listing->title }}
          @else
            {{ $other->user_type === 'employer' ? 'Nhà tuyển dụng' : 'Ứng viên' }}
          @endif
          @if(!$isOnline && $other->last_seen_at)
            · <span style="font-size:11px">Hoạt động {{ $other->last_seen_at->diffForHumans() }}</span>
          @endif
        </div>
      </div>
      <div class="flex gap-8" style="margin-left:auto; align-items:center">
        @if($conversation->listing)
          <a href="{{ url('/job/show/'.$conversation->listing->slug) }}" class="btn btn-outline btn-sm" target="_blank">
            <i class="fas fa-external-link-alt"></i> Xem tin
          </a>
        @endif
        @if(isset($tab) && $tab === 'archive')
          <form action="{{ route('messages.restore', $conversation->id) }}" method="POST" style="margin:0">
            @csrf
            <button type="submit" class="btn btn-outline-success btn-sm" style="display:flex;align-items:center;gap:6px">
              <i class="fas fa-undo"></i> Khôi phục đoạn chat
            </button>
          </form>
        @else
          <form action="{{ route('messages.destroy', $conversation->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đoạn chat này không? Toàn bộ lịch sử trò chuyện của bạn sẽ bị ẩn.')" style="margin:0">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">
              <i class="fas fa-trash-alt"></i> Xóa đoạn chat
            </button>
          </form>
        @endif
      </div>
    </div>

    {{-- Messages --}}
    <div class="chat-messages" id="messages-container">
      @forelse($messages as $msg)
        <div class="msg-bubble {{ $msg->sender_id === auth()->id() ? 'sent' : 'received' }}">
          
          {{-- Đính kèm File Card --}}
          @if($msg->attachment_path)
            <div class="attachment-card" style="display:flex;align-items:center;gap:10px;background:rgba(0,0,0,0.03);border-radius:6px;padding:8px 12px;margin-bottom:6px;border:1px solid rgba(0,0,0,0.08)">
              <i class="fas fa-file-pdf fa-2x" style="color:#e05252"></i>
              <div style="text-align:left;min-width:0;flex:1">
                <div style="font-weight:bold;font-size:12px;word-break:break-all;color:var(--secondary)">{{ $msg->attachment_name }}</div>
                <div style="font-size:10px;color:var(--text-muted)">Tệp đính kèm</div>
              </div>
              <a href="{{ Storage::url($msg->attachment_path) }}" class="btn btn-sm btn-outline-secondary" target="_blank" style="padding:4px 8px;font-size:10px;flex-shrink:0" download>
                <i class="fas fa-download"></i> Tải về
              </a>
            </div>
          @endif

          {{-- Thẻ Lịch hẹn phỏng vấn --}}
          @if($msg->interviewInvitation)
            <div class="interview-card" style="background:white;color:#333;border-radius:8px;padding:12px;margin-bottom:6px;border:1px solid #dee2e6;box-shadow:0 2px 5px rgba(0,0,0,0.05);text-align:left;min-width:230px">
              <div style="font-weight:bold;color:#007bff;font-size:13px;margin-bottom:6px"><i class="fas fa-calendar-alt"></i> THƯ MỜI PHỎNG VẤN</div>
              <div style="font-size:12px;margin-bottom:4px"><strong>Lịch hẹn:</strong> {{ $msg->interviewInvitation->scheduled_at->format('H:i d/m/Y') }}</div>
              <div style="font-size:12px;margin-bottom:4px"><strong>Địa điểm:</strong> {{ $msg->interviewInvitation->location }}</div>
              @if($msg->interviewInvitation->notes)
                <div style="font-size:12px;margin-bottom:8px"><strong>Ghi chú:</strong> {{ $msg->interviewInvitation->notes }}</div>
              @endif
              
              <div class="interview-status-section-{{ $msg->interviewInvitation->id }}" style="margin-top:10px;border-top:1px solid #eee;padding-top:8px">
                @if($msg->interviewInvitation->status === 'pending')
                  @if(auth()->user()->isCandidate())
                    <div style="display:flex;gap:6px">
                      <button type="button" class="btn btn-success btn-sm" onclick="respondInterview({{ $msg->interviewInvitation->id }}, 'accepted')" style="flex:1;font-size:11px;padding:4px 6px"><i class="fas fa-check"></i> Đồng ý</button>
                      <button type="button" class="btn btn-danger btn-sm" onclick="respondInterview({{ $msg->interviewInvitation->id }}, 'declined')" style="flex:1;font-size:11px;padding:4px 6px"><i class="fas fa-times"></i> Từ chối</button>
                    </div>
                  @else
                    <span class="badge bg-warning text-dark" style="padding:4px 8px;font-size:10px;border-radius:4px"><i class="fas fa-clock"></i> Đang chờ phản hồi</span>
                  @endif
                @elseif($msg->interviewInvitation->status === 'accepted')
                  <span class="badge bg-success" style="padding:4px 8px;font-size:10px;color:white;border-radius:4px"><i class="fas fa-check-circle"></i> Đã đồng ý phỏng vấn</span>
                @elseif($msg->interviewInvitation->status === 'declined')
                  <span class="badge bg-danger" style="padding:4px 8px;font-size:10px;color:white;border-radius:4px"><i class="fas fa-times-circle"></i> Đã từ chối phỏng vấn</span>
                @endif
              </div>
            </div>
          @endif

          <div class="msg-bubble__text">{{ $msg->body }}</div>
          <div class="msg-bubble__time">
            {{ $msg->created_at->format('H:i · d/m/Y') }}
            @if($msg->sender_id === auth()->id())
              · <span style="font-style:italic" id="msg-status-{{ $msg->id }}">{{ $msg->read_at ? 'Đã xem' : 'Đã gửi' }}</span>
            @endif
          </div>
        </div>
      @empty
        <div class="flex-center" style="height:100%;flex-direction:column;gap:8px;color:var(--text-muted)">
          <i class="fas fa-comment fa-2x"></i>
          <span class="fs-14">Bắt đầu cuộc trò chuyện!</span>
        </div>
      @endforelse
    </div>

    {{-- Session Error Alert --}}
    @if(session('error') || $errors->any())
      <div style="padding: 10px 20px 0 20px;">
        <div class="chat-error-banner" style="margin: 0; display: flex; align-items: center; gap: 8px; border-radius: var(--radius-md); padding: 12px 16px; background: #FFF2EE; color: var(--danger); border: 1px solid var(--danger);">
          <i class="fas fa-exclamation-circle"></i>
          <span>{{ session('error') ?: $errors->first() }}</span>
        </div>
      </div>
    @endif

    {{-- File Upload Indicator --}}
    <div id="file-indicator" style="display:none;margin: 10px 20px 0 20px;padding:8px 12px;background:#e9ecef;border-radius:var(--radius-md);font-size:13px;align-items:center;justify-content:space-between">
      <span id="file-indicator-name" style="font-weight:600;color:var(--secondary)"><i class="fas fa-file-alt"></i> file_name.pdf</span>
      <button type="button" class="btn btn-sm text-danger" onclick="clearSelectedFile()" style="padding:0;background:transparent;border:none;cursor:pointer"><i class="fas fa-times"></i></button>
    </div>

    {{-- Quick Replies Panel --}}
    <div id="quick-replies-panel" style="display:none;position:absolute;bottom:75px;left:20px;width:320px;background:white;border:1.5px solid var(--border);border-radius:var(--radius-lg);box-shadow:0 4px 15px rgba(0,0,0,0.15);z-index:999;padding:12px;text-align:left">
      <div style="font-weight:bold;font-size:11px;color:var(--text-muted);margin-bottom:8px;border-bottom:1px solid #eee;padding-bottom:6px;display:flex;justify-content:space-between;align-items:center">
        <span>TIN NHẮN MẪU NHANH</span>
        <button type="button" onclick="toggleQuickReplies()" style="background:transparent;border:none;cursor:pointer;font-size:14px;color:var(--text-muted)">&times;</button>
      </div>
      <div id="quick-replies-list" style="max-height:180px;overflow-y:auto;display:flex;flex-direction:column;gap:6px">
        <div style="font-size:12px;color:var(--text-muted);text-align:center;padding:10px">Đang tải câu mẫu...</div>
      </div>
    </div>

    <div class="chat-input" style="position: relative;">
      @if(isset($tab) && $tab === 'archive')
        <div style="width:100%; text-align:center; padding:16px 20px; background:#fff3cd; border-top:1px solid #ffeeba; color:#856404; font-size:13.5px; border-radius:0 0 var(--radius-lg) var(--radius-lg); display:flex; align-items:center; justify-content:center; gap:12px; box-shadow:inset 0 1px 0 rgba(0,0,0,0.05)">
          <span><i class="fas fa-archive"></i> Cuộc trò chuyện này đang nằm trong thư mục ẩn.</span>
          <form action="{{ route('messages.restore', $conversation->id) }}" method="POST" style="margin:0; display:inline">
            @csrf
            <button type="submit" class="btn btn-success btn-sm" style="font-weight:700; font-size:12px; padding:4px 12px; border-radius:var(--radius-sm); border:none; background:#28a745; color:white">
              <i class="fas fa-undo"></i> Khôi phục để nhắn tin
            </button>
          </form>
        </div>
      @else
        <form action="{{ url('/messages/'.$conversation->id.'/send') }}" method="POST" enctype="multipart/form-data" style="display:flex;gap:10px;width:100%;align-items:flex-end" id="msg-form">
          @csrf
          
          {{-- Attachment Inputs --}}
          <input type="file" name="file" id="file-input" style="display:none" onchange="handleFileSelect(this)">
          
          {{-- Buttons Group --}}
          <div style="display:flex;gap:6px;align-items:center;margin-bottom:2px">
            {{-- Attach File Icon --}}
            <button type="button" class="btn" onclick="document.getElementById('file-input').click()" style="height:40px;width:40px;padding:0;background:var(--bg-light);border:1.5px solid var(--border);border-radius:50%;display:flex;align-items:center;justify-content:center" title="Đính kèm tài liệu/CV">
              <i class="fas fa-paperclip" style="color:var(--text-muted)"></i>
            </button>

            {{-- Quick Replies Icon --}}
            <button type="button" class="btn" onclick="toggleQuickReplies()" style="height:40px;width:40px;padding:0;background:var(--bg-light);border:1.5px solid var(--border);border-radius:50%;display:flex;align-items:center;justify-content:center" title="Tin nhắn mẫu nhanh">
              <i class="fas fa-comment-dots" style="color:var(--text-muted)"></i>
            </button>

            {{-- Schedule Interview Icon (Only Employer) --}}
            @if(auth()->user()->isEmployer())
              <button type="button" class="btn" onclick="openInterviewModal()" style="height:40px;width:40px;padding:0;background:var(--bg-light);border:1.5px solid var(--border);border-radius:50%;display:flex;align-items:center;justify-content:center" title="Lên lịch phỏng vấn">
                <i class="fas fa-calendar-plus" style="color:var(--text-muted)"></i>
              </button>
            @endif
          </div>

          <textarea name="body" id="msg-input" class="form-control" placeholder="Nhập tin nhắn... (Enter để gửi)" rows="1"
            style="flex:1;border:1.5px solid var(--border);border-radius:var(--radius-lg);padding:10px 14px;font-family:inherit;font-size:14px;resize:none;max-height:120px"
            onkeydown="handleEnter(event)">{{ old('body') }}</textarea>

          <button type="submit" class="btn btn-primary" style="flex-shrink:0;height:44px;width:44px;padding:0;justify-content:center;border-radius:50%">
            <i class="fas fa-paper-plane"></i>
          </button>
        </form>
      @endif
    </div>
  </div>
</div>

{{-- Interview Modal Overlay --}}
@if(auth()->user()->isEmployer())
  <div id="interview-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
    <div style="background:white;border-radius:10px;padding:20px;width:400px;max-width:90%;box-shadow:0 5px 15px rgba(0,0,0,0.3)">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;border-bottom:1px solid #eee;padding-bottom:10px">
        <h4 style="margin:0;color:var(--secondary);font-weight:700;font-size:16px"><i class="fas fa-calendar-plus"></i> Lên lịch phỏng vấn</h4>
        <button type="button" onclick="closeInterviewModal()" style="background:transparent;border:none;font-size:20px;cursor:pointer;line-height:1">&times;</button>
      </div>
      <form id="interview-form" action="{{ url('/messages/'.$conversation->id.'/send') }}" method="POST">
        @csrf
        <input type="hidden" name="is_interview" value="1">
        <div class="mb-3" style="text-align:left;margin-bottom:12px">
          <label class="form-label" style="font-weight:600;font-size:12px;display:block;margin-bottom:4px">Thời gian phỏng vấn *</label>
          <input type="datetime-local" name="scheduled_at" class="form-control" style="width:100%;font-size:13px;padding:8px" required>
        </div>
        <div class="mb-3" style="text-align:left;margin-bottom:12px">
          <label class="form-label" style="font-weight:600;font-size:12px;display:block;margin-bottom:4px">Địa điểm / Link phỏng vấn *</label>
          <input type="text" name="location" class="form-control" style="width:100%;font-size:13px;padding:8px" placeholder="Google Meet link hoặc địa chỉ công ty" required>
        </div>
        <div class="mb-3" style="text-align:left;margin-bottom:12px">
          <label class="form-label" style="font-weight:600;font-size:12px;display:block;margin-bottom:4px">Ghi chú thêm</label>
          <textarea name="notes" class="form-control" rows="3" style="width:100%;font-size:13px;padding:8px" placeholder="Ví dụ: Chuẩn bị laptop cài đặt môi trường test..."></textarea>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:16px">
          <button type="button" class="btn btn-outline" style="font-size:13px;padding:6px 12px" onclick="closeInterviewModal()">Hủy</button>
          <button type="submit" class="btn btn-primary" style="font-size:13px;padding:6px 12px">Gửi lời mời</button>
        </div>
      </form>
    </div>
  </div>
@endif

@push('scripts')
<script>
// Scroll to bottom
var container = document.getElementById('messages-container');
if (container) container.scrollTop = container.scrollHeight;

// Tìm kiếm cuộc trò chuyện ở sidebar (Client-side)
var searchInput = document.getElementById('search-input');
var wrappers = document.querySelectorAll('.conversation-wrapper');
if (searchInput) {
  searchInput.addEventListener('input', function() {
    var query = this.value.trim().toLowerCase();
    wrappers.forEach(function(wrapper) {
      var searchText = wrapper.getAttribute('data-search') || '';
      if (searchText.indexOf(query) !== -1) {
        wrapper.style.setProperty('display', 'flex', 'important');
      } else {
        wrapper.style.setProperty('display', 'none', 'important');
      }
    });
  });
}

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

// Khi form submit, chèn token mới nhất
document.querySelector('form[action*="send"]')?.addEventListener('submit', function(e) {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) {
        document.querySelectorAll('input[name="_token"]').forEach(el => el.value = token);
    }
});

// ─── File Upload Functions ───
function handleFileSelect(input) {
  if (input.files && input.files[0]) {
    var file = input.files[0];
    document.getElementById('file-indicator-name').innerHTML = '<i class="fas fa-file-alt"></i> ' + file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
    document.getElementById('file-indicator').style.display = 'flex';
  }
}

function clearSelectedFile() {
  document.getElementById('file-input').value = '';
  document.getElementById('file-indicator').style.display = 'none';
}

// ─── Quick Replies Functions ───
let repliesLoaded = false;
function toggleQuickReplies() {
  var panel = document.getElementById('quick-replies-panel');
  if (panel.style.display === 'none') {
    panel.style.display = 'block';
    if (!repliesLoaded) {
      loadQuickReplies();
    }
  } else {
    panel.style.display = 'none';
  }
}

function loadQuickReplies() {
  fetch('{{ route("messages.quick_replies") }}')
    .then(r => r.json())
    .then(data => {
      if (data.success && data.replies) {
        var list = document.getElementById('quick-replies-list');
        list.innerHTML = '';
        data.replies.forEach(reply => {
          var div = document.createElement('div');
          div.style.padding = '8px';
          div.style.border = '1px solid #eee';
          div.style.borderRadius = '6px';
          div.style.cursor = 'pointer';
          div.style.fontSize = '12px';
          div.style.background = '#f8f9fa';
          div.style.transition = 'background 0.2s';
          div.innerHTML = `<strong>${reply.title}</strong><div style="color:#6c757d;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${reply.body}</div>`;
          div.onclick = function() {
            textarea.value = reply.body;
            panel = document.getElementById('quick-replies-panel');
            panel.style.display = 'none';
            textarea.focus();
            textarea.dispatchEvent(new Event('input'));
          };
          div.onmouseover = function() { this.style.background = '#e9ecef'; };
          div.onmouseout = function() { this.style.background = '#f8f9fa'; };
          list.appendChild(div);
        });
        repliesLoaded = true;
      }
    });
}

// ─── Interview Invitation Functions ───
function openInterviewModal() {
  const now = new Date();
  const offset = now.getTimezoneOffset() * 60000;
  const localISOTime = (new Date(now.getTime() - offset)).toISOString().slice(0, 16);
  
  const scheduledInput = document.querySelector('#interview-form input[name="scheduled_at"]');
  if (scheduledInput) {
    scheduledInput.min = localISOTime;
  }
  
  document.getElementById('interview-modal').style.display = 'flex';
}

function closeInterviewModal() {
  document.getElementById('interview-modal').style.display = 'none';
}

function respondInterview(inviteId, status) {
  if (!confirm('Bạn chắc chắn muốn ' + (status === 'accepted' ? 'ĐỒNG Ý' : 'TỪ CHỐI') + ' thư mời này?')) return;
  
  fetch('/messages/interviews/' + inviteId + '/respond', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    },
    body: JSON.stringify({ status: status })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      // Reload lại trang để hiển thị bubble chat phản hồi mới
      window.location.reload();
    }
  });
}

// Poll new messages
let lastId = {{ $messages->last()?->id ?? 0 }};
if (container) {
  setInterval(function () {
    fetch(`/messages/{{ $conversation->id }}/poll?after=${lastId}`)
      .then(r => r.json())
      .then(data => {
        // Cập nhật trạng thái "Đã xem" cho các tin nhắn cũ
        if (data.read_ids && data.read_ids.length > 0) {
          data.read_ids.forEach(id => {
            const el = document.getElementById('msg-status-' + id);
            if (el && el.textContent.trim() === 'Đã gửi') {
              el.textContent = 'Đã xem';
            }
          });
        }

        if (!data.messages || data.messages.length === 0) return;
        data.messages.forEach(m => {
          const isSent = m.sender_id === {{ auth()->id() }};
          const div = document.createElement('div');
          div.className = 'msg-bubble ' + (isSent ? 'sent' : 'received');
          
          let statusHtml = '';
          if (isSent) {
            statusHtml = ` · <span style="font-style:italic" id="msg-status-${m.id}">Đã gửi</span>`;
          }

          div.innerHTML = `<div class="msg-bubble__text"></div>
                           <div class="msg-bubble__time"></div>`;
          div.querySelector('.msg-bubble__text').textContent = m.body;
          div.querySelector('.msg-bubble__time').innerHTML = m.created_at + statusHtml;
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
