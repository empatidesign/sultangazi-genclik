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
