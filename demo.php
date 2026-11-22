<?php
require_once __DIR__ . '/config.php';
$settings = ag_load_settings();
$allowedPages = ag_parse_pages_csv($settings['demo_allowed_pages']);
$maxVerses = max(0, intval($settings['demo_max_verses']));
$waAskUrl = ag_wa_link($settings['wa_number'], $settings['wa_message_ask']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
  <meta name="theme-color" content="#73C2FB" />
  <meta name="description" content="Al-Qur'an Go · 30 Juz - Game hafalan dan murojaah Al-Qur'an untuk anak lengkap dengan murottal per ayat offline." />

  <!-- PWA & iOS Home Screen Meta -->
  <link rel="manifest" href="./manifest.json" />
  <link rel="icon" type="image/svg+xml" href="./icon.svg" />
  <link rel="apple-touch-icon" href="./icon.svg" />
  <meta name="apple-mobile-web-app-capable" content="yes" />
  <meta name="apple-mobile-web-app-status-bar-style" content="default" />
  <meta name="apple-mobile-web-app-title" content="Al-Qur'an Go" />
  <meta name="mobile-web-app-capable" content="yes" />

  <title><?php echo ag_h($settings['app_name']); ?> · Mode Demo Terbatas</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Amiri+Quran&family=Fredoka:wght@500;600;700&family=Nunito:wght@600;700;800;900&family=Scheherazade+New:wght@400;700&display=swap" rel="stylesheet" />

  <style>
    :root {
      --sky-top: #73C2FB;
      --sky-bottom: #BDE6FF;
      --grass-main: #5AC82A;
      --grass-dark: #43A31A;
      --grass-deep: #2E7D12;
      --mushaf-bg: #FFFDF5;
      --mushaf-line: #E6DFC5;
      --tray-bg: #FFF9E6;
      --tile-border: #E5D7B3;
      --tile-shadow: #D2C094;
      --gold-1: #FFE033;
      --gold-2: #FFA800;
      --navy: #142B5E;
      --ribbon-red: #E5252A;
      --text-dark: #1E293B;
      --text-muted: #64748B;
      --correct: #22C55E;
      --wrong: #EF4444;
      --highlight-bg: #FFE96B;
      --highlight-border: #F59E0B;
      --font-ui: 'Nunito', sans-serif;
      --font-display: 'Fredoka', sans-serif;
      --font-arabic: 'Scheherazade New', 'Amiri Quran', 'Traditional Arabic', serif;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      -webkit-tap-highlight-color: transparent;
      user-select: none;
    }

    html, body {
      width: 100%;
      min-height: 100vh;
      font-family: var(--font-ui);
      background: linear-gradient(180deg, var(--sky-top) 0%, var(--sky-bottom) 38%, var(--grass-main) 38.1%, var(--grass-dark) 100%);
      color: var(--text-dark);
      overflow-x: clip;
    }

    .app-shell {
      width: 100%;
      max-width: 780px;
      min-height: 100vh;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      position: relative;
      background: linear-gradient(180deg, var(--sky-top) 0%, var(--sky-bottom) 340px, var(--grass-main) 340px);
      box-shadow: 0 0 40px rgba(15, 45, 90, 0.14);
    }

    .screen { display: none; flex-direction: column; flex: 1; width: 100%; }
    .screen.active { display: flex; }
    .hidden { display: none !important; }

    .home-sky {
      position: relative;
      padding: max(14px, env(safe-area-inset-top)) 16px 22px;
      background: linear-gradient(180deg, #74C5FA 0%, #B8E5FF 100%);
      overflow: hidden;
    }

    .home-sky::before,
    .home-sky::after {
      content: "";
      position: absolute;
      background: rgba(255, 255, 255, 0.45);
      border-radius: 999px;
      pointer-events: none;
    }
    .home-sky::before { width: 140px; height: 44px; top: 56px; left: -30px; }
    .home-sky::after  { width: 160px; height: 48px; top: 72px; right: -40px; }

    .top-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      position: relative;
      z-index: 2;
    }

    .pill-btn {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      background: #FFFFFF;
      border: 2px solid rgba(255, 255, 255, 0.9);
      border-radius: 999px;
      padding: 6px 12px;
      font-family: var(--font-ui);
      font-weight: 800;
      font-size: 13px;
      color: var(--navy);
      box-shadow: 0 3px 0 rgba(20, 43, 94, 0.12);
      cursor: pointer;
      transition: transform 0.12s ease, box-shadow 0.12s ease;
    }

    .pill-btn:active {
      transform: translateY(2px);
      box-shadow: 0 1px 0 rgba(20, 43, 94, 0.12);
    }

    .avatar-circle {
      width: 26px;
      height: 26px;
      border-radius: 50%;
      background: linear-gradient(135deg, #6366F1, #3B82F6);
      color: #FFFFFF;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
    }

    .top-stats { display: flex; align-items: center; gap: 6px; }

    .icon-circle-btn {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      border: none;
      background: #FFFFFF;
      color: #2563EB;
      font-size: 18px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 3px 0 rgba(20, 43, 94, 0.14);
      cursor: pointer;
      transition: transform 0.12s ease;
      flex-shrink: 0;
    }
    .icon-circle-btn:active { transform: scale(0.93); }

    .brand-hero { text-align: center; margin: 14px auto 4px; position: relative; z-index: 2; }
    .brand-title {
      font-family: var(--font-display);
      font-size: clamp(34px, 8.5vw, 48px);
      font-weight: 700;
      line-height: 1.05;
      letter-spacing: -0.5px;
      color: #FFD600;
      -webkit-text-stroke: 2.5px var(--navy);
      text-shadow: 0 4px 0 var(--navy), 0 6px 14px rgba(20, 43, 94, 0.25);
      display: inline-block;
    }

    .brand-sparkle {
      font-size: 16px;
      vertical-align: super;
      margin: 0 2px;
      -webkit-text-stroke: 0;
      text-shadow: none;
    }

    .brand-ribbon-wrap { display: flex; align-items: center; justify-content: center; gap: 4px; margin-top: -2px; }
    .brand-ribbon {
      background: linear-gradient(180deg, #F43F5E 0%, var(--ribbon-red) 100%);
      color: #FFFFFF;
      font-family: var(--font-display);
      font-weight: 600;
      font-size: clamp(12px, 3.2vw, 15px);
      padding: 4px 16px;
      border-radius: 999px;
      border: 2.5px solid #FFFFFF;
      box-shadow: 0 4px 0 rgba(153, 27, 27, 0.45);
      transform: rotate(-1.2deg);
    }
    .ribbon-star { font-size: 18px; filter: drop-shadow(0 2px 0 rgba(20, 43, 94, 0.35)); }

    .pwa-install-banner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 100%);
      color: #FFFFFF;
      padding: 10px 14px;
      border-radius: 16px;
      margin-bottom: 12px;
      box-shadow: 0 4px 0 rgba(20, 43, 94, 0.35);
      border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .pwa-install-text { font-size: 12.5px; font-weight: 800; line-height: 1.3; }
    .pwa-install-text small { display: block; font-weight: 600; opacity: 0.88; font-size: 11px; }

    .pwa-install-btn {
      background: linear-gradient(180deg, #FFE64D 0%, #FFB800 100%);
      color: var(--navy);
      border: none;
      border-radius: 12px;
      padding: 7px 13px;
      font-family: var(--font-ui);
      font-weight: 900;
      font-size: 12.5px;
      box-shadow: 0 3px 0 #B45309;
      cursor: pointer;
      white-space: nowrap;
      flex-shrink: 0;
    }

    .home-meadow {
      flex: 1;
      background: linear-gradient(180deg, var(--grass-main) 0%, #4AB820 100%);
      border-radius: 28px 28px 0 0;
      padding: 16px 14px 36px;
      margin-top: -8px;
      position: relative;
      z-index: 3;
      box-shadow: 0 -6px 20px rgba(46, 125, 18, 0.16);
    }

    .meadow-prompt {
      background: #FFFFFF;
      color: var(--navy);
      font-weight: 900;
      font-size: 14px;
      text-align: center;
      padding: 7px 16px;
      border-radius: 999px;
      width: fit-content;
      margin: -28px auto 12px;
      box-shadow: 0 4px 0 rgba(20, 43, 94, 0.14);
      border: 2px solid #E2F7D6;
    }

    .control-strip { display: flex; flex-direction: column; gap: 8px; margin-bottom: 14px; }
    .toggle-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }

    .seg-btn {
      border: 2px solid rgba(255, 255, 255, 0.35);
      background: rgba(255, 255, 255, 0.2);
      color: #FFFFFF;
      font-family: var(--font-ui);
      font-weight: 800;
      font-size: 13px;
      padding: 9px 10px;
      border-radius: 14px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      transition: all 0.15s ease;
    }

    .seg-btn.active {
      background: #FFFFFF;
      color: var(--navy);
      border-color: #FFFFFF;
      box-shadow: 0 4px 0 rgba(30, 90, 12, 0.28);
    }

    .seg-btn.level-btn.active {
      background: linear-gradient(180deg, #FFF7B2 0%, #FFE14D 100%);
      color: var(--navy);
      border-color: #FFFBEB;
    }

    .progress-summary-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      background: rgba(20, 65, 10, 0.22);
      color: #FFFFFF;
      padding: 8px 12px;
      border-radius: 12px;
      font-size: 12.5px;
      font-weight: 800;
    }

    .offline-chip {
      background: rgba(255, 255, 255, 0.22);
      color: #FFFFFF;
      border: 1px solid rgba(255, 255, 255, 0.45);
      border-radius: 999px;
      padding: 3px 9px;
      font-size: 11px;
      font-weight: 800;
      cursor: pointer;
      white-space: nowrap;
    }

    .juz-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }

    .juz-card {
      background: #FFFFFF;
      border-radius: 16px;
      padding: 11px 8px 10px;
      text-align: center;
      border: 2px solid #EAF8E2;
      box-shadow: 0 4px 0 rgba(35, 105, 14, 0.3);
      cursor: pointer;
      transition: transform 0.12s ease, box-shadow 0.12s ease;
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: space-between;
      min-height: 98px;
    }

    .juz-card:active { transform: translateY(3px); box-shadow: 0 1px 0 rgba(35, 105, 14, 0.3); }
    .juz-card.has-offline::after { content: "⚡"; position: absolute; top: 5px; right: 6px; font-size: 11px; opacity: 0.85; }
    .juz-num { font-family: var(--font-display); font-size: 24px; font-weight: 700; color: var(--navy); line-height: 1.05; }
    .juz-surah { font-size: 12px; font-weight: 800; color: #334155; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }
    .juz-progress-text { font-size: 11px; font-weight: 800; color: var(--grass-dark); margin-top: 4px; }
    .juz-mini-bar { width: 82%; height: 5px; background: #E2E8F0; border-radius: 999px; margin-top: 4px; overflow: hidden; }
    .juz-mini-fill { height: 100%; width: 0%; background: linear-gradient(90deg, #4ADE80, #16A34A); border-radius: 999px; transition: width 0.3s ease; }

    /* ================= LAYAR PERMAINAN (HEADER & MENU BAWAH STICKY PENUH) ================= */
    #screen-game {
      position: fixed;
      inset: 0;
      width: 100%;
      max-width: 780px;
      height: 100vh;
      height: 100dvh;
      margin: 0 auto;
      overflow: hidden;
      z-index: 30;
      background: linear-gradient(180deg, #9FD9FC 0%, #9FD9FC 165px, var(--grass-main) 165px);
      box-shadow: 0 0 40px rgba(15, 45, 90, 0.16);
    }

    .game-header {
      flex-shrink: 0;
      z-index: 20;
      background: linear-gradient(180deg, #7CC9F8 0%, #9FD9FC 100%);
      padding: max(10px, env(safe-area-inset-top)) 14px 10px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      box-shadow: 0 2px 10px rgba(20, 43, 94, 0.08);
    }

    .game-title-box { text-align: center; flex: 1; min-width: 0; }
    .game-surah-latin { font-size: 14px; font-weight: 900; color: var(--navy); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .game-surah-arabic { font-family: var(--font-arabic); font-size: 20px; font-weight: 700; color: #D92B2B; line-height: 1.1; margin-top: -2px; }

    .game-meta-row {
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      padding: 0 14px 8px;
      background: #9FD9FC;
      flex-wrap: wrap;
    }

    .game-badge {
      background: #FFFFFF;
      color: var(--navy);
      font-size: 11.5px;
      font-weight: 800;
      padding: 4px 10px;
      border-radius: 999px;
      box-shadow: 0 2px 0 rgba(20, 43, 94, 0.1);
      border: none;
    }

    .game-badge-btn { cursor: pointer; transition: transform 0.1s ease; }
    .game-badge-btn:active { transform: scale(0.95); }
    .game-badge-btn.murottal-on { background: #DCFCE7; color: #166534; }
    .game-badge-btn.murottal-off { background: #F1F5F9; color: #64748B; }
    .game-badge-btn.audio-dl-ready { background: #FEF3C7; color: #92400E; }
    .game-badge-btn.audio-dl-done { background: #DCFCE7; color: #166534; }

    .game-progress-track { width: 100%; height: 7px; background: rgba(255, 255, 255, 0.55); border-radius: 999px; overflow: hidden; margin-top: 4px; }
    .game-progress-fill { height: 100%; width: 0%; background: linear-gradient(90deg, #FFE033, #22C55E); border-radius: 999px; transition: width 0.25s ease; }

    .mushaf-outer {
      flex: 1 1 auto;
      min-height: 0;
      padding: 10px 10px 22px;
      background: var(--grass-main);
      border-radius: 24px 24px 0 0;
      display: flex;
      flex-direction: column;
      overflow-y: auto;
      -webkit-overflow-scrolling: touch;
      overscroll-behavior: contain;
    }

    .mushaf-page {
      background: var(--mushaf-bg);
      border: 3px solid #D9F2C7;
      border-radius: 20px;
      padding: 12px 14px 20px;
      box-shadow: inset 0 0 18px rgba(217, 198, 148, 0.22), 0 6px 16px rgba(31, 88, 12, 0.18);
      flex: 0 0 auto;
      min-height: 100%;
      display: flex;
      flex-direction: column;
    }

    .surah-banner {
      background: linear-gradient(180deg, #62CE2C 0%, #4EB51F 100%);
      color: #FFFFFF;
      font-family: var(--font-arabic);
      font-size: 26px;
      font-weight: 700;
      text-align: center;
      padding: 4px 16px;
      border-radius: 14px;
      margin-bottom: 10px;
      box-shadow: 0 3px 0 #3B9014;
      border: 2px solid #96E86B;
    }

    .basmalah-line { font-family: var(--font-arabic); font-size: 26px; text-align: center; color: #2D3748; margin-bottom: 12px; line-height: 1.5; }

    .mushaf-lines {
      direction: rtl;
      text-align: justify;
      font-family: var(--font-arabic);
      font-size: clamp(25px, 6.2vw, 33px);
      line-height: 2.25;
      color: #1E293B;
      word-spacing: 5px;
    }

    .mushaf-word { display: inline-block; padding: 0 5px; margin: 2px 2px; border-radius: 8px; transition: all 0.22s ease; vertical-align: middle; }
    .mushaf-word.level1-hidden { color: rgba(120, 113, 98, 0.27); }
    .mushaf-word.level2-hidden { color: transparent; border-bottom: 2px dashed #CFC3A1; min-width: 44px; height: 1.55em; }
    .mushaf-word.active-slot { background: var(--highlight-bg); border: 2px solid var(--highlight-border); box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3); animation: pulseSlot 1.35s infinite ease-in-out; }
    .mushaf-word.level1-hidden.active-slot { color: rgba(146, 64, 14, 0.42); }
    .mushaf-word.level2-hidden.active-slot { color: transparent; border-bottom-style: solid; }
    .mushaf-word.solved { color: #111827; border-bottom: none; background: transparent; animation: popInWord 0.28s cubic-bezier(0.34, 1.56, 0.64, 1); }
    .mushaf-word.verse-reciting { background: #DCFCE7; color: #14532D; }
    .mushaf-word.hint-peek { color: #D97706 !important; background: #FEF3C7; }

    .ayah-end-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 31px;
      height: 31px;
      margin: 0 4px;
      border-radius: 50%;
      border: 2px solid #5AC82A;
      background: #F0FDF4;
      color: #2E7D12;
      font-family: var(--font-arabic);
      font-size: 16px;
      font-weight: 700;
      vertical-align: middle;
      line-height: 1;
      cursor: pointer;
      transition: transform 0.15s ease, background 0.15s ease;
    }

    .ayah-end-badge:hover, .ayah-end-badge:active { transform: scale(1.12); background: #DCFCE7; }
    .ayah-end-badge.playing { background: #22C55E; color: #FFFFFF; border-color: #15803D; box-shadow: 0 0 0 5px rgba(34, 197, 94, 0.28); animation: pulseSlot 0.9s infinite ease-in-out; }

    .arti-container { display: flex; flex-direction: column; gap: 12px; }
    .arti-verse-card { background: #FFFFFF; border: 2px solid #E8DEC0; border-radius: 16px; padding: 12px 14px; transition: all 0.2s ease; }
    .arti-verse-card.active-verse { border-color: var(--grass-main); box-shadow: 0 4px 14px rgba(90, 200, 42, 0.18); background: #FFFDF7; }
    .arti-verse-card.done-verse { border-color: #BBF7D0; background: #F0FDF4; }
    .arti-verse-card.reciting-verse { border-color: #16A34A; box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.2); }

    .arti-top-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; margin-bottom: 8px; }
    .arti-play-btn {
      width: 34px; height: 34px; border-radius: 50%;
      border: 2px solid #BBF7D0; background: #F0FDF4; color: #15803D;
      font-size: 15px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 6px;
    }
    .arti-play-btn.playing { background: #22C55E; color: #FFFFFF; border-color: #15803D; }

    .arti-arabic-text { direction: rtl; text-align: right; font-family: var(--font-arabic); font-size: clamp(24px, 5.8vw, 30px); line-height: 1.9; color: #1E293B; flex: 1; }
    .arti-answer-zone { display: flex; flex-wrap: wrap; gap: 6px; padding: 8px 10px; border-radius: 12px; background: #FFF9E6; border: 2px dashed #E5D4A1; min-height: 44px; align-items: center; }
    .arti-slot { padding: 4px 10px; border-radius: 9px; font-size: 13.5px; font-weight: 800; transition: all 0.2s ease; }
    .arti-slot.slot-level1 { color: rgba(100, 116, 139, 0.42); background: rgba(255, 255, 255, 0.65); border: 1px dashed #CBD5E1; }
    .arti-slot.slot-level2 { color: transparent; background: rgba(255, 255, 255, 0.65); border: 1px dashed #CBD5E1; min-width: 58px; }
    .arti-slot.slot-active { background: var(--highlight-bg); border: 2px solid var(--highlight-border); }
    .arti-slot.slot-solved { color: #14532D; background: #DCFCE7; border: 1.5px solid #86EFAC; animation: popInWord 0.25s ease; }

    .murottal-bar {
      display: flex; align-items: center; justify-content: space-between; gap: 8px;
      background: linear-gradient(90deg, #166534 0%, #15803D 100%);
      color: #FFFFFF; padding: 7px 12px; border-radius: 14px; margin-bottom: 9px;
      box-shadow: 0 3px 10px rgba(22, 101, 52, 0.25); font-size: 12.5px; font-weight: 800;
    }
    .murottal-bar-left { display: flex; align-items: center; gap: 8px; min-width: 0; }
    .eq-bars { display: inline-flex; align-items: flex-end; gap: 2px; height: 14px; flex-shrink: 0; }
    .eq-bars span { width: 3px; background: #FFE033; border-radius: 2px; animation: eqBounce 0.7s infinite ease-in-out; }
    .eq-bars span:nth-child(2) { animation-delay: 0.18s; }
    .eq-bars span:nth-child(3) { animation-delay: 0.34s; }
    .murottal-status-text { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .murottal-bar-actions { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
    .murottal-mini-btn { border: none; background: rgba(255, 255, 255, 0.2); color: #FFFFFF; border-radius: 8px; padding: 4px 8px; font-family: var(--font-ui); font-size: 12px; font-weight: 800; cursor: pointer; }

    .word-tray {
      flex-shrink: 0;
      position: relative;
      z-index: 25;
      background: var(--tray-bg);
      border-top: 3px solid #EBDDB8;
      border-radius: 22px 22px 0 0;
      padding: 10px 14px max(14px, env(safe-area-inset-bottom));
      box-shadow: 0 -6px 20px rgba(20, 43, 94, 0.18);
    }

    .tray-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; gap: 8px; }
    .tray-label { font-size: 12.5px; font-weight: 900; color: var(--navy); display: flex; align-items: center; gap: 6px; }
    .hint-btn {
      display: inline-flex; align-items: center; gap: 5px;
      background: linear-gradient(180deg, #FFF3A1 0%, #FFD633 100%);
      border: 2px solid #F59E0B; border-radius: 12px; padding: 4px 11px;
      font-family: var(--font-ui); font-weight: 900; font-size: 13px; color: #78350F;
      cursor: pointer; box-shadow: 0 3px 0 #D97706; flex-shrink: 0;
    }
    .hint-btn:active { transform: translateY(2px); box-shadow: 0 1px 0 #D97706; }
    .hint-btn:disabled { opacity: 0.48; cursor: not-allowed; }

    .word-choices { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; max-height: 155px; overflow-y: auto; padding: 2px 0 4px; }
    .word-choices.rtl-choices { direction: rtl; }

    .word-tile {
      background: #FFFFFF; border: 2px solid var(--tile-border); border-radius: 14px; padding: 6px 14px;
      box-shadow: 0 4px 0 var(--tile-shadow); cursor: pointer;
      transition: transform 0.12s ease, box-shadow 0.12s ease, background 0.15s ease; color: #1E293B;
    }
    .word-tile.arabic-tile { font-family: var(--font-arabic); font-size: clamp(23px, 5.5vw, 28px); line-height: 1.45; min-width: 68px; text-align: center; }
    .word-tile.meaning-tile { font-family: var(--font-ui); font-size: 14px; font-weight: 800; padding: 9px 14px; }
    .word-tile:active { transform: translateY(3px); box-shadow: 0 1px 0 var(--tile-shadow); }
    .word-tile.wrong-shake { background: #FEE2E2; border-color: var(--wrong); box-shadow: 0 4px 0 #DC2626; animation: shakeX 0.36s ease; }
    .word-tile.correct-flash { background: #DCFCE7; border-color: var(--correct); box-shadow: 0 4px 0 #16A34A; transform: scale(0.92); }

    /* ================= MODAL ================= */
    .modal-backdrop {
      position: fixed; inset: 0; background: rgba(15, 23, 42, 0.58); backdrop-filter: blur(4px);
      z-index: 100; display: flex; align-items: center; justify-content: center; padding: 16px;
    }

    .modal-card {
      background: #FFFFFF; width: 100%; max-width: 480px; max-height: 86vh;
      border-radius: 26px; border: 4px solid #E2F7D6; box-shadow: 0 20px 45px rgba(15, 23, 42, 0.32);
      display: flex; flex-direction: column; overflow: hidden; animation: popInModal 0.24s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .modal-head { background: linear-gradient(180deg, #74C5FA 0%, #9FD9FC 100%); padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .modal-title { font-family: var(--font-display); font-size: 20px; font-weight: 700; color: var(--navy); }
    .modal-body { padding: 16px; overflow-y: auto; flex: 1; }

    .juz-audio-box {
      background: #F0FDF4;
      border: 2px solid #BBF7D0;
      border-radius: 16px;
      padding: 10px 12px;
      margin-bottom: 12px;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .juz-audio-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      font-size: 12.5px;
      font-weight: 800;
      color: #166534;
    }
    .btn-mini-green {
      border: none;
      background: linear-gradient(180deg, #22C55E 0%, #16A34A 100%);
      color: #FFFFFF;
      border-radius: 10px;
      padding: 6px 11px;
      font-family: var(--font-ui);
      font-size: 12px;
      font-weight: 900;
      cursor: pointer;
      white-space: nowrap;
      box-shadow: 0 2px 0 #15803D;
    }
    .btn-mini-green:disabled { opacity: 0.6; cursor: not-allowed; }

    .level-list { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .level-item { background: #F8FAFC; border: 2px solid #E2E8F0; border-radius: 16px; padding: 11px 10px; cursor: pointer; display: flex; flex-direction: column; gap: 4px; transition: all 0.14s ease; }
    .level-item:hover { border-color: var(--grass-main); background: #F0FDF4; }
    .level-item.offline-ready { border-color: #BBF7D0; background: #F7FEE7; }
    .level-item-top { display: flex; align-items: center; justify-content: space-between; font-size: 11.5px; font-weight: 800; color: var(--text-muted); }
    .level-item-name { font-size: 14px; font-weight: 900; color: var(--navy); }
    .level-stars { font-size: 14px; letter-spacing: 1px; }

    .gift-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .gift-card { border-radius: 18px; padding: 14px 10px; text-align: center; border: 2.5px solid #FDE68A; background: linear-gradient(180deg, #FFFBEB 0%, #FEF3C7 100%); box-shadow: 0 4px 0 #F59E0B; display: flex; flex-direction: column; align-items: center; gap: 5px; }
    .gift-card.locked { background: #F1F5F9; border-color: #CBD5E1; box-shadow: 0 4px 0 #94A3B8; filter: grayscale(0.9); opacity: 0.72; }
    .gift-emoji { font-size: 38px; line-height: 1.1; }
    .gift-name { font-weight: 900; font-size: 13.5px; color: var(--navy); }
    .gift-req { font-size: 11px; font-weight: 800; color: #B45309; background: rgba(255, 255, 255, 0.75); padding: 2px 8px; border-radius: 999px; }

    .win-box { text-align: center; padding: 8px 6px; }
    .win-stars { font-size: 46px; letter-spacing: 6px; margin: 6px 0 8px; }
    .win-heading { font-family: var(--font-display); font-size: 26px; color: var(--grass-dark); margin-bottom: 4px; }
    .win-sub { font-size: 14px; color: var(--text-muted); font-weight: 700; margin-bottom: 14px; }
    .win-reward-banner { background: linear-gradient(135deg, #FEF3C7, #FDE68A); border: 2px solid #F59E0B; border-radius: 16px; padding: 12px; margin-bottom: 16px; }

    .btn-primary {
      width: 100%; border: none; border-radius: 16px; padding: 12px 16px;
      font-family: var(--font-ui); font-weight: 900; font-size: 15px; color: #FFFFFF;
      background: linear-gradient(180deg, #5FD32C 0%, #44A819 100%);
      box-shadow: 0 4px 0 #2F7A10; cursor: pointer;
    }
    .btn-primary:active { transform: translateY(2px); box-shadow: 0 2px 0 #2F7A10; }
    .btn-primary:disabled { opacity: 0.65; cursor: not-allowed; }

    .btn-blue {
      width: 100%; border: none; border-radius: 16px; padding: 11px 16px;
      font-family: var(--font-ui); font-weight: 900; font-size: 14px; color: #FFFFFF;
      background: linear-gradient(180deg, #3B82F6 0%, #1D4ED8 100%);
      box-shadow: 0 4px 0 #1E3A8A; cursor: pointer;
    }
    .btn-blue:active { transform: translateY(2px); box-shadow: 0 2px 0 #1E3A8A; }
    .btn-blue:disabled { opacity: 0.65; cursor: not-allowed; }

    .btn-amber {
      width: 100%; border: none; border-radius: 16px; padding: 11px 16px;
      font-family: var(--font-ui); font-weight: 900; font-size: 14px; color: #78350F;
      background: linear-gradient(180deg, #FDE047 0%, #F59E0B 100%);
      box-shadow: 0 4px 0 #B45309; cursor: pointer;
    }
    .btn-amber:active { transform: translateY(2px); box-shadow: 0 2px 0 #B45309; }
    .btn-amber:disabled { opacity: 0.65; cursor: not-allowed; }

    .btn-secondary {
      width: 100%; border: 2px solid #CBD5E1; border-radius: 16px; padding: 10px 16px;
      font-family: var(--font-ui); font-weight: 800; font-size: 14px; color: var(--navy);
      background: #FFFFFF; cursor: pointer;
    }
    .btn-secondary:disabled { opacity: 0.6; cursor: not-allowed; }

    .btn-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px; }

    .audio-juz-picker {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 8px;
      margin-bottom: 8px;
      align-items: stretch;
    }
    .audio-juz-picker .select-input { margin-bottom: 0; padding: 9px 12px; font-size: 13.5px; }

    .dl-progress-wrap {
      margin: 8px 0 10px;
      background: #F1F5F9;
      border-radius: 12px;
      padding: 8px 10px;
      border: 1.5px solid #CBD5E1;
    }
    .dl-progress-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      margin-bottom: 6px;
    }
    .dl-progress-text { font-size: 12px; font-weight: 800; color: var(--navy); }
    .dl-cancel-btn {
      border: none;
      background: #FEE2E2;
      color: #B91C1C;
      border-radius: 8px;
      padding: 3px 8px;
      font-family: var(--font-ui);
      font-size: 11px;
      font-weight: 900;
      cursor: pointer;
    }
    .dl-progress-track { width: 100%; height: 9px; background: #E2E8F0; border-radius: 999px; overflow: hidden; }
    .dl-progress-fill { height: 100%; width: 0%; background: linear-gradient(90deg, #38BDF8, #22C55E); border-radius: 999px; transition: width 0.25s ease; }

    .form-label { display: block; font-size: 13px; font-weight: 800; color: var(--navy); margin-bottom: 6px; }
    .text-input, .select-input { width: 100%; padding: 11px 14px; border-radius: 14px; border: 2px solid #CBD5E1; font-family: var(--font-ui); font-size: 14.5px; font-weight: 800; color: var(--navy); background: #FFFFFF; outline: none; margin-bottom: 12px; }
    .text-input:focus, .select-input:focus { border-color: var(--grass-main); }

    .setting-check-row {
      display: flex; align-items: center; justify-content: space-between; gap: 10px;
      background: #F0FDF4; border: 2px solid #BBF7D0; border-radius: 14px;
      padding: 10px 12px; margin-bottom: 12px; font-size: 13px; font-weight: 800; color: #14532D; cursor: pointer;
    }
    .setting-check-row input[type="checkbox"] { width: 20px; height: 20px; accent-color: #22C55E; cursor: pointer; }

    .avatar-picker { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px; }
    .avatar-opt { width: 44px; height: 44px; border-radius: 50%; border: 2.5px solid #E2E8F0; background: #F8FAFC; font-size: 22px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
    .avatar-opt.selected { border-color: var(--grass-main); background: #DCFCE7; transform: scale(1.08); }

    .toast {
      position: fixed; top: max(18px, env(safe-area-inset-top)); left: 50%;
      transform: translateX(-50%) translateY(-100px);
      background: var(--navy); color: #FFFFFF; font-weight: 800; font-size: 13px;
      padding: 10px 18px; border-radius: 999px; box-shadow: 0 8px 20px rgba(15, 23, 42, 0.25);
      z-index: 200; transition: transform 0.25s ease; text-align: center; max-width: 90vw;
    }
    .toast.show { transform: translateX(-50%) translateY(0); }

    .loading-state { text-align: center; padding: 36px 16px; color: var(--text-muted); font-weight: 800; }
    .spinner { width: 36px; height: 36px; border: 4px solid #E2E8F0; border-top-color: var(--grass-main); border-radius: 50%; margin: 0 auto 12px; animation: spin 0.8s linear infinite; }

    @keyframes pulseSlot { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.04); } }
    @keyframes popInWord { 0% { transform: scale(0.7); opacity: 0.4; } 70% { transform: scale(1.1); } 100% { transform: scale(1); opacity: 1; } }
    @keyframes shakeX { 0%, 100% { transform: translateX(0); } 20% { transform: translateX(-6px); } 40% { transform: translateX(6px); } 60% { transform: translateX(-4px); } 80% { transform: translateX(4px); } }
    @keyframes popInModal { 0% { transform: scale(0.86); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
    @keyframes spin { to { transform: rotate(360deg); } }
    @keyframes eqBounce { 0%, 100% { height: 4px; } 50% { height: 14px; } }

    @media (min-width: 600px) {
      .juz-grid { gap: 14px; }
      .juz-card { padding: 14px 12px; min-height: 110px; }
      .juz-num { font-size: 28px; }
      .juz-surah { font-size: 13.5px; }
      .level-list { grid-template-columns: repeat(3, 1fr); }
      .gift-grid { grid-template-columns: repeat(3, 1fr); }
    }

    /* ================= GAYA KHUSUS MODE DEMO TERBATAS ================= */
    .demo-top-ribbon {
      position: sticky;
      top: 0;
      z-index: 60;
      background: linear-gradient(90deg, #142B5E 0%, #1D4ED8 100%);
      color: #FFFFFF;
      padding: 8px 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      font-size: 12.5px;
      font-weight: 800;
      box-shadow: 0 3px 12px rgba(20, 43, 94, 0.25);
    }
    .demo-top-ribbon a.demo-buy-pill {
      background: linear-gradient(180deg, #FFE64D 0%, #FFB800 100%);
      color: #142B5E;
      text-decoration: none;
      padding: 5px 12px;
      border-radius: 999px;
      font-weight: 900;
      font-size: 12px;
      white-space: nowrap;
      box-shadow: 0 2px 0 #B45309;
    }
    .juz-card.demo-locked-card {
      background: #F1F5F9;
      border-color: #CBD5E1;
      opacity: 0.78;
    }
    .juz-card.demo-locked-card .juz-num,
    .juz-card.demo-locked-card .juz-surah { color: #64748B; }
    .demo-free-tag {
      position: absolute;
      top: 5px;
      left: 6px;
      background: #22C55E;
      color: #FFFFFF;
      font-size: 9px;
      font-weight: 900;
      padding: 1px 6px;
      border-radius: 999px;
    }
    .demo-lock-tag {
      position: absolute;
      top: 5px;
      right: 6px;
      font-size: 12px;
    }
    .level-item.demo-locked-item {
      background: #F1F5F9;
      border-color: #CBD5E1;
      opacity: 0.8;
    }
  </style>
</head>
<body>

  <div class="demo-top-ribbon">
    <span>🔒 <strong>Mode Demo Terbatas</strong> · Coba hal <?php echo ag_h(implode(', ', $allowedPages)); ?></span>
    <a class="demo-buy-pill" href="./checkout.php">🔓 Buka Full 30 Juz</a>
  </div>
  <div class="app-shell">

    <!-- ==================== LAYAR 1: BERANDA & PILIH 30 JUZ ==================== -->
    <section id="screen-home" class="screen active">
      <header class="home-sky">
        <div class="top-bar">
          <button class="pill-btn" id="btn-open-profile" type="button" aria-label="Profil Anak">
            <span class="avatar-circle" id="header-avatar">🧒</span>
            <span id="header-username">Sahabat Quran</span>
          </button>

          <div class="top-stats">
            <button class="pill-btn" id="btn-open-gifts" type="button" aria-label="Bintang dan Kartu Hadiah">
              <span id="header-stars">0</span> ⭐ · 🎁 <span id="header-gifts-count">0</span>
            </button>
            <button class="icon-circle-btn" id="btn-toggle-sound" type="button" aria-label="Suara">🔊</button>
          </div>
        </div>

        <div class="brand-hero">
          <div class="brand-title">
            <span class="brand-sparkle">✨</span>Al-Qur'an Go<span class="brand-sparkle">✨</span>
          </div>
          <div class="brand-ribbon-wrap">
            <span class="ribbon-star">⭐</span>
            <div class="brand-ribbon">Benerin ayat yang suka ketuker</div>
            <span class="ribbon-star">⭐</span>
          </div>
        </div>
      </header>

      <main class="home-meadow">
        <div class="meadow-prompt">Pilih Juz yang kamu mau di sini 👇</div>

        <div class="pwa-install-banner hidden" id="pwa-install-banner">
          <div class="pwa-install-text">
            📲 Pasang Al-Qur'an Go di HP
            <small id="pwa-install-desc">Buka seperti aplikasi native & main lebih cepat offline!</small>
          </div>
          <button class="pwa-install-btn" id="btn-install-pwa" type="button">Install</button>
        </div>

        <div class="control-strip">
          <div class="toggle-row" role="group" aria-label="Mode Permainan">
            <button class="seg-btn active" id="mode-ayat-btn" type="button" data-mode="ayat">
              🎯 Mode Tebak Ayat
            </button>
            <button class="seg-btn" id="mode-arti-btn" type="button" data-mode="arti">
              📖 Mode Tebak Arti
            </button>
          </div>

          <div class="toggle-row" role="group" aria-label="Level Permainan">
            <button class="seg-btn level-btn active" id="level-1-btn" type="button" data-level="1">
              ⭐ Level 1 · Bayangan
            </button>
            <button class="seg-btn level-btn" id="level-2-btn" type="button" data-level="2">
              ⭐⭐ Level 2 · Mandiri
            </button>
          </div>

          <div class="progress-summary-bar">
            <span id="progress-label">Progres: Susun Ayat · Level 1</span>
            <button class="offline-chip" id="btn-open-offline" type="button">📥 Offline: 3/604</button>
          </div>
        </div>

        <div class="juz-grid" id="juz-grid"></div>
      </main>
    </section>

    <!-- ==================== LAYAR 2: PERMAINAN MUSHAF ==================== -->
    <section id="screen-game" class="screen">
      <header class="game-header">
        <button class="icon-circle-btn" id="btn-game-back" type="button" aria-label="Kembali">⬅</button>
        <div class="game-title-box">
          <div class="game-surah-latin" id="game-surah-latin">An-Naba' · hal 582</div>
          <div class="game-surah-arabic" id="game-surah-arabic">النبأ</div>
        </div>
        <button class="icon-circle-btn" id="btn-game-sound" type="button" aria-label="Suara">🔊</button>
      </header>

      <div class="game-meta-row">
        <span class="game-badge" id="game-mode-badge">🎯 Tebak Ayat</span>
        <span class="game-badge" id="game-level-badge">⭐ Level 1</span>
        <button class="game-badge game-badge-btn murottal-on" id="btn-toggle-murottal" type="button">
          🎙️ Murottal: ON
        </button>
        <button class="game-badge game-badge-btn audio-dl-ready" id="btn-dl-page-audio" type="button" title="Unduh audio murottal halaman ini untuk diputar offline">
          ⬇️ Audio Hal
        </button>
        <span class="game-badge" id="game-accuracy-badge">✨ 0 salah</span>
        <div class="game-progress-track">
          <div class="game-progress-fill" id="game-progress-fill"></div>
        </div>
      </div>

      <div class="mushaf-outer" id="mushaf-outer">
        <div class="mushaf-page" id="mushaf-page">
          <div class="surah-banner" id="mushaf-surah-banner">سورة النبأ</div>
          <div class="basmalah-line" id="mushaf-basmalah">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</div>

          <div class="mushaf-lines" id="mushaf-lines"></div>
          <div class="arti-container hidden" id="arti-container"></div>
        </div>
      </div>

      <footer class="word-tray">
        <div class="murottal-bar hidden" id="murottal-bar">
          <div class="murottal-bar-left">
            <div class="eq-bars" aria-hidden="true"><span></span><span></span><span></span></div>
            <span class="murottal-status-text" id="murottal-status-text">Memutar murottal...</span>
          </div>
          <div class="murottal-bar-actions">
            <button class="murottal-mini-btn" id="btn-murottal-replay" type="button">🔁 Ulang</button>
            <button class="murottal-mini-btn" id="btn-murottal-stop" type="button">⏹ Stop</button>
          </div>
        </div>

        <div class="tray-header">
          <div class="tray-label" id="tray-label">👆 Ketuk kata berikutnya</div>
          <button class="hint-btn" id="btn-use-hint" type="button">
            💡 <span id="hint-count">3</span>
          </button>
        </div>
        <div class="word-choices rtl-choices" id="word-choices"></div>
      </footer>
    </section>

  </div>

  <!-- ==================== MODAL PILIH HALAMAN DALAM JUZ ==================== -->
  <div class="modal-backdrop hidden" id="modal-juz">
    <div class="modal-card">
      <div class="modal-head">
        <div class="modal-title" id="modal-juz-title">Juz 30 · An-Naba'</div>
        <button class="icon-circle-btn" data-close-modal="modal-juz" type="button">✕</button>
      </div>
      <div class="modal-body">
        <p id="modal-juz-desc" style="font-size:13px; color:#475569; font-weight:700; margin-bottom:10px;">
          Pilih halaman mushaf untuk mulai bermain. Tanda ⚡ berarti sudah siap dimainkan 100% offline.
        </p>

        <!-- Unduh Audio Murottal Offline Khusus Juz Ini -->
        <div class="juz-audio-box">
          <div class="juz-audio-row">
            <span id="modal-juz-audio-info">🎙️ Audio Murottal Juz ini</span>
            <button class="btn-mini-green" id="btn-dl-modal-juz-audio" type="button">⬇️ Unduh Audio Juz</button>
          </div>
          <div class="dl-progress-wrap hidden" id="juz-audio-dl-wrap" style="margin:0;">
            <div class="dl-progress-head">
              <div class="dl-progress-text" id="juz-audio-dl-text">Mengunduh audio...</div>
              <button class="dl-cancel-btn" id="btn-cancel-juz-audio" type="button">⏹ Batal</button>
            </div>
            <div class="dl-progress-track">
              <div class="dl-progress-fill" id="juz-audio-dl-fill"></div>
            </div>
          </div>
        </div>

        <div class="level-list" id="modal-level-list"></div>
      </div>
    </div>
  </div>

  <!-- ==================== MODAL KOLEKSI KARTU HADIAH ==================== -->
  <div class="modal-backdrop hidden" id="modal-gifts">
    <div class="modal-card">
      <div class="modal-head">
        <div class="modal-title">🎁 Kartu Hadiah & Bintang</div>
        <button class="icon-circle-btn" data-close-modal="modal-gifts" type="button">✕</button>
      </div>
      <div class="modal-body">
        <div style="background:#FEF9C3; border:2px solid #FDE047; border-radius:14px; padding:10px 12px; margin-bottom:14px; font-size:13px; font-weight:800; color:#854D0E; text-align:center;">
          Total Bintangmu: <span id="gift-modal-stars">0</span> ⭐ · Kumpulkan bintang untuk membuka semua kartu spesial!
        </div>
        <div class="gift-grid" id="gift-grid"></div>
      </div>
    </div>
  </div>

  <!-- ==================== MODAL MENANG ==================== -->
  <div class="modal-backdrop hidden" id="modal-win">
    <div class="modal-card">
      <div class="modal-head">
        <div class="modal-title">🎉 MasyaAllah, Hebat!</div>
        <button class="icon-circle-btn" data-close-modal="modal-win" type="button">✕</button>
      </div>
      <div class="modal-body">
        <div class="win-box">
          <div class="win-stars" id="win-stars">⭐⭐⭐</div>
          <div class="win-heading" id="win-heading">Ayat Tersusun Sempurna!</div>
          <div class="win-sub" id="win-sub">Kamu menyelesaikan An-Naba' tanpa tertukar.</div>

          <div class="win-reward-banner hidden" id="win-reward-banner">
            <div style="font-size:12px; font-weight:900; color:#B45309; text-transform:uppercase;">Kartu Hadiah Baru Terbuka!</div>
            <div id="win-reward-content" style="font-size:18px; font-weight:900; color:#142B5E; margin-top:4px;"></div>
          </div>

          <button class="btn-primary" id="btn-win-next" type="button">▶ Main Level Berikutnya</button>
          <div class="btn-row">
            <button class="btn-secondary" id="btn-win-replay" type="button">🔄 Ulangi</button>
            <button class="btn-secondary" id="btn-win-home" type="button">🏠 Pilih Juz</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ==================== MODAL PROFIL, MUROTTAL, & OFFLINE 30 JUZ ==================== -->
  <div class="modal-backdrop hidden" id="modal-profile">
    <div class="modal-card">
      <div class="modal-head">
        <div class="modal-title">⚙️ Profil, Murottal & Offline</div>
        <button class="icon-circle-btn" data-close-modal="modal-profile" type="button">✕</button>
      </div>
      <div class="modal-body">
        <label class="form-label" for="input-username">Nama Panggilan Anak</label>
        <input type="text" id="input-username" class="text-input" maxlength="20" placeholder="Misal: Aisyah / Farhan" />

        <label class="form-label">Pilih Avatar</label>
        <div class="avatar-picker" id="avatar-picker">
          <button type="button" class="avatar-opt selected" data-avatar="🧒">🧒</button>
          <button type="button" class="avatar-opt" data-avatar="👧">👧</button>
          <button type="button" class="avatar-opt" data-avatar="🧕">🧕</button>
          <button type="button" class="avatar-opt" data-avatar="👦">👦</button>
          <button type="button" class="avatar-opt" data-avatar="🦁">🦁</button>
          <button type="button" class="avatar-opt" data-avatar="🐼">🐼</button>
          <button type="button" class="avatar-opt" data-avatar="🌟">🌟</button>
        </div>

        <hr style="border:none; border-top:2px dashed #E2E8F0; margin:12px 0;" />

        <label class="setting-check-row" for="chk-auto-murottal">
          <span>🎙️ Putar murottal otomatis saat ayat selesai</span>
          <input type="checkbox" id="chk-auto-murottal" checked />
        </label>

        <label class="form-label" for="select-qari">Pilih Suara Qari Murottal</label>
        <select id="select-qari" class="select-input">
          <option value="Husary_128kbps">Syekh Al-Husary (Pelan & Jelas untuk Anak)</option>
          <option value="Alafasy_128kbps">Syekh Mishary Rashid Alafasy (Merdu)</option>
          <option value="Minshawy_Murattal_128kbps">Syekh Al-Minshawi (Murattal Klasik)</option>
        </select>

        <button class="btn-primary" id="btn-save-profile" type="button" style="margin-bottom:14px;">💾 Simpan Pengaturan</button>

        <hr style="border:none; border-top:2px dashed #E2E8F0; margin:12px 0;" />

        <!-- Bagian Teks 30 Juz Offline -->
        <div style="font-size:13px; font-weight:800; color:#1E293B; margin-bottom:6px;">📦 Teks & Arti 30 Juz Offline (604 Halaman)</div>
        <p id="offline-cache-info" style="font-size:12.5px; color:#64748B; font-weight:700; margin-bottom:10px;">
          Memeriksa data teks offline...
        </p>

        <div class="dl-progress-wrap hidden" id="dl-progress-wrap">
          <div class="dl-progress-head">
            <div class="dl-progress-text" id="dl-progress-text">Menyiapkan unduhan...</div>
          </div>
          <div class="dl-progress-track">
            <div class="dl-progress-fill" id="dl-progress-fill"></div>
          </div>
        </div>

        <button class="btn-blue" id="btn-cache-all30" type="button" style="margin-bottom:8px;">
          📦 Unduh Teks Lengkap 30 Juz (604 Hal)
        </button>
        <button class="btn-secondary" id="btn-cache-juz30" type="button" style="margin-bottom:14px;">
          📥 Unduh Teks Juz 30 Saja (23 Hal)
        </button>

        <hr style="border:none; border-top:2px dashed #E2E8F0; margin:12px 0;" />

        <!-- Bagian Audio Murottal Offline -->
        <div style="font-size:13px; font-weight:800; color:#1E293B; margin-bottom:6px;">🎙️ Audio Murottal Offline (Simpan di HP)</div>
        <p id="offline-audio-info" style="font-size:12.5px; color:#64748B; font-weight:700; margin-bottom:10px;">
          Memeriksa audio murottal offline...
        </p>

        <div class="dl-progress-wrap hidden" id="audio-dl-wrap">
          <div class="dl-progress-head">
            <div class="dl-progress-text" id="audio-dl-text">Mengunduh audio...</div>
            <button class="dl-cancel-btn" id="btn-cancel-audio-dl" type="button">⏹ Batal</button>
          </div>
          <div class="dl-progress-track">
            <div class="dl-progress-fill" id="audio-dl-fill"></div>
          </div>
        </div>

        <button class="btn-amber" id="btn-dl-starter-audio" type="button" style="margin-bottom:8px;">
          ⚡ Unduh Audio Surah Utama (38 Ayat)
        </button>

        <div class="audio-juz-picker">
          <select id="select-audio-juz" class="select-input" aria-label="Pilih Juz Audio"></select>
          <button class="btn-blue" id="btn-dl-juz-audio" type="button" style="width:auto; padding:0 14px; font-size:13px;">
            ⬇️ Unduh Audio
          </button>
        </div>

        <button class="btn-secondary" id="btn-clear-audio" type="button" style="margin-bottom:8px; color:#B45309; border-color:#FDE68A;">
          🧹 Hapus Cache Audio Murottal
        </button>
        <button class="btn-secondary" id="btn-reset-progress" type="button" style="color:#DC2626; border-color:#FECACA;">
          🗑️ Reset Semua Progres Bintang
        </button>
      </div>
    </div>
  </div>

  <!-- ==================== MODAL UPGRADE VERSI FULL (DEMO LOCK) ==================== -->
  <div class="modal-backdrop hidden" id="modal-demo-lock">
    <div class="modal-card">
      <div class="modal-head">
        <div class="modal-title">🔒 Akses Terbatas (Mode Demo)</div>
        <button class="icon-circle-btn" data-close-modal="modal-demo-lock" type="button">✕</button>
      </div>
      <div class="modal-body" style="text-align:center;">
        <div style="font-size:48px; margin-bottom:8px;">📖✨</div>
        <h3 style="font-family:var(--font-display); font-size:22px; color:var(--navy); margin-bottom:8px;">
          Buka Akses Penuh 30 Juz!
        </h3>
        <p id="demo-lock-desc" style="font-size:14px; color:#475569; font-weight:700; margin-bottom:14px;">
          Halaman atau fitur ini terkunci pada Mode Demo. Miliki Versi Full untuk membuka seluruh 604 halaman, Mode Tebak Ayat &amp; Arti, serta Audio Murottal Offline.
        </p>
        <div style="background:#FEF9C3; border:2px solid #FDE047; border-radius:14px; padding:10px 12px; margin-bottom:16px; font-weight:900; color:#854D0E; font-size:14px;">
          Bayar Sekali Tanpa Langganan:
          <span style="text-decoration:line-through; opacity:0.65; margin:0 4px;"><?php echo ag_rupiah($settings['price_normal']); ?></span>
          <span style="font-size:18px; color:#166534;"><?php echo ag_rupiah($settings['price_promo']); ?></span>
        </div>
        <a href="./checkout.php" class="btn-primary" style="display:block; text-decoration:none; margin-bottom:10px;">
          🔓 Beli &amp; Download Versi Full Sekarang
        </a>
        <a href="<?php echo ag_h($waAskUrl); ?>" target="_blank" rel="noopener" class="btn-secondary" style="display:block; text-decoration:none; color:#15803D; border-color:#86EFAC;">
          💬 Tanya Admin via WhatsApp
        </a>
      </div>
    </div>
  </div>

  <div class="toast" id="toast"></div>

    <script>
    window.DEMO_CONFIG = {
      enabled: true,
      allowedPages: <?php echo json_encode($allowedPages); ?>,
      maxVerses: <?php echo (int)$maxVerses; ?>,
      buyUrl: './checkout.php',
      waUrl: <?php echo json_encode($waAskUrl); ?>
    };
  </script>
  <script src="./app.js?v=7"></script>
</body>
</html>
