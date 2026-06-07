@extends('layouts.admin')
@section('title', 'Duyệt Tin Tuyển Dụng')

@section('content')

{{-- ══ Header ══════════════════════════════════════════════════════════════ --}}
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-18 fw-800" style="color:var(--secondary)">
      <i class="fas fa-clipboard-check" style="color:#f59e0b; margin-right:8px;"></i>
      Duyệt Tin Tuyển Dụng
    </h1>
    <p class="text-muted fs-13 mt-2">
      Có <strong style="color:#ef4444;">{{ $pendingCount }}</strong> tin đang chờ duyệt trong hệ thống
    </p>
  </div>

  {{-- Bộ lọc --}}
  <form action="{{ url('/admin/jobs/pending') }}" method="GET">
    <div style="display:flex; gap:8px; align-items:center;">
      <input type="text" name="search" class="form-control" style="width:220px; font-size:13px;"
        placeholder="Tiêu đề, công ty, địa điểm..." value="{{ request('search') }}">

      <select name="job_type" class="form-control" style="width:140px; font-size:13px; cursor:pointer;">
        <option value="">Tất cả loại</option>
        <option value="full-time"  {{ request('job_type') === 'full-time'  ? 'selected' : '' }}>Full-time</option>
        <option value="part-time"  {{ request('job_type') === 'part-time'  ? 'selected' : '' }}>Part-time</option>
        <option value="remote"     {{ request('job_type') === 'remote'     ? 'selected' : '' }}>Remote</option>
        <option value="hybrid"     {{ request('job_type') === 'hybrid'     ? 'selected' : '' }}>Hybrid</option>
        <option value="freelance"  {{ request('job_type') === 'freelance'  ? 'selected' : '' }}>Freelance</option>
        <option value="internship" {{ request('job_type') === 'internship' ? 'selected' : '' }}>Thực tập</option>
      </select>

      <button type="submit" class="btn btn-primary btn-sm" style="padding:0 14px; height:38px;">
        <i class="fas fa-search"></i> Lọc
      </button>
      @if(request()->anyFilled(['search','job_type']))
        <a href="{{ url('/admin/jobs/pending') }}" class="btn btn-light btn-sm" style="height:38px; padding:0 12px;">
          <i class="fas fa-times"></i>
        </a>
      @endif
    </div>
  </form>
</div>

{{-- ══ Flash messages ══════════════════════════════════════════════════════ --}}
@if(session('success'))
  <div style="background:#ecfdf5; border:1px solid #a7f3d0; border-radius:10px; padding:14px 18px; margin-bottom:18px; display:flex; align-items:center; gap:10px; font-size:13px; color:#065f46;">
    <i class="fas fa-check-circle" style="font-size:16px; color:#10b981;"></i>
    {{ session('success') }}
  </div>
@endif
@if(session('error'))
  <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:14px 18px; margin-bottom:18px; display:flex; align-items:center; gap:10px; font-size:13px; color:#991b1b;">
    <i class="fas fa-exclamation-circle" style="font-size:16px; color:#ef4444;"></i>
    {{ session('error') }}
  </div>
@endif

