<?php
/**
 * Yönetim Paneli Menü Onarımı
 * ------------------------------------------------------------------
 * Gençlik sitesinde kontrolcüsü, rotası, görünümü ve verisi hazır olan
 * ancak `dashboard_menu` tablosunda kaydı bulunmayan yönetim sayfalarını
 * menüye ekler. Bu sayfalar çalışır durumdadır ama menüde görünmedikleri
 * için yöneticiler tarafından erişilemez.
 *
 *   php _tools/fix_admin_menu.php           -> rapor (kuru calisma)
 *   php _tools/fix_admin_menu.php --apply   -> menuye ekler
 *
 * Betik tekrar çalıştırılabilir: zaten var olan menüler atlanır.
 */

$apply = in_array('--apply', $argv, true);

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

/**
 * Eklenecek menuler.
 * ust: ust menunun adi (dashboard_menu_lang icinde aranir)
 * Sira degerleri ana sitedeki duzeni izler.
 */
$menuler = [
    ['url' => 'map-module/map-categories',               'tr' => 'Harita Kategorileri',        'en' => 'Map Categories',        'ust' => 'Harita Modülü', 'sira' => 10],
    ['url' => 'map-module/map-locations',                'tr' => 'Harita Konumları',           'en' => 'Map Locations',         'ust' => 'Harita Modülü', 'sira' => 20],
    ['url' => 'designs/fast-menu-management',            'tr' => 'Hızlı Menü Yönetimi',      'en' => 'Fast Menu Management',  'ust' => 'Tasarımlar',    'sira' => 30],
    ['url' => 'sultangazi/city-guide-categories',        'tr' => 'Şehir Rehberi Kategorileri', 'en' => 'City Guide Categories', 'ust' => 'Sultangazi',     'sira' => 40],
    ['url' => 'sultangazi/city-guide-contents',          'tr' => 'Şehir Rehberi İçerikleri',  'en' => 'City Guide Contents',   'ust' => 'Sultangazi',     'sira' => 50],
    ['url' => 'contents/activity-report',                'tr' => 'Faaliyet Raporu',            'en' => 'Activity Report',       'ust' => 'İçerikler',     'sira' => 101],
    ['url' => 'contents/press-release',                  'tr' => 'Basın Bülteni',            'en' => 'Press Release',         'ust' => 'İçerikler',     'sira' => 111],
    ['url' => 'contents/strategic-plan-and-performance', 'tr' => 'Stratejik Plan',             'en' => 'Strategic Plan',        'ust' => 'İçerikler',     'sira' => 112],
    ['url' => 'contents/plan-and-program',               'tr' => 'Plan ve Program',            'en' => 'Plan and Programme',    'ust' => 'İçerikler',     'sira' => 113],
    ['url' => 'contents/internal-control',               'tr' => 'İç Kontrol',                'en' => 'Internal Control',      'ust' => 'İçerikler',     'sira' => 114],
    ['url' => 'contents/organization-chart',             'tr' => 'Organizasyon Şeması',       'en' => 'Organization Chart',    'ust' => 'İçerikler',     'sira' => 141],
];

/**
 * Ust menu kimligini adindan bulur.
 */
function ustMenuId(mysqli $db, string $ad): ?int
{
    $stmt = $db->prepare(
        'SELECT m.dashboard_menu_id
         FROM dashboard_menu m
         JOIN dashboard_menu_lang l ON l.dashboard_menu_id = m.dashboard_menu_id AND l.lang_id = 1
         WHERE l.dashboard_menu_name = ? AND m.dashboard_menu_parent_id = 0
         LIMIT 1'
    );
    $stmt->bind_param('s', $ad);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $row ? (int) $row['dashboard_menu_id'] : NULL;
}

$eklendi = 0;
$atlandi = 0;
$hata = 0;

foreach ($menuler as $m) {
    // Zaten var mi?
    $stmt = $db->prepare('SELECT dashboard_menu_id FROM dashboard_menu WHERE dashboard_menu_url = ? LIMIT 1');
    $stmt->bind_param('s', $m['url']);
    $stmt->execute();
    $mevcut = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($mevcut) {
        $atlandi++;
        continue;
    }

    $ustId = ustMenuId($db, $m['ust']);
    if ($ustId === NULL) {
        printf("  ATLANDI (ust menu yok: %s) %s\n", $m['ust'], $m['url']);
        $hata++;
        continue;
    }

    printf("  + %-42s -> %s\n", $m['url'], $m['ust']);

    if (!$apply) {
        $eklendi++;
        continue;
    }

    $stmt = $db->prepare(
        'INSERT INTO dashboard_menu (status, dashboard_menu_parent_id, dashboard_menu_icon, dashboard_menu_url, dashboard_menu_order)
         VALUES (1, ?, "", ?, ?)'
    );
    $stmt->bind_param('isi', $ustId, $m['url'], $m['sira']);
    $stmt->execute();
    $yeniId = $stmt->insert_id;
    $stmt->close();

    $stmt = $db->prepare('INSERT INTO dashboard_menu_lang (dashboard_menu_id, lang_id, dashboard_menu_name) VALUES (?, ?, ?)');
    foreach ([1 => $m['tr'], 2 => $m['en']] as $langId => $ad) {
        $stmt->bind_param('iis', $yeniId, $langId, $ad);
        $stmt->execute();
    }
    $stmt->close();

    $eklendi++;
}

printf(
    "%s -> eklenen=%d atlanan(zaten var)=%d hata=%d\n",
    $apply ? 'TAMAM' : 'KURU CALISMA',
    $eklendi,
    $atlandi,
    $hata
);
