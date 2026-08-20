<?php
/**
 * Toplu WebP Dönüştürme
 * ------------------------------------------------------------------
 * `uploads/` altındaki mevcut JPG/PNG görseller için `.webp` sürümü üretir.
 * Yeni yüklemeler zaten otomatik dönüştürülür (Backend/BaseController);
 * bu betik geçmişte yüklenmiş dosyalar içindir.
 *
 *   php _tools/convert_webp.php                 -> rapor (kuru calisma)
 *   php _tools/convert_webp.php --apply         -> donusturur
 *   php _tools/convert_webp.php --apply --limit=200
 *   php _tools/convert_webp.php --apply --dir=uploads/news
 *
 * Orijinal dosyalar SİLİNMEZ. Site `.webp` varsa onu, yoksa orijinali
 * gönderir (bkz. imageControl). Böylece dönüşüm yarıda kalsa bile
 * hiçbir görsel kırılmaz.
 */

require __DIR__ . '/../vendor/autoload.php';

use WebPConvert\WebPConvert;

$apply = in_array('--apply', $argv, true);
$limit = 0;
$dizin = __DIR__ . '/../uploads';

foreach ($argv as $a) {
    if (preg_match('/^--limit=(\d+)$/', $a, $m)) {
        $limit = (int) $m[1];
    }
    if (preg_match('/^--dir=(.+)$/', $a, $m)) {
        $dizin = __DIR__ . '/../' . trim($m[1], '/');
    }
}

if (!is_dir($dizin)) {
    fwrite(STDERR, "HATA: dizin yok: $dizin\n");
    exit(1);
}

// Kalite ayari Constants.php ile ayni tutulur.
const KALITE = 100;

$uzantilar = ['jpg', 'jpeg', 'png'];

$bekleyen = [];
$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dizin, FilesystemIterator::SKIP_DOTS)
);

foreach ($it as $f) {
    if (!$f->isFile()) {
        continue;
    }

    $uzanti = strtolower($f->getExtension());
    if (!in_array($uzanti, $uzantilar, true)) {
        continue;
    }

    $webp = $f->getPath() . '/' . $f->getBasename('.' . $f->getExtension()) . '.webp';
    if (is_file($webp)) {
        continue;
    }

    $bekleyen[] = [$f->getPathname(), $webp, $f->getSize()];
}

$toplam = count($bekleyen);
printf("Donusturulecek gorsel: %d\n", $toplam);

if ($toplam === 0) {
    echo "Tum gorsellerin webp surumu mevcut.\n";
    exit(0);
}

if (!$apply) {
    $mb = array_sum(array_column($bekleyen, 2)) / 1048576;
    printf("KURU CALISMA -> %d dosya (%.1f MB). Donusturmek icin --apply\n", $toplam, $mb);
    exit(0);
}

if ($limit > 0) {
    $bekleyen = array_slice($bekleyen, 0, $limit);
    printf("Bu calismada islenecek: %d (--limit)\n", count($bekleyen));
}

$basarili = 0;
$hatali   = 0;
$oncesi   = 0;
$sonrasi  = 0;

foreach ($bekleyen as $i => [$kaynak, $hedef, $boyut]) {
    try {
        WebPConvert::convert($kaynak, $hedef, [
            'quality'     => 'auto',
            'max-quality' => KALITE,
            'converters'  => ['cwebp', 'gd', 'imagick'],
        ]);

        if (is_file($hedef)) {
            $basarili++;
            $oncesi  += $boyut;
            $sonrasi += filesize($hedef);
        } else {
            $hatali++;
        }
    } catch (\Throwable $e) {
        $hatali++;
        fwrite(STDERR, sprintf("  HATA %s: %s\n", basename($kaynak), mb_substr($e->getMessage(), 0, 90)));
    }

    if (($i + 1) % 100 === 0) {
        printf("  ... %d/%d\n", $i + 1, count($bekleyen));
    }
}

printf(
    "TAMAM -> basarili=%d hatali=%d | %.1f MB -> %.1f MB (%%%d tasarruf)\n",
    $basarili,
    $hatali,
    $oncesi / 1048576,
    $sonrasi / 1048576,
    $oncesi > 0 ? round((1 - $sonrasi / $oncesi) * 100) : 0
);
