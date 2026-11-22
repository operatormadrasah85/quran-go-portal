<?php
require_once __DIR__ . '/config.php';

$settings = ag_load_settings();
$methods = ag_active_payment_methods($settings);
$errorMsg = '';
$activeOrder = null;
$showLookup = isset($_GET['cek']);

// Cek pesanan berdasarkan kode invoice di URL
if (!empty($_GET['inv'])) {
    $activeOrder = ag_find_order_by_invoice($_GET['inv']);
    if (!$activeOrder) {
        $errorMsg = 'Kode invoice "' . ag_h($_GET['inv']) . '" tidak ditemukan. Periksa kembali penulisan kode invoice Anda.';
        $showLookup = true;
    }
}

// Proses submit pembayaran baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_order') {
    if (!ag_verify_csrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $errorMsg = 'Sesi formulir tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } else {
        $buyerName = trim((string)(isset($_POST['buyer_name']) ? $_POST['buyer_name'] : ''));
        $buyerWa = trim((string)(isset($_POST['buyer_wa']) ? $_POST['buyer_wa'] : ''));
        $buyerEmail = trim((string)(isset($_POST['buyer_email']) ? $_POST['buyer_email'] : ''));
        $payMethodKey = trim((string)(isset($_POST['payment_method']) ? $_POST['payment_method'] : ''));

        if ($buyerName === '' || $buyerWa === '') {
            $errorMsg = 'Nama lengkap dan nomor WhatsApp wajib diisi.';
        } elseif (!isset($methods[$payMethodKey])) {
            $errorMsg = 'Pilih metode pembayaran E-Wallet atau QRIS yang tersedia.';
        } elseif (!isset($_FILES['proof_file']) || $_FILES['proof_file']['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = 'Silakan unggah foto/screenshot bukti pembayaran Anda terlebih dahulu.';
        } else {
            $file = $_FILES['proof_file'];
            $maxSize = 5 * 1024 * 1024; // 5 MB
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExt = array('jpg', 'jpeg', 'png', 'webp', 'pdf');

            if ($file['size'] > $maxSize) {
                $errorMsg = 'Ukuran file bukti transfer maksimal 5 MB.';
            } elseif (!in_array($ext, $allowedExt, true)) {
                $errorMsg = 'Format file bukti harus JPG, PNG, WEBP, atau PDF.';
            } else {
                $invoice = ag_generate_invoice();
                $safeFile = 'proof_' . strtolower($invoice) . '_' . substr(bin2hex(random_bytes(4)), 0, 8) . '.' . $ext;
                $destPath = AG_UPLOAD_DIR . '/' . $safeFile;

                if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                    $errorMsg = 'Gagal menyimpan file bukti transfer. Periksa izin tulis folder uploads/.';
                } else {
                    $isInstant = ($settings['payment_verification_mode'] === 'instant');
                    $newOrder = array(
                        'invoice' => $invoice,
                        'created_at' => date('Y-m-d H:i:s'),
                        'paid_at' => $isInstant ? date('Y-m-d H:i:s') : '',
                        'buyer_name' => $buyerName,
                        'buyer_wa' => ag_clean_wa($buyerWa),
                        'buyer_email' => $buyerEmail,
                        'method_key' => $payMethodKey,
                        'method_label' => $methods[$payMethodKey]['label'],
                        'amount' => intval($settings['price_promo']),
                        'proof_file' => 'uploads/' . $safeFile,
                        'status' => $isInstant ? 'paid' : 'pending', // 'pending' | 'paid' | 'rejected'
                        'download_token' => ag_generate_token(),
                        'download_count' => 0,
                        'last_download_at' => ''
                    );

                    $orders = ag_load_orders();
                    array_unshift($orders, $newOrder);
                    ag_save_orders($orders);

                    header('Location: ./checkout.php?inv=' . urlencode($invoice));
                    exit;
                }
            }
        }
    }
}

