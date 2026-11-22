<?php
// Al-Qur'an Go - Audio Murottal Same-Origin Proxy (Kompatibel PHP 7.2 - 8.2)
// Mengatasi blokir CORS saat mengunduh MP3 murottal ke IndexedDB untuk pemutaran 100% offline.

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if (isset($_GET['ping'])) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode(array('ok' => true, 'service' => 'alquran-go-audio-proxy'));
    exit;
}

$qariMap = array(
    'Husary_128kbps' => array(
        'everyAyah' => 'Husary_128kbps',
        'islamic'   => 'ar.husary',
        'quranCom'  => 'Husary/mp3'
    ),
    'Alafasy_128kbps' => array(
        'everyAyah' => 'Alafasy_128kbps',
        'islamic'   => 'ar.alafasy',
        'quranCom'  => 'Alafasy/mp3'
    ),
    'Minshawy_Murattal_128kbps' => array(
        'everyAyah' => 'Minshawy_Murattal_128kbps',
        'islamic'   => 'ar.minshawi',
        'quranCom'  => 'Minshawi/Murattal/mp3'
    )
);

$qari = isset($_GET['qari']) ? trim((string)$_GET['qari']) : 'Husary_128kbps';
if (!isset($qariMap[$qari])) {
    $qari = 'Husary_128kbps';
}

$surah = isset($_GET['s']) ? intval($_GET['s']) : 0;
$ayah  = isset($_GET['a']) ? intval($_GET['a']) : 0;

if ($surah < 1 || $surah > 114 || $ayah < 1 || $ayah > 286) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('ok' => false, 'error' => 'Parameter surah/ayat tidak valid'));
    exit;
}

$surahCounts = array(
    0,
    7,286,200,176,120,165,206,75,129,109,
    123,111,43,52,99,128,111,110,98,135,
    112,78,118,64,77,227,93,88,69,60,
    34,30,73,54,45,83,182,88,75,85,
    54,53,89,59,37,35,38,29,18,45,
    60,49,62,55,78,96,29,22,24,13,
    14,11,11,18,12,12,30,52,52,44,
    28,28,20,56,40,31,50,40,46,42,
    29,19,36,25,22,17,19,26,30,20,
    15,21,11,8,8,19,5,8,8,11,
    11,8,3,9,5,4,7,3,6,3,
    5,4,5,6
);

if ($ayah > $surahCounts[$surah]) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('ok' => false, 'error' => 'Nomor ayat melebihi jumlah ayat surah'));
    exit;
}

$globalId = $ayah;
for ($i = 1; $i < $surah; $i++) {
    $globalId += $surahCounts[$i];
}

$sss = str_pad((string)$surah, 3, '0', STR_PAD_LEFT);
$aaa = str_pad((string)$ayah, 3, '0', STR_PAD_LEFT);
$cfg = $qariMap[$qari];

$urls = array(
    'https://everyayah.com/data/' . $cfg['everyAyah'] . '/' . $sss . $aaa . '.mp3',
    'https://cdn.islamic.network/quran/audio/128/' . $cfg['islamic'] . '/' . $globalId . '.mp3',
    'https://cdn.islamic.network/quran/audio/64/' . $cfg['islamic'] . '/' . $globalId . '.mp3',
    'https://verses.quran.com/' . $cfg['quranCom'] . '/' . $sss . $aaa . '.mp3'
);

function fetchRemoteAudio($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 4);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_TIMEOUT, 16);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (AlQuranGo/5.0)');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $body = curl_exec($ch);
        $code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        curl_close($ch);
        if ($body !== false && $code >= 200 && $code < 300 && strlen($body) > 800) {
            return $body;
        }
    }

    if (ini_get('allow_url_fopen')) {
        $ctx = stream_context_create(array(
            'http' => array(
                'method' => 'GET',
                'timeout' => 14,
                'header' => "User-Agent: Mozilla/5.0 (AlQuranGo/5.0)\r\n"
            ),
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false
            )
        ));
        $body = @file_get_contents($url, false, $ctx);
        if ($body !== false && strlen($body) > 800) {
            return $body;
        }
    }

    return false;
}

$audioData = false;
foreach ($urls as $u) {
    $res = fetchRemoteAudio($u);
    if ($res !== false) {
        $audioData = $res;
        break;
    }
}

if ($audioData === false) {
    http_response_code(502);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('ok' => false, 'error' => 'Gagal mengambil audio dari server sumber'));
    exit;
}

header('Content-Type: audio/mpeg');
header('Content-Length: ' . strlen($audioData));
header('Cache-Control: public, max-age=31536000, immutable');
echo $audioData;
