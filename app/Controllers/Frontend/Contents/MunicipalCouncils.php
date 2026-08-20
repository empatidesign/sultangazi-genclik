<?php
namespace App\Controllers\Frontend\Contents;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Contents\MunicipalCouncilsModel;
use App\Models\Frontend\Contents\CorporateModel;

class MunicipalCouncils extends BaseController {

	protected $MunicipalCouncilsModel;
	protected $CorporateModel;
	protected $folder;

	public function __construct() {
		$this->MunicipalCouncilsModel = new MunicipalCouncilsModel();
		$this->CorporateModel = new CorporateModel();
		$this->folder = 'contents';
	}

	public function index() {

		// Segment
		$segment = $this->request->getUri()->getTotalSegments() > 1 ? $this->request->getUri()->getSegment(2) : $this->request->getUri()->getSegment(1);

		// Pagination
		$pager = service('pager');
		$page = isNotNull($this->request->getVar('page')) ? $this->request->getVar('page') : 1;
		$per_page = $this->designSettings->paging_count;
		$page_start = $page ? ($page * $per_page) - $per_page : 1;
		$total = count($this->allMunicipalCouncils());
		$pagination = $pager->makeLinks($page, $per_page, $total, 'classic');

		return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/contents/municipal-councils.html', [
			'head' => [
				'title' => lang('WebMunicipalCouncils.title'),
				'keywords' => $this->settings->site_keywords,
				'description' => $this->settings->site_description
			],
			'list' => [
				'municipal_councils' => $this->allMunicipalCouncils($page_start, $per_page),
				'left_menu' => $this->CorporateModel->leftMenuSlugInfoModel($segment, $this->defaultLangId)
			],
			'pagination' => [
				'list' => $pagination
			],
			'folder' => $this->folder
		]);
	}

	public function allMunicipalCouncils($page_start = NULL, $per_page = NULL) {
		$array = [];
		$sql = $this->MunicipalCouncilsModel->municipalCouncilsListModel($this->defaultLangId, $page_start, $per_page);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'municipal_council_name' => $row->municipal_council_name,
					'municipal_council_surname' => $row->municipal_council_surname,
					'municipal_council_sub_title' => $row->municipal_council_sub_title,
					'municipal_council_image' => [
						'normal' => $row->municipal_council_image,
						'base' => $this->contentImageUrl(FILE_PATH_MUNICIPAL_COUNCILS, $row->municipal_council_image)
					]
				];
			}
		}

		return $array;
	}
}
