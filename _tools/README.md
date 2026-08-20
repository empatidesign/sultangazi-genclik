# Bakım Araçları

Canlı sunucudan alınan veritabanı yedeğindeki içerik tutarsızlıklarını düzeltir.
Her script önce **kuru çalışma** yapar; veritabanına yazmak için `--apply` gerekir.

| Script | Amaç |
| --- | --- |
| `fix_slugs.php` | Sluglaştırılmamış kayıtları (`TENİS` gibi ham başlıklar) düzeltir. 400/500 hatalarına yol açıyordu. |
| `sync_sport_menu.php` | Spor kategorisindeki projeleri "Spor" menüsü altına ekler. |

```bash
php _tools/fix_slugs.php            # rapor
php _tools/fix_slugs.php --apply    # uygula
```

Bağlantı bilgileri `.env` dosyasından okunur.
