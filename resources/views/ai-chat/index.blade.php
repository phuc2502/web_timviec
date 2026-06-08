@extends('layouts.app')

@section('title', 'Trợ lý AI — ITWorks')

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
             style="display: flex; flex-direction: column; gap: 4px; padding: 14px 16px; border-bottom: 1px solid rgba(0,0,0,0.03); color: var(--text-dark); transition: var(--transition);"
             onmouseover="this.style.background='var(--bg-gray)'"
             onmouseout="this.style.background=''">
            <div class="fw-600 fs-13" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
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

    {{-- MAIN CONTENT --}}
    <div style="flex: 1; display: flex; flex-direction: column; background: #f8fafc; align-items: center; justify-content: center; padding: 32px; text-align: center;">
      <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 36px; margin-bottom: 20px; box-shadow: var(--shadow-sm);">
        <i class="fas fa-robot"></i>
      </div>
      <h3 style="font-weight: 700; color: var(--text-dark); margin-bottom: 8px; font-size: 18px;">
        Bắt đầu cuộc trò chuyện với trợ lý AI của ITWorks
      </h3>
      <p style="color: var(--text-secondary); max-width: 450px; font-size: 13.5px; line-height: 1.6; margin-bottom: 24px;">
        Chào bạn! Tôi là trợ lý AI chuyên về tuyển dụng và cơ hội việc làm IT tại ITWorks. Hãy đặt câu hỏi về tư vấn nghề nghiệp, viết CV, viết JD, hoặc chuẩn bị phỏng vấn để bắt đầu!
      </p>
      <form action="{{ route('ai-chat.create') }}" method="POST" style="margin: 0;">
        @csrf
        <button type="submit" class="btn btn-outline" style="font-weight: 700; border-radius: var(--radius-sm); padding: 10px 24px;">
          <i class="fas fa-comments"></i> Bắt đầu Chat ngay
        </button>
      </form>
    </div>

  </div>
</div>
@endsection
