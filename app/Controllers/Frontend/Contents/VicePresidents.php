<?php
namespace App\Controllers\Frontend\Contents;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Contents\VicePresidentsModel;
use App\Models\Frontend\Contents\CorporateModel;

class VicePresidents extends BaseController {

	protected $VicePresidentsModel;
	protected $CorporateModel;
	protected $folder;

	public function __construct() {
		$this->VicePresidentsModel = new VicePresidentsModel();
		$this->CorporateModel = new CorporateModel();
		$this->folder = 'contents';
	}

	public function index() {

		// Segment
		$segment = $this->request->getUri()->getTotalSegments() > 1 ? $this->request->getUri()->getSegment(2) : $this->request->getUri()->getSegment(1);

		return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/contents/vice-presidents.html', [
			'head' => [
				'title' => lang('WebVicePresidents.title'),
				'keywords' => $this->settings->site_keywords,
				'description' => $this->settings->site_description
			],
			'list' => [
				'vice_presidents' => $this->allVicePresident(),
				'left_menu' => $this->CorporateModel->leftMenuSlugInfoModel($segment, $this->defaultLangId)
			],
			'folder' => $this->folder
		]);
	}

	public function detail($slug, $vice_president_id) {
		$sql = $this->VicePresidentsModel->vicePresidentInfoModel($slug, $vice_president_id, $this->defaultLangId);
		if (isNotNull($sql)) {

			// Segment
			$segment = $this->request->getUri()->getTotalSegments() > 4 ? $this->request->getUri()->getSegment(2) : $this->request->getUri()->getSegment(1);

			return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/'.$this->folder.'/vice-presidents-detail.html', [
				'head' => [
					'title' => $sql->vice_president_name.' '.$sql->vice_president_surname,
					'keywords' => $this->settings->site_keywords,
					'description' => $this->settings->site_description
				],
				'result' => [
					'vice_president_name' => $sql->vice_president_name,
					'vice_president_surname' => $sql->vice_president_surname,
					'vice_president_telephone' => $sql->vice_president_telephone,
					'vice_president_email_address' => $sql->vice_president_email_address,
					'vice_president_sub_title' => $sql->vice_president_sub_title,
					'vice_president_description' => $sql->vice_president_description,
					'image' => [
						'normal' => $sql->vice_president_image,
						'base' => $this->contentImageUrl(FILE_PATH_VICE_PRESIDENTS, $sql->vice_president_image)
					]
				],
				'list' => [
					'directorates' => isNotNull($sql->directorates_id) ? $this->VicePresidentsModel->directoratesListModel($sql->directorates_id, $this->defaultLangId) : NULL,
					'left_menu' => $this->CorporateModel->leftMenuSlugInfoModel($segment, $this->defaultLangId)
				],
				'folder' => $this->folder,
				'PARAMETER' => [
					'WEB_URL_VICE_PRESIDENTS' => WEB_URL_VICE_PRESIDENTS,
					'WEB_URL_DIRECTORATES_DETAIL' => WEB_URL_DIRECTORATES_DETAIL
				]
			]);

		} else {
			return redirect()->to('404');
		}
	}

	public function allVicePresident() {
		$array = [];
		$sql = $this->VicePresidentsModel->vicePresidentListModel($this->defaultLangId);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'vice_president_name' => $row->vice_president_name,
					'vice_president_surname' => $row->vice_president_surname,
					'vice_president_sub_title' => $row->vice_president_sub_title,
					'vice_president_image' => [
						'normal' => $row->vice_president_image,
						'base' => $this->contentImageUrl(FILE_PATH_VICE_PRESIDENTS, $row->vice_president_image)
					],
					'link' => web_url(WEB_URL_VICE_PRESIDENTS_DETAIL.'/'.$row->vice_president_slug.'/'.$row->vice_president_id)
				];
			}
		}

		return $array;
	}
}
