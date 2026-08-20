<?php
namespace App\Controllers\Frontend\Api\Mobile;

use App\Controllers\Frontend\BaseController;

/**
 * Mobil API - Egitim Kurumlari
 *
 * Genclik sitesine ozgu uctur. Ana Kucagi, Kasif Cocuk, SEDA ve Bilim
 * Merkezi bilgilerini dondurur. Tanimlar Constants.php icindeki
 * EDUCATION_INSTITUTIONS sabitinde, metinler dil dosyalarindadir.
 */
class EducationInstitutions extends BaseController
{
    public function index()
    {
        $array = [];

        foreach (EDUCATION_INSTITUTIONS as $item) {
            $key = $item['key'];

            $array[] = [
                'key'         => $key,
                'name'        => lang('WebIndex.education.items.' . $key . '.name'),
                'subtitle'    => lang('WebIndex.education.items.' . $key . '.subtitle'),
                'description' => lang('WebIndex.education.items.' . $key . '.description'),
                'image'       => webp_url(FILE_PATH_ASSETS . '/' . FILE_PATH_IMAGES . '/education/' . $item['image']),
                'url'         => $item['url'],
            ];
        }

        return json($array);
    }
}