{{-- ══ Danh sách tin chờ duyệt ════════════════════════════════════════════ --}}
@forelse($listings as $job)
  <div class="pending-card" id="card-{{ $job->id }}"
    style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:20px 24px; margin-bottom:14px;
           box-shadow:0 1px 4px rgba(0,0,0,.05); transition:box-shadow .2s;"
    onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,.1)'"
    onmouseout="this.style.boxShadow='0 1px 4px rgba(0,0,0,.05)'">

    <div style="display:flex; gap:18px; align-items:flex-start;">

      {{-- Icon công ty --}}
      <div style="width:48px; height:48px; border-radius:12px; background:#eff6ff; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
        <i class="fas fa-briefcase" style="font-size:20px; color:#3b82f6;"></i>
      </div>

      {{-- Thông tin chính --}}
      <div style="flex:1; min-width:0;">
        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap;">
          <div>
            <div style="font-size:15px; font-weight:800; color:#0f172a; margin-bottom:4px;">
              {{ $job->title }}
            </div>
            <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:8px;">
              <span style="font-size:12px; color:#64748b;">
                <i class="fas fa-building" style="margin-right:4px; color:#94a3b8;"></i>
                {{ $job->user->company_name ?? $job->user->name ?? '—' }}
              </span>
              <span style="font-size:12px; color:#64748b;">
                <i class="fas fa-map-marker-alt" style="margin-right:4px; color:#94a3b8;"></i>
                {{ $job->address ?? '—' }}
              </span>
              <span style="font-size:12px; color:#64748b;">
                <i class="fas fa-envelope" style="margin-right:4px; color:#94a3b8;"></i>
                {{ $job->user->email ?? '—' }}
              </span>
              @if($job->salary)
                <span style="font-size:12px; color:#10b981; font-weight:600;">
                  <i class="fas fa-dollar-sign" style="margin-right:3px;"></i>
                  {{ $job->salary }}
                </span>
              @endif
            </div>

            {{-- Badges --}}
            <div style="display:flex; gap:6px; flex-wrap:wrap;">
              @php
                $typeColors = [
                  'full-time'  => ['bg'=>'#eff6ff','color'=>'#1d4ed8','label'=>'Full-time'],
                  'part-time'  => ['bg'=>'#f0fdf4','color'=>'#16a34a','label'=>'Part-time'],
                  'remote'     => ['bg'=>'#f5f3ff','color'=>'#7c3aed','label'=>'Remote'],
                  'hybrid'     => ['bg'=>'#fff7ed','color'=>'#c2410c','label'=>'Hybrid'],
                  'freelance'  => ['bg'=>'#fef9c3','color'=>'#854d0e','label'=>'Freelance'],
                  'internship' => ['bg'=>'#fce7f3','color'=>'#9d174d','label'=>'Thực tập'],
                ];
                $tc = $typeColors[$job->job_type] ?? ['bg'=>'#f1f5f9','color'=>'#475569','label'=>$job->job_type];
              @endphp
              <span style="font-size:10px; font-weight:700; padding:2px 9px; border-radius:5px; background:{{ $tc['bg'] }}; color:{{ $tc['color'] }};">
                {{ $tc['label'] }}
              </span>
              <span style="font-size:10px; font-weight:700; padding:2px 9px; border-radius:5px; background:#fffbeb; color:#d97706; border:1px solid #fef3c7;">
                🟡 Chờ duyệt
              </span>
              @if($job->application_close_date)
                @php $expired = \Carbon\Carbon::parse($job->application_close_date)->isPast(); @endphp
                <span style="font-size:10px; font-weight:600; padding:2px 9px; border-radius:5px; background:{{ $expired ? '#fef2f2' : '#f0fdf4' }}; color:{{ $expired ? '#ef4444' : '#16a34a' }};">
                  <i class="fas fa-calendar" style="margin-right:3px;"></i>
                  Hạn: {{ \Carbon\Carbon::parse($job->application_close_date)->format('d/m/Y') }}
                  {{ $expired ? '(Hết hạn)' : '' }}
                </span>
              @endif
              <span style="font-size:10px; color:#94a3b8; padding:2px 0;">
                Đăng {{ $job->created_at->diffForHumans() }}
              </span>
            </div>
          </div>

          {{-- Nút xem preview --}}
          <button onclick="openPreviewModal({{ $job->id }})"
            style="border:1px solid #e2e8f0; background:#f8fafc; color:#64748b; border-radius:8px; padding:7px 14px; font-size:12px; cursor:pointer; font-weight:600; white-space:nowrap; flex-shrink:0; transition:all .15s;"
            onmouseover="this.style.background='#e2e8f0';" onmouseout="this.style.background='#f8fafc';">
            <i class="fas fa-eye" style="margin-right:5px;"></i> Xem chi tiết
          </button>
        </div>

        {{-- Tóm tắt nội dung --}}
        @if($job->predes)
          <div style="margin-top:12px; padding:10px 14px; background:#f8fafc; border-radius:8px; font-size:12px; color:#475569; line-height:1.6; border-left:3px solid #cbd5e1;">
            {{ \Illuminate\Support\Str::limit(strip_tags($job->predes), 180) }}
          </div>
        @endif

        {{-- Nút hành động --}}
        <div style="display:flex; gap:10px; margin-top:14px; align-items:center; flex-wrap:wrap;">

          {{-- Duyệt --}}
          <form action="{{ url('/admin/jobs/'.$job->id.'/approve') }}" method="POST" style="margin:0;">
            @csrf
            <button type="submit"
              style="background:#10b981; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:7px; transition:all .15s;"
              onmouseover="this.style.background='#059669';" onmouseout="this.style.background='#10b981';">
              <i class="fas fa-check-circle"></i> Duyệt tin
            </button>
          </form>

          {{-- Từ chối (mở popup lý do) --}}
          <button onclick="openRejectModal({{ $job->id }}, '{{ addslashes($job->title) }}')"
            style="background:#fef2f2; color:#ef4444; border:1px solid #fecaca; border-radius:8px; padding:9px 20px; font-size:13px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:7px; transition:all .15s;"
            onmouseover="this.style.background='#ef4444';this.style.color='#fff';this.style.borderColor='#ef4444';"
            onmouseout="this.style.background='#fef2f2';this.style.color='#ef4444';this.style.borderColor='#fecaca';">
            <i class="fas fa-times-circle"></i> Từ chối
          </button>

          {{-- Xóa --}}
          <form action="{{ url('/admin/jobs/'.$job->id) }}" method="POST" style="margin:0;"
            onsubmit="return confirm('Xóa vĩnh viễn tin \"{{ addslashes($job->title) }}\"?\nKhông thể hoàn tác!')">
            @csrf @method('DELETE')
            <button type="submit"
              style="background:transparent; color:#94a3b8; border:1px solid #e2e8f0; border-radius:8px; padding:9px 14px; font-size:12px; cursor:pointer; transition:all .15s;"
              onmouseover="this.style.color='#ef4444';this.style.borderColor='#fca5a5';"
              onmouseout="this.style.color='#94a3b8';this.style.borderColor='#e2e8f0';">
              <i class="fas fa-trash"></i>
            </button>
          </form>

          <span style="font-size:11px; color:#94a3b8; margin-left:4px;">
            ID #{{ $job->id }}
          </span>
        </div>
      </div>
    </div>
  </div>
