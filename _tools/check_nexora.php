<?php
/**
 * Nexora Genel Katalog Servisi - Bağlantı ve Sözleşme Doğrulaması
 * ---------------------------------------------------------------
 * Nexora ayağa kalktığında bu betiği çalıştırın; site kodu ile servisin
 * gerçekten uyumlu olduğunu (alan adları, tipler, görsel yolları)
 * uçtan uca doğrular.
 *
 *   php _tools/check_nexora.php
 *
 * Ayarlar .env dosyasındaki nexora.* değerlerinden okunur.
 */

$env = [];
foreach (file(__DIR__ . '/../.env') as $line) {
    if (preg_match('/^\s*([\w.]+)\s*=\s*(.*)$/', $line, $m)) {
        $env[$m[1]] = trim($m[2]);
    }
}

$base   = rtrim($env['nexora.url'] ?? 'http://localhost:5207', '/');
$apiKey = $env['nexora.apiKey'] ?? '';
$path   = '/api/v1/public';

echo "Nexora servisi: $base$path\n";
echo "API anahtari  : " . ($apiKey === '' ? 'TANIMSIZ (site servise hic gitmez)' : 'tanimli') . "\n\n";

if ($apiKey === '') {
    echo "HATA: .env icinde nexora.apiKey bos. Nexora tarafindaki PublicApi:ApiKeys degerini girin.\n";
    exit(1);
}

/**
 * Uç noktayı çağırır.
 */
function iste(string $url, string $apiKey): array
{
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => ['Accept: application/json', 'X-Api-Key: ' . $apiKey],
    ]);
    $govde = curl_exec($curl);
    $kod   = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $hata  = curl_error($curl);
    curl_close($curl);

    return ['kod' => $kod, 'govde' => $govde, 'hata' => $hata];
}

// Site kodunun kullandigi alanlar. Servis bunlari dondurmezse kartlar eksik cikar.
$beklenen = [
    'events' => [
        'name', 'categoryName', 'facilityName', 'hallName', 'location',
        'startDate', 'startTime', 'isPaid', 'priceInfo',
        'registrationOpen', 'availableCapacity', 'description', 'primaryImageUrl', 'id',
    ],
    'service-facilities' => [
        'name', 'district', 'city', 'address', 'capacity',
        'description', 'about', 'facilities', 'primaryImageUrl',
    ],
];

$hataSayisi = 0;

foreach ($beklenen as $uc => $alanlar) {
    echo "=== /$uc ===\n";
    $yanit = iste("$base$path/$uc?limit=3", $apiKey);

    if ($yanit['kod'] !== 200) {
        echo "  HATA: HTTP {$yanit['kod']} {$yanit['hata']}\n";
        if ($yanit['kod'] === 401) {
            echo "  -> API anahtari gecersiz.\n";
        } elseif ($yanit['kod'] === 0) {
            echo "  -> Servise hic baglanilamadi (adres/kapali servis).\n";
        }
        $hataSayisi++;
        echo "\n";
        continue;
    }

    $veri = json_decode($yanit['govde'], TRUE);
    if (!is_array($veri) || empty($veri['success']) || !isset($veri['data'])) {
        echo "  HATA: Beklenmeyen yanit bicimi (success/data yok).\n";
        $hataSayisi++;
        echo "\n";
        continue;
    }

    $kayitlar = $veri['data'];
    echo "  Kayit sayisi: " . count($kayitlar) . "\n";

    if ($kayitlar === []) {
        echo "  UYARI: Servis calisiyor ama kayit dondurmedi; ilgili alan sitede gizlenir.\n\n";
        continue;
    }

    $ilk    = $kayitlar[0];
    $eksik  = array_values(array_diff($alanlar, array_keys($ilk)));

    if ($eksik === []) {
        echo "  Alan sozlesmesi: TAM (site kodunun bekledigi tum alanlar var)\n";
    } else {
        echo "  HATA: Eksik alanlar -> " . implode(', ', $eksik) . "\n";
        $hataSayisi++;
    }

    // Gorsel yolu gercekten aciliyor mu?
    $gorsel = $ilk['primaryImageUrl'] ?? NULL;
    if ($gorsel) {
        $tam = str_starts_with($gorsel, 'http') ? $gorsel : $base . '/' . ltrim($gorsel, '/');
        // HEAD yerine kismi GET: bazi CDN'ler HEAD'e 405 doner ama gorsel calisir.
        $c = curl_init($tam);
        curl_setopt_array($c, [
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_RANGE          => '0-2047',
        ]);
        curl_exec($c);
        $gkod = (int) curl_getinfo($c, CURLINFO_HTTP_CODE);
        $gtip = (string) curl_getinfo($c, CURLINFO_CONTENT_TYPE);
        curl_close($c);

        // 200 (tam) veya 206 (kismi icerik) kabul edilir.
        $gorselTamam = in_array($gkod, [200, 206], TRUE) && str_starts_with($gtip, 'image/');

        echo "  Gorsel  : $gorsel -> HTTP $gkod " . ($gtip ?: '-') . ($gorselTamam ? '' : '  (SORUN)') . "\n";
        if (!$gorselTamam) {
            $hataSayisi++;
        }
    } else {
        echo "  Gorsel  : ilk kayitta yok (kartta yedek gorunum cikar)\n";
    }

    echo "  Ornek   : " . mb_substr((string) ($ilk['name'] ?? '-'), 0, 50) . "\n\n";
}

if ($hataSayisi === 0) {
    echo "SONUC: Servis ve site sozlesmesi uyumlu.\n";
    exit(0);
}

echo "SONUC: $hataSayisi sorun bulundu.\n";
exit(1);
