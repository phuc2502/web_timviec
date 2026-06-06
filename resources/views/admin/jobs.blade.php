@extends('layouts.admin')
@section('title', 'Quản lý Tin tuyển dụng')

@section('content')
<div class="flex-between mb-20">
  <div>
    <h1 class="fs-18 fw-800" style="color:var(--secondary)">Quản lý Tin tuyển dụng</h1>
    <p class="text-muted fs-13 mt-2">Tổng số: <strong>{{ $listings->total() }}</strong> tin đang trong hệ thống</p>
  </div>

  {{-- Bộ lọc --}}
  <form action="{{ url('/admin/jobs') }}" method="GET">
    <div style="display:flex; gap:8px; align-items:center;">
      <input type="text" name="search" class="form-control" style="width:230px; font-size:13px;"
        placeholder="Tiêu đề, công ty, địa điểm..." value="{{ request('search') }}">

      <select name="job_type" class="form-control" style="width:140px; font-size:13px; cursor:pointer;">
        <option value="">Tất cả loại</option>
        <option value="full-time"  {{ request('job_type') === 'full-time'  ? 'selected' : '' }}>Full-time</option>
        <option value="part-time"  {{ request('job_type') === 'part-time'  ? 'selected' : '' }}>Part-time</option>
        <option value="remote"     {{ request('job_type') === 'remote'     ? 'selected' : '' }}>Remote</option>
        <option value="hybrid"     {{ request('job_type') === 'hybrid'     ? 'selected' : '' }}>Hybrid</option>
        <option value="freelance"  {{ request('job_type') === 'freelance'  ? 'selected' : '' }}>Freelance</option>
        <option value="internship" {{ request('job_type') === 'internship' ? 'selected' : '' }}>Internship</option>
      </select>

      <select name="status" class="form-control" style="width:140px; font-size:13px; cursor:pointer;">
        <option value="">Tất cả trạng thái</option>
        <option value="open"   {{ request('status') === 'open'   ? 'selected' : '' }}>🟢 Đang mở</option>
        <option value="hidden" {{ request('status') === 'hidden' ? 'selected' : '' }}>🔘 Tạm ẩn</option>
        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>🔴 Đã đóng</option>
      </select>

      <button type="submit" class="btn btn-primary btn-sm" style="padding:0 14px; height:38px;">
        <i class="fas fa-search"></i> Lọc
      </button>
      @if(request()->anyFilled(['search','job_type','status']))
        <a href="{{ url('/admin/jobs') }}" class="btn btn-light btn-sm" style="height:38px; padding:0 12px;">
          <i class="fas fa-times"></i> Xóa lọc
        </a>
      @endif
    </div>
  </form>
</div>

