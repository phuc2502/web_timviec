@extends('layouts.dashboard')
@section('title', 'Thông tin cá nhân')

@section('content')
@php $isEmployer = auth()->user()->user_type === 'employer'; @endphp

{{-- Employer accent banner --}}
@if($isEmployer)
<div style="background:linear-gradient(135deg,#2563eb,#1d4ed8);border-radius:12px;padding:16px 20px;margin-bottom:16px;display:flex;align-items:center;gap:14px;color:#fff">
  <div style="width:40px;height:40px;background:rgba(255,255,255,.2);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
    <i class="fas fa-building fs-18"></i>
  </div>
  <div style="flex:1">
    <div class="fw-700 fs-14">Trang quản lý Nhà tuyển dụng</div>
    <div style="font-size:12px;opacity:.85;margin-top:2px">Cập nhật thông tin công ty để thu hút ứng viên chất lượng hơn</div>
  </div>
  @if(auth()->user()->isPremium())
  @php $daysLeft = auth()->user()->daysLeft(); @endphp
  <div style="background:rgba(255,255,255,.15);border-radius:8px;padding:8px 14px;text-align:center;flex-shrink:0">
    <div style="font-size:10px;opacity:.8;text-transform:uppercase;letter-spacing:.5px">Gói Premium</div>
    <div class="fw-800 fs-16">{{ $daysLeft ?? '∞' }}<span style="font-size:11px;font-weight:400"> ngày</span></div>
  </div>
  @else
  <a href="{{ route('payment.subscription') }}" style="background:rgba(255,255,255,.2);border-radius:8px;padding:8px 14px;color:#fff;font-size:12px;font-weight:600;text-decoration:none;flex-shrink:0;white-space:nowrap">
    <i class="fas fa-crown" style="margin-right:4px;color:#f59e0b"></i> Nâng cấp Premium
  </a>
  @endif
</div>
@endif

<div class="flex-between mb-20">
  <div>
    <h1 class="fs-20 fw-800" style="color:var(--secondary)">Thông tin cá nhân</h1>
    <p class="text-muted fs-13 mt-4">Cập nhật hồ sơ để tăng khả năng được tìm thấy</p>
  </div>
</div>

{{-- Profile Completeness Bar --}}
@php $pct = $completeness['percent']; @endphp
<div class="card mb-16" style="padding:16px 20px">
  <div class="flex-between mb-8">
    <span class="fw-600 fs-13">Độ hoàn thiện hồ sơ</span>
    <span class="fw-800 fs-15" style="color:{{ $pct >= 80 ? 'var(--success)' : ($pct >= 50 ? 'var(--warning)' : 'var(--danger)') }}">{{ $pct }}%</span>
  </div>
  <div style="background:#eee;border-radius:99px;height:8px;overflow:hidden">
    <div style="width:{{ $pct }}%;height:100%;background:{{ $pct >= 80 ? 'var(--success,#00B14F)' : ($pct >= 50 ? '#f59e0b' : '#ef4444') }};border-radius:99px;transition:.4s"></div>
  </div>
  @if(!empty($completeness['missing']))
  <div class="fs-12 text-muted mt-8">
    <i class="fas fa-info-circle" style="color:var(--primary)"></i>
    Còn thiếu: <strong>{{ implode(', ', $completeness['missing']) }}</strong>
  </div>
  @endif
</div>

{{-- Tabs — tab Kỹ năng ẩn với employer --}}
<div style="border-bottom:2px solid var(--border);margin-bottom:20px;display:flex;gap:0">
  @php
    $profileTabs = [
      ['tab-info',     'fas fa-user',  'Thông tin cá nhân',     true],
      ['tab-skills',   'fas fa-tools', 'Kỹ năng & Kinh nghiệm', auth()->user()->user_type === 'employee'],
      ['tab-security', 'fas fa-lock',  'Bảo mật',               true],
      ['tab-notif',    'fas fa-bell',  'Thông báo',             true],
    ];
  @endphp
  @foreach($profileTabs as [$tabId,$tabIcon,$tabLabel,$tabVisible])
    @if($tabVisible)
    <button onclick="switchTab('{{ $tabId }}')" id="btn-{{ $tabId }}"
      style="padding:10px 20px;border:none;background:none;cursor:pointer;font-size:13px;font-weight:600;color:var(--text-muted);border-bottom:2px solid transparent;margin-bottom:-2px;transition:.2s">
      <i class="{{ $tabIcon }}" style="margin-right:6px"></i>{{ $tabLabel }}
    </button>
    @endif
  @endforeach
