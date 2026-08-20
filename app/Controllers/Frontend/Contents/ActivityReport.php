<?php
namespace App\Controllers\Frontend\Contents;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Contents\ActivityReportModel;
use App\Models\Frontend\Contents\CorporateModel;

class ActivityReport extends BaseController {

	protected $ActivityReportModel;
	protected $CorporateModel;
	protected $folder;

	public function __construct() {
		$this->ActivityReportModel = new ActivityReportModel();
		$this->CorporateModel = new CorporateModel();
		$this->folder = 'contents';
	}

	public function index() {

		// Segment
		$segment = $this->request->getUri()->getTotalSegments() > 1 ? $this->request->getUri()->getSegment(2) : $this->request->getUri()->getSegment(1);

		return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/contents/activity-report.html', [
			'head' => [
				'title' => lang('WebActivityReport.title'),
				'keywords' => $this->settings->site_keywords,
				'description' => $this->settings->site_description
			],
			'list' => [
				'activity_report' => $this->allActivityReport(),
				'left_menu' => $this->CorporateModel->leftMenuSlugInfoModel($segment, $this->defaultLangId)
			],
			'folder' => $this->folder
		]);
	}

	public function allActivityReport() {
		$array = [];
		$sql = $this->ActivityReportModel->activityReportListModel($this->defaultLangId);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'activity_report_name' => $row->activity_report_name,
					'file' => [
						'base' => base_url(FILE_PATH_ACTIVITY_REPORT.'/'.$row->activity_report_file)
					]
				];
			}
		}

		return $array;
	}
}
