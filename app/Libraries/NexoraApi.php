<?php

namespace App\Libraries;

use Config\Services;

/**
 * Nexora Genel Katalog API İstemcisi
 * -------------------------------------------------------------
 * Belediyenin etkinlik ve hizmet tesisi verileri Nexora backend'inin
 * token gerektirmeyen genel uçlarından okunur:
 *
 *   GET /api/v1/public/events
 *   GET /api/v1/public/service-facilities
 *
 * Uçlar `X-Api-Key` başlığı ile korunur (Nexora tarafında
 * `PublicApi:ApiKeys` ayarıyla eşleşmelidir).
 *
 * Tasarım ilkeleri:
 *  - Sonuçlar önbelleğe alınır; her sayfa isteğinde dış servise gidilmez.
 *  - Servis erişilemezse sayfa çökmez, boş dizi döner.
 *  - Bağlantı hiç kurulamazsa devre kesici devreye girer; aynı sayfa
 *    yüklemesinde diğer uçlar için tekrar beklenmez.
 */
class NexoraApi
{
    /** Genel API kök adresi */
    protected string $baseUrl;

    /** Servis kök adresi (görsel yolları için) */
    protected string $siteUrl;

    /** X-Api-Key değeri */
    protected string $apiKey;

    /** Önbellek süresi (saniye) */
    protected int $ttl;

    /** İstek zaman aşımı (saniye) */
    protected int $timeout;

    /**
     * Servis bu istek sırasında erişilemez bulunduysa TRUE olur.
     */
    protected static bool $unavailable = FALSE;

    public function __construct()
    {
        $this->siteUrl = rtrim(env('nexora.url', NEXORA_URL), '/');
        $this->baseUrl = $this->siteUrl . NEXORA_API_PATH;
        $this->apiKey  = (string) env('nexora.apiKey', '');
        $this->ttl     = NEXORA_CACHE_TTL;
        $this->timeout = NEXORA_TIMEOUT;
    }

    /**
     * Yaklaşan etkinlikler.
     */
    public function events(int $limit = 8): array
    {
        return $this->fetch('events', ['limit' => $limit]);
    }

    /**
     * Hizmet tesisleri.
     */
    public function facilities(int $limit = 8): array
    {
        return $this->fetch('service-facilities', ['limit' => $limit]);
    }

    /**
     * Etkinlik kategorileri.
     */
    public function eventCategories(): array
    {
        return $this->fetch('events/categories');
    }

    /**
     * Tesis harita noktaları.
     */
    public function facilityMapPoints(): array
    {
        return $this->fetch('service-facilities/map-points');
    }

    /**
     * Göreli görsel yolunu mutlak adrese çevirir.
     */
    public function imageUrl(?string $path): ?string
    {
        if ($path === NULL || $path === '') {
            return NULL;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $encoded = implode('/', array_map('rawurlencode', explode('/', ltrim($path, '/'))));

        return $this->siteUrl . '/' . $encoded;
    }

    /**
     * Vatandaş portalındaki etkinlik başvuru adresi.
     *
     * Portal tarafında etkinlik detayı ayrı bir rota değil, liste sayfasındaki
     * `eventId` sorgu parametresidir (bkz. citizen-portal App.tsx ve
     * public-portal `citizenPortalEventUrl`). `/etkinlikler/{id}` biçimi 404 verir.
     */
    public function eventUrl(?string $id = NULL): string
    {
        $portal = rtrim(env('nexora.portalUrl', NEXORA_PORTAL_URL), '/') . '/etkinlikler';

        return isNotNull($id) ? $portal . '?eventId=' . rawurlencode($id) : $portal;
    }

    /**
     * Vatandaş portalındaki hizmet tesisleri sayfası.
     */
    public function facilityUrl(): string
    {
        return rtrim(env('nexora.portalUrl', NEXORA_PORTAL_URL), '/') . '/hizmetler';
    }

    /**
     * Uç noktayı çağırır, sonucu önbelleğe alır.
     */
    protected function fetch(string $endpoint, array $params = []): array
    {
        // Anahtar tanımlı değilse servise hiç gidilmez.
        if ($this->apiKey === '') {
            return [];
        }

        $cache    = Services::cache();
        $cacheKey = 'nexora_' . str_replace(['/', '-'], '_', $endpoint) . (empty($params) ? '' : '_' . md5(http_build_query($params)));

        $cached = $cache->get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        if (self::$unavailable) {
            return [];
        }

        $data = $this->request($endpoint, $params);

        $cache->save($cacheKey, $data, $data === [] ? NEXORA_CACHE_TTL_ERROR : $this->ttl);

        return $data;
    }

    /**
     * Ham HTTP isteği.
     */
    protected function request(string $endpoint, array $params = []): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'X-Api-Key: ' . $this->apiKey,
            ],
        ]);

        $response = curl_exec($curl);
        $status   = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error    = curl_error($curl);
        curl_close($curl);

        if ($response === FALSE || $status !== 200) {
            if ($status === 0) {
                self::$unavailable = TRUE;
            }

            log_message('error', 'Nexora API hatasi: {url} (HTTP {status}) {error}', [
                'url'    => $url,
                'status' => $status,
                'error'  => $error,
            ]);

            return [];
        }

        $decoded = json_decode($response, TRUE);

        if (!is_array($decoded) || empty($decoded['success']) || !isset($decoded['data']) || !is_array($decoded['data'])) {
            log_message('error', 'Nexora API beklenmeyen yanit: {url}', ['url' => $url]);

            return [];
        }

        return $decoded['data'];
    }
}
