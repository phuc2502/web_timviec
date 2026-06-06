@extends('layouts.app')
@section('title', 'Tìm việc làm IT — ITWorks')
@section('description', 'Hàng nghìn việc làm IT đang chờ bạn. Lập trình viên, DevOps, Data, AI...')

@section('content')

{{-- HERO --}}
<section class="hero">
  <div class="container">
    <div style="max-width:680px;position:relative;z-index:1">
      <div class="hero-pill">
        <span class="dot"></span>
        {{ $totalJobs ?? '2,500+' }} việc làm đang tuyển dụng ngay
      </div>
      <h1>Nền tảng tuyển dụng IT<br><span>hàng đầu Việt Nam</span></h1>
      <p>Kết nối <strong>{{ $totalJobs ?? '2,500+' }}</strong> việc làm IT với hàng nghìn kỹ sư công nghệ xuất sắc trên cả nước</p>

      <form action="{{ url('/job') }}" method="GET">
        <div class="search-bar">
          <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm việc: Backend, Frontend, DevOps...">
          <div class="search-bar__divider"></div>
          <select name="address">
            <option value="">📍 Tất cả địa điểm</option>
            <option value="Hà Nội" {{ request('address') == 'Hà Nội' ? 'selected' : '' }}>Hà Nội</option>
            <option value="Hồ Chí Minh" {{ request('address') == 'Hồ Chí Minh' ? 'selected' : '' }}>Hồ Chí Minh</option>
            <option value="Đà Nẵng" {{ request('address') == 'Đà Nẵng' ? 'selected' : '' }}>Đà Nẵng</option>
            <option value="Remote" {{ request('address') == 'Remote' ? 'selected' : '' }}>Remote</option>
          </select>
          <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tìm kiếm</button>
        </div>
      </form>

      <div class="hero-stats">
        <div class="hero-stats__item">
          <div class="hero-stats__num">{{ $totalJobs ?? '2.5K' }}+</div>
          <div class="hero-stats__label">Việc làm</div>
        </div>
        <div class="hero-stats__item">
          <div class="hero-stats__num">500+</div>
          <div class="hero-stats__label">Công ty</div>
        </div>
        <div class="hero-stats__item">
          <div class="hero-stats__num">10K+</div>
          <div class="hero-stats__label">Ứng viên</div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- CATEGORY BAR --}}
<div class="category-bar">
  <div class="category-bar__inner container">
    @foreach([
      ['label'=>'Tất cả','icon'=>'fa-th'],
      ['label'=>'Backend','icon'=>'fa-server'],
      ['label'=>'Frontend','icon'=>'fa-code'],
      ['label'=>'Mobile','icon'=>'fa-mobile-alt'],
      ['label'=>'DevOps','icon'=>'fa-cogs'],
      ['label'=>'Data / AI','icon'=>'fa-brain'],
      ['label'=>'QA/Tester','icon'=>'fa-bug'],
      ['label'=>'UI/UX','icon'=>'fa-palette'],
      ['label'=>'Blockchain','icon'=>'fa-link'],
      ['label'=>'Game','icon'=>'fa-gamepad'],
    ] as $cat)
      @php $isAll = $cat['label']==='Tất cả'; $val = $isAll ? '' : $cat['label']; @endphp
      <a href="{{ url('/job?search='.urlencode($val)) }}" class="category-chip {{ ($isAll && !request('search')) || request('search')==$val ? 'active' : '' }}">
        <i class="fas {{ $cat['icon'] }}" style="font-size:12px"></i> {{ $cat['label'] }}
      </a>
    @endforeach
  </div>
</div>

