@extends('layouts.dashboard')
@section('title', 'Tin đăng của tôi')

@section('content')

@php
    $isPremium = auth()->user()->isPremium();
    $postCount = auth()->user()->monthlyPostCount();
    $freeLimit = 3;
    $quotaFull = !$isPremium && $postCount >= $freeLimit;
@endphp

<div class="flex-between mb-20">
  <div>
    <h1 class="fs-20 fw-800" style="color:var(--secondary)">Tin tuyển dụng của tôi</h1>
    <p class="text-muted fs-13 mt-4">Quản lý tất cả tin đăng tuyển dụng</p>
  </div>
  @if($quotaFull)
    <button onclick="document.getElementById('quota-modal').style.display='flex'" class="btn btn-primary">
      <i class="fas fa-plus"></i> Đăng tin mới
    </button>
  @else
    <a href="{{ url('/job/create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Đăng tin mới</a>
  @endif
</div>

@if(!$isPremium)
  <div style="background:linear-gradient(135deg,#fff7ed,#fffbeb);border:1px solid #fcd34d;border-radius:12px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:16px">
    <div style="display:flex;align-items:center;gap:12px">
      <i class="fas fa-info-circle" style="color:#f59e0b;font-size:18px"></i>
      <div>
        <span class="fw-600 fs-14" style="color:#92400e">Tài khoản Free — Đã dùng {{ $postCount }}/{{ $freeLimit }} lượt đăng tin tháng này</span>
        <div class="fs-12" style="color:#b45309;margin-top:2px">Nâng cấp Premium để đăng không giới hạn và mở khóa các tính năng nâng cao.</div>
      </div>
    </div>
    <a href="{{ route('payment.subscription') }}" class="btn btn-sm" style="background:#f59e0b;color:#fff;white-space:nowrap;flex-shrink:0">
      <i class="fas fa-crown"></i> Nâng cấp ngay
    </a>
  </div>
@endif

@if($listings->isEmpty())
  <div class="card text-center" style="padding:56px 24px">
    <div style="font-size:52px;margin-bottom:16px">📋</div>
    <div class="fw-700 fs-16">Chưa có tin tuyển dụng nào</div>
    <p class="text-muted mt-8 fs-13">Bắt đầu đăng tin để tìm kiếm ứng viên tài năng</p>
    <a href="{{ url('/job/create') }}" class="btn btn-primary mt-16" style="display:inline-flex"><i class="fas fa-plus"></i> Đăng tin ngay</a>
  </div>
@else
  <div class="card">
    <table class="table">
      <thead>
        <tr>
          <th>Tin tuyển dụng</th>
          <th>Địa điểm</th>
          <th>Lương</th>
          <th>Ứng viên</th>
          <th>Hết hạn</th>
          <th>Trạng thái</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        @foreach($listings as $listing)
          <tr>
            <td>
              <a href="{{ url('/job/show/'.$listing->slug) }}" class="fw-600 fs-13" style="color:var(--secondary)" target="_blank">
                {{ Str::limit($listing->title, 40) }}
              </a>
            </td>
            <td><span class="tag tag-blue fs-12">{{ $listing->address }}</span></td>
            <td class="fw-600 text-primary-color fs-13">
              {{ $listing->salary == 0 ? 'Thỏa thuận' : number_format($listing->salary).'đ' }}
            </td>
            <td>
              <a href="{{ route('employer.applicants', $listing->id) }}" class="flex gap-4" style="align-items:center;color:var(--text-secondary);font-size:13px">
                <i class="fas fa-users" style="color:var(--primary)"></i>
                <span class="fw-700" style="color:var(--primary)">{{ $listing->applications_count }}</span> người
              </a>
            </td>
            <td class="fs-13 text-muted">{{ \Carbon\Carbon::parse($listing->application_close_date)->format('d/m/Y') }}</td>
            <td>
              @if(\Carbon\Carbon::parse($listing->application_close_date)->isPast())
                <span class="status status-closed">Đã hết hạn</span>
              @else
                <span class="status status-open">Đang mở</span>
              @endif
            </td>
            <td>
              <div class="flex gap-6">
                <a href="{{ url('/job/'.$listing->id.'/edit') }}" class="btn btn-outline btn-sm" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                <a href="{{ route('employer.applicants', $listing->id) }}" class="btn btn-primary btn-sm" title="Xem ứng viên"><i class="fas fa-users"></i></a>
                <a href="{{ url('/job/'.$listing->id.'/delete') }}"
                  onclick="return confirm('Xoá tin này?')"
                  class="btn btn-danger btn-sm" title="Xoá"><i class="fas fa-trash"></i></a>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endif

{{-- Modal: Quota hết lượt --}}
<div id="quota-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:9999;align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:20px;padding:40px 36px;max-width:460px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.2)">
    <div style="width:72px;height:72px;background:linear-gradient(135deg,#fef3c7,#fde68a);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
      <i class="fas fa-crown" style="font-size:30px;color:#f59e0b"></i>
    </div>
    <h2 class="fw-800 fs-20" style="color:#1a1a1a;margin-bottom:10px">Bạn đã dùng hết quota miễn phí!</h2>
    <p class="text-muted fs-14" style="line-height:1.7;margin-bottom:24px">
      Tài khoản Free chỉ cho phép đăng <strong>3 tin/tháng</strong>. Bạn đã dùng <strong>{{ $postCount }}/{{ $freeLimit }}</strong> lượt.<br><br>
      Nâng cấp <strong>Premium</strong> để đăng không giới hạn, ưu tiên hiển thị và nhiều tính năng cao cấp khác!
    </p>
    <div style="background:#f8f9fa;border-radius:12px;padding:18px;margin-bottom:24px;text-align:left">
      <div class="fw-700 fs-13" style="color:#333;margin-bottom:10px">✨ Premium bao gồm:</div>
      <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:7px">
        <li class="fs-13" style="color:#555"><i class="fas fa-check" style="color:#00B14F;margin-right:8px"></i>Đăng tin không giới hạn mỗi tháng</li>
        <li class="fs-13" style="color:#555"><i class="fas fa-check" style="color:#00B14F;margin-right:8px"></i>Hiển thị ưu tiên top danh sách</li>
        <li class="fs-13" style="color:#555"><i class="fas fa-check" style="color:#00B14F;margin-right:8px"></i>Thống kê ứng viên nâng cao</li>
        <li class="fs-13" style="color:#555"><i class="fas fa-check" style="color:#00B14F;margin-right:8px"></i>Badge "Premium" trên mỗi tin đăng</li>
      </ul>
    </div>
    <div style="display:flex;gap:12px;justify-content:center">
      <button onclick="document.getElementById('quota-modal').style.display='none'" class="btn btn-outline">Để sau</button>
      <a href="{{ route('payment.subscription') }}" class="btn btn-primary" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
        <i class="fas fa-crown"></i> Mua gói Premium
      </a>
    </div>
  </div>
</div>
@endsection
