<?php

namespace App\Controllers\Frontend;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\I18n\Time;
use Kenjis\CI4Twig\Twig;

use App\Models\Frontend\GeneralModel;
use App\Models\Frontend\MenuModel;
use App\Models\Frontend\RoutingModel;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
class BaseController extends Controller
{
  /**
   * Instance of the main Request object.
   *
   * @var CLIRequest|IncomingRequest
   */
  protected $request;
  protected $general;
  protected $MenuModel;
  protected $RoutingModel;
  protected $defaultLangId;
  protected $defaultLangCode;
  protected $defaultLangTitle;
  protected $defaultLangIcon;
  protected $defaultLangPercentageLocation;
  protected $languages;
  protected $socialMedia;
  protected $trackingCodes;
  protected $contactInformation;
  protected $settings;
  protected $designSettings;
  protected $mainLeftMenu;
  protected $mainRightMenu;
  protected $footerMenu;
  protected $FRONTEND_TEMPLATE_PATH;
  protected $twig;

  /**
   * An array of helpers to be loaded automatically upon
   * class instantiation. These helpers will be available
   * to all other controllers that extend BaseController.
   *
   * @var array
   */
  protected $helpers = ['session', 'form', 'text', 'html', 'format', 'date', 'functions', 'request', 'response', 'sultangazi'];

  /**
   * Constructor.
   */
  public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
  {
    parent::initController($request, $response, $logger);

    // Timezone
    date_default_timezone_set(Time::now()->getTimezoneName());

    // Default Model
    $this->general = new GeneralModel();
    $this->MenuModel = new MenuModel();
    $this->RoutingModel = new RoutingModel();

    /*****************************************************/

    // Language Change
    $this->languageChange();

    // Language Datas
    $lang_data = $this->languageData();
    $this->defaultLangId = $lang_data[0];
    $this->defaultLangCode = $lang_data[1];
    $this->defaultLangTitle = $lang_data[2];
    $this->defaultLangPercentageLocation = $lang_data[3];
    $this->defaultLangIcon = $lang_data[4];

    // Language URL's
    $this->languages = $this->languageUrl();

    /*****************************************************/

    // Social Media
    $this->socialMedia = $this->general->getSocialMediaModel();

    // Tracking Codes
    $this->trackingCodes = $this->general->getTrackingCodesModel();

    // Contact Information
    $this->contactInformation = $this->general->getContactInformationModel($this->defaultLangId);

    // Settings
    $this->settings = $this->general->getSettingsModel($this->defaultLangId);
    $this->designSettings = $this->general->getDesignSettingsModel($this->defaultLangId);

    // Main Menu
    $this->mainLeftMenu = $this->MenuModel->mainLeftMenuModel($this->defaultLangId);
    $this->mainRightMenu = $this->MenuModel->mainRightMenuModel($this->defaultLangId);

    // Footer Menu
    $this->footerMenu = $this->MenuModel->footerMenuModel($this->defaultLangId);

    /*****************************************************/

    // Frontend Path
    $this->FRONTEND_TEMPLATE_PATH = $this->frontendTemplatePath();

    /*****************************************************/

    // Force SSL
    if ($this->settings->ssl_status == FORM_ACTIVE_NUMBER) {
      $this->forceHTTPS();
    }

    /*****************************************************/

    // Functions
    $this->Twig();
  }

  /**
   * Frontend Template Path
   */
  public function frontendTemplatePath()
  {
    return Frontend;
  }

  /**
   * Language Change
   */
  public function languageChange()
  {
    // Segment
    $segment1 = $this->request->getUri()->getSegment(1);

    // Library
    $language = \Config\Services::language();

    // Remove Session Lang
    if (session()->has('webLang')) {
      session()->remove(['webLang']);
    }

    $sql = $this->general->languagesInfoModel($segment1);
    if (isNotNull($sql)) {

      // Choose Lang
      session()->set('webLang', $sql->lang_code);
      $language->setLocale($sql->lang_set_locale);
      setlocale(LC_TIME, $sql->lang_set_locale . '.UTF-8');
    } else {

      // Default Lang
      $info = $this->general->languagesInfoModel();
      if (isNotNull($info)) {
        if ($info->lang_id == $info->frontend_lang_default) {

          session()->set('webLang', $info->lang_code);
          $language->setLocale($info->lang_set_locale);
          setlocale(LC_TIME, $info->lang_set_locale . '.UTF-8');
        }
      }
    }

    /*****************************************************/

    // Language Control
    $lang_control = $this->general->languagesDefaultModel();
    if (isNull($lang_control)) {

      // Frontend Default Language
      $default_lang_code = $this->general->languagesFrontendDefaultModel();

      $language->setLocale($default_lang_code->lang_code);
      echo lang('AdminSettings.languageManagement.alert.systemError');
      exit();
    }
  }

