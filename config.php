<?php
// Al-Qur'an Go - Konfigurasi & Penyimpanan Otomatis (Kompatibel PHP 7.2 - 8.2)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Jakarta');

define('AG_BASE_DIR', __DIR__);
define('AG_DATA_DIR', __DIR__ . '/data');
define('AG_UPLOAD_DIR', __DIR__ . '/uploads');
define('AG_PROTECTED_DIR', __DIR__ . '/protected_files');
define('AG_SETTINGS_FILE', AG_DATA_DIR . '/settings.json');
define('AG_ORDERS_FILE', AG_DATA_DIR . '/orders.json');

function ag_ensure_dirs() {
    $dirs = array(AG_DATA_DIR, AG_UPLOAD_DIR, AG_PROTECTED_DIR);
    foreach ($dirs as $d) {
        if (!is_dir($d)) {
            @mkdir($d, 0755, true);
        }
    }

    // Lindungi folder data & protected_files dari akses langsung via browser
    $denyHtaccess = "Order Allow,Deny\nDeny from all\n<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n";
    if (!file_exists(AG_DATA_DIR . '/.htaccess')) {
        @file_put_contents(AG_DATA_DIR . '/.htaccess', $denyHtaccess);
    }
    if (!file_exists(AG_PROTECTED_DIR . '/.htaccess')) {
        @file_put_contents(AG_PROTECTED_DIR . '/.htaccess', $denyHtaccess);
    }

    // Cegah eksekusi skrip PHP di folder uploads
    $uploadHtaccess = "Options -Indexes\n<FilesMatch \"\\.(php|phtml|php3|php4|php5|php7|phar)$\">\n    Order Allow,Deny\n    Deny from all\n</FilesMatch>\n";
    if (!file_exists(AG_UPLOAD_DIR . '/.htaccess')) {
        @file_put_contents(AG_UPLOAD_DIR . '/.htaccess', $uploadHtaccess);
    }
}

function ag_default_settings() {
    return array(
        'app_name' => "Al-Qur'an Go",
        'app_tagline' => 'Bikin hafalan terasa seperti permainan, fresh bukan stress!',
        'price_normal' => 149000,
        'price_promo' => 49000,
        'demo_url' => './demo.php',
        'demo_allowed_pages' => '1, 582, 604',
        'demo_max_verses' => 5,
        'download_mode' => 'local_file', // 'local_file' atau 'external_link'
        'download_local_file' => 'alquran-go-full-30juz.zip',
        'download_external_url' => '',
        'download_notes' => 'Ekstrak file ZIP, lalu ikuti panduan instalasi offline atau deploy ke Vercel/Hosting.',
        'wa_number' => '6281234567890',
        'wa_admin_name' => "Admin Al-Qur'an Go",
        'wa_message_ask' => "Assalamu'alaikum Admin, saya ingin bertanya tentang aplikasi Al-Qur'an Go 30 Juz.",
        'wa_message_confirm' => "Assalamu'alaikum Admin, saya sudah melakukan pembayaran Al-Qur'an Go.\nKode Invoice: {invoice}\nNama: {name}\nMetode: {method}\nNominal: {amount}\nMohon dicek ya, terima kasih.",
        'payment_verification_mode' => 'manual', // 'manual' (verifikasi admin) atau 'instant' (langsung buka download setelah upload bukti)
        'qris_enabled' => true,
        'qris_merchant' => "Al-Qur'an Go Official",
        'qris_image' => '',
        'ewallets' => array(
            'dana' => array('label' => 'DANA', 'enabled' => true, 'number' => '081234567890', 'holder' => "Admin Al-Qur'an Go"),
            'ovo' => array('label' => 'OVO', 'enabled' => true, 'number' => '081234567890', 'holder' => "Admin Al-Qur'an Go"),
            'gopay' => array('label' => 'GoPay', 'enabled' => true, 'number' => '081234567890', 'holder' => "Admin Al-Qur'an Go"),
            'shopeepay' => array('label' => 'ShopeePay', 'enabled' => false, 'number' => '', 'holder' => ''),
            'bank' => array('label' => 'Transfer Bank (BSI / BRI)', 'enabled' => false, 'number' => '', 'holder' => '')
        ),
        'admin_username' => 'admin',
        'admin_password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
        'is_default_password' => true
    );
}

