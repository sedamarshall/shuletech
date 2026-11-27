<?php
defined('BASEPATH') or exit('No direct script access allowed');
$e = function ($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); };
$contact_email = 'info@shuletech.co.ke';
$asset_ver = '4';

// Brand logo: uses backend/images/shuletech-logo.png when present, otherwise the SVG mark + wordmark.
$brand = function ($light = false) use ($logo_url, $e) {
    if (!empty($logo_url) && !$light) {
        return '<img src="' . $e($logo_url) . '" alt="ShuleTech">';
    }
    return '<svg class="brand-mark" viewBox="0 0 44 40" aria-hidden="true"><use href="#i-logo"/></svg>'
        . '<span class="brand-word"><span class="s">Shule</span><span class="t">Tech</span></span>';
};
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ShuleTech — School management system for Kenyan schools</title>
  <meta name="description" content="ShuleTech brings admissions, fees, M-Pesa payments, exams, CBC report cards, attendance and parent communication together in one school management system built for Kenyan schools.">
  <meta name="theme-color" content="#092B63">
  <meta property="og:title" content="ShuleTech — Run your whole school with one smart system">
  <meta property="og:description" content="Admissions, fees, exams, attendance and parents in one place. Built for Kenyan schools.">
  <meta property="og:type" content="website">
  <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='14' fill='%23092B63'/%3E%3Cpath d='M32 13 9 24l23 11 23-11z' fill='%23fff'/%3E%3Cpath d='M17 30v8c0 4 7 8 15 8s15-4 15-8v-8l-15 7z' fill='%23fff'/%3E%3Cpath d='M10 50c12 6 32 5 44-6' stroke='%23FF7900' stroke-width='4' fill='none' stroke-linecap='round'/%3E%3C/svg%3E">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Inter+Tight:wght@600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo $e(base_url('backend/landing/landing.css?v=' . $asset_ver)); ?>">
</head>
<body>

<!-- Icon sprite -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <defs>
    <symbol id="i-logo" viewBox="0 0 44 40">
      <path d="M22 3 42 11 22 19 2 11z" fill="currentColor"/>
      <path d="M11 15v7c0 3 5 6 11 6s11-3 11-6v-7l-11 4.5z" fill="currentColor"/>
      <path d="M6 12.5v9" stroke="#FF7900" stroke-width="1.8" stroke-linecap="round"/>
      <path d="M4.6 21.5h2.8l.6 4h-4z" fill="#FF7900"/>
      <path d="M3 33c10 6 27 5 38-6" stroke="#FF7900" stroke-width="3.2" fill="none" stroke-linecap="round"/>
    </symbol>
    <symbol id="i-arrows" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 17 5-5-5-5M13 17l5-5-5-5"/></symbol>
    <symbol id="i-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="m8.5 12 2.5 2.5 4.5-5"/></symbol>
    <symbol id="i-tick" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5 9-10"/></symbol>
    <symbol id="i-cap" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></symbol>
    <symbol id="i-users" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></symbol>
    <symbol id="i-wallet" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7H5a2 2 0 0 1 0-4h13v4"/><path d="M3 5v14a2 2 0 0 0 2 2h15V7"/><path d="M16 14h.01"/></symbol>
    <symbol id="i-chart" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m7 15 4-4 3 3 5-6"/></symbol>
    <symbol id="i-book" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4h7a3 3 0 0 1 3 3v13a2 2 0 0 0-2-2H2z"/><path d="M22 4h-7a3 3 0 0 0-3 3v13a2 2 0 0 1 2-2h8z"/></symbol>
    <symbol id="i-cal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></symbol>
    <symbol id="i-phone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="2" width="12" height="20" rx="2"/><path d="M11 18h2"/></symbol>
    <symbol id="i-msg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></symbol>
    <symbol id="i-mail" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/></symbol>
    <symbol id="i-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></symbol>
    <symbol id="i-lock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></symbol>
    <symbol id="i-finger" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 10a2 2 0 0 0-2 2c0 1.02-.1 2.51-.26 4M14 13.12c0 2.38 0 6.38-1 8.88M17.29 21.02c.12-.6.43-2.3.5-3.02M2 12a10 10 0 0 1 18-6M2 16h.01M21.8 16c.2-2 .131-5.354 0-6M5 19.5C5.5 18 6 15 6 12a6 6 0 0 1 .34-2M8.65 22c.21-.66.45-1.32.57-2M9 6.8a6 6 0 0 1 9 5.2v2"/></symbol>
    <symbol id="i-shield" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></symbol>
    <symbol id="i-bell" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></symbol>
    <symbol id="i-home" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></symbol>
    <symbol id="i-bus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="15" rx="2"/><path d="M4 11h16M8 18v3M16 18v3"/></symbol>
    <symbol id="i-file" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h5"/></symbol>
    <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></symbol>
    <symbol id="i-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></symbol>
    <symbol id="i-up" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 15 6-6 6 6"/></symbol>
    <symbol id="i-help" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3M12 17h.01"/></symbol>
  </defs>
