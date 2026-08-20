<?php

namespace App\Libraries;

use Config\Services;

/**
 * Spor Akademisi (Nexorada) Genel API İstemcisi
 * -------------------------------------------------------------
 * sporakademi.sultangazi.bel.tr üzerindeki genel uçtan spor branşlarını
 * okur ve branşlar için akademideki detay adresini üretir.
 *
 * Not: Etkinlik ve hizmet tesisi verileri bu servisten DEĞİL,
 * Nexora genel katalog servisinden gelir (bkz. NexoraApi).
 *
 * Tasarım ilkeleri:
 *  - Sonuçlar önbelleğe alınır; her sayfa isteğinde dış servise gidilmez.
 *  - Servis erişilemezse sayfa çökmez, boş dizi döner (çağıran taraf
 *    kendi yerel verisine düşebilir).
 *  - Görsel yolları mutlak adrese çevrilir.
 */
class SportAcademyApi
{
    /** Genel API kök adresi */
    protected string $baseUrl;

    /** Görsellerin ve site içi bağlantıların kök adresi */
    protected string $siteUrl;

    /** Önbellek süresi (saniye) */
    protected int $ttl;

    /** İstek zaman aşımı (saniye) */
    protected int $timeout;

    /**
     * Servis bu istek sırasında erişilemez bulunduysa TRUE olur.
     * Aynı sayfa yüklemesinde diğer uç noktalar için tekrar beklenmez.
     */
    protected static bool $unavailable = FALSE;

    public function __construct()
    {
        $this->siteUrl = rtrim(SPORT_ACADEMY_URL, '/');
        $this->baseUrl = $this->siteUrl . SPORT_ACADEMY_API_PATH;
        $this->ttl     = SPORT_ACADEMY_CACHE_TTL;
        $this->timeout = SPORT_ACADEMY_TIMEOUT;
    }

    /**
     * Spor branşları.
     */
    public function branches(): array
    {
        return $this->fetch('branches');
    }

    /**
     * Branş slug'ı için akademideki detay adresi.
     * Slug servis tarafında yoksa branşlar listesine yönlendirir.
     */
    public function branchUrl(?string $slug = NULL): string
    {
        if ($slug !== NULL && $slug !== '' && in_array($slug, $this->branchSlugs(), TRUE)) {
            return $this->siteUrl . '/' . SPORT_ACADEMY_PATH_BRANCHES . '/' . $slug;
        }

        return $this->siteUrl . '/' . SPORT_ACADEMY_PATH_BRANCHES;
    }

    /**
     * Servisteki branş slug listesi (önbellekli).
     */
    public function branchSlugs(): array
    {
        $slugs = [];
        foreach ($this->branches() as $branch) {
            if (isset($branch['slug'])) {
                $slugs[] = $branch['slug'];
            }
        }

        return $slugs;
    }

    /**
     * Uç noktayı çağırır, sonucu önbelleğe alır.
     * Hata durumunda boş dizi döner; sayfa akışı bozulmaz.
     */
    protected function fetch(string $endpoint): array
    {
        $cache    = Services::cache();
        $cacheKey = 'sport_academy_' . str_replace('/', '_', $endpoint);

        $cached = $cache->get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        // Devre kesici: servis bu istek sırasında yanıt vermediyse tekrar
        // beklemeden boş dön. Aksi halde her uç nokta için zaman aşımı
        // süresi kadar beklenir ve sayfa gereksiz yere yavaşlar.
        if (self::$unavailable) {
            return [];
        }

        $data = $this->request($endpoint);

        // Yalnızca başarılı yanıt önbelleğe alınır; hatalı yanıt için kısa süre
        // beklenir ki servis düzelince site hızla toparlansın.
        $cache->save($cacheKey, $data, $data === [] ? SPORT_ACADEMY_CACHE_TTL_ERROR : $this->ttl);

        return $data;
    }

    /**
     * Ham HTTP isteği.
     */
    protected function request(string $endpoint): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);

        $response = curl_exec($curl);
        $status   = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error    = curl_error($curl);
        curl_close($curl);

        if ($response === FALSE || $status !== 200) {
            // Bağlantı kurulamadıysa servis tamamen kapalı sayılır; bu istek
            // boyunca diğer uç noktalar denenmez.
            if ($status === 0) {
                self::$unavailable = TRUE;
            }

            log_message('error', 'Spor Akademisi API hatasi: {url} (HTTP {status}) {error}', [
                'url'    => $url,
                'status' => $status,
                'error'  => $error,
            ]);

            return [];
        }

        $decoded = json_decode($response, TRUE);

        if (!is_array($decoded) || empty($decoded['success']) || !isset($decoded['data']) || !is_array($decoded['data'])) {
            log_message('error', 'Spor Akademisi API beklenmeyen yanit: {url}', ['url' => $url]);

            return [];
        }

        return $decoded['data'];
    }
}