$csrf = ag_csrf_token();
$waAskUrl = ag_wa_link($settings['wa_number'], $settings['wa_message_ask']);
$demoUrl = trim((string)$settings['demo_url']) !== '' ? $settings['demo_url'] : './demo.php';

// Siapkan link WA konfirmasi jika sedang membuka invoice
$waConfirmUrl = '';
if ($activeOrder) {
    $tpl = isset($settings['wa_message_confirm']) ? $settings['wa_message_confirm'] : '';
    $msg = str_replace(
        array('{invoice}', '{name}', '{method}', '{amount}'),
        array($activeOrder['invoice'], $activeOrder['buyer_name'], $activeOrder['method_label'], ag_rupiah($activeOrder['amount'])),
        $tpl
    );
    $waConfirmUrl = ag_wa_link($settings['wa_number'], $msg);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#142B5E" />
  <title>Pembayaran &amp; Download — <?php echo ag_h($settings['app_name']); ?></title>
  <link rel="icon" type="image/svg+xml" href="./icon.svg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600;700&family=Nunito:wght@600;700;800;900&display=swap" rel="stylesheet" />
  <style>
    :root {
      --navy: #142B5E;
      --green-1: #5AC82A;
      --green-2: #3E9F15;
      --sky-top: #74C5FA;
      --sky-bot: #DDF2FF;
      --muted: #64748B;
      --border: #E2E8F0;
      --font-ui: 'Nunito', sans-serif;
      --font-display: 'Fredoka', sans-serif;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: var(--font-ui);
      background: linear-gradient(180deg, var(--sky-top) 0%, var(--sky-bot) 260px, #F8FAFC 260px);
      min-height: 100vh;
      color: #1E293B;
      padding-bottom: 48px;
    }
    .topbar {
      max-width: 760px;
      margin: 0 auto;
      padding: 14px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
    }
    .back-pill {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      background: #FFFFFF;
      color: var(--navy);
      text-decoration: none;
      font-weight: 900;
      font-size: 13.5px;
      padding: 8px 14px;
      border-radius: 999px;
      box-shadow: 0 3px 0 rgba(20, 43, 94, 0.12);
    }
    .wrap {
      max-width: 760px;
      margin: 0 auto;
      padding: 0 16px;
    }
    .card {
      background: #FFFFFF;
      border-radius: 24px;
      border: 2px solid #E8F2E4;
      box-shadow: 0 14px 35px rgba(20, 43, 94, 0.1);
      padding: 24px 20px;
      margin-bottom: 18px;
    }
    .title {
      font-family: var(--font-display);
      font-size: clamp(23px, 5vw, 30px);
      color: var(--navy);
      margin-bottom: 6px;
    }
    .sub {
      font-size: 14px;
      color: var(--muted);
      font-weight: 700;
      margin-bottom: 18px;
    }
    .price-banner {
      background: linear-gradient(135deg, #142B5E 0%, #1D4ED8 100%);
      color: #FFFFFF;
      border-radius: 18px;
      padding: 16px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 10px;
      margin-bottom: 20px;
    }
    .price-banner h2 {
      font-family: var(--font-display);
      font-size: 20px;
    }
    .price-val {
      text-align: right;
    }
    .price-old {
      font-size: 13px;
      text-decoration: line-through;
      opacity: 0.72;
      display: block;
    }
    .price-now {
      font-family: var(--font-display);
      font-size: 28px;
      color: #FFE138;
      line-height: 1.1;
    }
    .alert {
      border-radius: 14px;
      padding: 12px 15px;
      font-size: 13.5px;
      font-weight: 800;
      margin-bottom: 16px;
    }
    .alert-err { background: #FEF2F2; border: 2px solid #FECACA; color: #B91C1C; }
    .alert-ok  { background: #F0FDF4; border: 2px solid #86EFAC; color: #166534; }
    .alert-warn{ background: #FFFBEB; border: 2px solid #FDE68A; color: #92400E; }

    .section-label {
      font-size: 14px;
      font-weight: 900;
      color: var(--navy);
      margin: 18px 0 10px;
      display: flex;
      align-items: center;
      gap: 7px;
    }
    .form-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 12px;
    }
    @media (min-width: 600px) {
      .form-grid.two { grid-template-columns: 1fr 1fr; }
    }
    .field label {
      display: block;
      font-size: 13px;
      font-weight: 800;
      color: #334155;
      margin-bottom: 5px;
    }
    .field input[type="text"],
    .field input[type="tel"],
    .field input[type="email"],
    .field input[type="file"] {
      width: 100%;
      padding: 11px 13px;
      border-radius: 13px;
      border: 2px solid #CBD5E1;
      font-family: var(--font-ui);
      font-size: 14.5px;
      font-weight: 800;
      color: var(--navy);
      background: #FFFFFF;
      outline: none;
    }
    .field input:focus { border-color: var(--green-1); }

    .pay-options {
      display: grid;
      grid-template-columns: 1fr;
      gap: 10px;
      margin-bottom: 14px;
    }
    @media (min-width: 560px) {
      .pay-options { grid-template-columns: repeat(2, 1fr); }
    }
    .pay-opt {
      border: 2px solid #CBD5E1;
      border-radius: 15px;
      padding: 12px 14px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 900;
      font-size: 14px;
      color: var(--navy);
      background: #F8FAFC;
      transition: all 0.15s ease;
    }
    .pay-opt input { width: 18px; height: 18px; accent-color: var(--green-2); }
    .pay-opt.selected {
      border-color: var(--green-1);
      background: #F0FDF4;
      box-shadow: 0 4px 12px rgba(90, 200, 42, 0.16);
    }

    .pay-detail-box {
      background: #FFFDF5;
      border: 2px dashed #D6C79C;
      border-radius: 16px;
      padding: 16px;
      margin-bottom: 16px;
      text-align: center;
    }
    .qris-img {
      max-width: 260px;
      width: 100%;
      border-radius: 14px;
      border: 3px solid #E2E8F0;
      margin: 10px auto;
      display: block;
      background: #FFFFFF;
    }
    .acc-number {
      font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
      font-size: 22px;
      font-weight: 900;
      color: var(--navy);
      letter-spacing: 1px;
      margin: 6px 0;
    }
    .copy-btn {
      border: none;
      background: #E0F2FE;
      color: #0369A1;
      font-family: var(--font-ui);
      font-weight: 900;
      font-size: 13px;
      padding: 6px 14px;
      border-radius: 999px;
      cursor: pointer;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      width: 100%;
      border: none;
      border-radius: 16px;
      padding: 13px 18px;
      font-family: var(--font-ui);
      font-weight: 900;
      font-size: 15.5px;
      cursor: pointer;
      text-decoration: none;
      transition: transform 0.12s ease;
    }
    .btn:active { transform: translateY(2px); }
    .btn-green {
      background: linear-gradient(180deg, #62D42F 0%, #44A71A 100%);
      color: #FFFFFF;
      box-shadow: 0 4px 0 #2E7A0F;
    }
    .btn-gold {
      background: linear-gradient(180deg, #FFE852 0%, #FFB700 100%);
      color: var(--navy);
      box-shadow: 0 4px 0 #B45309;
    }
    .btn-wa {
      background: #25D366;
      color: #FFFFFF;
      box-shadow: 0 4px 0 #15803D;
    }
    .btn-outline {
      background: #FFFFFF;
      color: var(--navy);
      border: 2px solid #CBD5E1;
    }

    .inv-badge {
      display: inline-block;
      background: #EFF6FF;
      border: 2px solid #BFDBFE;
      color: #1E3A8A;
      font-family: ui-monospace, monospace;
      font-weight: 900;
      font-size: 16px;
      padding: 6px 14px;
      border-radius: 12px;
      margin-bottom: 12px;
    }
    .status-pill {
      display: inline-block;
      padding: 6px 14px;
      border-radius: 999px;
      font-weight: 900;
      font-size: 13.5px;
      margin-bottom: 12px;
    }
    .st-paid { background: #DCFCE7; color: #166534; }
    .st-pending { background: #FEF3C7; color: #92400E; }
    .st-rejected { background: #FEE2E2; color: #991B1B; }

    .summary-table {
      width: 100%;
      border-collapse: collapse;
      margin: 14px 0 18px;
      font-size: 14px;
    }
    .summary-table td {
      padding: 8px 4px;
      border-bottom: 1px dashed #E2E8F0;
      font-weight: 700;
    }
    .summary-table td:last-child {
      text-align: right;
      font-weight: 900;
      color: var(--navy);
    }
  </style>
</head>
<body>

  <div class="topbar">
    <a href="./index.php" class="back-pill">⬅ Beranda</a>
    <div style="display:flex; gap:8px;">
      <a href="<?php echo ag_h($demoUrl); ?>" class="back-pill">🎮 Demo</a>
      <a href="./checkout.php?cek=1" class="back-pill">🔍 Cek Invoice</a>
    </div>
  </div>

  <div class="wrap">

    <?php if ($errorMsg !== ''): ?>
      <div class="alert alert-err">⚠️ <?php echo ag_h($errorMsg); ?></div>
    <?php endif; ?>

    <?php if ($activeOrder): ?>
      <!-- ================= HALAMAN STATUS PESANAN & DOWNLOAD ================= -->
      <div class="card" style="text-align:center;">
        <div class="inv-badge">Invoice: <?php echo ag_h($activeOrder['invoice']); ?></div>
        <br />

        <?php if ($activeOrder['status'] === 'paid'): ?>
          <span class="status-pill st-paid">✅ PEMBAYARAN BERHASIL (LUNAS)</span>
          <h1 class="title">Alhamdulillah, Pembayaran Berhasil!</h1>
          <p class="sub">Terima kasih <strong><?php echo ag_h($activeOrder['buyer_name']); ?></strong>! Link download aplikasi Full 30 Juz Anda sudah aktif dan bisa langsung diunduh di bawah ini.</p>

          <div style="background:#F0FDF4; border:2px solid #86EFAC; border-radius:18px; padding:18px; margin-bottom:16px;">
            <a href="./download.php?token=<?php echo urlencode($activeOrder['download_token']); ?>" class="btn btn-green" style="font-size:17px; padding:15px;">
              ⬇️ Download Aplikasi Full 30 Juz Sekarang
            </a>
            <?php if (!empty($settings['download_notes'])): ?>
              <p style="font-size:13px; color:#166534; font-weight:700; margin-top:10px;">
                📌 <?php echo ag_h($settings['download_notes']); ?>
              </p>
            <?php endif; ?>
          </div>

          <a href="./index.php#instalasi" class="btn btn-outline" style="margin-bottom:10px;">📖 Baca Panduan Instalasi Offline &amp; Vercel</a>

        <?php elseif ($activeOrder['status'] === 'rejected'): ?>
          <span class="status-pill st-rejected">❌ PEMBAYARAN DITOLAK</span>
          <h1 class="title">Bukti Pembayaran Belum Sesuai</h1>
          <p class="sub">Mohon maaf, bukti pembayaran Anda belum dapat kami verifikasi. Silakan hubungi Admin melalui WhatsApp untuk bantuan.</p>
          <a href="<?php echo ag_h($waConfirmUrl); ?>" target="_blank" rel="noopener" class="btn btn-wa" style="margin-bottom:10px;">
            💬 Hubungi Admin via WhatsApp
          </a>
          <a href="./checkout.php" class="btn btn-outline">🔄 Buat Pesanan Ulang</a>

        <?php else: ?>
          <span class="status-pill st-pending">⏳ MENUNGGU VERIFIKASI ADMIN</span>
          <h1 class="title">Bukti Pembayaran Berhasil Dikirim!</h1>
          <p class="sub">
            Simpan kode invoice <strong><?php echo ag_h($activeOrder['invoice']); ?></strong>. Halaman ini akan <strong>otomatis menampilkan tombol Download</strong> begitu Admin menyetujui pembayaran Anda!
          </p>

          <div id="live-download-box" style="display:none; background:#F0FDF4; border:2px solid #86EFAC; border-radius:18px; padding:18px; margin-bottom:16px;">
            <div style="font-weight:900; color:#166534; font-size:16px; margin-bottom:10px;">🎉 Pembayaran Sudah Diverifikasi Lunas!</div>
            <a id="live-download-link" href="#" class="btn btn-green">⬇️ Download Aplikasi Full 30 Juz Sekarang</a>
          </div>

          <a href="<?php echo ag_h($waConfirmUrl); ?>" target="_blank" rel="noopener" class="btn btn-wa" style="margin-bottom:10px;">
            💬 Konfirmasi Cepat via WhatsApp Admin
          </a>
          <p id="poll-status" style="font-size:12.5px; color:#64748B; font-weight:800; margin-top:6px;">
            🔄 Mengecek status pembayaran otomatis setiap 5 detik...
          </p>

          <script>
            (function() {
              const inv = <?php echo json_encode($activeOrder['invoice']); ?>;
              const checkStatus = async () => {
                try {
                  const res = await fetch('./api_order_status.php?inv=' + encodeURIComponent(inv), { cache: 'no-store' });
                  if (!res.ok) return;
                  const data = await res.json();
                  if (data && data.paid && data.download_url) {
                    const box = document.getElementById('live-download-box');
                    const link = document.getElementById('live-download-link');
                    const p = document.getElementById('poll-status');
                    if (link) link.href = data.download_url;
                    if (box) box.style.display = 'block';
                    if (p) p.textContent = '✅ Status Lunas! Silakan klik tombol Download di atas.';
                    clearInterval(timer);
                  } else if (data && data.status === 'rejected') {
                    window.location.reload();
                  }
                } catch (e) {}
              };
              const timer = setInterval(checkStatus, 5000);
            })();
          </script>
        <?php endif; ?>

        <table class="summary-table" style="text-align:left;">
          <tr><td>Nama Pembeli</td><td><?php echo ag_h($activeOrder['buyer_name']); ?></td></tr>
          <tr><td>WhatsApp</td><td><?php echo ag_h($activeOrder['buyer_wa']); ?></td></tr>
          <tr><td>Metode Pembayaran</td><td><?php echo ag_h($activeOrder['method_label']); ?></td></tr>
          <tr><td>Nominal</td><td><?php echo ag_rupiah($activeOrder['amount']); ?></td></tr>
          <tr><td>Waktu Order</td><td><?php echo ag_h($activeOrder['created_at']); ?></td></tr>
        </table>

        <a href="<?php echo ag_h($waAskUrl); ?>" target="_blank" rel="noopener" style="font-size:13.5px; font-weight:800; color:#15803D; text-decoration:underline;">
          Butuh bantuan? Hubungi <?php echo ag_h($settings['wa_admin_name']); ?>
        </a>
      </div>

    <?php elseif ($showLookup): ?>
      <!-- ================= FORM CEK STATUS INVOICE ================= -->
      <div class="card">
        <h1 class="title">🔍 Cek Status Pesanan &amp; Download</h1>
        <p class="sub">Masukkan Kode Invoice Anda (contoh: <code>AG-20261122-XXXX</code>) untuk membuka kembali link download aplikasi.</p>
        <form method="get" action="./checkout.php">
          <div class="field" style="margin-bottom:14px;">
            <label for="inv">Kode Invoice</label>
            <input type="text" id="inv" name="inv" required placeholder="Contoh: AG-20261122-A1B2" value="<?php echo isset($_GET['inv']) ? ag_h($_GET['inv']) : ''; ?>" />
          </div>
          <button type="submit" class="btn btn-green">🔎 Cek Pesanan Saya</button>
        </form>
        <div style="text-align:center; margin-top:14px;">
          <a href="./checkout.php" style="font-weight:800; font-size:14px; color:#1D4ED8; text-decoration:underline;">← Kembali ke Halaman Pembelian</a>
        </div>
      </div>

    <?php else: ?>
      <!-- ================= FORM PEMBELIAN & PEMBAYARAN ================= -->
      <div class="card">
        <h1 class="title">🛒 Dapatkan <?php echo ag_h($settings['app_name']); ?> Full 30 Juz</h1>
        <p class="sub">Sekali bayar tanpa langganan. Setelah pembayaran berhasil, link download aplikasi Full 30 Juz langsung terbuka!</p>

        <div class="price-banner">
          <div>
            <h2>Paket Full Akses 30 Juz (PWA + Offline)</h2>
            <div style="font-size:12.5px; opacity:0.9; font-weight:700;">604 Halaman · Tebak Ayat &amp; Arti · Murottal Offline · 12 Kartu Hadiah</div>
          </div>
          <div class="price-val">
            <span class="price-old"><?php echo ag_rupiah($settings['price_normal']); ?></span>
            <span class="price-now"><?php echo ag_rupiah($settings['price_promo']); ?></span>
          </div>
        </div>

        <?php if (empty($methods)): ?>
          <div class="alert alert-warn">
            Metode pembayaran belum diaktifkan oleh Admin. Silakan hubungi Admin langsung via WhatsApp di bawah ini.
          </div>
          <a href="<?php echo ag_h($waAskUrl); ?>" target="_blank" rel="noopener" class="btn btn-wa">💬 Pesan via WhatsApp Admin</a>
        <?php else: ?>
          <form method="post" action="./checkout.php" enctype="multipart/form-data" id="checkout-form">
            <input type="hidden" name="action" value="create_order" />
            <input type="hidden" name="csrf_token" value="<?php echo ag_h($csrf); ?>" />

            <div class="section-label">1️⃣ Data Pembeli</div>
            <div class="form-grid two">
              <div class="field">
                <label for="buyer_name">Nama Lengkap *</label>
                <input type="text" id="buyer_name" name="buyer_name" required placeholder="Contoh: Ibu Siti / Pak Budi" value="<?php echo isset($_POST['buyer_name']) ? ag_h($_POST['buyer_name']) : ''; ?>" />
              </div>
              <div class="field">
                <label for="buyer_wa">Nomor WhatsApp *</label>
                <input type="tel" id="buyer_wa" name="buyer_wa" required placeholder="Contoh: 081234567890" value="<?php echo isset($_POST['buyer_wa']) ? ag_h($_POST['buyer_wa']) : ''; ?>" />
              </div>
            </div>
            <div class="field" style="margin-top:10px;">
              <label for="buyer_email">Email (Opsional)</label>
              <input type="email" id="buyer_email" name="buyer_email" placeholder="nama@email.com" value="<?php echo isset($_POST['buyer_email']) ? ag_h($_POST['buyer_email']) : ''; ?>" />
            </div>

            <div class="section-label">2️⃣ Pilih Metode Pembayaran (QRIS / E-Wallet)</div>
            <div class="pay-options" id="pay-options">
              <?php $firstIdx = 0; foreach ($methods as $mKey => $m): ?>
                <label class="pay-opt <?php echo $firstIdx === 0 ? 'selected' : ''; ?>">
                  <input type="radio" name="payment_method" value="<?php echo ag_h($mKey); ?>" <?php echo $firstIdx === 0 ? 'checked' : ''; ?> />
                  <span><?php echo $m['type'] === 'qris' ? '📱 ' : '💳 '; ?><?php echo ag_h($m['label']); ?></span>
                </label>
              <?php $firstIdx++; endforeach; ?>
            </div>

            <!-- Instruksi Detail Metode Pembayaran Terpilih -->
            <div class="pay-detail-box" id="pay-detail-box"></div>

            <div class="section-label">3️⃣ Upload Bukti Pembayaran</div>
            <div class="field" style="margin-bottom:16px;">
              <label for="proof_file">Screenshot / Foto Bukti Transfer (JPG, PNG, WEBP, PDF · Maks 5MB) *</label>
              <input type="file" id="proof_file" name="proof_file" accept=".jpg,.jpeg,.png,.webp,.pdf" required />
            </div>

            <button type="submit" class="btn btn-green">
              <?php echo ($settings['payment_verification_mode'] === 'instant') ? '✅ Kirim Bukti &amp; Buka Download Langsung' : '✅ Kirim Bukti Pembayaran'; ?>
            </button>
          </form>

          <div style="text-align:center; margin-top:14px;">
            <a href="<?php echo ag_h($waAskUrl); ?>" target="_blank" rel="noopener" style="font-size:13.5px; font-weight:800; color:#15803D; text-decoration:underline;">
              💬 Ada kendala pembayaran? Chat Admin via WhatsApp
            </a>
          </div>

          <script>
            (function() {
              const methodsData = <?php echo json_encode($methods, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
              const promoRupiah = <?php echo json_encode(ag_rupiah($settings['price_promo'])); ?>;
              const detailBox = document.getElementById('pay-detail-box');
              const radios = document.querySelectorAll('input[name="payment_method"]');

              function esc(s) {
                return String(s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
              }

              function renderDetail(key) {
                const m = methodsData[key];
                if (!m) return;
                if (m.type === 'qris') {
                  const imgHtml = m.image
                    ? `<img src="./${esc(m.image)}" alt="QRIS Pembayaran" class="qris-img" />
                       <a href="./${esc(m.image)}" download class="copy-btn" style="display:inline-block; text-decoration:none; margin-top:6px;">⬇️ Simpan Gambar QRIS</a>`
                    : `<div style="padding:16px; background:#FEF3C7; border-radius:12px; color:#92400E; font-weight:800; font-size:13px; margin:10px 0;">Admin belum mengunggah gambar QRIS. Silakan pilih E-Wallet lain atau hubungi WhatsApp Admin.</div>`;
                  detailBox.innerHTML = `
                    <div style="font-weight:900; color:#142B5E; font-size:15px;">Scan QRIS Menggunakan DANA / OVO / GoPay / ShopeePay / M-Banking</div>
                    <div style="font-size:13px; color:#64748B; font-weight:700; margin-top:2px;">Merchant: <strong>${esc(m.merchant)}</strong> · Nominal: <strong style="color:#166534;">${esc(promoRupiah)}</strong></div>
                    ${imgHtml}
                  `;
                } else {
                  detailBox.innerHTML = `
                    <div style="font-weight:900; color:#142B5E; font-size:15px;">Transfer ke ${esc(m.label)}</div>
                    <div class="acc-number" id="copy-target">${esc(m.number)}</div>
                    <div style="font-size:13.5px; color:#475569; font-weight:800; margin-bottom:10px;">
                      Atas Nama: <strong style="color:#142B5E;">${esc(m.holder)}</strong> · Nominal: <strong style="color:#166534;">${esc(promoRupiah)}</strong>
                    </div>
                    <button type="button" class="copy-btn" id="btn-copy-num">📋 Salin Nomor</button>
                  `;
                  const cp = document.getElementById('btn-copy-num');
                  if (cp) {
                    cp.addEventListener('click', () => {
                      navigator.clipboard.writeText(m.number).then(() => {
                        cp.textContent = '✅ Nomor Disalin!';
                        setTimeout(() => { cp.textContent = '📋 Salin Nomor'; }, 2000);
                      }).catch(() => {
                        prompt('Salin nomor tujuan pembayaran:', m.number);
                      });
                    });
                  }
                }
              }

              radios.forEach(r => {
                r.addEventListener('change', () => {
                  document.querySelectorAll('.pay-opt').forEach(l => l.classList.remove('selected'));
                  r.closest('.pay-opt').classList.add('selected');
                  renderDetail(r.value);
                });
              });

              const checked = document.querySelector('input[name="payment_method"]:checked');
              if (checked) renderDetail(checked.value);
            })();
          </script>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
