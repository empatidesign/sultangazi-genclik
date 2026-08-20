<?php
namespace App\Controllers\Frontend\Projects;
use App\Controllers\Frontend\BaseController;
use App\Models\Frontend\Contents\CorporateModel;
use CodeIgniter\Controller;

use App\Models\Frontend\Projects\ProjectsModel;

class Projects extends BaseController {

	protected $ProjectsModel;
	protected $CorporateModel;
	protected $folder;

	public function __construct() {
		$this->ProjectsModel = new ProjectsModel();
		$this->CorporateModel = new CorporateModel();
		$this->folder = 'projects';
	}

	public function index() {
		// Search
		$category = @$this->request->getGet('category') ?? '';
		$category_data = @$this->ProjectsModel->projectCategorySlugInfoModel(@$category, $this->defaultLangId) ;//?? (object) ['project_category_id' => NULL, 'project_category_name' => NULL];

		$neighbourhood = $this->sanitizeParam($this->request->getGet('neighbourhood'));
		$status = $this->sanitizeParam($this->request->getGet('status'));

		// Pagination
		$pager = service('pager');
		$page = isNotNull($this->request->getVar('page')) ? $this->request->getVar('page') : 1;
		$per_page = $this->designSettings->paging_count;
		$page_start = $page ? ($page * $per_page) - $per_page : 1;
		$total = count($this->allProjects(@$category_data->project_category_id, $neighbourhood, $status));
		$pagination = $pager->makeLinks($page, $per_page, $total, 'classic');

		return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/'.$this->folder.'/projects.html', [
			'head' => [
				'title' => lang('WebProjects.title'),
				'keywords' => $this->settings->site_keywords,
				'description' => $this->settings->site_description
			],
			'list' => [
				'all' => $this->allProjects(@$category_data->project_category_id, $neighbourhood, $status, $page_start, $per_page),
				'categories' => $this->ProjectsModel->projectCategoryListModel($this->defaultLangId),
				'neighbourhoods' => $this->ProjectsModel->neighbourhoodListModel(),
				'status' => $this->ProjectsModel->projectStatusListModel($this->defaultLangId),
			],
			'selected' => [
				'category' => $category_data
			],
			'pagination' => [
				'list' => $pagination
			],
			'folder' => $this->folder,
			'PARAMETER' => [
				'WEB_URL_PROJECTS' => WEB_URL_PROJECTS,
				'WEB_URL_PROJECTS_DETAIL' => WEB_URL_PROJECTS_DETAIL
			]
		]);

	}

	/**
	 * Sanitizes and validates a parameter to prevent path traversal attacks
	 * 
	 * @param mixed $param The parameter to sanitize
	 * @return int|null Returns only the numeric value or null
	 */
	private function sanitizeParam($param) {
		if (empty($param)) {
			return null;
		}
		
		// Strip any non-numeric characters to prevent path traversal
		$sanitized = filter_var($param, FILTER_VALIDATE_INT);
		
		// Return only if it's a valid integer, otherwise return null
		return ($sanitized !== false) ? $sanitized : null;
	}

	public function detail($slug, $project_id) {
		$sql = $this->ProjectsModel->projectInfoModel($slug, $project_id, $this->defaultLangId);
		if (isNotNull($sql)) {
			$segment = $this->request->getUri()->getSegment(1);

			return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/'.$this->folder.'/projects-detail.html', [
				'page_name' => 'projects-detail',
				'head' => [
					'title' => isNotNull($sql->project_meta_title) ? $sql->project_meta_title : $sql->project_name,
					'keywords' => isNotNull($sql->project_meta_keywords) ? $sql->project_meta_keywords : $this->settings->site_keywords,
					'description' => isNotNull($sql->project_meta_description) ? $sql->project_meta_description : $this->settings->site_description
				],
				'result' => [
					'project_name' => $sql->project_name,
					'project_category_name' => $sql->project_category_name,
					'project_category_id' => $sql->project_category_id,
					'project_status_name' => $sql->project_status_name,
					'project_description' => $sql->project_description,
					'project_location_address' => $sql->project_location_address,
					'project_responsible' => $sql->project_responsible,
					'project_location_telephone' => $sql->project_location_telephone,
					'project_location_map' => $sql->project_location_map
				],
				'list' => [
					'images' => $this->images($project_id),
					'left_menu' => $this->CorporateModel->leftMenuSlugInfoModel($segment, $this->defaultLangId, NULL, NULL, $sql->project_id)
				],
				'folder' => $this->folder,
				'PARAMETER' => [
					'WEB_URL_PROJECTS' => WEB_URL_PROJECTS,
					'WEB_URL_NEWS' => WEB_URL_NEWS,
					'WEB_URL_EVENTS' => WEB_URL_EVENTS,
					'WEB_URL_ANNOUNCEMENTS' => WEB_URL_ANNOUNCEMENTS
				]
			]);

		} else {
			return redirect()->to('404');
		}
	}

	public function allProjects($category = NULL, $neighbourhood = NULL, $status = NULL, $page_start = NULL, $per_page = NULL) {
		$array = [];
		$sql = $this->ProjectsModel->projectListModel($category, $neighbourhood, $status, $this->defaultLangId, $page_start, $per_page);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'project_id' => $row->project_id,
					'project_name' => $row->project_name,
					'project_category_name' => $row->project_category_name,
					'project_status_name' => $row->project_status_name,
					'project_location_address' => $row->project_location_address,
					'project_lat_coordinate' => $row->project_lat_coordinate,
					'project_long_coordinate' => $row->project_long_coordinate,
					'project_responsible' => $row->project_responsible,
					'project_location_telephone' => $row->project_location_telephone,
					'project_slug' => $row->project_slug,
					'image' => [
						'base' => $this->imageControl(FILE_PATH_PROJECT_MEDIUM, $row->project_image),
						'list' => array_slice($this->images($row->project_id), 0, 6)
					],
					'dates' => [
						'start' => $row->project_start_date != '0000-00-00' ? dateFormat($row->project_start_date, 'd/m/Y') : NULL,
						'end' => $row->project_end_date != '0000-00-00' ? dateFormat($row->project_end_date, 'd/m/Y') : NULL
					]
				];
			}
		}

		return $array;
	}

	public function images($project_id) {
		$array = [];
		$sql = $this->ProjectsModel->projectImageListModel($project_id);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'image' => [
						'normal' => $row->project_image,
						'format' => $this->imageControl(FILE_PATH_PROJECT_BIG, $row->project_image)
					]
				];
			}
		}

		return $array;
	}
}