  /**
   * Language Data
   */
  public function languageData()
  {
    $this->general = new GeneralModel();
    $lang_info = $this->general->languagesDefaultModel();
    if (isNotNull($lang_info)) {
      $lang_id = $lang_info[0];
      $code = $lang_info[1];
      $title = $lang_info[2];
      $percentage_location = $lang_info[3];
      $icon = $lang_info[4];

      return [
        $lang_id,
        $code,
        $title,
        $percentage_location,
        $icon
      ];
    }
  }

  /**
   * Language URL
   */
  public function languageUrl()
  {
    $segment1 = $this->request->getUri()->getSegment(1);
    $segment2 = $this->request->getUri()->getTotalSegments() > 1 ? $this->request->getUri()->getSegment(2) : NULL;
    $segment3 = $this->request->getUri()->getTotalSegments() > 2 ? $this->request->getUri()->getSegment(3) : NULL;

    $default_segment = $segment1;
    $languages = $this->RoutingModel->languagesModel();
    if (isNotNull($languages) && is_array($languages)) {
      if (in_array($segment1, $languages)) {
        $default_segment = $segment2;
      }
    }

    // Language Lists and URLS
    $return = [];
    $languages_sql = $this->general->languagesListModel();
    if (isNotNull($languages_sql)) {
      foreach ($languages_sql as $row) {

        // Lang Url
        $lang_url = NULL;
        if ($segment1 == WEB_URL_PAGES || $segment2 == WEB_URL_PAGES) { // Pages

          /*****************************************************/

          $segment3 = $this->request->getUri()->getTotalSegments() > 2 ? $this->request->getUri()->getSegment(3) : NULL;
          $segment4 = $this->request->getUri()->getTotalSegments() > 3 ? $this->request->getUri()->getSegment(4) : NULL;

          if ($segment1 == WEB_URL_PAGES) {
            $new_segment = $segment3;
          } else {
            $new_segment = $segment4;
          }

          $page_slug = $this->general->langPagesInfoModel($row->lang_id, $new_segment);
          if (isNotNull($page_slug)) {
            $lang_url = WEB_URL_PAGES . '/' . $page_slug->page_slug . '/' . $page_slug->page_id;
          }

          /*****************************************************/
        } elseif (($segment1 == WEB_URL_SULTANGAZI && $segment2 == WEB_URL_SULTANGAZI_CONTENTS) || ($segment2 == WEB_URL_SULTANGAZI && $segment3 == WEB_URL_SULTANGAZI_CONTENTS)) { // Sultangazi Contents

          /*****************************************************/

          $segment4 = $this->request->getUri()->getTotalSegments() > 3 ? $this->request->getUri()->getSegment(4) : NULL;
          $segment5 = $this->request->getUri()->getTotalSegments() > 4 ? $this->request->getUri()->getSegment(5) : NULL;

          if ($segment1 == WEB_URL_SULTANGAZI && $segment2 == WEB_URL_SULTANGAZI_CONTENTS) {
            $new_segment = $segment4;
          } else {
            $new_segment = $segment5;
          }

          $content_slug = $this->general->langSultangaziContentsInfoModel($row->lang_id, $new_segment);
          if (isNotNull($content_slug)) {
            $lang_url = WEB_URL_SULTANGAZI . '/' . WEB_URL_SULTANGAZI_CONTENTS . '/' . $content_slug->content_slug . '/' . $content_slug->content_id;
          }

          /*****************************************************/
        } elseif ($segment1 == WEB_URL_CONTRACTS || $segment2 == WEB_URL_CONTRACTS) { // Contracts

          /*****************************************************/

          $segment3 = $this->request->getUri()->getTotalSegments() > 2 ? $this->request->getUri()->getSegment(3) : NULL;
          $segment4 = $this->request->getUri()->getTotalSegments() > 3 ? $this->request->getUri()->getSegment(4) : NULL;

          if ($segment1 == WEB_URL_CONTRACTS) {
            $new_segment = $segment3;
          } else {
            $new_segment = $segment4;
          }

          $contract_slug = $this->general->langContractsInfoModel($row->lang_id, $new_segment);
          if (isNotNull($contract_slug)) {
            $lang_url = WEB_URL_CONTRACTS . '/' . $contract_slug->contract_slug . '/' . $contract_slug->contract_id;
          }

          /*****************************************************/
        } elseif (($segment1 == WEB_URL_SERVICES) || ($segment2 == WEB_URL_SERVICES)) { // Services

          /*****************************************************/

          $segment4 = $this->request->getUri()->getTotalSegments() > 2 ? $this->request->getUri()->getSegment(3) : NULL;
          $segment5 = $this->request->getUri()->getTotalSegments() > 3 ? $this->request->getUri()->getSegment(4) : NULL;

          if ($segment1 == WEB_URL_SERVICES) {
            $new_segment = $segment4;
          } else {
            $new_segment = $segment5;
          }

          $services_detail_slug = $this->general->langServicesDetailInfoModel($row->lang_id, $new_segment);
          if (isNotNull($services_detail_slug)) {
            $lang_url = WEB_URL_SERVICES . '/' . $services_detail_slug->service_slug . '/' . $services_detail_slug->service_id;
          }

          /*****************************************************/
        } elseif (($segment1 == WEB_URL_PROJECT_CATEGORY && $segment2 == WEB_URL_PROJECT_CATEGORY_DETAIL_PARAMETER) || ($segment2 == WEB_URL_PROJECT_CATEGORY && $segment3 == WEB_URL_PROJECT_CATEGORY_DETAIL_PARAMETER)) { // Project Categories

          /*****************************************************/

          $segment4 = $this->request->getUri()->getTotalSegments() > 3 ? $this->request->getUri()->getSegment(4) : NULL;
          $segment5 = $this->request->getUri()->getTotalSegments() > 4 ? $this->request->getUri()->getSegment(5) : NULL;

          if ($segment1 == WEB_URL_PROJECT_CATEGORY && $segment2 == WEB_URL_PROJECT_CATEGORY_DETAIL_PARAMETER) {
            $new_segment = $segment4;
          } else {
            $new_segment = $segment5;
          }

          $project_category_slug = $this->general->langProjectCategoryDetailInfoModel($row->lang_id, $new_segment);
          if (isNotNull($project_category_slug)) {
            $lang_url = WEB_URL_PROJECT_CATEGORY_DETAIL_PARAMETER . '/' . $project_category_slug->project_category_slug . '/' . $project_category_slug->project_category_id;
          }

          /*****************************************************/
        } elseif (($segment1 == WEB_URL_PROJECTS && $segment2 == WEB_URL_PROJECTS_DETAIL_PARAMETER) || ($segment2 == WEB_URL_PROJECTS && $segment3 == WEB_URL_PROJECTS_DETAIL_PARAMETER)) { // Projects

          /*****************************************************/

          $segment4 = $this->request->getUri()->getTotalSegments() > 3 ? $this->request->getUri()->getSegment(4) : NULL;
          $segment5 = $this->request->getUri()->getTotalSegments() > 4 ? $this->request->getUri()->getSegment(5) : NULL;

          if ($segment1 == WEB_URL_PROJECTS && $segment2 == WEB_URL_PROJECTS_DETAIL_PARAMETER) {
            $new_segment = $segment4;
          } else {
            $new_segment = $segment5;
          }

          $project_slug = $this->general->langProjectDetailInfoModel($row->lang_id, $new_segment);
          if (isNotNull($project_slug)) {
            $lang_url = WEB_URL_PROJECTS_DETAIL . '/' . $project_slug->project_slug . '/' . $project_slug->project_id;
          }

          /*****************************************************/
        } elseif (($segment1 == WEB_URL_NEWS && $segment2 == WEB_URL_NEWS_DETAIL_PARAMETER) || ($segment2 == WEB_URL_NEWS && $segment3 == WEB_URL_NEWS_DETAIL_PARAMETER)) { // News

          /*****************************************************/

          $segment4 = $this->request->getUri()->getTotalSegments() > 3 ? $this->request->getUri()->getSegment(4) : NULL;
          $segment5 = $this->request->getUri()->getTotalSegments() > 4 ? $this->request->getUri()->getSegment(5) : NULL;

          if ($segment1 == WEB_URL_NEWS && $segment2 == WEB_URL_NEWS_DETAIL_PARAMETER) {
            $new_segment = $segment4;
          } else {
            $new_segment = $segment5;
          }

          $news_slug = $this->general->langNewsDetailInfoModel($row->lang_id, $new_segment);
          if (isNotNull($news_slug)) {
            $lang_url = WEB_URL_NEWS_DETAIL . '/' . $news_slug->news_slug . '/' . $news_slug->news_id;
          }

          /*****************************************************/
        } elseif (($segment1 == WEB_URL_EVENTS && isNotNull($segment2)) || ($segment2 == WEB_URL_EVENTS && isNotNull($segment3))) { // Events

          /*****************************************************/

          $segment4 = $this->request->getUri()->getTotalSegments() > 2 ? $this->request->getUri()->getSegment(3) : NULL;
          $segment5 = $this->request->getUri()->getTotalSegments() > 3 ? $this->request->getUri()->getSegment(4) : NULL;

          if ($segment1 == WEB_URL_EVENTS) {
            $new_segment = $segment4;
          } else {
            $new_segment = $segment5;
          }

          $event_slug = $this->general->langEventsDetailInfoModel($row->lang_id, $new_segment);
          if (isNotNull($event_slug)) {
            $lang_url = WEB_URL_EVENTS . '/' . $event_slug->event_slug . '/' . $event_slug->event_id;
          }

          /*****************************************************/
        } elseif (($segment1 == WEB_URL_DIRECTORATES && $segment2 == WEB_URL_DIRECTORATES_DETAIL_PARAMETER) || ($segment2 == WEB_URL_DIRECTORATES && $segment3 == WEB_URL_DIRECTORATES_DETAIL_PARAMETER)) { // Directorates

          /*****************************************************/

          $segment4 = $this->request->getUri()->getTotalSegments() > 3 ? $this->request->getUri()->getSegment(4) : NULL;
          $segment5 = $this->request->getUri()->getTotalSegments() > 4 ? $this->request->getUri()->getSegment(5) : NULL;

          if ($segment1 == WEB_URL_DIRECTORATES && $segment2 == WEB_URL_DIRECTORATES_DETAIL_PARAMETER) {
            $new_segment = $segment4;
          } else {
            $new_segment = $segment5;
          }

          $directorates_slug = $this->general->langDirectoratesDetailInfoModel($row->lang_id, $new_segment);
          if (isNotNull($directorates_slug)) {
            $lang_url = WEB_URL_DIRECTORATES_DETAIL . '/' . $directorates_slug->directorates_slug . '/' . $directorates_slug->directorates_id;
          }

          /*****************************************************/
        } elseif (($segment1 == WEB_URL_VICE_PRESIDENTS && $segment2 == WEB_URL_VICE_PRESIDENTS_DETAIL_PARAMETER) || ($segment2 == WEB_URL_VICE_PRESIDENTS && $segment3 == WEB_URL_VICE_PRESIDENTS_DETAIL_PARAMETER)) { // Vice Presidents

          /*****************************************************/

          $segment4 = $this->request->getUri()->getTotalSegments() > 3 ? $this->request->getUri()->getSegment(4) : NULL;
          $segment5 = $this->request->getUri()->getTotalSegments() > 4 ? $this->request->getUri()->getSegment(5) : NULL;

          if ($segment1 == WEB_URL_VICE_PRESIDENTS && $segment2 == WEB_URL_VICE_PRESIDENTS_DETAIL_PARAMETER) {
            $new_segment = $segment4;
          } else {
            $new_segment = $segment5;
          }

          $vice_president_slug = $this->general->langVicePresedentDetailInfoModel($row->lang_id, $new_segment);
          if (isNotNull($vice_president_slug)) {
            $lang_url = WEB_URL_VICE_PRESIDENTS_DETAIL . '/' . $vice_president_slug->vice_president_slug . '/' . $vice_president_slug->vice_president_id;
          }

          /*****************************************************/
        } elseif (($segment1 == WEB_URL_GALLERY && $segment2 == WEB_URL_GALLERY_CATEGORY_PARAMETER) || ($segment2 == WEB_URL_GALLERY && $segment3 == WEB_URL_GALLERY_CATEGORY_PARAMETER)) { // Gallery Categories

          /*****************************************************/

          $segment4 = $this->request->getUri()->getTotalSegments() > 3 ? $this->request->getUri()->getSegment(4) : NULL;
          $segment5 = $this->request->getUri()->getTotalSegments() > 4 ? $this->request->getUri()->getSegment(5) : NULL;

          if ($segment1 == WEB_URL_GALLERY && $segment2 == WEB_URL_GALLERY_CATEGORY_PARAMETER) {
            $new_segment = $segment4;
          } else {
            $new_segment = $segment5;
          }

          $gallery_category_slug = $this->general->langGalleryCategoriesDetailInfoModel($row->lang_id, $new_segment);
          if (isNotNull($gallery_category_slug)) {
            $lang_url = WEB_URL_GALLERY_CATEGORY . '/' . slug($gallery_category_slug->gallery_category_name) . '/' . $gallery_category_slug->gallery_category_id;
          }

          /*****************************************************/
        } elseif (($segment1 == WEB_URL_GALLERY && $segment2 == WEB_URL_GALLERY_DETAIL_PARAMETER) || ($segment2 == WEB_URL_GALLERY && $segment3 == WEB_URL_GALLERY_DETAIL_PARAMETER)) { // Gallery Detail

          /*****************************************************/

          $segment4 = $this->request->getUri()->getTotalSegments() > 3 ? $this->request->getUri()->getSegment(4) : NULL;
          $segment5 = $this->request->getUri()->getTotalSegments() > 4 ? $this->request->getUri()->getSegment(5) : NULL;

          if ($segment1 == WEB_URL_GALLERY && $segment2 == WEB_URL_GALLERY_DETAIL_PARAMETER) {
            $new_segment = $segment4;
          } else {
            $new_segment = $segment5;
          }

          $gallery_slug = $this->general->langGalleryDetailInfoModel($row->lang_id, $new_segment);
          if (isNotNull($gallery_slug)) {
            $lang_url = WEB_URL_GALLERY_DETAIL . '/' . slug($gallery_slug->gallery_name) . '/' . $gallery_slug->gallery_id;
          }

          /*****************************************************/
        } elseif (($segment1 == WEB_URL_SULTANGAZI && $segment2 == WEB_URL_SULTANGAZI_CITY_GUIDES && isNotNull($segment3) && (int) isNotNull($this->request->getUri()->getSegment(4))) || ($segment2 == WEB_URL_SULTANGAZI && $segment3 == WEB_URL_SULTANGAZI_CITY_GUIDES && isNotNull($this->request->getUri()->getSegment(4)) && (int) isNotNull($this->request->getUri()->getSegment(5)))) { // City Guide Categories

          /*****************************************************/

          $segment4 = $this->request->getUri()->getTotalSegments() > 3 ? $this->request->getUri()->getSegment(4) : NULL;
          $segment5 = $this->request->getUri()->getTotalSegments() > 4 ? $this->request->getUri()->getSegment(5) : NULL;

          if ($segment1 == WEB_URL_SULTANGAZI && $segment2 == WEB_URL_SULTANGAZI_CITY_GUIDES) {
            $new_segment = $segment4;
          } else {
            $new_segment = $segment5;
          }

          $city_guide_category_slug = $this->general->langCityGuideCategoriesInfoModel($row->lang_id, $new_segment);
          if (isNotNull($city_guide_category_slug)) {
            $lang_url = WEB_URL_SULTANGAZI . '/' . WEB_URL_SULTANGAZI_CITY_GUIDES . '/' . $city_guide_category_slug->city_guide_category_slug . '/' . $city_guide_category_slug->city_guide_category_id;
          }

          /*****************************************************/
        } elseif (($segment1 == WEB_URL_SULTANGAZI && $segment2 == WEB_URL_SULTANGAZI_CITY_GUIDES && $segment3 == WEB_URL_SULTANGAZI_CITY_GUIDES_DETAIL_PARAMETER) || ($segment2 == WEB_URL_SULTANGAZI && $segment3 == WEB_URL_SULTANGAZI_CITY_GUIDES && $this->request->getUri()->getSegment(4) == WEB_URL_SULTANGAZI_CITY_GUIDES_DETAIL_PARAMETER)) { // City Guide Contents

          /*****************************************************/

          $segment5 = $this->request->getUri()->getTotalSegments() > 4 ? $this->request->getUri()->getSegment(5) : NULL;
          $segment6 = $this->request->getUri()->getTotalSegments() > 5 ? $this->request->getUri()->getSegment(6) : NULL;

          if ($segment1 == WEB_URL_SULTANGAZI && $segment2 == WEB_URL_SULTANGAZI_CITY_GUIDES && $segment3 == WEB_URL_SULTANGAZI_CITY_GUIDES_DETAIL_PARAMETER) {
            $new_segment = $segment5;
          } else {
            $new_segment = $segment6;
          }

          $city_guide_content_slug = $this->general->langCityGuideContentsInfoModel($row->lang_id, $new_segment);
          if (isNotNull($city_guide_content_slug)) {
            $lang_url = WEB_URL_SULTANGAZI . '/' . WEB_URL_SULTANGAZI_CITY_GUIDES . '/' . WEB_URL_SULTANGAZI_CITY_GUIDES_DETAIL_PARAMETER . '/' . $city_guide_content_slug->city_guide_content_slug . '/' . $city_guide_content_slug->city_guide_content_id;
          }

          /*****************************************************/
        } else {

          // Full URL
          $uri = new \CodeIgniter\HTTP\URI(fullUrl());
          $get_query = $uri->getQuery();
          $get_query = isNotNull($get_query) ? '?' . $get_query : NULL;

          // Standart Links
          if (in_array($segment1, $languages)) {
            $segment3 = $this->request->getUri()->getTotalSegments() > 2 ? '/' . $this->request->getUri()->getSegment(3) : NULL;
            $segment4 = $this->request->getUri()->getTotalSegments() > 3 ? '/' . $this->request->getUri()->getSegment(4) : NULL;
            $segment5 = $this->request->getUri()->getTotalSegments() > 4 ? '/' . $this->request->getUri()->getSegment(5) : NULL;
            $lang_url = $segment2 . $segment3 . $segment4 . $segment5 . $get_query;
          } else {
            $lang_url = $this->request->getPath() . $get_query;
          }
        }

        $return[] = [
          'lang_title' => $row->lang_title,
          'lang_code' => $row->lang_code,
          'lang_code_end' => $row->lang_code_end,
          'lang_url' => $lang_url,
          'lang_icon' => $row->lang_icon
        ];
      }
    }

    return $return;
  }

