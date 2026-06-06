@extends('layouts.admin')
@section('title', 'Lịch sử giao dịch thanh toán')

@section('content')
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-18 fw-800" style="color:var(--secondary)">Quản lý giao dịch</h1>
    <p class="text-muted fs-13 mt-2">Tổng số: <strong>{{ $transactions->total() }}</strong> giao dịch thanh toán Premium qua VNPay</p>
  </div>
  
  {{-- Bộ lọc tìm kiếm --}}
  <form action="{{ url('/admin/transactions') }}" method="GET">
    <div style="display:flex; gap:8px; align-items: center;">
      <input type="text" name="search" class="form-control" style="width:220px; font-size:13px;" placeholder="Mã GD, tên, email..." value="{{ request('search') }}">
      
      <select name="plan" class="form-control" style="width:130px; font-size:13px; cursor:pointer;">
        <option value="">Tất cả gói mua</option>
        <option value="monthly" {{ request('plan') == 'monthly' ? 'selected' : '' }}>Gói Tháng</option>
        <option value="yearly" {{ request('plan') == 'yearly' ? 'selected' : '' }}>Gói Năm</option>
      </select>

      <select name="status" class="form-control" style="width:140px; font-size:13px; cursor:pointer;">
        <option value="">Tất cả trạng thái</option>
        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>✅ Thành công</option>
        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ Chờ thanh toán</option>
        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>❌ Thất bại</option>
        <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>↩️ Đã hoàn tiền</option>
      </select>

      <button type="submit" class="btn btn-primary btn-sm" style="padding:0 14px; height: 38px;"><i class="fas fa-search"></i> Lọc</button>
    </div>
  </form>
</div>

<div class="card shadow-sm" style="border-radius: var(--radius-lg); overflow: hidden;">
  <table class="table" style="vertical-align: middle;">
    <thead>
      <tr style="background: #f8fafc; border-bottom: 1px solid var(--border);">
        <th style="width: 50px;">ID</th>
        <th>Khách hàng</th>
        <th>Mã tham chiếu GD (VNPay)</th>
        <th style="text-align: right;">Số tiền</th>
        <th style="text-align: center;">Gói mua</th>
        <th style="text-align: center;">Trạng thái</th>
        <th>Thời gian thanh toán</th>
      </tr>
    </thead>
    <tbody>
      @forelse($transactions as $t)
        <tr>
          <td class="text-muted fs-12 fw-700">#{{ $t->id }}</td>
          <td>
            @if($t->user)
              <div class="flex gap-10" style="align-items:center">
                <div class="avatar avatar-sm avatar-placeholder" style="background:var(--primary-light); color:var(--primary); font-size:12px; font-weight:700; flex-shrink:0;">
                  {{ strtoupper(substr($t->user->name, 0, 1)) }}
                </div>
                <div style="min-width: 0; flex: 1;">
                  <div class="fw-700 fs-13" style="color:var(--secondary); text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">{{ $t->user->name }}</div>
                  <div class="text-muted fs-12" style="text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">{{ $t->user->email }}</div>
                </div>
              </div>
            @else
              <span class="text-muted fs-12 italic">Người dùng đã bị xóa</span>
            @endif
          </td>
          <td>
            <div class="fw-600 fs-12" style="color:#1e293b;">Ref: {{ $t->vnp_txn_ref }}</div>
            @if($t->vnp_transaction_no)
              <div class="text-muted fs-11" style="margin-top: 2px;">VNPay No: {{ $t->vnp_transaction_no }}</div>
            @endif
          </td>
          <td style="text-align: right;" class="fw-700 text-secondary fs-13">
            {{ number_format($t->amount) }}đ
          </td>
          <td style="text-align: center;">
            @if($t->plan === 'yearly')
              <span class="tag fs-11" style="background:#f5f3ff; color:#7c3aed; border:1px solid #ddd6fe; padding:2px 8px; border-radius:4px; font-weight:600;">Năm (Yearly)</span>
            @else
              <span class="tag fs-11" style="background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; padding:2px 8px; border-radius:4px; font-weight:600;">Tháng (Monthly)</span>
            @endif
          </td>
          <td style="text-align: center;">
            @if($t->status === 'paid')
              <span class="status status-open" style="font-size:11px; font-weight:600; background:#ecfdf5; color:#10b981; border:1px solid #d1fae5; padding: 2px 8px; border-radius: 4px;">Thành công</span>
            @elseif($t->status === 'pending')
              <span class="status status-pending" style="font-size:11px; font-weight:600; background:#fffbeb; color:#d97706; border:1px solid #fef3c7; padding: 2px 8px; border-radius: 4px;">Chờ thanh toán</span>
            @elseif($t->status === 'failed')
              <span class="status status-closed" style="font-size:11px; font-weight:600; background:#fef2f2; color:#ef4444; border:1px solid #fee2e2; padding: 2px 8px; border-radius: 4px;">Thất bại</span>
            @elseif($t->status === 'refunded')
              <span class="status status-closed" style="font-size:11px; font-weight:600; background:#fff7ed; color:#ea580c; border:1px solid #ffedd5; padding: 2px 8px; border-radius: 4px;">Đã hoàn tiền</span>
            @endif
          </td>
          <td class="text-muted fs-12">
            @if($t->paid_at)
              {{ $t->paid_at->format('H:i d/m/Y') }}
            @else
              {{ $t->created_at->format('H:i d/m/Y') }} <span class="text-muted fs-10">(Khởi tạo)</span>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="text-center text-muted" style="padding:32px">Không tìm thấy giao dịch nào thỏa mãn điều kiện tìm kiếm.</td></tr>
      @endforelse
    </tbody>
  </table>

  {{-- Phân trang chuẩn --}}
  @if($transactions->hasPages())
    <div class="card-footer" style="background:#f8fafc; border-top:1px solid var(--border);">
      <div class="flex-between">
        <span class="text-muted fs-13">Đang xem {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} trong tổng số {{ $transactions->total() }}</span>
        <div class="pagination">
          @if(!$transactions->onFirstPage())<a href="{{ $transactions->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>@endif
          @foreach($transactions->getUrlRange(max(1,$transactions->currentPage()-2), min($transactions->lastPage(),$transactions->currentPage()+2)) as $page => $url)
            @if ($page == $transactions->currentPage())
              <span class="active" style="background:var(--primary); color:white;">{{ $page }}</span>
            @else
              <a href="{{ $url }}">{{ $page }}</a>
            @endif
          @endforeach
          @if($transactions->hasMorePages())<a href="{{ $transactions->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>@endif
        </div>
      </div>
    </div>
  @endif
</div>
@endsection
