@extends('layouts.app')

@section('title', ($conversation->title ?? 'Trợ lý AI') . ' — ITWorks')

@section('content')
<div class="container mt-24">
  <div class="card" style="display: flex; flex-direction: row; height: 680px; overflow: hidden; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);">
    
    {{-- SIDEBAR --}}
    <div style="width: 280px; border-right: 1px solid var(--border); display: flex; flex-direction: column; background: #fff; flex-shrink: 0;">
      <div style="padding: 16px; border-bottom: 1px solid var(--border);">
        <form action="{{ route('ai-chat.create') }}" method="POST" style="margin: 0;">
          @csrf
          <button type="submit" class="btn btn-primary btn-block" style="gap: 8px; justify-content: center; border-radius: var(--radius-sm);">
            <i class="fas fa-plus"></i> Cuộc trò chuyện mới
          </button>
        </form>
      </div>
      <div style="flex: 1; overflow-y: auto;">
        @forelse($conversations as $c)
          <a href="{{ route('ai-chat.show', $c->id) }}" 
             style="display: flex; flex-direction: column; gap: 4px; padding: 14px 16px; border-bottom: 1px solid rgba(0,0,0,0.03); color: var(--text-dark); transition: var(--transition); text-decoration: none; {{ $c->id == $conversation->id ? 'background: var(--primary-light); border-left: 4px solid var(--primary);' : '' }}"
             onmouseover="this.style.background='{{ $c->id == $conversation->id ? 'var(--primary-light)' : 'var(--bg-gray)' }}'"
             onmouseout="this.style.background='{{ $c->id == $conversation->id ? 'var(--primary-light)' : '' }}'">
            <div class="fw-600 fs-13" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: {{ $c->id == $conversation->id ? 'var(--primary)' : 'var(--text-dark)' }};">
              {{ $c->title ?? 'Cuộc trò chuyện mới' }}
            </div>
            <div class="text-muted fs-11">
              {{ $c->updated_at->diffForHumans() }}
            </div>
          </a>
        @empty
          <div class="text-center text-muted" style="padding: 32px 16px; font-size: 13px;">
            Chưa có lịch sử cuộc trò chuyện.
          </div>
        @endforelse
      </div>
    </div>

    {{-- CHAT INTERFACE --}}
    <div style="flex: 1; display: flex; flex-direction: column; background: #f8fafc;">
      
      {{-- HEADER --}}
      <div style="padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: #fff; flex-shrink: 0;">
        <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
          <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
            <i class="fas fa-robot"></i>
          </div>
          <div style="min-width: 0;">
            <h4 class="fw-700 fs-14 text-secondary" style="margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $conversation->title }}">
              {{ $conversation->title ?? 'Trợ lý AI' }}
            </h4>
            <span style="font-size: 11px; color: var(--text-secondary); display: block; margin-top: 2px;">
              Trợ lý tuyển dụng IT chuyên nghiệp
            </span>
          </div>
        </div>
        <div>
          <form action="{{ route('ai-chat.destroy', $conversation->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa cuộc trò chuyện này? Toàn bộ lịch sử sẽ bị mất vĩnh viễn.')" style="margin: 0;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm" style="display: flex; align-items: center; gap: 6px; border-radius: var(--radius-sm);">
              <i class="fas fa-trash-alt"></i> Xóa cuộc trò chuyện
            </button>
          </form>
        </div>
      </div>

      {{-- MESSAGES LIST --}}
      <div id="chat-messages" style="flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 20px;">
        @php
          $messagesList = $conversation->messages ?? [];
        @endphp
        
        @if(count($messagesList) === 0)
          <div style="margin: auto; max-width: 480px; text-align: center; padding: 40px 20px;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 16px;">
              <i class="fas fa-robot"></i>
            </div>
            <h5 class="fw-700" style="color: var(--text-dark); margin-bottom: 8px;">Xin chào! Tôi có thể giúp gì cho bạn?</h5>
            <p style="color: var(--text-secondary); font-size: 13px; line-height: 1.6;">
              Hãy gửi tin nhắn đầu tiên để bắt đầu cuộc trò chuyện. Tôi có thể hỗ trợ bạn tư vấn nghề nghiệp IT, viết CV, sửa JD hoặc chuẩn bị phỏng vấn.
            </p>
          </div>
        @else
          @foreach($messagesList as $msg)
            @if(($msg['role'] ?? '') === 'user')
              {{-- USER MESSAGE (RIGHT) --}}
              <div style="display: flex; flex-direction: column; align-items: flex-end; align-self: flex-end; max-width: 75%;">
                <div style="background: var(--primary); color: #fff; padding: 12px 16px; border-radius: 16px 16px 2px 16px; font-size: 13.5px; line-height: 1.5; box-shadow: var(--shadow-sm); white-space: pre-wrap; word-break: break-word;">
                  {{ $msg['content'] }}
                </div>
                <span style="font-size: 10px; color: var(--text-secondary); margin-top: 4px; margin-right: 4px;">
                  {{ isset($msg['created_at']) ? \Carbon\Carbon::parse($msg['created_at'])->format('H:i, d/m/Y') : '' }}
                </span>
              </div>
            @else
              {{-- AI MESSAGE (LEFT) --}}
              <div style="display: flex; align-items: flex-start; gap: 10px; align-self: flex-start; max-width: 75%;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #e2e8f0; color: #475569; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; box-shadow: var(--shadow-sm);">
                  <i class="fas fa-robot"></i>
                </div>
                <div style="display: flex; flex-direction: column; align-items: flex-start;">
                  <div class="ai-markdown-content" style="background: #f1f5f9; color: var(--text-dark); padding: 12px 16px; border-radius: 2px 16px 16px 16px; font-size: 13.5px; line-height: 1.5; box-shadow: var(--shadow-sm); word-break: break-word;">
                    {!! \Illuminate\Support\Str::markdown(e($msg['content'])) !!}
                  </div>
                  <span style="font-size: 10px; color: var(--text-secondary); margin-top: 4px; margin-left: 4px;">
                    {{ isset($msg['created_at']) ? \Carbon\Carbon::parse($msg['created_at'])->format('H:i, d/m/Y') : '' }}
                  </span>
                </div>
              </div>
            @endif
          @endforeach
        @endif
      </div>

      {{-- CHAT INPUT FORM --}}
      <div style="padding: 16px 20px; border-top: 1px solid var(--border); background: #fff; flex-shrink: 0;">
        <form id="chat-form" action="{{ route('ai-chat.send', $conversation->id) }}" method="POST" style="margin: 0; display: flex; gap: 12px; align-items: flex-end;">
          @csrf
          <div style="flex: 1; position: relative;">
            <textarea id="chat-input" name="message" class="form-control" placeholder="Hỏi về tuyển dụng, CV, phỏng vấn..." required rows="2" maxlength="1000" style="padding: 10px 14px; border-radius: var(--radius-sm); border: 1px solid var(--border); resize: none; width: 100%; font-family: inherit; font-size: 13.5px;"></textarea>
          </div>
          <button type="submit" id="submit-btn" class="btn btn-primary" style="height: 44px; padding: 0 20px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; gap: 8px; flex-shrink: 0; font-weight: 700;">
            <i class="fas fa-paper-plane"></i> <span>Gửi</span>
          </button>
        </form>
      </div>

    </div>

  </div>
