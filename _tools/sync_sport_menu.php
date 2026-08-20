<?php
/**
 * Spor branşı projelerini "Spor" menüsü altına ekler.
 * Mevcut Taekwondo kaydı (menu_id 69) şablon olarak kullanılır.
 *
 *   php _tools/sync_sport_menu.php          -> kuru çalışma
 *   php _tools/sync_sport_menu.php --apply  -> uygular
 */


/**
 * Türkçe harf kurallarına uygun başlık biçimi.
 * PHP'nin mb_strtolower'ı "İ" harfini bozduğu için özel eşleme kullanılır.
 */
function trBaslik(string $metin): string {
    // Veride Türkçe "İ" zaten doğru kullanıldığı için ASCII "I" -> "i" eşlenir
    // (BADMINTON, FITNESS gibi yabancı kelimeler doğru yazılsın).
    $buyuk = ['I' => 'i', 'İ' => 'i', 'Ş' => 'ş', 'Ğ' => 'ğ', 'Ü' => 'ü', 'Ö' => 'ö', 'Ç' => 'ç'];
    $kucuk = ['ı' => 'I', 'i' => 'İ', 'ş' => 'Ş', 'ğ' => 'Ğ', 'ü' => 'Ü', 'ö' => 'Ö', 'ç' => 'Ç'];

    $metin = mb_strtolower(strtr($metin, $buyuk), 'UTF-8');

    $kelimeler = preg_split('/(\s+)/u', $metin, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($kelimeler as $i => $kelime) {
        if (trim($kelime) === '') {
            continue;
        }
        $ilk = mb_substr($kelime, 0, 1, 'UTF-8');
        $ilk = $kucuk[$ilk] ?? mb_strtoupper($ilk, 'UTF-8');
        $kelimeler[$i] = $ilk . mb_substr($kelime, 1, null, 'UTF-8');
    }

    return implode('', $kelimeler);
}

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

const SPOR_MENU_PARENT_ID = 65;   // "Spor" üst menüsü
const SPOR_PROJE_KATEGORI = 8;    // Spor branşları proje kategorisi
const MENU_TYPE_PROJECT   = 6;

// Menüde zaten bulunan proje id'leri
$mevcut = [];
$res = $db->query('SELECT menu_project_id FROM menus WHERE menu_parent_id = ' . SPOR_MENU_PARENT_ID);
while ($r = $res->fetch_assoc()) {
    $mevcut[(int)$r['menu_project_id']] = true;
}

// Eklenmesi gereken aktif spor projeleri
$sql = 'SELECT p.project_id, l1.project_name AS ad_tr, l2.project_name AS ad_en
        FROM projects p
        LEFT JOIN projects_lang l1 ON l1.project_id = p.project_id AND l1.lang_id = 1
        LEFT JOIN projects_lang l2 ON l2.project_id = p.project_id AND l2.lang_id = 2
        WHERE p.status = 1 AND p.project_category_id = ' . SPOR_PROJE_KATEGORI . '
        ORDER BY l1.project_name';
$projeler = $db->query($sql);

// Sıra numarası mevcut en büyükten devam etsin
$row = $db->query('SELECT COALESCE(MAX(menu_order), 0) AS m FROM menus WHERE menu_parent_id = ' . SPOR_MENU_PARENT_ID)->fetch_assoc();
$order = (int)$row['m'];

$eklenen = 0;
while ($p = $projeler->fetch_assoc()) {
    $pid = (int)$p['project_id'];
    if (isset($mevcut[$pid])) {
        continue;
    }

    // Menüde başlık düzgün görünsün: TAEKWONDO -> Taekwondo (Türkçe harf kurallarıyla)
    $adTr = trBaslik($p['ad_tr']);
    $adEn = trBaslik($p['ad_en'] ?: $p['ad_tr']);

    printf("  + %-22s (proje #%d)\n", $adTr, $pid);

    if ($apply) {
        $order++;
        $stmt = $db->prepare(
            'INSERT INTO menus
             (status, status_mobile, menu_parent_id, menu_template_sub_menu_id, menu_type,
              menu_page_id, menu_sultangazi_content_id, menu_contract_id, menu_service_id,
              menu_project_id, menu_president_content_id, menu_order, menu_target, menu_location)
             VALUES (1, 1, ?, 0, ?, 0, 0, 0, 0, ?, 0, ?, "_self", "1,2")'
        );
        $parent = SPOR_MENU_PARENT_ID;
        $type   = MENU_TYPE_PROJECT;
        $stmt->bind_param('iiii', $parent, $type, $pid, $order);
        $stmt->execute();
        $menuId = $stmt->insert_id;
        $stmt->close();

        $stmt = $db->prepare('INSERT INTO menus_lang (menu_id, lang_id, menu_name, menu_link) VALUES (?, ?, ?, "")');
        foreach ([1 => $adTr, 2 => $adEn] as $langId => $ad) {
            $stmt->bind_param('iis', $menuId, $langId, $ad);
            $stmt->execute();
        }
        $stmt->close();
    }
    $eklenen++;
}

echo "TOPLAM: $eklenen menu kaydi" . ($apply ? ' eklendi.' : ' eklenecek (yazmak icin --apply).') . "\n";
