@extends('layouts.app')
@section('title', 'Tin nhắn')

@section('content')
<div class="chat-layout">

  {{-- CONVERSATION LIST --}}
  <div class="chat-sidebar">
    <div style="padding:16px 16px 12px;border-bottom:1px solid var(--border)">
      <div class="fw-800 fs-16" style="color:var(--secondary)">Tin nhắn</div>
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
        <a href="{{ url('/messages/'.$conv->id) }}" class="conversation-item {{ isset($current) && $current->id == $conv->id ? 'active' : '' }}" style="text-decoration:none">
          <div class="avatar avatar-md avatar-placeholder" style="background:var(--primary-light);color:var(--primary);font-size:16px;font-weight:700;flex-shrink:0">
            {{ strtoupper(substr($other->name ?? 'U', 0, 1)) }}
          </div>
          <div style="flex:1;min-width:0">
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