</div>

<div class="grid" style="grid-template-columns:220px 1fr;gap:20px;align-items:start">

  {{-- LEFT SIDEBAR --}}
  <div class="flex-col gap-16">
    {{-- Avatar card --}}
    <div class="card">
      <div class="card-body" style="padding:24px;text-align:center">
        {{-- Employer: viền xanh nhận diện rõ --}}
        <div style="position:relative;display:inline-block;margin-bottom:12px">
          @if($isEmployer && auth()->user()->company_logo)
            {{-- Logo công ty làm avatar chính --}}
            <div style="width:72px;height:72px;margin:0 auto;border-radius:12px;overflow:hidden;border:3px solid #2563eb;background:#f0f4ff;display:flex;align-items:center;justify-content:center">
              <img src="{{ asset('storage/images/'.auth()->user()->company_logo) }}" style="width:100%;height:100%;object-fit:contain" alt="Logo">
            </div>
          @elseif(auth()->user()->profile_pic)
            <img src="{{ asset('storage/images/'.auth()->user()->profile_pic) }}"
              class="avatar avatar-xl"
              style="border:3px solid {{ $isEmployer ? '#2563eb' : 'var(--primary-light)' }}"
              alt="">
          @else
            <div class="avatar avatar-xl avatar-placeholder"
              style="font-size:32px;background:{{ $isEmployer ? '#dbeafe' : 'var(--primary-light)' }};color:{{ $isEmployer ? '#2563eb' : 'var(--primary)' }};margin:0 auto;border:3px solid {{ $isEmployer ? '#2563eb' : 'var(--primary-light)' }}">
              {{ auth()->user()->initials() }}
            </div>
          @endif
        </div>
        @if($isEmployer && auth()->user()->company_name)
          <div class="fw-700 fs-15">{{ auth()->user()->company_name }}</div>
          <div class="text-muted fs-12 mt-2">{{ auth()->user()->name }}</div>
        @else
          <div class="fw-700 fs-15">{{ auth()->user()->name }}</div>
        @endif
        <div class="text-muted fs-12 mt-4">{{ auth()->user()->email }}</div>
        <div class="tag mt-10" style="display:inline-flex;background:{{ $isEmployer ? '#dbeafe' : '#d1fae5' }};color:{{ $isEmployer ? '#2563eb' : '#065f46' }}">
          @if($isEmployer)
            <i class="fas fa-building" style="margin-right:4px"></i> Nhà tuyển dụng
            @if(auth()->user()->isPremium())
              &nbsp;· <i class="fas fa-crown" style="margin:0 2px;color:#f59e0b"></i> Premium
              @php $days = auth()->user()->daysLeft(); @endphp
              @if($days !== null)
                <span style="font-size:11px;color:#f59e0b;margin-left:4px">({{ $days }} ngày)</span>
              @endif
            @endif
          @else
            <i class="fas fa-search" style="margin-right:4px"></i> Đang tìm việc
          @endif
        </div>
        @if(!auth()->user()->email_verified_at)
        <div class="tag" style="display:inline-flex;background:#fef3cd;color:#856404;margin-top:6px;font-size:11px">
          <i class="fas fa-exclamation-triangle" style="margin-right:4px"></i> Chưa xác minh email
        </div>
        @else
        <div class="tag tag-green" style="display:inline-flex;margin-top:6px;font-size:11px">
          <i class="fas fa-check-circle" style="margin-right:4px"></i> Email đã xác minh
        </div>
        @endif
      </div>
    </div>

    {{-- Stats (employer only) --}}
    @if(auth()->user()->user_type === 'employer')
    <div class="card">
      <div class="card-header"><span class="fw-600 fs-13">Tổng quan</span></div>
      <div class="card-body" style="padding:16px">
        <div class="flex-between" style="margin-bottom:10px">
          <span class="fs-13 text-muted">Tin đang đăng</span>
          <span class="fw-700 fs-15">{{ auth()->user()->listings()->whereIn('status',['active','published'])->count() }}</span>
        </div>
        <div class="flex-between" style="margin-bottom:10px">
          <span class="fs-13 text-muted">Tổng ứng viên</span>
          <span class="fw-700 fs-15">{{ \App\Models\Application::whereHas('listing', fn($q) => $q->where('user_id', auth()->id()))->count() }}</span>
        </div>
        <div class="flex-between">
          <span class="fs-13 text-muted">Đã duyệt hồ sơ</span>
          <span class="fw-700 fs-15">{{ \App\Models\Application::whereHas('listing', fn($q) => $q->where('user_id', auth()->id()))->where('status','approved')->count() }}</span>
        </div>
      </div>
    </div>
    @endif
  </div>

  {{-- RIGHT CONTENT --}}
  <div>

    {{-- TAB: Thông tin cá nhân --}}
    <div id="tab-info" class="tab-pane flex-col gap-16">
      <div class="card">
        <div class="card-header">
          <span class="fw-700 fs-14"><i class="fas fa-user" style="color:var(--primary);margin-right:8px"></i>Thông tin cơ bản</span>
        </div>
        <div class="card-body" style="padding:24px">
          <form action="{{ route(auth()->user()->user_type === 'employer' ? 'profile.update.employer' : 'profile.update.employee') }}"
                method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Avatar upload inline --}}
            <div class="form-group mb-16" style="display:flex;align-items:center;gap:14px">
              <label for="avatar-upload" style="cursor:pointer;position:relative">
                @if(auth()->user()->profile_pic)
                  <img id="avatar-preview" src="{{ asset('storage/images/'.auth()->user()->profile_pic) }}" class="avatar avatar-lg" style="border:2px solid var(--primary-light)" alt="">
                @else
                  <div id="avatar-preview-initials" class="avatar avatar-lg avatar-placeholder" style="font-size:20px;background:var(--primary-light);color:var(--primary)">{{ auth()->user()->initials() }}</div>
                @endif
                <div style="position:absolute;bottom:0;right:0;width:22px;height:22px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid #fff">
                  <i class="fas fa-camera" style="font-size:9px;color:#fff"></i>
                </div>
              </label>
              <input type="file" id="avatar-upload" name="profile_pic" accept="image/*" style="display:none" onchange="previewAvatar(this)">
              <div>
                <div class="fw-600 fs-13">Ảnh đại diện</div>
                <div class="fs-12 text-muted">JPG, PNG, WEBP · Tối đa 2MB</div>
              </div>
            </div>

            <div class="grid-2" style="gap:14px;margin-bottom:14px">
              <div class="form-group">
                <label class="form-label">Họ và tên <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
              </div>
              <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="{{ auth()->user()->email }}" disabled style="background:#f8f9fa;color:var(--text-muted)">
                <div class="form-hint">Email không thể thay đổi</div>
              </div>
            </div>

            <div class="form-group mb-14">
              <label class="form-label">Giới thiệu bản thân</label>
              <textarea name="about" class="form-control" rows="4" placeholder="Chia sẻ kinh nghiệm và mục tiêu nghề nghiệp...">{{ old('about', auth()->user()->about) }}</textarea>
            </div>

            @if(auth()->user()->user_type === 'employer')
              <div class="divider mb-14"></div>
              <div class="fw-600 fs-12 text-muted mb-12" style="text-transform:uppercase;letter-spacing:.5px">Thông tin công ty</div>

              {{-- Company logo --}}
              <div class="form-group mb-14" style="display:flex;align-items:center;gap:14px">
                <label for="logo-upload" style="cursor:pointer;position:relative">
                  @if(auth()->user()->company_logo)
                    <img src="{{ asset('storage/images/'.auth()->user()->company_logo) }}" style="width:56px;height:56px;object-fit:contain;border:1px solid var(--border);border-radius:8px" alt="">
                  @else
                    <div style="width:56px;height:56px;background:#f0f4f8;border-radius:8px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border)">
                      <i class="fas fa-image" style="color:#aaa;font-size:20px"></i>
                    </div>
                  @endif
                  <div style="position:absolute;bottom:-4px;right:-4px;width:20px;height:20px;background:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid #fff">
                    <i class="fas fa-camera" style="font-size:8px;color:#fff"></i>
                  </div>
                </label>
                <input type="file" id="logo-upload" name="company_logo" accept="image/*" style="display:none">
                <div>
                  <div class="fw-600 fs-13">Logo công ty</div>
                  <div class="fs-12 text-muted">Tối đa 2MB</div>
                </div>
              </div>

              <div class="grid-2" style="gap:14px;margin-bottom:14px">
                <div class="form-group">
                  <label class="form-label">Tên công ty</label>
                  <input type="text" name="company_name" class="form-control" value="{{ old('company_name', auth()->user()->company_name) }}" placeholder="FPT Software...">
                </div>
                <div class="form-group">
                  <label class="form-label">Website</label>
                  <input type="url" name="company_website" class="form-control" value="{{ old('company_website', auth()->user()->company_website) }}" placeholder="https://company.com">
                </div>
              </div>
              <div class="form-group mb-14">
                <label class="form-label">Quy mô công ty</label>
                <select name="company_size" class="form-control">
                  <option value="">Chọn quy mô...</option>
                  @foreach(['1-10','11-50','51-200','201-500','500+'] as $size)
                    <option value="{{ $size }}" {{ old('company_size', auth()->user()->company_size) == $size ? 'selected' : '' }}>{{ $size }} nhân viên</option>
                  @endforeach
                </select>
              </div>
            @else
              {{-- Employee location --}}
              <div class="form-group mb-14">
                <label class="form-label">Địa điểm làm việc</label>
                <select name="location" class="form-control">
                  <option value="">Chọn địa điểm...</option>
                  @foreach(['Hà Nội','Hồ Chí Minh','Đà Nẵng','Cần Thơ','Khác'] as $loc)
                    <option value="{{ $loc }}" {{ old('location', auth()->user()->location) == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                  @endforeach
                </select>
              </div>
            @endif

            <div style="text-align:right">
              <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu thay đổi</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    {{-- TAB: Kỹ năng & Kinh nghiệm (employee only) --}}
    <div id="tab-skills" class="tab-pane flex-col gap-16" style="display:none">
      @if(auth()->user()->user_type === 'employee')
      <div class="card">
        <div class="card-header">
          <span class="fw-700 fs-14"><i class="fas fa-tools" style="color:var(--primary);margin-right:8px"></i>Kỹ năng & Kinh nghiệm</span>
        </div>
        <div class="card-body" style="padding:24px">
          <form action="{{ route('profile.update.employee') }}" method="POST">
            @csrf
            {{-- Hidden fields to keep other data --}}
            <input type="hidden" name="name" value="{{ e(auth()->user()->name) }}">
            <input type="hidden" name="about" value="{{ e(auth()->user()->about) }}">
            <input type="hidden" name="location" value="{{ e(auth()->user()->location) }}">

            {{-- Skills tags input --}}
            <div class="form-group mb-16">
              <label class="form-label">Kỹ năng</label>
              <div id="skills-container" style="border:1px solid var(--border);border-radius:8px;padding:8px 10px;min-height:44px;display:flex;flex-wrap:wrap;gap:6px;cursor:text" onclick="document.getElementById('skill-input').focus()">
                @foreach(auth()->user()->skills ?? [] as $skill)
                  <span class="tag tag-blue" style="display:inline-flex;align-items:center;gap:4px" data-skill="{{ $skill }}">
                    {{ $skill }}
                    <button type="button" onclick="removeSkill(this)" style="background:none;border:none;cursor:pointer;padding:0;color:inherit;line-height:1">&times;</button>
                    <input type="hidden" name="skills[]" value="{{ $skill }}">
                  </span>
                @endforeach
                <input type="text" id="skill-input" placeholder="{{ empty(auth()->user()->skills) ? 'Nhập kỹ năng rồi Enter (vd: Laravel, React, Python)' : 'Thêm kỹ năng...' }}"
                  style="border:none;outline:none;flex:1;min-width:120px;font-size:13px;background:transparent"
                  onkeydown="addSkillOnEnter(event)">
              </div>
              <div class="form-hint">Nhấn Enter để thêm từng kỹ năng</div>
            </div>

            <div class="grid-2" style="gap:14px;margin-bottom:14px">
              <div class="form-group">
                <label class="form-label">Số năm kinh nghiệm</label>
                <select name="experience_years" class="form-control">
                  <option value="">Chọn...</option>
                  <option value="0" {{ (auth()->user()->experience_years ?? '') == '0' ? 'selected' : '' }}>Chưa có kinh nghiệm</option>
                  <option value="1" {{ (auth()->user()->experience_years ?? '') == '1' ? 'selected' : '' }}>1 năm</option>
                  <option value="2" {{ (auth()->user()->experience_years ?? '') == '2' ? 'selected' : '' }}>2 năm</option>
                  <option value="3" {{ (auth()->user()->experience_years ?? '') == '3' ? 'selected' : '' }}>3 năm</option>
                  <option value="5" {{ (auth()->user()->experience_years ?? '') == '5' ? 'selected' : '' }}>5+ năm</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Mức lương mong muốn (VNĐ/tháng)</label>
                <input type="number" name="desired_salary" class="form-control" value="{{ old('desired_salary', auth()->user()->desired_salary) }}" placeholder="15000000">
              </div>
            </div>

            <div class="form-group mb-14">
              <label class="form-label">Hình thức làm việc mong muốn</label>
              <select name="job_type_pref" class="form-control">
                <option value="">Chọn...</option>
                @foreach(['full-time'=>'Full-time','part-time'=>'Part-time','remote'=>'Remote','freelance'=>'Freelance'] as $val=>$label)
                  <option value="{{ $val }}" {{ (auth()->user()->job_type_pref ?? '') == $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>

            <div style="text-align:right">
              <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu kỹ năng</button>
            </div>
          </form>
        </div>
      </div>
      @else
      <div class="card">
        <div class="card-body" style="padding:40px;text-align:center;color:var(--text-muted)">
          <i class="fas fa-info-circle fs-24 mb-10" style="display:block"></i>
          Tab này chỉ dành cho ứng viên.
        </div>
      </div>
      @endif
    </div>

    {{-- TAB: Bảo mật --}}
    <div id="tab-security" class="tab-pane flex-col gap-16" style="display:none">
      <div class="card">
        <div class="card-header">
          <span class="fw-700 fs-14"><i class="fas fa-lock" style="color:var(--primary);margin-right:8px"></i>Đổi mật khẩu</span>
        </div>
        <div class="card-body" style="padding:24px">
          @if(is_null(auth()->user()->password))
            <div style="padding:16px;background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;color:#92400e;font-size:13px">
              <i class="fas fa-info-circle" style="margin-right:6px"></i>
              Tài khoản của bạn được đăng nhập qua <strong>{{ auth()->user()->google_id ? 'Google' : 'GitHub' }}</strong>.
              Để đặt mật khẩu, hãy nhập mật khẩu mới bên dưới (bỏ trống ô "Mật khẩu hiện tại").
            </div>
            <form action="{{ route('profile.password') }}" method="POST" style="margin-top:16px">
              @csrf
              <input type="hidden" name="current_password" value="">
              <div class="flex-col gap-14">
                <div class="grid-2" style="gap:14px">
                  <div class="form-group">
                    <label class="form-label">Mật khẩu mới <span class="required">*</span></label>
                    <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="Tối thiểu 8 ký tự">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                  <div class="form-group">
                    <label class="form-label">Xác nhận mật khẩu mới</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Nhập lại">
                  </div>
                </div>
                <div style="text-align:right">
                  <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Đặt mật khẩu</button>
                </div>
              </div>
            </form>
          @else
          <form action="{{ route('profile.password') }}" method="POST">
            @csrf
            <div class="flex-col gap-14">
              <div class="form-group">
                <label class="form-label">Mật khẩu hiện tại <span class="required">*</span></label>
                <input type="password" name="current_password" class="form-control {{ $errors->has('current_password') ? 'is-invalid' : '' }}" placeholder="Nhập mật khẩu hiện tại">
                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="grid-2" style="gap:14px">
                <div class="form-group">
                  <label class="form-label">Mật khẩu mới <span class="required">*</span></label>
                  <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="Tối thiểu 8 ký tự">
                  @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                  <label class="form-label">Xác nhận mật khẩu mới</label>
                  <input type="password" name="password_confirmation" class="form-control" placeholder="Nhập lại">
                </div>
              </div>
              <div style="text-align:right">
                <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Cập nhật mật khẩu</button>
              </div>
            </div>
          </form>
          @endif
        </div>
      </div>
    </div>

    {{-- TAB: Thông báo --}}
    <div id="tab-notif" class="tab-pane flex-col gap-16" style="display:none">
      <div class="card">
        <div class="card-header">
          <span class="fw-700 fs-14"><i class="fas fa-bell" style="color:var(--primary);margin-right:8px"></i>Cài đặt thông báo</span>
        </div>
        <div class="card-body" style="padding:24px">
          <form action="{{ route('profile.notification-settings') }}" method="POST">
            @csrf

            @php
            $toggles = [
              ['mail', 'Nhận email thông báo', 'Bật/tắt toàn bộ email từ hệ thống'],
              ['notify_shortlist', 'Được shortlist', 'Khi nhà tuyển dụng shortlist hồ sơ của bạn'],
              ['notify_app_status', 'Cập nhật trạng thái đơn', 'Khi trạng thái đơn ứng tuyển thay đổi'],
              ['notify_job_alert', 'Job Alert hàng tuần', 'Tin mới phù hợp với kỹ năng của bạn (mỗi thứ Hai)'],
            ];
            @endphp

            <div class="flex-col gap-16">
              @foreach($toggles as [$field, $title, $desc])
              <div class="flex-between">
                <div>
                  <div class="fw-600 fs-13">{{ $title }}</div>
                  <div class="text-muted fs-12">{{ $desc }}</div>
                </div>
                @php $on = (bool)(auth()->user()->{$field} ?? true); @endphp
                <label style="position:relative;display:inline-block;width:44px;height:24px;cursor:pointer;flex-shrink:0">
                  <input type="checkbox" name="{{ $field }}" value="1" {{ $on ? 'checked' : '' }} style="opacity:0;width:0;height:0">
                  <span id="track-{{ $field }}" style="position:absolute;inset:0;background:{{ $on ? 'var(--primary)' : '#ccc' }};border-radius:24px;transition:.3s"></span>
                  <span style="position:absolute;top:2px;left:{{ $on ? '22px' : '2px' }};width:20px;height:20px;background:#fff;border-radius:50%;transition:.3s;box-shadow:0 1px 4px rgba(0,0,0,.2)" id="thumb-{{ $field }}"></span>
                </label>
              </div>
              @if(!$loop->last)<div class="divider"></div>@endif
              @endforeach
            </div>

            <div style="text-align:right;margin-top:20px">
              <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu cài đặt</button>
            </div>
          </form>
        </div>
      </div>

      {{-- Link to notifications page --}}
      <div class="card">
        <div class="card-body" style="padding:16px 20px;display:flex;align-items:center;justify-content:space-between">
          <div>
            <div class="fw-600 fs-13">Lịch sử thông báo</div>
            <div class="fs-12 text-muted">Xem tất cả thông báo đã nhận</div>
          </div>
          <a href="{{ route('notifications.index') }}?full=1" class="btn btn-outline btn-sm">
            <i class="fas fa-list"></i> Xem tất cả
          </a>
        </div>
      </div>
    </div>

  </div>{{-- end right --}}
</div>

@push('scripts')
<script>
function switchTab(id) {
  document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
  document.querySelectorAll('[id^="btn-tab-"]').forEach(b => {
    b.style.color = 'var(--text-muted)';
    b.style.borderBottomColor = 'transparent';
  });
  document.getElementById(id).style.display = 'flex';
  var btn = document.getElementById('btn-'+id);
  btn.style.color = 'var(--primary)';
  btn.style.borderBottomColor = 'var(--primary)';
}

// Init first tab
switchTab('tab-info');

// Auto-switch if errors are in password tab
@if($errors->has('current_password') || $errors->has('password'))
  switchTab('tab-security');
@endif

// Auto-switch if errors are in info/skills tab (employee/employer update)
@if($errors->hasAny(['name','about','profile_pic','company_name','company_logo','company_website','company_size','location']) && !$errors->hasAny(['current_password','password']))
  switchTab('tab-info');
@endif

@if($errors->hasAny(['skills','experience_years','desired_salary','job_type_pref']) && !$errors->hasAny(['current_password','password']))
  switchTab('tab-skills');
@endif

// Avatar preview
function previewAvatar(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      const container = input.closest('.form-group') || input.parentElement;
      // Try to find existing img preview first
      let imgEl = document.getElementById('avatar-preview');
      if (imgEl) {
        imgEl.src = e.target.result;
        return;
      }
      // If placeholder initials, replace with an img
      const placeholder = document.getElementById('avatar-preview-initials');
      if (placeholder) {
        const img = document.createElement('img');
        img.id = 'avatar-preview';
        img.className = placeholder.className.replace('avatar-placeholder','');
        img.style.cssText = 'border:2px solid var(--primary-light)';
        img.alt = '';
        img.src = e.target.result;
        placeholder.replaceWith(img);
      }
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// Skills tag input
function addSkillOnEnter(e) {
  if (e.key !== 'Enter' && e.key !== ',') return;
  e.preventDefault();
  const input = document.getElementById('skill-input');
  const val = input.value.trim().replace(/,$/,'');
  if (!val) return;
  addSkillTag(val);
  input.value = '';
}

function addSkillTag(skill) {
  const container = document.getElementById('skills-container');
  const input     = document.getElementById('skill-input');
  const span = document.createElement('span');
  span.className = 'tag tag-blue';
  span.style = 'display:inline-flex;align-items:center;gap:4px';
  span.dataset.skill = skill;

  // Use DOM methods instead of innerHTML to prevent XSS
  const skillText = document.createTextNode(skill);
  span.appendChild(skillText);

  const btn = document.createElement('button');
  btn.type = 'button';
  btn.setAttribute('onclick', 'removeSkill(this)');
  btn.style = 'background:none;border:none;cursor:pointer;padding:0;color:inherit';
  btn.textContent = '×';
  span.appendChild(btn);

  const hidden = document.createElement('input');
  hidden.type = 'hidden';
  hidden.name = 'skills[]';
  hidden.value = skill;
  span.appendChild(hidden);

  container.insertBefore(span, input);
}

function removeSkill(btn) {
  btn.closest('[data-skill]').remove();
}

// Notification toggle visual feedback
document.querySelectorAll('#tab-notif input[type=checkbox]').forEach(chk => {
  chk.addEventListener('change', function() {
    const field = this.name;
    const track = document.getElementById('track-' + field);
    const thumb = document.getElementById('thumb-' + field);
    if (!track || !thumb) return;
    track.style.background = this.checked ? 'var(--primary)' : '#ccc';
    thumb.style.left = this.checked ? '22px' : '2px';
  });
});
</script>
@endpush
@endsection