<div class="card shadow-sm" style="border-radius:var(--radius-lg); overflow:hidden;">
  <table class="table" style="vertical-align:middle;">
    <thead>
      <tr style="background:#f8fafc; border-bottom:1px solid var(--border);">
        <th style="width:50px;">ID</th>
        <th>Tiêu đề tin & Công ty</th>
        <th>Địa điểm</th>
        <th style="text-align:center;">Loại</th>
        <th style="text-align:center;">Hồ sơ</th>
        <th style="text-align:center;">Trạng thái</th>
        <th>Hạn nộp</th>
        <th style="text-align:center; width:120px;">Thao tác</th>
      </tr>
    </thead>
    <tbody>
      @forelse($listings as $job)
        <tr>
          <td class="text-muted fs-12 fw-700">#{{ $job->id }}</td>

          {{-- Tiêu đề & Công ty --}}
          <td>
            <a href="{{ url('/job/show/'.$job->slug) }}" target="_blank"
              style="display:block; font-size:13px; font-weight:700; color:var(--text-dark); text-decoration:none; white-space:nowrap; max-width:260px; overflow:hidden; text-overflow:ellipsis;"
              title="{{ $job->title }}"
              onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-dark)'">
              {{ $job->title }}
            </a>
            <div class="text-muted fs-11" style="margin-top:2px;">
              <i class="fas fa-building" style="margin-right:3px;"></i>
              {{ $job->user->company_name ?? $job->user->name ?? '—' }}
            </div>
          </td>

          {{-- Địa điểm --}}
          <td class="text-muted fs-12">
            <i class="fas fa-map-marker-alt" style="margin-right:3px; color:#94a3b8;"></i>
            {{ $job->address }}
          </td>

          {{-- Loại công việc --}}
          <td style="text-align:center;">
            @php
              $typeColors = [
                'full-time'  => ['bg' => '#eff6ff', 'color' => '#1d4ed8', 'label' => 'Full-time'],
                'part-time'  => ['bg' => '#f0fdf4', 'color' => '#16a34a', 'label' => 'Part-time'],
                'remote'     => ['bg' => '#f5f3ff', 'color' => '#7c3aed', 'label' => 'Remote'],
                'hybrid'     => ['bg' => '#fff7ed', 'color' => '#c2410c', 'label' => 'Hybrid'],
                'freelance'  => ['bg' => '#fef9c3', 'color' => '#854d0e', 'label' => 'Freelance'],
                'internship' => ['bg' => '#fce7f3', 'color' => '#9d174d', 'label' => 'Thực tập'],
              ];
              $tc = $typeColors[$job->job_type] ?? ['bg'=>'#f1f5f9','color'=>'#475569','label'=>$job->job_type];
            @endphp
            <span style="font-size:10px; font-weight:600; padding:2px 8px; border-radius:4px;
              background:{{ $tc['bg'] }}; color:{{ $tc['color'] }};">
              {{ $tc['label'] }}
            </span>
          </td>

          {{-- Số hồ sơ --}}
          <td style="text-align:center;">
            <span class="fw-700 fs-13" style="color:#f97316;">{{ $job->users->count() }}</span>
            <span class="text-muted fs-11"> hồ sơ</span>
          </td>

          {{-- Trạng thái --}}
          <td style="text-align:center;">
            @if($job->status === 'open')
              <span style="font-size:11px; font-weight:600; background:#ecfdf5; color:#10b981; border:1px solid #d1fae5; padding:2px 8px; border-radius:4px;">
                Đang mở
              </span>
            @elseif($job->status === 'hidden')
              <span style="font-size:11px; font-weight:600; background:#f1f5f9; color:#64748b; border:1px solid #e2e8f0; padding:2px 8px; border-radius:4px;">
                Tạm ẩn
              </span>
            @else
              <span style="font-size:11px; font-weight:600; background:#fef2f2; color:#ef4444; border:1px solid #fee2e2; padding:2px 8px; border-radius:4px;">
                Đã đóng
              </span>
            @endif
          </td>

          {{-- Hạn nộp --}}
          <td class="fs-12">
            @if($job->application_close_date)
              @php $isExpired = \Carbon\Carbon::parse($job->application_close_date)->isPast(); @endphp
              <span style="color:{{ $isExpired ? '#ef4444' : '#10b981' }}; font-weight:600;">
                {{ \Carbon\Carbon::parse($job->application_close_date)->format('d/m/Y') }}
              </span>
              @if($isExpired)
                <div class="text-muted fs-11">Đã hết hạn</div>
              @else
                <div class="text-muted fs-11">còn {{ \Carbon\Carbon::parse($job->application_close_date)->diffForHumans() }}</div>
              @endif
            @else
              <span class="text-muted fs-12">Không giới hạn</span>
            @endif
          </td>

          {{-- Thao tác --}}
          <td style="text-align:center;">
            <div style="display:flex; gap:6px; justify-content:center;">
              {{-- Nút xem chi tiết --}}
              <button onclick="openJobModal({{ $job->id }})" title="Xem chi tiết"
                style="border:none; background:#eff6ff; color:#3b82f6; border-radius:6px; padding:5px 10px; font-size:12px; cursor:pointer; font-weight:600; transition:all .15s;"
                onmouseover="this.style.background='#3b82f6';this.style.color='#fff';"
                onmouseout="this.style.background='#eff6ff';this.style.color='#3b82f6';">
                <i class="fas fa-eye"></i>
              </button>
              {{-- Nút xóa --}}
              <form action="{{ url('/admin/jobs/'.$job->id) }}" method="POST" style="margin:0;"
                onsubmit="return confirm('Xóa tin \"{{ addslashes($job->title) }}\"?\nKhông thể hoàn tác!')">
                @csrf @method('DELETE')
                <button type="submit" title="Xóa tin tuyển dụng"
                  style="border:none; background:#fef2f2; color:#ef4444; border-radius:6px; padding:5px 10px; font-size:12px; cursor:pointer; font-weight:600; transition:all .15s;"
                  onmouseover="this.style.background='#ef4444';this.style.color='#fff';"
                  onmouseout="this.style.background='#fef2f2';this.style.color='#ef4444';">
                  <i class="fas fa-trash-alt"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="8" class="text-center text-muted" style="padding:40px;">
            <i class="fas fa-briefcase" style="font-size:28px; opacity:.3; display:block; margin-bottom:8px;"></i>
            Không tìm thấy tin tuyển dụng nào thỏa mãn điều kiện.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>

  {{-- Phân trang --}}
  @if($listings->hasPages())
    <div class="card-footer" style="background:#f8fafc; border-top:1px solid var(--border);">
      <div class="flex-between">
        <span class="text-muted fs-13">
          Đang xem {{ $listings->firstItem() }}–{{ $listings->lastItem() }} trong tổng số {{ $listings->total() }} tin
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
</div>

