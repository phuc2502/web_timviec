@extends('layouts.app')
@section('title', 'Tìm việc làm IT — ITWorks')
@section('description', 'Hàng nghìn việc làm IT đang chờ bạn. Lập trình viên, DevOps, Data, AI...')

@section('content')

{{-- HERO --}}
<section class="hero-section">
  <div class="container">
    <div style="max-width:700px">
      <h1>Nền tảng tuyển dụng IT<br><span style="color:var(--primary)">hàng đầu Việt Nam</span></h1>
      <p>Kết nối <strong>{{ $totalJobs ?? '2,500+' }}</strong> việc làm IT với hàng nghìn kỹ sư công nghệ xuất sắc</p>

      {{-- Search bar --}}
      <form action="{{ url('/job') }}" method="GET">
        <div class="search-box">
          <input type="text" name="keyword" class="search-input" value="{{ request('keyword') }}" placeholder="🔍 Tìm việc: Backend, Frontend, DevOps...">
          <select name="address" class="select-box" style="min-width:150px">
            <option value="">📍 Tất cả địa điểm</option>
            <option value="Hà Nội" {{ request('address') == 'Hà Nội' ? 'selected' : '' }}>Hà Nội</option>
            <option value="Hồ Chí Minh" {{ request('address') == 'Hồ Chí Minh' ? 'selected' : '' }}>Hồ Chí Minh</option>
            <option value="Đà Nẵng" {{ request('address') == 'Đà Nẵng' ? 'selected' : '' }}>Đà Nẵng</option>
            <option value="Remote" {{ request('address') == 'Remote' ? 'selected' : '' }}>Remote</option>
          </select>
          <button type="submit" class="btn btn-primary" style="border-radius: var(--radius-md)">Tìm kiếm</button>
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
      <a href="{{ request()->fullUrlWithQuery(['keyword' => $cat, 'page' => null]) }}" class="tag {{ request('keyword') == $cat ? 'tag-green' : 'tag-gray' }}" style="white-space:nowrap;font-size:13px;padding:6px 14px">
        {{ $cat }}
      </a>
    @endforeach
  </div>
</div>

