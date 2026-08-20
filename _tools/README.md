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

Branş **detay sayfaları** (`/projeler/{slug}/{id}`) da bu servisten beslenir:
eşleşen branşlarda antrenör, yaş grubu, antrenman programı, kazanımlar ve
gerekli belgeler gösterilir; eşleşmeyenlerde genel tanıtım metni ve akademiye
yönlendirme kartı çıkar. Bu alan yalnızca spor branşı kategorisinde
(`SPORT_PROJECT_CATEGORY_ID`) görünür, diğer projeler etkilenmez.
Metinler: `app/Language/{tr,en}/WebProjects.php` -> `academy`.

## Görseller: WebP

Yönetim panelinden yüklenen her görsel için otomatik olarak `.webp` sürümü
üretilir (`Backend/BaseController::uploadSingleFile`). Site tarafı görseli
gönderirken önce `.webp` arar, yoksa orijinali kullanır
(`Frontend/BaseController::imageControl`).

Orijinal dosyalar **silinmez**; böylece dönüşüm başarısız olsa bile hiçbir
görsel kırılmaz.

### Mevcut görselleri dönüştürme

```bash
php _tools/convert_webp.php                      # rapor
php _tools/convert_webp.php --apply              # tumunu donustur
php _tools/convert_webp.php --apply --limit=200  # parca parca
php _tools/convert_webp.php --apply --dir=uploads/news
```

Ölçülen kazanç: ortalama **%65 boyut azalması**. Animasyonlu GIF'ler
dönüştürülmez.

Kalite ayarı: `app/Config/Constants.php` -> `IMAGE_UPLOAD_QUALITY`

## Yönetim Paneli Menüsü

Kontrolcüsü ve verisi hazır olduğu halde menüde görünmeyen sayfaları ekler:

```bash
php _tools/fix_admin_menu.php          # rapor
php _tools/fix_admin_menu.php --apply  # menuye ekle
```

## Mobil API (`/api/mobile`)

Ana site yapısından uyarlanan JSON servisi: 20 uç, Bearer token korumalı.
Ayrıntılar ve farklar: **`_tools/API.md`**

```bash
curl -X POST -d "username=...&password=..." http://SITE/api/mobile/authenticate
curl -H "Authorization: Bearer <token>" http://SITE/api/mobile/events
```

## Başkan İçerikleri (ana site API'si + cron)

Başkanın özgeçmişi ve mesajı, Sultangazi Belediyesi ana sitesinin genel mobil
servisinden çekilir (yerel kopya bayat kalıyordu):

```
GET https://www.sultangazi.bel.tr/api/mobile/president-contents
GET https://www.sultangazi.bel.tr/api/mobile/president-general-information
```

| Konu | Yer |
| --- | --- |
| Senkron betiği | `_tools/sync_president.php` |
| Şema | `_database/sultangazi_president.sql` |
| Model | `app/Models/Frontend/President/SultangaziPresidentModel.php` |
| API adresi | `.env` -> `sultangazi.apiUrl` |

Sayfa adresleri korunur: `/baskan/baskanin-ozgecmisi/1`, `/baskan/baskanin-mesaji/2`.
Senkron tablosu boşsa sayfa eski yerel kaynağa düşer, boş kalmaz.
Servis erişilemezse mevcut kayıtlar silinmez.

## Etkinlikler (site içi detay + cron)

Etkinlik detayları artık dış portalda değil, **site içinde** açılır:

| Sayfa | Adres |
| --- | --- |
| Liste | `/etkinlikler` (sayfalı, 12'şerli) |
| Detay | `/etkinlikler/{slug}/{id}` |

Veriler her ziyaretçi isteğinde API'den çekilmez; **cron ile günde iki kez**
yerel `nexora_events` tablosuna aktarılır:

```bash
php _tools/sync_events.php            # senkronize et
php _tools/sync_events.php --dry-run  # yalnizca rapor
```

Kurulum ve zamanlama: `_tools/cron/README.md`

**Geçmiş etkinlikler gösterilmez.** Üç katmanlı koruma:
1. Servisten `includePast=false` ile istenir.
2. Her senkronda `end_date < CURDATE()` kayıtları silinir.
3. Model sorguları da geçmişi filtreler; cron gecikse bile eski etkinlik çıkmaz.
   Geçmiş bir etkinliğin detay adresi doğrudan açılırsa 404'e yönlendirilir.

Servis erişilemezse mevcut kayıtlar **silinmez**; site son başarılı senkronun
içeriğini göstermeye devam eder. Her çalışma `nexora_sync_log` tablosuna yazılır.

Not: Başvurular hâlâ Sultanşehir vatandaş portalına yönlendirir
(`/etkinlikler?eventId=...`); yalnızca içerik görüntüleme siteye taşındı.

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

Başvuru/detay bağlantıları vatandaş portalına gider:

| Bağlantı | Adres |
| --- | --- |
| Etkinlik detayı | `/etkinlikler?eventId={id}` |
| Tüm etkinlikler | `/etkinlikler` |
| Tesisler | `/hizmetler` |

Not: Portalda `/etkinlikler/{id}` diye bir rota **yoktur**; detay, liste sayfasındaki
`eventId` sorgu parametresiyle açılır (citizen-portal `DiscoverTab` deep link).

Davranış:
- Yanıtlar 15 dakika önbelleklenir (`NEXORA_CACHE_TTL`).
- API anahtarı boşsa servise hiç gidilmez.
- Servis erişilemez veya anahtar geçersizse ilgili alanlar gizlenir, site çalışır.
- Bağlantı kurulamazsa devre kesici devreye girer.

Önbelleği temizlemek için:

```bash
rm -rf writable/cache/nexora_* writable/cache/sport_academy_*
```

### Canlıya alırken

`nexora.url` varsayılanı **yerel geliştirme adresidir** (`http://localhost:5207`).
Canlı sunucuda bu adres çalışmaz ve etkinlik/tesis alanları sessizce gizlenir.
Bu yüzden `CI_ENVIRONMENT = production` iken şu iki durum log'a `CRITICAL`
olarak yazılır:

- `nexora.apiKey` boş
- `nexora.url` hâlâ `localhost` / `127.0.0.1` gösteriyor

Yayına almadan önce `.env` içindeki `nexora.url` ve `nexora.apiKey` değerlerini
gerçek servis bilgileriyle doldurun, sonra `php _tools/check_nexora.php` çalıştırın.

### Bağlantı doğrulaması

Nexora ayağa kalktığında veya adres/anahtar değiştiğinde:

```bash
php _tools/check_nexora.php
```

Servise gerçekten bağlanır ve site kodunun beklediği tüm alanların döndüğünü,
görsel yollarının açıldığını kontrol eder. Sorun varsa 1 ile çıkar; şunları yakalar:
servis kapalı, geçersiz API anahtarı, eksik alan (sözleşme değişikliği), kırık görsel.
