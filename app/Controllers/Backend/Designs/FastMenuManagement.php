<?php
namespace App\Controllers\Backend\Designs;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Designs\FastMenuManagementModel;
use App\Models\Backend\DatatableModel;
use App\Libraries\Nestable\FastMenuManagementNestable;

class FastMenuManagement extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $filePath;
	protected $FastMenuManagementModel;
	protected $DatatableModel;
	protected $FastMenuManagementNestable;
	protected $menuType;
	protected $menuTarget;
	protected $menuLocation;
	protected $menuTemplate;

	public function __construct() {
		$this->table = 'fast_menus';
		$this->tableLang = 'fast_menus_lang';
		$this->pageUrl = ADMIN_URL_DESIGNS.'/'.ADMIN_URL_FAST_MENU_MANAGEMENT;
		$this->filePath = FILE_PATH_MENU_MANAGEMENT;
		$this->FastMenuManagementModel = new FastMenuManagementModel();
		$this->DatatableModel = new DatatableModel();
		$this->FastMenuManagementNestable = new FastMenuManagementNestable();

		// Type
		$this->menuType = [
			MENU_MANAGEMENT_TYPE_LINK => [
				'name' => lang('AdminDesigns.fastMenuManagement.general.typeConnection.link.title'),
				'type' => 'link',
				'selected' => 'selected'
			],
			MENU_MANAGEMENT_TYPE_PAGES => [
				'name' => lang('AdminDesigns.menuManagement.general.typeConnection.pages'),
				'type' => 'page',
				'selected' => NULL
			],
			MENU_MANAGEMENT_TYPE_SERVICES => [
				'name' => lang('AdminDesigns.menuManagement.general.typeConnection.services'),
				'type' => 'service',
				'selected' => NULL
			]
		];

		// Target
		$this->menuTarget = [
			MENU_MANAGEMENT_TARGET_SAME_WINDOW => lang('AdminDesigns.fastMenuManagement.general.target.sameWindow'),
			MENU_MANAGEMENT_TARGET_NEW_WINDOW => lang('AdminDesigns.fastMenuManagement.general.target.newWindow')
		];

		// Location
		$this->menuLocation = [
			MENU_MANAGEMENT_LOCATION_PARENT_MENU => lang('AdminDesigns.fastMenuManagement.general.location.parentMenu'),
			MENU_MANAGEMENT_LOCATION_FOOTER_MENU => lang('AdminDesigns.fastMenuManagement.general.location.footerMenu')
		];

		// Template
		$this->menuTemplate = [
			MENU_MANAGEMENT_TEMPLATE_SUB_MENU_1 => lang('AdminDesigns.fastMenuManagement.general.template.option1'),
			MENU_MANAGEMENT_TEMPLATE_SUB_MENU_2 => lang('AdminDesigns.fastMenuManagement.general.template.option2'),
			MENU_MANAGEMENT_TEMPLATE_SUB_MENU_3 => lang('AdminDesigns.fastMenuManagement.general.template.option3')
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
			$column = ['status', 'status_mobile', 'menu_parent_id', 'menu_name', NULL];
			$search = ['status', 'menu_name'];
			$orderBy = ['menu_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {

					// Breadcrumbs
					if ($row->menu_parent_id == MENU_MANAGEMENT_PARENT_MENU) {
						$menu_parent = set_danger(lang('AdminDesigns.fastMenuManagement.general.mainMenu'));
					} else {
						$menu_parent = $this->FastMenuManagementModel->menuManagementBreadchumbModel($row->menu_id, $this->defaultLangId);
					}

					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_status($row->status_mobile);
					$array[] = $menu_parent;
					$array[] = $row->menu_name;
					$array[] = action_links($row->menu_id, ['edit', 'delete'], $this->pageUrl);
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

		// Pages
		$pages_array = [];
		$pagesList = $this->FastMenuManagementModel->menuManagementPagesListModel($this->defaultLangId);
		if (isNotNull($pagesList)) {
			foreach ($pagesList as $row) {
				$pages_array[] = [
					'id' => $row->page_id,
					'name' => $row->page_name
				];
			}
		}

		// Services
		$services_array = [];
		$servicesList = $this->FastMenuManagementModel->menuManagementServicesListModel($this->defaultLangId);
		if (isNotNull($servicesList)) {
			foreach ($servicesList as $row) {
				$services_array[] = [
					'id' => $row->service_id,
					'name' => $row->service_name.' ('.$row->service_type_name.')'
				];
			}
		}

		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_name' => 'add',
			'page_url' => $this->pageUrl,
			'last_id' => $this->general->lastIDModel($this->table, 'menu_id'),
			'result' => [
				'menu_type' => $this->menuType,
				'menu_target' => $this->menuTarget,
				'menu_template' => $this->menuTemplate
			],
			'list' => [
				'menu' => $this->FastMenuManagementModel->menuManagementParentModel($this->defaultLangId),
				'pages' => $pages_array,
				'services' => $services_array
			]
		]);
	}

	public function insert() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'form.status' => [
						'label' => lang('AdminDesigns.fastMenuManagement.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.menu_name' => [
						'label' => lang('AdminDesigns.fastMenuManagement.general.name'),
						'rules' => 'required'
					],
					'form.menu_type' => [
						'label' => lang('AdminDesigns.fastMenuManagement.general.typeConnection.title'),
						'rules' => 'required'
					]
				];

				if ($this->validate($rules)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['menu_icon'] = trim($this->request->getVar('form[menu_icon]'));
					}

					$result = $this->general->insertModel($this->table, $data);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {
								$lang_data = [
									'menu_id' => $result,
									'lang_id' => $lang_id,
									'menu_name' => isNotNull($value['menu_name']) ? $value['menu_name'] : $this->request->getVar('lang['.$this->defaultLangId.'][menu_name]'),
									'menu_link' => $value['menu_link']
								];

								$this->general->insertModel($this->tableLang, $lang_data);
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminDesigns.result.add.menuManagement', [$this->request->getVar('lang['.$this->defaultLangId.'][menu_name]')]));

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

	public function edit(int $menu_id) {
		$sql = $this->FastMenuManagementModel->menuManagementInfoModel($menu_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->FastMenuManagementModel->menuManagementLangModel($menu_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['menu_name'] = $row->menu_name;
					$lang_array['data']['translations'][$row->lang_id]['menu_link'] = $row->menu_link;
				}
			}

			// Pages
			$pages_array = [];
			$pagesList = $this->FastMenuManagementModel->menuManagementPagesListModel($this->defaultLangId);
			if (isNotNull($pagesList)) {
				foreach ($pagesList as $row) {
					$pages_array[] = [
						'id' => $row->page_id,
						'name' => $row->page_name
					];
				}
			}

			// Services
			$services_array = [];
			$servicesList = $this->FastMenuManagementModel->menuManagementServicesListModel($this->defaultLangId);
			if (isNotNull($servicesList)) {
				foreach ($servicesList as $row) {
					$services_array[] = [
						'id' => $row->service_id,
						'name' => $row->service_name.' ('.$row->service_type_name.')'
					];
				}
			}

			// Type
			$menu_type = [];
			$menu_type_selected = [];
			foreach ($this->menuType as $key => $row) {
				$menu_type[] = [
					'id' => $key,
					'name' => $row['name'],
					'type' => $row['type'],
					'selected' => $sql->menu_type == $key ? 'selected' : NULL
				];

				$menu_type_selected['data'][$row['type']]['id'] = $key;
			}

			// Target
			$menu_target = [];
			foreach ($this->menuTarget as $key => $row) {
				$menu_target[] = [
					'id' => $key,
					'name' => $row,
					'selected' => $sql->menu_target == $key ? 'selected' : NULL
				];
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'menu_id' => $sql->menu_id,
					'status' => $sql->status,
					'status_mobile' => $sql->status_mobile,
					'menu_parent_id' => $sql->menu_parent_id,
					'menu_type' => $sql->menu_type,
					'menu_type_array' => $menu_type,
					'menu_type_selected' => $menu_type_selected,
					'menu_page_id' => $sql->menu_page_id,
					'menu_service_id' => $sql->menu_service_id,
					'menu_target' => $menu_target,
					'menu_icon' => $sql->menu_icon
				],
				'list' => [
					'menu' => $this->FastMenuManagementModel->menuManagementParentModel($this->defaultLangId),
					'pages' => $pages_array,
					'services' => $services_array
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $menu_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->FastMenuManagementModel->menuManagementInfoModel($menu_id);
				if (isNotNull($sql)) {

					$rules = [
						'form.status' => [
							'label' => lang('AdminDesigns.fastMenuManagement.general.status'),
							'rules' => 'required'
						],
						'form.status_mobile' => [
							'label' => lang('Admin.mobile'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.menu_name' => [
							'label' => lang('AdminDesigns.fastMenuManagement.general.name'),
							'rules' => 'required'
						],
						'form.menu_type' => [
							'label' => lang('AdminDesigns.fastMenuManagement.general.typeConnection.title'),
							'rules' => 'required'
						]
					];

					if ($this->validate($rules)) {

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['menu_icon'] = trim($this->request->getVar('form[menu_icon]'));
						}

						$result = $this->general->updateModel($this->table, $data, ['menu_id' => $menu_id]);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'menu_id' => $menu_id,
										'lang_id' => $lang_id,
										'menu_name' => isNotNull($value['menu_name']) ? $value['menu_name'] : $this->request->getVar('lang['.$this->defaultLangId.'][menu_name]'),
										'menu_link' => $value['menu_link']
									];

									$langControlModel = $this->FastMenuManagementModel->menuManagementLangControlModel($menu_id, $lang_id);
									if (isNotNull($langControlModel)) {
										$this->general->updateModel($this->tableLang, $lang_data, ['menu_id' => $menu_id, 'lang_id' => $lang_id]);
									} else {
										$this->general->insertModel($this->tableLang, $lang_data);
									}
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminDesigns.result.edit.menuManagement', [$this->request->getVar('lang['.$this->defaultLangId.'][menu_name]')]));

							$ajax_message['success'] = TRUE;

							if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
								$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$menu_id);
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

	public function delete(int $menu_id) {
		$sql = $this->FastMenuManagementModel->menuManagementInfoModel($menu_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['menu_id' => $menu_id]);
			if ($delete) {

				// Lang
				$lang = $this->FastMenuManagementModel->menuManagementLangModel($menu_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['menu_id' => $row->menu_id]);
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
		$menu_list = $this->FastMenuManagementModel->menuManagementNestableModel($this->defaultLangId);
		if (isNotNull($menu_list)) {
			$nestable = $this->FastMenuManagementNestable->get_nestable($menu_list);
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
					$returnSubSubArray = parseJsonArray($subArray->children, $subArray->menu_id);
				}
				$return[] = ['menu_id' => $subArray->menu_id, 'parentID' => $parentID];
				$return = array_merge($return, $returnSubSubArray);
			}

			return $return;
		}

		$readbleArray = parseJsonArray(json_decode($this->request->getVar('jsonstring')));
		foreach ($readbleArray as $key => $value) {
			if (is_array($value)) {
				$data = [
					'menu_order' => $key,
					'menu_parent_id' => $value['parentID']
				];

				$this->general->updateModel($this->table, $data, ['menu_id' => $value['menu_id']]);
			}
		}
	}
}