{{-- MAIN --}}
<div class="container section">
  <div class="flex gap-24" style="align-items:flex-start">

    {{-- SIDEBAR FILTER --}}
    <aside class="sidebar" id="sidebar">
      <form action="{{ url('/job') }}" method="GET" id="filter-form">
        {{-- Giữ lại keyword từ search bar --}}
        <input type="hidden" name="keyword" value="{{ request('keyword') }}">
        {{-- Giữ lại sort hiện tại --}}
        @if(request('sort'))
          <input type="hidden" name="sort" value="{{ request('sort') }}">
        @endif

        <div class="sidebar-card">
          <div class="sidebar-card__title">
            <i class="fas fa-filter" style="color:var(--primary);margin-right:6px"></i>Lọc kết quả
            @php
              $activeCount = collect(['job_type','work_mode','salary_range','exp_range','job_level','address'])
                ->filter(fn($k) => request()->filled($k))->count();
            @endphp
            @if($activeCount > 0)
              <span class="badge" style="background:var(--primary);color:#fff;border-radius:99px;padding:1px 8px;font-size:11px;margin-left:6px">{{ $activeCount }}</span>
            @endif
          </div>
          <div class="sidebar-card__body">

            {{-- Loại hình --}}
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

            {{-- Work mode --}}
            <div class="filter-group">
              <div class="filter-group__label">Hình thức làm việc</div>
              @foreach(['onsite' => 'Tại văn phòng', 'hybrid' => 'Hybrid', 'remote' => 'Remote 100%'] as $val => $label)
                <label class="filter-option">
                  <input type="radio" name="work_mode" value="{{ $val }}" {{ request('work_mode') == $val ? 'checked' : '' }}>
                  {{ $label }}
                </label>
              @endforeach
            </div>
            <div class="divider"></div>

            {{-- Mức lương --}}
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

            {{-- Kinh nghiệm --}}
            <div class="filter-group">
              <div class="filter-group__label">Kinh nghiệm</div>
              @foreach(['Chưa có KN','Dưới 1 năm','1 - 3 năm','3 - 5 năm','Trên 5 năm'] as $exp)
                <label class="filter-option">
                  <input type="radio" name="exp_range" value="{{ $exp }}" {{ request('exp_range') == $exp ? 'checked' : '' }}>
                  {{ $exp }}
                </label>
              @endforeach
            </div>
            <div class="divider"></div>

            {{-- Cấp độ --}}
            <div class="filter-group">
              <div class="filter-group__label">Cấp độ</div>
              @foreach(['intern' => 'Intern', 'fresher' => 'Fresher', 'junior' => 'Junior', 'middle' => 'Middle', 'senior' => 'Senior', 'lead' => 'Lead / Manager'] as $val => $label)
                <label class="filter-option">
                  <input type="radio" name="job_level" value="{{ $val }}" {{ request('job_level') == $val ? 'checked' : '' }}>
                  {{ $label }}
                </label>
              @endforeach
            </div>
            <div class="divider"></div>

            {{-- Địa điểm --}}
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
            <a href="{{ url('/job') . (request('keyword') ? '?keyword='.urlencode(request('keyword')) : '') }}" class="btn btn-outline btn-block mt-8" style="font-size:13px">Xoá tất cả bộ lọc</a>
          </div>
        </div>
      </form>
    </aside>

    {{-- JOB LIST --}}
    <div style="flex:1;min-width:0">
      <div class="flex-between mb-16">
        <div>
          <span class="fw-700 fs-16" style="color:var(--secondary)">
            @php
              $sortLabels = [
                'newest'       => 'Việc làm mới nhất',
                'salary_desc'  => 'Lương cao nhất',
                'salary_asc'   => 'Lương thấp nhất',
                'closing_soon' => 'Hạn nộp gần nhất',
              ];
              $currentSort = request('sort');
              if (request('keyword')) {
                $sortLabel = 'Kết quả cho: "' . request('keyword') . '"';
              } elseif ($currentSort && isset($sortLabels[$currentSort])) {
                $sortLabel = $sortLabels[$currentSort];
              } else {
                $sortLabel = 'Tất cả việc làm';
              }
            @endphp
            {{ $sortLabel }}
          </span>
          <span class="text-muted fs-13 ml-8">({{ method_exists($listings, 'total') ? $listings->total() : $listings->count() }} kết quả)</span>
        </div>
        <div class="flex gap-8">
          <select class="form-control" style="width:auto;padding:6px 12px;font-size:13px" onchange="location=this.value">
            <option value="{{ url('/job') . (request('keyword') ? '?keyword='.urlencode(request('keyword')) : '') }}" {{ !request('sort') ? 'selected' : '' }}>Mặc định</option>
            <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" {{ request('sort') == 'newest' ? 'selected' : '' }}>Mới nhất</option>
            <option value="{{ request()->fullUrlWithQuery(['sort' => 'salary_desc']) }}" {{ request('sort') == 'salary_desc' ? 'selected' : '' }}>Lương cao nhất</option>
            <option value="{{ request()->fullUrlWithQuery(['sort' => 'salary_asc']) }}" {{ request('sort') == 'salary_asc' ? 'selected' : '' }}>Lương thấp nhất</option>
            <option value="{{ request()->fullUrlWithQuery(['sort' => 'closing_soon']) }}" {{ request('sort') == 'closing_soon' ? 'selected' : '' }}>Hạn nộp gần nhất</option>
          </select>
          @if(request()->hasAny(['keyword','job_type','work_mode','salary_range','exp_range','job_level','address','sort']))
            <a href="{{ url('/job') }}" class="btn btn-outline btn-sm" title="Xóa tất cả bộ lọc" style="white-space:nowrap">
              <i class="fas fa-times"></i> Xóa lọc
            </a>
          @endif
          <button onclick="var s=document.getElementById('sidebar');s.style.display=s.style.display==='none'?'block':'none'" class="btn btn-outline btn-sm d-lg-none"><i class="fas fa-filter"></i> Lọc</button>
        </div>
      </div>

      @forelse($listings as $listing)
        <div class="job-card mb-12">
          <!-- Left: Logo box -->
          <div class="job-card__logo" style="display:flex;align-items:center;justify-content:center;font-size:20px;color:var(--primary)">
            @if($listing->feature_image)
              <img src="{{ asset('storage/images/'.$listing->feature_image) }}" alt="{{ $listing->title }}" style="width:48px;height:48px;object-fit:contain">
            @else
              <i class="fas fa-building"></i>
            @endif
          </div>

          <!-- Right: Stacked Info, Tags and Footer -->
          <div class="job-card__content">
            <div class="job-card__header">
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
        </div>
      @empty
        <div class="card text-center" style="padding:48px 24px">
          <div style="font-size:48px;margin-bottom:12px">🔍</div>
          <div class="fw-700 fs-16">Không tìm thấy việc làm nào</div>
          <p class="text-muted mt-8 fs-13">Thử tìm với từ khóa khác hoặc xoá bộ lọc</p>
          <a href="{{ url('/job') }}" class="btn btn-primary mt-16" style="display:inline-flex">Xem tất cả việc làm</a>
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