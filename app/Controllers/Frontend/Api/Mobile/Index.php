<?php
namespace App\Controllers\Frontend\Api\Mobile;

use App\Controllers\Frontend\BaseController;

/**
 * Mobil API - Uc Listesi
 *
 * Kimlik dogrulamasi gerektirmeyen tanitim sayfasi. JSON dondurur;
 * mobil istemciler uc listesini programatik olarak okuyabilir.
 */
class Index extends BaseController
{
    public function index()
    {
        $kok = rtrim(base_url('api/mobile'), '/');

        $uclar = [
            ['yol' => 'authenticate',                  'yontem' => 'POST', 'aciklama' => 'Token alir. Parametreler: username, password'],
            ['yol' => 'menu',                          'yontem' => 'GET',  'aciklama' => 'Menu. parent_id 0 = ana menu, location 1 = ust, 2 = alt menu'],
            ['yol' => 'banner',                        'yontem' => 'GET',  'aciklama' => 'Banner gorselleri'],
            ['yol' => 'municipal-councils',            'yontem' => 'GET',  'aciklama' => 'Belediye encumenleri'],
            ['yol' => 'council-members',               'yontem' => 'GET',  'aciklama' => 'Meclis uyeleri'],
            ['yol' => 'directorates',                  'yontem' => 'GET',  'aciklama' => 'Mudurlukler'],
            ['yol' => 'vice-presidents',               'yontem' => 'GET',  'aciklama' => 'Baskan yardimcilari'],
            ['yol' => 'services',                      'yontem' => 'GET',  'aciklama' => 'Hizmetler'],
            ['yol' => 'projects',                      'yontem' => 'GET',  'aciklama' => 'Projeler'],
            ['yol' => 'announcements',                 'yontem' => 'GET',  'aciklama' => 'Duyurular'],
            ['yol' => 'news',                          'yontem' => 'GET',  'aciklama' => 'Haberler'],
            ['yol' => 'events',                        'yontem' => 'GET',  'aciklama' => 'Etkinlikler (Nexora senkronu, gecmis haric)'],
            ['yol' => 'referances',                    'yontem' => 'GET',  'aciklama' => 'Referanslar'],
            ['yol' => 'contact',                       'yontem' => 'GET',  'aciklama' => 'Iletisim bilgileri'],
            ['yol' => 'president-general-information',  'yontem' => 'GET',  'aciklama' => 'Baskan genel bilgileri'],
            ['yol' => 'president-contents',            'yontem' => 'GET',  'aciklama' => 'Baskan icerikleri (ozgecmis, mesaj)'],
            ['yol' => 'president-gallery',             'yontem' => 'GET',  'aciklama' => 'Baskan galerisi'],
            ['yol' => 'push-notifications',            'yontem' => 'POST', 'aciklama' => 'Bildirim tokeni kaydeder'],
            ['yol' => 'sport-branches',                'yontem' => 'GET',  'aciklama' => 'Spor branslari (genclik sitesine ozgu)'],
            ['yol' => 'education-institutions',        'yontem' => 'GET',  'aciklama' => 'Egitim kurumlari (genclik sitesine ozgu)'],
        ];

        foreach ($uclar as &$uc) {
            $uc['url'] = $kok . '/' . $uc['yol'];
        }
        unset($uc);

        return json([
            'servis'   => 'Sultangazi Genclik Mobil API',
            'surum'    => 'v1',
            'kimlik'   => 'authenticate disindaki tum uclar Authorization: Bearer <token> basligi ister.',
            'uc_sayisi' => count($uclar),
            'uclar'    => $uclar,
        ]);
    }
}
