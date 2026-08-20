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

## Spor Akademisi (Nexorada) Servisi

Spor branşları, hizmet tesisleri ve akademi programı `sporakademi.sultangazi.bel.tr`
üzerindeki genel API'den okunur.

| Konu | Yer |
| --- | --- |
| İstemci | `app/Libraries/SportAcademyApi.php` |
| Adres, önbellek süresi, zaman aşımı | `app/Config/Constants.php` (`SPORT_ACADEMY_*`) |
| Başlık ve açıklama metinleri | `app/Language/{tr,en}/WebIndex.php` |
| Tasarım | `app/Views/Frontend/index.html.twig` |

Kullanılan uç noktalar (`/api/public/v1`): `branches`, `facilities`, `courses`, `news`, `achievements`.

Davranış:
- Yanıtlar 30 dakika önbelleklenir (`SPORT_ACADEMY_CACHE_TTL`).
- Servis erişilemezse ilgili alanlar gizlenir, site çalışmaya devam eder.
- Bağlantı kurulamazsa devre kesici devreye girer; aynı sayfa yüklemesinde
  diğer uç noktalar için tekrar beklenmez.

Önbelleği temizlemek için:

```bash
rm -rf writable/cache/sport_academy_*
```

Not: Yerel branş slug'ları akademideki slug'larla eşleşirse (futbol, basketbol,
voleybol, gures) doğrudan branş detayına, eşleşmezse branş listesine gidilir.
