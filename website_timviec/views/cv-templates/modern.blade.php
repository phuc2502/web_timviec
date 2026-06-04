<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>{{ $cvData->full_name }} - Modern CV</title>
  <style>
    body {
      font-family: 'DejaVu Sans', sans-serif;
      color: #334155;
      line-height: 1.4;
      font-size: 12px;
      margin: 0;
      padding: 0;
    }
    .cv-table {
      width: 100%;
      border-collapse: collapse;
    }
    .left-col {
      width: 32%;
      background-color: #f8fafc;
      border-right: 1px solid #e2e8f0;
      padding: 20px 15px;
      vertical-align: top;
    }
    .right-col {
      width: 68%;
      padding: 20px;
      vertical-align: top;
    }
    .avatar-container {
      text-align: center;
      margin-bottom: 20px;
    }
    .avatar-img {
      width: 100px;
      height: 120px;
      object-fit: cover;
      border-radius: 6px;
      border: 2px solid #cbd5e1;
    }
    .candidate-name {
      font-size: 22px;
      font-weight: bold;
      color: #1e293b;
      margin: 0 0 4px 0;
    }
    .candidate-title {
      font-size: 13px;
      color: #1a73e8;
      font-weight: bold;
      margin: 0 0 16px 0;
      text-transform: uppercase;
    }
    .left-section-title {
      font-size: 12px;
      font-weight: bold;
      color: #1e293b;
      border-bottom: 1.5px solid #1a73e8;
      padding-bottom: 3px;
      margin-top: 20px;
      margin-bottom: 10px;
      text-transform: uppercase;
    }
    .right-section-title {
      font-size: 13px;
      font-weight: bold;
      color: #1e293b;
      border-bottom: 1.5px solid #64748b;
      padding-bottom: 3px;
      margin-top: 22px;
      margin-bottom: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .contact-item {
      font-size: 11px;
      color: #475569;
      margin-bottom: 6px;
      word-wrap: break-word;
    }
    .contact-label {
      font-weight: bold;
      color: #1e293b;
      display: block;
      margin-bottom: 2px;
    }
    .skill-tag {
      display: inline-block;
      background-color: #e2e8f0;
      color: #1e293b;
      padding: 3px 6px;
      border-radius: 4px;
      margin-right: 4px;
      margin-bottom: 5px;
      font-size: 10px;
    }
    .item-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    .item-title {
      font-weight: bold;
      color: #1e293b;
      font-size: 12px;
    }
    .item-date {
      text-align: right;
      color: #64748b;
      font-size: 11px;
      vertical-align: top;
    }
    .item-subtitle {
      color: #1a73e8;
      font-weight: bold;
      font-size: 11px;
      margin-bottom: 3px;
    }
    .item-desc {
      color: #475569;
      font-size: 11.5px;
      white-space: pre-line;
      margin-top: 4px;
    }
    .lang-table {
      width: 100%;
      border-collapse: collapse;
    }
    .lang-name {
      font-weight: bold;
      color: #1e293b;
      padding: 3px 0;
    }
    .lang-level {
      color: #64748b;
      text-align: right;
      font-size: 11px;
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

  <table class="cv-table">
    <tr>
      <!-- Cột Trái -->
      <td class="left-col">
        @if($imageSrc)
          <div class="avatar-container">
            <img src="{{ $imageSrc }}" class="avatar-img" alt="Ảnh thẻ">
          </div>
        @endif

        <!-- Liên hệ -->
        <div class="left-section-title" style="margin-top: 0;">Thông tin liên hệ</div>
        @if($cvData->phone)
          <div class="contact-item">
            <span class="contact-label">Số điện thoại:</span>
            {{ $cvData->phone }}
          </div>
        @endif
        @if($cvData->email)
          <div class="contact-item">
            <span class="contact-label">Email:</span>
            {{ $cvData->email }}
          </div>
        @endif
        @if($cvData->address)
          <div class="contact-item">
            <span class="contact-label">Địa chỉ:</span>
            {{ $cvData->address }}
          </div>
        @endif

        <!-- Ngoại ngữ -->
        @if(!empty($cvData->languages) && count($cvData->languages) > 0)
          <div class="left-section-title">Ngoại ngữ</div>
          <table class="lang-table">
            @foreach($cvData->languages as $lang)
              @if(!empty($lang['lang']))
                <tr>
                  <td class="lang-name">{{ $lang['lang'] }}</td>
                  <td class="lang-level">{{ $lang['level'] ?? '' }}</td>
                </tr>
              @endif
            @endforeach
          </table>
        @endif

        <!-- Chứng chỉ -->
        @if(!empty($cvData->certifications) && count($cvData->certifications) > 0)
          <div class="left-section-title">Chứng chỉ</div>
          @foreach($cvData->certifications as $cert)
            @if(!empty($cert['name']))
              <div style="margin-bottom: 8px; font-size: 11px;">
                <div style="font-weight: bold; color: #1e293b;">{{ $cert['name'] }}</div>
                <div style="color: #64748b;">{{ $cert['issuer'] ?? '' }} ({{ $cert['year'] ?? '' }})</div>
              </div>
            @endif
          @endforeach
        @endif
      </td>

      <!-- Cột Phải -->
      <td class="right-col">
        <!-- Tên candidate -->
        <h1 class="candidate-name">{{ $cvData->full_name }}</h1>
        <div class="candidate-title">Lập trình viên chuyên nghiệp</div>

        <!-- Mục tiêu nghề nghiệp -->
        @if($cvData->objective)
          <div class="page-break">
            <div class="right-section-title" style="margin-top: 0;">Mục tiêu nghề nghiệp</div>
            <div style="color: #475569; font-size: 11.5px;">
              {{ $cvData->objective }}
            </div>
          </div>
        @endif

        <!-- Kỹ năng chuyên môn -->
        @if($cvData->skills_text)
          <div class="page-break">
            <div class="right-section-title">Kỹ năng chuyên môn</div>
            <div style="margin-top: 5px;">
              @foreach(array_map('trim', explode(',', $cvData->skills_text)) as $skill)
                @if(!empty($skill))
                  <span class="skill-tag">{{ $skill }}</span>
                @endif
              @endforeach
            </div>
          </div>
        @endif

        <!-- Kinh nghiệm làm việc -->
        @if(!empty($cvData->experience) && count($cvData->experience) > 0)
          <div class="page-break">
            <div class="right-section-title">Kinh nghiệm làm việc</div>
            @foreach($cvData->experience as $exp)
              @if(!empty($exp['company']) || !empty($exp['role']))
                <div style="margin-bottom: 12px; page-break-inside: avoid;">
                  <table class="item-table">
                    <tr>
                      <td class="item-title">{{ $exp['role'] }}</td>
                      <td class="item-date">
                        {{ $exp['year_start'] ?? '' }} - {{ $exp['year_end'] ?? 'Hiện tại' }}
                      </td>
                    </tr>
                  </table>
                  <div class="item-subtitle">{{ $exp['company'] }}</div>
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
            <div class="right-section-title">Dự án nổi bật</div>
            @foreach($cvData->projects as $proj)
              @if(!empty($proj['name']))
                <div style="margin-bottom: 12px; page-break-inside: avoid;">
                  <table class="item-table">
                    <tr>
                      <td class="item-title">{{ $proj['name'] }}</td>
                      <td class="item-date">
                        @if(!empty($proj['url']))
                          <span style="font-size: 10px;">Link: {{ $proj['url'] }}</span>
                        @endif
                      </td>
                    </tr>
                  </table>
                  @if(!empty($proj['tech']))
                    <div class="item-subtitle">Công nghệ: {{ $proj['tech'] }}</div>
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
            <div class="right-section-title">Học vấn & Bằng cấp</div>
            @foreach($cvData->education as $edu)
              @if(!empty($edu['school']))
                <div style="margin-bottom: 10px; page-break-inside: avoid;">
                  <table class="item-table">
                    <tr>
                      <td class="item-title">{{ $edu['school'] }}</td>
                      <td class="item-date">
                        {{ $edu['year_start'] ?? '' }} - {{ $edu['year_end'] ?? '' }}
                      </td>
                    </tr>
                  </table>
                  <div class="item-subtitle">{{ $edu['degree'] }}</div>
                </div>
              @endif
            @endforeach
          </div>
        @endif
      </td>
    </tr>
  </table>

</body>
</html>
