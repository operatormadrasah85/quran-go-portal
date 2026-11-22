<?php
require_once __DIR__ . '/config.php';

$token = isset($_GET['token']) ? trim((string)$_GET['token']) : '';
if ($token === '') {
    http_response_code(400);
    die('Token unduhan tidak valid.');
}

$orders = ag_load_orders();
$foundIdx = -1;
foreach ($orders as $idx => $o) {
    if (isset($o['download_token']) && hash_equals((string)$o['download_token'], $token)) {
        $foundIdx = $idx;
        break;
    }
}

if ($foundIdx === -1) {
    http_response_code(404);
    die('Link unduhan tidak ditemukan atau sudah kedaluwarsa.');
}

$order = $orders[$foundIdx];
if ($order['status'] !== 'paid') {
    http_response_code(403);
    die('Pembayaran untuk invoice ' . ag_h($order['invoice']) . ' belum diverifikasi lunas.');
}

// Catat statistik unduhan
$orders[$foundIdx]['download_count'] = isset($order['download_count']) ? intval($order['download_count']) + 1 : 1;
$orders[$foundIdx]['last_download_at'] = date('Y-m-d H:i:s');
ag_save_orders($orders);

$settings = ag_load_settings();
$mode = isset($settings['download_mode']) ? $settings['download_mode'] : 'local_file';

if ($mode === 'external_link' && !empty($settings['download_external_url'])) {
    header('Location: ' . $settings['download_external_url']);
    exit;
}

// Mode file lokal terproteksi
$fileName = basename((string)(isset($settings['download_local_file']) ? $settings['download_local_file'] : ''));
$fullPath = AG_PROTECTED_DIR . '/' . $fileName;

if ($fileName === '' || !file_exists($fullPath) || !is_file($fullPath)) {
    // Fallback jika file lokal belum ada tapi external_url terisi
    if (!empty($settings['download_external_url'])) {
        header('Location: ' . $settings['download_external_url']);
        exit;
    }
    http_response_code(404);
    die('File aplikasi belum tersedia di server. Silakan hubungi Admin melalui WhatsApp.');
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($fullPath));
if (ob_get_level()) {
    ob_end_clean();
}
readfile($fullPath);
exit;
