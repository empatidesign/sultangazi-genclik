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
