<?php
namespace App\Controllers\Frontend\Services;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Services\ServicesModel;

class Services extends BaseController {

	protected $ServicesModel;
	protected $folder;

	public function __construct() {
		$this->ServicesModel = new ServicesModel();
		$this->folder = 'services';
	}

	public function index() {
		return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/'.$this->folder.'/services.html', [
			'head' => [
				'title' => lang('WebServices.title'),
				'keywords' => $this->settings->site_keywords,
				'description' => $this->settings->site_description
			],
			'result' => [
				'default' => $this->servicesDefault()
			],
			'list' => [
				'all_services' => $this->allServices()
			],
			'PARAMETER' => [
				'WEB_URL_SERVICES' => WEB_URL_SERVICES
			]
		]);

	}

	public function detail($slug, $service_id) {
		// die();
		$sql = $this->ServicesModel->serviceInfoModel($slug, $service_id, $this->defaultLangId);
		if (isNotNull($sql)) {
			return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/'.$this->folder.'/services.html', [
				'head' => [
					'title' => isNotNull($sql->service_meta_title) ? $sql->service_meta_title : $sql->service_name,
					'keywords' => isNotNull($sql->service_meta_keywords) ? $sql->service_meta_keywords : $this->settings->site_keywords,
					'description' => isNotNull($sql->service_meta_description) ? $sql->service_meta_description : $this->settings->site_description
				],
				'result' => [
					'service_id' => $sql->service_id,
					'service_name' => $sql->service_name,
					'image' => [
						'normal' => $sql->service_image,
						'base' => base_url(FILE_PATH_SERVICES.'/'.$sql->service_image)
					],
					'service_description' => $sql->service_description
				],
				'list' => [
					'all_services' => $this->allServices()
				],
				'PARAMETER' => [
					'WEB_URL_SERVICES' => WEB_URL_SERVICES
				]
			]);

		} else {
			return redirect()->to('404');
		}
	}

	public function allServices() {
		$array = [];
		$sql = $this->ServicesModel->serviceListModel($this->defaultLangId);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'service_id' => $row->service_id,
					'service_name' => $row->service_name,
					'service_link' => web_url(WEB_URL_SERVICES.'/'.$row->service_slug.'/'.$row->service_id)
				];
			}
		}

		return $array;
	}

	public function servicesDefault() {
		$array = [];
		$sql = $this->ServicesModel->serviceDefaultModel($this->defaultLangId);
		if (isNotNull($sql)) {
			$array = [
				'service_id' => $sql->service_id,
				'service_name' => $sql->service_name,
				'image' => [
					'normal' => $sql->service_image,
					'base' => $this->imageControl(FILE_PATH_SERVICES, $sql->service_image)
				],
				'service_description' => $sql->service_description
			];
		}

		return $array;
	}
}
