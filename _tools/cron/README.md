# Zamanlanmış Görevler (Cron)

Etkinlikler Nexora servisinden **günde iki kez** çekilip yerel veritabanına yazılır.
Site her ziyaretçi isteğinde dış servise gitmez; yalnızca yerel tabloyu okur.

| Görev | Betik | Sıklık |
| --- | --- | --- |
| Etkinlik senkronizasyonu | `_tools/sync_events.php` | 06:00 ve 18:00 |

## Sunucuda kurulum (crontab)

```bash
crontab -e
```

Aşağıdaki iki satırı ekleyin (yolu kendi kurulumunuza göre düzeltin):

```cron
0 6  * * * cd /var/www/genclik && /usr/bin/php _tools/sync_events.php >> writable/logs/cron-events.log 2>&1
0 18 * * * cd /var/www/genclik && /usr/bin/php _tools/sync_events.php >> writable/logs/cron-events.log 2>&1
```

`cd` şart: betik `.env` dosyasını proje köküne göre okur.

## cPanel / Plesk

Zamanlanmış görev ekranında komut olarak şunu kullanın:

```
cd /home/KULLANICI/public_html && /usr/local/bin/php _tools/sync_events.php >> writable/logs/cron-events.log 2>&1
```

Zamanlama: `0 6,18 * * *`

## macOS (yerel geliştirme)

`launchd` kullanılır. Örnek dosya: `_tools/cron/local.sultangazi-sync-events.plist`

```bash
cp _tools/cron/local.sultangazi-sync-events.plist ~/Library/LaunchAgents/
launchctl load ~/Library/LaunchAgents/local.sultangazi-sync-events.plist
```

## Elle çalıştırma

```bash
php _tools/sync_events.php            # senkronize et
php _tools/sync_events.php --dry-run  # yalnizca rapor, yazmaz
```

## İzleme

Her çalışma `nexora_sync_log` tablosuna kaydedilir:

```sql
SELECT started_at, status, fetched, inserted, updated, deleted, message
FROM nexora_sync_log ORDER BY started_at DESC LIMIT 10;
```

Beklenen davranış:
- `status = ok` ve `fetched > 0`
- Servis erişilemezse `status = error` yazılır, **mevcut veriler silinmez**
  (site son başarılı senkronun içeriğini göstermeye devam eder).

## Geçmiş etkinlikler

İki katmanlı temizlik yapılır:
1. Servisten `includePast=false` ile istenir (bitişi geçmiş olanlar hiç gelmez).
2. Yerelde `end_date < CURDATE()` olan kayıtlar her senkronda silinir.

Ayrıca sorgular da geçmişi filtreler; cron gecikse bile eski etkinlik görünmez.