{{-- ══════════════════════════════════════════════
     MODAL CHI TIẾT TIN TUYỂN DỤNG
══════════════════════════════════════════════ --}}
<div id="jobModal" onclick="if(event.target===this)closeJobModal()"
  style="display:none; position:fixed; inset:0; background:rgba(15,23,42,.55); backdrop-filter:blur(4px);
         z-index:9999; align-items:center; justify-content:center; padding:20px;">
  <div style="background:#fff; border-radius:16px; width:100%; max-width:640px; max-height:90vh;
              overflow-y:auto; box-shadow:0 24px 60px rgba(0,0,0,.25); animation:jobModalIn .2s ease;">

    {{-- Header --}}
    <div style="padding:24px; border-bottom:1px solid #f1f5f9; display:flex; align-items:flex-start; gap:16px; position:relative;">
      <div id="jIcon" style="width:52px; height:52px; border-radius:12px; background:#eff6ff; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
        <i class="fas fa-briefcase" style="font-size:22px; color:#3b82f6;"></i>
      </div>
      <div style="flex:1; min-width:0;">
        <div id="jTitle" style="font-size:17px; font-weight:800; color:#0f172a; margin-bottom:4px; line-height:1.3;"></div>
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
          <span id="jCompany" style="font-size:13px; color:#64748b;"></span>
          <span style="color:#cbd5e1;">•</span>
          <span id="jAddress" style="font-size:13px; color:#64748b;"></span>
        </div>
        <div style="margin-top:8px; display:flex; gap:6px; flex-wrap:wrap;">
          <span id="jTypeBadge" style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px;"></span>
          <span id="jStatusBadge" style="font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px;"></span>
        </div>
      </div>
      <button onclick="closeJobModal()"
        style="position:absolute; top:16px; right:16px; border:none; background:#f1f5f9; border-radius:50%; width:32px; height:32px;
               cursor:pointer; font-size:16px; color:#64748b; display:flex; align-items:center; justify-content:center; flex-shrink:0;"
        onmouseover="this.style.background='#e2e8f0';" onmouseout="this.style.background='#f1f5f9';">
        <i class="fas fa-times"></i>
      </button>
    </div>

    {{-- Body --}}
    <div style="padding:24px;">
      {{-- Loading --}}
      <div id="jLoading" style="text-align:center; padding:40px; color:#94a3b8;">
        <i class="fas fa-circle-notch fa-spin" style="font-size:28px; margin-bottom:8px; display:block;"></i>
        Đang tải...
      </div>

      {{-- Content --}}
      <div id="jContent" style="display:none;">
        {{-- KPI cards --}}
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:20px;">
          <div style="background:#f8fafc; border-radius:10px; padding:12px; text-align:center;">
            <div id="jApplicants" style="font-size:20px; font-weight:800; color:#f97316;"></div>
            <div style="font-size:11px; color:#64748b; margin-top:2px;">Hồ sơ</div>
          </div>
          <div style="background:#f8fafc; border-radius:10px; padding:12px; text-align:center;">
            <div id="jSalary" style="font-size:13px; font-weight:700; color:#10b981;"></div>
            <div style="font-size:11px; color:#64748b; margin-top:2px;">Mức lương</div>
          </div>
          <div style="background:#f8fafc; border-radius:10px; padding:12px; text-align:center;">
            <div id="jDeadline" style="font-size:13px; font-weight:700; color:#0f172a;"></div>
            <div style="font-size:11px; color:#64748b; margin-top:2px;">Hạn nộp</div>
          </div>
          <div style="background:#f8fafc; border-radius:10px; padding:12px; text-align:center;">
            <div id="jPostedAt" style="font-size:13px; font-weight:700; color:#0f172a;"></div>
            <div style="font-size:11px; color:#64748b; margin-top:2px;">Ngày đăng</div>
          </div>
        </div>

        {{-- Mô tả công việc --}}
        <div id="jPredesWrap" style="margin-bottom:14px;">
          <div style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px;">Tóm tắt</div>
          <p id="jPredes" style="font-size:13px; color:#334155; margin:0; line-height:1.7; background:#f8fafc; border-radius:8px; padding:12px;"></p>
        </div>
        <div id="jDescWrap" style="margin-bottom:14px;">
          <div style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px;">Mô tả công việc</div>
          <div id="jDesc" style="font-size:13px; color:#334155; line-height:1.7; background:#f8fafc; border-radius:8px; padding:12px; max-height:160px; overflow-y:auto;"></div>
        </div>
        <div id="jReqWrap" style="margin-bottom:14px;">
          <div style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px;">Yêu cầu</div>
          <div id="jReq" style="font-size:13px; color:#334155; line-height:1.7; background:#f8fafc; border-radius:8px; padding:12px; max-height:120px; overflow-y:auto;"></div>
        </div>

        {{-- Thông tin nhà tuyển dụng --}}
        <div style="border-top:1px solid #f1f5f9; padding-top:16px; margin-top:4px;">
          <div style="font-size:12px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; margin-bottom:10px;">Nhà tuyển dụng</div>
          <div style="display:flex; align-items:center; gap:12px; background:#f8fafc; border-radius:10px; padding:14px;">
            <img id="jEmpAvatar" src="" alt="" style="width:44px; height:44px; border-radius:50%; object-fit:cover;">
            <div>
              <div id="jEmpName" style="font-size:14px; font-weight:700; color:#0f172a;"></div>
              <div id="jEmpEmail" style="font-size:12px; color:#64748b;"></div>
            </div>
            <a id="jEmpLink" href="" target="_blank"
              style="margin-left:auto; font-size:12px; background:#eff6ff; color:#3b82f6; padding:6px 12px; border-radius:6px; text-decoration:none; font-weight:600;"
              onmouseover="this.style.background='#3b82f6';this.style.color='#fff';"
              onmouseout="this.style.background='#eff6ff';this.style.color='#3b82f6';">
              <i class="fas fa-external-link-alt"></i> Xem tin
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
@keyframes jobModalIn { from { opacity:0; transform:scale(.95) translateY(10px); } to { opacity:1; transform:scale(1) translateY(0); } }
</style>

