<?php

if (!function_exists('sultangazi_url')) {

    /**
     * Base URL helper
     * 
     * @param string $path  Bağlantıya eklenecek yol
     * @param bool $secondary  True ise ikinci domain kullan
     * @return string
     */
    function sultangazi_url(string $path = '', bool $secondary = false): string
    {
        $secondDomain = SULTANGAZI_URL;
        return rtrim($secondDomain, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('api_image_url')) {

    /**
     * Mobil API gorsel adresi.
     *
     * Dosya yerelde varsa site adresini, yoksa ana site (sultangazi.bel.tr)
     * adresini dondurur. Bazi iceriklerin gorselleri yalnizca ana sitede
     * bulundugu icin gereklidir.
     *
     * @param string|null $path  uploads altindaki klasor (ör. FILE_PATH_COUNCIL_MEMBERS)
     * @param string|null $name  dosya adi
     * @return string|null
     */
    function api_image_url(?string $path, ?string $name): ?string
    {
        if (!isNotNull($name) || !isNotNull($path)) {
            return NULL;
        }

        // Zaten tam adres verilmisse dokunma
        if (str_starts_with($name, 'http://') || str_starts_with($name, 'https://')) {
            return $name;
        }

        $rel = rtrim($path, '/') . '/' . ltrim($name, '/');

        return is_file(FCPATH . $rel) ? base_url($rel) : sultangazi_url($rel);
    }
}

if (!function_exists('webp_url')) {

    /**
     * Gorsel adresi: varsa .webp surumunu dondurur.
     *
     * Sablonlarda base_url() ile dogrudan basilan gorseller webp
     * donusumunden yararlanamiyordu. Bu yardimci ayni dizinde .webp
     * dosyasi varsa onu, yoksa orijinali dondurur.
     *
     * Kullanim: {{ webp_url(FILE_PATH_MAIN ~ design.logo) }}
     *
     * @param string|null $path uploads/... seklinde goreli yol
     * @return string
     */
    function webp_url(?string $path): string
    {
        if (!isNotNull($path)) {
            return base_url();
        }

        $path = ltrim((string) $path, '/');
        $uzanti = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        // Animasyonlu gif ve vektorel dosyalar donusturulmez
        if (in_array($uzanti, ['gif', 'svg', 'webp'], TRUE)) {
            return base_url($path);
        }

        $dizin = pathinfo($path, PATHINFO_DIRNAME);
        $webp  = ($dizin && $dizin !== '.' ? $dizin . '/' : '') . pathinfo($path, PATHINFO_FILENAME) . '.webp';

        return is_file(FCPATH . $webp) ? base_url($webp) : base_url($path);
    }
}
