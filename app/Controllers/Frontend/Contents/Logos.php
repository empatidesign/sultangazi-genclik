<?php
namespace App\Controllers\Frontend\Contents;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Contents\CorporateModel;

class Logos extends BaseController {

	protected $CorporateModel;
	protected $folder;

	public function __construct() {
		$this->CorporateModel = new CorporateModel();
		$this->folder = 'contents';
	}

	public function index() {

		// Segment
		$segment = $this->request->getUri()->getTotalSegments() > 1 ? $this->request->getUri()->getSegment(2) : $this->request->getUri()->getSegment(1);

		return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/contents/logos.html', [
			'head' => [
				'title' => lang('WebLogos.title'),
				'keywords' => $this->settings->site_keywords,
				'description' => $this->settings->site_description
			],
			'list' => [
				'left_menu' => $this->CorporateModel->leftMenuSlugInfoModel($segment, $this->defaultLangId)
			],
			'folder' => $this->folder,
			'PARAMETER' => [
				'FILE_PATH_MAIN_STORAGE_PDF' => FILE_PATH_MAIN_STORAGE_PDF
			]
		]);
	}
}
