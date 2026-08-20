<?php
namespace App\Controllers\Frontend\Contents;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Contents\OrganizationChartModel;

class OrganizationChart extends BaseController {

	protected $OrganizationChartModel;

	public function __construct() {
		$this->OrganizationChartModel = new OrganizationChartModel();
	}

	public function index() {
		return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/contents/organization-chart.html', [
			'head' => [
				'title' => lang('WebOrganizationChart.title'),
				'keywords' => $this->settings->site_keywords,
				'description' => $this->settings->site_description
			],
			'list' => [
				'organization_chart' => [
					'first' => $this->OrganizationChartModel->organizationChartFirstListModel($this->defaultLangId),
					'second' => $this->OrganizationChartModel->organizationChartSecondListModel($this->defaultLangId),
					'third' => $this->OrganizationChartModel->organizationChartThirdListModel($this->defaultLangId)
				]
			]
		]);
	}
}
