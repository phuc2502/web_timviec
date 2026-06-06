<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>{{ $cvData->full_name }} - CV</title>
  <style>
    body {
      font-family: 'DejaVu Sans', sans-serif;
      color: #334155;
      line-height: 1.5;
      font-size: 13px;
      margin: 0;
      padding: 0;
    }
    .cv-header {
      border-bottom: 2px solid #1a73e8;
      padding-bottom: 20px;
      margin-bottom: 20px;
    }
    .header-table {
      width: 100%;
      border-collapse: collapse;
    }
    .header-info {
      vertical-align: top;
    }
    .header-avatar {
      width: 110px;
      text-align: right;
      vertical-align: top;
    }
    .avatar-img {
      width: 100px;
      height: 120px;
      object-fit: cover;
      border-radius: 4px;
      border: 1px solid #cbd5e1;
    }
    .candidate-name {
      font-size: 24px;
      font-weight: bold;
      color: #1e293b;
      margin: 0 0 6px 0;
    }
    .candidate-title {
      font-size: 14px;
      color: #1a73e8;
      font-weight: bold;
      margin: 0 0 12px 0;
    }
    .contact-item {
      font-size: 12px;
      color: #475569;
      margin-bottom: 4px;
    }
    .contact-item i {
      color: #1a73e8;
      width: 16px;
      display: inline-block;
    }
    .section-title {
      font-size: 14px;
      font-weight: bold;
      color: #1e293b;
      border-bottom: 1px solid #e2e8f0;
      padding-bottom: 4px;
      margin-top: 20px;
      margin-bottom: 10px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .section-content {
      margin-bottom: 15px;
    }
    .skills-container {
      margin-top: 5px;
    }
    .skill-badge {
      display: inline-block;
      background-color: #f1f5f9;
      color: #334155;
      padding: 4px 10px;
      border-radius: 4px;
      margin-right: 6px;
      margin-bottom: 6px;
      font-size: 11px;
      border: 1px solid #e2e8f0;
    }
    .item-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }
    .item-header-left {
      font-weight: bold;
      color: #1e293b;
      font-size: 13px;
      text-align: left;
    }
    .item-header-right {
      text-align: right;
      color: #64748b;
      font-size: 12px;
      vertical-align: top;
    }
    .item-sub {
      color: #1a73e8;
      font-weight: 500;
      font-size: 12px;
      margin-bottom: 4px;
    }
    .item-desc {
      color: #475569;
      font-size: 12px;
      white-space: pre-line;
      margin-top: 4px;
    }
    .page-break {
      page-break-inside: avoid;
    }
  </style>
