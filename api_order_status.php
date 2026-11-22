<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$inv = isset($_GET['inv']) ? trim((string)$_GET['inv']) : '';
if ($inv === '') {
    http_response_code(400);
    echo json_encode(array('ok' => false, 'error' => 'Invoice kosong'));
    exit;
}

$order = ag_find_order_by_invoice($inv);
if (!$order) {
    http_response_code(404);
    echo json_encode(array('ok' => false, 'error' => 'Pesanan tidak ditemukan'));
    exit;
}

$isPaid = ($order['status'] === 'paid');
echo json_encode(array(
    'ok' => true,
    'invoice' => $order['invoice'],
    'status' => $order['status'],
    'paid' => $isPaid,
    'download_url' => $isPaid ? ('./download.php?token=' . urlencode($order['download_token'])) : ''
));
