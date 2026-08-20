<?php

/*
 | --------------------------------------------------------------------
 | App Namespace
 | --------------------------------------------------------------------
 |
 | This defines the default Namespace that is used throughout
 | CodeIgniter to refer to the Application directory. Change
 | this constant to change the namespace that all application
 | classes should use.
 |
 | NOTE: changing this will require manually modifying the
 | existing namespaces of App\* namespaced-classes.
 */
defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');

/*
 | --------------------------------------------------------------------------
 | Composer Path
 | --------------------------------------------------------------------------
 |
 | The path that Composer's autoload file is expected to live. By default,
 | the vendor folder is in the Root directory, but you can customize that here.
 */
defined('COMPOSER_PATH') || define('COMPOSER_PATH', ROOTPATH . 'vendor/autoload.php');

/*
 |--------------------------------------------------------------------------
 | Timing Constants
 |--------------------------------------------------------------------------
 |
 | Provide simple ways to work with the myriad of PHP functions that
 | require information to be in seconds.
 */
defined('SECOND') || define('SECOND', 1);
defined('MINUTE') || define('MINUTE', 60);
defined('HOUR')   || define('HOUR', 3600);
defined('DAY')    || define('DAY', 86400);
defined('WEEK')   || define('WEEK', 604800);
defined('MONTH')  || define('MONTH', 2_592_000);
defined('YEAR')   || define('YEAR', 31_536_000);
defined('DECADE') || define('DECADE', 315_360_000);

/*
 | --------------------------------------------------------------------------
 | Exit Status Codes
 | --------------------------------------------------------------------------
 |
 | Used to indicate the conditions under which the script is exit()ing.
 | While there is no universal standard for error codes, there are some
 | broad conventions.  Three such conventions are mentioned below, for
 | those who wish to make use of them.  The CodeIgniter defaults were
 | chosen for the least overlap with these conventions, while still
 | leaving room for others to be defined in future versions and user
 | applications.
 |
 | The three main conventions used for determining exit status codes
 | are as follows:
 |
 |    Standard C/C++ Library (stdlibc):
 |       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
 |       (This link also contains other GNU-specific conventions)
 |    BSD sysexits.h:
 |       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
 |    Bash scripting:
 |       http://tldp.org/LDP/abs/html/exitcodes.html
 |
 */
defined('EXIT_SUCCESS')        || define('EXIT_SUCCESS', 0);        // no errors
defined('EXIT_ERROR')          || define('EXIT_ERROR', 1);          // generic error
defined('EXIT_CONFIG')         || define('EXIT_CONFIG', 3);         // configuration error
defined('EXIT_UNKNOWN_FILE')   || define('EXIT_UNKNOWN_FILE', 4);   // file not found
defined('EXIT_UNKNOWN_CLASS')  || define('EXIT_UNKNOWN_CLASS', 5);  // unknown class
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     || define('EXIT_USER_INPUT', 7);     // invalid user input
defined('EXIT_DATABASE')       || define('EXIT_DATABASE', 8);       // database error
defined('EXIT__AUTO_MIN')      || define('EXIT__AUTO_MIN', 9);      // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      || define('EXIT__AUTO_MAX', 125);    // highest automatically-assigned error code

/*
 * MY Defined
*/
defined('Backend') OR define('Backend', 'Backend');
defined('Frontend') OR define('Frontend', 'Frontend');
defined('BACKEND_URL') OR define('BACKEND_URL', 'sbyonetim');

