<?php
/**
 * Bozuk slug'ları uygulamanın kendi slug() yardımcısıyla düzeltir.
 * Kullanım:
 *   php _tools/fix_slugs.php          -> sadece raporlar (kuru çalışma)
 *   php _tools/fix_slugs.php --apply  -> veritabanına yazar
 */

require __DIR__ . '/../app/Helpers/format_helper.php';

$apply = in_array('--apply', $argv, true);

// .env'den veritabanı ayarlarını oku
$env = [];
foreach (file(__DIR__ . '/../.env') as $line) {
    if (preg_match('/^\s*([\w.]+)\s*=\s*(.*)$/', $line, $m)) {
        $env[$m[1]] = trim($m[2]);
    }
}

$db = new mysqli(
    $env['database.default.hostname'] ?? '127.0.0.1',
    $env['database.default.username'] ?? 'root',
    $env['database.default.password'] ?? '',
    $env['database.default.database'] ?? 'sultangazi_genclik',
    (int)($env['database.default.port'] ?? 3306)
);
$db->set_charset('utf8mb4');

// tablo => [id sütunu, isim sütunu, slug sütunu]
$targets = [
    'announcements_lang'       => ['announcement_lang_id', 'announcement_name', 'announcement_slug'],
    'events_lang'              => ['event_lang_id', 'event_name', 'event_slug'],
    'pages_lang'               => ['page_lang_id', 'page_name', 'page_slug'],
    'projects_lang'            => ['project_lang_id', 'project_name', 'project_slug'],
    'services_lang'            => ['service_lang_id', 'service_name', 'service_slug'],
    'sultangazi_contents_lang' => ['content_lang_id', 'content_name', 'content_slug'],
    'news_lang'                => ['news_lang_id', 'news_name', 'news_slug'],
];

$totalFixed = 0;

foreach ($targets as $table => [$pk, $nameCol, $slugCol]) {
    $check = $db->query("SHOW TABLES LIKE '$table'");
    if (!$check || $check->num_rows === 0) {
        echo "ATLANDI (tablo yok): $table\n";
        continue;
    }

    $rows = $db->query("SELECT `$pk`, `$nameCol`, `$slugCol` FROM `$table`");
    if (!$rows) {
        echo "ATLANDI (sorgu hatasi): $table -> {$db->error}\n";
        continue;
    }

    $fixed = 0;
    while ($row = $rows->fetch_assoc()) {
        $current = (string)$row[$slugCol];
        $name    = (string)$row[$nameCol];

        // Zaten temiz slug ise dokunma
        if ($current !== '' && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $current)) {
            continue;
        }

        // Mevcut slug'tan üret, boşsa isimden üret
        $new = slug($current !== '' ? $current : $name);
        if ($new === '' || $new === $current) {
            continue;
        }

        printf("  %-26s #%-6s %-34s -> %s\n", $table, $row[$pk], $current, $new);

        if ($apply) {
            $stmt = $db->prepare("UPDATE `$table` SET `$slugCol` = ? WHERE `$pk` = ?");
            $stmt->bind_param('si', $new, $row[$pk]);
            $stmt->execute();
            $stmt->close();
        }
        $fixed++;
    }

    if ($fixed > 0) {
        echo "$table: $fixed kayit" . ($apply ? ' guncellendi' : ' duzeltilecek') . "\n\n";
    }
    $totalFixed += $fixed;
}

echo "TOPLAM: $totalFixed kayit" . ($apply ? ' guncellendi.' : ' duzeltilecek (yazmak icin --apply).') . "\n";
