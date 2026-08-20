<?php
namespace App\Controllers\Backend\MapModule;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\MapModule\MapCategoriesModel;
use App\Models\Backend\DatatableModel;

class MapCategories extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $MapCategoriesModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'map_categories';
		$this->tableLang = 'map_categories_lang';
		$this->pageUrl = ADMIN_URL_MAP_MODULE.'/'.ADMIN_URL_MAP_CATEGORIES;
		$this->MapCategoriesModel = new MapCategoriesModel();
		$this->DatatableModel = new DatatableModel();
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
			$column = ['status', 'map_category_default', 'map_category_name', 'map_category_created_date', 'map_category_updated_date', NULL];
			$search = [];
			$orderBy = ['map_category_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_status($row->map_category_default);
					$array[] = $row->map_category_name;
					$array[] = dateFormat($row->map_category_created_date, 'd-m-Y H:i:s');
					$array[] = $row->map_category_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->map_category_updated_date, 'd-m-Y H:i:s') : '--';
					$array[] = action_links($row->map_category_id, ['confirmation', 'edit', 'delete'], $this->pageUrl, $row->map_category_default);
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
			'page_url' => $this->pageUrl
		]);
	}

	public function insert() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'form.status' => [
						'label' => lang('AdminMapModule.mapCategories.general.status'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.map_category_name' => [
						'label' => lang('AdminMapModule.mapCategories.general.name'),
						'rules' => 'required'
					]
				];

				if ($this->validate($rules)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['map_category_created_date'] = nowDate();
					}

					$result = $this->general->insertModel($this->table, $data);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {
								$lang_data = [
									'map_category_id' => $result,
									'lang_id' => $lang_id,
									'map_category_name' => isNotNull($value['map_category_name']) ? $value['map_category_name'] : $this->request->getVar('lang['.$this->defaultLangId.'][map_category_name]')
								];

								$this->general->insertModel($this->tableLang, $lang_data);
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminMapModule.result.add.mapCategories', [$this->request->getVar('lang['.$this->defaultLangId.'][map_category_name]')]));

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

	public function edit(int $map_category_id) {
		$sql = $this->MapCategoriesModel->mapCategoriesInfoModel($map_category_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->MapCategoriesModel->mapCategoriesLangModel($map_category_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['map_category_name'] = $row->map_category_name;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'map_category_id' => $sql->map_category_id,
					'status' => $sql->status
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $map_category_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->MapCategoriesModel->mapCategoriesInfoModel($map_category_id);
				if (isNotNull($sql)) {

					$rules = [
						'form.status' => [
							'label' => lang('AdminMapModule.mapCategories.general.status'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.map_category_name' => [
							'label' => lang('AdminMapModule.mapCategories.general.name'),
							'rules' => 'required'
						]
					];

					if ($this->validate($rules)) {

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['map_category_updated_date'] = nowDate();
						}

						$result = $this->general->updateModel($this->table, $data, ['map_category_id' => $map_category_id]);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'map_category_id' => $map_category_id,
										'lang_id' => $lang_id,
										'map_category_name' => isNotNull($value['map_category_name']) ? $value['map_category_name'] : $this->request->getVar('lang['.$this->defaultLangId.'][map_category_name]')
									];

									$langControlModel = $this->MapCategoriesModel->mapCategoriesLangControlModel($map_category_id, $lang_id);
									if (isNotNull($langControlModel)) {
										$this->general->updateModel($this->tableLang, $lang_data, ['map_category_id' => $map_category_id, 'lang_id' => $lang_id]);
									} else {
										$this->general->insertModel($this->tableLang, $lang_data);
									}
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminMapModule.result.edit.mapCategories', [$this->request->getVar('lang['.$this->defaultLangId.'][map_category_name]')]));

							$ajax_message['success'] = TRUE;

							if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
								$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$map_category_id);
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

	public function delete(int $map_category_id) {
		$sql = $this->MapCategoriesModel->mapCategoriesInfoModel($map_category_id);
		if (isNotNull($sql)) {

			if ($sql->map_category_default == DEFAULT_RECORD_PASSIVE) {

				$delete = $this->general->deleteModel($this->table, ['map_category_id' => $map_category_id]);
				if ($delete) {

					// Lang
					$lang = $this->MapCategoriesModel->mapCategoriesLangModel($map_category_id);
					if (isNotNull($lang)) {
						foreach ($lang as $row) {
							$this->general->deleteModel($this->tableLang, ['map_category_id' => $row->map_category_id]);
						}
					}

					$ajax_message['success'] = TRUE;
				} else {
					$ajax_message['error'] = lang('Admin.error.delete');
				}

			} else {
				$ajax_message['error'] = lang('AdminMapModule.mapCategories.alert.default.delete');
			}

		} else {
			$ajax_message['error'] = lang('Admin.error.noRecord');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function confirmation(int $map_category_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->MapCategoriesModel->mapCategoriesInfoModel($map_category_id);
				if (isNotNull($sql)) {

					// If there is a default, make it passive
					$list_sql = $this->MapCategoriesModel->mapCategoriesListModel();
					if (isNotNull($list_sql)) {
						foreach ($list_sql as $row) {
							$this->general->updateModel($this->table, ['map_category_default' => DEFAULT_RECORD_PASSIVE], ['map_category_id' => $row->map_category_id]);
						}
					}

					/*****************************************************/

					$result = $this->general->updateModel($this->table, ['map_category_default' => DEFAULT_RECORD_ACTIVE], ['map_category_id' => $map_category_id]);
					if ($result) {
						$ajax_message['success'] = TRUE;
						$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);
					} else {
						$ajax_message['error'] = lang('Admin.error.update');
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
}
