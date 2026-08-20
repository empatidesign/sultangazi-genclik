<?php
namespace App\Controllers\Backend\Designs;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Designs\MenuManagementModel;
use App\Models\Backend\DatatableModel;
use App\Libraries\Nestable\MenuManagementNestable;

class MenuManagement extends BaseController {

	protected $table;
	protected $tableLang;
	protected $tableImage;
	protected $tableCourses;
	protected $pageUrl;
	protected $filePath;
	protected $MenuManagementModel;
	protected $DatatableModel;
	protected $MenuManagementNestable;
	protected $menuType;
	protected $menuTarget;
	protected $menuLocation;
	protected $menuTemplate;

	public function __construct() {
		$this->table = 'menus';
		$this->tableLang = 'menus_lang';
		$this->tableImage = 'menus_image';
		$this->pageUrl = ADMIN_URL_DESIGNS.'/'.ADMIN_URL_MENU_MANAGEMENT;
		$this->filePath = FILE_PATH_MENU_MANAGEMENT;
		$this->MenuManagementModel = new MenuManagementModel();
		$this->DatatableModel = new DatatableModel();
		$this->MenuManagementNestable = new MenuManagementNestable();

		// Type
		$this->menuType = [
			MENU_MANAGEMENT_TYPE_PAGES => [
				'name' => lang('AdminDesigns.menuManagement.general.typeConnection.pages'),
				'type' => 'page',
				'selected' => NULL
			],
			MENU_MANAGEMENT_TYPE_SULTANGAZI_CONTENTS => [
				'name' => lang('AdminDesigns.menuManagement.general.typeConnection.sultangaziContents'),
				'type' => 'sultangazi_content',
				'selected' => NULL
			],
			MENU_MANAGEMENT_TYPE_CONTRACTS => [
				'name' => lang('AdminDesigns.menuManagement.general.typeConnection.contracts'),
				'type' => 'contract',
				'selected' => NULL
			],
			MENU_MANAGEMENT_TYPE_SERVICES => [
				'name' => lang('AdminDesigns.menuManagement.general.typeConnection.services'),
				'type' => 'service',
				'selected' => NULL
			],
			MENU_MANAGEMENT_TYPE_PROJECTS => [
				'name' => lang('AdminDesigns.menuManagement.general.typeConnection.projects'),
				'type' => 'projects',
				'selected' => NULL
			],
			MENU_MANAGEMENT_TYPE_PRESIDENT_CONTENTS => [
				'name' => lang('AdminDesigns.menuManagement.general.typeConnection.presidentContents'),
				'type' => 'president_content',
				'selected' => NULL
			],
			MENU_MANAGEMENT_TYPE_LINK => [
				'name' => lang('AdminDesigns.menuManagement.general.typeConnection.link.title'),
				'type' => 'link',
				'selected' => 'selected'
			]
		];

		// Target
		$this->menuTarget = [
			MENU_MANAGEMENT_TARGET_SAME_WINDOW => lang('AdminDesigns.menuManagement.general.target.sameWindow'),
			MENU_MANAGEMENT_TARGET_NEW_WINDOW => lang('AdminDesigns.menuManagement.general.target.newWindow')
		];

		// Location
		$this->menuLocation = [
			MENU_MANAGEMENT_LOCATION_PARENT_MENU => lang('AdminDesigns.menuManagement.general.location.parentMenu'),
			MENU_MANAGEMENT_LOCATION_FOOTER_MENU => lang('AdminDesigns.menuManagement.general.location.footerMenu')
		];

		// Template
		$this->menuTemplate = [
			MENU_MANAGEMENT_TEMPLATE_SUB_MENU_1 => lang('AdminDesigns.menuManagement.general.template.option1'),
			MENU_MANAGEMENT_TEMPLATE_SUB_MENU_2 => lang('AdminDesigns.menuManagement.general.template.option2'),
			MENU_MANAGEMENT_TEMPLATE_SUB_MENU_3 => lang('AdminDesigns.menuManagement.general.template.option3')
		];
	}