function ag_load_settings() {
    ag_ensure_dirs();
    $defaults = ag_default_settings();
    if (!file_exists(AG_SETTINGS_FILE)) {
        ag_save_settings($defaults);
        return $defaults;
    }
    $raw = @file_get_contents(AG_SETTINGS_FILE);
    $parsed = $raw ? json_decode($raw, true) : null;
    if (!is_array($parsed)) {
        return $defaults;
    }
    $merged = array_merge($defaults, $parsed);
    if (!isset($merged['ewallets']) || !is_array($merged['ewallets'])) {
        $merged['ewallets'] = $defaults['ewallets'];
    } else {
        foreach ($defaults['ewallets'] as $k => $v) {
            if (!isset($merged['ewallets'][$k]) || !is_array($merged['ewallets'][$k])) {
                $merged['ewallets'][$k] = $v;
            } else {
                $merged['ewallets'][$k] = array_merge($v, $merged['ewallets'][$k]);
            }
        }
    }
    return $merged;
}

function ag_save_settings($settings) {
    ag_ensure_dirs();
    $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return @file_put_contents(AG_SETTINGS_FILE, $json, LOCK_EX) !== false;
}

function ag_load_orders() {
    ag_ensure_dirs();
    if (!file_exists(AG_ORDERS_FILE)) {
        @file_put_contents(AG_ORDERS_FILE, '[]', LOCK_EX);
        return array();
    }
    $raw = @file_get_contents(AG_ORDERS_FILE);
    $parsed = $raw ? json_decode($raw, true) : null;
    return is_array($parsed) ? $parsed : array();
}

function ag_save_orders($orders) {
    ag_ensure_dirs();
    $json = json_encode(array_values($orders), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return @file_put_contents(AG_ORDERS_FILE, $json, LOCK_EX) !== false;
}

function ag_find_order_by_invoice($invoice) {
    $orders = ag_load_orders();
    foreach ($orders as $o) {
        if (isset($o['invoice']) && strtoupper($o['invoice']) === strtoupper(trim($invoice))) {
            return $o;
        }
    }
    return null;
}

function ag_find_order_by_token($token) {
    $orders = ag_load_orders();
    foreach ($orders as $o) {
        if (isset($o['download_token']) && hash_equals((string)$o['download_token'], (string)$token)) {
            return $o;
        }
    }
    return null;
}

function ag_rupiah($amount) {
    return 'Rp ' . number_format((int)$amount, 0, ',', '.');
}

function ag_clean_wa($phone) {
    $digits = preg_replace('/[^0-9]/', '', (string)$phone);
    if (substr($digits, 0, 1) === '0') {
        $digits = '62' . substr($digits, 1);
    }
    return $digits;
}

function ag_wa_link($phone, $message) {
    $clean = ag_clean_wa($phone);
    return 'https://wa.me/' . $clean . '?text=' . rawurlencode((string)$message);
}

function ag_h($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function ag_parse_pages_csv($csv) {
    $parts = explode(',', (string)$csv);
    $pages = array();
    foreach ($parts as $p) {
        $n = intval(trim($p));
        if ($n >= 1 && $n <= 604 && !in_array($n, $pages, true)) {
            $pages[] = $n;
        }
    }
    if (empty($pages)) {
        $pages = array(1, 582, 604);
    }
    return $pages;
}

function ag_csrf_token() {
    if (empty($_SESSION['ag_csrf'])) {
        $_SESSION['ag_csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['ag_csrf'];
}

function ag_verify_csrf($token) {
    return isset($_SESSION['ag_csrf']) && is_string($token) && hash_equals($_SESSION['ag_csrf'], $token);
}

function ag_is_admin() {
    return !empty($_SESSION['ag_admin_logged_in']);
}

function ag_generate_invoice() {
    $rand = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
    return 'AG-' . date('Ymd') . '-' . $rand;
}

function ag_generate_token() {
    return bin2hex(random_bytes(20));
}

function ag_active_payment_methods($settings) {
    $methods = array();
    if (!empty($settings['qris_enabled'])) {
        $methods['qris'] = array(
            'key' => 'qris',
            'type' => 'qris',
            'label' => 'QRIS (Semua E-Wallet & Mobile Banking)',
            'merchant' => isset($settings['qris_merchant']) ? $settings['qris_merchant'] : '',
            'image' => isset($settings['qris_image']) ? $settings['qris_image'] : ''
        );
    }
    if (isset($settings['ewallets']) && is_array($settings['ewallets'])) {
        foreach ($settings['ewallets'] as $key => $ew) {
            if (!empty($ew['enabled']) && trim((string)$ew['number']) !== '') {
                $methods[$key] = array(
                    'key' => $key,
                    'type' => 'ewallet',
                    'label' => $ew['label'],
                    'number' => $ew['number'],
                    'holder' => $ew['holder']
                );
            }
        }
    }
    return $methods;
}
