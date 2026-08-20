<?php
namespace App\Controllers\Backend\MapModule;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\MapModule\MapLocationsModel;
use App\Models\Backend\DatatableModel;

class MapLocations extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $MapLocationsModel;
	protected $DatatableModel;
	protected $types;

	public function __construct() {
		$this->table = 'map_locations';
		$this->tableLang = 'map_locations_lang';
		$this->pageUrl = ADMIN_URL_MAP_MODULE.'/'.ADMIN_URL_MAP_LOCATIONS;
		$this->MapLocationsModel = new MapLocationsModel();
		$this->DatatableModel = new DatatableModel();
		helper('array');

		// Types
		$this->types = [
			MAP_LOCATIONS_TYPE_1 => lang('AdminMapModule.mapLocations.general.types.type1'),
			MAP_LOCATIONS_TYPE_2 => lang('AdminMapModule.mapLocations.general.types.type2')
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
			$column = ['status', 'map_category_name', 'map_type_id', 'map_location_name', 'map_location_created_date', 'map_location_updated_date', NULL];
			$search = [];
			$orderBy = ['map_location_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = $row->map_category_name;
					$array[] = $row->map_type_id ? dot_array_search($row->map_type_id, $this->types) : NULL;
					$array[] = $row->map_location_name;
					$array[] = dateFormat($row->map_location_created_date, 'd-m-Y H:i:s');
					$array[] = $row->map_location_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->map_location_updated_date, 'd-m-Y H:i:s') : '--';
					$array[] = action_links($row->map_location_id, ['edit', 'delete'], $this->pageUrl);
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
		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_name' => 'add',
			'page_url' => $this->pageUrl,
			'list' => [
				'categories' => $this->MapLocationsModel->mapCategoriesListModel($this->defaultLangId),
				'projects' => $this->MapLocationsModel->projectListModel($this->defaultLangId),
				'types' => $this->types
			]
		]);
	}

	public function insert() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules1 = [
					'form.status' => [
						'label' => lang('AdminMapModule.mapLocations.general.status'),
						'rules' => 'required'
					],
					'form.map_category_id' => [
						'label' => lang('AdminMapModule.mapLocations.general.category'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.map_location_name' => [
						'label' => lang('AdminMapModule.mapLocations.general.name'),
						'rules' => 'required'
					],
					'form.map_type_id' => [
						'label' => lang('AdminMapModule.mapLocations.general.types.title'),
						'rules' => 'required'
					]
				];

				$rules2 = [];
				if ($this->request->getVar('form[map_type_id]') == MAP_LOCATIONS_TYPE_1) {
					$rules2 = [
						'form.map_location_lat_coordinate' => [
							'label' => lang('AdminMapModule.mapLocations.general.coordinate.lat'),
							'rules' => 'required'
						],
						'form.map_location_long_coordinate' => [
							'label' => lang('AdminMapModule.mapLocations.general.coordinate.long'),
							'rules' => 'required'
						]
					];
				}

				$rules3 = [];
				if ($this->request->getVar('form[map_type_id]') == MAP_LOCATIONS_TYPE_2) {
					$rules3 = [
						'form.map_project_id' => [
							'label' => lang('AdminMapModule.mapLocations.general.types.projects'),
							'rules' => 'required'
						]
					];
				}

				/*****************************************************/

				$rules = array_merge_recursive($rules1, $rules2, $rules3);

				if ($this->validate($rules)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['map_location_lat_coordinate'] = trim(removeWhiteSpaces($this->request->getVar('form[map_location_lat_coordinate]')));
						$data['map_location_long_coordinate'] = trim(removeWhiteSpaces($this->request->getVar('form[map_location_long_coordinate]')));
						$data['map_location_created_date'] = nowDate();
					}

					$result = $this->general->insertModel($this->table, $data);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {
								$lang_data = [
									'map_location_id' => $result,
									'lang_id' => $lang_id,
									'map_location_name' => isNotNull($value['map_location_name']) ? $value['map_location_name'] : $this->request->getVar('lang['.$this->defaultLangId.'][map_location_name]')
								];

								$this->general->insertModel($this->tableLang, $lang_data);
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminMapModule.result.add.mapLocations', [$this->request->getVar('lang['.$this->defaultLangId.'][map_location_name]')]));

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

	public function edit(int $map_location_id) {
		$sql = $this->MapLocationsModel->mapLocationsInfoModel($map_location_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->MapLocationsModel->mapLocationsLangModel($map_location_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['map_location_name'] = $row->map_location_name;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'map_location_id' => $sql->map_location_id,
					'status' => $sql->status,
					'map_category_id' => $sql->map_category_id,
					'map_type_id' => $sql->map_type_id,
					'map_project_id' => $sql->map_project_id,
					'map_location_lat_coordinate' => $sql->map_location_lat_coordinate,
					'map_location_long_coordinate' => $sql->map_location_long_coordinate
				],
				'list' => [
					'categories' => $this->MapLocationsModel->mapCategoriesListModel($this->defaultLangId),
					'projects' => $this->MapLocationsModel->projectListModel($this->defaultLangId),
					'types' => $this->types
				],
				'PARAMETER' => [
					'TYPES' => [
						'MAP_LOCATIONS_TYPE_1' => MAP_LOCATIONS_TYPE_1,
						'MAP_LOCATIONS_TYPE_2' => MAP_LOCATIONS_TYPE_2
					]
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $map_location_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->MapLocationsModel->mapLocationsInfoModel($map_location_id);
				if (isNotNull($sql)) {

					$rules1 = [
						'form.status' => [
							'label' => lang('AdminMapModule.mapLocations.general.status'),
							'rules' => 'required'
						],
						'form.map_category_id' => [
							'label' => lang('AdminMapModule.mapLocations.general.category'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.map_location_name' => [
							'label' => lang('AdminMapModule.mapLocations.general.name'),
							'rules' => 'required'
						],
						'form.map_type_id' => [
							'label' => lang('AdminMapModule.mapLocations.general.types.title'),
							'rules' => 'required'
						]
					];

					$rules2 = [];
					if ($this->request->getVar('form[map_type_id]') == MAP_LOCATIONS_TYPE_1) {
						$rules2 = [
							'form.map_location_lat_coordinate' => [
								'label' => lang('AdminMapModule.mapLocations.general.coordinate.lat'),
								'rules' => 'required'
							],
							'form.map_location_long_coordinate' => [
								'label' => lang('AdminMapModule.mapLocations.general.coordinate.long'),
								'rules' => 'required'
							]
						];
					}

					$rules3 = [];
					if ($this->request->getVar('form[map_type_id]') == MAP_LOCATIONS_TYPE_2) {
						$rules3 = [
							'form.map_project_id' => [
								'label' => lang('AdminMapModule.mapLocations.general.types.projects'),
								'rules' => 'required'
							]
						];
					}

					/*****************************************************/

					$rules = array_merge_recursive($rules1, $rules2, $rules3);

					if ($this->validate($rules)) {

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['map_location_lat_coordinate'] = trim(removeWhiteSpaces($this->request->getVar('form[map_location_lat_coordinate]')));
							$data['map_location_long_coordinate'] = trim(removeWhiteSpaces($this->request->getVar('form[map_location_long_coordinate]')));
							$data['map_location_updated_date'] = nowDate();
						}

						$result = $this->general->updateModel($this->table, $data, ['map_location_id' => $map_location_id]);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'map_location_id' => $map_location_id,
										'lang_id' => $lang_id,
										'map_location_name' => isNotNull($value['map_location_name']) ? $value['map_location_name'] : $this->request->getVar('lang['.$this->defaultLangId.'][map_location_name]')
									];

									$langControlModel = $this->MapLocationsModel->mapLocationsLangControlModel($map_location_id, $lang_id);
									if (isNotNull($langControlModel)) {
										$this->general->updateModel($this->tableLang, $lang_data, ['map_location_id' => $map_location_id, 'lang_id' => $lang_id]);
									} else {
										$this->general->insertModel($this->tableLang, $lang_data);
									}
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminMapModule.result.edit.mapLocations', [$this->request->getVar('lang['.$this->defaultLangId.'][map_location_name]')]));

							$ajax_message['success'] = TRUE;

							if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
								$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$map_location_id);
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

	public function delete(int $map_location_id) {
		$sql = $this->MapLocationsModel->mapLocationsInfoModel($map_location_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['map_location_id' => $map_location_id]);
			if ($delete) {

				// Lang
				$lang = $this->MapLocationsModel->mapLocationsLangModel($map_location_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['map_location_id' => $row->map_location_id]);
					}
				}

				$ajax_message['success'] = TRUE;
			} else {
				$ajax_message['error'] = lang('Admin.error.delete');
			}

		} else {
			$ajax_message['error'] = lang('Admin.error.noRecord');
		}

		return $this->response->setJSON($ajax_message);
	}
}