	public function index() {
		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_name' => 'index',
			'page_url' => $this->pageUrl,
			'datatable_url' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/datatable'),
			'result' => [
				'menu_location' => $this->menuLocation
			]
		]);
	}

	public function datatable() {
		if ($this->request->isAJAX()) {
			$column = ['status', 'status_mobile', 'menu_parent_id', 'menu_name', NULL];
			$search = ['status', 'menu_name', 'menu_location'];
			$orderBy = ['menu_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {

					// Breadcrumbs
					if ($row->menu_parent_id == MENU_MANAGEMENT_PARENT_MENU) {
						$menu_parent = set_danger(lang('AdminDesigns.menuManagement.general.mainMenu'));
					} else {
						$menu_parent = $this->MenuManagementModel->menuManagementBreadchumbModel($row->menu_id, $this->defaultLangId);
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
		$pagesList = $this->MenuManagementModel->menuManagementPagesListModel($this->defaultLangId);
		if (isNotNull($pagesList)) {
			foreach ($pagesList as $row) {
				$pages_array[] = [
					'id' => $row->page_id,
					'name' => $row->page_name
				];
			}
		}

		// Sultangazi Contents
		$sultangazi_contents_array = [];
		$sultangaziContentsList = $this->MenuManagementModel->menuManagementSultangaziContentsListModel($this->defaultLangId);
		if (isNotNull($sultangaziContentsList)) {
			foreach ($sultangaziContentsList as $row) {
				$sultangazi_contents_array[] = [
					'id' => $row->content_id,
					'name' => $row->content_name
				];
			}
		}

		// Contracts
		$contracts_array = [];
		$contractsList = $this->MenuManagementModel->menuManagementContractsListModel($this->defaultLangId);
		if (isNotNull($contractsList)) {
			foreach ($contractsList as $row) {
				$contracts_array[] = [
					'id' => $row->contract_id,
					'name' => $row->contract_name
				];
			}
		}

		// Projects
		$projects_array = [];
		$projectList = $this->MenuManagementModel->menuManagementProjectsListModel($this->defaultLangId);
		if (isNotNull($projectList)) {
			foreach ($projectList as $row) {
				$projects_array[] = [
					'id' => $row->project_id,
					'name' => $row->project_name
				];
			}
		}

		// Services
		$services_array = [];
		$servicesList = $this->MenuManagementModel->menuManagementServicesListModel($this->defaultLangId);
		if (isNotNull($servicesList)) {
			foreach ($servicesList as $row) {
				$services_array[] = [
					'id' => $row->service_id,
					'name' => $row->service_name.' ('.$row->service_type_name.')'
				];
			}
		}

		// President Contents
		$president_content_array = [];
		$presidentContentList = $this->MenuManagementModel->menuManagementPresidentContentsListModel($this->defaultLangId);
		if (isNotNull($presidentContentList)) {
			foreach ($presidentContentList as $row) {
				$president_content_array[] = [
					'id' => $row->president_content_id,
					'name' => $row->president_content_name
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
				'menu_location' => $this->menuLocation,
				'menu_template' => $this->menuTemplate
			],
			'list' => [
				'menu' => $this->MenuManagementModel->menuManagementParentModel($this->defaultLangId),
				'pages' => $pages_array,
				'sultangazi_contents' => $sultangazi_contents_array,
				'contracts' => $contracts_array,
				'projects' => $projects_array,
				'services' => $services_array,
				'president_contents' => $president_content_array
			]
		]);
	}

	public function insert() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules1 = [
					'form.status' => [
						'label' => lang('AdminDesigns.menuManagement.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.menu_name' => [
						'label' => lang('AdminDesigns.menuManagement.general.name'),
						'rules' => 'required'
					],
					'form.menu_type' => [
						'label' => lang('AdminDesigns.menuManagement.general.typeConnection.title'),
						'rules' => 'required'
					],
					'form.menu_location' => [
						'label' => lang('AdminDesigns.menuManagement.general.location.title'),
						'rules' => 'required'
					]
				];

				$rules2 = [];
				if ($this->request->getVar('form[menu_parent_id]') == MENU_MANAGEMENT_PARENT_MENU) {
					$rules2 = [
						'form.menu_template_sub_menu_id' => [
							'label' => lang('AdminDesigns.menuManagement.general.template.title'),
							'rules' => 'required'
						]
					];
				}

				/*****************************************************/

				$rules = array_merge($rules1, $rules2);

				if ($this->validate($rules)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;

						// Menu Location
						$data['menu_location'] = '';
						if (isNotNull($this->request->getVar('form[menu_location]'))) {
							$menu_location = NULL;
							foreach ($this->request->getVar('form[menu_location]') as $row) {
								$menu_location .= $row.',';
							}

							$data['menu_location'] = reduce_multiples($menu_location, ',', TRUE);
						}
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
		$sql = $this->MenuManagementModel->menuManagementInfoModel($menu_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->MenuManagementModel->menuManagementLangModel($menu_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['menu_name'] = $row->menu_name;
					$lang_array['data']['translations'][$row->lang_id]['menu_link'] = $row->menu_link;
				}
			}

			// Pages
			$pages_array = [];
			$pagesList = $this->MenuManagementModel->menuManagementPagesListModel($this->defaultLangId);
			if (isNotNull($pagesList)) {
				foreach ($pagesList as $row) {
					$pages_array[] = [
						'id' => $row->page_id,
						'name' => $row->page_name
					];
				}
			}

			// Sultangazi Contents
			$sultangazi_contents_array = [];
			$sultangaziContentsList = $this->MenuManagementModel->menuManagementSultangaziContentsListModel($this->defaultLangId);
			if (isNotNull($sultangaziContentsList)) {
				foreach ($sultangaziContentsList as $row) {
					$sultangazi_contents_array[] = [
						'id' => $row->content_id,
						'name' => $row->content_name
					];
				}
			}

			// Projects
			$projects_array = [];
			$projectsList = $this->MenuManagementModel->menuManagementProjectsListModel($this->defaultLangId);
			if (isNotNull($projectsList)) {
				foreach ($projectsList as $row) {
					$projects_array[] = [
						'id' => $row->project_id,
						'name' => $row->project_name
					];
				}
			}

			// Contracts
			$contracts_array = [];
			$contractsList = $this->MenuManagementModel->menuManagementContractsListModel($this->defaultLangId);
			if (isNotNull($contractsList)) {
				foreach ($contractsList as $row) {
					$contracts_array[] = [
						'id' => $row->contract_id,
						'name' => $row->contract_name
					];
				}
			}

			// Services
			$services_array = [];
			$servicesList = $this->MenuManagementModel->menuManagementServicesListModel($this->defaultLangId);
			if (isNotNull($servicesList)) {
				foreach ($servicesList as $row) {
					$services_array[] = [
						'id' => $row->service_id,
						'name' => $row->service_name.' ('.$row->service_type_name.')'
					];
				}
			}

			// President Contents
			$president_content_array = [];
			$presidentContentList = $this->MenuManagementModel->menuManagementPresidentContentsListModel($this->defaultLangId);
			if (isNotNull($presidentContentList)) {
				foreach ($presidentContentList as $row) {
					$president_content_array[] = [
						'id' => $row->president_content_id,
						'name' => $row->president_content_name
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

			// Location
			$menu_location = [];
			foreach ($this->menuLocation as $key => $row) {

				// Selected
				$selected = FALSE;
				$explode = explode(',', $sql->menu_location);
				if (in_array($key, $explode)) {
					$selected = TRUE;
				}

				$menu_location[] = [
					'id' => $key,
					'name' => $row,
					'selected' => $selected == TRUE ? 'selected' : NULL
				];
			}

			// Template
			$menu_template = [];
			$menu_template_selected = [];
			foreach ($this->menuTemplate as $key => $row) {
				$menu_template[] = [
					'id' => $key,
					'name' => $row,
					'selected' => $sql->menu_template_sub_menu_id == $key ? 'selected' : NULL
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
					'menu_template_sub_menu_id' => $sql->menu_template_sub_menu_id,
					'menu_type' => $sql->menu_type,
					'menu_type_array' => $menu_type,
					'menu_type_selected' => $menu_type_selected,
					'menu_page_id' => $sql->menu_page_id,
					'menu_sultangazi_content_id' => $sql->menu_sultangazi_content_id,
					'menu_contract_id' => $sql->menu_contract_id,
					'menu_service_id' => $sql->menu_service_id,
					'menu_project_id' => $sql->menu_project_id,
					'menu_president_content_id' => $sql->menu_president_content_id,
					'menu_target' => $menu_target,
					'menu_location' => $menu_location,
					'menu_template' => $menu_template
				],
				'list' => [
					'menu' => $this->MenuManagementModel->menuManagementParentModel($this->defaultLangId),
					'pages' => $pages_array,
					'projects' => $projects_array,
					'sultangazi_contents' => $sultangazi_contents_array,
					'contracts' => $contracts_array,
					'services' => $services_array,
					'president_contents' => $president_content_array
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $menu_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->MenuManagementModel->menuManagementInfoModel($menu_id);
				if (isNotNull($sql)) {

					$rules1 = [
						'form.status' => [
							'label' => lang('AdminDesigns.menuManagement.general.status'),
							'rules' => 'required'
						],
						'form.status_mobile' => [
							'label' => lang('Admin.mobile'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.menu_name' => [
							'label' => lang('AdminDesigns.menuManagement.general.name'),
							'rules' => 'required'
						],
						'form.menu_type' => [
							'label' => lang('AdminDesigns.menuManagement.general.typeConnection.title'),
							'rules' => 'required'
						],
						'form.menu_location' => [
							'label' => lang('AdminDesigns.menuManagement.general.location.title'),
							'rules' => 'required'
						]
					];

					$rules2 = [];
					if ($this->request->getVar('form[menu_parent_id]') == MENU_MANAGEMENT_PARENT_MENU) {
						$rules2 = [
							'form.menu_template_sub_menu_id' => [
								'label' => lang('AdminDesigns.menuManagement.general.template.title'),
								'rules' => 'required'
							]
						];
					}

					/*****************************************************/

					$rules = array_merge($rules1, $rules2);

					if ($this->validate($rules)) {

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;

							// Menu Location
							$data['menu_location'] = '';
							if (isNotNull($this->request->getVar('form[menu_location]'))) {
								$menu_location = NULL;
								foreach ($this->request->getVar('form[menu_location]') as $row) {
									$menu_location .= $row.',';
								}

								$data['menu_location'] = reduce_multiples($menu_location, ',', TRUE);
							}
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

									$langControlModel = $this->MenuManagementModel->menuManagementLangControlModel($menu_id, $lang_id);
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
		$sql = $this->MenuManagementModel->menuManagementInfoModel($menu_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['menu_id' => $menu_id]);
			if ($delete) {

				// Lang
				$lang = $this->MenuManagementModel->menuManagementLangModel($menu_id);
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
		$menu_list = $this->MenuManagementModel->menuManagementNestableModel($this->defaultLangId);
		if (isNotNull($menu_list)) {
			$nestable = $this->MenuManagementNestable->get_nestable($menu_list);
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
