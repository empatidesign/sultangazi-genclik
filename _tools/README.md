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

## CSS Derleme

`assets/css/output.css` derlenmiş dosyadır, elle düzenlenmez.
Kaynak `assets/css/style.css` (Tailwind v4). Yeni sınıf eklerseniz yeniden derleyin:

```bash
npx @tailwindcss/cli@4.1.11 -i assets/css/style.css -o assets/css/output.css
```

## Anasayfa: Eğitim Kurumlarımız Alanı

| Ne değişecek | Nerede |
| --- | --- |
| Başlık, açıklama, kurum metinleri | `app/Language/tr/WebIndex.php` ve `en/WebIndex.php` (`education`) |
| Site adresi, logo, renk teması | `app/Config/Constants.php` (`EDUCATION_INSTITUTIONS`) |
| Logo dosyaları | `assets/img/education/` |
| Tasarım | `app/Views/Frontend/index.html.twig` (`Education Institutions`) |

## Spor Akademisi Servisi (spor branşları)

Anasayfadaki spor branşı kartları, `sporakademi.sultangazi.bel.tr` üzerindeki
genel API'den branş listesini okur ve her branş için akademideki detay adresini üretir.

| Konu | Yer |
| --- | --- |
| İstemci | `app/Libraries/SportAcademyApi.php` |
| Adres, önbellek, zaman aşımı | `app/Config/Constants.php` (`SPORT_ACADEMY_*`) |

Yerel branş slug'ı akademideki slug ile eşleşirse (futbol, basketbol, voleybol,
gures) doğrudan branş detayına, eşleşmezse branş listesine gidilir.

## Nexora Genel Katalog Servisi (etkinlikler ve hizmet tesisleri)

Etkinlikler ve hizmet tesisleri Nexora backend'inin token gerektirmeyen
genel uçlarından okunur:

```
GET /api/v1/public/events
GET /api/v1/public/service-facilities
```

Uçlar `X-Api-Key` başlığı ile korunur.

| Konu | Yer |
| --- | --- |
| İstemci | `app/Libraries/NexoraApi.php` |
| Adres ve API anahtarı | `.env` (`nexora.url`, `nexora.apiKey`, `nexora.portalUrl`) |
| Varsayılanlar | `app/Config/Constants.php` (`NEXORA_*`) |
| Metinler | `app/Language/{tr,en}/WebIndex.php` |
| Tasarım | `app/Views/Frontend/index.html.twig` |

`.env` örneği:

```
nexora.url = http://localhost:5207
nexora.apiKey = dev-public-key-2026
nexora.portalUrl = https://sultansehir.sultangazi.bel.tr
```

Davranış:
- Yanıtlar 15 dakika önbelleklenir (`NEXORA_CACHE_TTL`).
- API anahtarı boşsa servise hiç gidilmez.
- Servis erişilemez veya anahtar geçersizse ilgili alanlar gizlenir, site çalışır.
- Bağlantı kurulamazsa devre kesici devreye girer.

Önbelleği temizlemek için:

```bash
rm -rf writable/cache/nexora_* writable/cache/sport_academy_*
```