  /**
   * Image Control
   * @param $path
   * @param $name
   */
  public function imageControl($path, $name)
  {
    // Path ve name parametrelerini temizle ve güvenli hale getir
    $path = $this->sanitizePath($path);
    $name = $this->sanitizeFilename($name);

    if (empty($path) || empty($name)) {
      return base_url('assets/' . FILE_PATH_IMAGES . '/no-image.jpg');
    }

    // .webp dosyasının adını güvenli şekilde oluştur
    $webp_filename = pathinfo($name, PATHINFO_FILENAME) . '.webp';
    $webp_image = $path . '/' . $webp_filename;

    // Güvenli dosya yolu kontrolü
    if (file_exists($webp_image) && $this->isValidFilePath($webp_image)) {
      $image = $webp_image;
    } else {
      if (isNotNull($name) && $this->isValidFilePath($path . '/' . $name)) {
        $image = $path . '/' . $name;
      } else {
        $image = 'assets/' . FILE_PATH_IMAGES . '/no-image.jpg';
      }
    }

    return base_url($image);
  }
  /**
   * Image Control
   * @param $path
   * @param $name
   */
  public function sultanImageControl(string $path, string $name): string
  {
    // Temizle
    $path = $this->sanitizePath($path);
    $name = $this->sanitizeFilename($name);

    // Boşsa varsayılan resmi döndür
    if (empty($name)) {
      return sultangazi_url('assets/' . SULTAN_FILE_PATH_IMAGES . '/no-image.jpg');
    }

    // WebP versiyonu
    $webpFile = ($path ? $path . '/' : '') . pathinfo($name, PATHINFO_FILENAME) . '.webp';

    // Öncelik WebP
    if (file_exists($webpFile) && $this->isValidFilePath($webpFile)) {
      return sultangazi_url($webpFile);
    }

    // Orijinal dosya kontrolü
    $originalFile = $path . '/' . $name;
    
    return sultangazi_url($originalFile);
  }


