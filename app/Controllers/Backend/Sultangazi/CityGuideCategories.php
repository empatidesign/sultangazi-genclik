<?php
namespace App\Controllers\Backend\Sultangazi;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Sultangazi\CityGuideCategoriesModel;
use App\Models\Backend\DatatableModel;

class CityGuideCategories extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $CityGuideCategoriesModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'city_guide_categories';
		$this->tableLang = 'city_guide_categories_lang';
		$this->pageUrl = ADMIN_URL_SULTANGAZI.'/'.ADMIN_URL_SULTANGAZI_CITY_GUIDE_CATEGORIES;
		$this->CityGuideCategoriesModel = new CityGuideCategoriesModel();
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
			$column = ['status', 'city_guide_category_name', 'city_guide_category_created_date', 'city_guide_category_updated_date', NULL];
			$search = [];
			$orderBy = ['city_guide_category_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = $row->city_guide_category_name;
					$array[] = dateFormat($row->city_guide_category_created_date, 'd-m-Y H:i:s');
					$array[] = $row->city_guide_category_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->city_guide_category_updated_date, 'd-m-Y H:i:s') : '--';
					$array[] = action_links($row->city_guide_category_id, ['edit', 'delete'], $this->pageUrl);
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
						'label' => lang('AdminSultangazi.cityGuideCategories.general.status'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.city_guide_category_name' => [
						'label' => lang('AdminSultangazi.cityGuideCategories.general.name'),
						'rules' => 'required'
					]
				];

				if ($this->validate($rules)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['city_guide_category_created_date'] = nowDate();
					}

					$result = $this->general->insertModel($this->table, $data);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {
								$lang_data = [
									'city_guide_category_id' => $result,
									'lang_id' => $lang_id,
									'city_guide_category_name' => trim($value['city_guide_category_name']),
									'city_guide_category_slug' => slug(trim($value['city_guide_category_name']))
								];

								$this->general->insertModel($this->tableLang, $lang_data);
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminSultangazi.result.add.cityGuideCategories', [$this->request->getVar('lang['.$this->defaultLangId.'][city_guide_category_name]')]));

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

	public function edit(int $city_guide_category_id) {
		$sql = $this->CityGuideCategoriesModel->cityGuideCategoriesInfoModel($city_guide_category_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->CityGuideCategoriesModel->cityGuideCategoriesLangModel($city_guide_category_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['city_guide_category_name'] = $row->city_guide_category_name;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'city_guide_category_id' => $sql->city_guide_category_id,
					'status' => $sql->status
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $city_guide_category_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->CityGuideCategoriesModel->cityGuideCategoriesInfoModel($city_guide_category_id);
				if (isNotNull($sql)) {

					$rules = [
						'form.status' => [
							'label' => lang('AdminSultangazi.cityGuideCategories.general.status'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.city_guide_category_name' => [
							'label' => lang('AdminSultangazi.cityGuideCategories.general.name'),
							'rules' => 'required'
						]
					];

					if ($this->validate($rules)) {

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['city_guide_category_updated_date'] = nowDate();
						}

						$result = $this->general->updateModel($this->table, $data, ['city_guide_category_id' => $city_guide_category_id]);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'city_guide_category_id' => $city_guide_category_id,
										'lang_id' => $lang_id,
										'city_guide_category_name' => trim($value['city_guide_category_name']),
										'city_guide_category_slug' => slug(trim($value['city_guide_category_name']))
									];

									$langControlModel = $this->CityGuideCategoriesModel->cityGuideCategoriesLangControlModel($city_guide_category_id, $lang_id);
									if (isNotNull($langControlModel)) {
										$this->general->updateModel($this->tableLang, $lang_data, ['city_guide_category_id' => $city_guide_category_id, 'lang_id' => $lang_id]);
									} else {
										$this->general->insertModel($this->tableLang, $lang_data);
									}
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminSultangazi.result.edit.cityGuideCategories', [$this->request->getVar('lang['.$this->defaultLangId.'][city_guide_category_name]')]));

							$ajax_message['success'] = TRUE;

							if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
								$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$city_guide_category_id);
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

	public function delete(int $city_guide_category_id) {
		$sql = $this->CityGuideCategoriesModel->cityGuideCategoriesInfoModel($city_guide_category_id);
		if (isNotNull($sql)) {

			$control = $this->CityGuideCategoriesModel->cityGuideContentsControlModel($city_guide_category_id);
			if (isNull($control)) {

				$delete = $this->general->deleteModel($this->table, ['city_guide_category_id' => $city_guide_category_id]);
				if ($delete) {

					// Lang
					$lang = $this->CityGuideCategoriesModel->cityGuideCategoriesLangModel($city_guide_category_id);
					if (isNotNull($lang)) {
						foreach ($lang as $row) {
							$this->general->deleteModel($this->tableLang, ['city_guide_category_id' => $row->city_guide_category_id]);
						}
					}

					$ajax_message['success'] = TRUE;
				} else {
					$ajax_message['error'] = lang('Admin.error.delete');
				}

			} else {
				$ajax_message['error'] = lang('AdminSultangazi.cityGuideCategories.alert.contents');
			}

		} else {
			$ajax_message['error'] = lang('Admin.error.noRecord');
		}

		return $this->response->setJSON($ajax_message);
	}
}
