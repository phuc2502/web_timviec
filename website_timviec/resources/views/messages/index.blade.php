@extends('layouts.app')
@section('title', 'Tin nhắn')

@section('content')
<div class="chat-layout">

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
    <div class="chat-search" style="padding:10px 14px">
      <input type="text" class="form-control" style="font-size:13px" placeholder="🔍 Tìm cuộc trò chuyện...">
    </div>
    <div style="flex:1;overflow-y:auto">
      @forelse($conversations ?? [] as $conv)
        @php
          $other = auth()->user()->user_type === 'employer' ? $conv->employee : $conv->employer;
          $lastMsg = $conv->messages->last();
        @endphp
        <div style="position:relative; display:flex; align-items:center; width:100%">
          <a href="{{ url('/messages/'.$conv->id.(isset($tab) && $tab === 'archive' ? '?tab=archive' : '')) }}" class="conversation-item {{ isset($current) && $current->id == $conv->id ? 'active' : '' }}" style="text-decoration:none; flex:1">
            <div class="avatar avatar-md avatar-placeholder" style="background:var(--primary-light);color:var(--primary);font-size:16px;font-weight:700;flex-shrink:0">
              {{ strtoupper(substr($other->name ?? 'U', 0, 1)) }}
            </div>
            <div style="flex:1;min-width:0;margin-left:10px;padding-right:24px">
              <div class="flex-between">
                <span class="conversation-item__name">{{ $other->name ?? 'Người dùng' }}</span>
                @if($lastMsg)
                  <span class="text-muted" style="font-size:11px">{{ $lastMsg->created_at->diffForHumans(null, true) }}</span>
                @endif
              </div>
              <div class="conversation-item__preview mt-2">
                @if($lastMsg)
                  {{ $lastMsg->sender_id === auth()->id() ? 'Bạn: ' : '' }}{{ Str::limit($lastMsg->body, 40) }}
                @else
                  <em>Chưa có tin nhắn</em>
                @endif
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
      @empty
        <div class="text-center text-muted" style="padding:32px 16px;font-size:13px">
          <i class="fas fa-comment-slash fa-2x mb-12" style="display:block;color:var(--text-muted)"></i>
          Chưa có cuộc trò chuyện nào
        </div>
      @endforelse
    </div>
  </div>

  {{-- EMPTY STATE --}}
  <div class="chat-main flex-center" style="flex-direction:column;background:#f8f9fa">
    <div style="font-size:64px;margin-bottom:16px">💬</div>
    <div class="fw-700 fs-18" style="color:var(--secondary)">Chọn cuộc trò chuyện</div>
    <p class="text-muted mt-8 fs-14">Chọn một cuộc trò chuyện từ danh sách bên trái để bắt đầu</p>
  </div>
</div>
@endsection
