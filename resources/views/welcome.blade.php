<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ITWorks — Nền tảng tuyển dụng IT hàng đầu Việt Nam</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <style>
    body { background: var(--bg-dark); }
    .landing-nav {
      position: fixed; top: 0; left: 0; right: 0; z-index: 200;
      padding: 18px 0;
      transition: all 0.3s;
    }
    .landing-nav.scrolled {
      background: rgba(11,15,26,0.95);
      backdrop-filter: blur(16px);
      border-bottom: 1px solid rgba(255,255,255,0.08);
      padding: 12px 0;
    }
    .landing-nav .navbar-brand { color: #fff; }
    .landing-nav .nav-links a { color: rgba(255,255,255,0.6); font-size: 14px; font-weight: 500; padding: 6px 14px; border-radius: var(--r-sm); transition: var(--t-fast); }
    .landing-nav .nav-links a:hover { color: #fff; background: rgba(255,255,255,0.08); }

    /* ── HERO ── */
    .landing-hero {
      min-height: 100vh;
      display: flex;
      align-items: center;
      position: relative;
      overflow: hidden;
      background: var(--bg-dark);
      background-image:
        radial-gradient(ellipse 90% 70% at 50% -10%, rgba(0,217,126,0.22) 0%, transparent 65%),
        radial-gradient(ellipse 50% 60% at 90% 80%, rgba(59,130,246,0.1) 0%, transparent 55%),
        radial-gradient(ellipse 40% 50% at 10% 60%, rgba(139,92,246,0.07) 0%, transparent 50%);
      padding-top: 80px;
    }
    .hero-grid {
      display: grid;
      grid-template-columns: 1fr;
      max-width: 820px;
      margin: 0 auto;
      text-align: center;
    }
    .hero-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(0,217,126,0.1);
      border: 1px solid rgba(0,217,126,0.25);
      border-radius: var(--r-full);
      padding: 7px 18px;
      font-size: 13px;
      font-weight: 600;
      color: var(--primary);
      margin-bottom: 28px;
    }
    .hero-eyebrow .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--primary); animation: pulse 2s infinite; }
    .landing-hero h1 {
      font-family: var(--font-display);
      font-size: clamp(38px, 5.5vw, 64px);
      font-weight: 900;
      line-height: 1.1;
      letter-spacing: -2px;
      color: #fff;
      margin-bottom: 22px;
    }
    .landing-hero h1 .grad {
      background: linear-gradient(135deg, #00D97E 0%, #5EFFC0 50%, #3B82F6 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .landing-hero .subtitle {
      font-size: 18px;
      color: rgba(255,255,255,0.5);
      max-width: 580px;
      margin: 0 auto 40px;
      line-height: 1.7;
      font-weight: 400;
    }
    .hero-cta-group {
      display: flex;
      gap: 14px;
      justify-content: center;
      flex-wrap: wrap;
      margin-bottom: 60px;
    }
    .hero-cta-group .btn-hero-primary {
      display: inline-flex; align-items: center; gap: 9px;
      padding: 15px 32px;
      background: linear-gradient(135deg, var(--primary) 0%, #00C170 100%);
      color: #fff; font-size: 15px; font-weight: 700;
      border-radius: var(--r-xl); border: none; cursor: pointer;
      box-shadow: 0 6px 28px rgba(0,217,126,0.4);
      transition: var(--t-base); text-decoration: none;
    }
    .hero-cta-group .btn-hero-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 40px rgba(0,217,126,0.5); color: #fff; }
    .hero-cta-group .btn-hero-outline {
      display: inline-flex; align-items: center; gap: 9px;
      padding: 14px 30px;
      background: rgba(255,255,255,0.07);
      border: 1.5px solid rgba(255,255,255,0.2);
      color: rgba(255,255,255,0.85); font-size: 15px; font-weight: 600;
      border-radius: var(--r-xl); cursor: pointer;
      backdrop-filter: blur(8px);
      transition: var(--t-base); text-decoration: none;
    }
    .hero-cta-group .btn-hero-outline:hover { background: rgba(255,255,255,0.12); border-color: rgba(255,255,255,0.35); transform: translateY(-2px); color: #fff; }

    /* Stats strip */
    .hero-stats-strip {
      display: flex;
      gap: 0;
      justify-content: center;
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: var(--r-2xl);
      backdrop-filter: blur(12px);
      overflow: hidden;
      max-width: 620px;
      margin: 0 auto;
    }
    .hero-stat {
      flex: 1;
      padding: 22px 20px;
      text-align: center;
      border-right: 1px solid rgba(255,255,255,0.07);
    }
    .hero-stat:last-child { border-right: none; }
    .hero-stat__num { font-family: var(--font-display); font-size: 28px; font-weight: 800; color: #fff; letter-spacing: -0.5px; }
    .hero-stat__num span { color: var(--primary); }
    .hero-stat__label { font-size: 11.5px; color: rgba(255,255,255,0.4); font-weight: 500; text-transform: uppercase; letter-spacing: 0.08em; margin-top: 3px; }

    /* ── FEATURES ── */
    .features-section {
      background: var(--bg-gray);
      padding: 96px 0;
    }
    .features-header { text-align: center; margin-bottom: 60px; }
    .features-header .overline {
      display: inline-block;
      font-size: 12px; font-weight: 700;
      text-transform: uppercase; letter-spacing: 0.12em;
      color: var(--primary);
      margin-bottom: 14px;
    }
    .features-header h2 {
      font-family: var(--font-display);
      font-size: clamp(26px, 3.5vw, 40px);
      font-weight: 900;
      color: var(--text-dark);
      letter-spacing: -1px;
      line-height: 1.2;
      margin-bottom: 14px;
    }
    .features-header p { font-size: 16px; color: var(--text-secondary); max-width: 520px; margin: 0 auto; line-height: 1.7; }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
    }
    .feature-card {
      background: #fff;
      border-radius: var(--r-xl);
      padding: 32px;
      border: 1px solid var(--border);
      box-shadow: var(--shadow-xs);
      transition: var(--t-base);
      position: relative;
      overflow: hidden;
    }
    .feature-card::after {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 2px;
      background: transparent;
      transition: var(--t-base);
    }
    .feature-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-4px); border-color: rgba(0,217,126,0.2); }
    .feature-card:hover::after { background: linear-gradient(90deg, var(--primary), var(--accent-blue)); }
    .feature-icon {
      width: 56px; height: 56px;
      border-radius: var(--r-md);
      display: flex; align-items: center; justify-content: center;
      font-size: 24px;
      margin-bottom: 20px;
    }
    .feature-icon--green { background: var(--primary-light); color: var(--primary-dark); }
    .feature-icon--blue  { background: #EFF6FF; color: var(--accent-blue); }
    .feature-icon--purple { background: #F5F3FF; color: var(--accent-purple); }
    .feature-card h3 { font-family: var(--font-display); font-size: 17px; font-weight: 700; color: var(--text-dark); margin-bottom: 8px; letter-spacing: -0.2px; }
    .feature-card p { font-size: 14px; color: var(--text-secondary); line-height: 1.7; }

    /* ── HOW IT WORKS ── */
    .how-section { background: var(--bg-dark); padding: 96px 0; position: relative; overflow: hidden; }
    .how-section::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(ellipse 70% 60% at 50% 100%, rgba(0,217,126,0.1) 0%, transparent 60%);
    }
    .how-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px; position: relative; z-index: 1; }
    .how-step { text-align: center; }
    .how-step__num {
      width: 52px; height: 52px;
      border-radius: 50%;
      background: rgba(0,217,126,0.12);
      border: 2px solid rgba(0,217,126,0.3);
      color: var(--primary);
      font-family: var(--font-display);
      font-size: 20px;
      font-weight: 800;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 20px;
    }
    .how-step h3 { font-family: var(--font-display); font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 10px; }
    .how-step p { font-size: 14px; color: rgba(255,255,255,0.5); line-height: 1.7; max-width: 240px; margin: 0 auto; }

    /* ── CTA BAND ── */
    .cta-band {
      background: linear-gradient(135deg, #00D97E 0%, #00A85F 40%, #0059D1 100%);
      padding: 72px 0;
      position: relative;
      overflow: hidden;
    }
    .cta-band::before {
      content: '';
      position: absolute; inset: 0;
      background-image: linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
                        linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
      background-size: 32px 32px;
    }
    .cta-content { text-align: center; position: relative; z-index: 1; }
    .cta-content h2 { font-family: var(--font-display); font-size: clamp(28px, 4vw, 44px); font-weight: 900; color: #fff; letter-spacing: -1px; margin-bottom: 16px; }
    .cta-content p { font-size: 17px; color: rgba(255,255,255,0.75); margin-bottom: 36px; }
    .cta-buttons { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
    .cta-buttons .btn-white {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 14px 30px;
      background: #fff; color: #00A85F;
      font-size: 15px; font-weight: 700;
      border-radius: var(--r-xl); border: none;
      transition: var(--t-base); text-decoration: none;
    }
    .cta-buttons .btn-white:hover { transform: translateY(-2px); box-shadow: 0 10px 32px rgba(0,0,0,0.25); color: #008A50; }
    .cta-buttons .btn-outline-white {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 13px 28px;
      background: rgba(255,255,255,0.12);
      border: 1.5px solid rgba(255,255,255,0.35);
      color: #fff; font-size: 15px; font-weight: 600;
      border-radius: var(--r-xl);
      transition: var(--t-base); text-decoration: none; backdrop-filter: blur(8px);
    }
    .cta-buttons .btn-outline-white:hover { background: rgba(255,255,255,0.2); transform: translateY(-2px); color: #fff; }

    @media (max-width: 768px) {
      .features-grid, .how-grid { grid-template-columns: 1fr; }
      .hero-stats-strip { flex-wrap: wrap; }
    }
  </style>
</head>
<body>

{{-- NAV --}}
<nav class="landing-nav" id="landing-nav">
  <div class="container navbar-inner">
    <a href="{{ url('/') }}" class="navbar-brand">IT<span>Works</span></a>
    <div class="nav-links flex gap-4">
      <a href="{{ url('/job') }}">Tìm việc</a>
      <a href="#">Nhà tuyển dụng</a>
    </div>
    <div class="flex gap-8">
      @auth
        <a href="{{ url('/dashboard') }}" class="btn btn-ghost btn-sm">Dashboard</a>
      @else
        <a href="{{ url('/login') }}" class="btn btn-ghost btn-sm" style="color:rgba(255,255,255,0.7);border-color:rgba(255,255,255,0.2)">Đăng nhập</a>
        <a href="{{ url('/register') }}" class="btn btn-primary btn-sm">Đăng ký miễn phí</a>
      @endauth
    </div>
  </div>
</nav>

{{-- HERO --}}
<section class="landing-hero">
  <div class="container">
    <div class="hero-grid">
      <div>
        <div class="hero-eyebrow">
          <span class="dot"></span>
          Hơn 2,500+ vị trí IT đang tuyển dụng
        </div>
        <h1>
          Tìm việc IT<br>
          <span class="grad">nhanh hơn, dễ hơn</span>
        </h1>
        <p class="subtitle">
          Kết nối nhân tài công nghệ với hàng nghìn cơ hội từ các công ty hàng đầu Việt Nam và quốc tế.
        </p>
        <div class="hero-cta-group">
          <a href="{{ url('/job') }}" class="btn-hero-primary">
            <i class="fas fa-search"></i> Khám phá việc làm
          </a>
          @guest
          <a href="{{ url('/register') }}" class="btn-hero-outline">
            <i class="fas fa-user-plus"></i> Tạo hồ sơ miễn phí
          </a>
          @endguest
        </div>
        <div class="hero-stats-strip">
          <div class="hero-stat">
            <div class="hero-stat__num">2.5<span>K</span>+</div>
            <div class="hero-stat__label">Việc làm</div>
          </div>
          <div class="hero-stat">
            <div class="hero-stat__num">500<span>+</span></div>
            <div class="hero-stat__label">Công ty</div>
          </div>
          <div class="hero-stat">
            <div class="hero-stat__num">10<span>K</span>+</div>
            <div class="hero-stat__label">Ứng viên</div>
          </div>
          <div class="hero-stat">
            <div class="hero-stat__num">98<span>%</span></div>
            <div class="hero-stat__label">Hài lòng</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- FEATURES --}}
<section class="features-section">
  <div class="container">
    <div class="features-header">
      <div class="overline">Tại sao chọn ITWorks?</div>
      <h2>Mọi thứ bạn cần để<br>bứt phá sự nghiệp IT</h2>
      <p>Từ tìm kiếm việc làm đến xây dựng CV chuyên nghiệp — tất cả trong một nền tảng.</p>
    </div>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon feature-icon--green"><i class="fas fa-search"></i></div>
        <h3>Tìm kiếm thông minh</h3>
        <p>Lọc việc làm theo kỹ năng, mức lương, địa điểm và hình thức làm việc. AI gợi ý vị trí phù hợp nhất với bạn.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon feature-icon--blue"><i class="fas fa-file-alt"></i></div>
        <h3>CV Builder chuyên nghiệp</h3>
        <p>Tạo CV ấn tượng với nhiều mẫu hiện đại. Xuất PDF chỉ trong vài giây, được nhà tuyển dụng đánh giá cao.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon feature-icon--purple"><i class="fas fa-bell"></i></div>
        <h3>Thông báo thời gian thực</h3>
        <p>Nhận ngay thông báo khi có việc làm mới phù hợp hoặc khi nhà tuyển dụng phản hồi đơn ứng tuyển của bạn.</p>
      </div>
    </div>
  </div>
</section>

{{-- HOW IT WORKS --}}
<section class="how-section">
  <div class="container">
    <div class="features-header" style="color:#fff">
      <div class="overline">Đơn giản — Nhanh chóng</div>
      <h2 style="color:#fff">Bắt đầu chỉ trong 3 bước</h2>
      <p style="color:rgba(255,255,255,0.5)">Từ đăng ký đến nhận offer — trung bình chỉ mất 2 tuần.</p>
    </div>
    <div class="how-grid">
      <div class="how-step">
        <div class="how-step__num">01</div>
        <h3>Tạo tài khoản</h3>
        <p>Đăng ký miễn phí trong 60 giây. Kết nối Google hoặc GitHub cho nhanh hơn.</p>
      </div>
      <div class="how-step">
        <div class="how-step__num">02</div>
        <h3>Xây dựng hồ sơ</h3>
        <p>Upload CV hoặc tạo mới với builder. Thêm kỹ năng để được gợi ý việc làm phù hợp.</p>
      </div>
      <div class="how-step">
        <div class="how-step__num">03</div>
        <h3>Ứng tuyển & Offer</h3>
        <p>Nộp đơn một click. Theo dõi trạng thái và nhận thông báo phản hồi ngay lập tức.</p>
      </div>
    </div>
  </div>
</section>

{{-- CTA BAND --}}
<section class="cta-band">
  <div class="container">
    <div class="cta-content">
      <h2>Cơ hội đang chờ bạn 🚀</h2>
      <p>Hàng nghìn việc làm IT hấp dẫn đang được cập nhật mỗi ngày.</p>
      <div class="cta-buttons">
        <a href="{{ url('/job') }}" class="btn-white"><i class="fas fa-briefcase"></i> Xem việc làm ngay</a>
        @guest
        <a href="{{ url('/register') }}" class="btn-outline-white"><i class="fas fa-user-plus"></i> Đăng ký miễn phí</a>
        @endguest
        @auth
        <a href="{{ url('/dashboard') }}" class="btn-outline-white"><i class="fas fa-chart-bar"></i> Dashboard của tôi</a>
        @endauth
      </div>
    </div>
  </div>
</section>

{{-- FOOTER --}}
<footer class="footer">
  <div class="container">
    <div class="grid-4" style="grid-template-columns:2fr 1fr 1fr 1fr;gap:48px">
      <div>
        <div class="footer-logo">IT<span>Works</span></div>
        <p style="font-size:13.5px;color:rgba(255,255,255,.45);line-height:1.75;margin-bottom:22px;max-width:280px">Nền tảng tuyển dụng IT hàng đầu Việt Nam. Kết nối nhân tài và doanh nghiệp công nghệ.</p>
        <div class="footer-social">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-linkedin-in"></i></a>
          <a href="#"><i class="fab fa-youtube"></i></a>
          <a href="#"><i class="fab fa-github"></i></a>
        </div>
      </div>
      <div>
        <h4>Ứng viên</h4>
        <ul>
          <li><a href="{{ url('/job') }}">Tìm việc làm</a></li>
          <li><a href="{{ url('/user/cv/create') }}">Tạo CV online</a></li>
          <li><a href="{{ url('/user/cv') }}">Upload CV</a></li>
        </ul>
      </div>
      <div>
        <h4>Nhà tuyển dụng</h4>
        <ul>
          <li><a href="{{ url('/job/create') }}">Đăng tin tuyển dụng</a></li>
          <li><a href="{{ url('/applicants') }}">Quản lý ứng viên</a></li>
          <li><a href="{{ route('payment.subscription') }}">Gói premium</a></li>
        </ul>
      </div>
      <div>
        <h4>Hỗ trợ</h4>
        <ul>
          <li><a href="#">Trung tâm hỗ trợ</a></li>
          <li><a href="{{ url('/legal/terms') }}">Điều khoản dịch vụ</a></li>
          <li><a href="{{ url('/legal/privacy') }}">Chính sách bảo mật</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© {{ date('Y') }} ITWorks. All rights reserved.</span>
      <span>Made with ❤️ in Vietnam</span>
    </div>
  </div>
</footer>

<script>
window.addEventListener('scroll', function() {
  var nav = document.getElementById('landing-nav');
  nav.classList.toggle('scrolled', window.scrollY > 40);
});
</script>
</body>
</html>
