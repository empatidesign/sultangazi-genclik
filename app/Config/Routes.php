<?php
namespace Config;

// Model
use App\Models\Frontend\RoutingModel;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace(APP_NAMESPACE . '\Controllers');
$routes->setDefaultController('Index');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(FALSE);
$routes->set404Override(APP_NAMESPACE . '\Controllers\Frontend\NotFound::index');
$routes->setAutoRoute(FALSE);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.

// Türkçe karakterleri normalize etmek için bir fonksiyon
function normalizeText($text)
{
  $search = ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç'];
  $replace = ['i', 'g', 'u', 's', 'o', 'c', 'i', 'g', 'u', 's', 'o', 'c'];
  return strtolower(str_replace($search, $replace, $text));
}

// Birden fazla kelimeyle arama yapma
function filterResults($results, $value)
{
  $filteredResults = [];
  $value = normalizeText($value); // Arama terimini normalize et
  $searchWords = explode(' ', $value); // Arama terimini kelimelere ayır

  foreach ($results as $result) {
    $resultWords = explode(' ', normalizeText($result['name'])); // Sonuç kelimelerini normalize et

    // Her bir arama kelimesinin en az bir sonuç kelimesinde bulunup bulunmadığını kontrol et
    $matchFound = false;
    foreach ($searchWords as $searchWord) {
      foreach ($resultWords as $resultWord) {
        if (strpos($resultWord, $searchWord) !== false) {
          $matchFound = true;
          break 2; // Bir eşleşme bulduğumuzda dış döngüye geç
        }
      }
    }

    if ($matchFound) {
      $filteredResults[] = $result;
    }
  }

  return $filteredResults;
}

