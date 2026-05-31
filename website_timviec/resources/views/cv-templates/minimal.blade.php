<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>{{ $cvData->full_name }} - Minimalist CV</title>
  <style>
    body {
      font-family: 'DejaVu Sans', sans-serif;
      color: #1e293b;
      line-height: 1.4;
      font-size: 12.5px;
      margin: 0;
      padding: 0;
    }
    .cv-container {
      padding: 10px;
    }
    .candidate-name {
      font-size: 26px;
      font-weight: bold;
      color: #0f172a;
      margin: 0 0 6px 0;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .contact-line {
      font-size: 11px;
      color: #475569;
      margin-bottom: 15px;
      border-bottom: 1px solid #cbd5e1;
      padding-bottom: 10px;
    }
    .contact-item {
      display: inline-block;
      margin-right: 15px;
    }
    .section-title {
      font-size: 13px;
      font-weight: bold;
      color: #0f172a;
      margin-top: 22px;
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.8px;
    }
    .section-divider {
      height: 1px;
      background-color: #e2e8f0;
      margin-bottom: 12px;
    }
    .section-content {
      margin-bottom: 14px;
    }
    .item-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 4px;
    }
    .item-title {
      font-weight: bold;
      color: #0f172a;
      font-size: 12px;
      text-align: left;
    }
    .item-date {
      text-align: right;
      color: #64748b;
      font-size: 11px;
      vertical-align: top;
    }
    .item-sub {
      color: #475569;
      font-style: italic;
      font-size: 11.5px;
      margin-bottom: 4px;
    }
    .item-desc {
      color: #334155;
      font-size: 11.5px;
      white-space: pre-line;
      margin-top: 4px;
    }
    .skills-list {
      color: #334155;
      font-size: 12px;
    }
    .lang-item {
      display: inline-block;
      margin-right: 20px;
      font-size: 12px;
    }
    .page-break {
      page-break-inside: avoid;
    }
  </style>
</head>
<body>

  <div class="cv-container">
    
    <!-- Header -->
    <h1 class="candidate-name">{{ $cvData->full_name }}</h1>
    
    <div class="contact-line">
      @if($cvData->email)
        <span class="contact-item"><strong>Email:</strong> {{ $cvData->email }}</span>
      @endif
      @if($cvData->phone)
        <span class="contact-item"><strong>Phone:</strong> {{ $cvData->phone }}</span>
      @endif
      @if($cvData->address)
        <span class="contact-item"><strong>Address:</strong> {{ $cvData->address }}</span>
      @endif
    </div>

    <!-- Mục tiêu nghề nghiệp -->
    @if($cvData->objective)
      <div class="page-break">
        <div class="section-title">Mục tiêu</div>
        <div class="section-divider"></div>
        <div style="color: #334155; font-size: 12px; margin-bottom: 15px;">
          {{ $cvData->objective }}
        </div>
      </div>
    @endif

    <!-- Kỹ năng -->
    @if($cvData->skills_text)
      <div class="page-break">
        <div class="section-title">Kỹ năng</div>
        <div class="section-divider"></div>
        <div class="skills-list" style="margin-bottom: 15px;">
          {{ $cvData->skills_text }}
        </div>
      </div>
    @endif

    <!-- Kinh nghiệm làm việc -->
    @if(!empty($cvData->experience) && count($cvData->experience) > 0)
      <div class="page-break">
        <div class="section-title">Kinh nghiệm</div>
        <div class="section-divider"></div>
        @foreach($cvData->experience as $exp)
          @if(!empty($exp['company']) || !empty($exp['role']))
            <div class="section-content" style="page-break-inside: avoid;">
              <table class="item-table">
                <tr>
                  <td class="item-title">{{ $exp['role'] }}</td>
                  <td class="item-date">
                    {{ $exp['year_start'] ?? '' }} — {{ $exp['year_end'] ?? 'Present' }}
                  </td>
                </tr>
              </table>
              <div class="item-sub">{{ $exp['company'] }}</div>
              @if(!empty($exp['desc']))
                <div class="item-desc">{!! e($exp['desc']) !!}</div>
              @endif
            </div>
          @endif
        @endforeach
      </div>
    @endif

    <!-- Dự án -->
    @if(!empty($cvData->projects) && count($cvData->projects) > 0)
      <div class="page-break">
        <div class="section-title">Dự án</div>
        <div class="section-divider"></div>
        @foreach($cvData->projects as $proj)
          @if(!empty($proj['name']))
            <div class="section-content" style="page-break-inside: avoid;">
              <table class="item-table">
                <tr>
                  <td class="item-title">{{ $proj['name'] }}</td>
                  <td class="item-date">
                    @if(!empty($proj['url']))
                      <span style="font-size: 10px;">{{ $proj['url'] }}</span>
                    @endif
                  </td>
                </tr>
              </table>
              @if(!empty($proj['tech']))
                <div class="item-sub" style="font-style: normal; font-weight: 500;">Tech stack: {{ $proj['tech'] }}</div>
              @endif
              @if(!empty($proj['desc']))
                <div class="item-desc">{!! e($proj['desc']) !!}</div>
              @endif
            </div>
          @endif
        @endforeach
      </div>
    @endif

    <!-- Học vấn -->
    @if(!empty($cvData->education) && count($cvData->education) > 0)
      <div class="page-break">
        <div class="section-title">Học vấn</div>
        <div class="section-divider"></div>
        @foreach($cvData->education as $edu)
          @if(!empty($edu['school']))
            <div class="section-content" style="page-break-inside: avoid;">
              <table class="item-table">
                <tr>
                  <td class="item-title">{{ $edu['school'] }}</td>
                  <td class="item-date">
                    {{ $edu['year_start'] ?? '' }} — {{ $edu['year_end'] ?? '' }}
                  </td>
                </tr>
              </table>
              <div class="item-sub">{{ $edu['degree'] }}</div>
            </div>
          @endif
        @endforeach
      </div>
    @endif

    <!-- Chứng chỉ -->
    @if(!empty($cvData->certifications) && count($cvData->certifications) > 0)
      <div class="page-break">
        <div class="section-title">Chứng chỉ</div>
        <div class="section-divider"></div>
        @foreach($cvData->certifications as $cert)
          @if(!empty($cert['name']))
            <div class="section-content" style="margin-bottom: 8px; page-break-inside: avoid;">
              <table class="item-table">
                <tr>
                  <td class="item-title" style="font-weight: normal; font-size: 12px;">
                    <strong>{{ $cert['name'] }}</strong>
                    @if(!empty($cert['issuer']))
                      — {{ $cert['issuer'] }}
                    @endif
                  </td>
                  <td class="item-date">{{ $cert['year'] ?? '' }}</td>
                </tr>
              </table>
            </div>
          @endif
        @endforeach
      </div>
    @endif

    <!-- Ngoại ngữ -->
    @if(!empty($cvData->languages) && count($cvData->languages) > 0)
      <div class="page-break">
        <div class="section-title">Ngoại ngữ</div>
        <div class="section-divider"></div>
        <div style="margin-bottom: 10px; page-break-inside: avoid;">
          @foreach($cvData->languages as $lang)
            @if(!empty($lang['lang']))
              <span class="lang-item">
                <strong>{{ $lang['lang'] }}</strong>: {{ $lang['level'] }}
              </span>
            @endif
          @endforeach
        </div>
      </div>
    @endif

  </div>

</body>
</html>