</div>

<style>
/* CSS cho phần Markdown nội dung từ AI */
.ai-markdown-content p {
  margin-bottom: 10px;
}
.ai-markdown-content p:last-child {
  margin-bottom: 0;
}
.ai-markdown-content ul, .ai-markdown-content ol {
  margin-bottom: 10px;
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
  font-size: 12px;
}
.ai-markdown-content pre {
  background: #1e293b;
  color: #f8fafc;
  padding: 12px;
  border-radius: 6px;
  overflow-x: auto;
  margin-bottom: 10px;
}
.ai-markdown-content pre code {
  background: transparent;
  color: inherit;
  padding: 0;
  font-size: 12px;
}
.ai-markdown-content h1, .ai-markdown-content h2, .ai-markdown-content h3 {
  font-size: 15px;
  font-weight: 700;
  margin-top: 12px;
  margin-bottom: 8px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const chatMessages = document.getElementById('chat-messages');
  const chatForm = document.getElementById('chat-form');
  const chatInput = document.getElementById('chat-input');
  const submitBtn = document.getElementById('submit-btn');
  const submitBtnText = submitBtn.querySelector('span');
  const submitBtnIcon = submitBtn.querySelector('i');

  // 1. Tự động cuộn xuống cuối danh sách tin nhắn khi load trang
  chatMessages.scrollTop = chatMessages.scrollHeight;

  // 2. Nhấn Enter để gửi (Shift+Enter để xuống dòng)
  chatInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault(); // Tránh tạo dòng mới
      chatForm.dispatchEvent(new Event('submit', { cancelable: true }));
    }
  });

  // 3. Gửi tin nhắn qua AJAX giúp trả lời cực nhanh không cần load lại trang
  chatForm.addEventListener('submit', function(e) {
    e.preventDefault();

    if (submitBtn.disabled) {
      return;
    }

    const messageContent = chatInput.value.trim();
    if (!messageContent) {
      return;
    }

    // A. Thêm tin nhắn của User vào khung chat ngay lập tức
    const userMsgHtml = `
      <div style="display: flex; flex-direction: column; align-items: flex-end; align-self: flex-end; max-width: 75%;">
        <div style="background: var(--primary); color: #fff; padding: 12px 16px; border-radius: 16px 16px 2px 16px; font-size: 13.5px; line-height: 1.5; box-shadow: var(--shadow-sm); white-space: pre-wrap; word-break: break-word;">${escapeHtml(messageContent)}</div>
        <span style="font-size: 10px; color: var(--text-secondary); margin-top: 4px; margin-right: 4px;">Vừa xong</span>
      </div>
    `;

    // Ẩn khung chào mừng nếu có
    const welcomeScreen = chatMessages.querySelector('div[style*="margin: auto"]');
    if (welcomeScreen) {
      chatMessages.innerHTML = '';
    }

    chatMessages.insertAdjacentHTML('beforeend', userMsgHtml);
    chatMessages.scrollTop = chatMessages.scrollHeight;

    // B. Thêm tin nhắn chờ AI phản hồi
    const aiLoadingId = 'ai-loading-' + Date.now();
    const aiLoadingHtml = `
      <div id="${aiLoadingId}" style="display: flex; align-items: flex-start; gap: 10px; align-self: flex-start; max-width: 75%;">
        <div style="width: 32px; height: 32px; border-radius: 50%; background: #e2e8f0; color: #475569; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; box-shadow: var(--shadow-sm);">
          <i class="fas fa-robot"></i>
        </div>
        <div style="display: flex; flex-direction: column; align-items: flex-start;">
          <div style="background: #f1f5f9; color: var(--text-dark); padding: 12px 16px; border-radius: 2px 16px 16px 16px; font-size: 13.5px; line-height: 1.5; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-spinner fa-spin" style="color: var(--primary);"></i>
            <span style="color: var(--text-secondary); font-style: italic; font-size: 13px;">AI đang trả lời...</span>
          </div>
        </div>
      </div>
    `;
    chatMessages.insertAdjacentHTML('beforeend', aiLoadingHtml);
    chatMessages.scrollTop = chatMessages.scrollHeight;

    // C. Vô hiệu hóa input và thay đổi trạng thái nút Gửi
    chatInput.readOnly = true;
    submitBtn.disabled = true;
    submitBtn.style.opacity = '0.7';
    submitBtn.style.cursor = 'not-allowed';
    submitBtnText.textContent = 'Đang trả lời...';
    submitBtnIcon.className = 'fas fa-spinner fa-spin';

    // D. Reset input
    chatInput.value = '';

    // E. Chuẩn bị gửi request
    const formData = new FormData(chatForm);
    formData.set('message', messageContent);

    fetch(chatForm.action, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Đã xảy ra lỗi kết nối.');
      }
      return response.json();
    })
    .then(data => {
      // Xóa tin nhắn chờ
      const loadingEl = document.getElementById(aiLoadingId);
      if (loadingEl) {
        loadingEl.remove();
      }

      if (data.success) {
        // Thêm câu trả lời của AI vào khung chat
        const aiMsgHtml = `
          <div style="display: flex; align-items: flex-start; gap: 10px; align-self: flex-start; max-width: 75%;">
            <div style="width: 32px; height: 32px; border-radius: 50%; background: #e2e8f0; color: #475569; display: flex; align-items: center; justify-content: center; font-size: 13px; flex-shrink: 0; box-shadow: var(--shadow-sm);">
              <i class="fas fa-robot"></i>
            </div>
            <div style="display: flex; flex-direction: column; align-items: flex-start;">
              <div class="ai-markdown-content" style="background: #f1f5f9; color: var(--text-dark); padding: 12px 16px; border-radius: 2px 16px 16px 16px; font-size: 13.5px; line-height: 1.5; box-shadow: var(--shadow-sm); word-break: break-word;">
                ${data.ai_message.content_markdown}
              </div>
              <span style="font-size: 10px; color: var(--text-secondary); margin-top: 4px; margin-left: 4px;">
                ${data.ai_message.created_at}
              </span>
            </div>
          </div>
        `;
        chatMessages.insertAdjacentHTML('beforeend', aiMsgHtml);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        // Cập nhật tiêu đề trên Header
        const headerTitle = document.querySelector('h4[title]');
        if (headerTitle) {
          headerTitle.textContent = data.conversation_title;
          headerTitle.title = data.conversation_title;
        }

        // Cập nhật tiêu đề trong Sidebar danh sách chat
        const activeSidebarItem = document.querySelector('a[style*="background: var(--primary-light)"]');
        if (activeSidebarItem) {
          const titleDiv = activeSidebarItem.querySelector('div.fw-600.fs-13');
          if (titleDiv) {
            titleDiv.textContent = data.conversation_title;
          }
        }
      } else {
        alert(data.message || 'Không nhận được câu trả lời từ AI.');
      }
    })
    .catch(error => {
      console.error(error);
      const loadingEl = document.getElementById(aiLoadingId);
      if (loadingEl) {
        loadingEl.remove();
      }
      alert('Không thể kết nối đến máy chủ AI. Vui lòng kiểm tra kết nối mạng.');
    })
    .finally(() => {
      // Khôi phục trạng thái nhập liệu
      chatInput.readOnly = false;
      submitBtn.disabled = false;
      submitBtn.style.opacity = '';
      submitBtn.style.cursor = '';
      submitBtnText.textContent = 'Gửi';
      submitBtnIcon.className = 'fas fa-paper-plane';
      chatInput.focus();
    });
  });

  // Hàm escape HTML đơn giản
  function escapeHtml(text) {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
  }
});
</script>
@endsection