@push('scripts')
<script>
const JOB_DETAIL_URL = '{{ url("/admin/jobs") }}';

function openJobModal(id) {
  const modal = document.getElementById('jobModal');
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';

  document.getElementById('jLoading').style.display = 'block';
  document.getElementById('jContent').style.display = 'none';
  document.getElementById('jTitle').textContent = '';
  document.getElementById('jCompany').textContent = '';

  fetch(`${JOB_DETAIL_URL}/${id}/detail`, {
    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
  })
  .then(r => r.json())
  .then(j => {
    // Header
    document.getElementById('jTitle').textContent   = j.title;
    document.getElementById('jCompany').textContent = j.employer.company_name;
    document.getElementById('jAddress').textContent = j.address || '—';

    // Type badge
    const typeMap = {
      'full-time':  ['Full-time',  '#eff6ff','#1d4ed8'],
      'part-time':  ['Part-time',  '#f0fdf4','#16a34a'],
      'remote':     ['Remote',     '#f5f3ff','#7c3aed'],
      'hybrid':     ['Hybrid',     '#fff7ed','#c2410c'],
      'freelance':  ['Freelance',  '#fef9c3','#854d0e'],
      'internship': ['Thực tập',   '#fce7f3','#9d174d'],
    };
    const [tLabel, tBg, tColor] = typeMap[j.job_type] || [j.job_type,'#f1f5f9','#475569'];
    const tBadge = document.getElementById('jTypeBadge');
    tBadge.textContent = tLabel; tBadge.style.background = tBg; tBadge.style.color = tColor;

    // Status badge
    const sBadge = document.getElementById('jStatusBadge');
    const statusMap = {
      open:   ['🟢 Đang mở', '#ecfdf5', '#10b981'],
      hidden: ['🔘 Tạm ẩn',  '#f1f5f9', '#64748b'],
      closed: ['🔴 Đã đóng', '#fef2f2', '#ef4444'],
    };
    const [sLabel, sBg, sColor] = statusMap[j.status] || [j.status,'#f1f5f9','#475569'];
    sBadge.textContent = sLabel; sBadge.style.background = sBg; sBadge.style.color = sColor;

    // KPI
    document.getElementById('jApplicants').textContent = j.applicants_count;
    document.getElementById('jSalary').textContent     = j.salary || 'Thương lượng';
    document.getElementById('jDeadline').textContent   = j.application_close_date || 'Không giới hạn';
    document.getElementById('jPostedAt').textContent   = j.created_at.split(' ')[0];

    // Text blocks
    const setBlock = (wrapId, elId, html) => {
      const el = document.getElementById(elId);
      if (html) { el.innerHTML = html; document.getElementById(wrapId).style.display='block'; }
      else { document.getElementById(wrapId).style.display='none'; }
    };
    setBlock('jPredesWrap', 'jPredes', j.predes);
    setBlock('jDescWrap',   'jDesc',   j.description);
    setBlock('jReqWrap',    'jReq',    j.requirements);

    // Employer
    document.getElementById('jEmpAvatar').src        = j.employer.avatar_url;
    document.getElementById('jEmpName').textContent  = j.employer.company_name;
    document.getElementById('jEmpEmail').textContent = j.employer.email;
    document.getElementById('jEmpLink').href         = `/job/show/${j.slug}`;

    document.getElementById('jLoading').style.display = 'none';
    document.getElementById('jContent').style.display = 'block';
  })
  .catch(() => {
    document.getElementById('jLoading').innerHTML = '<i class="fas fa-exclamation-circle" style="color:#ef4444;font-size:24px;display:block;margin-bottom:8px;"></i>Không thể tải dữ liệu.';
  });
}

function closeJobModal() {
  document.getElementById('jobModal').style.display = 'none';
  document.body.style.overflow = '';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeJobModal(); });
</script>
@endpush
@endsection
