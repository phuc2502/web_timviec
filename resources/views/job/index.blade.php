@extends('layouts.app')
@section('title', 'Tìm việc làm IT — ITWorks')
@section('description', 'Hàng nghìn việc làm IT đang chờ bạn. Lập trình viên, DevOps, Data, AI...')

@section('content')

{{-- HERO --}}
<section class="hero">
  <div class="container">
    <div style="max-width:700px">
      <h1>Nền tảng tuyển dụng IT<br><span style="color:#7effc4">hàng đầu Việt Nam</span></h1>
      <p>Kết nối <strong>{{ $totalJobs ?? '2,500+' }}</strong> việc làm IT với hàng nghìn kỹ sư công nghệ xuất sắc</p>

      {{-- Search bar --}}
      <form action="{{ url('/job') }}" method="GET">
        <div class="search-bar">
          <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Tìm việc: Backend, Frontend, DevOps...">
          <div class="search-bar__divider"></div>
          <select name="address" style="padding:14px 14px;border:none;font-size:14px;font-family:inherit;color:var(--text-secondary);background:transparent;cursor:pointer;min-width:150px">
            <option value="">📍 Tất cả địa điểm</option>
            <option value="Hà Nội" {{ request('address') == 'Hà Nội' ? 'selected' : '' }}>Hà Nội</option>
            <option value="Hồ Chí Minh" {{ request('address') == 'Hồ Chí Minh' ? 'selected' : '' }}>Hồ Chí Minh</option>
            <option value="Đà Nẵng" {{ request('address') == 'Đà Nẵng' ? 'selected' : '' }}>Đà Nẵng</option>
            <option value="Remote" {{ request('address') == 'Remote' ? 'selected' : '' }}>Remote</option>
          </select>
          <button type="submit" class="btn btn-primary">Tìm kiếm</button>
        </div>
      </form>

      <div class="hero-stats">
        <div class="hero-stats__item"><div class="hero-stats__num">{{ $totalJobs ?? '2.5K+' }}</div><div class="hero-stats__label">Việc làm</div></div>
        <div class="hero-stats__item"><div class="hero-stats__num">500+</div><div class="hero-stats__label">Công ty</div></div>
        <div class="hero-stats__item"><div class="hero-stats__num">10K+</div><div class="hero-stats__label">Ứng viên</div></div>
      </div>
    </div>
  </div>
</section>

{{-- CATEGORY QUICK FILTER --}}
<div style="background:#fff;border-bottom:1px solid var(--border)">
  <div class="container" style="padding:14px 16px;display:flex;gap:8px;overflow-x:auto;-webkit-overflow-scrolling:touch">
    @foreach(['Backend', 'Frontend', 'Mobile', 'DevOps', 'Data / AI', 'QA/Tester', 'UI/UX', 'Blockchain', 'Game'] as $cat)
      <a href="{{ url('/job?search='.urlencode($cat)) }}" class="tag {{ request('search') == $cat ? 'tag-green' : 'tag-gray' }}" style="white-space:nowrap;font-size:13px;padding:6px 14px">
        {{ $cat }}
      </a>
    @endforeach
  </div>
</div>

