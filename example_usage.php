<?php
// Contoh cara mengambil data rating menggunakan PHP dari API Vercel Anda

// 1. URL API Anda (IP VPS IONOS Anda)
$baseUrl = 'http://74.208.64.184'; 
// Atau gunakan localhost jika tes lokal:
// $baseUrl = 'http://localhost';

// 2. Fungsi Helper untuk mengambil data
function getRating($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // Vercel menggunakan HTTPS, jadi kita perlu handle SSL verification (atau skip untuk dev)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    
    $output = curl_exec($ch);
    
    if($output === false) {
        return 'Curl Error: ' . curl_error($ch);
    }
    
    curl_close($ch);
    return json_decode($output, true);
}

echo "<h1>Demo Ambil Rating & Ulasan</h1>";

// --- Google Play Store ---
$playStoreId = 'com.henskristal.hens_kristal';
$playStoreUrl = "$baseUrl/api/playstore?id=$playStoreId";
$playStoreData = getRating($playStoreUrl);

if ($playStoreData && $playStoreData['success']) {
    $d = $playStoreData['data'];
    echo "<h3>Google Play Store: {$d['title']}</h3>";
    echo "Developer: {$d['developer']}<br>";
    echo "Versi: {$d['version']}<br>";
    echo "Rating: <strong>{$d['score']}</strong> / 5 ({$d['ratings']} users)<br>";
    echo "<img src='{$d['icon']}' width='50'><br>";
    echo "<strong>Apa yang baru:</strong><br>" . nl2br($d['recentChanges'] ?? '-');
    
    // Screenshots
    if (!empty($d['screenshots']) && is_array($d['screenshots'])) {
        echo "<br><strong>Screenshot:</strong><div style='overflow-x:auto; white-space:nowrap; margin-top:10px;'>";
        foreach (array_slice($d['screenshots'], 0, 5) as $ss) {
             echo "<img src='$ss' height='150' style='margin-right:5px;'>";
        }
        echo "</div>";
    }

    echo "<h4>Ulasan Terbaru</h4>";
    if (!empty($d['recent_reviews']) && is_array($d['recent_reviews'])) {
        echo "<div class='review-container'>";
        echo "<ul class='review-list'>";
        foreach ($d['recent_reviews'] as $review) {
            $author = $review['userName'] ?? 'User';
            $score = $review['score'] ?? 5;
            $stars = str_repeat('★', $score);
            $text = $review['text'] ?? '';

            echo "<li class='review-item'>";
            echo "<div class='review-header'>";
            echo "<span class='review-author'>{$author}</span>";
            echo "<span class='star'>{$stars}</span>";
            echo "</div>";
            echo "<div>{$text}</div>";
            echo "</li>";
        }
        echo "</ul>";
        echo "</div>";
    } else {
        echo "Belum ada ulasan atau gagal mengambil ulasan.<br>";
    }
} else {
    echo "<p>Gagal mengambil data Play Store</p>";
}

echo "<hr>";

// --- App Store ---
$appStoreId = '6473765666'; // WhatsApp
$appStoreUrl = "$baseUrl/api/appstore?id=$appStoreId";
$appStoreData = getRating($appStoreUrl);

if ($appStoreData && $appStoreData['success']) {
    $d = $appStoreData['data'];
    echo "<h3>App Store: {$d['title']}</h3>";
    echo "Developer: " . ($d['developer'] ?? '-') . "<br>";
    echo "Versi: " . ($d['currentVersion'] ?? '-') . "<br>";
    echo "Rating: <strong>{$d['score']}</strong> / 5 ({$d['ratings']} users)<br>";
    echo "<img src='{$d['icon']}' width='50'><br>";
    
    // Screenshots
    if (!empty($d['screenshots']) && is_array($d['screenshots'])) {
        echo "<div style='overflow-x:auto; white-space:nowrap; margin-top:10px;'>";
        foreach (array_slice($d['screenshots'], 0, 3) as $ss) {
             echo "<img src='$ss' height='150' style='margin-right:5px;'>";
        }
        echo "</div>";
    }

    echo "<strong>Ulasan Terbaru:</strong><br>";
    if (!empty($d['recent_reviews']) && is_array($d['recent_reviews'])) {
        echo "<ul>";
        foreach ($d['recent_reviews'] as $review) {
             $author = $review['userName'] ?? 'User';
             $rating = $review['score'] ?? '-';
             $text = $review['text'] ?? '';
            echo "<li><strong>{$author}</strong> ({$rating}/5): {$text}</li>";
        }
        echo "</ul>";
    } else {
        echo "Belum ada ulasan atau gagal mengambil ulasan.<br>";
    }
} else {
    echo "<p>Gagal mengambil data App Store</p>";
}

echo "<hr>";

// --- Google Maps ---
// Ganti dengan Place ID yang valid. 
// Cara dapat Place ID:
// 1. Pakai endpoint search API: /api/search/maps?q=NamaTempat&key=API_KEY
// 2. Atau cari di: https://developers.google.com/maps/documentation/places/web-service/place-id
$placeId = 'ChIJOU59Vup_9i0RiiboXnWANDA'; // Contoh: Google Sydney
// Jika API Key belum diset di Vercel, Anda bisa menambahkannya di URL: &key=API_KEY_ANDA
$mapsUrl = "$baseUrl/api/maps?place_id=$placeId"; 
$mapsData = getRating($mapsUrl);

if ($mapsData && $mapsData['success']) {
    $d = $mapsData['data'];
    echo "<h3>Google Maps: {$d['title']}</h3>";
    echo "Alamat: " . ($d['address'] ?? '-') . "<br>";
    echo "Rating: <strong>{$d['score']}</strong> / 5 ({$d['ratings']} users)<br>";
    echo "<img src='{$d['icon']}' width='30'><br>";
    
    echo "<strong>Ulasan Terbaru:</strong><br>";
    if (!empty($d['recent_reviews']) && is_array($d['recent_reviews'])) {
        echo "<div class='review-container'>";
        echo "<ul class='review-list'>";
        foreach ($d['recent_reviews'] as $review) {
            $author = $review['author_name'];
            $score = $review['rating'];
            $stars = str_repeat('★', $score);
            $time = $review['relative_time_description'];
            $text = $review['text'];

            echo "<li class='review-item'>";
            echo "<div class='review-header'>";
            echo "<span class='review-author'>{$author}</span>";
            echo "<span class='star'>{$stars} ({$time})</span>";
            echo "</div>";
            echo "<div>{$text}</div>";
            echo "</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
} else {
    echo "<p>Gagal mengambil data Google Maps. Pastikan Place ID benar dan API Key valid.</p>";
    if ($mapsData) {
        echo "Error: " . $mapsData['message'];
    }
}

?>
