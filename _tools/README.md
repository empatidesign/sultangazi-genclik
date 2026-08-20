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
