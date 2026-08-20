<?php
/**
 * Nexora Etkinlik Senkronizasyonu
 * ------------------------------------------------------------------
 * Nexora genel katalog servisinden etkinlikleri çeker ve yerel
 * `nexora_events` tablosuna yazar. Site bu tabloyu okur; böylece her
 * ziyaretçi isteğinde dış servise gidilmez.
 *
 * Cron ile günde iki kez çalıştırılır (bkz. _tools/cron/README.md).
 *
 *   php _tools/sync_events.php           -> senkronize et
 *   php _tools/sync_events.php --dry-run -> yalnizca rapor
 *
 * Geçmiş etkinlikler:
 *   - Servisten `includePast=false` ile istenir (bitiş tarihi geçmiş olanlar gelmez).
 *   - Yerelde de bitiş tarihi dünden eski olan kayıtlar silinir.
 */

require __DIR__ . '/../app/Helpers/format_helper.php';

$dryRun = in_array('--dry-run', $argv, true);

// bind_param tip dizgisi: $satir dizisiyle birebir ayni sirada (30 alan).
const ALAN_TIPLERI = 'ssssssssssddssssisiiiiiiisissi';

// .env oku
$env = [];
foreach (file(__DIR__ . '/../.env') as $line) {
    if (preg_match('/^\s*([\w.]+)\s*=\s*(.*)$/', $line, $m)) {
        $env[$m[1]] = trim($m[2]);
    }
}

$apiBase = rtrim($env['nexora.url'] ?? 'http://localhost:5207', '/') . '/api/v1/public';
$apiKey  = $env['nexora.apiKey'] ?? '';

$db = new mysqli(
    $env['database.default.hostname'] ?? '127.0.0.1',
    $env['database.default.username'] ?? 'root',
    $env['database.default.password'] ?? '',
    $env['database.default.database'] ?? 'sultangazi_genclik',
    (int)($env['database.default.port'] ?? 3306)
);
$db->set_charset('utf8mb4');

$basladi = date('Y-m-d H:i:s');

/**
 * Senkron kaydını yazar.
 */
function logla(mysqli $db, string $basladi, string $durum, array $sayac = [], ?string $mesaj = null): void
{
    $stmt = $db->prepare(
        'INSERT INTO nexora_sync_log (started_at, finished_at, status, fetched, inserted, updated, deleted, message)
         VALUES (?, NOW(), ?, ?, ?, ?, ?, ?)'
    );
    $f = $sayac['fetched'] ?? 0;
    $i = $sayac['inserted'] ?? 0;
    $u = $sayac['updated'] ?? 0;
    $d = $sayac['deleted'] ?? 0;
    $stmt->bind_param('ssiiiis', $basladi, $durum, $f, $i, $u, $d, $mesaj);
    $stmt->execute();
    $stmt->close();
}

if ($apiKey === '') {
    fwrite(STDERR, "HATA: .env icinde nexora.apiKey bos.\n");
    logla($db, $basladi, 'error', [], 'API anahtari tanimsiz');
    exit(1);
}

/**
 * Servisten etkinlikleri çeker.
 */
function etkinlikleriGetir(string $url, string $apiKey): ?array
{
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_HTTPHEADER     => ['Accept: application/json', 'X-Api-Key: ' . $apiKey],
    ]);
    $govde = curl_exec($curl);
    $kod   = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $hata  = curl_error($curl);
    curl_close($curl);

    if ($govde === FALSE || $kod !== 200) {
        fwrite(STDERR, "HATA: Servis yaniti HTTP $kod $hata\n");
        return NULL;
    }

    $veri = json_decode($govde, TRUE);
    if (!is_array($veri) || empty($veri['success']) || !isset($veri['data']) || !is_array($veri['data'])) {
        fwrite(STDERR, "HATA: Beklenmeyen yanit bicimi.\n");
        return NULL;
    }

    return $veri['data'];
}

// Gecmis etkinlikler istenmez; limit yuksek tutulur.
$kayitlar = etkinlikleriGetir($apiBase . '/events?includePast=false&limit=500', $apiKey);

if ($kayitlar === NULL) {
    logla($db, $basladi, 'error', [], 'Servise ulasilamadi');
    exit(1);
}

echo "Servisten gelen etkinlik: " . count($kayitlar) . "\n";

/**
 * ISO tarih/saat degerini MySQL bicimine cevirir.
 */
function tarih(?string $iso): ?string
{
    if (!$iso) return NULL;
    $t = strtotime($iso);
    return $t === FALSE ? NULL : date('Y-m-d', $t);
}

function saat(?string $deger): ?string
{
    if (!$deger) return NULL;
    return substr($deger, 0, 8) ?: NULL;
}

$eklendi = 0;
$guncellendi = 0;
$gorulen = [];