{{-- MAIN CONTENT --}}
<div class="container section">
  <div class="flex gap-24" style="align-items:flex-start">

    {{-- SIDEBAR --}}
    <aside class="sidebar" id="sidebar" style="display:none">
      <form action="{{ url('/job') }}" method="GET">
        <input type="hidden" name="search" value="{{ request('search') }}">
        <div class="sidebar-card">
          <div class="sidebar-card__title"><i class="fas fa-sliders-h" style="color:var(--primary)"></i> Lọc kết quả</div>
          <div class="sidebar-card__body">
            <div class="filter-group">
              <div class="filter-group__label">Loại hình công việc</div>
              @foreach(['Full-time','Part-time','Remote','Freelance','Internship'] as $type)
                <label class="filter-option">
                  <input type="radio" name="job_type" value="{{ $type }}" {{ request('job_type')==$type ? 'checked' : '' }}>
                  {{ $type }}
                </label>
              @endforeach
            </div>
            <div class="divider"></div>
            <div class="filter-group">
              <div class="filter-group__label">Mức lương</div>
              @foreach(['Thỏa Thuận','Dưới 5 triệu','5 - 10 triệu','10 - 15 triệu','Trên 15 triệu'] as $range)
                <label class="filter-option">
                  <input type="radio" name="salary_range" value="{{ $range }}" {{ request('salary_range')==$range ? 'checked' : '' }}>
                  {{ $range }}
                </label>
              @endforeach
            </div>
            <div class="divider"></div>
            <div class="filter-group">
              <div class="filter-group__label">Địa điểm</div>
              @foreach(['Hà Nội','Hồ Chí Minh','Đà Nẵng','Remote'] as $loc)
                <label class="filter-option">
                  <input type="radio" name="address" value="{{ $loc }}" {{ request('address')==$loc ? 'checked' : '' }}>
                  {{ $loc }}
                </label>
              @endforeach
            </div>
            <button type="submit" class="btn btn-primary btn-block mt-16">Áp dụng bộ lọc</button>
            <a href="{{ url('/job') }}" class="btn btn-ghost btn-block mt-8">Xoá lọc</a>
          </div>
        </div>
      </form>
    </aside>

    {{-- JOB LIST --}}
    <div style="flex:1;min-width:0">
      <div class="flex-between mb-20">
        <div>
          <div class="section-title">Việc làm mới nhất</div>
          <div class="section-subtitle">{{ method_exists($listings,'total') ? $listings->total() : $listings->count() }} kết quả được tìm thấy</div>
        </div>
        <div class="flex gap-8">
          <select class="form-control" style="width:auto;padding:8px 14px;font-size:13px" onchange="location='?sort='+this.value+'{{ request('search') ? '&search='.request('search') : '' }}'">
            <option value="newest">Mới nhất</option>
            <option value="salary">Lương cao nhất</option>
          </select>
          <button onclick="toggleSidebar()" class="btn btn-ghost btn-sm" id="filter-btn">
            <i class="fas fa-sliders-h"></i> Bộ lọc
          </button>
        </div>
      </div>

      @forelse($listings as $listing)
        <div class="job-card mb-12">
          <div class="job-card__header">
            <div class="job-card__logo">
              @if($listing->feature_image)
                <img src="{{ asset('storage/images/'.$listing->feature_image) }}" alt="{{ $listing->title }}">
              @else
                <i class="fas fa-building" style="font-size:22px;color:var(--primary)"></i>
              @endif
            </div>
            <div class="job-card__info">
              <a href="{{ url('/job/show/'.$listing->slug) }}" class="job-card__title">{{ $listing->title }}</a>
              <div class="job-card__company">
                <i class="fas fa-building fa-fw"></i>
                {{ $listing->user->company_name ?? $listing->user->name }}
              </div>
            </div>
            @auth
              <button class="btn btn-ghost btn-sm" style="flex-shrink:0;width:36px;padding:0;height:36px;border-radius:50%" onclick="saveJob({{ $listing->id }})" title="Lưu việc làm">
                <i class="far fa-bookmark"></i>
              </button>
            @endauth
          </div>

          <div class="job-card__tags">
            <span class="tag tag-green">
              <i class="fas fa-money-bill-wave" style="font-size:11px"></i>
              {{ $listing->salary == 0 ? 'Thỏa thuận' : number_format($listing->salary).' đ' }}
            </span>
            <span class="tag tag-blue">
              <i class="fas fa-map-marker-alt" style="font-size:11px"></i>{{ $listing->address }}
            </span>
            <span class="tag tag-gray">{{ $listing->job_type }}</span>
          </div>

          <div class="job-card__footer">
            <span class="job-card__deadline">
              <i class="fas fa-calendar-times"></i>
              Hết hạn: {{ \Carbon\Carbon::parse($listing->application_close_date)->format('d/m/Y') }}
            </span>
            <a href="{{ url('/job/show/'.$listing->slug) }}" class="btn btn-primary btn-sm">Xem chi tiết</a>
          </div>
        </div>
      @empty
        <div class="empty-state">
          <div class="empty-state__icon">🔍</div>
          <div class="empty-state__title">Không tìm thấy việc làm nào</div>
          <div class="empty-state__desc">Thử tìm với từ khóa khác hoặc xoá bộ lọc để xem thêm cơ hội</div>
          <a href="{{ url('/job') }}" class="btn btn-primary">Xem tất cả việc làm</a>
        </div>
      @endforelse

      @if(isset($listings) && method_exists($listings,'hasPages') && $listings->hasPages())
        <div class="flex-center mt-24">
          <div class="pagination">
            @if($listings->onFirstPage())
              <span class="disabled"><i class="fas fa-chevron-left"></i></span>
            @else
              <a href="{{ $listings->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>
            @endif
            @foreach($listings->getUrlRange(1,$listings->lastPage()) as $page => $url)
              @if($page == $listings->currentPage())
                <span class="active">{{ $page }}</span>
              @else
                <a href="{{ $url }}">{{ $page }}</a>
              @endif
            @endforeach
            @if($listings->hasMorePages())
              <a href="{{ $listings->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>
            @else
              <span class="disabled"><i class="fas fa-chevron-right"></i></span>
            @endif
          </div>
        </div>
      @endif
    </div>
  </div>
</div>

@push('styles')
<style>
@keyframes pulse {
  0%,100% { opacity: 1; }
  50% { opacity: 0.4; }
}
</style>
@endpush

@push('scripts')
<script>
function toggleSidebar() {
  const sb = document.getElementById('sidebar');
  const open = sb.style.display !== 'none' && sb.style.display !== '';
  sb.style.display = open ? 'none' : 'block';
  document.getElementById('filter-btn').style.background = open ? '' : 'var(--primary-light)';
  document.getElementById('filter-btn').style.color = open ? '' : 'var(--primary)';
}
function saveJob(id) {
  // Bookmark logic
  console.log('Save job', id);
}
</script>
@endpush
@endsection
