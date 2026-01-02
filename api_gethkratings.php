<?php
// API Wrapper / Proxy untuk mengambil data rating dalam format JSON
// File ini bisa di-host di server PHP (Shared Hosting/XAMPP) dan akan mengambil data dari VPS Node.js

// 1. Set Header agar output dianggap JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Mengizinkan akses dari aplikasi/website lain (CORS)
header('Access-Control-Allow-Methods: GET');

// 2. Konfigurasi URL VPS Node.js Anda
$vpsUrl = 'http://74.208.64.184'; 

// 3. Ambil parameter dari URL
$type = $_GET['type'] ?? ''; // playstore, appstore, maps, atau search_maps
$params = $_GET;

// 4. Tentukan endpoint tujuan berdasarkan tipe
$endpoint = '';
switch ($type) {
    case 'playstore':
        $endpoint = '/api/playstore';
        break;
    case 'appstore':
        $endpoint = '/api/appstore';
        break;
    case 'maps':
        $endpoint = '/api/maps';
        break;
    case 'search_maps':
        $endpoint = '/api/search/maps';
        break;
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Parameter "type" tidak valid atau kosong. Gunakan: playstore, appstore, maps, atau search_maps.'
        ]);
        exit;
}

// 5. Buat URL request ke VPS
// Hapus parameter 'type' agar tidak ikut terkirim ke VPS (karena VPS tidak butuh param ini)
unset($params['type']); 
$queryString = http_build_query($params);
$targetUrl = $vpsUrl . $endpoint . '?' . $queryString;

// 6. Eksekusi Request menggunakan cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $targetUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Skip SSL check (aman karena HTTP)
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Timeout 60 detik (karena data sekarang lebih banyak)

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// 7. Handle Error dan Output
if ($response === false) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menghubungi server VPS: ' . curl_error($ch)
    ]);
} else {
    // Teruskan HTTP Status Code dari VPS (200, 400, 404, 500)
    http_response_code($httpCode);
    
    // Outputkan response JSON asli dari VPS apa adanya
    echo $response;
}

curl_close($ch);
?>