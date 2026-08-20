<?php
namespace App\Controllers\Frontend\Contents;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Contents\DirectoratesModel;
use App\Models\Frontend\Contents\CorporateModel;

class Directorates extends BaseController {

	protected $DirectoratesModel;
	protected $CorporateModel;

	public function __construct() {
		$this->DirectoratesModel = new DirectoratesModel();
		$this->CorporateModel = new CorporateModel();
		$this->folder = 'contents';
	}

	public function index() {

		// Segment
		$segment = $this->request->getUri()->getTotalSegments() > 1 ? $this->request->getUri()->getSegment(2) : $this->request->getUri()->getSegment(1);

		return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/contents/directorates.html', [
			'head' => [
				'title' => lang('WebDirectorates.title'),
				'keywords' => $this->settings->site_keywords,
				'description' => $this->settings->site_description
			],
			'list' => [
				'directorates' => $this->allDirectorates(),
				'left_menu' => $this->CorporateModel->leftMenuSlugInfoModel($segment, $this->defaultLangId)
			],
			'folder' => $this->folder
		]);
	}

	public function detail($slug, $directorates_id) {
		$sql = $this->DirectoratesModel->directoratesInfoModel($slug, $directorates_id, $this->defaultLangId);
		if (isNotNull($sql)) {

			// Segment
			$segment = $this->request->getUri()->getTotalSegments() > 1 ? $this->request->getUri()->getSegment(1) : $this->request->getUri()->getSegment(2);

			return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/'.$this->folder.'/directorates-detail.html', [
				'head' => [
					'title' => $sql->directorates_name,
					'keywords' => $this->settings->site_keywords,
					'description' => $this->settings->site_description
				],
				'result' => [
					'directorates_id' => $sql->directorates_id,
					'directorates_name' => $sql->directorates_name,
					'person' => [
						'name' => $sql->directorates_person_name,
						'surname' => $sql->directorates_person_surname,
						'sub_title' => $sql->directorates_person_sub_title,
						'image' => [
							'normal' => $sql->directorates_person_image,
							'base' => $this->contentImageUrl(FILE_PATH_DIRECTORATES, $sql->directorates_person_image)
						]
					],
					'contact' => [
						'telephone' => $sql->directorates_telephone,
						'fax' => $sql->directorates_fax,
						'email_address' => $sql->directorates_email_address
					]
				],
				'list' => [
					'directorates' => $this->allDirectorates(),
					'files' => $this->files($sql->directorates_id),
					'announcements' => $this->DirectoratesModel->announcementsListModel($sql->directorates_id, $this->defaultLangId),
					'news' => $this->DirectoratesModel->newsListModel($sql->directorates_id, $this->defaultLangId),
					'left_menu' => $this->CorporateModel->leftMenuSlugInfoModel($segment, $this->defaultLangId)
				],
				'folder' => $this->folder,
				'PARAMETER' => [
					'WEB_URL_VICE_PRESIDENTS' => WEB_URL_VICE_PRESIDENTS,
					'WEB_URL_DIRECTORATES' => WEB_URL_DIRECTORATES,
					'WEB_URL_ANNOUNCEMENTS' => WEB_URL_ANNOUNCEMENTS,
					'WEB_URL_NEWS_DETAIL' => WEB_URL_NEWS_DETAIL
				]
			]);

		} else {
			return redirect()->to('404');
		}
	}

	public function allDirectorates() {
		$array = [];
		$sql = $this->DirectoratesModel->directoratesListModel($this->defaultLangId);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'directorates_id' => $row->directorates_id,
					'directorates_name' => $row->directorates_name,
					'icon' => [
						'normal' => $row->directorates_icon,
						'base' => $this->contentImageUrl(FILE_PATH_DIRECTORATES, $row->directorates_icon)
					],
					'link' => web_url(WEB_URL_DIRECTORATES_DETAIL.'/'.$row->directorates_slug.'/'.$row->directorates_id)
				];
			}
		}

		return $array;
	}

	public function files($directorates_id) {
		$array = [];
		$sql = $this->DirectoratesModel->directorateCategoriesListModel($directorates_id, $this->defaultLangId);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {

				// Files
				$files = [];
				$file_sql = $this->DirectoratesModel->directoratesFileListModel($directorates_id, $row->directorate_category_id, $this->defaultLangId);
				if (isNotNull($file_sql)) {
					foreach ($file_sql as $value) {
						$files[] = [
							'file_name' => $value->directorates_file_name,
							'file' => isNotNull($value->directorates_file) ? base_url(FILE_PATH_DIRECTORATES.'/'.$value->directorates_file) : NULL
						];
					}
				}

				$array[] = [
					'category_name' => $row->directorate_category_name,
					'files' => $files
				];
			}
		}

		return $array;
	}
}