{{-- MAIN --}}
<div class="container section">
  <div class="flex gap-24" style="align-items:flex-start">

    {{-- SIDEBAR FILTER --}}
    <aside class="sidebar" style="display:none" id="sidebar">
      <form action="{{ url('/job') }}" method="GET">
        <input type="hidden" name="search" value="{{ request('search') }}">

        <div class="sidebar-card">
          <div class="sidebar-card__title"><i class="fas fa-filter" style="color:var(--primary);margin-right:6px"></i>Lọc kết quả</div>
          <div class="sidebar-card__body">
            {{-- Job type --}}
            <div class="filter-group">
              <div class="filter-group__label">Loại hình công việc</div>
              @foreach(['Full-time','Part-time','Remote','Freelance','Internship'] as $type)
                <label class="filter-option">
                  <input type="radio" name="job_type" value="{{ $type }}" {{ request('job_type') == $type ? 'checked' : '' }}>
                  {{ $type }}
                </label>
              @endforeach
            </div>
            <div class="divider"></div>
            {{-- Salary --}}
            <div class="filter-group">
              <div class="filter-group__label">Mức lương</div>
              @foreach(['Thỏa Thuận','Dưới 5 triệu','5 - 10 triệu','10 - 15 triệu','Trên 15 triệu'] as $range)
                <label class="filter-option">
                  <input type="radio" name="salary_range" value="{{ $range }}" {{ request('salary_range') == $range ? 'checked' : '' }}>
                  {{ $range }}
                </label>
              @endforeach
            </div>
            <div class="divider"></div>
            {{-- Location --}}
            <div class="filter-group">
              <div class="filter-group__label">Địa điểm</div>
              @foreach(['Hà Nội','Hồ Chí Minh','Đà Nẵng','Remote'] as $loc)
                <label class="filter-option">
                  <input type="radio" name="address" value="{{ $loc }}" {{ request('address') == $loc ? 'checked' : '' }}>
                  {{ $loc }}
                </label>
              @endforeach
            </div>
            <button type="submit" class="btn btn-primary btn-block mt-12">Áp dụng</button>
            <a href="{{ url('/') }}" class="btn btn-outline btn-block mt-8" style="font-size:13px">Xoá lọc</a>
          </div>
        </div>
      </form>
    </aside>

    {{-- JOB LIST --}}
    <div style="flex:1;min-width:0">
      <div class="flex-between mb-16">
        <div>
          <span class="fw-700 fs-16" style="color:var(--secondary)">Việc làm mới nhất</span>
          <span class="text-muted fs-13 ml-8">({{ method_exists($listings, 'total') ? $listings->total() : $listings->count() }} kết quả)</span>
        </div>
        <div class="flex gap-8">
          <select class="form-control" style="width:auto;padding:6px 12px;font-size:13px" onchange="location='?sort='+this.value">
            <option value="newest">Mới nhất</option>
            <option value="salary">Lương cao nhất</option>
          </select>
          <button onclick="document.getElementById('sidebar').style.display=document.getElementById('sidebar').style.display==='none'?'block':'none'" class="btn btn-outline btn-sm"><i class="fas fa-filter"></i> Lọc</button>
        </div>
      </div>

      @forelse($listings as $listing)
        <div class="job-card mb-12">
          <div class="job-card__header">
            <div class="job-card__logo" style="display:flex;align-items:center;justify-content:center;font-size:20px;color:var(--primary)">
              @if($listing->feature_image)
                <img src="{{ asset('storage/images/'.$listing->feature_image) }}" alt="{{ $listing->title }}" style="width:48px;height:48px;object-fit:contain">
              @else
                <i class="fas fa-building"></i>
              @endif
            </div>
            <div class="job-card__info">
              <a href="{{ url('/job/show/'.$listing->slug) }}" class="job-card__title">{{ $listing->title }}</a>
              <div class="job-card__company">
                <i class="fas fa-building fa-fw" style="color:var(--text-muted)"></i>
                {{ $listing->user->company_name ?? $listing->user->name }}
              </div>
            </div>
            @auth
              <button class="btn btn-outline btn-sm" style="flex-shrink:0" onclick="saveJob({{ $listing->id }})">
                <i class="far fa-bookmark"></i>
              </button>
            @endauth
          </div>

          <div class="job-card__tags">
            <span class="tag tag-green"><i class="fas fa-money-bill-wave" style="margin-right:4px"></i>
              {{ $listing->salary == 0 ? 'Thỏa thuận' : number_format($listing->salary).' đ' }}
            </span>
            <span class="tag tag-blue"><i class="fas fa-map-marker-alt" style="margin-right:4px"></i>{{ $listing->address }}</span>
            <span class="tag tag-gray">{{ $listing->job_type }}</span>
          </div>

          <div class="job-card__footer">
            <span class="job-card__deadline">
              <i class="fas fa-clock fa-fw"></i> Hết hạn: {{ \Carbon\Carbon::parse($listing->application_close_date)->format('d/m/Y') }}
            </span>
            <a href="{{ url('/job/show/'.$listing->slug) }}" class="btn btn-primary btn-sm">Xem chi tiết</a>
          </div>
        </div>
      @empty
        <div class="card text-center" style="padding:48px 24px">
          <div style="font-size:48px;margin-bottom:12px">🔍</div>
          <div class="fw-700 fs-16">Không tìm thấy việc làm nào</div>
          <p class="text-muted mt-8 fs-13">Thử tìm với từ khóa khác hoặc xoá bộ lọc</p>
          <a href="{{ url('/') }}" class="btn btn-primary mt-16" style="display:inline-flex">Xem tất cả việc làm</a>
        </div>
      @endforelse

      {{-- PAGINATION --}}
      @if(isset($listings) && method_exists($listings, 'hasPages') && $listings->hasPages())
        <div class="flex-center mt-24">
          <div class="pagination">
            @if($listings->onFirstPage())
              <span class="disabled"><i class="fas fa-chevron-left"></i></span>
            @else
              <a href="{{ $listings->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>
            @endif

            @foreach($listings->getUrlRange(1, $listings->lastPage()) as $page => $url)
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

@endsection
