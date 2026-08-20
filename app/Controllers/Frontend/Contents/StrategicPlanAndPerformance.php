<?php
namespace App\Controllers\Frontend\Contents;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Contents\StrategicPlanAndPerformanceModel;
use App\Models\Frontend\Contents\CorporateModel;

class StrategicPlanAndPerformance extends BaseController {

	protected $StrategicPlanAndPerformanceModel;
	protected $CorporateModel;
	protected $folder;

	public function __construct() {
		$this->StrategicPlanAndPerformanceModel = new StrategicPlanAndPerformanceModel();
		$this->CorporateModel = new CorporateModel();
		$this->folder = 'contents';
	}

	public function index() {

		// Segment
		$segment = $this->request->getUri()->getTotalSegments() > 1 ? $this->request->getUri()->getSegment(2) : $this->request->getUri()->getSegment(1);

		return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/contents/strategic-plan-and-performance.html', [
			'head' => [
				'title' => lang('WebStrategicPlanAndPerformance.title'),
				'keywords' => $this->settings->site_keywords,
				'description' => $this->settings->site_description
			],
			'list' => [
				'strategic_plans' => $this->allStrategicPlanAndPerformance(),
				'left_menu' => $this->CorporateModel->leftMenuSlugInfoModel($segment, $this->defaultLangId)
			],
			'folder' => $this->folder
		]);
	}

	public function allStrategicPlanAndPerformance() {
		$array = [];
		$sql = $this->StrategicPlanAndPerformanceModel->strategicPlanAndPerformanceListModel($this->defaultLangId);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'strategic_plan_name' => $row->strategic_plan_name,
					'strategic_plan_number' => $row->strategic_plan_number,
					'file' => [
						'base' => base_url(FILE_PATH_STRATEGIC_PLAN_AND_PERFORMANCA.'/'.$row->strategic_plan_file)
					],
					'image' => [
						'base' => $this->contentImageUrl(FILE_PATH_STRATEGIC_PLAN_AND_PERFORMANCA, $row->strategic_plan_image)
					]
				];
			}
		}

		return $array;
	}
}
