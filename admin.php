<?php
require_once __DIR__ . '/config.php';

$settings = ag_load_settings();
$flashSuccess = '';
$flashError = '';

// Proses Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['ag_admin_logged_in']);
    header('Location: ./admin.php');
    exit;
}

// Proses Login Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $u = trim((string)(isset($_POST['username']) ? $_POST['username'] : ''));
    $p = (string)(isset($_POST['password']) ? $_POST['password'] : '');
    if ($u === $settings['admin_username'] && password_verify($p, $settings['admin_password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['ag_admin_logged_in'] = true;
        header('Location: ./admin.php');
        exit;
    } else {
        $flashError = 'Username atau password admin salah.';
    }
}

$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'orders';
if (!in_array($activeTab, array('orders', 'demo', 'payment', 'whatsapp'), true)) {
    $activeTab = 'orders';
}

// Proses Aksi Admin (Hanya jika sudah login)
if (ag_is_admin() && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

    if (!ag_verify_csrf($token)) {
        $flashError = 'Token keamanan (CSRF) tidak valid. Silakan coba lagi.';
    } elseif ($action === 'update_order_status') {
        $inv = trim((string)(isset($_POST['invoice']) ? $_POST['invoice'] : ''));
        $newStatus = trim((string)(isset($_POST['new_status']) ? $_POST['new_status'] : ''));
        if (in_array($newStatus, array('paid', 'pending', 'rejected', 'delete'), true)) {
            $orders = ag_load_orders();
            $updated = false;
            foreach ($orders as $idx => $o) {
                if ($o['invoice'] === $inv) {
                    if ($newStatus === 'delete') {
                        array_splice($orders, $idx, 1);
                        $flashSuccess = 'Pesanan ' . $inv . ' berhasil dihapus.';
                    } else {
                        $orders[$idx]['status'] = $newStatus;
                        if ($newStatus === 'paid') {
                            $orders[$idx]['paid_at'] = date('Y-m-d H:i:s');
                            $flashSuccess = 'Pesanan ' . $inv . ' disetujui LUNAS! Link download pembeli langsung terbuka.';
                        } elseif ($newStatus === 'rejected') {
                            $flashSuccess = 'Pesanan ' . $inv . ' ditandai DITOLAK.';
                        } else {
                            $flashSuccess = 'Pesanan ' . $inv . ' dikembalikan ke MENUNGGU.';
                        }
                    }
                    $updated = true;
                    break;
                }
            }
            if ($updated) ag_save_orders($orders);
        }
        $activeTab = 'orders';

    } elseif ($action === 'save_demo_product') {
        $settings['app_name'] = trim((string)$_POST['app_name']) !== '' ? trim((string)$_POST['app_name']) : "Al-Qur'an Go";
        $settings['app_tagline'] = trim((string)$_POST['app_tagline']);
        $settings['price_normal'] = max(0, intval($_POST['price_normal']));
        $settings['price_promo'] = max(0, intval($_POST['price_promo']));
        $settings['demo_url'] = trim((string)$_POST['demo_url']) !== '' ? trim((string)$_POST['demo_url']) : './demo.php';
        $settings['demo_allowed_pages'] = implode(', ', ag_parse_pages_csv($_POST['demo_allowed_pages']));
        $settings['demo_max_verses'] = max(0, intval($_POST['demo_max_verses']));
        $settings['download_mode'] = (isset($_POST['download_mode']) && $_POST['download_mode'] === 'external_link') ? 'external_link' : 'local_file';
        $settings['download_external_url'] = trim((string)$_POST['download_external_url']);
        $settings['download_notes'] = trim((string)$_POST['download_notes']);

        // Jika Admin mengunggah file ZIP aplikasi Full baru
        if (isset($_FILES['full_zip_file']) && $_FILES['full_zip_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['full_zip_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, array('zip', 'rar', 'apk', 'pdf'), true)) {
                $cleanBase = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['full_zip_file']['name']));
                $dest = AG_PROTECTED_DIR . '/' . $cleanBase;
                if (move_uploaded_file($_FILES['full_zip_file']['tmp_name'], $dest)) {
                    $settings['download_local_file'] = $cleanBase;
                } else {
                    $flashError = 'Gagal menyimpan file aplikasi Full ke folder protected_files/.';
                }
            } else {
                $flashError = 'Format file aplikasi yang diizinkan: ZIP, RAR, APK, atau PDF.';
            }
        }

        if ($flashError === '') {
            ag_save_settings($settings);
            $flashSuccess = 'Pengaturan Link Demo, Batasan Demo, Harga, & File Download berhasil disimpan!';
        }
        $activeTab = 'demo';

    } elseif ($action === 'save_payment') {
        $settings['payment_verification_mode'] = (isset($_POST['payment_verification_mode']) && $_POST['payment_verification_mode'] === 'instant') ? 'instant' : 'manual';
        $settings['qris_enabled'] = !empty($_POST['qris_enabled']);
        $settings['qris_merchant'] = trim((string)$_POST['qris_merchant']);

        // Upload Gambar QRIS
        if (isset($_FILES['qris_file']) && $_FILES['qris_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['qris_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, array('jpg', 'jpeg', 'png', 'webp'), true)) {
                $qrisName = 'qris_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['qris_file']['tmp_name'], AG_UPLOAD_DIR . '/' . $qrisName)) {
                    $settings['qris_image'] = 'uploads/' . $qrisName;
                } else {
                    $flashError = 'Gagal mengunggah gambar QRIS ke folder uploads/.';
                }
            } else {
                $flashError = 'Format gambar QRIS harus JPG, PNG, atau WEBP.';
            }
        }
        if (!empty($_POST['remove_qris_image'])) {
            $settings['qris_image'] = '';
        }

        foreach ($settings['ewallets'] as $key => $ew) {
            $settings['ewallets'][$key]['enabled'] = !empty($_POST['ew_enabled_' . $key]);
            $settings['ewallets'][$key]['number'] = trim((string)(isset($_POST['ew_number_' . $key]) ? $_POST['ew_number_' . $key] : ''));
            $settings['ewallets'][$key]['holder'] = trim((string)(isset($_POST['ew_holder_' . $key]) ? $_POST['ew_holder_' . $key] : ''));
        }

        if ($flashError === '') {
            ag_save_settings($settings);
            $flashSuccess = 'Pengaturan Pembayaran QRIS & E-Wallet berhasil disimpan!';
        }
        $activeTab = 'payment';

    } elseif ($action === 'save_wa_account') {
        $settings['wa_number'] = ag_clean_wa($_POST['wa_number']);
        $settings['wa_admin_name'] = trim((string)$_POST['wa_admin_name']);
        $settings['wa_message_ask'] = trim((string)$_POST['wa_message_ask']);
        $settings['wa_message_confirm'] = trim((string)$_POST['wa_message_confirm']);

        $newUser = trim((string)(isset($_POST['new_admin_username']) ? $_POST['new_admin_username'] : ''));
        $newPass = (string)(isset($_POST['new_admin_password']) ? $_POST['new_admin_password'] : '');

        if ($newUser !== '') {
            $settings['admin_username'] = $newUser;
        }
        if ($newPass !== '') {
            if (strlen($newPass) < 6) {
                $flashError = 'Password baru minimal 6 karakter.';
            } else {
                $settings['admin_password_hash'] = password_hash($newPass, PASSWORD_DEFAULT);
                $settings['is_default_password'] = false;
            }
        }

        if ($flashError === '') {
            ag_save_settings($settings);
            $flashSuccess = 'Pengaturan Nomor WhatsApp & Akun Admin berhasil disimpan!';
        }
        $activeTab = 'whatsapp';
    }
}

