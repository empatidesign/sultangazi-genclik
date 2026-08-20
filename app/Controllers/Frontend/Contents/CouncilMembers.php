<?php
namespace App\Controllers\Frontend\Contents;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Contents\CouncilMembersModel;
use App\Models\Frontend\Contents\CorporateModel;

class CouncilMembers extends BaseController {

	protected $CouncilMembersModel;
	protected $CorporateModel;

	public function __construct() {
		$this->CouncilMembersModel = new CouncilMembersModel();
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
		$total = count($this->allCouncilMembers());
		$pagination = $pager->makeLinks($page, $per_page, $total, 'classic');

		return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/contents/council-members.html', [
			'head' => [
				'title' => lang('WebCouncilMembers.title'),
				'keywords' => $this->settings->site_keywords,
				'description' => $this->settings->site_description
			],
			'list' => [
				'council_members' => $this->allCouncilMembers($page_start, $per_page),
				'left_menu' => $this->CorporateModel->leftMenuSlugInfoModel($segment, $this->defaultLangId)
			],
			'pagination' => [
				'list' => $pagination
			],
			'folder' => $this->folder
		]);
	}

	public function allCouncilMembers($page_start = NULL, $per_page = NULL) {
		$array = [];
		$sql = $this->CouncilMembersModel->councilMembersListModel($this->defaultLangId, $page_start, $per_page);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'council_member_name' => $row->council_member_name,
					'council_member_surname' => $row->council_member_surname,
					'council_member_sub_title' => $row->council_member_sub_title,
					'council_member_image' => [
						'normal' => $row->council_member_image,
						'base' => $this->contentImageUrl(FILE_PATH_COUNCIL_MEMBERS, $row->council_member_image)
					]
				];
			}
		}

		return $array;
	}
}