@empty
  <div style="background:#fff; border:1px dashed #e2e8f0; border-radius:14px; padding:60px; text-align:center;">
    <i class="fas fa-clipboard-check" style="font-size:40px; color:#d1fae5; display:block; margin-bottom:12px;"></i>
    <div style="font-size:16px; font-weight:700; color:#0f172a; margin-bottom:6px;">Không có tin nào chờ duyệt!</div>
    <p style="font-size:13px; color:#94a3b8; margin:0;">Tất cả tin tuyển dụng đã được xử lý. Bạn có thể xem toàn bộ tin tại
      <a href="{{ url('/admin/jobs') }}" style="color:var(--primary); font-weight:600;">Quản lý tin tuyển dụng</a>.
    </p>
  </div>
@endforelse

{{-- ══ Phân trang ══════════════════════════════════════════════════════════ --}}
@if($listings->hasPages())
  <div class="card-footer" style="background:#fff; border:1px solid var(--border); border-radius:10px; padding:14px 20px; margin-top:8px;">
    <div class="flex-between">
      <span class="text-muted fs-13">
        Đang xem {{ $listings->firstItem() }}–{{ $listings->lastItem() }} trong {{ $listings->total() }} tin chờ duyệt
      </span>
      <div class="pagination">
        @if(!$listings->onFirstPage())
          <a href="{{ $listings->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>
        @endif
        @foreach($listings->getUrlRange(max(1,$listings->currentPage()-2), min($listings->lastPage(),$listings->currentPage()+2)) as $page => $url)
          @if($page == $listings->currentPage())
            <span class="active" style="background:var(--primary); color:white;">{{ $page }}</span>
          @else
            <a href="{{ $url }}">{{ $page }}</a>
          @endif
        @endforeach
        @if($listings->hasMorePages())
          <a href="{{ $listings->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>
        @endif
      </div>
    </div>
  </div>