$csrf = ag_csrf_token();
$orders = ag_is_admin() ? ag_load_orders() : array();

// Hitung ringkasan statistik pesanan
$statTotal = count($orders);
$statPending = 0;
$statPaid = 0;
$statRevenue = 0;
foreach ($orders as $o) {
    if ($o['status'] === 'pending') $statPending++;
    if ($o['status'] === 'paid') {
        $statPaid++;
        $statRevenue += intval($o['amount']);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Panel Admin — <?php echo ag_h($settings['app_name']); ?></title>
  <link rel="icon" type="image/svg+xml" href="./icon.svg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600;700&family=Nunito:wght@600;700;800;900&display=swap" rel="stylesheet" />
  <style>
    :root {
      --navy: #142B5E;
      --green: #44A71A;
      --blue: #2563EB;
      --bg: #F1F5F9;
      --border: #E2E8F0;
      --muted: #64748B;
      --font-ui: 'Nunito', sans-serif;
      --font-display: 'Fredoka', sans-serif;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: var(--font-ui);
      background: var(--bg);
      color: #1E293B;
      min-height: 100vh;
      padding-bottom: 48px;
    }
    .login-wrap {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      background: linear-gradient(135deg, #142B5E 0%, #2563EB 100%);
    }
    .login-card {
      width: 100%;
      max-width: 400px;
      background: #FFFFFF;
      border-radius: 24px;
      padding: 28px 24px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
    }
    .admin-nav {
      position: sticky;
      top: 0;
      z-index: 40;
      background: var(--navy);
      color: #FFFFFF;
      padding: 12px 16px;
      box-shadow: 0 4px 16px rgba(15, 23, 42, 0.18);
    }
    .admin-nav-inner {
      max-width: 1060px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      flex-wrap: wrap;
    }
    .brand {
      display: flex;
      align-items: center;
      gap: 9px;
      font-family: var(--font-display);
      font-size: 20px;
      color: #FFE138;
    }
    .nav-btns { display: flex; gap: 8px; flex-wrap: wrap; }
    .nav-link {
      background: rgba(255, 255, 255, 0.14);
      color: #FFFFFF;
      text-decoration: none;
      font-size: 12.5px;
      font-weight: 800;
      padding: 6px 12px;
      border-radius: 10px;
    }
    .nav-link.danger { background: rgba(239, 68, 68, 0.3); }

    .container {
      max-width: 1060px;
      margin: 18px auto 0;
      padding: 0 16px;
    }
    .tabs {
      display: flex;
      gap: 8px;
      overflow-x: auto;
      padding-bottom: 6px;
      margin-bottom: 16px;
    }
    .tab {
      background: #FFFFFF;
      border: 2px solid var(--border);
      color: var(--navy);
      text-decoration: none;
      font-weight: 900;
      font-size: 13.5px;
      padding: 9px 15px;
      border-radius: 13px;
      white-space: nowrap;
    }
    .tab.active {
      background: var(--navy);
      color: #FFFFFF;
      border-color: var(--navy);
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
      margin-bottom: 18px;
    }
    @media (min-width: 720px) {
      .stats-grid { grid-template-columns: repeat(4, 1fr); }
    }
    .stat-card {
      background: #FFFFFF;
      border-radius: 18px;
      padding: 15px 16px;
      border: 1.5px solid var(--border);
      box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
    }
    .stat-label { font-size: 12px; font-weight: 800; color: var(--muted); }
    .stat-val { font-family: var(--font-display); font-size: 24px; color: var(--navy); margin-top: 2px; }

    .card {
      background: #FFFFFF;
      border-radius: 20px;
      border: 1.5px solid var(--border);
      padding: 20px;
      margin-bottom: 16px;
      box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
    }
    .card h2 {
      font-family: var(--font-display);
      font-size: 20px;
      color: var(--navy);
      margin-bottom: 12px;
    }
    .alert {
      border-radius: 14px;
      padding: 11px 14px;
      font-size: 13.5px;
      font-weight: 800;
      margin-bottom: 14px;
    }
    .alert-ok { background: #DCFCE7; color: #166534; border: 1.5px solid #86EFAC; }
    .alert-err { background: #FEE2E2; color: #991B1B; border: 1.5px solid #FECACA; }
    .alert-warn { background: #FEF3C7; color: #92400E; border: 1.5px solid #FDE68A; }

    .grid-2 { display: grid; grid-template-columns: 1fr; gap: 14px; }
    @media (min-width: 680px) { .grid-2 { grid-template-columns: 1fr 1fr; } }

    .field { margin-bottom: 13px; }
    .field label { display: block; font-size: 13px; font-weight: 800; color: #334155; margin-bottom: 5px; }
    .field small { display: block; font-size: 11.5px; color: var(--muted); font-weight: 700; margin-top: 4px; }
    .field input[type="text"],
    .field input[type="number"],
    .field input[type="url"],
    .field input[type="password"],
    .field input[type="file"],
    .field select,
    .field textarea {
      width: 100%;
      padding: 10px 13px;
      border-radius: 12px;
      border: 2px solid #CBD5E1;
      font-family: var(--font-ui);
      font-size: 14px;
      font-weight: 800;
      color: var(--navy);
      background: #FFFFFF;
      outline: none;
    }
    .field textarea { min-height: 92px; resize: vertical; }
    .field input:focus, .field select:focus, .field textarea:focus { border-color: var(--blue); }

    .switch-row {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 900;
      font-size: 14px;
      color: var(--navy);
      margin-bottom: 10px;
      cursor: pointer;
    }
    .switch-row input { width: 19px; height: 19px; accent-color: var(--green); }

    .ew-box {
      background: #F8FAFC;
      border: 1.5px solid var(--border);
      border-radius: 15px;
      padding: 14px;
      margin-bottom: 12px;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      border: none;
      border-radius: 12px;
      padding: 10px 16px;
      font-family: var(--font-ui);
      font-weight: 900;
      font-size: 14px;
      cursor: pointer;
      text-decoration: none;
    }
    .btn-primary { background: linear-gradient(180deg, #5FD32C 0%, #44A819 100%); color: #FFFFFF; box-shadow: 0 3px 0 #2F7A10; }
    .btn-blue { background: var(--blue); color: #FFFFFF; }
    .btn-sm { padding: 6px 10px; font-size: 12px; border-radius: 9px; }
    .btn-red { background: #FEE2E2; color: #B91C1C; }
    .btn-amber { background: #FEF3C7; color: #92400E; }
    .btn-green-soft { background: #DCFCE7; color: #166534; }

    .order-item {
      border: 1.5px solid var(--border);
      border-radius: 16px;
      padding: 14px;
      margin-bottom: 12px;
      background: #FFFFFF;
    }
    .order-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 8px;
      margin-bottom: 8px;
    }
    .badge {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 999px;
      font-size: 11.5px;
      font-weight: 900;
    }
    .b-paid { background: #DCFCE7; color: #166534; }
    .b-pending { background: #FEF3C7; color: #92400E; }
    .b-rejected { background: #FEE2E2; color: #991B1B; }

    .order-meta {
      font-size: 13px;
      color: #475569;
      font-weight: 700;
      line-height: 1.6;
      margin-bottom: 10px;
    }
    .order-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 7px;
    }
    .order-actions form { display: inline; }
  </style>
</head>
<body>

<?php if (!ag_is_admin()): ?>
  <div class="login-wrap">
    <div class="login-card">
      <div style="text-align:center; margin-bottom:18px;">
        <img src="./icon.svg" alt="Ikon" style="width:58px; height:58px; border-radius:14px; margin-bottom:8px;" />
        <h1 style="font-family:var(--font-display); font-size:24px; color:var(--navy);">Login Admin</h1>
        <p style="font-size:13px; color:var(--muted); font-weight:700;"><?php echo ag_h($settings['app_name']); ?> · Manajemen Demo &amp; Pembayaran</p>
      </div>

      <?php if ($flashError !== ''): ?>
        <div class="alert alert-err">⚠️ <?php echo ag_h($flashError); ?></div>
      <?php endif; ?>

      <form method="post" action="./admin.php">
        <input type="hidden" name="action" value="login" />
        <div class="field">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" required autofocus placeholder="admin" />
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required placeholder="••••••••" />
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%; padding:12px;">🔐 Masuk Panel Admin</button>
      </form>
      <div style="text-align:center; margin-top:14px; font-size:12.5px; color:var(--muted); font-weight:700;">
        <a href="./index.php" style="color:#2563EB; text-decoration:underline;">← Kembali ke Landing Page</a>
      </div>
    </div>
  </div>
<?php else: ?>
  <header class="admin-nav">
    <div class="admin-nav-inner">
      <div class="brand">
        <img src="./icon.svg" alt="Ikon" style="width:32px; height:32px; border-radius:8px;" />
        <span>Admin <?php echo ag_h($settings['app_name']); ?></span>
      </div>
      <div class="nav-btns">
        <a href="./index.php" target="_blank" class="nav-link">🌐 Landing Page</a>
        <a href="<?php echo ag_h($settings['demo_url']); ?>" target="_blank" class="nav-link">🎮 Demo</a>
        <a href="./checkout.php" target="_blank" class="nav-link">🛒 Checkout</a>
        <a href="./admin.php?logout=1" class="nav-link danger">🚪 Keluar</a>
      </div>
    </div>
  </header>

  <main class="container">
    <?php if (!empty($settings['is_default_password'])): ?>
      <div class="alert alert-warn">
        🔐 <strong>Keamanan:</strong> Anda masih memakai password bawaan (<code>admin123</code>). Segera ganti password di tab <a href="./admin.php?tab=whatsapp" style="text-decoration:underline; color:#92400E;">WhatsApp &amp; Akun Admin</a>.
      </div>
    <?php endif; ?>

    <?php if ($flashSuccess !== ''): ?>
      <div class="alert alert-ok">✅ <?php echo ag_h($flashSuccess); ?></div>
    <?php endif; ?>
    <?php if ($flashError !== ''): ?>
      <div class="alert alert-err">⚠️ <?php echo ag_h($flashError); ?></div>
    <?php endif; ?>

    <nav class="tabs">
      <a href="./admin.php?tab=orders" class="tab <?php echo $activeTab === 'orders' ? 'active' : ''; ?>">📋 Pesanan (<?php echo $statPending; ?> baru)</a>
      <a href="./admin.php?tab=demo" class="tab <?php echo $activeTab === 'demo' ? 'active' : ''; ?>">🎮 Link Demo &amp; File Full</a>
      <a href="./admin.php?tab=payment" class="tab <?php echo $activeTab === 'payment' ? 'active' : ''; ?>">💳 QRIS &amp; E-Wallet</a>
      <a href="./admin.php?tab=whatsapp" class="tab <?php echo $activeTab === 'whatsapp' ? 'active' : ''; ?>">💬 WhatsApp &amp; Akun</a>
    </nav>

    <?php if ($activeTab === 'orders'): ?>
      <!-- ================= TAB 1: PESANAN & VERIFIKASI ================= -->
      <div class="stats-grid">
        <div class="stat-card"><div class="stat-label">Total Pesanan</div><div class="stat-val"><?php echo $statTotal; ?></div></div>
        <div class="stat-card"><div class="stat-label">Menunggu Verifikasi</div><div class="stat-val" style="color:#D97706;"><?php echo $statPending; ?></div></div>
        <div class="stat-card"><div class="stat-label">Pembayaran Lunas</div><div class="stat-val" style="color:#16A34A;"><?php echo $statPaid; ?></div></div>
        <div class="stat-card"><div class="stat-label">Total Pemasukan</div><div class="stat-val" style="font-size:20px; color:#1D4ED8;"><?php echo ag_rupiah($statRevenue); ?></div></div>
      </div>

      <div class="card">
        <h2>📋 Daftar Pesanan &amp; Bukti Pembayaran</h2>
        <?php if (empty($orders)): ?>
          <p style="color:var(--muted); font-weight:700; padding:16px 0;">Belum ada pesanan masuk. Pembeli yang mengirim bukti bayar di halaman Checkout akan muncul otomatis di sini.</p>
        <?php else: ?>
          <?php foreach ($orders as $o):
            $stClass = ($o['status'] === 'paid') ? 'b-paid' : (($o['status'] === 'rejected') ? 'b-rejected' : 'b-pending');
            $stText = ($o['status'] === 'paid') ? '✅ LUNAS' : (($o['status'] === 'rejected') ? '❌ DITOLAK' : '⏳ MENUNGGU');
            $buyerWaUrl = ag_wa_link($o['buyer_wa'], "Assalamu'alaikum " . $o['buyer_name'] . ", pembayaran Al-Qur'an Go untuk invoice " . $o['invoice'] . " sudah kami terima. Link download sudah aktif di halaman invoice Anda!");
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
            $dlUrlFull = $baseUrl . '/download.php?token=' . urlencode($o['download_token']);
          ?>
            <div class="order-item">
              <div class="order-top">
                <div>
                  <strong style="font-family:monospace; font-size:15px; color:var(--navy);"><?php echo ag_h($o['invoice']); ?></strong>
                  <span style="font-size:12px; color:var(--muted); font-weight:700; margin-left:6px;"><?php echo ag_h($o['created_at']); ?></span>
                </div>
                <span class="badge <?php echo $stClass; ?>"><?php echo $stText; ?></span>
              </div>
              <div class="order-meta">
                👤 <strong><?php echo ag_h($o['buyer_name']); ?></strong> ·
                📱 <?php echo ag_h($o['buyer_wa']); ?>
                <?php if (!empty($o['buyer_email'])): ?> · ✉️ <?php echo ag_h($o['buyer_email']); ?><?php endif; ?><br />
                💳 <?php echo ag_h($o['method_label']); ?> ·
                💰 <strong style="color:#166534;"><?php echo ag_rupiah($o['amount']); ?></strong> ·
                ⬇️ Diunduh: <strong><?php echo intval(isset($o['download_count']) ? $o['download_count'] : 0); ?>x</strong>
              </div>
              <div class="order-actions">
                <?php if (!empty($o['proof_file'])): ?>
                  <a href="./<?php echo ag_h($o['proof_file']); ?>" target="_blank" class="btn btn-blue btn-sm">🖼️ Lihat Bukti</a>
                <?php endif; ?>

                <?php if ($o['status'] !== 'paid'): ?>
                  <form method="post" action="./admin.php?tab=orders">
                    <input type="hidden" name="csrf_token" value="<?php echo ag_h($csrf); ?>" />
                    <input type="hidden" name="action" value="update_order_status" />
                    <input type="hidden" name="invoice" value="<?php echo ag_h($o['invoice']); ?>" />
                    <input type="hidden" name="new_status" value="paid" />
                    <button type="submit" class="btn btn-primary btn-sm">✅ Setujui &amp; Buka Download</button>
                  </form>
                <?php endif; ?>

                <?php if ($o['status'] === 'paid'): ?>
                  <button type="button" class="btn btn-green-soft btn-sm" onclick="copyText(<?php echo ag_h(json_encode($dlUrlFull)); ?>, this)">📋 Salin Link Download</button>
                <?php endif; ?>

                <a href="<?php echo ag_h($buyerWaUrl); ?>" target="_blank" rel="noopener" class="btn btn-green-soft btn-sm">💬 WA Pembeli</a>

                <?php if ($o['status'] !== 'rejected'): ?>
                  <form method="post" action="./admin.php?tab=orders">
                    <input type="hidden" name="csrf_token" value="<?php echo ag_h($csrf); ?>" />
                    <input type="hidden" name="action" value="update_order_status" />
                    <input type="hidden" name="invoice" value="<?php echo ag_h($o['invoice']); ?>" />
                    <input type="hidden" name="new_status" value="rejected" />
                    <button type="submit" class="btn btn-amber btn-sm">❌ Tolak</button>
                  </form>
                <?php endif; ?>

                <form method="post" action="./admin.php?tab=orders" onsubmit="return confirm('Hapus pesanan ini?');">
                  <input type="hidden" name="csrf_token" value="<?php echo ag_h($csrf); ?>" />
                  <input type="hidden" name="action" value="update_order_status" />
                  <input type="hidden" name="invoice" value="<?php echo ag_h($o['invoice']); ?>" />
                  <input type="hidden" name="new_status" value="delete" />
                  <button type="submit" class="btn btn-red btn-sm">🗑️ Hapus</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    <?php elseif ($activeTab === 'demo'): ?>
      <!-- ================= TAB 2: LINK DEMO, BATASAN DEMO, & FILE DOWNLOAD FULL ================= -->
      <div class="card">
        <h2>🎮 Pengaturan Link Demo, Harga &amp; File Aplikasi Full</h2>
        <form method="post" action="./admin.php?tab=demo" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?php echo ag_h($csrf); ?>" />
          <input type="hidden" name="action" value="save_demo_product" />

          <div class="grid-2">
            <div class="field">
              <label for="app_name">Nama Aplikasi / Produk</label>
              <input type="text" id="app_name" name="app_name" required value="<?php echo ag_h($settings['app_name']); ?>" />
            </div>
            <div class="field">
              <label for="app_tagline">Tagline Promosi</label>
              <input type="text" id="app_tagline" name="app_tagline" value="<?php echo ag_h($settings['app_tagline']); ?>" />
            </div>
          </div>

          <div class="grid-2">
            <div class="field">
              <label for="price_normal">Harga Normal / Coret (Rp)</label>
              <input type="number" id="price_normal" name="price_normal" min="0" value="<?php echo intval($settings['price_normal']); ?>" />
            </div>
            <div class="field">
              <label for="price_promo">Harga Jual Sekali Bayar (Rp)</label>
              <input type="number" id="price_promo" name="price_promo" min="0" required value="<?php echo intval($settings['price_promo']); ?>" />
            </div>
          </div>

          <hr style="border:none; border-top:2px dashed #E2E8F0; margin:16px 0;" />

          <div class="field">
            <label for="demo_url">🔗 Link Demo Aplikasi (Tampil di Tombol "Coba Demo")</label>
            <input type="text" id="demo_url" name="demo_url" value="<?php echo ag_h($settings['demo_url']); ?>" placeholder="./demo.php atau https://demo-anda.vercel.app" />
            <small>Biarkan <code>./demo.php</code> untuk memakai skrip demo akses terbatas bawaan, atau masukkan URL demo eksternal (misal link Vercel Anda).</small>
          </div>

          <div class="grid-2">
            <div class="field">
              <label for="demo_allowed_pages">Halaman yang Dibuka di Demo Lokal (<code>demo.php</code>)</label>
              <input type="text" id="demo_allowed_pages" name="demo_allowed_pages" value="<?php echo ag_h($settings['demo_allowed_pages']); ?>" placeholder="1, 582, 604" />
              <small>Pisahkan nomor halaman mushaf (1–604) dengan koma. Contoh: <code>1, 582, 604</code> (Al-Fatihah, An-Naba', 3 Qul).</small>
            </div>
            <div class="field">
              <label for="demo_max_verses">Batas Maksimal Ayat per Halaman Demo</label>
              <input type="number" id="demo_max_verses" name="demo_max_verses" min="0" max="50" value="<?php echo intval($settings['demo_max_verses']); ?>" />
              <small>Isi <code>5</code> agar demo hanya menampilkan 5 ayat pertama tiap halaman, atau <code>0</code> untuk seluruh ayat di halaman demo.</small>
            </div>
          </div>

          <hr style="border:none; border-top:2px dashed #E2E8F0; margin:16px 0;" />

          <div class="field">
            <label for="download_mode">📦 Sumber Download Aplikasi Full Setelah Pembayaran Lunas</label>
            <select id="download_mode" name="download_mode">
              <option value="local_file" <?php echo $settings['download_mode'] === 'local_file' ? 'selected' : ''; ?>>File Terproteksi di Server (Aman via Token download.php)</option>
              <option value="external_link" <?php echo $settings['download_mode'] === 'external_link' ? 'selected' : ''; ?>>Link Download Eksternal (Google Drive / MediaFire / URL Lain)</option>
            </select>
          </div>

          <div class="grid-2">
            <div class="field">
              <label for="full_zip_file">Upload File Aplikasi Full (.zip / .apk / .pdf)</label>
              <input type="file" id="full_zip_file" name="full_zip_file" accept=".zip,.rar,.apk,.pdf" />
              <small>File aktif saat ini: <strong><?php echo ag_h($settings['download_local_file'] ?: '(belum ada)'); ?></strong></small>
            </div>
            <div class="field">
              <label for="download_external_url">Atau Link Download Eksternal (Jika pilih mode Eksternal)</label>
              <input type="url" id="download_external_url" name="download_external_url" value="<?php echo ag_h($settings['download_external_url']); ?>" placeholder="https://drive.google.com/..." />
            </div>
          </div>

          <div class="field">
            <label for="download_notes">Catatan / Petunjuk untuk Pembeli Setelah Lunas</label>
            <textarea id="download_notes" name="download_notes"><?php echo ag_h($settings['download_notes']); ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary">💾 Simpan Pengaturan Demo &amp; Produk</button>
        </form>
      </div>

    <?php elseif ($activeTab === 'payment'): ?>
      <!-- ================= TAB 3: PENGATURAN QRIS & E-WALLET ================= -->
      <div class="card">
        <h2>💳 Pengaturan Pembayaran QRIS &amp; E-Wallet</h2>
        <form method="post" action="./admin.php?tab=payment" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?php echo ag_h($csrf); ?>" />
          <input type="hidden" name="action" value="save_payment" />

          <div class="field">
            <label for="payment_verification_mode">⚡ Mode Pembukaan Link Download Setelah Pembeli Upload Bukti</label>
            <select id="payment_verification_mode" name="payment_verification_mode">
              <option value="manual" <?php echo $settings['payment_verification_mode'] === 'manual' ? 'selected' : ''; ?>>Verifikasi Admin (Aman — Link download terbuka otomatis saat Admin klik "Setujui")</option>
              <option value="instant" <?php echo $settings['payment_verification_mode'] === 'instant' ? 'selected' : ''; ?>>Otomatis Instan (Link download langsung terbuka begitu pembeli unggah bukti transfer)</option>
            </select>
          </div>

          <div class="ew-box">
            <label class="switch-row">
              <input type="checkbox" name="qris_enabled" value="1" <?php echo !empty($settings['qris_enabled']) ? 'checked' : ''; ?> />
              <span>📱 Aktifkan Pembayaran QRIS</span>
            </label>
            <div class="grid-2">
              <div class="field">
                <label for="qris_merchant">Nama Merchant / Toko QRIS</label>
                <input type="text" id="qris_merchant" name="qris_merchant" value="<?php echo ag_h($settings['qris_merchant']); ?>" />
              </div>
              <div class="field">
                <label for="qris_file">Upload Gambar QRIS (JPG / PNG / WEBP)</label>
                <input type="file" id="qris_file" name="qris_file" accept=".jpg,.jpeg,.png,.webp" />
              </div>
            </div>
            <?php if (!empty($settings['qris_image'])): ?>
              <div style="margin-top:8px; display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
                <img src="./<?php echo ag_h($settings['qris_image']); ?>" alt="Preview QRIS" style="width:110px; border-radius:10px; border:2px solid #CBD5E1;" />
                <label style="font-size:13px; font-weight:800; color:#DC2626; cursor:pointer;">
                  <input type="checkbox" name="remove_qris_image" value="1" /> Hapus gambar QRIS saat ini
                </label>
              </div>
            <?php endif; ?>
          </div>

          <?php foreach ($settings['ewallets'] as $key => $ew): ?>
            <div class="ew-box">
              <label class="switch-row">
                <input type="checkbox" name="ew_enabled_<?php echo ag_h($key); ?>" value="1" <?php echo !empty($ew['enabled']) ? 'checked' : ''; ?> />
                <span>💳 Aktifkan <?php echo ag_h($ew['label']); ?></span>
              </label>
              <div class="grid-2">
                <div class="field" style="margin-bottom:4px;">
                  <label>Nomor <?php echo ag_h($ew['label']); ?> / Rekening</label>
                  <input type="text" name="ew_number_<?php echo ag_h($key); ?>" value="<?php echo ag_h($ew['number']); ?>" placeholder="0812xxxxxxxx" />
                </div>
                <div class="field" style="margin-bottom:4px;">
                  <label>Atas Nama</label>
                  <input type="text" name="ew_holder_<?php echo ag_h($key); ?>" value="<?php echo ag_h($ew['holder']); ?>" placeholder="Nama Pemilik Akun" />
                </div>
              </div>
            </div>
          <?php endforeach; ?>

          <button type="submit" class="btn btn-primary">💾 Simpan Pengaturan Pembayaran</button>
        </form>
      </div>

    <?php elseif ($activeTab === 'whatsapp'): ?>
      <!-- ================= TAB 4: WHATSAPP & AKUN ADMIN ================= -->
      <div class="card">
        <h2>💬 Pengaturan Nomor WhatsApp &amp; Akun Admin</h2>
        <form method="post" action="./admin.php?tab=whatsapp">
          <input type="hidden" name="csrf_token" value="<?php echo ag_h($csrf); ?>" />
          <input type="hidden" name="action" value="save_wa_account" />

          <div class="grid-2">
            <div class="field">
              <label for="wa_number">Nomor WhatsApp Admin (Format 628... atau 08...)</label>
              <input type="text" id="wa_number" name="wa_number" required value="<?php echo ag_h($settings['wa_number']); ?>" placeholder="6281234567890" />
              <small>Otomatis dikonversi ke format internasional <code>628...</code> untuk tombol WhatsApp di Landing Page, Demo, &amp; Checkout.</small>
            </div>
            <div class="field">
              <label for="wa_admin_name">Nama Admin / CS</label>
              <input type="text" id="wa_admin_name" name="wa_admin_name" value="<?php echo ag_h($settings['wa_admin_name']); ?>" />
            </div>
          </div>

          <div class="field">
            <label for="wa_message_ask">Template Pesan WA (Tombol Tanya Produk)</label>
            <textarea id="wa_message_ask" name="wa_message_ask"><?php echo ag_h($settings['wa_message_ask']); ?></textarea>
          </div>

          <div class="field">
            <label for="wa_message_confirm">Template Pesan WA (Konfirmasi Pembayaran)</label>
            <textarea id="wa_message_confirm" name="wa_message_confirm"><?php echo ag_h($settings['wa_message_confirm']); ?></textarea>
            <small>Variabel otomatis: <code>{invoice}</code>, <code>{name}</code>, <code>{method}</code>, <code>{amount}</code>.</small>
          </div>

          <hr style="border:none; border-top:2px dashed #E2E8F0; margin:16px 0;" />

          <div class="grid-2">
            <div class="field">
              <label for="new_admin_username">Username Admin</label>
              <input type="text" id="new_admin_username" name="new_admin_username" value="<?php echo ag_h($settings['admin_username']); ?>" />
            </div>
            <div class="field">
              <label for="new_admin_password">Password Baru Admin (Kosongkan jika tidak diubah)</label>
              <input type="password" id="new_admin_password" name="new_admin_password" placeholder="Minimal 6 karakter" />
            </div>
          </div>

          <button type="submit" class="btn btn-primary">💾 Simpan Pengaturan WA &amp; Akun</button>
        </form>
      </div>
    <?php endif; ?>
  </main>

  <script>
    function copyText(text, btn) {
      navigator.clipboard.writeText(text).then(() => {
        const old = btn.textContent;
        btn.textContent = '✅ Link Disalin!';
        setTimeout(() => { btn.textContent = old; }, 2000);
      }).catch(() => {
        prompt('Salin link download pembeli:', text);
      });
    }
  </script>
<?php endif; ?>
</body>
</html>
