<?php
/**
 * Başkan İçerikleri Senkronizasyonu
 * ------------------------------------------------------------------
 * Sultangazi Belediyesi ana sitesinin genel mobil servisinden başkan
 * içeriklerini (özgeçmiş, mesaj) ve genel bilgileri çeker; yerel
 * `sultangazi_president_contents` / `sultangazi_president_info`
 * tablolarına yazar.
 *
 * Cron ile günde iki kez çalışır (bkz. _tools/cron/README.md).
 *
 *   php _tools/sync_president.php           -> senkronize et
 *   php _tools/sync_president.php --dry-run -> yalnizca rapor
 *
 * Not: Mevcut sayfa adresleri (ör. /baskan/baskanin-ozgecmisi/1) korunur;
 * slug ve kimlik numaraları sabit eşleme ile atanır.
 */

require __DIR__ . '/../app/Helpers/format_helper.php';

$dryRun = in_array('--dry-run', $argv, true);

$env = [];
foreach (file(__DIR__ . '/../.env') as $line) {
    if (preg_match('/^\s*([\w.]+)\s*=\s*(.*)$/', $line, $m)) {
        $env[$m[1]] = trim($m[2]);
    }
}

$apiBase = rtrim($env['sultangazi.apiUrl'] ?? 'https://www.sultangazi.bel.tr/api/mobile', '/');

$db = new mysqli(
    $env['database.default.hostname'] ?? '127.0.0.1',
    $env['database.default.username'] ?? 'root',
    $env['database.default.password'] ?? '',
    $env['database.default.database'] ?? 'sultangazi_genclik',
    (int)($env['database.default.port'] ?? 3306)
);
$db->set_charset('utf8mb4');

/**
 * Servisten JSON çeker.
 */
function getir(string $url): ?array
{
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_FOLLOWLOCATION => TRUE,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 45,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $govde = curl_exec($curl);
    $kod   = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $hata  = curl_error($curl);
    curl_close($curl);

    if ($govde === FALSE || $kod !== 200) {
        fwrite(STDERR, "HATA: $url -> HTTP $kod $hata\n");
        return NULL;
    }

    $veri = json_decode($govde, TRUE);
    if (!is_array($veri)) {
        fwrite(STDERR, "HATA: $url -> gecersiz JSON\n");
        return NULL;
    }

    return $veri;
}

/**
 * Mevcut sayfa adresleri bozulmasin diye sabit eslesme.
 * Servis icerik adini degistirse bile slug ve kimlik korunur.
 */
const SABIT_ESLEME = [
    'baskanin-ozgecmisi' => ['id' => 1, 'sira' => 1, 'anahtar' => 'ozgecmis'],
    'baskanin-mesaji'    => ['id' => 2, 'sira' => 2, 'anahtar' => 'mesaj'],
];

/**
 * Icerik adindan bilinen slug'i bulur.
 */
function slugBul(string $ad): ?string
{
    $normal = mb_strtolower($ad, 'UTF-8');

    if (str_contains($normal, 'özgeçmiş') || str_contains($normal, 'ozgecmis')) {
        return 'baskanin-ozgecmisi';
    }

    if (str_contains($normal, 'mesaj')) {
        return 'baskanin-mesaji';
    }

    // Bilinmeyen icerik: adindan uret
    $uretilen = slug($ad);

    return $uretilen !== '' ? $uretilen : NULL;
}

$icerikler = getir($apiBase . '/president-contents');

if ($icerikler === NULL) {
    fwrite(STDERR, "Servise ulasilamadi; mevcut kayitlar korunuyor.\n");
    exit(1);
}

echo "Servisten gelen icerik: " . count($icerikler) . "\n";

$eklendi = 0;
$guncellendi = 0;

foreach ($icerikler as $r) {
    $ad = trim((string) ($r['name'] ?? ''));
    if ($ad === '') {
        continue;
    }

    $slugDeger = slugBul($ad);
    if ($slugDeger === NULL) {
        continue;
    }

    $eslesme = SABIT_ESLEME[$slugDeger] ?? NULL;
    $id      = $eslesme['id'] ?? NULL;
    $sira    = $eslesme['sira'] ?? 99;

    $aciklama = $r['description'] ?? NULL;
    $gorsel   = $r['image'] ?? NULL;
    $gorsel   = ($gorsel === '' ? NULL : $gorsel);

    printf("  %-22s %s karakter %s\n", $slugDeger, mb_strlen((string) $aciklama), $gorsel ? '(gorselli)' : '');

    if ($dryRun) {
        $eklendi++;
        continue;
    }

    if ($id !== NULL) {
        // Kimlik sabit: adres bozulmasin
        $stmt = $db->prepare(
            'INSERT INTO sultangazi_president_contents (content_id, slug, name, description, image_url, sort_order, synced_at)
             VALUES (?,?,?,?,?,?,NOW())
             ON DUPLICATE KEY UPDATE slug=VALUES(slug), name=VALUES(name), description=VALUES(description),
                                     image_url=VALUES(image_url), sort_order=VALUES(sort_order), synced_at=NOW()'
        );
        $stmt->bind_param('issssi', $id, $slugDeger, $ad, $aciklama, $gorsel, $sira);
    } else {
        $stmt = $db->prepare(
            'INSERT INTO sultangazi_president_contents (slug, name, description, image_url, sort_order, synced_at)
             VALUES (?,?,?,?,?,NOW())
             ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description),
                                     image_url=VALUES(image_url), sort_order=VALUES(sort_order), synced_at=NOW()'
        );
        $stmt->bind_param('ssssi', $slugDeger, $ad, $aciklama, $gorsel, $sira);
    }

    $stmt->execute();

    if ($stmt->affected_rows === 1) {
        $eklendi++;
    } elseif ($stmt->affected_rows === 2) {
        $guncellendi++;
    }

    $stmt->close();
}

// Genel bilgiler
$bilgi = getir($apiBase . '/president-general-information');

if ($bilgi !== NULL && isset($bilgi[0]) && !$dryRun) {
    $b = $bilgi[0];
    $sosyal = isset($b['social_media']) ? json_encode($b['social_media'], JSON_UNESCAPED_UNICODE) : NULL;

    $db->query('DELETE FROM sultangazi_president_info');
    $stmt = $db->prepare(
        'INSERT INTO sultangazi_president_info (name_surname, sub_title, image_url, banner_url, social_media, synced_at)
         VALUES (?,?,?,?,?,NOW())'
    );
    $ad     = $b['name_surname'] ?? NULL;
    $alt    = $b['sub_title'] ?? NULL;
    $gorsel = $b['image'] ?? NULL;
    $banner = $b['banner'] ?? NULL;
    $stmt->bind_param('sssss', $ad, $alt, $gorsel, $banner, $sosyal);
    $stmt->execute();
    $stmt->close();

    echo "Genel bilgiler guncellendi: " . ($ad ?? '-') . "\n";
}

printf(
    "%s -> eklenen=%d guncellenen=%d\n",
    $dryRun ? 'KURU CALISMA' : 'TAMAM',
    $eklendi,
    $guncellendi
);