$sql = 'INSERT INTO nexora_events
        (remote_id, slug, name, code, category_name, category_color, venue_type,
         facility_name, hall_name, location_name, latitude, longitude,
         start_date, end_date, start_time, end_time, is_single_day, gender,
         min_age, max_age, capacity, registered_count, available_capacity,
         registration_open, is_paid, price_info, resident_only, description,
         image_url, session_count, synced_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())
        ON DUPLICATE KEY UPDATE
         slug=VALUES(slug), name=VALUES(name), code=VALUES(code),
         category_name=VALUES(category_name), category_color=VALUES(category_color),
         venue_type=VALUES(venue_type), facility_name=VALUES(facility_name),
         hall_name=VALUES(hall_name), location_name=VALUES(location_name),
         latitude=VALUES(latitude), longitude=VALUES(longitude),
         start_date=VALUES(start_date), end_date=VALUES(end_date),
         start_time=VALUES(start_time), end_time=VALUES(end_time),
         is_single_day=VALUES(is_single_day), gender=VALUES(gender),
         min_age=VALUES(min_age), max_age=VALUES(max_age), capacity=VALUES(capacity),
         registered_count=VALUES(registered_count), available_capacity=VALUES(available_capacity),
         registration_open=VALUES(registration_open), is_paid=VALUES(is_paid),
         price_info=VALUES(price_info), resident_only=VALUES(resident_only),
         description=VALUES(description), image_url=VALUES(image_url),
         session_count=VALUES(session_count), synced_at=NOW()';

$stmt = $dryRun ? NULL : $db->prepare($sql);

foreach ($kayitlar as $r) {
    $remoteId = $r['id'] ?? NULL;
    $ad       = $r['name'] ?? NULL;
    $baslangic = tarih($r['startDate'] ?? NULL);

    // Zorunlu alanlari olmayan kayitlar atlanir.
    if (!$remoteId || !$ad || !$baslangic) {
        continue;
    }

    $gorulen[] = $remoteId;

    // Slug: ad + kisa kimlik (ayni isimli etkinlikler cakismasin)
    $slug = slug($ad);
    if ($slug === '') {
        $slug = 'etkinlik';
    }
    $slug = substr($slug, 0, 200);

    $kon = $r['location'] ?? NULL;

    $satir = [
        $remoteId,
        $slug,
        mb_substr($ad, 0, 255),
        $r['code'] ?? NULL,
        $r['categoryName'] ?? NULL,
        $r['categoryColor'] ?? NULL,
        $r['venueType'] ?? NULL,
        $r['facilityName'] ?? NULL,
        $r['hallName'] ?? NULL,
        is_array($kon) ? ($kon['name'] ?? NULL) : NULL,
        is_array($kon) ? ($kon['latitude'] ?? NULL) : NULL,
        is_array($kon) ? ($kon['longitude'] ?? NULL) : NULL,
        $baslangic,
        tarih($r['endDate'] ?? NULL) ?? $baslangic,
        saat($r['startTime'] ?? NULL),
        saat($r['endTime'] ?? NULL),
        !empty($r['isSingleDay']) ? 1 : 0,
        $r['gender'] ?? NULL,
        $r['minAge'] ?? NULL,
        $r['maxAge'] ?? NULL,
        $r['capacity'] ?? NULL,
        $r['registeredCount'] ?? NULL,
        $r['availableCapacity'] ?? NULL,
        !empty($r['registrationOpen']) ? 1 : 0,
        !empty($r['isPaid']) ? 1 : 0,
        $r['priceInfo'] ?? NULL,
        !empty($r['sultangaziResidentOnly']) ? 1 : 0,
        $r['description'] ?? NULL,
        $r['primaryImageUrl'] ?? NULL,
        $r['sessionCount'] ?? 0,
    ];

    if ($dryRun) {
        $eklendi++;
        continue;
    }

    $stmt->bind_param(ALAN_TIPLERI, ...$satir);
    $stmt->execute();

    // affected_rows: 1 = eklendi, 2 = guncellendi
    if ($stmt->affected_rows === 1) {
        $eklendi++;
    } elseif ($stmt->affected_rows === 2) {
        $guncellendi++;
    }
}

if ($stmt) {
    $stmt->close();
}

// Temizlik: servisten artik gelmeyen ve bitisi gecmis kayitlari sil.
$silindi = 0;
if (!$dryRun) {
    // 1) Bitis tarihi dunden eski olanlar
    $db->query('DELETE FROM nexora_events WHERE end_date < CURDATE()');
    $silindi += $db->affected_rows;

    // 2) Servisten gelmeyenler (iptal edilmis olabilir)
    if ($gorulen !== []) {
        $liste = implode(',', array_map(fn($x) => "'" . $db->real_escape_string($x) . "'", $gorulen));
        $db->query("DELETE FROM nexora_events WHERE remote_id NOT IN ($liste)");
        $silindi += $db->affected_rows;
    }
}

$sayac = [
    'fetched'  => count($kayitlar),
    'inserted' => $eklendi,
    'updated'  => $guncellendi,
    'deleted'  => $silindi,
];

if (!$dryRun) {
    logla($db, $basladi, 'ok', $sayac);
}

printf(
    "%s -> eklenen=%d guncellenen=%d silinen=%d\n",
    $dryRun ? 'KURU CALISMA' : 'TAMAM',
    $eklendi,
    $guncellendi,
    $silindi
);

if (!$dryRun) {
    $r = $db->query('SELECT COUNT(*) AS n FROM nexora_events')->fetch_assoc();
    echo "Yereldeki etkinlik sayisi: {$r['n']}\n";
}
