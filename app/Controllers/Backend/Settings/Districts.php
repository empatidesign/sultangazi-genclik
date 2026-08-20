<?php
namespace App\Controllers\Backend\Settings;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\SettingModel;
use App\Models\Backend\DatatableModel;

class Districts extends BaseController {

	protected $table;
	protected $pageUrl;
	protected $SettingModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'districts';
		$this->pageUrl = ADMIN_URL_SETTINGS.'/'.ADMIN_URL_DISTRICTS;
		$this->SettingModel = new SettingModel();
		$this->DatatableModel = new DatatableModel();
	}

	public function index() {
		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_name' => 'index',
			'page_url' => $this->pageUrl,
			'datatable_url' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/datatable'),
			'list' => [
				'countries' => $this->SettingModel->countriesListModel()
			]
		]);
	}

	public function datatable() {
		if ($this->request->isAJAX()) {
			$column = ['status', 'country_name', 'city_name', 'district_name', NULL];
			$search = ['country_id', 'city_id', 'district_name'];
			$orderBy = ['country_name' => 'ASC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = $row->country_name;
					$array[] = $row->city_name;
					$array[] = $row->district_name;
					$array[] = action_links($row->district_id, ['edit', 'delete'], $this->pageUrl);
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
				'countries' => $this->SettingModel->countriesListModel()
			]
		]);
	}

	public function insert() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'form.status' => [
						'label' => lang('AdminSettings.districts.general.status'),
						'rules' => 'required'
					],
					'form.country_id' => [
						'label' => lang('AdminSettings.districts.general.countryName'),
						'rules' => 'required'
					],
					'form.city_id' => [
						'label' => lang('AdminSettings.districts.general.cityName'),
						'rules' => 'required'
					],
					'form.district_name' => [
						'label' => lang('AdminSettings.districts.general.districtName'),
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

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminSettings.result.add.districts', [$this->request->getVar('form[district_name]')]));

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

	public function edit(int $district_id) {
		$sql = $this->SettingModel->districtsInfoModel($district_id);
		if (isNotNull($sql)) {

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'result' => [
					'district_id' => $sql->district_id,
					'status' => $sql->status,
					'district_name' => $sql->district_name,
					'country_id' => $sql->country_id,
					'city_id' => $sql->city_id
				],
				'list' => [
					'countries' => $this->SettingModel->countriesListModel(),
					'cities' => $this->SettingModel->citiesSelectedModel($sql->country_id)
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $district_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'form.status' => [
						'label' => lang('AdminSettings.districts.general.status'),
						'rules' => 'required'
					],
					'form.country_id' => [
						'label' => lang('AdminSettings.districts.general.countryName'),
						'rules' => 'required'
					],
					'form.city_id' => [
						'label' => lang('AdminSettings.districts.general.cityName'),
						'rules' => 'required'
					],
					'form.district_name' => [
						'label' => lang('AdminSettings.districts.general.districtName'),
						'rules' => 'required'
					]
				];

				if ($this->validate($rules)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
					}

					$result = $this->general->updateModel($this->table, $data, ['district_id' => $district_id]);
					if ($result !== FALSE) {

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminSettings.result.edit.districts', [$this->request->getVar('form[district_name]')]));

						$ajax_message['success'] = TRUE;

						if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
							$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$district_id);
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
				$ajax_message['error'] = lang('Admin.error.description');
			}
		} else {
			$ajax_message['error'] = lang('Admin.error.ajax');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function delete(int $district_id) {
		$sql = $this->SettingModel->districtsInfoModel($district_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['district_id' => $district_id]);
			if ($delete) {
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
