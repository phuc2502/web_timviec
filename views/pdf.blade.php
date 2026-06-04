<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CV — {{ $data['name'] ?? 'Ứng viên' }}</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', sans-serif; font-size: 13px; color: #212f3f; background: #fff; }
    .page { max-width: 800px; margin: 0 auto; padding: 0; }

    /* HEADER */
    .cv-header { background: linear-gradient(135deg, #00b14f, #008a3e); color: #fff; padding: 28px 32px; display: flex; align-items: center; gap: 20px; }
    .cv-avatar { width: 80px; height: 80px; border-radius: 50%; border: 3px solid rgba(255,255,255,.4); object-fit: cover; flex-shrink: 0; }
    .cv-avatar-placeholder { width: 80px; height: 80px; border-radius: 50%; border: 3px solid rgba(255,255,255,.4); background: rgba(255,255,255,.2); display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 700; flex-shrink: 0; }
    .cv-header h1 { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
    .cv-contacts { display: flex; flex-wrap: wrap; gap: 8px 16px; margin-top: 8px; font-size: 12px; opacity: .9; }
    .cv-contacts span { display: flex; align-items: center; gap: 4px; }

    /* BODY */
    .cv-body { display: grid; grid-template-columns: 1fr 2fr; gap: 0; }
    .cv-left { background: #f8f9fa; padding: 24px 20px; border-right: 1px solid #e8edf2; }
    .cv-right { padding: 24px 24px; }

    /* SECTIONS */
    .cv-section { margin-bottom: 20px; }
    .cv-section-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #00b14f; border-bottom: 2px solid #00b14f; padding-bottom: 4px; margin-bottom: 12px; }
    .cv-objective { font-size: 13px; line-height: 1.7; color: #444; }

    /* SKILL BAR */
    .skill-item { margin-bottom: 8px; }
    .skill-name { font-size: 12px; font-weight: 600; margin-bottom: 3px; }
    .skill-bar { height: 5px; background: #e0e0e0; border-radius: 5px; overflow: hidden; }
    .skill-fill { height: 100%; background: #00b14f; border-radius: 5px; }

    /* EXPERIENCE */
    .exp-item { margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #f0f0f0; }
    .exp-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    .exp-title { font-weight: 700; font-size: 14px; color: #212f3f; }
    .exp-company { font-weight: 600; font-size: 12px; color: #00b14f; margin: 2px 0; }
    .exp-period { font-size: 11px; color: #6f7882; margin-bottom: 6px; }
    .exp-desc { font-size: 12px; line-height: 1.7; color: #444; }
    .exp-desc li { margin-left: 14px; list-style: disc; }

    /* EDUCATION */
    .edu-school { font-weight: 700; font-size: 13px; }
    .edu-major { font-size: 12px; color: #00b14f; font-weight: 600; margin: 2px 0; }
    .edu-detail { font-size: 11px; color: #6f7882; }

    /* PROJECT */
    .proj-name { font-weight: 700; font-size: 13px; }
    .proj-link { font-size: 11px; color: #1a73e8; }
    .proj-desc { font-size: 12px; line-height: 1.6; color: #444; margin-top: 4px; }

    /* PRINT */
    @media print {
      body { margin: 0; }
      .no-print { display: none !important; }
      .page { max-width: 100%; }
    }
  </style>
</head>
<body>

{{-- PRINT BUTTON --}}
<div class="no-print" style="background:#00b14f;padding:14px 24px;display:flex;align-items:center;justify-content:space-between">
  <span style="color:#fff;font-weight:600;font-size:14px;font-family:Inter,sans-serif">📄 Preview CV — {{ $data['name'] ?? '' }}</span>
  <div style="display:flex;gap:10px">
    <a href="{{ url('/user/cv/create') }}" style="background:rgba(255,255,255,.2);color:#fff;padding:8px 16px;border-radius:6px;font-size:13px;font-family:Inter,sans-serif;text-decoration:none">← Chỉnh sửa</a>
    <button onclick="window.print()" style="background:#fff;color:#00b14f;padding:8px 16px;border-radius:6px;font-size:13px;font-weight:700;cursor:pointer;border:none;font-family:Inter,sans-serif">🖨️ In / Xuất PDF</button>
  </div>
</div>

<div class="page">
  {{-- HEADER --}}
  <div class="cv-header">
    @if(!empty($data['avatar']) && $path)
      <img src="{{ $path }}" alt="" class="cv-avatar">
    @else
      <div class="cv-avatar-placeholder">{{ strtoupper(substr($data['name'] ?? 'U', 0, 1)) }}</div>
    @endif
    <div>
      <h1>{{ $data['name'] ?? 'Họ và Tên' }}</h1>
      <div style="font-size:13px;opacity:.85;margin-top:2px">{{ $data['objective'] ? Str::limit($data['objective'], 60) : 'Software Developer' }}</div>
      <div class="cv-contacts">
        @if(!empty($data['email']))<span>✉ {{ $data['email'] }}</span>@endif
        @if(!empty($data['phone']))<span>📱 {{ $data['phone'] }}</span>@endif
        @if(!empty($data['address']))<span>📍 {{ $data['address'] }}</span>@endif
        @if(!empty($data['linkedin']))<span>🔗 {{ $data['linkedin'] }}</span>@endif
      </div>
    </div>
  </div>

  <div class="cv-body">
    {{-- LEFT --}}
    <div class="cv-left">
      {{-- Skills --}}
      @if(!empty($data['skills']))
        <div class="cv-section">
          <div class="cv-section-title">Kỹ năng</div>
          @foreach(explode(',', $data['skills']) as $skill)
            @if(trim($skill))
              <div class="skill-item">
                <div class="skill-name">{{ trim($skill) }}</div>
                <div class="skill-bar"><div class="skill-fill" style="width:{{ rand(60,95) }}%"></div></div>
              </div>
            @endif
          @endforeach
        </div>
      @endif

      {{-- Soft skills --}}
      @if(!empty($data['soft_skills']))
        <div class="cv-section">
          <div class="cv-section-title">Kỹ năng mềm</div>
          @foreach(explode(',', $data['soft_skills']) as $skill)
            @if(trim($skill))
              <div style="font-size:12px;padding:3px 0;color:#444">• {{ trim($skill) }}</div>
            @endif
          @endforeach
        </div>
      @endif

      {{-- Education --}}
      @if(!empty($data['edu_school']))
        <div class="cv-section">
          <div class="cv-section-title">Học vấn</div>
          <div class="edu-school">{{ $data['edu_school'] }}</div>
          <div class="edu-major">{{ $data['edu_major'] ?? '' }}</div>
          <div class="edu-detail">
            @if(!empty($data['edu_year']))Tốt nghiệp {{ $data['edu_year'] }}@endif
            @if(!empty($data['edu_gpa'])) · {{ $data['edu_gpa'] }}@endif
          </div>
        </div>
      @endif
    </div>

    {{-- RIGHT --}}
    <div class="cv-right">
      {{-- Objective --}}
      @if(!empty($data['objective']))
        <div class="cv-section">
          <div class="cv-section-title">Mục tiêu nghề nghiệp</div>
          <div class="cv-objective">{{ $data['objective'] }}</div>
        </div>
      @endif

      {{-- Experience --}}
      @if(!empty($data['exp_title']))
        <div class="cv-section">
          <div class="cv-section-title">Kinh nghiệm làm việc</div>
          @foreach($data['exp_title'] as $i => $title)
            @if($title)
              <div class="exp-item">
                <div class="exp-title">{{ $title }}</div>
                <div class="exp-company">{{ $data['exp_company'][$i] ?? '' }}</div>
                <div class="exp-period">
                  {{ $data['exp_from'][$i] ?? '' }}
                  {{ !empty($data['exp_to'][$i]) ? '→ '.$data['exp_to'][$i] : '→ Hiện tại' }}
                </div>
                @if(!empty($data['exp_desc'][$i]))
                  <div class="exp-desc">{{ $data['exp_desc'][$i] }}</div>
                @endif
              </div>
            @endif
          @endforeach
        </div>
      @endif

      {{-- Projects --}}
      @if(!empty($data['proj_name']))
        <div class="cv-section">
          <div class="cv-section-title">Dự án nổi bật</div>
          <div class="proj-name">{{ $data['proj_name'] }}</div>
          @if(!empty($data['proj_link']))<div class="proj-link">🔗 {{ $data['proj_link'] }}</div>@endif
          <div class="proj-desc">{{ $data['proj_desc'] ?? '' }}</div>
        </div>
      @endif
    </div>
  </div>
</div>
</body>
</html>
