# Mobil API (`/api/mobile`)

Ana site (`/Volumes/PROJELER/Web/Sultangazi`) yapısından uyarlanmıştır.
Mobil uygulamalar ve dış istemciler için JSON servisi sağlar.

## Kimlik Doğrulama

`authenticate` dışındaki tüm uçlar `Authorization: Bearer <token>` başlığı ister.

```bash
# 1) Token al
curl -X POST -d "username=KULLANICI&password=SIFRE" \
  https://SITE/api/mobile/authenticate

# {"access_token":"...","token_type":"bearer","expires_in":7200}

# 2) Ucları çağır
curl -H "Authorization: Bearer <token>" https://SITE/api/mobile/events
```

Kimlik bilgileri `.env` dosyasındadır:

```
mobileApi.username = sultangazi
mobileApi.password = ...
mobileApi.tokenExpire = 7200
```

**Canlıya alırken şifreyi mutlaka değiştirin.**

## Uçlar

Uç listesini programatik olarak almak için: `GET /api/mobile/` (token gerekmez).

| Uç | Yöntem | Açıklama |
| --- | --- | --- |
| `authenticate` | POST | Token alır |
| `menu` | GET | Menü (parent_id 0 = ana menü, location 1 = üst, 2 = alt) |
| `banner` | GET | Banner görselleri |
| `municipal-councils` | GET | Belediye encümenleri |
| `council-members` | GET | Meclis üyeleri |
| `directorates` | GET | Müdürlükler |
| `vice-presidents` | GET | Başkan yardımcıları |
| `services` | GET | Hizmetler |
| `projects` | GET | Projeler |
| `announcements` | GET | Duyurular |
| `news` | GET | Haberler |
| `events` | GET | Etkinlikler |
| `referances` | GET | Referanslar |
| `contact` | GET | İletişim bilgileri |
| `president-general-information` | GET | Başkan genel bilgileri |
| `president-contents` | GET | Başkan içerikleri |
| `president-gallery` | GET | Başkan galerisi |
| `push-notifications` | POST | Bildirim tokeni kaydeder |
| `sport-branches` | GET | Spor branşları (gençliğe özgü) |
| `education-institutions` | GET | Eğitim kurumları (gençliğe özgü) |

## Ana Siteden Farklar

Ana sitedeki yapı olduğu gibi kopyalanmadı; gençlik sitesinin veri
kaynaklarına uyarlandı:

| Konu | Ana site | Gençlik sitesi |
| --- | --- | --- |
| Yetkilendirme | `adminauth` (oturum yoksa HTML giriş sayfasına yönlendirir) | `mobileapiauth` (JSON 401 döner) |
| Etkinlikler | Yerel `events` tablosu | `nexora_events` (Nexora senkronu, geçmiş hariç) |
| Haberler | Varsayılan bağlantı | `application` bağlantısı |
| Görsel adresi | `base_url()` | `api_image_url()` (dosya yerelde yoksa ana siteye düşer) |
| Kimlik bilgileri | Koda gömülü | `.env` |
| Kök sayfa | HTML liste | JSON uç listesi |

Ek olarak gençliğe özgü iki uç eklendi: `sport-branches`, `education-institutions`.

## Bilinen Veri Eksiği

`president-gallery` uçları 200 döner ancak 56 görselin tamamı 404'tür.
Bu dosyalar ne yerelde ne de ana sitede bulunuyor; API uyarlamasından
önce de böyleydi (sitenin kendi `/baskan/galeri` sayfası da boş).
Görseller yüklendiğinde uç kendiliğinden çalışır.

## Yapı

```
app/Controllers/Frontend/Api/Mobile/   20 kontrolcü
app/Models/Frontend/Api/Mobile/        17 model
app/Filters/MobileApiAuth.php          Bearer token filtresi
app/Libraries/MobileApiJWT.php         JWT üretimi ve doğrulama
```