</svg>

<div class="loader" aria-hidden="true"><span>0%</span></div>

<header class="site-header">
  <div class="container">
    <nav class="nav" aria-label="Main">
      <a class="brand" href="#home" aria-label="ShuleTech home"><?php echo $brand(); ?></a>
      <ul class="nav-links">
        <li><a href="#home" class="active">Home</a></li>
        <li><a href="#features">Features</a></li>
        <li><a href="#pricing">Pricing</a></li>
        <li><a href="#faq">FAQ</a></li>
        <li><a href="#contact">Contact</a></li>
      </ul>
      <div class="nav-actions">
        <a class="nav-login" href="<?php echo $e($login_url); ?>">Log in</a>
        <a class="btn btn-navy" href="#contact"><span class="arr"><svg width="16" height="16"><use href="#i-arrows"/></svg></span>Book a free demo</a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-label="Open menu"><svg width="20" height="20"><use href="#i-menu"/></svg></button>
      </div>
    </nav>
  </div>
</header>

<main>
  <!-- Hero -->
  <section class="hero" id="home">
    <div class="container">
      <span class="eyebrow"><span class="tw" data-text="Built for Kenyan schools"></span></span>
      <h1>Run Your Whole School <span class="chip-icon" aria-hidden="true"><svg><use href="#i-cap"/></svg></span> with One Smart System</h1>
      <p class="hero-sub">Admissions, fees, exams, attendance and parents in one place for Kenyan schools. That means
        <span class="pill"><svg><use href="#i-users"/></svg>Admit</span>,
        <span class="pill"><svg><use href="#i-wallet"/></svg>Collect</span> and
        <span class="pill"><svg><use href="#i-chart"/></svg>Report</span>.
      </p>
      <div class="hero-cta">
        <a class="btn btn-lg" href="#contact"><span class="arr"><svg width="18" height="18"><use href="#i-arrows"/></svg></span>Get Started Free</a>
      </div>
      <div class="hero-tags">
        <span><svg><use href="#i-check"/></svg>M-PESA</span>
        <span><svg><use href="#i-check"/></svg>CBC REPORTS</span>
        <span><svg><use href="#i-check"/></svg>SMS &amp; WHATSAPP</span>
        <span><svg><use href="#i-check"/></svg>PARENT PORTAL</span>
      </div>

      <div class="mock-wrap">
        <div class="mock" aria-label="ShuleTech dashboard preview">
          <div class="mock-inner">
            <aside class="mock-side">
              <div class="brand"><svg class="brand-mark" viewBox="0 0 44 40"><use href="#i-logo"/></svg><span class="brand-word"><span class="s">Shule</span><span class="t">Tech</span></span></div>
              <a class="on"><svg><use href="#i-home"/></svg>Dashboard</a>
              <small>Academics</small>
              <a><svg><use href="#i-users"/></svg>Students</a>
              <a><svg><use href="#i-cal"/></svg>Attendance</a>
              <a><svg><use href="#i-file"/></svg>Exams &amp; Reports</a>
              <a><svg><use href="#i-book"/></svg>Homework</a>
              <small>Finance</small>
              <a><svg><use href="#i-wallet"/></svg>Fees Collection</a>
              <a><svg><use href="#i-chart"/></svg>Finance Reports</a>
              <small>Communicate</small>
              <a><svg><use href="#i-msg"/></svg>SMS &amp; Email</a>
            </aside>
            <div class="mock-main">
              <div class="mock-top"><b>Good morning, Principal</b><span class="mock-user"><span>Term 3 · 2026</span><span class="avatar">PW</span></span></div>
              <div class="mock-stats">
                <div class="mstat"><span>Learners</span><b>1,248</b><i>+36 this term</i></div>
                <div class="mstat"><span>Fees collected</span><b>KSh 8.6M</b><i>78% of target</i></div>
                <div class="mstat"><span>Attendance today</span><b>96.4%</b><i>+1.2% vs last week</i></div>
                <div class="mstat"><span>Staff</span><b>74</b><i>All present</i></div>
              </div>
              <div class="mock-grid">
                <div class="mcard">
                  <h4>Fee collection <span>Term 3 · weekly</span></h4>
                  <div class="bars"><div style="height:38%"></div><div style="height:52%"></div><div style="height:45%"></div><div style="height:68%"></div><div style="height:60%"></div><div class="hi" style="height:88%"></div><div style="height:72%"></div></div>
                  <div class="bars-x"><span>W1</span><span>W2</span><span>W3</span><span>W4</span><span>W5</span><span>W6</span><span>W7</span></div>
                </div>
                <div class="mcard">
                  <h4>Recent activity</h4>
                  <div class="row"><div>Fee paid · Grade 7 East<small>M-Pesa · KSh 15,000</small></div><em>Paid</em></div>
                  <div class="row"><div>Mid-term reports<small>Form 3 · 212 learners</small></div><em class="n">Ready</em></div>
                  <div class="row"><div>New admission<small>Grade 4 · Wanjiku M.</small></div><em class="o">Pending</em></div>
                  <div class="row"><div>SMS to parents<small>Closing date reminder</small></div><em>Sent</em></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Works with -->
  <section class="works" aria-label="Works with">
    <div class="container"><span class="eyebrow"><span class="tw" data-text="Works with"></span></span></div>
    <div class="marquee">
      <div class="marquee-track">
        <?php
        $chips = array(
            array('i-phone', 'M-Pesa'), array('i-msg', 'Bulk SMS'), array('i-msg', 'WhatsApp'),
            array('i-finger', 'Biometric attendance'), array('i-mail', 'Email'), array('i-wallet', 'Online payments'),
            array('i-file', 'CBC report cards'), array('i-cal', 'Calendar'),
        );
        for ($r = 0; $r < 2; $r++) {
            foreach ($chips as $c) {
                echo '<span class="logo-chip"' . ($r ? ' aria-hidden="true"' : '') . '><svg><use href="#' . $c[0] . '"/></svg>' . $e($c[1]) . '</span>';
            }
        }
        ?>
      </div>
    </div>
  </section>

  <!-- Why -->
  <section class="section why">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow"><span class="tw" data-text="Why ShuleTech"></span></span>
        <h2 class="h2 blur-words">Cut admin time, stop chasing fee balances, and keep every parent informed with one system built for Kenyan schools</h2>
        <p class="lead reveal">ShuleTech brings learners, classes, fees, exams and communication together, so heads, teachers and bursars spend less time on paperwork and more time on learning.</p>
        <div class="hero-cta reveal"><a class="btn" href="#contact"><span class="arr"><svg width="16" height="16"><use href="#i-arrows"/></svg></span>Start your free trial</a></div>
      </div>
      <div class="stats">
        <div class="stat reveal"><b><span data-count="15">0</span><span class="u">+</span></b><p>School modules in one system</p></div>
        <div class="stat reveal"><b><span data-count="1">0</span> <span class="u">tap</span></b><p>M-Pesa fee payment for parents</p></div>
        <div class="stat reveal"><b><span data-count="100">0</span><span class="u">%</span></b><p>Fee balances worked out automatically</p></div>
        <div class="stat reveal"><b><span data-count="24">0</span><span class="u">/7</span></b><p>Parent and student portal access</p></div>
      </div>
    </div>
  </section>

  <!-- Features (tabs) -->
  <section class="section" id="features" style="padding-top:20px">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow"><span class="tw" data-text="Core features"></span></span>
        <h2 class="h2 blur-words">Powerful Tools Built for Real School Work</h2>
      </div>
      <div class="tabs">
        <div class="tablist" role="tablist" aria-label="Features">
          <button class="tab" role="tab" id="t1" aria-controls="p1" aria-selected="true"><svg><use href="#i-users"/></svg>Students &amp; Admissions</button>
          <button class="tab" role="tab" id="t2" aria-controls="p2" aria-selected="false" tabindex="-1"><svg><use href="#i-wallet"/></svg>Fees &amp; M-Pesa</button>
          <button class="tab" role="tab" id="t3" aria-controls="p3" aria-selected="false" tabindex="-1"><svg><use href="#i-file"/></svg>Exams &amp; Reports</button>
        </div>
      </div>

      <div class="panel" role="tabpanel" id="p1" aria-labelledby="t1">
        <div class="panel-copy">
          <div class="icon-box"><svg><use href="#i-users"/></svg></div>
          <h3>Admit, Enrol and Track Every Learner from One Profile</h3>
          <ul class="checks">
            <li><svg><use href="#i-check"/></svg>Online admission forms parents fill from their phone</li>
            <li><svg><use href="#i-check"/></svg>Classes, streams, sessions and promotions in a few clicks</li>
            <li><svg><use href="#i-check"/></svg>Attendance by register or biometric device</li>
          </ul>
          <a class="btn btn-navy" href="#contact"><span class="arr"><svg width="16" height="16"><use href="#i-arrows"/></svg></span>Get started</a>
        </div>
        <div class="panel-visual">
          <div class="pv-title">Learner journey</div>
          <div class="steps">
            <div class="step ok"><span class="dot"><svg><use href="#i-tick"/></svg></span>Online application received</div>
            <div class="step ok"><span class="dot"><svg><use href="#i-tick"/></svg></span>Admitted · Grade 4 East</div>
            <div class="step ok"><span class="dot"><svg><use href="#i-tick"/></svg></span>Fee structure assigned · KSh 42,000</div>
            <div class="step next"><span class="dot"><svg><use href="#i-cal"/></svg></span>Reporting day · 6 Jan, 8:00 AM<span class="tag">Next</span></div>
            <div class="step"><span class="dot"><svg><use href="#i-msg"/></svg></span>Welcome SMS sent to parent</div>
          </div>
        </div>
      </div>

      <div class="panel" role="tabpanel" id="p2" aria-labelledby="t2" hidden>
        <div class="panel-copy">
          <div class="icon-box"><svg><use href="#i-wallet"/></svg></div>
          <h3>Collect Fees Faster and Always Know Who Owes What</h3>
          <ul class="checks">
            <li><svg><use href="#i-check"/></svg>Fee structures per class, term and category</li>
            <li><svg><use href="#i-check"/></svg>M-Pesa and online payments matched to the right learner</li>
            <li><svg><use href="#i-check"/></svg>Receipts, statements and balance reminders by SMS</li>
          </ul>
          <a class="btn btn-navy" href="#contact"><span class="arr"><svg width="16" height="16"><use href="#i-arrows"/></svg></span>Get started</a>
        </div>
        <div class="panel-visual">
          <div class="pv-title">Fee payment flow</div>
          <div class="steps">
            <div class="step ok"><span class="dot"><svg><use href="#i-tick"/></svg></span>Invoice · Term 3 · KSh 42,000</div>
            <div class="step ok"><span class="dot"><svg><use href="#i-tick"/></svg></span>M-Pesa payment · KSh 20,000</div>
            <div class="step ok"><span class="dot"><svg><use href="#i-tick"/></svg></span>Receipt sent to parent</div>
            <div class="step next"><span class="dot"><svg><use href="#i-bell"/></svg></span>Balance reminder · KSh 22,000<span class="tag">Next</span></div>
            <div class="step"><span class="dot"><svg><use href="#i-chart"/></svg></span>Bursar report updated</div>
          </div>
        </div>
      </div>

      <div class="panel" role="tabpanel" id="p3" aria-labelledby="t3" hidden>
        <div class="panel-copy">
          <div class="icon-box"><svg><use href="#i-file"/></svg></div>
          <h3>Enter Marks Once and Print Report Cards in Minutes</h3>
          <ul class="checks">
            <li><svg><use href="#i-check"/></svg>Exam schedules, mark entry and grading for every class</li>
            <li><svg><use href="#i-check"/></svg>CBC performance levels and 8-4-4 style mean grades</li>
            <li><svg><use href="#i-check"/></svg>Report cards parents can view and download online</li>
          </ul>
          <a class="btn btn-navy" href="#contact"><span class="arr"><svg width="16" height="16"><use href="#i-arrows"/></svg></span>Get started</a>
        </div>
        <div class="panel-visual">
          <div class="pv-title">End of term</div>
          <div class="steps">
            <div class="step ok"><span class="dot"><svg><use href="#i-tick"/></svg></span>Exam timetable published</div>
            <div class="step ok"><span class="dot"><svg><use href="#i-tick"/></svg></span>Marks entered · 14 subjects</div>
            <div class="step ok"><span class="dot"><svg><use href="#i-tick"/></svg></span>Ranks and grades worked out</div>
            <div class="step next"><span class="dot"><svg><use href="#i-file"/></svg></span>Report cards ready to print<span class="tag">Next</span></div>
            <div class="step"><span class="dot"><svg><use href="#i-msg"/></svg></span>Results shared with parents</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Dark bento -->
  <section class="section dark">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow"><span class="tw" data-text="Everything your school needs"></span></span>
        <h2 class="h2 blur-words">Collect Fees Faster, Stay Organised, and Keep Learner Data Safe</h2>
      </div>
      <div class="bento">
        <div class="bcard wide reveal" data-anim>
          <div class="chart-card">
            <b>KSh <span data-count="8">0</span>.6M</b><small>fees collected this term</small><span class="badge">TERM 3</span>
            <svg class="line" viewBox="0 0 300 130" preserveAspectRatio="none" aria-hidden="true">
              <defs><linearGradient id="lg" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#FF7900" stop-opacity=".45"/><stop offset="1" stop-color="#FF7900" stop-opacity="0"/></linearGradient></defs>
              <path d="M0 115 L40 100 L80 104 L120 78 L160 84 L200 52 L240 58 L300 18 L300 130 L0 130z" fill="url(#lg)"/>
              <path class="stroke" d="M0 115 L40 100 L80 104 L120 78 L160 84 L200 52 L240 58 L300 18" fill="none" stroke="#FF7900" stroke-width="3" stroke-linejoin="round"/>
            </svg>
          </div>
          <h3>Faster Fee Collection</h3>
          <p>M-Pesa payments, automatic balances and SMS reminders help parents pay on time.</p>
        </div>
        <div class="bcard wide reveal">
          <div class="tt-list">
            <div class="tt now"><svg><use href="#i-book"/></svg>Mathematics · Grade 8 East<time>08:00</time></div>
            <div class="tt"><svg><use href="#i-book"/></svg>English · Form 2 North<time>10:20</time></div>
            <div class="tt"><svg><use href="#i-book"/></svg>Integrated Science · Grade 7<time>14:00</time></div>
          </div>
          <h3>Organised Timetables</h3>
          <p>Classes, subjects, teachers and homework in one place for the whole school.</p>
        </div>
        <div class="bcard center reveal">
          <div class="glow-icon"><svg><use href="#i-shield"/></svg></div>
          <h3>Secure by Design</h3>
          <p>Role-based access, encrypted logins and daily backups of your school's records.</p>
        </div>
        <div class="bcard center reveal">
          <div class="glow-icon"><svg><use href="#i-users"/></svg></div>
          <h3>Connected Staff</h3>
          <p>Heads, teachers, bursars and librarians work from the same records.</p>
        </div>
        <div class="bcard center reveal" data-anim>
          <div class="ring">
            <svg viewBox="0 0 110 110" aria-hidden="true"><circle cx="55" cy="55" r="48" fill="none" stroke="rgba(255,255,255,.1)" stroke-width="8"/><circle class="p" cx="55" cy="55" r="48" fill="none" stroke="#FF7900" stroke-width="8" stroke-linecap="round"/></svg>
            <span><span data-count="100">0</span>%</span>
          </div>
          <h3>Automatic Reminders</h3>
          <p>Fee balances, events and results sent by SMS, email and WhatsApp.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Built for Kenyan schools -->
  <section class="section">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow"><span class="tw" data-text="Built for Kenyan schools"></span></span>
        <h2 class="h2 blur-words">Engineered for How Kenyan Schools Work</h2>
      </div>

      <div class="split">
        <div class="window reveal">
          <div class="window-bar"><i></i><i></i><i></i><span><svg width="11" height="11"><use href="#i-lock"/></svg>HTTPS</span></div>
          <div class="window-body">
            <div class="kv"><span>Curriculum</span><b>CBC · 8-4-4</b></div>
            <div class="kv"><span>Fees</span><b>KSh · per term</b></div>
            <div class="kv"><span>Payments</span><b>M-Pesa · Bank · Card</b></div>
            <div class="kv"><span>Learner ID</span><b>Admission no. · UPI</b></div>
            <div class="kv"><span>Parent alerts</span><code>SMS</code></div>
          </div>
        </div>
        <div class="info-card reveal">
          <h3>Local by Design</h3>
          <p>Shillings, terms, streams, CBC and M-Pesa are built in, not bolted on. Receipts, statements and report cards look the way Kenyan parents expect.</p>
          <div class="tag-list">
            <span><svg><use href="#i-check"/></svg>Terms &amp; streams</span>
            <span><svg><use href="#i-check"/></svg>M-Pesa receipts</span>
            <span><svg><use href="#i-check"/></svg>CBC levels</span>
          </div>
        </div>
      </div>

      <div class="split rev">
        <div class="window reveal">
          <div class="window-bar"><i></i><i></i><i></i><span><svg width="11" height="11"><use href="#i-lock"/></svg>HTTPS</span></div>
          <div class="window-body">
            <table class="report-table">
              <thead><tr><th>Learning area</th><th>Score</th><th>Level</th></tr></thead>
              <tbody>
                <tr><td>Mathematics</td><td>82</td><td><span class="lvl ee">EE</span></td></tr>
                <tr><td>English</td><td>71</td><td><span class="lvl me">ME</span></td></tr>
                <tr><td>Kiswahili</td><td>76</td><td><span class="lvl me">ME</span></td></tr>
                <tr><td>Integrated Science</td><td>58</td><td><span class="lvl ae">AE</span></td></tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="info-card reveal">
          <h3>Results Parents Can Understand</h3>
          <p>Teachers enter marks once. ShuleTech works out levels, means and ranks, and parents see the report card on their phone.</p>
          <div class="mini-stats">
            <div><b>1×</b><small>Mark entry</small></div>
            <div><b>CBC</b><small>Levels</small></div>
            <div><b>PDF</b><small>Report cards</small></div>
          </div>
        </div>
      </div>

      <div class="split">
        <div class="window reveal">
          <div class="window-bar"><i></i><i></i><i></i><span><svg width="11" height="11"><use href="#i-lock"/></svg>HTTPS</span></div>
          <div class="window-body tt-list" style="gap:10px">
            <div class="kv"><span>Closing date reminder</span><b>1,248 parents</b></div>
            <div class="kv"><span>Fee balance alerts</span><b>312 parents</b></div>
            <div class="kv"><span>Absent today</span><b>18 parents</b></div>
            <div class="kv"><span>Report cards ready</span><code>WhatsApp</code></div>
          </div>
        </div>
        <div class="info-card reveal">
          <h3>Keep Every Parent in the Loop</h3>
          <p>Send fee reminders, absence alerts, events and results to the whole school or one class, by SMS, email and WhatsApp.</p>
          <div class="tag-list">
            <span><svg><use href="#i-check"/></svg>Bulk SMS</span>
            <span><svg><use href="#i-check"/></svg>Parent portal</span>
            <span><svg><use href="#i-check"/></svg>Notice board</span>
            <span><svg><use href="#i-check"/></svg>WhatsApp</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Pricing -->
  <section class="section dark" id="pricing">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow"><span class="tw" data-text="Pricing"></span></span>
        <h2 class="h2 blur-words">Simple Pricing That Grows with Your School</h2>
        <p class="lead reveal">Every plan includes setup help, training for your staff and moving your existing records across.</p>
      </div>
      <div class="plans">
        <div class="plan reveal">
          <h3>Starter</h3>
          <p class="for">For small primary schools and academies.</p>
          <div class="price">Free trial <small>/ 30 days</small></div>
          <h4>WHAT’S INCLUDED</h4>
          <ul>
            <li><svg><use href="#i-check"/></svg>Up to 300 learners</li>
            <li><svg><use href="#i-check"/></svg>Students &amp; admissions</li>
            <li><svg><use href="#i-check"/></svg>Fees &amp; M-Pesa receipts</li>
            <li><svg><use href="#i-check"/></svg>Attendance</li>
            <li><svg><use href="#i-check"/></svg>Exams &amp; report cards</li>
          </ul>
          <a class="btn-ghost" href="#contact">Start free</a>
        </div>
        <div class="plan featured reveal">
          <h3>Standard</h3>
          <p class="for">For growing primary and secondary schools.</p>
          <div class="price">Custom <small>/ per term</small></div>
          <h4>EVERYTHING IN STARTER, PLUS</h4>
          <ul>
            <li><svg><use href="#i-check"/></svg>Up to 1,500 learners</li>
            <li><svg><use href="#i-check"/></svg>Parent &amp; student portal</li>
            <li><svg><use href="#i-check"/></svg>Bulk SMS &amp; email</li>
            <li><svg><use href="#i-check"/></svg>Homework &amp; timetables</li>
            <li><svg><use href="#i-check"/></svg>Finance reports</li>
          </ul>
          <a class="btn" href="#contact"><span class="arr"><svg width="16" height="16"><use href="#i-arrows"/></svg></span>Get a quote</a>
        </div>
        <div class="plan reveal">
          <h3>Premium</h3>
          <p class="for">For large schools and groups of schools.</p>
          <div class="price">Custom <small>/ per term</small></div>
          <h4>EVERYTHING IN STANDARD, PLUS</h4>
          <ul>
            <li><svg><use href="#i-check"/></svg>Unlimited learners</li>
            <li><svg><use href="#i-check"/></svg>Biometric attendance</li>
            <li><svg><use href="#i-check"/></svg>Online admissions website</li>
            <li><svg><use href="#i-check"/></svg>Transport &amp; hostel</li>
            <li><svg><use href="#i-check"/></svg>Priority support</li>
          </ul>
          <a class="btn-ghost" href="#contact">Talk to sales</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Roles -->
  <section class="section">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow"><span class="tw" data-text="Made for your whole school"></span></span>
        <h2 class="h2 blur-words">Built for Every Role in Your School</h2>
      </div>
      <div class="roles">
        <div class="role reveal">
          <p>See enrolment, attendance, fee collection and results at a glance, from one dashboard.</p>
          <div class="who"><div class="icon-box"><svg width="20" height="20"><use href="#i-chart"/></svg></div><div><b>Principals</b><small>OVERSIGHT</small></div></div>
        </div>
        <div class="role reveal">
          <p>Mark the register, set homework and enter exam marks without leaving one screen.</p>
          <div class="who"><div class="icon-box"><svg width="20" height="20"><use href="#i-book"/></svg></div><div><b>Teachers</b><small>DAILY WORK</small></div></div>
        </div>
        <div class="role reveal">
          <p>Record M-Pesa payments, print receipts and send balance reminders in a few clicks.</p>
          <div class="who"><div class="icon-box"><svg width="20" height="20"><use href="#i-wallet"/></svg></div><div><b>Bursars</b><small>FINANCE</small></div></div>
        </div>
        <div class="role reveal">
          <p>Check fee balances, attendance, homework and report cards from any phone.</p>
          <div class="who"><div class="icon-box"><svg width="20" height="20"><use href="#i-users"/></svg></div><div><b>Parents</b><small>PORTAL</small></div></div>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="section" id="faq" style="padding-top:20px">
    <div class="container">
      <div class="section-head">
        <span class="eyebrow"><span class="tw" data-text="FAQ"></span></span>
        <h2 class="h2 blur-words">Frequently Asked Questions</h2>
      </div>
      <div class="faq-grid">
        <div class="help-card reveal">
          <div class="icon-box"><svg width="22" height="22"><use href="#i-help"/></svg></div>
          <h3>Need guidance?</h3>
          <p>We help schools get set up and trained in days, not weeks.</p>
          <a class="btn" href="#contact"><span class="arr"><svg width="16" height="16"><use href="#i-arrows"/></svg></span>Talk to us</a>
        </div>
        <div class="faq">
          <details open>
            <summary>What is ShuleTech used for?<span class="pm"><svg width="16" height="16"><use href="#i-plus"/></svg></span></summary>
            <p>ShuleTech is a school management system. It covers admissions, student records, attendance, fees, exams and report cards, homework, timetables, staff and communication with parents, all in one place.</p>
          </details>
          <details>
            <summary>Can parents pay school fees with M-Pesa?<span class="pm"><svg width="16" height="16"><use href="#i-plus"/></svg></span></summary>
            <p>Yes. Fee payments are recorded against the right learner, receipts are generated automatically and balances update straight away.</p>
          </details>
          <details>
            <summary>Does it support CBC?<span class="pm"><svg width="16" height="16"><use href="#i-plus"/></svg></span></summary>
            <p>Yes. You can grade learners with CBC performance levels and still use mean grades and ranks for classes that need them.</p>
          </details>
          <details>
            <summary>Can parents and students log in?<span class="pm"><svg width="16" height="16"><use href="#i-plus"/></svg></span></summary>
            <p>Yes. Parents and students get their own portal to see fee statements, attendance, homework, notices and report cards.</p>
          </details>
          <details>
            <summary>Is our school's data safe?<span class="pm"><svg width="16" height="16"><use href="#i-plus"/></svg></span></summary>
            <p>Each user only sees what their role allows, logins are protected, and your records are backed up regularly.</p>
          </details>
          <details>
            <summary>Can you move our existing records across?<span class="pm"><svg width="16" height="16"><use href="#i-plus"/></svg></span></summary>
            <p>Yes. Send us your learner lists and fee balances in Excel and we will import them for you during setup.</p>
          </details>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact -->
  <section class="section" id="contact" style="padding-top:20px">
    <div class="container contact-grid">
      <div>
        <span class="eyebrow"><span class="tw" data-text="Get in touch"></span></span>
        <h2 class="h2 blur-words" style="margin-top:20px">Talk to the ShuleTech Team</h2>
        <p class="lead reveal">Questions about plans, a demo for your board, or moving your records across? Send us a message and we will get back to you within one working day.</p>
        <div class="contact-items">
          <a class="citem reveal" href="mailto:<?php echo $e($contact_email); ?>"><span class="icon-box"><svg><use href="#i-mail"/></svg></span><span><small>EMAIL</small><b><?php echo $e($contact_email); ?></b></span></a>
          <div class="citem reveal"><span class="icon-box"><svg><use href="#i-pin"/></svg></span><span><small>OFFICE</small><b>Kenya</b></span></div>
        </div>
      </div>
      <form class="form reveal" id="contact-form" data-to="<?php echo $e($contact_email); ?>">
        <div class="field"><label for="cf-name">Full name</label><input id="cf-name" name="name" required autocomplete="name"></div>
        <div class="field"><label for="cf-school">School name</label><input id="cf-school" name="school" required autocomplete="organization"></div>
        <div class="field"><label for="cf-email">Email</label><input id="cf-email" name="email" type="email" required autocomplete="email"></div>
        <div class="field"><label for="cf-learners">Number of learners</label>
          <select id="cf-learners" name="learners"><option>Under 300</option><option>300 – 1,000</option><option>1,000 – 2,500</option><option>Over 2,500</option></select>
        </div>
        <div class="field full"><label for="cf-subject">Subject</label><input id="cf-subject" name="subject" placeholder="e.g. Demo for a 600-learner primary school"></div>
        <div class="field full"><label for="cf-message">Message</label><textarea id="cf-message" name="message" required></textarea></div>
        <input type="text" name="website" tabindex="-1" autocomplete="off" class="sr-only" aria-hidden="true">
        <button class="btn" type="submit"><span class="arr"><svg width="16" height="16"><use href="#i-arrows"/></svg></span>Send message</button>
        <p class="form-note">We only use your details to reply to you.</p>
      </form>
    </div>
  </section>

  <!-- CTA -->
  <section class="cta-band">
    <div class="container">
      <div class="cta-box reveal">
        <span class="eyebrow"><span class="tw" data-text="Get started today"></span></span>
        <h2 class="h2" style="margin-top:20px">Start Running Your School Smarter Today with ShuleTech</h2>
        <div class="hero-cta">
          <a class="btn btn-lg" href="#contact"><span class="arr"><svg width="18" height="18"><use href="#i-arrows"/></svg></span>Start free trial</a>
          <a class="btn-ghost" href="<?php echo $e($login_url); ?>">Log in</a>
        </div>
      </div>
    </div>
  </section>
