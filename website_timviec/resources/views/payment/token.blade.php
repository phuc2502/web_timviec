@extends('layouts.app')

@section('title', 'Mua lượt ứng tuyển')

@section('content')
<div class="container section" style="max-width:860px">

  <div class="mb-24">
    <h1 class="fw-700 fs-24" style="color:var(--secondary)">🎫 Mua thêm lượt ứng tuyển</h1>
    <p class="text-muted fs-13 mt-8">Chọn gói phù hợp để tiếp tục nộp đơn vào các công việc yêu thích</p>
  </div>

  @if(session('error'))
    <div class="alert alert-danger mb-16">⚠️ {{ session('error') }}</div>
  @endif

  @php
    $tokenRecord = \App\Models\UserToken::where('user_id', auth()->id())->first();
    $balance = $tokenRecord?->balance ?? 0;
  @endphp

  <div style="display:inline-flex;align-items:center;gap:10px;background:#e0f2fe;border:1.5px solid #38bdf8;border-radius:10px;padding:12px 20px;margin-bottom:24px">
    <i class="fas fa-ticket-alt fa-fw" style="color:#0284c7;font-size:16px"></i>
    <span style="font-size:14px;color:#0c4a6e">
      Lượt ứng tuyển hiện tại của bạn:
      <strong style="color:#0369a1;margin-left:4px">{{ $balance }} lượt</strong>
    </span>
  </div>

  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px">
    @php
      $icons  = [5=>'🌱', 10=>'⚡', 20=>'🚀'];
      $tags   = [5=>'', 10=>'Phổ biến', 20=>'Tiết kiệm nhất'];
      $colors = [5=>'var(--border)', 10=>'var(--primary)', 20=>'var(--border)'];
    @endphp
    @foreach($packages as $amount => $price)
      <div style="border:2px solid {{ $colors[$amount] }};border-radius:var(--radius-xl);overflow:hidden;background:#fff;box-shadow:var(--shadow-md);transition:var(--transition);position:relative"
        onmouseover="this.style.boxShadow='var(--shadow-lg)';this.style.transform='translateY(-2px)'"
        onmouseout="this.style.boxShadow='var(--shadow-md)';this.style.transform='translateY(0)'">

        @if($tags[$amount])
          <div style="background:var(--primary);color:#fff;text-align:center;font-size:12px;font-weight:600;padding:5px;letter-spacing:.5px">
            ⭐ {{ $tags[$amount] }}
          </div>
        @else
          <div style="height:27px"></div>
        @endif

        <div style="padding:24px;text-align:center">
          <div style="font-size:40px;margin-bottom:12px">{{ $icons[$amount] }}</div>
          <div class="fw-700" style="font-size:20px;color:var(--secondary)">{{ $amount }} lượt</div>
          <div class="fw-700" style="font-size:28px;color:var(--primary);margin:8px 0">
            {{ number_format($price) }}<span style="font-size:14px;font-weight:500;color:var(--text-secondary)">đ</span>
          </div>
          <div class="text-muted fs-12 mb-16">≈ {{ number_format($price / $amount) }}đ / lượt</div>

          <form action="{{ route('payment.token.initiate') }}" method="POST">
            @csrf
            <input type="hidden" name="package" value="{{ $amount }}">
            <button type="submit" class="btn {{ $amount === 10 ? 'btn-primary' : 'btn-outline' }} btn-block">
              Mua ngay <i class="fas fa-arrow-right fa-fw"></i>
            </button>
          </form>
        </div>
      </div>
    @endforeach
  </div>

  <div class="text-center mt-24" style="color:var(--text-secondary);font-size:12px">
    <i class="fas fa-lock fa-fw" style="color:var(--primary)"></i>
    Thanh toán an toàn qua <strong>VNPay</strong>. Lượt được cộng ngay sau khi thanh toán thành công.
  </div>

</div>
@endsection
