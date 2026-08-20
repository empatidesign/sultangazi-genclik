<?php
namespace App\Controllers\Backend\Contents;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Contents\DirectorateCategoriesModel;
use App\Models\Backend\DatatableModel;

class DirectorateCategories extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $DirectorateCategoriesModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'directorate_categories';
		$this->tableLang = 'directorate_categories_lang';
		$this->pageUrl = ADMIN_URL_CONTENTS.'/'.ADMIN_URL_DIRECTORATE_CATEGORIES;
		$this->DirectorateCategoriesModel = new DirectorateCategoriesModel();
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
			$column = ['status', 'directorate_category_name', 'directorate_category_created_date', 'directorate_category_updated_date', NULL];
			$search = [];
			$orderBy = ['directorate_category_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = $row->directorate_category_name;
					$array[] = dateFormat($row->directorate_category_created_date, 'd-m-Y H:i:s');
					$array[] = $row->directorate_category_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->directorate_category_updated_date, 'd-m-Y H:i:s') : NULL;
					$array[] = action_links($row->directorate_category_id, ['edit', 'delete'], $this->pageUrl);
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
						'label' => lang('AdminContents.directorateCategories.general.status'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.directorate_category_name' => [
						'label' => lang('AdminContents.directorateCategories.general.name'),
						'rules' => 'required'
					]
				];

				if ($this->validate($rules)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['directorate_category_created_date'] = nowDate();
					}

					$result = $this->general->insertModel($this->table, $data);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {
								$lang_data = [
									'directorate_category_id' => $result,
									'lang_id' => $lang_id,
									'directorate_category_name' => trim($value['directorate_category_name'])
								];

								$this->general->insertModel($this->tableLang, $lang_data);
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.add.directorates', [$this->request->getVar('lang['.$this->defaultLangId.'][directorate_category_name]')]));

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

	public function edit(int $directorate_category_id) {
		$sql = $this->DirectorateCategoriesModel->directorateCategoriesInfoModel($directorate_category_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->DirectorateCategoriesModel->directorateCategoriesLangModel($directorate_category_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['directorate_category_name'] = $row->directorate_category_name;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'directorate_category_id' => $sql->directorate_category_id,
					'status' => $sql->status
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $directorate_category_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->DirectorateCategoriesModel->directorateCategoriesInfoModel($directorate_category_id);
				if (isNotNull($sql)) {

					$rules = [
						'form.status' => [
							'label' => lang('AdminContents.directorateCategories.general.status'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.directorate_category_name' => [
							'label' => lang('AdminContents.directorateCategories.general.name'),
							'rules' => 'required'
						]
					];

					if ($this->validate($rules)) {

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['directorate_category_updated_date'] = nowDate();
						}

						$result = $this->general->updateModel($this->table, $data, ['directorate_category_id' => $directorate_category_id]);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'directorate_category_id' => $directorate_category_id,
										'lang_id' => $lang_id,
										'directorate_category_name' => trim($value['directorate_category_name'])
									];

									$langControlModel = $this->DirectorateCategoriesModel->directorateCategoriesLangControlModel($directorate_category_id, $lang_id);
									if (isNotNull($langControlModel)) {
										$this->general->updateModel($this->tableLang, $lang_data, ['directorate_category_id' => $directorate_category_id, 'lang_id' => $lang_id]);
									} else {
										$this->general->insertModel($this->tableLang, $lang_data);
									}
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.edit.directorates', [$this->request->getVar('lang['.$this->defaultLangId.'][directorate_category_name]')]));

							$ajax_message['success'] = TRUE;

							if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
								$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$directorate_category_id);
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

	public function delete(int $directorate_category_id) {
		$sql = $this->DirectorateCategoriesModel->directorateCategoriesInfoModel($directorate_category_id);
		if (isNotNull($sql)) {

			$file_control = $this->DirectorateCategoriesModel->directoratesFileControlModel($directorate_category_id);
			if (isNull($file_control)) {

				$delete = $this->general->deleteModel($this->table, ['directorate_category_id' => $directorate_category_id]);
				if ($delete) {

					// Lang
					$lang = $this->DirectorateCategoriesModel->directorateCategoriesLangModel($directorate_category_id);
					if (isNotNull($lang)) {
						foreach ($lang as $row) {
							$this->general->deleteModel($this->tableLang, ['directorate_category_id' => $row->directorate_category_id]);
						}
					}

					$ajax_message['success'] = TRUE;
				} else {
					$ajax_message['error'] = lang('Admin.error.delete');
				}

			} else {
				$ajax_message['error'] = lang('AdminContents.directorateCategories.alert.cannotBeDeleted');
			}

		} else {
			$ajax_message['error'] = lang('Admin.error.noRecord');
		}

		return $this->response->setJSON($ajax_message);
	}
}