  /**
   * Dosya yolunu temizle
   * @param string $path
   * @return string
   */
  private function sanitizePath($path)
  {
    if (empty($path)) {
      return '';
    }

    // Canonical yol elde et ve güvenlik kontrolleri yap
    $cleaned_path = str_replace('\\', '/', $path);
    $cleaned_path = rtrim($cleaned_path, '/');

    // Mutlak yolları göreceli yollarla değiştir
    if (strpos($cleaned_path, '/') === 0) {
      $cleaned_path = substr($cleaned_path, 1);
    }

    // Path traversal denemelerini engelle
    // ".." ifadelerini tamamen kaldır
    $cleaned_path = preg_replace('/\.{2,}/', '', $cleaned_path);

    // Çift slash işaretlerini tekli slash ile değiştir
    $cleaned_path = preg_replace('/\/+/', '/', $cleaned_path);

    // İzin verilen karakterleri belirle
    $cleaned_path = preg_replace('/[^a-zA-Z0-9\/_\-\.]/', '', $cleaned_path);

    return $cleaned_path;
  }

  /**
   * Dosya adını temizle
   * @param string $filename
   * @return string
   */
  private function sanitizeFilename($filename)
  {
    if (empty($filename)) {
      return '';
    }

    // Sadece dosya adını al
    $filename = basename($filename);

    // Dosya adındaki tehlikeli karakterleri kaldır
    $filename = preg_replace('/[\/\\\\]/', '', $filename);

    // Null byte saldırılarını engelle
    $filename = str_replace(chr(0), '', $filename);

    // Dosya uzantısını ve adını ayır
    $parts = explode('.', $filename);
    $extension = '';

    if (count($parts) > 1) {
      $extension = '.' . end($parts);
      array_pop($parts);
    }

    // Dosya adını temizle
    $name = implode('.', $parts);
    $name = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $name);