@endif


{{-- ══════════════════════════════════════════════
     MODAL TỪ CHỐI (nhập lý do)
══════════════════════════════════════════════ --}}
<div id="rejectModal" onclick="if(event.target===this)closeRejectModal()"
  style="display:none; position:fixed; inset:0; background:rgba(15,23,42,.55); backdrop-filter:blur(4px);
         z-index:9999; align-items:center; justify-content:center; padding:20px;">
  <div style="background:#fff; border-radius:16px; width:100%; max-width:480px;
              box-shadow:0 24px 60px rgba(0,0,0,.25); animation:modalIn .2s ease; overflow:hidden;">

    <div style="padding:20px 24px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:12px;">
      <div style="width:40px; height:40px; border-radius:10px; background:#fef2f2; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
        <i class="fas fa-times-circle" style="color:#ef4444; font-size:18px;"></i>
      </div>
      <div>
        <div style="font-size:15px; font-weight:800; color:#0f172a;">Từ chối tin tuyển dụng</div>
        <div id="rejectJobTitle" style="font-size:12px; color:#64748b; margin-top:2px;"></div>
      </div>
      <button onclick="closeRejectModal()"
        style="margin-left:auto; border:none; background:#f1f5f9; border-radius:50%; width:30px; height:30px; cursor:pointer; font-size:14px; color:#64748b; display:flex; align-items:center; justify-content:center;"
        onmouseover="this.style.background='#e2e8f0';" onmouseout="this.style.background='#f1f5f9';">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <form id="rejectForm" method="POST" style="padding:20px 24px;">
      @csrf
      <div style="margin-bottom:16px;">
        <label style="font-size:13px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">
          Lý do từ chối <span style="color:#94a3b8; font-weight:400;">(không bắt buộc)</span>
        </label>
        <textarea name="reason" rows="4"
          style="width:100%; border:1px solid #e2e8f0; border-radius:8px; padding:10px 12px; font-size:13px; color:#334155; resize:vertical; outline:none; font-family:inherit; transition:border-color .15s; box-sizing:border-box;"
          placeholder="VD: Nội dung không phù hợp, thiếu thông tin, vi phạm chính sách..."
          onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#e2e8f0'"></textarea>
      </div>
      <div style="background:#fffbeb; border:1px solid #fef3c7; border-radius:8px; padding:10px 14px; font-size:12px; color:#92400e; margin-bottom:20px;">
        <i class="fas fa-info-circle" style="margin-right:5px;"></i>
        Tin sẽ bị ẩn khỏi hệ thống. Nhà tuyển dụng vẫn có thể chỉnh sửa và gửi lại.
      </div>
      <div style="display:flex; gap:10px; justify-content:flex-end;">
        <button type="button" onclick="closeRejectModal()"
          style="border:1px solid #e2e8f0; background:#fff; color:#64748b; border-radius:8px; padding:9px 20px; font-size:13px; font-weight:600; cursor:pointer; transition:all .15s;"
          onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='#fff';">
          Hủy
        </button>
        <button type="submit"
          style="background:#ef4444; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:7px; transition:all .15s;"
          onmouseover="this.style.background='#dc2626';" onmouseout="this.style.background='#ef4444';">
          <i class="fas fa-times-circle"></i> Xác nhận từ chối
        </button>
      </div>
    </form>
  </div>