</head>
<body>

  @php
    $imageSrc = $isPdf ? ($photoBase64 ?? '') : ($photoUrl ?? '');
  @endphp

  <!-- Header -->
  <div class="cv-header">
    <table class="header-table">
      <tr>
        <td class="header-info">
          <h1 class="candidate-name">{{ $cvData->full_name }}</h1>
          <div class="candidate-title">LẬP TRÌNH VIÊN PHẦN MỀM</div>
          
          <table style="width: 100%;">
            <tr>
              <td style="width: 50%; vertical-align: top; padding-right: 10px;">
                @if($cvData->email)
                  <div class="contact-item"><strong>Email:</strong> {{ $cvData->email }}</div>
                @endif
                @if($cvData->phone)
                  <div class="contact-item"><strong>SĐT:</strong> {{ $cvData->phone }}</div>
                @endif
              </td>
              <td style="width: 50%; vertical-align: top;">
                @if($cvData->address)
                  <div class="contact-item"><strong>Địa chỉ:</strong> {{ $cvData->address }}</div>
                @endif
              </td>
            </tr>
          </table>
        </td>
        @if($imageSrc)
          <td class="header-avatar">
            <img src="{{ $imageSrc }}" class="avatar-img" alt="Ảnh thẻ">
          </td>
        @endif
      </tr>
    </table>
  </div>

  <!-- Mục tiêu nghề nghiệp -->
  @if($cvData->objective)
    <div class="page-break">
      <div class="section-title">Mục tiêu nghề nghiệp</div>
      <div class="section-content" style="font-size: 12.5px; color: #475569;">
        {{ $cvData->objective }}
      </div>
    </div>
  @endif

  <!-- Kỹ năng chuyên môn -->
  @if($cvData->skills_text)
    <div class="page-break">
      <div class="section-title">Kỹ năng chuyên môn</div>
      <div class="section-content skills-container">
        @foreach(array_map('trim', explode(',', $cvData->skills_text)) as $skill)
          @if(!empty($skill))
            <span class="skill-badge">{{ $skill }}</span>
          @endif
        @endforeach
      </div>
    </div>
  @endif

  <!-- Kinh nghiệm làm việc -->
  @if(!empty($cvData->experience) && count($cvData->experience) > 0)
    <div class="page-break">
      <div class="section-title">Kinh nghiệm làm việc</div>
      @foreach($cvData->experience as $exp)
        @if(!empty($exp['company']) || !empty($exp['role']))
          <div class="section-content" style="margin-bottom: 12px; page-break-inside: avoid;">
            <table class="item-table">
              <tr>
                <td class="item-header-left">{{ $exp['role'] ?? 'Nhân viên' }}</td>
                <td class="item-header-right">
                  {{ $exp['year_start'] ?? '' }} - {{ $exp['year_end'] ?? 'Hiện tại' }}
                </td>
              </tr>
            </table>
            <div class="item-sub">{{ $exp['company'] ?? '' }}</div>
            @if(!empty($exp['desc']))
              <div class="item-desc">{!! e($exp['desc']) !!}</div>
            @endif
          </div>
        @endif
      @endforeach
    </div>
  @endif

  <!-- Dự án nổi bật -->
  @if(!empty($cvData->projects) && count($cvData->projects) > 0)
    <div class="page-break">
      <div class="section-title">Dự án nổi bật</div>
      @foreach($cvData->projects as $proj)
        @if(!empty($proj['name']))
          <div class="section-content" style="margin-bottom: 12px; page-break-inside: avoid;">
            <table class="item-table">
              <tr>
                <td class="item-header-left">{{ $proj['name'] }}</td>
                <td class="item-header-right">
                  @if(!empty($proj['url']))
                    <span style="font-size: 11px;">Link: {{ $proj['url'] }}</span>
                  @endif
                </td>
              </tr>
            </table>
            @if(!empty($proj['tech']))
              <div class="item-sub">Công nghệ: {{ $proj['tech'] }}</div>
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
      <div class="section-title">Học vấn & Bằng cấp</div>
      @foreach($cvData->education as $edu)
        @if(!empty($edu['school']))
          <div class="section-content" style="margin-bottom: 12px; page-break-inside: avoid;">
            <table class="item-table">
              <tr>
                <td class="item-header-left">{{ $edu['school'] }}</td>
                <td class="item-header-right">
                  {{ $edu['year_start'] ?? '' }} - {{ $edu['year_end'] ?? '' }}
                </td>
              </tr>
            </table>
            <div class="item-sub">{{ $edu['degree'] ?? '' }}</div>
          </div>
        @endif
      @endforeach
    </div>
  @endif

  <!-- Chứng chỉ -->
  @if(!empty($cvData->certifications) && count($cvData->certifications) > 0)
    <div class="page-break">
      <div class="section-title">Chứng chỉ</div>
      @foreach($cvData->certifications as $cert)
        @if(!empty($cert['name']))
          <div class="section-content" style="margin-bottom: 8px; page-break-inside: avoid;">
            <table class="item-table">
              <tr>
                <td class="item-header-left">{{ $cert['name'] }}</td>
                <td class="item-header-right">{{ $cert['year'] ?? '' }}</td>
              </tr>
            </table>
            @if(!empty($cert['issuer']))
              <div class="item-sub" style="font-size: 11px; margin-top: -4px;">Cấp bởi: {{ $cert['issuer'] }}</div>
            @endif
          </div>
        @endif
      @endforeach
    </div>
  @endif

  <!-- Ngoại ngữ -->
  @if(!empty($cvData->languages) && count($cvData->languages) > 0)
    <div class="page-break">
      <div class="section-title">Ngoại ngữ</div>
      <table style="width: 100%; border-collapse: collapse;">
        @foreach($cvData->languages as $lang)
          @if(!empty($lang['lang']))
            <tr style="page-break-inside: avoid;">
              <td style="width: 30%; font-weight: bold; padding: 4px 0; color: #1e293b;">{{ $lang['lang'] }}</td>
              <td style="padding: 4px 0; color: #475569;">: {{ $lang['level'] }}</td>
            </tr>
          @endif
        @endforeach
      </table>
    </div>
  @endif

</body>
</html>
