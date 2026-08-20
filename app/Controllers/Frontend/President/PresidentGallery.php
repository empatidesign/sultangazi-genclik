<?php
namespace App\Controllers\Frontend\President;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\President\PresidentGalleryModel;
use App\Models\Frontend\Contents\CorporateModel;

class PresidentGallery extends BaseController {

	protected $PresidentGalleryModel;
	protected $CorporateModel;
	protected $folder;

	public function __construct() {
		$this->PresidentGalleryModel = new PresidentGalleryModel();
		$this->CorporateModel = new CorporateModel();
		$this->folder = 'contents';
	}

	public function index() {
		// Segment
		$segment = $this->request->getUri()->getTotalSegments() > 2 ? $this->request->getUri()->getSegment(2).'/'.$this->request->getUri()->getSegment(3) : $this->request->getUri()->getSegment(1).'/'.$this->request->getUri()->getSegment(2);

		return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/president/president-gallery.html', [
			'page_name' => 'president-gallery',
			'head' => [
				'title' => lang('WebPresident.gallery'),
				'keywords' => $this->settings->site_keywords,
				'description' => $this->settings->site_description
			],
			'result' => [
				'president' => [
					'informations' => $this->informations()
				]
			],
			'list' => [
				'left_menu' => $this->CorporateModel->leftMenuSlugInfoModel($segment, $this->defaultLangId),
				'gallery' => $this->allGallery()
			],
			'folder' => $this->folder
		]);
	}

	public function informations() {
		$data = [];
		$sql = $this->general->getPresidentGeneralInformationsModel($this->defaultLangId);
		if (isNotNull($sql)) {
			$data = [
				'president_name_surname' => $sql->president_name_surname,
        'president_sub_title' => $sql->president_general_information_sub_title,
				'president_image' => [
					'base' => isNotNull($sql->president_image) ? $this->sultanImageControl(FILE_PATH_PRESIDENT, $sql->president_image) : NULL
				],
				'president_facebook' => $sql->president_facebook,
				'president_twitter' => $sql->president_twitter,
				'president_instagram' => $sql->president_instagram,
				'president_youtube' => $sql->president_youtube
			];
		}

		return $data;
	}

	public function allGallery() {
		$array = [];
		$sql = $this->PresidentGalleryModel->presidentGalleryListModel();
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'president_gallery_image' => $this->sultanImageControl(FILE_PATH_GALLERY, $row->president_gallery_image)
				];
			}
		}

		return $array;
	}
}