// Frontend
$routes->group('', ['namespace' => APP_NAMESPACE . '\Controllers\Frontend'], function ($routes) {
  // Library
  helper('request');
  $segment = \Config\Services::request();

  /*****************************************************/

  // Mobil API (/api/mobile)
  // Ana site yapisindan uyarlandi. authenticate disindaki uclar Bearer token
  // ister; 'mobileapiauth' filtresi JSON 401 doner (adminauth gibi giris
  // sayfasina yonlendirmez).
  $routes->group('api', function ($routes) {
    $routes->group('mobile', function ($routes) {
      $routes->get('/', 'Api\Mobile\Index::index');
      $routes->post('authenticate', 'Api\Mobile\Authenticate::index');

      $routes->group('', ['filter' => 'mobileapiauth'], function ($routes) {
        $routes->get('menu', 'Api\Mobile\Menu::index');
        $routes->get('banner', 'Api\Mobile\Banner::index');
        $routes->get('municipal-councils', 'Api\Mobile\MunicipalCouncils::index');
        $routes->get('council-members', 'Api\Mobile\CouncilMembers::index');
        $routes->get('directorates', 'Api\Mobile\Directorates::index');
        $routes->get('vice-presidents', 'Api\Mobile\VicePresidents::index');
        $routes->get('services', 'Api\Mobile\Services::index');
        $routes->get('projects', 'Api\Mobile\Projects::index');
        $routes->get('announcements', 'Api\Mobile\Announcements::index');
        $routes->get('news', 'Api\Mobile\News::index');
        $routes->get('events', 'Api\Mobile\Events::index');
        $routes->get('referances', 'Api\Mobile\Referances::index');
        $routes->get('contact', 'Api\Mobile\ContactInformation::index');
        $routes->get('president-general-information', 'Api\Mobile\PresidentGeneralInformation::index');
        $routes->get('president-contents', 'Api\Mobile\PresidentContents::index');
        $routes->get('president-gallery', 'Api\Mobile\PresidentGallery::index');
        $routes->post('push-notifications', 'Api\Mobile\PushNotifications::index');

        // Genclik sitesine ozgu uclar
        $routes->get('sport-branches', 'Api\Mobile\SportBranches::index');
        $routes->get('education-institutions', 'Api\Mobile\EducationInstitutions::index');
      });
    });
  });

  /*****************************************************/

  $routes->post('wapi/search', function () {
    $request = \Config\Services::request();
    $db = \Config\Database::connect();

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $value = $data['value'];
    $results = [];

    $lang_id = 1;

    // Türkçe karakterleri normalize eden fonksiyon
    function normalize($string)
    {
      $turkish = ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'İ', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç'];
      $english = ['i', 'g', 'u', 's', 'o', 'c', 'I', 'G', 'U', 'S', 'O', 'C'];
      return str_replace($turkish, $english, $string);
    }

    ## Menü İçince Arama
    $menuSorgu = $db->query("SELECT * FROM menus_lang WHERE lang_id = $lang_id AND menu_name LIKE '%$value%' ORDER BY menu_id DESC LIMIT 10;");
    $menus = $menuSorgu->getResult();
    foreach ($menus as $menu) {
      $results[] = [
        'name' => $menu->menu_name,
        'slug' => $menu->menu_link
      ];
    }



    // Giriş değerini normalize ediyoruz
    $normalizedValue = normalize($value);

    // Kelimeleri ayırıyoruz
    $keywords = explode(' ', $normalizedValue);

    // LIKE koşullarını oluşturuyoruz
    $searchCondition = [];
    foreach ($keywords as $keyword) {
      $searchCondition[] = "project_name LIKE '% $keyword %'";
      $searchCondition[] = "project_name LIKE '$keyword %'";
      $searchCondition[] = "project_name LIKE '% $keyword'";
      $searchCondition[] = "project_name LIKE '$keyword'";
    }
    $searchCondition = implode(' OR ', $searchCondition);




    // Projeler
    $query = $db->query("SELECT project_id, project_name as name, project_slug as slug FROM projects_lang WHERE lang_id = $lang_id AND ($searchCondition) ORDER BY project_id DESC LIMIT 10");
    $projeler = $query->getResult();

    foreach ($projeler as $proje) {
      $results[] = [
        'name' => $proje->name,
        'slug' => 'projects/detail/' . $proje->slug . '/' . $proje->project_id,
      ];
    }

    // Haberler için aynı şekilde
    $searchCondition = [];
    foreach ($keywords as $keyword) {
      $searchCondition[] = "news_name LIKE '% $keyword %'";
      $searchCondition[] = "news_name LIKE '$keyword %'";
      $searchCondition[] = "news_name LIKE '% $keyword'";
      $searchCondition[] = "news_name LIKE '$keyword'";
    }
    $searchCondition = implode(' OR ', $searchCondition);

    $query = $db->query("SELECT news_id, news_name as name, news_slug as slug FROM news_lang WHERE lang_id = $lang_id AND ($searchCondition) ORDER BY news_id DESC LIMIT 10");
    $haberler = $query->getResult();

    foreach ($haberler as $haber) {
      $results[] = [
        'name' => $haber->name,
        'slug' => 'news/detail/' . $haber->slug . '/' . $haber->news_id,
      ];
    }

    // Müdürlükler
    $searchCondition = [];
    foreach ($keywords as $keyword) {
      $searchCondition[] = "directorates_name LIKE '% $keyword %'";
      $searchCondition[] = "directorates_name LIKE '$keyword %'";
      $searchCondition[] = "directorates_name LIKE '% $keyword'";
      $searchCondition[] = "directorates_name LIKE '$keyword'";
    }
    $searchCondition = implode(' OR ', $searchCondition);

    $query = $db->query("SELECT directorates_id, directorates_name as name, directorates_slug as slug FROM directorates_lang WHERE lang_id = $lang_id AND ($searchCondition)");
    $hizmetler = $query->getResult();

    foreach ($hizmetler as $hizmet) {
      $results[] = [
        'name' => $hizmet->name,
        'slug' => '/directorates/detail/' . $hizmet->slug . '/' . $hizmet->directorates_id . '',
      ];
    }





    // Hizmetler için aynı şekilde
    $searchCondition = [];
    foreach ($keywords as $keyword) {
      $searchCondition[] = "service_name LIKE '% $keyword %'";
      $searchCondition[] = "service_name LIKE '$keyword %'";
      $searchCondition[] = "service_name LIKE '% $keyword'";
      $searchCondition[] = "service_name LIKE '$keyword'";
    }
    $searchCondition = implode(' OR ', $searchCondition);

    $query = $db->query("SELECT service_id, service_name as name, service_slug as slug FROM services_lang WHERE lang_id = $lang_id AND ($searchCondition)");
    $hizmetler = $query->getResult();

    foreach ($hizmetler as $hizmet) {
      $results[] = [
        'name' => $hizmet->name,
        'slug' => 'services/' . $hizmet->slug . '/' . $hizmet->service_id,
      ];
    }

    // Şehir Rehberi için aynı şekilde
    $searchCondition = [];
    foreach ($keywords as $keyword) {
      $searchCondition[] = "city_guide_content_name LIKE '% $keyword %'   ";
      $searchCondition[] = "city_guide_content_name LIKE '$keyword %'  ";
      $searchCondition[] = "city_guide_content_name LIKE '% $keyword'   ";
      $searchCondition[] = "city_guide_content_name LIKE '$keyword'   ";
    }
    $searchCondition = implode(' OR ', $searchCondition);

    $query = $db->query("SELECT city_guide_content_id, city_guide_content_name as name, city_guide_content_slug as slug FROM city_guide_contents_lang WHERE lang_id = $lang_id AND ($searchCondition) ORDER BY city_guide_content_id DESC LIMIT 10");
    $sehirRehberi = $query->getResult();

    foreach ($sehirRehberi as $sehir) {
      $results[] = [
        'name' => $sehir->name,
        'slug' => 'sultangazi/city-guides/detail/' . $sehir->slug . '/' . $sehir->city_guide_content_id,
      ];
    }

    // return data in json format
    return $this->response->setJSON([
      'results' => $results,
    ]);
  });
  // Model
  $this->RoutingModel = new RoutingModel();

  /*****************************************************/

  if ($segment->getUri()->getSegment(1) != BACKEND_URL) {

    // Index
    $routes->get('/', 'Index::index');

    // Callback
    $routes->post('callback', 'Callback::index', ['priority' => 2]);
    $routes->post('/{locale}/callback', 'Callback::index', ['priority' => 2]);

    // Sitemap
    $routes->get('sitemap\.xml', 'Sitemap::index');
    /*****************************************************/

    // Languages
    $result = $this->RoutingModel->languagesModel();
    if (isNotNull($result) && is_array($result)) {
      if (in_array($segment->getUri()->getSegment(1), $result)) {
        $routes->get('/{locale}', 'Index::index');
      }
    }

    /*****************************************************/

    // Constracts
    if (isNotNull($segment->getUri()->getSegment(1))) {
      // Default Lang
      if ($segment->getUri()->getSegment(1) == WEB_URL_CONTRACTS) {
        $result = $this->RoutingModel->contractsModel(NULL, $segment->getUri()->getSegment(2), $segment->getUri()->getSegment(3));
        if (isNotNull($result)) {
          $routes->get(WEB_URL_CONTRACTS . '/(:any)/(:num)', 'Contents\Contracts::index/$1/$2');
        }
      }

      // Other Lang
      if ($segment->getUri()->getSegment(2) == WEB_URL_CONTRACTS) {
        $result = $this->RoutingModel->contractsModel($segment->getUri()->getSegment(1), $segment->getUri()->getSegment(3), $segment->getUri()->getSegment(4));
        if (isNotNull($result)) {
          $routes->get('/{locale}/' . WEB_URL_CONTRACTS . '/(:any)/(:num)', 'Contents\Contracts::index/$1/$2');
        }
      }
    }

    /*****************************************************/

    // President Contents
    if (isNotNull($segment->getUri()->getSegment(1))) {
      // Default Lang
      if ($segment->getUri()->getSegment(1) == WEB_URL_PRESIDENT) {
        $result = $this->RoutingModel->presidentContentModel(NULL, $segment->getUri()->getSegment(2), $segment->getUri()->getSegment(3));
        if (isNotNull($result)) {
          $routes->get(WEB_URL_PRESIDENT . '/(:any)/(:num)', 'President\PresidentContents::index/$1/$2');
        }
      }

      // Other Lang
      if ($segment->getUri()->getSegment(2) == WEB_URL_PRESIDENT) {
        $result = $this->RoutingModel->presidentContentModel($segment->getUri()->getSegment(1), $segment->getUri()->getSegment(3), $segment->getUri()->getSegment(4));
        if (isNotNull($result)) {
          $routes->get('/{locale}/' . WEB_URL_PRESIDENT . '/(:any)/(:num)', 'President\PresidentContents::index/$1/$2');
        }
      }
    }

    /*****************************************************/

    // Project Detail
    if (isNotNull($segment->getUri()->getSegment(1))) {
      // Default Lang
      if ($segment->getUri()->getSegment(1) == WEB_URL_PROJECTS && $segment->getUri()->getSegment(2) == WEB_URL_PROJECTS_DETAIL_PARAMETER) {
        $result = $this->RoutingModel->projectDetailModel(NULL, $segment->getUri()->getSegment(3), $segment->getUri()->getSegment(4));
        if (isNotNull($result)) {
          $routes->get(WEB_URL_PROJECTS_DETAIL . '/(:any)/(:num)', 'Projects\Projects::detail/$1/$2');
        }
      }

      // Other Lang
      if ($segment->getUri()->getSegment(2) == WEB_URL_PROJECTS && $segment->getUri()->getSegment(3) == WEB_URL_PROJECTS_DETAIL_PARAMETER) {
        $result = $this->RoutingModel->projectDetailModel($segment->getUri()->getSegment(1), $segment->getUri()->getSegment(4), $segment->getUri()->getSegment(5));
        if (isNotNull($result)) {
          $routes->get('/{locale}/' . WEB_URL_PROJECTS_DETAIL . '/(:any)/(:num)', 'Projects\Projects::detail/$1/$2');
        }
      }
    }

    /*****************************************************/

    // Services Detail
    if (isNotNull($segment->getUri()->getSegment(1))) {
      // Default Lang
      if ($segment->getUri()->getSegment(1) == WEB_URL_SERVICES && isNotNull($segment->getUri()->getSegment(2)) && isNotNull($segment->getUri()->getSegment(3))) {
        $result = $this->RoutingModel->serviceDetailModel(NULL, $segment->getUri()->getSegment(3), $segment->getUri()->getSegment(4));
        if (isNotNull($result)) {
          $routes->get(WEB_URL_SERVICES . '/(:any)/(:any)/(:num)', 'Services\Services::detail/$1/$2/$3');
        }
      }

      // Other Lang
      if ($segment->getUri()->getSegment(2) == WEB_URL_SERVICES && isNotNull($segment->getUri()->getSegment(3))) {
        $result = $this->RoutingModel->serviceDetailModel($segment->getUri()->getSegment(1), $segment->getUri()->getSegment(3), $segment->getUri()->getSegment(4));
        if (isNotNull($result)) {
          $routes->get('/{locale}/' . WEB_URL_SERVICES . '/(:any)/(:any)/(:num)', 'Services\Services::detail/$1/$2/$3');
        }
      }
    }

    /*****************************************************/

    // Etkinlikler (liste + detay)
    // Icerik Nexora'dan cron ile yerel nexora_events tablosuna aktarilir.
    // Detay kontrolcusu gecmis/bulunamayan etkinlikte 404'e yonlendirir,
    // bu yuzden rota kosulsuz tanimlanir.
    $routes->get(WEB_URL_EVENTS, 'Events\Events::index');
    $routes->get(WEB_URL_EVENTS . '/(:any)/(:num)', 'Events\Events::detail/$1/$2');
    $routes->get('/{locale}/' . WEB_URL_EVENTS, 'Events\Events::index');
    $routes->get('/{locale}/' . WEB_URL_EVENTS . '/(:any)/(:num)', 'Events\Events::detail/$1/$2');

    /*****************************************************/

    // President Gallery
    $routes->get(WEB_URL_PRESIDENT . '/' . WEB_URL_PRESIDENT_GALLERY, 'President\PresidentGallery::index');
    $routes->get('/{locale}/' . WEB_URL_PRESIDENT . '/' . WEB_URL_PRESIDENT_GALLERY, 'President\PresidentGallery::index');

    /*****************************************************/

    // President Contact
    $routes->get(WEB_URL_PRESIDENT . '/' . WEB_URL_PRESIDENT_CONTACT, 'President\PresidentContact::index');
    $routes->get('/{locale}/' . WEB_URL_PRESIDENT . '/' . WEB_URL_PRESIDENT_CONTACT, 'President\PresidentContact::index');
    // $routes->post(WEB_URL_PRESIDENT . '/' . WEB_URL_PRESIDENT_CONTACT_SEND, 'President\PresidentContact::send');
    // $routes->post('/{locale}/' . WEB_URL_PRESIDENT . '/' . WEB_URL_PRESIDENT_CONTACT_SEND, 'President\PresidentContact::send');

    /*****************************************************/

    // News
    $routes->get(WEB_URL_NEWS, 'News\News::index');
    $routes->get('/{locale}/' . WEB_URL_NEWS, 'News\News::index');

    $routes->get(WEB_URL_NEWS_DETAIL . '/(:any)/(:num)', 'News\News::detail/$1/$2');
    $routes->get('/{locale}/' . WEB_URL_NEWS_DETAIL . '/(:any)/(:num)', 'News\News::detail/$1/$2');

    /*****************************************************/

    // Projects
    $routes->get(WEB_URL_PROJECTS, 'Projects\Projects::index');
    $routes->get('/{locale}/' . WEB_URL_PROJECTS, 'Projects\Projects::index');
    $routes->get(WEB_URL_PROJECTS . '/(:any)/(:num)', 'Projects\Projects::detail/$1/$2');
    $routes->get('/{locale}/' . WEB_URL_PROJECTS . '/(:any)/(:num)', 'Projects\Projects::detail/$1/$2');

    /*****************************************************/

    // Services;
    $routes->get(WEB_URL_SERVICES, 'Services\Services::index');
    $routes->get(WEB_URL_SERVICES . '/(.*)', 'Services\Services::detail/$1');
    $routes->get(WEB_URL_SERVICES . '/(.*)/(.*)', 'Services\Services::detail/$1/$2');
    $routes->get('/{locale}/' . WEB_URL_SERVICES . '/(:any)', 'Services\Services::index/$1');
    $routes->get('/{locale}/' . WEB_URL_SERVICES, 'Services\Services::index');

    /*****************************************************/

    // Contact
    $routes->get(WEB_URL_CONTACT, 'Contents\Contact::index');
    $routes->get('/{locale}/' . WEB_URL_CONTACT, 'Contents\Contact::index');
    // $routes->post(WEB_URL_CONTACT_SEND, 'Contents\Contact::send');
    // $routes->post('/{locale}/' . WEB_URL_CONTACT_SEND, 'Contents\Contact::send');
  }

});