    // Dosya adı ve uzantıyı birleştir
    return $name . $extension;
  }

  /**
   * Dosya yolunun güvenli olup olmadığını kontrol et
   * @param string $filepath
   * @return bool
   */
  private function isValidFilePath($filepath)
  {
    // Dosya varlığını kontrol et
    if (!file_exists($filepath)) {
      return false;
    }

    // Gerçek yolu al
    $realpath = realpath($filepath);

    if ($realpath === false) {
      return false;
    }

    // Web kök dizinini al
    $webroot = realpath(FCPATH);

    if ($webroot === false) {
      return false;
    }

    // Dosyanın web kök dizininde olup olmadığını kontrol et
    if (strpos($realpath, $webroot) !== 0) {
      return false;
    }

    // Dosya uzantısını kontrol et - sadece izin verilen dosya türlerine izin ver
    $extension = strtolower(pathinfo($realpath, PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf'];

    if (!in_array($extension, $allowed_extensions)) {
      return false;
    }

    return true;
  }

  /**
   * Services (E-Belediye)
   * @param $lang_id
   */
  public function ebelediyeServices()
  {
    $array = [];
    $sql = $this->general->ebelediyeServicesModel($this->defaultLangId);
    if (isNotNull($sql)) {
      foreach ($sql as $row) {
        $array[] = [
          'service_id' => $row->service_id,
          'service_name' => $row->service_name,
          'service_link' => $row->service_link,
          'service_slug' => $row->service_slug,
          'icon' => [
            'normal' => $row->service_icon,
            'base' => $this->imageControl(FILE_PATH_SERVICES, $row->service_icon)
          ]
        ];
      }
    }

    return $array;
  }

  /**
   * Twig
   */
  public function Twig()
  {
    $agent = $this->request->getUserAgent();
        
    $twigConfig = [
      'functions_safe' => ['doctype', 'lang', 'form', 'link_tag', 'current_url', 'script_tag', 'web_url', 'slug', 'sultangazi_url']
    ];

    $this->twig = new Twig($twigConfig);

    $this->twig->addGlobal('isMobile', $agent->isMobile());
    $this->twig->addGlobal('FRONTEND_TEMPLATE_PATH', $this->FRONTEND_TEMPLATE_PATH);
    $this->twig->addGlobal('DEFAULT_LANGUAGES_CODE', $this->defaultLangCode);
    $this->twig->addGlobal('DEFAULT_LANGUAGES_NAME', $this->defaultLangTitle);
    $this->twig->addGlobal('DEFAULT_LANGUAGES_ICON', $this->defaultLangIcon);
    $this->twig->addGlobal('FILE_PATH_MAIN', FILE_PATH_MAIN);
    $this->twig->addGlobal('FILE_PATH_ASSETS', FILE_PATH_ASSETS);
    $this->twig->addGlobal('FILE_PATH_IMAGES', FILE_PATH_IMAGES);
    $this->twig->addGlobal('FILE_PATH_PROJECT', FILE_PATH_PROJECT);
    $this->twig->addGlobal('FILE_PATH_FLAGS', FILE_PATH_FLAGS);
    // Spor Akademisi (Nexorada) bağlantıları
    $this->twig->addGlobal('SPORT_ACADEMY_URL', SPORT_ACADEMY_URL);
    $this->twig->addGlobal('SPORT_ACADEMY_PATH_BRANCHES', SPORT_ACADEMY_PATH_BRANCHES);
    $this->twig->addGlobal('SPORT_ACADEMY_PATH_FACILITIES', SPORT_ACADEMY_PATH_FACILITIES);
    $this->twig->addGlobal('FORM_ACTIVE_NUMBER', FORM_ACTIVE_NUMBER);
    $this->twig->addGlobal('FORM_CHECKBOX_VALUE_NUMBER', FORM_CHECKBOX_VALUE_NUMBER);
    $this->twig->addGlobal('WEB_URL_CONTRACTS', WEB_URL_CONTRACTS);
    $this->twig->addGlobal('WEB_URL_CONTACT', WEB_URL_CONTACT);
    $this->twig->addGlobal('WEB_URL_SERVICES', WEB_URL_SERVICES);
    $this->twig->addGlobal('WEB_URL_PROJECTS', WEB_URL_PROJECTS);
    $this->twig->addGlobal('MENU_MANAGEMENT_TEMPLATE_SUB_MENU_1', MENU_MANAGEMENT_TEMPLATE_SUB_MENU_1);
    $this->twig->addGlobal('MENU_MANAGEMENT_TEMPLATE_SUB_MENU_2', MENU_MANAGEMENT_TEMPLATE_SUB_MENU_2);
    $this->twig->addGlobal('MENU_MANAGEMENT_TEMPLATE_SUB_MENU_3', MENU_MANAGEMENT_TEMPLATE_SUB_MENU_3);
    $this->twig->addGlobal('main_left_menu', $this->mainLeftMenu);
    $this->twig->addGlobal('main_right_menu', $this->mainRightMenu);
    $this->twig->addGlobal('footer_menu', $this->footerMenu);
    $this->twig->addGlobal('settings', $this->settings);
    $this->twig->addGlobal('design', $this->designSettings);
    $this->twig->addGlobal('tracking_codes', $this->trackingCodes);
    $this->twig->addGlobal('languages', $this->languages);
    $this->twig->addGlobal('social_media', $this->socialMedia);
    $this->twig->addGlobal('contact', $this->contactInformation);
    $this->twig->addGlobal('ebelediye_services', $this->ebelediyeServices());
  }
}
