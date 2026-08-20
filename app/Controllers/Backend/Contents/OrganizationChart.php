<?php
namespace App\Controllers\Backend\Contents;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Contents\OrganizationChartModel;
use App\Models\Backend\DatatableModel;
use App\Libraries\Nestable\OrganizationChartNestable;

class OrganizationChart extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $OrganizationChartModel;
	protected $DatatableModel;
	protected $OrganizationChartNestable;
	protected $organizationChartType;
	protected $organizationChartLevel;

	public function __construct() {
		$this->table = 'organization_chart';
		$this->tableLang = 'organization_chart_lang';
		$this->pageUrl = ADMIN_URL_CONTENTS.'/'.ADMIN_URL_ORGANIZATION_CHART;
		$this->OrganizationChartModel = new OrganizationChartModel();
		$this->DatatableModel = new DatatableModel();
		$this->OrganizationChartNestable = new OrganizationChartNestable();

		// Type
		$this->organizationChartType = [
			ORGANIZATION_CHART_PRESIDENT => [
				'name' => lang('AdminContents.organizationChart.general.typeConnection.president'),
				'type' => 'president',
				'selected' => NULL
			],
			ORGANIZATION_CHART_VICE_PRESIDENTS => [
				'name' => lang('AdminContents.organizationChart.general.typeConnection.vicePresidents'),
				'type' => 'vice_presidents',
				'selected' => NULL
			],
			ORGANIZATION_CHART_DIRECTORATES => [
				'name' => lang('AdminContents.organizationChart.general.typeConnection.directorates'),
				'type' => 'directorates',
				'selected' => NULL
			],
			ORGANIZATION_CHART_TYPE_LINK => [
				'name' => lang('AdminContents.organizationChart.general.typeConnection.link.title'),
				'type' => 'link',
				'selected' => 'selected'
			]
		];

		// Level
		$this->organizationChartLevel = [
			ORGANIZATION_CHART_LEVEL_1 => lang('AdminContents.organizationChart.general.level.option1'),
			ORGANIZATION_CHART_LEVEL_2 => lang('AdminContents.organizationChart.general.level.option2'),
			ORGANIZATION_CHART_LEVEL_3 => lang('AdminContents.organizationChart.general.level.option3')
		];
	}

	public function index() {
		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_name' => 'index',
			'page_url' => $this->pageUrl,
			'datatable_url' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/datatable')
		]);
	}

	public function datatable() {
		if ($this->request->isAJAX()) {
			$column = ['status', 'status_mobile', 'organization_chart_parent_id', 'organization_chart_name', NULL];
			$search = ['status', 'organization_chart_name'];
			$orderBy = ['organization_chart_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {

					// Breadcrumbs
					if ($row->organization_chart_parent_id == MENU_MANAGEMENT_PARENT_MENU) {
						$organization_chart_parent = set_danger(lang('AdminContents.organizationChart.general.mainChart'));
					} else {
						$organization_chart_parent = $this->OrganizationChartModel->organizationChartBreadchumbModel($row->organization_chart_id, $this->defaultLangId);
					}

					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_status($row->status_mobile);
					$array[] = $organization_chart_parent;
					$array[] = isNotNull($row->organization_chart_name) ? $row->organization_chart_name : set_danger(lang('AdminContents.organizationChart.general.empty'));
					$array[] = action_links($row->organization_chart_id, ['edit', 'delete'], $this->pageUrl);
					$data[] = $array;

				}
			}

			$output = [
				'draw' => $this->request->getVar('draw'),
				'recordsTotal' => $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getNumRows'),
				'recordsFiltered' => $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'countAllResults'),
				'data' => $data
			];

			return $this->response->setJSON($output);
		}
	}

	public function add() {

		// Presidents
		$president_array = [];
		$president = $this->OrganizationChartModel->organizationChartPresidentGeneralInformationListModel();
		if (isNotNull($president)) {
			foreach ($president as $row) {
				$president_array[] = [
					'id' => $row->president_general_information_id,
					'name' => $row->president_name_surname
				];
			}
		}

		// Vice Presidents
		$vice_presidents_array = [];
		$vicePresidents = $this->OrganizationChartModel->organizationChartVicePresidentsListModel();
		if (isNotNull($vicePresidents)) {
			foreach ($vicePresidents as $row) {
				$vice_presidents_array[] = [
					'id' => $row->vice_president_id,
					'name' => $row->vice_president_name.' '.$row->vice_president_surname
				];
			}
		}

		// Directorates
		$directorates_array = [];
		$directorates = $this->OrganizationChartModel->organizationChartDirectoratesListModel($this->defaultLangId);
		if (isNotNull($directorates)) {
			foreach ($directorates as $row) {
				$directorates_array[] = [
					'id' => $row->directorates_id,
					'name' => $row->directorates_name
				];
			}
		}

		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_name' => 'add',
			'page_url' => $this->pageUrl,
			'last_id' => $this->general->lastIDModel($this->table, 'organization_chart_id'),
			'result' => [
				'organization_chart_type' => $this->organizationChartType,
				'organization_chart_level' => $this->organizationChartLevel
			],
			'list' => [
				'menu' => $this->OrganizationChartModel->organizationChartParentModel($this->defaultLangId),
				'president' => $president_array,
				'vice_presidents' => $vice_presidents_array,
				'directorates' => $directorates_array
			]
		]);
	}

	public function insert() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'form.status' => [
						'label' => lang('AdminContents.organizationChart.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					],
					'form.organization_chart_type' => [
						'label' => lang('AdminContents.organizationChart.general.typeConnection.title'),
						'rules' => 'required'
					],
					'form.organization_chart_level' => [
						'label' => lang('AdminContents.organizationChart.general.level.title'),
						'rules' => 'required'
					]
				];

				if ($this->validate($rules)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
					}

					$result = $this->general->insertModel($this->table, $data);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {
								$lang_data = [
									'organization_chart_id' => $result,
									'lang_id' => $lang_id,
									'organization_chart_name' => isNotNull($value['organization_chart_name']) ? $value['organization_chart_name'] : $this->request->getVar('lang['.$this->defaultLangId.'][organization_chart_name]'),
									'organization_chart_sub_title' => trim($value['organization_chart_sub_title']),
									'organization_chart_link' => $value['organization_chart_link']
								];

								$this->general->insertModel($this->tableLang, $lang_data);
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.add.organizationChart', [$this->request->getVar('lang['.$this->defaultLangId.'][organization_chart_name]')]));

						$ajax_message['success'] = TRUE;
						$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);

					} else {
						$ajax_message['error'] = lang('Admin.error.insert');
					}
				} else {
					$ajax_message['error'] = $this->validator->listErrors();
				}

			} else {
				$ajax_message['error'] = lang('Admin.error.description');
			}
		} else {
			$ajax_message['error'] = lang('Admin.error.ajax');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function edit(int $organization_chart_id) {
		$sql = $this->OrganizationChartModel->organizationChartInfoModel($organization_chart_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->OrganizationChartModel->organizationChartLangModel($organization_chart_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['organization_chart_name'] = $row->organization_chart_name;
					$lang_array['data']['translations'][$row->lang_id]['organization_chart_sub_title'] = $row->organization_chart_sub_title;
					$lang_array['data']['translations'][$row->lang_id]['organization_chart_link'] = $row->organization_chart_link;
				}
			}

			// Presidents
			$president_array = [];
			$president = $this->OrganizationChartModel->organizationChartPresidentGeneralInformationListModel();
			if (isNotNull($president)) {
				foreach ($president as $row) {
					$president_array[] = [
						'id' => $row->president_general_information_id,
						'name' => $row->president_name_surname
					];
				}
			}

			// Vice Presidents
			$vice_presidents_array = [];
			$vicePresidents = $this->OrganizationChartModel->organizationChartVicePresidentsListModel();
			if (isNotNull($vicePresidents)) {
				foreach ($vicePresidents as $row) {
					$vice_presidents_array[] = [
						'id' => $row->vice_president_id,
						'name' => $row->vice_president_name.' '.$row->vice_president_surname
					];
				}
			}

			// Directorates
			$directorates_array = [];
			$directorates = $this->OrganizationChartModel->organizationChartDirectoratesListModel($this->defaultLangId);
			if (isNotNull($directorates)) {
				foreach ($directorates as $row) {
					$directorates_array[] = [
						'id' => $row->directorates_id,
						'name' => $row->directorates_name
					];
				}
			}

			// Type
			$organization_chart_type = [];
			$organization_chart_type_selected = [];
			foreach ($this->organizationChartType as $key => $row) {
				$organization_chart_type[] = [
					'id' => $key,
					'name' => $row['name'],
					'type' => $row['type'],
					'selected' => $sql->organization_chart_type == $key ? 'selected' : NULL
				];

				$organization_chart_type_selected['data'][$row['type']]['id'] = $key;
			}

			// Level
			$organization_chart_level = [];
			foreach ($this->organizationChartLevel as $key => $row) {
				$organization_chart_level[] = [
					'id' => $key,
					'name' => $row,
					'selected' => $sql->organization_chart_level == $key ? 'selected' : NULL
				];
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'organization_chart_id' => $sql->organization_chart_id,
					'status' => $sql->status,
					'status_mobile' => $sql->status_mobile,
					'organization_chart_parent_id' => $sql->organization_chart_parent_id,
					'organization_chart_type' => $sql->organization_chart_type,
					'organization_chart_president_id' => $sql->organization_chart_president_id,
					'organization_chart_vice_president_id' => $sql->organization_chart_vice_president_id,
					'organization_chart_directorate_id' => $sql->organization_chart_directorate_id,
					'organization_chart_type_array' => $organization_chart_type,
					'organization_chart_type_selected' => $organization_chart_type_selected,
					'organization_chart_level' => $organization_chart_level
				],
				'list' => [
					'menu' => $this->OrganizationChartModel->organizationChartParentModel($this->defaultLangId),
					'president' => $president_array,
					'vice_presidents' => $vice_presidents_array,
					'directorates' => $directorates_array
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $organization_chart_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->OrganizationChartModel->organizationChartInfoModel($organization_chart_id);
				if (isNotNull($sql)) {

					$rules = [
						'form.status' => [
							'label' => lang('AdminContents.organizationChart.general.status'),
							'rules' => 'required'
						],
						'form.status_mobile' => [
							'label' => lang('Admin.mobile'),
							'rules' => 'required'
						],
						'form.organization_chart_type' => [
							'label' => lang('AdminContents.organizationChart.general.typeConnection.title'),
							'rules' => 'required'
						],
						'form.organization_chart_level' => [
							'label' => lang('AdminContents.organizationChart.general.level.title'),
							'rules' => 'required'
						]
					];

					if ($this->validate($rules)) {

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
						}

						$result = $this->general->updateModel($this->table, $data, ['organization_chart_id' => $organization_chart_id]);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'organization_chart_id' => $organization_chart_id,
										'lang_id' => $lang_id,
										'organization_chart_name' => isNotNull($value['organization_chart_name']) ? $value['organization_chart_name'] : $this->request->getVar('lang['.$this->defaultLangId.'][organization_chart_name]'),
										'organization_chart_sub_title' => trim($value['organization_chart_sub_title']),
										'organization_chart_link' => $value['organization_chart_link']
									];

									$langControlModel = $this->OrganizationChartModel->organizationChartLangControlModel($organization_chart_id, $lang_id);
									if (isNotNull($langControlModel)) {
										$this->general->updateModel($this->tableLang, $lang_data, ['organization_chart_id' => $organization_chart_id, 'lang_id' => $lang_id]);
									} else {
										$this->general->insertModel($this->tableLang, $lang_data);
									}
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.edit.organizationChart', [$this->request->getVar('lang['.$this->defaultLangId.'][organization_chart_name]')]));

							$ajax_message['success'] = TRUE;

							if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
								$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$organization_chart_id);
							} else {
								$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);
							}

						} else {
							$ajax_message['error'] = lang('Admin.error.update');
						}

					} else {
						$ajax_message['error'] = $this->validator->listErrors();
					}

				} else {
					$ajax_message['error'] = lang('Admin.error.noRecord');
				}

			} else {
				$ajax_message['error'] = lang('Admin.error.description');
			}
		} else {
			$ajax_message['error'] = lang('Admin.error.ajax');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function delete(int $organization_chart_id) {
		$sql = $this->OrganizationChartModel->organizationChartInfoModel($organization_chart_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['organization_chart_id' => $organization_chart_id]);
			if ($delete) {

				// Lang
				$lang = $this->OrganizationChartModel->organizationChartLangModel($organization_chart_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['organization_chart_id' => $row->organization_chart_id]);
					}
				}

				// Flash Data
				session()->setFlashdata('flashDataMessageSuccess', lang('Admin.success.recordDeleted'));

				$ajax_message['success'] = TRUE;
				$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);

			} else {
				$ajax_message['error'] = lang('Admin.error.delete');
			}

		} else {
			$ajax_message['error'] = lang('Admin.error.noRecord');
		}

		return $this->response->setJSON($ajax_message);
	}

	/*****************************************************/

	public function nestableList() {
		$organization_chart_list = $this->OrganizationChartModel->organizationChartNestableModel($this->defaultLangId);
		if (isNotNull($organization_chart_list)) {
			$nestable = $this->OrganizationChartNestable->get_nestable($organization_chart_list);
			$ajax_message['success'] = $nestable;
		} else {
			$ajax_message['error'] = lang('Admin.error.noRecord');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function nestableUpdate() {
		function parseJsonArray(array $jsonArray, $parentID = 0) {
			$return = [];
			foreach ($jsonArray as $subArray) {
				$returnSubSubArray = [];
				if (isset($subArray->children)) {
					$returnSubSubArray = parseJsonArray($subArray->children, $subArray->organization_chart_id);
				}
				$return[] = ['organization_chart_id' => $subArray->organization_chart_id, 'parentID' => $parentID];
				$return = array_merge($return, $returnSubSubArray);
			}

			return $return;
		}

		$readbleArray = parseJsonArray(json_decode($this->request->getVar('jsonstring')));
		foreach ($readbleArray as $key => $value) {
			if (is_array($value)) {
				$data = [
					'organization_chart_order' => $key,
					'organization_chart_parent_id' => $value['parentID']
				];

				$this->general->updateModel($this->table, $data, ['organization_chart_id' => $value['organization_chart_id']]);
			}
		}
	}
}