// Backend
$routes->group(BACKEND_URL, ['namespace' => APP_NAMESPACE . '\Controllers\Backend'], function ($routes) {

  // Login
  $routes->get('/', 'Login::index', ['filter' => 'adminnoauth']);
  $routes->get(ADMIN_URL_LOGIN, 'Login::index', ['filter' => 'adminnoauth']);
  $routes->post(ADMIN_URL_LOGIN_AUTH, 'Login::loginAuth', ['filter' => 'adminnoauth']);
  $routes->get(ADMIN_URL_LOGOUT, 'Login::logout');

  // Language
  $routes->get('lang/{locale}', 'Language::index', ['filter' => 'adminauth']);

  // Dashboard
  $routes->group(ADMIN_URL_DASHBOARD, function ($routes) {
    $routes->get('/', 'Dashboard::index', ['filter' => 'adminauth']);
  });

  // Callback
  $routes->setPrioritize();
  $routes->post('callback', 'Callback::index', ['filter' => 'adminauth', 'priority' => 1]);

  // Settings
  $routes->group(ADMIN_URL_SETTINGS, function ($routes) {

    // General Settings
    $routes->group(ADMIN_URL_GENERAL_SETTINGS, function ($routes) {
      $routes->get('/', 'Settings\GeneralSettings::index', ['filter' => 'adminauth']);
      $routes->post('update', 'Settings\GeneralSettings::update', ['filter' => 'adminauth']);
    });

    // Social Media Settings
    $routes->group(ADMIN_URL_SOCIAL_MEDIA_SETTINGS, function ($routes) {
      $routes->get('/', 'Settings\SocialMediaSettings::index', ['filter' => 'adminauth']);
      $routes->post('update', 'Settings\SocialMediaSettings::update', ['filter' => 'adminauth']);
    });

    // Tracking Codes
    // Kontrolcu, gorunum ve tablo mevcuttu ancak rota tanimli degildi;
    // panel menusundeki baglanti 404 veriyordu.
    $routes->group(ADMIN_URL_TRACKING_CODES, function ($routes) {
      $routes->get('/', 'Settings\TrackingCodes::index', ['filter' => 'adminauth']);
      $routes->post('update', 'Settings\TrackingCodes::update', ['filter' => 'adminauth']);
    });

    // Maintenance Mode
    $routes->group(ADMIN_URL_MAINTENANCE_MODE, function ($routes) {
      $routes->get('/', 'Settings\MaintenanceMode::index', ['filter' => 'adminauth']);
      $routes->post('update', 'Settings\MaintenanceMode::update', ['filter' => 'adminauth']);
      $routes->post('database-repair', 'Settings\MaintenanceMode::databaseRepair', ['filter' => 'adminauth']);
      $routes->post('cache-clearing', 'Settings\MaintenanceMode::cacheClearing', ['filter' => 'adminauth']);
      $routes->post('cart-clearing', 'Settings\MaintenanceMode::cartClearing', ['filter' => 'adminauth']);
      $routes->post('guest-user-clearing', 'Settings\MaintenanceMode::guestUserClearing', ['filter' => 'adminauth']);
    });

    // E-Mail Settings
    $routes->group(ADMIN_URL_EMAIL_SETTINGS, function ($routes) {
      $routes->get('/', 'Settings\EmailSettings::index', ['filter' => 'adminauth']);
      $routes->post('update', 'Settings\EmailSettings::update', ['filter' => 'adminauth']);
    });

    // E-Mail Templates
    $routes->group(ADMIN_URL_EMAIL_TEMPLATES, function ($routes) {
      $routes->get('/', 'Settings\EmailTemplates::index', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Settings\EmailTemplates::update/$1', ['filter' => 'adminauth']);
    });

    // Language Management
    $routes->group(ADMIN_URL_LANGUAGE_MANAGEMENT, function ($routes) {
      $routes->get('/', 'Settings\LanguageManagement::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Settings\LanguageManagement::datatable', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Settings\LanguageManagement::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Settings\LanguageManagement::update/$1', ['filter' => 'adminauth']);
    });

    // Manager Accounts
    $routes->group(ADMIN_URL_MANAGER_ACCOUNTS, function ($routes) {
      $routes->get('/', 'Settings\ManagerAccounts::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Settings\ManagerAccounts::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Settings\ManagerAccounts::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Settings\ManagerAccounts::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Settings\ManagerAccounts::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Settings\ManagerAccounts::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Settings\ManagerAccounts::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'Settings\ManagerAccounts::removeImage/$1', ['filter' => 'adminauth']);
      $routes->get('change-password/(:num)', 'Settings\ManagerAccounts::changePassword/$1', ['filter' => 'adminauth']);
      $routes->post('change-password-update/(:num)', 'Settings\ManagerAccounts::changePasswordUpdate/$1', ['filter' => 'adminauth']);
    });

  });

  /*****************************************************/


  /*****************************************************/

  // Contents
  $routes->group(ADMIN_URL_CONTENTS, function ($routes) {

    // Contracts
    // Kontrolcu, gorunum ve tablo mevcuttu ancak rota tanimli degildi.
    $routes->group(ADMIN_URL_CONTRACTS, function ($routes) {
      $routes->get('/', 'Contents\Contracts::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Contents\Contracts::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Contents\Contracts::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Contents\Contracts::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Contents\Contracts::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Contents\Contracts::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Contents\Contracts::delete/$1', ['filter' => 'adminauth']);
    });

    // Referances
    $routes->group(ADMIN_URL_REFERANCES, function ($routes) {
      $routes->get('/', 'Contents\Referances::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Contents\Referances::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Contents\Referances::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Contents\Referances::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Contents\Referances::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Contents\Referances::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Contents\Referances::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'Contents\Referances::removeImage/$1', ['filter' => 'adminauth']);
    });

    // Statistics
    $routes->group(ADMIN_URL_STATISTIC, function ($routes) {
      $routes->get('/', 'Contents\Statistics::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Contents\Statistics::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Contents\Statistics::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Contents\Statistics::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Contents\Statistics::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Contents\Statistics::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Contents\Statistics::delete/$1', ['filter' => 'adminauth']);
    });

    // Services
    $routes->group(ADMIN_URL_SERVICES, function ($routes) {
      $routes->get('/', 'Contents\Services::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Contents\Services::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Contents\Services::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Contents\Services::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Contents\Services::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Contents\Services::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Contents\Services::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'Contents\Services::removeImage/$1', ['filter' => 'adminauth']);
    });

    // Services
    $routes->group(ADMIN_URL_SERVICE_CATEGORIES, function ($routes) {
      $routes->get('/', 'Contents\ServiceCategories::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Contents\ServiceCategories::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Contents\ServiceCategories::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Contents\ServiceCategories::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Contents\ServiceCategories::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Contents\ServiceCategories::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Contents\ServiceCategories::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'Contents\ServiceCategories::removeImage/$1', ['filter' => 'adminauth']);
    });

    // Municipal Councils
    $routes->group(ADMIN_URL_MUNICIPAL_COUNCILS, function ($routes) {
      $routes->get('/', 'Contents\MunicipalCouncils::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Contents\MunicipalCouncils::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Contents\MunicipalCouncils::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Contents\MunicipalCouncils::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Contents\MunicipalCouncils::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Contents\MunicipalCouncils::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Contents\MunicipalCouncils::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'Contents\MunicipalCouncils::removeImage/$1', ['filter' => 'adminauth']);
    });

    // Council Members
    $routes->group(ADMIN_URL_COUNCIL_MEMBERS, function ($routes) {
      $routes->get('/', 'Contents\CouncilMembers::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Contents\CouncilMembers::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Contents\CouncilMembers::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Contents\CouncilMembers::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Contents\CouncilMembers::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Contents\CouncilMembers::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Contents\CouncilMembers::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'Contents\CouncilMembers::removeImage/$1', ['filter' => 'adminauth']);
    });

    // Vice Presidents
    $routes->group(ADMIN_URL_VICE_PRESIDENTS, function ($routes) {
      $routes->get('/', 'Contents\VicePresidents::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Contents\VicePresidents::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Contents\VicePresidents::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Contents\VicePresidents::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Contents\VicePresidents::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Contents\VicePresidents::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Contents\VicePresidents::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'Contents\VicePresidents::removeImage/$1', ['filter' => 'adminauth']);
    });

    // Parliamentary Agenda
    $routes->group(ADMIN_URL_PARLIAMENTARY_AGENDA, function ($routes) {
      $routes->get('/', 'Contents\ParliamentaryAgenda::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Contents\ParliamentaryAgenda::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Contents\ParliamentaryAgenda::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Contents\ParliamentaryAgenda::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Contents\ParliamentaryAgenda::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Contents\ParliamentaryAgenda::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Contents\ParliamentaryAgenda::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-file/(:num)', 'Contents\ParliamentaryAgenda::removeFile/$1', ['filter' => 'adminauth']);
    });

    // Activity Report
    $routes->group(ADMIN_URL_ACTIVITY_REPORT, function ($routes) {
      $routes->get('/', 'Contents\ActivityReport::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Contents\ActivityReport::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Contents\ActivityReport::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Contents\ActivityReport::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Contents\ActivityReport::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Contents\ActivityReport::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Contents\ActivityReport::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-file/(:num)', 'Contents\ActivityReport::removeFile/$1', ['filter' => 'adminauth']);
    });

    // Strategic Plan and Performance
    $routes->group(ADMIN_URL_STRATEGIC_PLAN_AND_PERFORMANCA, function ($routes) {
      $routes->get('/', 'Contents\StrategicPlanAndPerformance::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Contents\StrategicPlanAndPerformance::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Contents\StrategicPlanAndPerformance::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Contents\StrategicPlanAndPerformance::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Contents\StrategicPlanAndPerformance::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Contents\StrategicPlanAndPerformance::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Contents\StrategicPlanAndPerformance::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-file/(:num)', 'Contents\StrategicPlanAndPerformance::removeFile/$1', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'Contents\StrategicPlanAndPerformance::removeImage/$1', ['filter' => 'adminauth']);
    });

    // Strategic Plan and Performance
    $routes->group(ADMIN_URL_PLAN_AND_PROGRAM, function ($routes) {
      $routes->get('/', 'Contents\PlanAndProgram::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Contents\PlanAndProgram::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Contents\PlanAndProgram::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Contents\PlanAndProgram::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Contents\PlanAndProgram::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Contents\PlanAndProgram::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Contents\PlanAndProgram::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-file/(:num)', 'Contents\PlanAndProgram::removeFile/$1', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'Contents\PlanAndProgram::removeImage/$1', ['filter' => 'adminauth']);
    });
    // Strategic Plan and Performance
    $routes->group(ADMIN_URL_PRESS_RELEASE, function ($routes) {
      $routes->get('/', 'Contents\PressRelease::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Contents\PressRelease::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Contents\PressRelease::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Contents\PressRelease::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Contents\PressRelease::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Contents\PressRelease::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Contents\PressRelease::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-file/(:num)', 'Contents\PressRelease::removeFile/$1', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'Contents\PressRelease::removeImage/$1', ['filter' => 'adminauth']);
    });
    // Strategic Plan and Performance
    $routes->group(ADMIN_URL_INTERNAL_CONTROL, function ($routes) {
      $routes->get('/', 'Contents\InternalControl::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Contents\InternalControl::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Contents\InternalControl::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Contents\InternalControl::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Contents\InternalControl::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Contents\InternalControl::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Contents\InternalControl::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-file/(:num)', 'Contents\InternalControl::removeFile/$1', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'Contents\InternalControl::removeImage/$1', ['filter' => 'adminauth']);
    });

    // Directorates
    $routes->group(ADMIN_URL_DIRECTORATES, function ($routes) {
      $routes->get('/', 'Contents\Directorates::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Contents\Directorates::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Contents\Directorates::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Contents\Directorates::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Contents\Directorates::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Contents\Directorates::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Contents\Directorates::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'Contents\Directorates::removeImage/$1', ['filter' => 'adminauth']);

      $routes->group('files', function ($routes) {
        $routes->get('datatable/(:num)', 'Contents\Directorates::filesDatatable/$1', ['filter' => 'adminauth']);
        $routes->post('insert/(:num)', 'Contents\Directorates::filesInsert/$1', ['filter' => 'adminauth']);
        $routes->post('update/(:num)', 'Contents\Directorates::filesUpdate/$1', ['filter' => 'adminauth']);
        $routes->post('delete/(:num)', 'Contents\Directorates::filesDelete/$1', ['filter' => 'adminauth']);
        $routes->post('remove-file/(:num)', 'Contents\Directorates::filesRemoveFile/$1', ['filter' => 'adminauth']);
      });
    });

    // Directorate Categories
    $routes->group(ADMIN_URL_DIRECTORATE_CATEGORIES, function ($routes) {
      $routes->get('/', 'Contents\DirectorateCategories::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Contents\DirectorateCategories::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Contents\DirectorateCategories::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Contents\DirectorateCategories::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Contents\DirectorateCategories::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Contents\DirectorateCategories::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Contents\DirectorateCategories::delete/$1', ['filter' => 'adminauth']);
    });

    // Organization Chart
    $routes->group(ADMIN_URL_ORGANIZATION_CHART, function ($routes) {
      $routes->get('/', 'Contents\OrganizationChart::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Contents\OrganizationChart::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Contents\OrganizationChart::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Contents\OrganizationChart::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Contents\OrganizationChart::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Contents\OrganizationChart::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Contents\OrganizationChart::delete/$1', ['filter' => 'adminauth']);
      $routes->get('nestable-list', 'Contents\OrganizationChart::nestableList', ['filter' => 'adminauth']);
      $routes->get('nestable-update', 'Contents\OrganizationChart::nestableUpdate', ['filter' => 'adminauth']);
    });

    // Popup Module
    $routes->group(ADMIN_URL_POPUP_MODULE, function ($routes) {
      $routes->get('/', 'Contents\PopupModule::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Contents\PopupModule::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Contents\PopupModule::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Contents\PopupModule::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Contents\PopupModule::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Contents\PopupModule::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Contents\PopupModule::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'Contents\PopupModule::removeImage/$1', ['filter' => 'adminauth']);
      $routes->post('remove-mobile-image/(:num)', 'Contents\PopupModule::removeMobileImage/$1', ['filter' => 'adminauth']);
    });

  });

  /*****************************************************/

  // Sultangazi
  $routes->group(ADMIN_URL_SULTANGAZI, function ($routes) {

    // Contents
    $routes->group(ADMIN_URL_SULTANGAZI_CONTENTS, function ($routes) {
      $routes->get('/', 'Sultangazi\Contents::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Sultangazi\Contents::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Sultangazi\Contents::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Sultangazi\Contents::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Sultangazi\Contents::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Sultangazi\Contents::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Sultangazi\Contents::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'Sultangazi\Contents::removeImage/$1', ['filter' => 'adminauth']);
    });

    // Gallery
    $routes->group(ADMIN_URL_SULTANGAZI_GALLERY, function ($routes) {
      $routes->get('/', 'Sultangazi\Gallery::index', ['filter' => 'adminauth']);
      $routes->get('add', 'Sultangazi\Gallery::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Sultangazi\Gallery::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Sultangazi\Gallery::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Sultangazi\Gallery::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Sultangazi\Gallery::delete/$1', ['filter' => 'adminauth']);
      $routes->post('sort', 'Sultangazi\Gallery::sort', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'Sultangazi\Gallery::removeImage/$1', ['filter' => 'adminauth']);
    });

    // Video Gallery
    $routes->group(ADMIN_URL_SULTANGAZI_VIDEO_GALLERY, function ($routes) {
      $routes->get('/', 'Sultangazi\VideoGallery::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Sultangazi\VideoGallery::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Sultangazi\VideoGallery::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Sultangazi\VideoGallery::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Sultangazi\VideoGallery::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Sultangazi\VideoGallery::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Sultangazi\VideoGallery::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'Sultangazi\VideoGallery::removeImage/$1', ['filter' => 'adminauth']);
    });

    // City Guide Categories
    $routes->group(ADMIN_URL_SULTANGAZI_CITY_GUIDE_CATEGORIES, function ($routes) {
      $routes->get('/', 'Sultangazi\CityGuideCategories::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Sultangazi\CityGuideCategories::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Sultangazi\CityGuideCategories::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Sultangazi\CityGuideCategories::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Sultangazi\CityGuideCategories::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Sultangazi\CityGuideCategories::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Sultangazi\CityGuideCategories::delete/$1', ['filter' => 'adminauth']);
    });

    // City Guide Contents
    $routes->group(ADMIN_URL_SULTANGAZI_CITY_GUIDE_CONTENTS, function ($routes) {
      $routes->get('/', 'Sultangazi\CityGuideContents::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Sultangazi\CityGuideContents::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Sultangazi\CityGuideContents::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Sultangazi\CityGuideContents::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Sultangazi\CityGuideContents::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Sultangazi\CityGuideContents::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Sultangazi\CityGuideContents::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'Sultangazi\CityGuideContents::removeImage/$1', ['filter' => 'adminauth']);
    });

  });

  /*****************************************************/

  // Projects
  $routes->group(ADMIN_URL_PROJECTS, function ($routes) {

    // Categories
    $routes->group(ADMIN_URL_PROJECTS_CATEGORY, function ($routes) {
      $routes->get('/', 'Projects\ProjectsCategory::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Projects\ProjectsCategory::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Projects\ProjectsCategory::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Projects\ProjectsCategory::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Projects\ProjectsCategory::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Projects\ProjectsCategory::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Projects\ProjectsCategory::delete/$1', ['filter' => 'adminauth']);
    });

    // Contents
    $routes->group(ADMIN_URL_PROJECTS_CONTENT, function ($routes) {
      $routes->get('/', 'Projects\ProjectsContent::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Projects\ProjectsContent::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Projects\ProjectsContent::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Projects\ProjectsContent::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Projects\ProjectsContent::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Projects\ProjectsContent::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Projects\ProjectsContent::delete/$1', ['filter' => 'adminauth']);

      $routes->group('images', function ($routes) {
        $routes->post('list/(:num)', 'Projects\ProjectsContent::imageList/$1', ['filter' => 'adminauth']);
        $routes->post('upload/(:num)', 'Projects\ProjectsContent::imageUpload/$1', ['filter' => 'adminauth']);
        $routes->post('update/(:num)', 'Projects\ProjectsContent::imageUpdate/$1', ['filter' => 'adminauth']);
        $routes->get('sort', 'Projects\ProjectsContent::imageSort', ['filter' => 'adminauth']);
        $routes->get('delete', 'Projects\ProjectsContent::imageDelete', ['filter' => 'adminauth']);
      });
    });

    // Status
    $routes->group(ADMIN_URL_PROJECTS_STATUS, function ($routes) {
      $routes->get('/', 'Projects\ProjectsStatus::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Projects\ProjectsStatus::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Projects\ProjectsStatus::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Projects\ProjectsStatus::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Projects\ProjectsStatus::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Projects\ProjectsStatus::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Projects\ProjectsStatus::delete/$1', ['filter' => 'adminauth']);
    });

  });

  /*****************************************************/

  // News & Announcements
  $routes->group(ADMIN_URL_NEWS, function ($routes) {

    // News
    $routes->group(ADMIN_URL_NEWS_CONTENT, function ($routes) {
      $routes->get('/', 'News\NewsContent::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'News\NewsContent::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'News\NewsContent::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'News\NewsContent::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'News\NewsContent::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'News\NewsContent::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'News\NewsContent::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'News\NewsContent::removeImage/$1', ['filter' => 'adminauth']);

      $routes->group('paragraphs', function ($routes) {
        $routes->get('datatable/(:num)', 'News\NewsContent::paragraphDatatable/$1', ['filter' => 'adminauth']);
        $routes->post('insert/(:num)', 'News\NewsContent::paragraphInsert/$1', ['filter' => 'adminauth']);
        $routes->post('update/(:num)', 'News\NewsContent::paragraphUpdate/$1', ['filter' => 'adminauth']);
        $routes->post('delete/(:num)', 'News\NewsContent::paragraphDelete/$1', ['filter' => 'adminauth']);
        $routes->post('remove-image/(:num)', 'News\NewsContent::paragraphRemoveImage/$1', ['filter' => 'adminauth']);
      });

      $routes->group('images', function ($routes) {
        $routes->post('list/(:num)', 'News\NewsContent::imageList/$1', ['filter' => 'adminauth']);
        $routes->post('upload/(:num)', 'News\NewsContent::imageUpload/$1', ['filter' => 'adminauth']);
        $routes->get('sort', 'News\NewsContent::imageSort', ['filter' => 'adminauth']);
        $routes->get('delete', 'News\NewsContent::imageDelete', ['filter' => 'adminauth']);
      });
    });

    // Announcements
    $routes->group(ADMIN_URL_ANNOUNCEMENTS, function ($routes) {
      $routes->get('/', 'News\Announcements::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'News\Announcements::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'News\Announcements::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'News\Announcements::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'News\Announcements::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'News\Announcements::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'News\Announcements::delete/$1', ['filter' => 'adminauth']);
    });

  });

  /*****************************************************/

  // Events
  $routes->group(ADMIN_URL_EVENTS, function ($routes) {

    // Categories
    $routes->group(ADMIN_URL_EVENTS_CATEGORY, function ($routes) {
      $routes->get('/', 'Events\EventsCategory::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Events\EventsCategory::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Events\EventsCategory::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Events\EventsCategory::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Events\EventsCategory::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Events\EventsCategory::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Events\EventsCategory::delete/$1', ['filter' => 'adminauth']);
    });

    // Contents
    $routes->group(ADMIN_URL_EVENTS_CONTENT, function ($routes) {
      $routes->get('/', 'Events\EventsContent::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Events\EventsContent::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Events\EventsContent::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Events\EventsContent::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Events\EventsContent::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Events\EventsContent::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Events\EventsContent::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'Events\EventsContent::removeImage/$1', ['filter' => 'adminauth']);

      $routes->group('paragraphs', function ($routes) {
        $routes->get('datatable/(:num)', 'Events\EventsContent::paragraphDatatable/$1', ['filter' => 'adminauth']);
        $routes->post('insert/(:num)', 'Events\EventsContent::paragraphInsert/$1', ['filter' => 'adminauth']);
        $routes->post('update/(:num)', 'Events\EventsContent::paragraphUpdate/$1', ['filter' => 'adminauth']);
        $routes->post('delete/(:num)', 'Events\EventsContent::paragraphDelete/$1', ['filter' => 'adminauth']);
        $routes->post('remove-image/(:num)', 'Events\EventsContent::paragraphRemoveImage/$1', ['filter' => 'adminauth']);
      });
    });

  });

  /*****************************************************/

  // Map Module
  $routes->group(ADMIN_URL_MAP_MODULE, function ($routes) {

    // Map Categories
    $routes->group(ADMIN_URL_MAP_CATEGORIES, function ($routes) {
      $routes->get('/', 'MapModule\MapCategories::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'MapModule\MapCategories::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'MapModule\MapCategories::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'MapModule\MapCategories::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'MapModule\MapCategories::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'MapModule\MapCategories::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'MapModule\MapCategories::delete/$1', ['filter' => 'adminauth']);
      $routes->post('confirmation/(:num)', 'MapModule\MapCategories::confirmation/$1', ['filter' => 'adminauth']);
    });

    // Map Locations
    $routes->group(ADMIN_URL_MAP_LOCATIONS, function ($routes) {
      $routes->get('/', 'MapModule\MapLocations::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'MapModule\MapLocations::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'MapModule\MapLocations::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'MapModule\MapLocations::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'MapModule\MapLocations::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'MapModule\MapLocations::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'MapModule\MapLocations::delete/$1', ['filter' => 'adminauth']);
    });

  });

  /*****************************************************/

  // Multimedia
  $routes->group(ADMIN_URL_MULTIMEDIA, function ($routes) {

    // Gallery Categories
    $routes->group(ADMIN_URL_MULTIMEDIA_GALLERY_CATEGORIES, function ($routes) {
      $routes->get('/', 'Multimedia\GalleryCategories::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Multimedia\GalleryCategories::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Multimedia\GalleryCategories::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Multimedia\GalleryCategories::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Multimedia\GalleryCategories::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Multimedia\GalleryCategories::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Multimedia\GalleryCategories::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'Multimedia\GalleryCategories::removeImage/$1', ['filter' => 'adminauth']);
    });

    // Gallery
    $routes->group(ADMIN_URL_MULTIMEDIA_GALLERY, function ($routes) {
      $routes->get('/', 'Multimedia\Gallery::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Multimedia\Gallery::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Multimedia\Gallery::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Multimedia\Gallery::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Multimedia\Gallery::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Multimedia\Gallery::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Multimedia\Gallery::delete/$1', ['filter' => 'adminauth']);

      $routes->group('images', function ($routes) {
        $routes->post('list/(:num)', 'Multimedia\Gallery::imageList/$1', ['filter' => 'adminauth']);
        $routes->post('upload/(:num)', 'Multimedia\Gallery::imageUpload/$1', ['filter' => 'adminauth']);
        $routes->get('sort', 'Multimedia\Gallery::imageSort', ['filter' => 'adminauth']);
        $routes->get('delete', 'Multimedia\Gallery::imageDelete', ['filter' => 'adminauth']);
      });
    });

    // Video Gallery
    $routes->group(ADMIN_URL_MULTIMEDIA_VIDEO_GALLERY, function ($routes) {
      $routes->get('/', 'Multimedia\VideoGallery::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Multimedia\VideoGallery::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Multimedia\VideoGallery::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Multimedia\VideoGallery::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Multimedia\VideoGallery::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Multimedia\VideoGallery::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Multimedia\VideoGallery::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'Multimedia\VideoGallery::removeImage/$1', ['filter' => 'adminauth']);
    });

  });

  /*****************************************************/

  // Designs
  $routes->group(ADMIN_URL_DESIGNS, function ($routes) {

    // Design Settings
    $routes->group(ADMIN_URL_DESIGN_SETTINGS, function ($routes) {
      $routes->get('/', 'Designs\DesignSettings::index', ['filter' => 'adminauth']);
      $routes->post('update', 'Designs\DesignSettings::update', ['filter' => 'adminauth']);
      $routes->post('remove-image', 'Designs\DesignSettings::removeImage', ['filter' => 'adminauth']);
    });

    // Menu Management
    $routes->group(ADMIN_URL_MENU_MANAGEMENT, function ($routes) {
      $routes->get('/', 'Designs\MenuManagement::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Designs\MenuManagement::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Designs\MenuManagement::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Designs\MenuManagement::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Designs\MenuManagement::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Designs\MenuManagement::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Designs\MenuManagement::delete/$1', ['filter' => 'adminauth']);
      $routes->get('nestable-list', 'Designs\MenuManagement::nestableList', ['filter' => 'adminauth']);
      $routes->get('nestable-update', 'Designs\MenuManagement::nestableUpdate', ['filter' => 'adminauth']);
    });

    // Fast Menu Management
    $routes->group(ADMIN_URL_FAST_MENU_MANAGEMENT, function ($routes) {
      $routes->get('/', 'Designs\FastMenuManagement::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Designs\FastMenuManagement::datatable', ['filter' => 'adminauth']);
      $routes->get('add', 'Designs\FastMenuManagement::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Designs\FastMenuManagement::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Designs\FastMenuManagement::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Designs\FastMenuManagement::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Designs\FastMenuManagement::delete/$1', ['filter' => 'adminauth']);
      $routes->get('nestable-list', 'Designs\FastMenuManagement::nestableList', ['filter' => 'adminauth']);
      $routes->get('nestable-update', 'Designs\FastMenuManagement::nestableUpdate', ['filter' => 'adminauth']);
    });

    // Banner Management
    $routes->group(ADMIN_URL_BANNER_MANAGEMENT, function ($routes) {
      $routes->get('/', 'Designs\BannerManagement::index', ['filter' => 'adminauth']);
      $routes->get('add', 'Designs\BannerManagement::add', ['filter' => 'adminauth']);
      $routes->post('insert', 'Designs\BannerManagement::insert', ['filter' => 'adminauth']);
      $routes->get('edit/(:num)', 'Designs\BannerManagement::edit/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Designs\BannerManagement::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Designs\BannerManagement::delete/$1', ['filter' => 'adminauth']);
      $routes->post('remove-image/(:num)', 'Designs\BannerManagement::removeImage/$1', ['filter' => 'adminauth']);
      $routes->post('sort', 'Designs\BannerManagement::sort', ['filter' => 'adminauth']);
    });

  });

  /*****************************************************/

  // Forms
  $routes->group(ADMIN_URL_FORMS, function ($routes) {

    // Contact Requests
    $routes->group(ADMIN_URL_CONTACT_REQUESTS, function ($routes) {
      $routes->get('/', 'Forms\ContactRequests::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Forms\ContactRequests::datatable', ['filter' => 'adminauth']);
      $routes->get('detail/(:num)', 'Forms\ContactRequests::detail/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Forms\ContactRequests::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Forms\ContactRequests::delete/$1', ['filter' => 'adminauth']);
    });

    // President Contact Requests
    $routes->group(ADMIN_URL_PRESIDENT_CONTACT_REQUESTS, function ($routes) {
      $routes->get('/', 'Forms\PresidentContactRequests::index', ['filter' => 'adminauth']);
      $routes->get('datatable', 'Forms\PresidentContactRequests::datatable', ['filter' => 'adminauth']);
      $routes->get('detail/(:num)', 'Forms\PresidentContactRequests::detail/$1', ['filter' => 'adminauth']);
      $routes->post('update/(:num)', 'Forms\PresidentContactRequests::update/$1', ['filter' => 'adminauth']);
      $routes->post('delete/(:num)', 'Forms\PresidentContactRequests::delete/$1', ['filter' => 'adminauth']);
    });

  });

});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
  require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}