</div>

{{-- ══════════════════════════════════════════════
     MODAL XEM CHI TIẾT (dùng lại API /admin/jobs/{id}/detail)
══════════════════════════════════════════════ --}}
<div id="previewModal" onclick="if(event.target===this)closePreviewModal()"
  style="display:none; position:fixed; inset:0; background:rgba(15,23,42,.55); backdrop-filter:blur(4px);
         z-index:9998; align-items:center; justify-content:center; padding:20px;">
  <div style="background:#fff; border-radius:16px; width:100%; max-width:660px; max-height:88vh;
              overflow-y:auto; box-shadow:0 24px 60px rgba(0,0,0,.25); animation:modalIn .2s ease;">

    <div style="padding:20px 24px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:12px; position:sticky; top:0; background:#fff; z-index:1;">
      <div style="width:44px; height:44px; border-radius:12px; background:#eff6ff; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
        <i class="fas fa-file-alt" style="color:#3b82f6; font-size:18px;"></i>
      </div>
      <div style="flex:1; min-width:0;">
        <div id="pvTitle" style="font-size:15px; font-weight:800; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"></div>
        <div id="pvCompany" style="font-size:12px; color:#64748b;"></div>
      </div>
      <button onclick="closePreviewModal()"
        style="border:none; background:#f1f5f9; border-radius:50%; width:32px; height:32px; cursor:pointer; font-size:14px; color:#64748b; display:flex; align-items:center; justify-content:center; flex-shrink:0;"
        onmouseover="this.style.background='#e2e8f0';" onmouseout="this.style.background='#f1f5f9';">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <div style="padding:20px 24px;">
      <div id="pvLoading" style="text-align:center; padding:40px; color:#94a3b8;">
        <i class="fas fa-circle-notch fa-spin" style="font-size:28px; display:block; margin-bottom:8px;"></i> Đang tải...
      </div>
      <div id="pvContent" style="display:none;">
        <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:10px; margin-bottom:18px;">
          <div style="background:#f8fafc; border-radius:10px; padding:12px;">
            <div style="font-size:11px; color:#94a3b8; margin-bottom:4px;">Mức lương</div>
            <div id="pvSalary" style="font-size:14px; font-weight:700; color:#10b981;"></div>
          </div>
          <div style="background:#f8fafc; border-radius:10px; padding:12px;">
            <div style="font-size:11px; color:#94a3b8; margin-bottom:4px;">Hạn nộp</div>
            <div id="pvDeadline" style="font-size:14px; font-weight:700; color:#0f172a;"></div>
          </div>
        </div>

        <div id="pvPredesWrap" style="margin-bottom:14px;">
          <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px;">Tóm tắt</div>
          <div id="pvPredes" style="font-size:13px; color:#334155; line-height:1.7; background:#f8fafc; border-radius:8px; padding:12px;"></div>
        </div>
        <div id="pvDescWrap" style="margin-bottom:14px;">
          <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px;">Mô tả công việc</div>
          <div id="pvDesc" style="font-size:13px; color:#334155; line-height:1.7; background:#f8fafc; border-radius:8px; padding:12px; max-height:180px; overflow-y:auto;"></div>
        </div>
        <div id="pvReqWrap" style="margin-bottom:14px;">
          <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px;">Yêu cầu</div>
          <div id="pvReq" style="font-size:13px; color:#334155; line-height:1.7; background:#f8fafc; border-radius:8px; padding:12px; max-height:140px; overflow-y:auto;"></div>
        </div>

        {{-- Nút hành động trong modal --}}
        <div id="pvActions" style="border-top:1px solid #f1f5f9; padding-top:16px; display:flex; gap:10px; justify-content:flex-end;">
          <button id="pvRejectBtn"
            style="background:#fef2f2; color:#ef4444; border:1px solid #fecaca; border-radius:8px; padding:9px 20px; font-size:13px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:7px;"
            onmouseover="this.style.background='#ef4444';this.style.color='#fff';this.style.borderColor='#ef4444';"
            onmouseout="this.style.background='#fef2f2';this.style.color='#ef4444';this.style.borderColor='#fecaca';">
            <i class="fas fa-times-circle"></i> Từ chối
          </button>
          <form id="pvApproveForm" method="POST" style="margin:0;">
            @csrf
            <button type="submit"
              style="background:#10b981; color:#fff; border:none; border-radius:8px; padding:9px 20px; font-size:13px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:7px; transition:all .15s;"
              onmouseover="this.style.background='#059669';" onmouseout="this.style.background='#10b981';">
              <i class="fas fa-check-circle"></i> Duyệt tin ngay
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
@keyframes modalIn { from { opacity:0; transform:scale(.95) translateY(8px); } to { opacity:1; transform:scale(1) translateY(0); } }
</style>