</main>

<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <a class="brand" href="#home"><?php echo $brand(true); ?></a>
        <p>Smart school management for Kenyan schools: admissions, fees, exams, attendance and parents in one place.</p>
        <div class="fcontact">
          <span><svg><use href="#i-pin"/></svg>Kenya</span>
          <span><svg><use href="#i-mail"/></svg><a href="mailto:<?php echo $e($contact_email); ?>"><?php echo $e($contact_email); ?></a></span>
        </div>
      </div>
      <div>
        <h4>Main menu</h4>
        <ul><li><a href="#home">Home</a></li><li><a href="#features">Features</a></li><li><a href="#pricing">Pricing</a></li><li><a href="#faq">FAQ</a></li><li><a href="#contact">Contact</a></li></ul>
      </div>
      <div>
        <h4>Account</h4>
        <ul>
          <li><a href="<?php echo $e($login_url); ?>">Staff log in</a></li>
          <li><a href="<?php echo $e($user_login_url); ?>">Parent &amp; student log in</a></li>
          <li><a href="#contact">Start free trial</a></li>
          <li><a href="<?php echo $e(site_url('site/forgotpassword')); ?>">Forgot password</a></li>
        </ul>
      </div>
      <div>
        <h4>Company</h4>
        <ul><li><a href="#features">About us</a></li><li><a href="#contact">Contact us</a></li><li><a href="#faq">FAQ</a></li></ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <span id="year"><?php echo $e($year); ?></span> ShuleTech. All rights reserved.</span>
      <a class="to-top" href="#home"><svg width="16" height="16"><use href="#i-up"/></svg>Back to top</a>
    </div>
  </div>
</footer>

<script src="<?php echo $e(base_url('backend/landing/landing.js?v=' . $asset_ver)); ?>"></script>
</body>
</html>