// HTTP_HOST kullanılır: standart dışı port (ör. yerel geliştirme :8088) korunur.
defined('APP_BASE_URL') OR define('APP_BASE_URL', ($_SERVER['SERVER_PORT'] == 443 ? 'https' : 'http').'://'.($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME']).str_replace(basename($_SERVER['SCRIPT_NAME']),'', $_SERVER['SCRIPT_NAME']));
defined('SULTANGAZI_URL') OR define('SULTANGAZI_URL', 'https://www.sultangazi.bel.tr/');

/*
 * Eğitim Kurumları
 * Anasayfadaki "Eğitim Kurumlarımız" alanında kullanılır.
 * Metinler app/Language/{tr,en}/WebIndex.php -> education.items altındadır.
 * Görseller assets/img/education/ klasöründedir.
 */
const EDUCATION_INSTITUTIONS = [
    [
        'key'   => 'anakucagi',
        'url'   => 'https://anakucagi.sultangazi.bel.tr',
        'image' => 'anakucagi.png',
        'theme' => 'from-rose-500 to-pink-600',
    ],
    [
        'key'   => 'kasifcocuk',
        'url'   => 'https://kasifcocuk.sultangazi.bel.tr',
        'image' => 'kasifcocuk.webp',
        'theme' => 'from-amber-400 to-orange-500',
    ],
    [
        'key'   => 'seda',
        'url'   => 'https://seda.sultangazi.bel.tr',
        'image' => 'seda.png',
        'theme' => 'from-sky-500 to-blue-600',
    ],
    [
        'key'   => 'bilimmerkezi',
        'url'   => 'https://bilimmerkezi.sultangazi.bel.tr',
        'image' => 'bilimmerkezi.png',
        'theme' => 'from-emerald-500 to-teal-600',
    ],
];

/*
 * Web Links
*/
const WEB_URL_PRESIDENT = 'baskan';
const WEB_URL_PRESIDENT_GALLERY = 'galeri';
const WEB_URL_PRESIDENT_CONTACT = 'iletisim';
const WEB_URL_PRESIDENT_CONTACT_SEND = WEB_URL_PRESIDENT.'/'.WEB_URL_PRESIDENT_CONTACT.'/gonder';

const WEB_URL_PAGES = 'pages';
const WEB_URL_CONTRACTS = 'sozlesmeler';
const WEB_URL_REFERANCES = 'referances';

const WEB_URL_SULTANGAZI = 'sultangazi';
const WEB_URL_SULTANGAZI_CONTENTS = 'contents';
const WEB_URL_SULTANGAZI_GALLERY = WEB_URL_SULTANGAZI.'/osmanli-arsivlerinde-sultangazi';
const WEB_URL_SULTANGAZI_VIDEO_GALLERY = WEB_URL_SULTANGAZI.'/video-gallery';
const WEB_URL_SULTANGAZI_CITY_GUIDES = 'city-guides';
const WEB_URL_SULTANGAZI_CITY_GUIDES_DETAIL_PARAMETER = 'detail';

const WEB_URL_MUNICIPAL_COUNCILS = 'municipal-councils';
const WEB_URL_ORGANIZATION_CHART = 'organization-chart';
const WEB_URL_COUNCIL_MEMBERS = 'council-members';
const WEB_URL_LOGOS = 'logos';
const WEB_URL_PARLIAMENTARY_AGENDA = 'parliamentary-agenda';
const WEB_URL_ACTIVITY_REPORT = 'activity-report';
const WEB_URL_STRATEGIC_PLAN_AND_PERFORMANCA = 'strategic-plan-and-performance';
const WEB_URL_PLAN_AND_PROGRAM = 'plan-and-program';
const WEB_URL_PRESS_RELEASE = 'press-release';
const WEB_URL_INTERNAL_CONTROL = 'internal-control';

const WEB_URL_CONTACT = 'iletisim';
const WEB_URL_CONTACT_SEND = WEB_URL_CONTACT.'/gonder';

const WEB_URL_VICE_PRESIDENTS = 'vice-presidents';
const WEB_URL_VICE_PRESIDENTS_DETAIL_PARAMETER = 'detail';
const WEB_URL_VICE_PRESIDENTS_DETAIL = WEB_URL_VICE_PRESIDENTS.'/'.WEB_URL_VICE_PRESIDENTS_DETAIL_PARAMETER;

const WEB_URL_DIRECTORATES = 'directorates';
const WEB_URL_DIRECTORATES_DETAIL_PARAMETER = 'detail';
const WEB_URL_DIRECTORATES_DETAIL = WEB_URL_DIRECTORATES.'/'.WEB_URL_DIRECTORATES_DETAIL_PARAMETER;

const WEB_URL_SERVICES = 'services';

const WEB_URL_ANNOUNCEMENTS = 'announcements';
const WEB_URL_TENDER_ANNOUNCEMENTS = 'pages/ihale/10';

const WEB_URL_NEWS = 'haberler';
const WEB_URL_NEWS_DETAIL_PARAMETER = 'detay';
const WEB_URL_NEWS_DETAIL = WEB_URL_NEWS.'/'.WEB_URL_NEWS_DETAIL_PARAMETER;

const WEB_URL_EVENTS = 'etkinlikler';
const WEB_URL_EVENTS_DETAIL_PARAMETER = 'detay';
const WEB_URL_EVENTS_DETAIL = WEB_URL_EVENTS.'/'.WEB_URL_EVENTS_DETAIL_PARAMETER;

const WEB_URL_PROJECT_CATEGORY = 'project-category';
const WEB_URL_PROJECT_CATEGORY_DETAIL_PARAMETER = 'detail';
const WEB_URL_PROJECT_CATEGORY_DETAIL = WEB_URL_PROJECT_CATEGORY.'/'.WEB_URL_PROJECT_CATEGORY_DETAIL_PARAMETER;

const WEB_URL_PROJECTS = 'projeler';
const WEB_URL_PROJECTS_DETAIL_PARAMETER = 'detay';
const WEB_URL_PROJECTS_DETAIL = WEB_URL_PROJECTS.'/'.WEB_URL_PROJECTS_DETAIL_PARAMETER;

const WEB_URL_GALLERY = 'gallery';
const WEB_URL_GALLERY_CATEGORY_PARAMETER = 'category';
const WEB_URL_GALLERY_CATEGORY = WEB_URL_GALLERY.'/'.WEB_URL_GALLERY_CATEGORY_PARAMETER;

const WEB_URL_GALLERY_DETAIL_PARAMETER = 'detail';
const WEB_URL_GALLERY_DETAIL = WEB_URL_GALLERY.'/'.WEB_URL_GALLERY_DETAIL_PARAMETER;

const WEB_URL_VIDEO_GALLERY = 'video-gallery';

const WEB_URL_PRESIDENT_TR = 'baskan';
const WEB_URL_PRESIDENT_GALLERY_TR = 'galeri';
const WEB_URL_PRESIDENT_CONTACT_TR = 'iletisim';
const WEB_URL_PRESIDENT_CONTACT_SEND_TR = WEB_URL_PRESIDENT.'/'.WEB_URL_PRESIDENT_CONTACT.'/gonder';

const WEB_URL_PAGES_TR = 'sayfalar';
const WEB_URL_CONTRACTS_TR = 'sozlesmeler';
const WEB_URL_REFERANCES_TR = 'referanslar';

const WEB_URL_SULTANGAZI_TR = 'sultangazi';
const WEB_URL_SULTANGAZI_CONTENTS_TR = 'icerikler';
const WEB_URL_SULTANGAZI_GALLERY_TR = WEB_URL_SULTANGAZI.'/osmanli-arsivlerinde-sultangazi';
const WEB_URL_SULTANGAZI_VIDEO_GALLERY_TR = WEB_URL_SULTANGAZI.'/video-galeri';
const WEB_URL_SULTANGAZI_CITY_GUIDES_TR = 'sehir-rehberi';
const WEB_URL_SULTANGAZI_CITY_GUIDES_DETAIL_PARAMETER_TR = 'detay';

const WEB_URL_MUNICIPAL_COUNCILS_TR = 'belediye-meclisi';
const WEB_URL_ORGANIZATION_CHART_TR = 'organizasyon-semasi';
const WEB_URL_COUNCIL_MEMBERS_TR = 'meclis-uyeleri';
const WEB_URL_LOGOS_TR = 'logolar';
const WEB_URL_PARLIAMENTARY_AGENDA_TR = 'meclis-gundemi';
const WEB_URL_ACTIVITY_REPORT_TR = 'faaliyet-raporu';
const WEB_URL_STRATEGIC_PLAN_AND_PERFORMANCA_TR = 'stratejik-plan-ve-performans';
const WEB_URL_PLAN_AND_PROGRAM_TR = 'plan-ve-program';
const WEB_URL_PRESS_RELEASE_TR = 'basin-bulteni';
const WEB_URL_INTERNAL_CONTROL_TR = 'ic-kontrol';

const WEB_URL_CONTACT_TR = 'iletisim';
const WEB_URL_CONTACT_SEND_TR = WEB_URL_CONTACT.'/gonder';

const WEB_URL_VICE_PRESIDENTS_TR = 'baskan-yardimcilari';
const WEB_URL_VICE_PRESIDENTS_DETAIL_PARAMETER_TR = 'detay';
const WEB_URL_VICE_PRESIDENTS_DETAIL_TR = WEB_URL_VICE_PRESIDENTS.'/'.WEB_URL_VICE_PRESIDENTS_DETAIL_PARAMETER;

const WEB_URL_DIRECTORATES_TR = 'mudurlukler';
const WEB_URL_DIRECTORATES_DETAIL_PARAMETER_TR = 'detay';
const WEB_URL_DIRECTORATES_DETAIL_TR = WEB_URL_DIRECTORATES.'/'.WEB_URL_DIRECTORATES_DETAIL_PARAMETER;

const WEB_URL_SERVICES_TR = 'hizmetler';

const WEB_URL_ANNOUNCEMENTS_TR = 'duyurular';
const WEB_URL_TENDER_ANNOUNCEMENTS_TR = 'sayfalar/ihale/10';

const WEB_URL_NEWS_TR = 'haberler';
const WEB_URL_NEWS_DETAIL_PARAMETER_TR = 'detay';
const WEB_URL_NEWS_DETAIL_TR = WEB_URL_NEWS.'/'.WEB_URL_NEWS_DETAIL_PARAMETER;

const WEB_URL_EVENTS_TR = 'etkinlikler';
const WEB_URL_EVENTS_DETAIL_PARAMETER_TR = 'detay';
const WEB_URL_EVENTS_DETAIL_TR = WEB_URL_EVENTS.'/'.WEB_URL_EVENTS_DETAIL_PARAMETER;

const WEB_URL_PROJECT_CATEGORY_TR = 'proje-kategori';
const WEB_URL_PROJECT_CATEGORY_DETAIL_PARAMETER_TR = 'detay';
const WEB_URL_PROJECT_CATEGORY_DETAIL_TR = WEB_URL_PROJECT_CATEGORY.'/'.WEB_URL_PROJECT_CATEGORY_DETAIL_PARAMETER;

const WEB_URL_PROJECTS_TR = 'projeler';
const WEB_URL_PROJECTS_DETAIL_PARAMETER_TR = 'detay';
const WEB_URL_PROJECTS_DETAIL_TR = WEB_URL_PROJECTS.'/'.WEB_URL_PROJECTS_DETAIL_PARAMETER;

const WEB_URL_GALLERY_TR = 'galeri';
const WEB_URL_GALLERY_CATEGORY_PARAMETER_TR = 'kategori';
const WEB_URL_GALLERY_CATEGORY_TR = WEB_URL_GALLERY.'/'.WEB_URL_GALLERY_CATEGORY_PARAMETER;

const WEB_URL_GALLERY_DETAIL_PARAMETER_TR = 'detay';
const WEB_URL_GALLERY_DETAIL_TR = WEB_URL_GALLERY.'/'.WEB_URL_GALLERY_DETAIL_PARAMETER;

const WEB_URL_VIDEO_GALLERY_TR = 'video-galeri';


/*
 * Admin Links
*/
const ADMIN_URL_LOGIN = 'login';
const ADMIN_URL_LOGIN_AUTH = 'loginAuth';
const ADMIN_URL_LOGOUT = 'logout';
const ADMIN_URL_DASHBOARD = 'dashboard';

const ADMIN_URL_SETTINGS = 'settings';
const ADMIN_URL_GENERAL_SETTINGS = 'general-settings';
const ADMIN_URL_SOCIAL_MEDIA_SETTINGS = 'social-media-settings';
const ADMIN_URL_TRACKING_CODES = 'tracking-codes';
const ADMIN_URL_COUNTRIES = 'countries';
const ADMIN_URL_CITIES = 'cities';
const ADMIN_URL_DISTRICTS = 'districts';
const ADMIN_URL_NEIGHBOURHOODS = 'neighbourhoods';
const ADMIN_URL_MAINTENANCE_MODE = 'maintenance-mode';
const ADMIN_URL_EMAIL_SETTINGS = 'email-settings';
const ADMIN_URL_EMAIL_TEMPLATES = 'email-templates';
const ADMIN_URL_LANGUAGE_MANAGEMENT = 'language-management';
const ADMIN_URL_MANAGER_ACCOUNTS = 'manager-accounts';

const ADMIN_URL_PRESIDENT = 'president';
const ADMIN_URL_PRESIDENT_GENERAL_INFORMATION = 'president-general-information';
const ADMIN_URL_PRESIDENT_CONTENTS = 'president-contents';
const ADMIN_URL_PRESIDENT_GALLERY = 'president-gallery';

const ADMIN_URL_CONTENTS = 'contents';
const ADMIN_URL_PAGES = 'pages';
const ADMIN_URL_CONTRACTS = 'contracts';
const ADMIN_URL_CONTACT_INFORMATION = 'contact-information';
const ADMIN_URL_REFERANCES = 'referances';
const ADMIN_URL_STATISTIC = 'statistics';
const ADMIN_URL_SERVICES = 'services';
const ADMIN_URL_SERVICE_CATEGORIES = 'service_categories';
const ADMIN_URL_MUNICIPAL_COUNCILS = 'municipal-councils';
const ADMIN_URL_COUNCIL_MEMBERS = 'council-members';
const ADMIN_URL_VICE_PRESIDENTS = 'vice-presidents';
const ADMIN_URL_PARLIAMENTARY_AGENDA = 'parliamentary-agenda';
const ADMIN_URL_ACTIVITY_REPORT = 'activity-report';
const ADMIN_URL_STRATEGIC_PLAN_AND_PERFORMANCA = 'strategic-plan-and-performance';
const ADMIN_URL_PLAN_AND_PROGRAM = 'plan-and-program';
const ADMIN_URL_PRESS_RELEASE = 'press-release';
const ADMIN_URL_INTERNAL_CONTROL = 'internal-control';
const ADMIN_URL_DIRECTORATES = 'directorates';
const ADMIN_URL_DIRECTORATE_CATEGORIES = 'directorate-categories';
const ADMIN_URL_NEWS = 'news';
const ADMIN_URL_NEWS_CONTENT = 'news-content';
const ADMIN_URL_ANNOUNCEMENTS = 'announcements';
const ADMIN_URL_EVENTS = 'events';
const ADMIN_URL_EVENTS_CATEGORY = 'events-category';
const ADMIN_URL_EVENTS_CONTENT = 'events-content';
const ADMIN_URL_PROJECTS = 'projects';
const ADMIN_URL_PROJECTS_CATEGORY = 'projects-category';
const ADMIN_URL_PROJECTS_CONTENT = 'projects-content';
const ADMIN_URL_PROJECTS_STATUS = 'projects-status';
const ADMIN_URL_ORGANIZATION_CHART = 'organization-chart';
const ADMIN_URL_POPUP_MODULE = 'popup-module';

const ADMIN_URL_SULTANGAZI = 'sultangazi';
const ADMIN_URL_SULTANGAZI_CONTENTS = 'contents';
const ADMIN_URL_SULTANGAZI_GALLERY = 'gallery';
const ADMIN_URL_SULTANGAZI_VIDEO_GALLERY = 'video-gallery';
const ADMIN_URL_SULTANGAZI_CITY_GUIDE_CATEGORIES = 'city-guide-categories';
const ADMIN_URL_SULTANGAZI_CITY_GUIDE_CONTENTS = 'city-guide-contents';

const ADMIN_URL_MAP_MODULE = 'map-module';
const ADMIN_URL_MAP_CATEGORIES = 'map-categories';
const ADMIN_URL_MAP_LOCATIONS = 'map-locations';

const ADMIN_URL_MULTIMEDIA = 'multimedia';
const ADMIN_URL_MULTIMEDIA_GALLERY_CATEGORIES = 'gallery-categories';
const ADMIN_URL_MULTIMEDIA_GALLERY = 'gallery';
const ADMIN_URL_MULTIMEDIA_VIDEO_GALLERY = 'video-gallery';

const ADMIN_URL_DESIGNS = 'designs';
const ADMIN_URL_DESIGN_SETTINGS = 'design-settings';
const ADMIN_URL_MENU_MANAGEMENT = 'menu-management';
const ADMIN_URL_FAST_MENU_MANAGEMENT = 'fast-menu-management';
const ADMIN_URL_BANNER_MANAGEMENT = 'banner-management';

const ADMIN_URL_FORMS = 'forms';
const ADMIN_URL_CONTACT_REQUESTS = 'contact-requests';
const ADMIN_URL_PRESIDENT_CONTACT_REQUESTS = 'president-contact-requests';

/*
 * File Path
*/
const FILE_PATH_ASSETS = 'assets';
const FILE_PATH_IMAGES = 'img';
const SULTAN_FILE_PATH_IMAGES = 'images';
// Backend sablonunun gorsel klasoru (assets/Backend/images)
const BACKEND_FILE_PATH_IMAGES = 'images';
const FILE_PATH_FLAGS = FILE_PATH_ASSETS.'/'.FILE_PATH_IMAGES.'/flags/';
const FILE_PATH_MAIN_STORAGE = 'storage/';
const FILE_PATH_MAIN_STORAGE_PDF = FILE_PATH_MAIN_STORAGE.'pdf';

const FILE_PATH_MAIN = 'uploads/';

const FILE_PATH_PRESIDENT = FILE_PATH_MAIN.'president';

const FILE_PATH_PROJECT = FILE_PATH_MAIN.'projects';
const FILE_PATH_PROJECT_THUMB = FILE_PATH_PROJECT.'/thumb';
const FILE_PATH_PROJECT_MEDIUM = FILE_PATH_PROJECT.'/medium';
const FILE_PATH_PROJECT_BIG = FILE_PATH_PROJECT.'/big';

const FILE_PATH_NEWS = FILE_PATH_MAIN.'news';
const FILE_PATH_NEWS_GALLERY = FILE_PATH_MAIN.'news-gallery';

const FILE_PATH_COURSES = FILE_PATH_MAIN.'courses';
const FILE_PATH_COURSES_GALLERY = FILE_PATH_MAIN.'courses-gallery';

const FILE_PATH_EVENTS = FILE_PATH_MAIN.'events';
const FILE_PATH_EVENTS_THUMB = FILE_PATH_EVENTS.'/thumb';
const FILE_PATH_EVENTS_BIG = FILE_PATH_EVENTS.'/big';

const FILE_PATH_MANAGER_ACCOUNTS = FILE_PATH_MAIN.'users';
const FILE_PATH_PAGES = FILE_PATH_MAIN.'pages';
const FILE_PATH_CONTACT = FILE_PATH_MAIN.'contact';
const FILE_PATH_GALLERY = FILE_PATH_MAIN.'gallery';
const FILE_PATH_VIDEO_GALLERY = FILE_PATH_MAIN.'video-gallery';
const FILE_PATH_REFERANCES = FILE_PATH_MAIN.'referances';
const FILE_PATH_SERVICES = FILE_PATH_MAIN.'services';
const FILE_PATH_MUNICIPAL_COUNCILS = FILE_PATH_MAIN.'municipal-councils';
const FILE_PATH_COUNCIL_MEMBERS = FILE_PATH_MAIN.'council-members';
const FILE_PATH_VICE_PRESIDENTS = FILE_PATH_MAIN.'vice-presidents';
const FILE_PATH_PARLIAMENTARY_AGENDA = FILE_PATH_MAIN.'parliamentary-agenda';
const FILE_PATH_ACTIVITY_REPORT = FILE_PATH_MAIN.'activity-report';
const FILE_PATH_STRATEGIC_PLAN_AND_PERFORMANCA = FILE_PATH_MAIN.'strategic-plan-and-performance';
const FILE_PATH_PLAN_AND_PROGRAM = FILE_PATH_MAIN.'plan-and-program';
const FILE_PATH_PRESS_RELEASE = FILE_PATH_MAIN.'press-release';
const FILE_PATH_INTERNAL_CONTROL = FILE_PATH_MAIN.'internal-control';
const FILE_PATH_DIRECTORATES = FILE_PATH_MAIN.'directorates';
const FILE_PATH_SULTANGAZI = FILE_PATH_MAIN.'sultangazi';
const FILE_PATH_CITY_GUIDE = FILE_PATH_MAIN.'city-guide';
const FILE_PATH_POPUP = FILE_PATH_MAIN.'popup';
const FILE_PATH_MENU_MANAGEMENT = FILE_PATH_MAIN.'menu';
const FILE_PATH_BANNER_MANAGEMENT = FILE_PATH_MAIN.'banner';

/*
 * Validation
*/
const FORM_EMAIL_MIN_LENGTH = 6;
const FORM_EMAIL_MAX_LENGTH = 100;

const FORM_PASSWORD_MIN_LENGTH = 6;
const FORM_PASSWORD_MAX_LENGTH = 14;

/*
 * Form Parameter
*/
const FORM_ACTIVE_NUMBER = 1;
const FORM_PASSIVE_NUMBER = 2;
const FORM_CHECKBOX_VALUE_NUMBER = 1;

const FORM_ACTIVE_TRUE = 'true';
const FORM_PASSIVE_FALSE = 'false';

const DEFAULT_RECORD_ACTIVE = 1;
const DEFAULT_RECORD_PASSIVE = 0;

const FORM_EMAIL_NOTIFICATION = 1;

/*
 * Form Redirect Parameter
*/
const FORM_BUTTON_REDIRECT_PAGE1 = 1;
const FORM_BUTTON_REDIRECT_PAGE2 = 2;

/*
 * Character Limiter
*/
const GENERAL_SETTINGS_DESCRIPTION_LIMITER = 255;
const WEB_ADDRESS_CHARACTER_LIMITER = 100;

/*
 * Services
*/
const SERVICES_TYPE_1 = 1;
const SERVICES_TYPE_2 = 2;
const SERVICES_TYPE_3 = 3;

/*
 * Banner
*/
const BANNER_TYPE_1 = 1;
const BANNER_TYPE_2 = 2;

/*
 * Image Upload
*/
const IMAGE_UPLOAD_MIME = 'image/jpg,image/jpeg,image/png,image/gif,image/webp,image/svg.xml';
const IMAGE_UPLOAD_SIZE = 2048;
const IMAGE_UPLOAD_QUALITY = 100;

/*
 * File Upload
*/
const FILE_UPLOAD_MIME = 'image/jpg,image/jpeg,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
const FILE_UPLOAD_SIZE = 60480;

/*
 * Export
*/
const EXPORT_PDF_PARAMETER = 'pdf_export';

/*
 * Breadcrumb
*/
const BREADCRUMB_SEPERATOR = ' > ';

/*
 * Percentage
*/
const PERCENTAGE_SYMBOL = '%';
const LANGUAGES_PERCENTAGE_LOCATION_LEFT = 1;
const LANGUAGES_PERCENTAGE_LOCATION_RIGHT = 2;

/*
 * Category Management
*/
const CATEGORY_MANAGEMENT_PARENT_MENU = 0;

/*
 * Map Locations
*/
const MAP_LOCATIONS_TYPE_1 = 1;
const MAP_LOCATIONS_TYPE_2 = 2;

/*
 * Popup Module
*/
const POPUP_TIMING_24_HOURS = 1;
const POPUP_TIMING_1_HOUR = 2;
const POPUP_TIMING_1_WEEK = 3;
const POPUP_TIMING_1_MONTH = 4;
const POPUP_TIMING_AT_EVERY_ENTRY = 5;

const POPUP_DISPLAY_FIELD_DESKTOP = 1;
const POPUP_DISPLAY_FIELD_MOBILE = 2;

const POPUP_TYPE_HTML = 1;
const POPUP_TYPE_IMAGE = 2;

/*
 * Organization Chart
*/
const ORGANIZATION_CHART_PRESIDENT = 1;
const ORGANIZATION_CHART_VICE_PRESIDENTS = 2;
const ORGANIZATION_CHART_DIRECTORATES = 3;
const ORGANIZATION_CHART_TYPE_LINK = 4;

const ORGANIZATION_CHART_LEVEL_1 = 1;
const ORGANIZATION_CHART_LEVEL_2 = 2;
const ORGANIZATION_CHART_LEVEL_3 = 3;

/*
 * Sultangazi Video Gallery Categories
*/
const SULTANGAZI_VIDEO_GALLERY_CATEGORY_1 = 1;
const SULTANGAZI_VIDEO_GALLERY_CATEGORY_2 = 2;

/*
 * Menu Management
*/
const MENU_MANAGEMENT_PARENT_MENU = 0;

const MENU_MANAGEMENT_TYPE_PAGES = 1;
const MENU_MANAGEMENT_TYPE_CONTRACTS = 3;
const MENU_MANAGEMENT_TYPE_SERVICES = 5;
const MENU_MANAGEMENT_TYPE_PROJECTS = 6;
const MENU_MANAGEMENT_TYPE_SULTANGAZI_CONTENTS = 7;
const MENU_MANAGEMENT_TYPE_PRESIDENT_CONTENTS = 8;
const MENU_MANAGEMENT_TYPE_LINK = 4;

const MENU_MANAGEMENT_TARGET_SAME_WINDOW = '_self';
const MENU_MANAGEMENT_TARGET_NEW_WINDOW = '_blank';

const MENU_MANAGEMENT_LOCATION_PARENT_MENU = 1;
const MENU_MANAGEMENT_LOCATION_FOOTER_MENU = 2;
const MENU_MANAGEMENT_LOCATION_HEADER_MENU = 3;

const MENU_MANAGEMENT_TEMPLATE_SUB_MENU_1 = 1;
const MENU_MANAGEMENT_TEMPLATE_SUB_MENU_2 = 2;
const MENU_MANAGEMENT_TEMPLATE_SUB_MENU_3 = 3;

/*
 * E-Mail Templates
*/
const EMAIL_TEMPLATES_CONTACT_REQUESTS = 1;
const EMAIL_TEMPLATES_PRESIDENT_CONTACT_REQUESTS = 2;

/*
 * Default ID
*/
const SETTING_ID = 1;
const SOCIAL_MEDIA_ID = 1;
const TRACKING_CODE_ID = 1;
const DESIGN_SETTING_ID = 1;
const PRESIDENT_GENERAL_INFORMATION_ID = 1;

// Contact Requests
const TMP_CONTACT_REQUESTS_NAME = '{name}';
const TMP_CONTACT_REQUESTS_SURNAME = '{surname}';
const TMP_CONTACT_REQUESTS_TELEPHONE = '{telephone}';
const TMP_CONTACT_REQUESTS_EMAIL = '{email}';
const TMP_CONTACT_REQUESTS_MESSAGE = '{message}';
const TMP_CONTACT_REQUESTS_DATE = '{date}';

// Offers
const TMP_OFFERS_NAME = '{name}';
const TMP_OFFERS_SURNAME = '{surname}';
const TMP_OFFERS_TELEPHONE = '{telephone}';
const TMP_OFFERS_EMAIL = '{email}';
const TMP_OFFERS_MESSAGE = '{message}';
const TMP_OFFERS_DATE = '{date}';

// General Parameters
const TMP_GENERAL_SITE_TELEPHONE = '{SiteTelephone}';
const TMP_GENERAL_SITE_EMAIL = '{SiteEmail}';
