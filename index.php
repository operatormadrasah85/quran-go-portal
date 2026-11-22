<?php
require_once __DIR__ . '/config.php';
$settings = ag_load_settings();
$appName = $settings['app_name'];
$demoUrl = trim((string)$settings['demo_url']) !== '' ? $settings['demo_url'] : './demo.php';
$isExternalDemo = (strpos($demoUrl, 'http://') === 0 || strpos($demoUrl, 'https://') === 0);
$demoTargetAttr = $isExternalDemo ? ' target="_blank" rel="noopener"' : '';
$waAskUrl = ag_wa_link($settings['wa_number'], $settings['wa_message_ask']);
$priceNormal = ag_rupiah($settings['price_normal']);
$pricePromo = ag_rupiah($settings['price_promo']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#142B5E" />
  <meta name="description" content="Al-Qur'an Go · 30 Juz — Game hafalan dan murojaah Al-Qur'an untuk anak. Bikin hafalan terasa seperti permainan, fresh bukan stress! Bisa offline & sekali bayar." />
  <title><?php echo ag_h($appName); ?> · 30 Juz — Bikin Hafalan Terasa Seperti Permainan</title>
  <link rel="icon" type="image/svg+xml" href="./icon.svg" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@500;600;700;800;900&family=Scheherazade+New:wght@400;700&display=swap" rel="stylesheet" />

  <style>
    :root {
      --navy: #142B5E;
      --navy-soft: #1E3A7A;
      --sky-1: #62BAFA;
      --sky-2: #B8E5FF;
      --sky-pale: #F0F9FF;
      --green-1: #5AC82A;
      --green-2: #3E9F15;
      --green-pale: #F0FDF4;
      --gold-1: #FFE138;
      --gold-2: #F59E0B;
      --red-ribbon: #E5252A;
      --cream: #FFFDF5;
      --text: #1E293B;
      --muted: #64748B;
      --border: #E2E8F0;
      --font-ui: 'Nunito', sans-serif;
      --font-display: 'Fredoka', sans-serif;
      --font-arabic: 'Scheherazade New', serif;
      --shadow-sm: 0 4px 14px rgba(20, 43, 94, 0.08);
      --shadow-md: 0 12px 32px rgba(20, 43, 94, 0.12);
      --shadow-lg: 0 22px 50px rgba(20, 43, 94, 0.18);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: var(--font-ui);
      color: var(--text);
      background: #FFFFFF;
      line-height: 1.65;
      overflow-x: clip;
    }

    a {
      color: inherit;
      text-decoration: none;
    }

    .container {
      width: 100%;
      max-width: 1120px;
      margin: 0 auto;
      padding: 0 20px;
    }

    /* ================= NAVBAR STICKY ================= */
    .navbar {
      position: sticky;
      top: 0;
      z-index: 100;
      background: rgba(255, 255, 255, 0.92);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(20, 43, 94, 0.08);
    }

    .nav-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      height: 70px;
    }

    .nav-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      font-family: var(--font-display);
      font-weight: 700;
      font-size: 22px;
      color: var(--navy);
    }

    .nav-brand img {
      width: 38px;
      height: 38px;
      border-radius: 10px;
      box-shadow: 0 3px 8px rgba(20, 43, 94, 0.16);
    }

    .nav-links {
      display: none;
      align-items: center;
      gap: 24px;
      font-weight: 800;
      font-size: 14.5px;
      color: #334155;
    }

    .nav-links a:hover {
      color: var(--green-2);
    }

    .nav-actions {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      font-family: var(--font-ui);
      font-weight: 900;
      font-size: 14.5px;
      padding: 11px 20px;
      border-radius: 14px;
      border: none;
      cursor: pointer;
      transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .btn:active {
      transform: translateY(2px);
    }

    .btn-green {
      background: linear-gradient(180deg, #62D42F 0%, #44A71A 100%);
      color: #FFFFFF;
      box-shadow: 0 4px 0 #2E7A0F, 0 8px 20px rgba(68, 167, 26, 0.25);
    }

    .btn-green:hover {
      filter: brightness(1.04);
    }

    .btn-gold {
      background: linear-gradient(180deg, #FFE852 0%, #FFB700 100%);
      color: var(--navy);
      box-shadow: 0 4px 0 #C27803, 0 8px 20px rgba(245, 158, 11, 0.28);
    }

    .btn-outline {
      background: #FFFFFF;
      color: var(--navy);
      border: 2px solid #CBD5E1;
    }

    .btn-outline:hover {
      border-color: var(--navy);
    }

    .btn-sm {
      padding: 8px 14px;
      font-size: 13px;
      border-radius: 11px;
    }

    .menu-toggle {
      display: inline-flex;
      width: 40px;
      height: 40px;
      border-radius: 10px;
      border: 2px solid var(--border);
      background: #FFFFFF;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      color: var(--navy);
      cursor: pointer;
    }

    .mobile-drawer {
      display: none;
      flex-direction: column;
      gap: 10px;
      padding: 14px 20px 20px;
      background: #FFFFFF;
      border-bottom: 2px solid var(--border);
    }

    .mobile-drawer.open {
      display: flex;
    }

    .mobile-drawer a {
      font-weight: 800;
      padding: 8px 4px;
      color: var(--navy);
      border-bottom: 1px dashed var(--border);
    }

    /* ================= HERO SECTION ================= */
    .hero {
      position: relative;
      background: linear-gradient(180deg, #6FC3FA 0%, #C7EAFF 64%, #F0FDF4 100%);
      padding: 44px 0 68px;
      overflow: hidden;
    }

    .hero::before,
    .hero::after {
      content: "";
      position: absolute;
      background: rgba(255, 255, 255, 0.42);
      border-radius: 999px;
      pointer-events: none;
    }

    .hero::before { width: 260px; height: 74px; top: 34px; left: -60px; }
    .hero::after  { width: 320px; height: 84px; top: 96px; right: -80px; }

    .hero-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 40px;
      align-items: center;
      position: relative;
      z-index: 2;
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: #FFFFFF;
      color: var(--navy);
      padding: 6px 14px;
      border-radius: 999px;
      font-weight: 900;
      font-size: 13px;
      box-shadow: var(--shadow-sm);
      margin-bottom: 16px;
    }

    .hero-title {
      font-family: var(--font-display);
      font-size: clamp(34px, 6vw, 52px);
      line-height: 1.12;
      color: var(--navy);
      margin-bottom: 14px;
    }

    .hero-title .highlight {
      color: var(--green-2);
      position: relative;
      display: inline-block;
    }

    .hero-sub {
      font-size: clamp(15.5px, 2.2vw, 18px);
      color: #334155;
      font-weight: 700;
      margin-bottom: 24px;
      max-width: 560px;
    }

    .hero-cta-row {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 24px;
    }

    .hero-trust {
      display: flex;
      flex-wrap: wrap;
      gap: 12px 18px;
      font-size: 13.5px;
      font-weight: 800;
      color: var(--navy);
    }

    .hero-trust span {
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    /* ================= INTERACTIVE PHONE MOCKUP ================= */
    .phone-stage {
      display: flex;
      justify-content: center;
    }

    .phone-frame {
      width: 100%;
      max-width: 340px;
      background: #0F172A;
      border-radius: 36px;
      padding: 12px;
      box-shadow: var(--shadow-lg), 0 0 0 4px rgba(255, 255, 255, 0.55);
      position: relative;
    }

    .phone-notch {
      width: 108px;
      height: 18px;
      background: #0F172A;
      border-radius: 0 0 14px 14px;
      margin: 0 auto -10px;
      position: relative;
      z-index: 5;
    }

    .phone-screen {
      background: linear-gradient(180deg, #7CC9F8 0%, #9FD9FC 102px, #5AC82A 102px);
      border-radius: 26px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }

    .demo-header {
      padding: 16px 12px 8px;
      text-align: center;
    }

    .demo-header-title {
      font-size: 13px;
      font-weight: 900;
      color: var(--navy);
    }

    .demo-header-arab {
      font-family: var(--font-arabic);
      font-size: 19px;
      color: #D92B2B;
      font-weight: 700;
      line-height: 1.1;
    }

    .demo-badges {
      display: flex;
      justify-content: center;
      gap: 5px;
      margin-top: 4px;
      flex-wrap: wrap;
    }

    .demo-badge {
      background: #FFFFFF;
      color: var(--navy);
      font-size: 10px;
      font-weight: 800;
      padding: 2px 8px;
      border-radius: 999px;
    }

    .demo-mushaf-wrap {
      padding: 8px 9px 10px;
    }

    .demo-mushaf {
      background: var(--cream);
      border: 2px solid #D9F2C7;
      border-radius: 16px;
      padding: 10px 12px 14px;
    }

    .demo-banner {
      background: linear-gradient(180deg, #62CE2C 0%, #4EB51F 100%);
      color: #FFFFFF;
      font-family: var(--font-arabic);
      font-size: 21px;
      font-weight: 700;
      text-align: center;
      border-radius: 10px;
      padding: 2px 10px;
      margin-bottom: 8px;
    }

    .demo-verse {
      direction: rtl;
      text-align: center;
      font-family: var(--font-arabic);
      font-size: 25px;
      line-height: 2.1;
    }

    .demo-word {
      display: inline-block;
      padding: 0 5px;
      margin: 2px;
      border-radius: 7px;
      transition: all 0.2s ease;
    }

    .demo-word.ghost {
      color: rgba(120, 113, 98, 0.28);
    }

    .demo-word.active {
      background: #FFE96B;
      border: 2px solid #F59E0B;
      color: rgba(146, 64, 14, 0.5);
    }

    .demo-word.solved {
      color: #111827;
      background: transparent;
      border: none;
    }

    .demo-ayah-num {
      display: inline-flex;
      width: 24px;
      height: 24px;
      border-radius: 50%;
      border: 2px solid #5AC82A;
      background: #F0FDF4;
      color: #2E7D12;
      font-size: 13px;
      align-items: center;
      justify-content: center;
      vertical-align: middle;
    }

    .demo-tray {
      background: #FFF9E6;
      border-top: 2px solid #EBDDB8;
      padding: 9px 10px 12px;
    }

    .demo-tray-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 11px;
      font-weight: 900;
      color: var(--navy);
      margin-bottom: 8px;
    }

    .demo-tiles {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 6px;
      direction: rtl;
    }

    .demo-tile {
      background: #FFFFFF;
      border: 2px solid #E5D7B3;
      border-radius: 11px;
      padding: 3px 11px;
      font-family: var(--font-arabic);
      font-size: 21px;
      color: #1E293B;
      box-shadow: 0 3px 0 #D2C094;
      cursor: pointer;
      transition: transform 0.12s ease, background 0.15s ease;
    }

    .demo-tile:active {
      transform: translateY(2px);
    }

    .demo-tile.wrong {
      background: #FEE2E2;
      border-color: #EF4444;
    }

    .demo-tile.ok {
      background: #DCFCE7;
      border-color: #22C55E;
      opacity: 0.45;
      pointer-events: none;
    }

    .demo-status {
      text-align: center;
      font-size: 11.5px;
      font-weight: 800;
      color: #15803D;
      margin-top: 7px;
      min-height: 18px;
    }

    /* ================= SECTION COMMON ================= */
    section.block {
      padding: 68px 0;
    }

    section.block-alt {
      background: #F8FAFC;
      border-top: 1px solid #EEF2F6;
      border-bottom: 1px solid #EEF2F6;
    }

    .section-head {
      text-align: center;
      max-width: 700px;
      margin: 0 auto 42px;
    }

    .section-eyebrow {
      display: inline-block;
      background: #DCFCE7;
      color: #166534;
      font-size: 12.5px;
      font-weight: 900;
      padding: 5px 14px;
      border-radius: 999px;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      margin-bottom: 10px;
    }

    .section-title {
      font-family: var(--font-display);
      font-size: clamp(26px, 4.5vw, 38px);
      color: var(--navy);
      line-height: 1.18;
      margin-bottom: 10px;
    }

    .section-desc {
      font-size: 15.5px;
      color: var(--muted);
      font-weight: 700;
    }

    /* ================= MASALAH -> SOLUSI ================= */
    .compare-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 20px;
    }

    .compare-card {
      border-radius: 22px;
      padding: 26px 24px;
      border: 2px solid var(--border);
      background: #FFFFFF;
      box-shadow: var(--shadow-sm);
    }

    .compare-card.pain {
      background: #FFF7F7;
      border-color: #FECACA;
    }

    .compare-card.gain {
      background: #F0FDF4;
      border-color: #BBF7D0;
    }

    .compare-card h3 {
      font-family: var(--font-display);
      font-size: 21px;
      margin-bottom: 14px;
      color: var(--navy);
    }

    .check-list {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 11px;
      font-weight: 700;
      font-size: 15px;
    }

    .check-list li {
      display: flex;
      align-items: flex-start;
      gap: 10px;
    }

    /* ================= FITUR UNGGULAN ================= */
    .features-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 18px;
    }

    .feat-card {
      background: #FFFFFF;
      border: 2px solid #E8EEF5;
      border-radius: 20px;
      padding: 22px 20px;
      box-shadow: var(--shadow-sm);
      transition: transform 0.18s ease, border-color 0.18s ease;
    }

    .feat-card:hover {
      transform: translateY(-4px);
      border-color: var(--green-1);
    }

    .feat-icon {
      width: 52px;
      height: 52px;
      border-radius: 15px;
      background: #EFF6FF;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 26px;
      margin-bottom: 14px;
    }

    .feat-card h3 {
      font-family: var(--font-display);
      font-size: 19px;
      color: var(--navy);
      margin-bottom: 6px;
    }

    .feat-card p {
      font-size: 14.5px;
      color: var(--muted);
      font-weight: 600;
    }

    /* ================= TATA CARA BERMAIN ================= */
    .steps-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 18px;
    }

    .step-card {
      background: #FFFFFF;
      border: 2px solid #E2F7D6;
      border-radius: 20px;
      padding: 22px 20px;
      position: relative;
      box-shadow: var(--shadow-sm);
    }

    .step-num {
      width: 38px;
      height: 38px;
      border-radius: 12px;
      background: linear-gradient(180deg, #62D42F 0%, #42A318 100%);
      color: #FFFFFF;
      font-family: var(--font-display);
      font-size: 19px;
      font-weight: 700;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 12px;
      box-shadow: 0 3px 0 #2E7D12;
    }

    .step-card h3 {
      font-family: var(--font-display);
      font-size: 18.5px;
      color: var(--navy);
      margin-bottom: 6px;
    }

    .step-card p {
      font-size: 14.5px;
      color: var(--muted);
      font-weight: 600;
    }

    .tips-banner {
      margin-top: 24px;
      background: linear-gradient(135deg, #FEF9C3 0%, #FEF3C7 100%);
      border: 2px solid #FCD34D;
      border-radius: 18px;
      padding: 16px 20px;
      font-size: 14.5px;
      font-weight: 800;
      color: #78350F;
      display: flex;
      align-items: flex-start;
      gap: 12px;
    }

    /* ================= TAB PANDUAN INSTALASI (OFFLINE & VERCEL) ================= */
    .install-tabs {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 10px;
      margin-bottom: 24px;
    }

    .tab-btn {
      border: 2px solid #CBD5E1;
      background: #FFFFFF;
      color: var(--navy);
      font-family: var(--font-ui);
      font-weight: 900;
      font-size: 14px;
      padding: 10px 18px;
      border-radius: 14px;
      cursor: pointer;
      transition: all 0.16s ease;
    }

    .tab-btn.active {
      background: var(--navy);
      color: #FFFFFF;
      border-color: var(--navy);
      box-shadow: 0 4px 12px rgba(20, 43, 94, 0.22);
    }

    .tab-panel {
      display: none;
      background: #FFFFFF;
      border: 2px solid #E2E8F0;
      border-radius: 24px;
      padding: 26px 22px;
      box-shadow: var(--shadow-md);
    }

    .tab-panel.active {
      display: block;
    }

    .tab-panel h3 {
      font-family: var(--font-display);
      font-size: 22px;
      color: var(--navy);
      margin-bottom: 12px;
    }

    .guide-list {
      padding-left: 20px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      font-size: 15px;
      font-weight: 700;
      color: #334155;
      margin-bottom: 18px;
    }

    .guide-list li strong {
      color: var(--navy);
    }

    .code-box {
      background: #0F172A;
      color: #E2E8F0;
      font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
      font-size: 13px;
      padding: 14px 16px;
      border-radius: 14px;
      overflow-x: auto;
      line-height: 1.6;
      margin: 12px 0 16px;
    }

    .info-callout {
      background: #EFF6FF;
      border-left: 4px solid #2563EB;
      border-radius: 12px;
      padding: 12px 15px;
      font-size: 13.5px;
      font-weight: 700;
      color: #1E3A8A;
    }

    .info-callout.warn {
      background: #FFFBEB;
      border-left-color: #F59E0B;
      color: #92400E;
    }

    /* ================= OFFER / HARGA CTA ================= */
    .offer-box {
      background: linear-gradient(135deg, #142B5E 0%, #1D4ED8 100%);
      color: #FFFFFF;
      border-radius: 28px;
      padding: 40px 26px;
      text-align: center;
      box-shadow: var(--shadow-lg);
      position: relative;
      overflow: hidden;
    }

    .offer-box::after {
      content: "⭐";
      position: absolute;
      font-size: 140px;
      opacity: 0.08;
      right: -20px;
      bottom: -30px;
      pointer-events: none;
    }

    .offer-pill {
      display: inline-block;
      background: rgba(255, 225, 56, 0.2);
      color: var(--gold-1);
      border: 1.5px solid rgba(255, 225, 56, 0.5);
      font-size: 12.5px;
      font-weight: 900;
      padding: 5px 14px;
      border-radius: 999px;
      margin-bottom: 14px;
    }

    .offer-title {
      font-family: var(--font-display);
      font-size: clamp(26px, 5vw, 40px);
      line-height: 1.15;
      margin-bottom: 12px;
    }

    .offer-sub {
      max-width: 600px;
      margin: 0 auto 24px;
      font-size: 15.5px;
      opacity: 0.92;
      font-weight: 600;
    }

    .offer-perks {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 10px 18px;
      margin-bottom: 26px;
      font-size: 14px;
      font-weight: 800;
    }

    .offer-actions {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 12px;
    }

    /* ================= FAQ ACCORDION ================= */
    .faq-list {
      max-width: 780px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .faq-item {
      background: #FFFFFF;
      border: 2px solid #E2E8F0;
      border-radius: 16px;
      overflow: hidden;
    }

    .faq-q {
      width: 100%;
      background: transparent;
      border: none;
      padding: 16px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      text-align: left;
      font-family: var(--font-ui);
      font-size: 15.5px;
      font-weight: 900;
      color: var(--navy);
      cursor: pointer;
    }

    .faq-icon {
      font-size: 18px;
      transition: transform 0.2s ease;
      flex-shrink: 0;
    }

    .faq-item.open .faq-icon {
      transform: rotate(45deg);
    }

    .faq-a {
      display: none;
      padding: 0 18px 16px;
      font-size: 14.5px;
      color: var(--muted);
      font-weight: 700;
    }

    .faq-item.open .faq-a {
      display: block;
    }

    /* ================= FOOTER ================= */
    footer.site-footer {
      background: #0F172A;
      color: #CBD5E1;
      padding: 36px 0;
      text-align: center;
      font-size: 14px;
      font-weight: 700;
    }

    .footer-brand {
      font-family: var(--font-display);
      font-size: 22px;
      color: #FFFFFF;
      margin-bottom: 8px;
    }

    /* ================= RESPONSIVE DESKTOP ================= */
    @media (min-width: 768px) {
      .nav-links { display: flex; }
      .menu-toggle { display: none; }
      .hero-grid { grid-template-columns: 1.15fr 0.85fr; }
      .compare-grid { grid-template-columns: 1fr 1fr; }
      .features-grid { grid-template-columns: repeat(3, 1fr); }
      .steps-grid { grid-template-columns: repeat(4, 1fr); }
    }
  </style>
</head>
<body>

  <!-- ================= NAVBAR ================= -->
  <nav class="navbar">
    <div class="container nav-inner">
      <a href="#top" class="nav-brand">
        <img src="./icon.svg" alt="Ikon Al-Qur'an Go" />
        <span><?php echo ag_h($appName); ?></span>
      </a>

      <div class="nav-links">
        <a href="#fitur">Fitur Unggulan</a>
        <a href="#cara-main">Cara Bermain</a>
        <a href="#instalasi">Instalasi Offline &amp; Vercel</a>
        <a href="#faq">FAQ</a>
      </div>

      <div class="nav-actions">
        <a href="<?php echo ag_h($demoUrl); ?>"<?php echo $demoTargetAttr; ?> class="btn btn-outline btn-sm">🎮 Coba Demo</a>
        <a href="./checkout.php" class="btn btn-green btn-sm">🔓 Beli Full</a>
        <button class="menu-toggle" id="btn-menu" type="button" aria-label="Menu">☰</button>
      </div>
    </div>

    <div class="mobile-drawer" id="mobile-drawer">
      <a href="#fitur">Fitur Unggulan</a>
      <a href="#cara-main">Cara Bermain</a>
      <a href="#instalasi">Instalasi Offline &amp; Vercel</a>
      <a href="#faq">FAQ</a>
      <a href="./checkout.php?cek=1">🔍 Cek Status Pesanan</a>
      <a href="<?php echo ag_h($demoUrl); ?>"<?php echo $demoTargetAttr; ?> class="btn btn-gold" style="margin-top:4px;">🎮 Coba Demo Terbatas</a>
      <a href="./checkout.php" class="btn btn-green">🔓 Beli &amp; Download Full</a>
    </div>
  </nav>

  <!-- ================= HERO SECTION ================= -->
  <header class="hero" id="top">
    <div class="container hero-grid">
      <div>
        <div class="hero-badge">⭐ 30 Juz Lengkap · 604 Halaman Mushaf · Bisa Offline</div>
        <h1 class="hero-title">
          Bikin Hafalan Terasa Seperti Permainan — <span class="highlight">Fresh, Bukan Stress!</span>
        </h1>
        <p class="hero-sub">
          <strong>Al-Qur'an Go</strong> membantu anak melatih letak ayat yang sering tertukar dengan cara menyusun kata Arab, menebak arti ayat, mendengarkan murottal per ayat, dan mengumpulkan bintang serta kartu hadiah.
        </p>

        <div class="hero-cta-row">
          <a href="<?php echo ag_h($demoUrl); ?>"<?php echo $demoTargetAttr; ?> class="btn btn-gold">🎮 Coba Demo Gratis</a>
          <a href="./checkout.php" class="btn btn-green">🔓 Beli Full <?php echo ag_h($pricePromo); ?></a>
          <a href="<?php echo ag_h($waAskUrl); ?>" target="_blank" rel="noopener" class="btn btn-outline">💬 Chat WhatsApp</a>
        </div>

        <div class="hero-trust">
          <span>✅ Sekali Bayar, Tanpa Langganan</span>
          <span>✅ Bisa Offline (Teks &amp; Audio)</span>
          <span>✅ HP Android, iPhone &amp; Tablet</span>
        </div>
      </div>

      <!-- Pratinjau Interaktif Aplikasi -->
      <div class="phone-stage">
        <div class="phone-frame" aria-label="Pratinjau Interaktif Al-Qur'an Go">
          <div class="phone-notch"></div>
          <div class="phone-screen">
            <div class="demo-header">
              <div class="demo-header-title">Al-Fatihah · hal 1</div>
              <div class="demo-header-arab">الفاتحة</div>
              <div class="demo-badges">
                <span class="demo-badge">🎯 Tebak Ayat</span>
                <span class="demo-badge">⭐ Level 1</span>
                <span class="demo-badge">🎙️ Murottal ON</span>
              </div>
            </div>

            <div class="demo-mushaf-wrap">
              <div class="demo-mushaf">
                <div class="demo-banner">سورة الفاتحة</div>
                <div class="demo-verse" id="demo-verse">
                  <span class="demo-word active" data-idx="0">بِسْمِ</span>
                  <span class="demo-word ghost" data-idx="1">اللَّهِ</span>
                  <span class="demo-word ghost" data-idx="2">الرَّحْمَٰنِ</span>
                  <span class="demo-word ghost" data-idx="3">الرَّحِيمِ</span>
                  <span class="demo-ayah-num">١</span>
                </div>
              </div>
            </div>

            <div class="demo-tray">
              <div class="demo-tray-head">
                <span>👆 Coba ketuk kata yang benar!</span>
                <button type="button" id="demo-reset" style="border:none; background:#FFE96B; color:#78350F; font-weight:900; font-size:10.5px; padding:2px 8px; border-radius:8px; cursor:pointer;">🔄 Ulang</button>
              </div>
              <div class="demo-tiles" id="demo-tiles">
                <button type="button" class="demo-tile" data-word="الرَّحِيمِ">الرَّحِيمِ</button>
                <button type="button" class="demo-tile" data-word="بِسْمِ">بِسْمِ</button>
                <button type="button" class="demo-tile" data-word="الرَّحْمَٰنِ">الرَّحْمَٰنِ</button>
                <button type="button" class="demo-tile" data-word="اللَّهِ">اللَّهِ</button>
              </div>
              <div class="demo-status" id="demo-status">Ketuk kata pertama: بِسْمِ</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- ================= MASALAH VS SOLUSI ================= -->
  <section class="block">
    <div class="container">
      <div class="section-head">
        <span class="section-eyebrow">Menghafal Terasa Indah, Murojaahnya Jadi Mudah</span>
        <h2 class="section-title">Kenapa Anak Sering Bosan Saat Murojaah?</h2>
        <p class="section-desc">Mengulang hafalan secara monoton sering membuat anak cepat jenuh dan bingung membedakan ayat yang mirip.</p>
      </div>

      <div class="compare-grid">
        <div class="compare-card pain">
          <h3>😟 Tantangan Sebelum Pakai Al-Qur'an Go</h3>
          <ul class="check-list">
            <li><span>❌</span><span>Anak cepat bosan dan menganggap murojaah seperti tugas berat.</span></li>
            <li><span>❌</span><span>Sering lupa urutan kata atau ayat kebolak-balik saat setoran hafalan.</span></li>
            <li><span>❌</span><span>Hanya hafal bunyi bacaannya tanpa memahami makna dari ayat yang dibaca.</span></li>
            <li><span>❌</span><span>Aplikasi lain mahal karena sistem langganan bulanan atau wajib online terus.</span></li>
          </ul>
        </div>

        <div class="compare-card gain">
          <h3>🌟 Perubahan Bersama Al-Qur'an Go</h3>
          <ul class="check-list">
            <li><span>✅</span><span>Belajar terasa seperti main game ceria — anak semangat mengulang sendiri.</span></li>
            <li><span>✅</span><span>Melatih ketelitian posisi kata langsung di tampilan halaman mushaf 30 Juz.</span></li>
            <li><span>✅</span><span>Mode Tebak Arti membuat anak paham makna ayat, bukan sekadar bunyi.</span></li>
            <li><span>✅</span><span>Sekali bayar tanpa langganan, dapat di-install ke HP dan dipakai offline.</span></li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- ================= FITUR UNGGULAN ================= -->
  <section class="block block-alt" id="fitur">
    <div class="container">
      <div class="section-head">
        <span class="section-eyebrow">Fitur Lengkap 30 Juz</span>
        <h2 class="section-title">Dirancang Khusus Agar Anak Betah Murojaah</h2>
        <p class="section-desc">Semua yang dibutuhkan orang tua, guru TPQ/tahfizh, dan anak dalam satu aplikasi web modern berbasis PWA.</p>
      </div>

      <div class="features-grid">
        <article class="feat-card">
          <div class="feat-icon">📖</div>
          <h3>Lengkap 30 Juz (604 Halaman)</h3>
          <p>Disusun rapi mengikuti halaman Mushaf Madinah dari Juz 1 (Al-Fatihah) hingga Juz 30 (An-Naba' – An-Nas), lengkap dengan indikator progres per Juz.</p>
        </article>

        <article class="feat-card">
          <div class="feat-icon">🎯</div>
          <h3>Mode Tebak Ayat</h3>
          <p>Anak mengetuk dan menyusun potongan kata Arab satu per satu ke halaman mushaf kosong sampai seluruh ayat pada halaman tersebut terisi sempurna.</p>
        </article>

        <article class="feat-card">
          <div class="feat-icon">🧠</div>
          <h3>Mode Tebak Arti</h3>
          <p>Anak menyusun potongan terjemahan bahasa Indonesia tepat di bawah setiap ayat Arab agar ikut memahami pesan dan maknanya.</p>
        </article>

        <article class="feat-card">
          <div class="feat-icon">🎮</div>
          <h3>2 Level Bertahap</h3>
          <p><strong>Level 1 (Bayangan)</strong> memberi bantuan huruf transparan untuk pemula. <strong>Level 2 (Mandiri)</strong> tanpa bayangan bagi anak yang sudah percaya diri.</p>
        </article>

        <article class="feat-card">
          <div class="feat-icon">🎙️</div>
          <h3>Murottal Per Ayat (3 Qari)</h3>
          <p>Setiap kali satu ayat selesai disusun, suara murottal otomatis berbunyi. Pilihan Syekh Al-Husary, Mishary Alafasy, dan Al-Minshawi yang bisa diunduh offline.</p>
        </article>

        <article class="feat-card">
          <div class="feat-icon">🎁</div>
          <h3>Bintang &amp; 12 Kartu Hadiah</h3>
          <p>Setiap halaman yang selesai dihadiahi 1–3 bintang untuk membuka 12 koleksi kartu hadiah spesial mulai dari <em>Tunas Hafizh</em> hingga <em>Mahkota Cahaya</em>.</p>
        </article>
      </div>
    </div>
  </section>

  <!-- ================= TATA CARA BERMAIN ================= -->
  <section class="block" id="cara-main">
    <div class="container">
      <div class="section-head">
        <span class="section-eyebrow">Mudah Dipahami Anak</span>
        <h2 class="section-title">Tata Cara Bermain Al-Qur'an Go</h2>
        <p class="section-desc">Cukup 4 langkah sederhana — anak SD pun bisa langsung bermain mandiri di HP atau tablet.</p>
      </div>

      <div class="steps-grid">
        <div class="step-card">
          <div class="step-num">1</div>
          <h3>Pilih Mode &amp; Level</h3>
          <p>Di beranda, pilih <strong>Mode Tebak Ayat</strong> atau <strong>Mode Tebak Arti</strong>, lalu tentukan <strong>Level 1 (Bayangan)</strong> atau <strong>Level 2 (Mandiri)</strong>.</p>
        </div>

        <div class="step-card">
          <div class="step-num">2</div>
          <h3>Pilih Juz &amp; Halaman</h3>
          <p>Ketuk salah satu kotak dari <strong>Juz 1 sampai 30</strong>, lalu pilih halaman mushaf yang ingin dimurojaah (tanda ⚡ berarti sudah siap offline).</p>
        </div>

        <div class="step-card">
          <div class="step-num">3</div>
          <h3>Ketuk Kata Berurutan</h3>
          <p>Perhatikan kotak kuning yang menyala di mushaf, lalu ketuk potongan kata/arti yang tepat di panel bawah yang menempel (sticky).</p>
        </div>

        <div class="step-card">
          <div class="step-num">4</div>
          <h3>Dengar Murottal &amp; Raih Bintang</h3>
          <p>Tiap ayat selesai, murottal otomatis diputar. Ketuk nomor ayat untuk mengulang bacaan, gunakan <strong>💡 Bantuan (3x)</strong> bila lupa, dan kumpulkan kartu hadiah!</p>
        </div>
      </div>

      <div class="tips-banner">
        <span style="font-size:22px;">💡</span>
        <div>
          <strong>Tips Orang Tua &amp; Guru:</strong> Mulai dari <em>Mode Tebak Ayat · Level 1</em> pada surah pendek di Juz 30 (Hal 582–604). Jika anak sudah mendapatkan 3 bintang (maksimal 2 kali salah), naikkan tantangan ke <em>Level 2 (Mandiri)</em> lalu lanjut ke <em>Mode Tebak Arti</em>.
        </div>
      </div>
    </div>
  </section>

  <!-- ================= PANDUAN INSTALASI (OFFLINE & ONLINE VERCEL) ================= -->
  <section class="block block-alt" id="instalasi">
    <div class="container">
      <div class="section-head">
        <span class="section-eyebrow">Panduan Lengkap</span>
        <h2 class="section-title">Tata Cara Instalasi Aplikasi (Offline &amp; Online Vercel)</h2>
        <p class="section-desc">Pilih panduan sesuai kebutuhan: memasang langsung ke HP sebagai aplikasi offline (PWA), atau menayangkan aplikasi secara online gratis menggunakan Vercel.</p>
      </div>

      <div class="install-tabs" role="tablist">
        <button type="button" class="tab-btn active" data-tab="tab-pwa">📱 1. Install ke HP &amp; Mode Offline</button>
        <button type="button" class="tab-btn" data-tab="tab-vercel">▲ 2. Deploy Online di Vercel</button>
        <button type="button" class="tab-btn" data-tab="tab-local">💻 3. Jalankan Offline di Laptop</button>
      </div>

      <!-- TAB 1: PWA OFFLINE DI HP -->
      <div class="tab-panel active" id="tab-pwa">
        <h3>📱 Memasang Al-Qur'an Go di Layar Utama HP (Seperti Aplikasi Native)</h3>
        <ol class="guide-list">
          <li><strong>Di HP Android (Google Chrome / Edge):</strong> Buka alamat web Al-Qur'an Go, lalu ketuk tombol kuning <strong>"Install"</strong> pada banner biru <em>"Pasang Al-Qur'an Go di HP"</em> (atau ketuk menu titik tiga <code>⋮</code> &rarr; <strong>Tambahkan ke layar utama / Install app</strong>).</li>
          <li><strong>Di iPhone / iPad (Safari):</strong> Buka alamat web Al-Qur'an Go di Safari, ketuk ikon <strong>Bagikan (⬆️)</strong> di bagian bawah layar, lalu pilih <strong>Tambah ke Layar Utama (Add to Home Screen)</strong> &rarr; ketuk <strong>Tambah</strong>.</li>
          <li><strong>Unduh Teks 30 Juz untuk Akses Offline:</strong> Buka ikon <strong>Profil</strong> (pojok kiri atas) atau tombol <strong>📥 Offline</strong>, lalu ketuk tombol biru <strong>"📦 Unduh Teks Lengkap 30 Juz (604 Hal)"</strong>. Tunggu beberapa detik hingga 100% tersimpan di memori perangkat (<code>IndexedDB</code>).</li>
          <li><strong>Unduh Audio Murottal Offline:</strong> Masih di menu Profil, pilih Qari favorit, lalu ketuk <strong>"⚡ Unduh Audio Surah Utama"</strong> atau pilih Juz 1–30 lalu klik <strong>"⬇️ Unduh Audio"</strong>. Bisa juga langsung menekan tombol <strong>"⬇️ Audio Hal"</strong> di layar mushaf saat bermain.</li>
          <li><strong>Uji Mode Offline:</strong> Aktifkan <strong>Mode Pesawat</strong> di HP, buka ikon <strong>Al-Qur'an Go</strong> di layar utama, dan mainkan seluruh halaman serta audio yang telah diunduh tanpa kuota internet!</li>
        </ol>
        <div class="info-callout">
          <strong>Info Penting:</strong> Halaman 1 (Al-Fatihah), Halaman 582 (An-Naba'), dan Halaman 604 (Al-Ikhlas, Al-Falaq, An-Nas) sudah tertanam langsung di dalam aplikasi sehingga bisa langsung dimainkan tanpa perlu mengunduh dulu.
        </div>
      </div>

      <!-- TAB 2: DEPLOY ONLINE DI VERCEL -->
      <div class="tab-panel" id="tab-vercel">
        <h3>▲ Cara Deploy Online Gratis Menggunakan Vercel (HTTPS Otomatis)</h3>
        <p style="font-size:14.5px; font-weight:700; color:#475569; margin-bottom:14px;">
          Vercel menyediakan hosting statis gratis dengan sertifikat <strong>HTTPS otomatis</strong>, yang merupakan syarat wajib agar fitur PWA (<em>Install to Home Screen</em>) dan <em>Service Worker</em> aktif di semua HP.
        </p>

        <h4 style="color:var(--navy); font-size:16px; margin-bottom:8px;">Cara A — Paling Mudah lewat GitHub + Dashboard Vercel</h4>
        <ol class="guide-list">
          <li><strong>Siapkan File Aplikasi:</strong> Ekstrak file <code>alquran-go-lengkap-vercel.zip</code> ke dalam satu folder (berisi <code>index.html</code>, <code>app.js</code>, <code>manifest.json</code>, <code>sw.js</code>, <code>icon.svg</code>). Anda juga dapat menambahkan <code>landing.html</code> ke folder yang sama.</li>
          <li><strong>Unggah ke GitHub:</strong> Buat repositori baru di <a href="https://github.com" target="_blank" rel="noopener" style="color:#2563EB; text-decoration:underline;">github.com</a> (misalnya <code>alquran-go</code>), lalu klik <strong>Add file &rarr; Upload files</strong> dan unggah seluruh file tersebut, lalu klik <strong>Commit changes</strong>.</li>
          <li><strong>Hubungkan ke Vercel:</strong> Masuk ke <a href="https://vercel.com" target="_blank" rel="noopener" style="color:#2563EB; text-decoration:underline;">vercel.com</a> menggunakan akun GitHub &rarr; klik <strong>Add New &rarr; Project</strong> &rarr; pilih repositori <code>alquran-go</code> &rarr; klik <strong>Import</strong>.</li>
          <li><strong>Konfigurasi &amp; Deploy:</strong> Biarkan <em>Framework Preset</em> pada <strong>Other</strong> dan <em>Build Command</em> kosong, lalu langsung klik tombol <strong>Deploy</strong>.</li>
          <li><strong>Selesai!</strong> Dalam ~20 detik aplikasi aktif di URL <code>https://nama-proyek.vercel.app</code> dan langsung bisa di-install sebagai PWA di HP Android maupun iPhone.</li>
        </ol>

        <h4 style="color:var(--navy); font-size:16px; margin:16px 0 8px;">Cara B — Langsung dari Komputer/Laptop via Vercel CLI (Tanpa GitHub)</h4>
        <div class="code-box"># 1. Pastikan Node.js terpasang, lalu install Vercel CLI sekali saja
npm install -g vercel

# 2. Masuk ke folder hasil ekstrak Al-Qur'an Go
cd alquran-go

# 3. Login dan langsung deploy ke produksi
vercel login
vercel --prod</div>

        <div class="info-callout">
          <strong>Audio Murottal Offline di Vercel Siap Jalan 100%:</strong> Paket bundel <code>alquran-go-lengkap-vercel.zip</code> sudah menyertakan <code>vercel.json</code> dan Serverless Function <code>api/audio_proxy.js</code>. Jadi saat di-deploy ke Vercel, unduhan audio murottal offline otomatis berjalan melalui jalur Server Proxy yang bebas blokir CORS — sama cepatnya dengan hosting PHP!
        </div>
      </div>

      <!-- TAB 3: OFFLINE DI LAPTOP / LOKAL -->
      <div class="tab-panel" id="tab-local">
        <h3>💻 Menjalankan Al-Qur'an Go Secara Lokal di Laptop / PC</h3>
        <ol class="guide-list">
          <li><strong>Ekstrak Paket Aplikasi:</strong> Ekstrak <code>alquran-go-lengkap-vercel.zip</code> ke folder pilihan Anda (misalnya <code>D:\alquran-go</code>).</li>
          <li><strong>Jalankan Server Lokal (Disarankan agar PWA &amp; Audio Proxy aktif penuh):</strong> Buka terminal/CMD di dalam folder tersebut, lalu pilih salah satu perintah berikut:</li>
        </ol>
        <div class="code-box"># Opsi 1: Menggunakan PHP (Laragon / XAMPP / PHP CLI) — Audio Proxy langsung aktif!
php -S localhost:8080

# Opsi 2: Menggunakan Python 3
python -m http.server 8080

# Opsi 3: Menggunakan Node.js (npx)
npx serve .</div>
        <ol class="guide-list" start="3">
          <li><strong>Buka di Browser:</strong> Ketik alamat <code>http://localhost:8080</code> di Chrome/Edge. Untuk membuka landing page promosi, buka <code>http://localhost:8080/landing.html</code>.</li>
          <li><strong>Buka Tanpa Server (Darurat):</strong> Anda tetap bisa klik ganda <code>index.html</code> secara langsung untuk memainkan 3 halaman bawaan (Al-Fatihah, An-Naba', 3 Qul), namun fitur Install PWA dan Service Worker hanya aktif di <code>http://localhost</code> atau <code>https://</code>.</li>
        </ol>
      </div>
    </div>
  </section>

  <!-- ================= OFFER / CTA SECTION ================= -->
  <section class="block" id="mulai">
    <div class="container">
      <div class="offer-box">
        <div class="offer-pill">✨ PENAWARAN TERBAIK UNTUK KELUARGA &amp; SEKOLAH</div>
        <h2 class="offer-title">Cukup Sekali Bayar, Tanpa Langganan Bulanan!</h2>
        <p class="offer-sub">
          Berikan pengalaman murojaah yang menyenangkan untuk buah hati atau santri di rumah dan sekolah. Buka kapan saja di HP, tablet, maupun laptop.
        </p>
        <div class="offer-perks">
          <span>✅ Akses Penuh 30 Juz (604 Halaman)</span>
          <span>✅ Mode Tebak Ayat &amp; Tebak Arti</span>
          <span>✅ Audio Murottal 3 Qari Offline</span>
          <span>✅ 12 Koleksi Kartu Hadiah</span>
        </div>
        <div style="margin-bottom:22px;">
          <span style="font-size:16px; text-decoration:line-through; opacity:0.7; margin-right:10px; font-weight:800;"><?php echo ag_h($priceNormal); ?></span>
          <span style="font-family:var(--font-display); font-size:clamp(32px, 6vw, 44px); color:#FFE138; font-weight:700;"><?php echo ag_h($pricePromo); ?></span>
          <div style="font-size:12.5px; opacity:0.88; font-weight:700;">Sekali bayar · Pembayaran QRIS / E-Wallet · Langsung Download</div>
        </div>
        <div class="offer-actions">
          <a href="./checkout.php" class="btn btn-gold">🛒 Beli &amp; Download Sekarang</a>
          <a href="<?php echo ag_h($demoUrl); ?>"<?php echo $demoTargetAttr; ?> class="btn btn-outline">🎮 Coba Demo Terbatas</a>
          <a href="<?php echo ag_h($waAskUrl); ?>" target="_blank" rel="noopener" class="btn btn-green">💬 Tanya via WhatsApp</a>
        </div>
      </div>
    </div>
  </section>

  <!-- ================= FAQ ================= -->
  <section class="block block-alt" id="faq">
    <div class="container">
      <div class="section-head">
        <span class="section-eyebrow">Pertanyaan Umum</span>
        <h2 class="section-title">Sering Ditanyakan Orang Tua &amp; Guru</h2>
      </div>

      <div class="faq-list">
        <div class="faq-item open">
          <button type="button" class="faq-q">
            <span>Apakah Al-Qur'an Go benar-benar bisa dimainkan tanpa internet?</span>
            <span class="faq-icon">+</span>
          </button>
          <div class="faq-a">
            Ya! Tiga halaman utama (Al-Fatihah, An-Naba', dan Al-Ikhlas–An-Nas) sudah tertanam langsung. Untuk seluruh 30 Juz (604 halaman) dan audio murottal per Juz/halaman, cukup unduh sekali di menu Profil &amp; Offline saat ada Wi-Fi, setelah itu bisa dimainkan 100% offline.
          </div>
        </div>

        <div class="faq-item">
          <button type="button" class="faq-q">
            <span>Apakah perlu mengunduh file APK dari luar Play Store?</span>
            <span class="faq-icon">+</span>
          </button>
          <div class="faq-a">
            Tidak perlu! Al-Qur'an Go menggunakan teknologi PWA (Progressive Web App), sehingga aman dan ringan dipasang langsung dari Chrome (Android) atau Safari (iPhone/iPad) tanpa instalasi file APK yang berisiko.
          </div>
        </div>

        <div class="faq-item">
          <button type="button" class="faq-q">
            <span>Cocok untuk anak usia berapa?</span>
            <span class="faq-icon">+</span>
          </button>
          <div class="faq-a">
            Sangat cocok untuk anak usia 5–15 tahun (TK B, SD/MI, hingga SMP/MTs) yang sudah mulai bisa membaca huruf hijaiyah berharakat. Anak pemula bisa memakai Level 1 (bayangan huruf), sementara yang sudah lancar hafalannya bisa mencoba Level 2 (mandiri).
          </div>
        </div>

        <div class="faq-item">
          <button type="button" class="faq-q">
            <span>Apakah progres bintang anak hilang kalau aplikasi ditutup?</span>
            <span class="faq-icon">+</span>
          </button>
          <div class="faq-a">
            Tidak hilang. Nama panggilan, avatar, bintang per halaman, serta kartu hadiah yang sudah terbuka otomatis tersimpan di penyimpanan lokal HP selama data browser tidak dihapus.
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ================= FOOTER ================= -->
  <footer class="site-footer">
    <div class="container">
      <div class="footer-brand">✨ Al-Qur'an Go · 30 Juz</div>
      <p>Bikin hafalan terasa seperti permainan — fresh, bukan stress.</p>
      <p style="margin-top:10px; font-size:13px;">
        <a href="./checkout.php?cek=1" style="color:#93C5FD; text-decoration:underline; margin:0 8px;">Cek Status Pesanan</a> ·
        <a href="./admin.php" style="color:#94A3B8; text-decoration:underline; margin:0 8px;">Panel Admin</a>
      </p>
      <p style="margin-top:8px; font-size:12.5px; opacity:0.75;">&copy; <?php echo date('Y'); ?> <?php echo ag_h($appName); ?>. Dibuat untuk menemani generasi cinta Al-Qur'an.</p>
    </div>
  </footer>

  <!-- Floating WhatsApp Button -->
  <a href="<?php echo ag_h($waAskUrl); ?>" target="_blank" rel="noopener" aria-label="Chat WhatsApp Admin"
     style="position:fixed; right:18px; bottom:18px; z-index:95; display:inline-flex; align-items:center; gap:8px; background:#25D366; color:#FFFFFF; font-weight:900; font-size:14px; padding:11px 16px; border-radius:999px; box-shadow:0 8px 24px rgba(37,211,102,0.4); border:2px solid #FFFFFF;">
    <span style="font-size:18px;">💬</span><span>Chat Admin</span>
  </a>

  <script>
    'use strict';

    // 1. Menu Mobile Drawer
    const btnMenu = document.getElementById('btn-menu');
    const mobileDrawer = document.getElementById('mobile-drawer');
    if (btnMenu && mobileDrawer) {
      btnMenu.addEventListener('click', () => {
        mobileDrawer.classList.toggle('open');
      });
      mobileDrawer.querySelectorAll('a').forEach(a => {
        a.addEventListener('click', () => mobileDrawer.classList.remove('open'));
      });
    }

    // 2. Mini Demo Interaktif di Mockup HP (Al-Fatihah Ayat 1)
    const demoTarget = ['بِسْمِ', 'اللَّهِ', 'الرَّحْمَٰنِ', 'الرَّحِيمِ'];
    let demoIdx = 0;

    const demoWords = document.querySelectorAll('#demo-verse .demo-word');
    const demoTiles = document.querySelectorAll('#demo-tiles .demo-tile');
    const demoStatus = document.getElementById('demo-status');
    const demoReset = document.getElementById('demo-reset');

    function resetDemo() {
      demoIdx = 0;
      demoWords.forEach((w, i) => {
        w.classList.remove('solved', 'active', 'ghost');
        w.classList.add(i === 0 ? 'active' : 'ghost');
      });
      demoTiles.forEach(t => t.classList.remove('ok', 'wrong'));
      demoStatus.textContent = 'Ketuk kata pertama: بِسْمِ';
      demoStatus.style.color = '#15803D';
    }

    demoTiles.forEach(tile => {
      tile.addEventListener('click', () => {
        if (demoIdx >= demoTarget.length) return;
        const word = tile.dataset.word;
        if (word === demoTarget[demoIdx]) {
          tile.classList.add('ok');
          demoWords[demoIdx].classList.remove('active', 'ghost');
          demoWords[demoIdx].classList.add('solved');
          demoIdx++;
          if (demoIdx < demoTarget.length) {
            demoWords[demoIdx].classList.remove('ghost');
            demoWords[demoIdx].classList.add('active');
            demoStatus.textContent = `Benar! Lanjut kata berikutnya (${demoIdx}/4)`;
            demoStatus.style.color = '#15803D';
          } else {
            demoStatus.textContent = '🎉 MasyaAllah! Ayat 1 tersusun sempurna ⭐⭐⭐';
            demoStatus.style.color = '#B45309';
          }
        } else {
          tile.classList.add('wrong');
          demoStatus.textContent = 'Hampir tepat! Coba lihat bayangan katanya 😉';
          demoStatus.style.color = '#DC2626';
          setTimeout(() => tile.classList.remove('wrong'), 380);
        }
      });
    });

    if (demoReset) demoReset.addEventListener('click', resetDemo);

    // 3. Tab Panduan Instalasi (PWA Offline / Vercel / Lokal)
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanels = document.querySelectorAll('.tab-panel');
    tabBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        tabBtns.forEach(b => b.classList.remove('active'));
        tabPanels.forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        const target = document.getElementById(btn.dataset.tab);
        if (target) target.classList.add('active');
      });
    });

    // 4. FAQ Accordion
    document.querySelectorAll('.faq-q').forEach(btn => {
      btn.addEventListener('click', () => {
        const item = btn.closest('.faq-item');
        if (item) item.classList.toggle('open');
      });
    });
  </script>
</body>
</html>
