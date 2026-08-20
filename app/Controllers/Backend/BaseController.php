<?php
namespace App\Controllers\Backend;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\I18n\Time;
use Kenjis\CI4Twig\Twig;
use WebPConvert\Convert\Exceptions\ConversionFailedException;
use WebPConvert\WebPConvert;

use App\Models\Backend\GeneralModel;
use App\Libraries\ImageMoo;

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
class BaseController extends Controller {
  /**
   * Instance of the main Request object.
   *
   * @var CLIRequest|IncomingRequest
   */
  protected $request;
  protected $BACKEND_TEMPLATE_PATH;
  protected $general;
  protected $defaultLangId;
  protected $defaultLangCode;
  protected $langData;
  protected $designSettings;
  protected $settings;
  protected $twig;

  /**
   * An array of helpers to be loaded automatically upon
   * class instantiation. These helpers will be available
   * to all other controllers that extend BaseController.
   *
   * @var array
   */
  protected $helpers = ['session', 'form', 'text', 'html', 'format', 'date', 'functions', 'request', 'response', 'datatable'];

  /**
   * Constructor.
   */
  public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger) {
    // Do Not Edit This Line
    parent::initController($request, $response, $logger);

    // Timezone
		date_default_timezone_set(Time::now()->getTimezoneName());

    // Backend Path
		$this->BACKEND_TEMPLATE_PATH = $this->backendTemplatePath();

    // Model
		$this->general = new GeneralModel();

    // Language Datas
		$lang_data = $this->languageData();
		$this->defaultLangId = $lang_data[0];
		$this->defaultLangCode = $lang_data[1];

    // Designs
		$this->designSettings = $this->general->getDesignSettingsModel();

    // Settings
		$this->settings = $this->general->getSettingsModel($this->defaultLangId);

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
   * Backend Template Path
   */
	public function backendTemplatePath() {
		return Backend;
	}

  /**
   * Language Data
   */
	public function languageData() {
		$this->langData = $this->general->languagesDefaultModel();
		if (isNotNull($this->langData)) {
			return [$this->langData[0], $this->langData[1]];
		} else {
				echo lang('AdminSettings.languageManagement.alert.systemError');
				exit();
		}
	}

  /**
   * Twig
   */
	public function Twig() {
		$twigConfig = [
			'functions_safe' => ['doctype', 'lang', 'form', 'str_repeat', 'link_tag', 'script_tag', 'monthName']
		];

		$this->twig = new Twig($twigConfig);

    $this->twig->addGlobal('BACKEND_URL', BACKEND_URL);
		$this->twig->addGlobal('BACKEND_TEMPLATE_PATH', $this->BACKEND_TEMPLATE_PATH);
    $this->twig->addGlobal('SESSION_USER_NAME', session()->get('admin_user_name'));
    $this->twig->addGlobal('SESSION_USER_ISLOGGEDIN', session()->get('adminIsLoggedIn'));
    $this->twig->addGlobal('settings', $this->settings);
    $this->twig->addGlobal('design', $this->designSettings);
    $this->twig->addGlobal('languages', $this->general->languagesListModel());
    $this->twig->addGlobal('DEFAULT_LANGUAGES_ID', $this->defaultLangId);
    $this->twig->addGlobal('DEFAULT_LANGUAGES_CODE', $this->defaultLangCode);
    $this->twig->addGlobal('FLASH_DATA_MESSAGE_SUCCESS', session()->getFlashdata('flashDataMessageSuccess'));
    $this->twig->addGlobal('FLASH_DATA_MESSAGE_ERROR', session()->getFlashdata('flashDataMessageError'));
    $this->twig->addGlobal('FILE_PATH_ASSETS', FILE_PATH_ASSETS);
    $this->twig->addGlobal('FILE_PATH_IMAGES', FILE_PATH_IMAGES);
    $this->twig->addGlobal('FILE_PATH_MAIN', FILE_PATH_MAIN);
    $this->twig->addGlobal('FORM_ACTIVE_NUMBER', FORM_ACTIVE_NUMBER);
    $this->twig->addGlobal('FORM_PASSIVE_NUMBER', FORM_PASSIVE_NUMBER);
    $this->twig->addGlobal('FORM_CHECKBOX_VALUE_NUMBER', FORM_CHECKBOX_VALUE_NUMBER);
    $this->twig->addGlobal('FORM_ACTIVE_TRUE', FORM_ACTIVE_TRUE);
		$this->twig->addGlobal('FORM_PASSIVE_FALSE', FORM_PASSIVE_FALSE);
    $this->twig->addGlobal('FORM_PASSWORD_MIN_LENGTH', FORM_PASSWORD_MIN_LENGTH);
    $this->twig->addGlobal('FORM_PASSWORD_MAX_LENGTH', FORM_PASSWORD_MAX_LENGTH);
    $this->twig->addGlobal('FORM_BUTTON_REDIRECT_PAGE1', FORM_BUTTON_REDIRECT_PAGE1);
    $this->twig->addGlobal('FORM_BUTTON_REDIRECT_PAGE2', FORM_BUTTON_REDIRECT_PAGE2);
    $this->twig->addGlobal('ADMIN_URL_LOGOUT', ADMIN_URL_LOGOUT);
    $this->twig->addGlobal('ADMIN_URL_SETTINGS', ADMIN_URL_SETTINGS);
    $this->twig->addGlobal('ADMIN_URL_GENERAL_SETTINGS', ADMIN_URL_GENERAL_SETTINGS);
    $this->twig->addGlobal('MAP_LOCATIONS_TYPE_1', MAP_LOCATIONS_TYPE_1);
    $this->twig->addGlobal('ADMIN_MENU', $this->general->adminMenuModel($this->defaultLangId));
	}

  /**
   * Upload Single File
	 * @param $file
	 * @param $path
	 * @param $name
	 * @param $width
	 * @param $height
   */
	public function uploadSingleFile(string $file, string $path, string $name, int $width = NULL, int $height = NULL) {
		try {

			$info = \Config\Services::image()
				->withFile($file)
				->save($path.'/'.$name);

			// Crop
			if ($width && $height) {
				$this->imageCrop($path.'/'.$name, $width, $height);
			}

			// Webp
			if (fileExtension($path.'/'.$name) !== 'gif') { // not gif
				//$this->createImageWebp($path, $name);
			}

		} catch (CodeIgniter\Images\ImageException $e) {
			return $e->getMessage();
		}
	}

	/**
   * Image Crop
	 * @param $path
	 * @param $width
	 * @param $height
   */
	public function imageCrop(string $path, int $width, int $height) {
		$imageMoo = new ImageMoo();
		$imageMoo->load($path)->resize($width, $height, TRUE)->save_pa('', '', TRUE);
	}

	/**
   * Create Image Webp
	 * @param $path
	 * @param $name
	 * @throws ConversionFailedException
   */
	public function createImageWebp(string $path, string $name) {
		if (fileExtension($path.'/'.$name) !== 'gif') { // not gif
			$webp_image = $path.'/'.pathinfo($name, PATHINFO_FILENAME).'.webp';
			if (!file_exists($webp_image)) {
				WebPConvert::convert($path.'/'.$name, $webp_image, [
					'quality' => 'auto',
					'max-quality' => IMAGE_UPLOAD_QUALITY,
					'converters' => ['cwebp', 'gd', 'imagick', 'wpc', 'ewww']
				]);
			}
		}
	}
}