@push('scripts')
<script>
const ADMIN_JOBS_URL = '{{ url("/admin/jobs") }}';
let _currentJobId = null;
let _currentJobTitle = '';

// ── Reject Modal ──────────────────────────────────────────────────────────
function openRejectModal(id, title) {
  closePreviewModal();
  _currentJobId    = id;
  _currentJobTitle = title;
  document.getElementById('rejectJobTitle').textContent = title;
  document.getElementById('rejectForm').action = `${ADMIN_JOBS_URL}/${id}/reject`;
  document.getElementById('rejectForm').querySelector('textarea').value = '';
  const modal = document.getElementById('rejectModal');
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
  setTimeout(() => document.getElementById('rejectForm').querySelector('textarea').focus(), 200);
}

function closeRejectModal() {
  document.getElementById('rejectModal').style.display = 'none';
  document.body.style.overflow = '';
}

// ── Preview Modal ─────────────────────────────────────────────────────────
function openPreviewModal(id) {
  const modal = document.getElementById('previewModal');
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';

  document.getElementById('pvLoading').style.display = 'block';
  document.getElementById('pvContent').style.display = 'none';
  document.getElementById('pvTitle').textContent   = '';
  document.getElementById('pvCompany').textContent = '';

  fetch(`${ADMIN_JOBS_URL}/${id}/detail`, {
    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
  })
  .then(r => r.json())
  .then(j => {
    document.getElementById('pvTitle').textContent   = j.title;
    document.getElementById('pvCompany').textContent = j.employer.company_name + ' • ' + (j.address || '');
    document.getElementById('pvSalary').textContent  = j.salary || 'Thương lượng';
    document.getElementById('pvDeadline').textContent = j.application_close_date || 'Không giới hạn';

    const setBlock = (wId, eId, html) => {
      if (html) {
        document.getElementById(eId).innerHTML = html;
        document.getElementById(wId).style.display = 'block';
      } else {
        document.getElementById(wId).style.display = 'none';
      }
    };
    setBlock('pvPredesWrap', 'pvPredes', j.predes);
    setBlock('pvDescWrap',   'pvDesc',   j.description);
    setBlock('pvReqWrap',    'pvReq',    j.requirements);

    // Gán action cho nút trong modal
    document.getElementById('pvApproveForm').action = `${ADMIN_JOBS_URL}/${j.id}/approve`;
    document.getElementById('pvRejectBtn').onclick = () => {
      openRejectModal(j.id, j.title);
    };

    document.getElementById('pvLoading').style.display = 'none';
    document.getElementById('pvContent').style.display = 'block';
  })
  .catch(() => {
    document.getElementById('pvLoading').innerHTML =
      '<i class="fas fa-exclamation-circle" style="color:#ef4444;font-size:24px;display:block;margin-bottom:8px;"></i>Không thể tải dữ liệu.';
  });
}

function closePreviewModal() {
  document.getElementById('previewModal').style.display = 'none';
  document.body.style.overflow = '';
}

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') { closeRejectModal(); closePreviewModal(); }
});
</script>
@endpush
@endsection
