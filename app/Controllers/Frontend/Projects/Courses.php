<?php
namespace App\Controllers\Frontend\Projects;
use App\Controllers\Frontend\BaseController;
use App\Models\Frontend\Contents\CorporateModel;
use App\Models\Frontend\Projects\CourseModel;
use CodeIgniter\Controller;

class Courses extends BaseController {

	protected $CoursesModel;
	protected $CorporateModel;
	protected $folder;

	public function __construct() {
		$this->CoursesModel = new CourseModel();
		$this->CorporateModel = new CorporateModel();
		$this->folder = 'courses';
	}

	public function index($slug = NULL, $courses_id = NULL) {
		$slug = esc($slug); 
		
		if (isNotNull($courses_id) && (!is_numeric($courses_id) || $courses_id <= 0)) {
			return redirect()->to('404');
		}
		$courses_id = (int) $courses_id; 
		
		$sql = $this->CoursesModel->coursesInfoModel($slug, $courses_id, $this->defaultLangId);
		if (isNotNull($sql)) {
			$segment = $this->request->getUri()->getSegment(1);

			return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/'.$this->folder.'/detail.html', [
				'page_name' => 'courses-detail',
				'head' => [
					'title' => isNotNull($sql->courses_meta_title) ? $sql->courses_meta_title : $sql->courses_name,
					'keywords' => isNotNull($sql->courses_meta_keywords) ? $sql->courses_meta_keywords : $this->settings->site_keywords,
					'description' => isNotNull($sql->courses_meta_description) ? $sql->courses_meta_description : $this->settings->site_description
				],
				'result' => [
					'courses_name' => $sql->courses_name,
					'created_date' => substr(dateFormat($sql->courses_created_date, 'd/m/Y'), 0, 10),
					'image' => [
						'format' => $this->imageControl(FILE_PATH_NEWS, $sql->courses_image)
					],
					'courses_description' => $sql->courses_description
				],
				'list' => [
					'paragraphs' => $this->paragraphs($courses_id),
					'images' => $this->images($courses_id),
					'left_menu' => $this->CorporateModel->leftMenuSlugInfoModel($segment, $this->defaultLangId, NULL, NULL, $sql->courses_id)
				],
				'folder' => $this->folder,
				'PARAMETER' => [
					'WEB_URL_PROJECTS' => WEB_URL_PROJECTS,
					'WEB_URL_EVENTS' => WEB_URL_EVENTS,
					'WEB_URL_ANNOUNCEMENTS' => WEB_URL_ANNOUNCEMENTS,
					'WEB_URL_TENDER_ANNOUNCEMENTS' => WEB_URL_TENDER_ANNOUNCEMENTS
				]
			]);

		} else {
			return redirect()->to('404');
		}
	}

	public function paragraphs($courses_id) {
		if (!is_numeric($courses_id) || $courses_id <= 0) {
			return [];
		}
		
		$courses_id = (int) $courses_id;
		
		$array = [];
		$sql = $this->CoursesModel->coursesParagraphsListModel($courses_id, $this->defaultLangId);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'courses_paragraph_name' => $row->courses_paragraph_name,
					'courses_paragraph_description' => $row->courses_paragraph_description,
					'image' => [
						'normal' => $row->courses_paragraph_image,
						'format' => $this->imageControl(FILE_PATH_COURSES, $row->courses_paragraph_image)
					]
				];
			}
		}

		return $array;
	}

	public function images($courses_id) {
		if (!is_numeric($courses_id) || $courses_id <= 0) {
			return [];
		}
		
		$courses_id = (int) $courses_id;
		
		$array = [];
		$sql = $this->CoursesModel->coursesImageListModel($courses_id);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'image' => [
						'normal' => $row->courses_image,
						'format' => $this->imageControl(FILE_PATH_COURSES_GALLERY, $row->courses_image)
					]
				];
			}
		}

		return $array;
	}
}
