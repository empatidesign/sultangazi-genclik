<?php

namespace App\Controllers\Backend\Contents;

use App\Controllers\Backend\BaseController;

use App\Models\Backend\Contents\StatisticsModel;
use App\Models\Backend\DatatableModel;

class Statistics extends BaseController
{

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $StatisticsModel;
	protected $DatatableModel;

	public function __construct()
	{
		$this->table = 'statistics';
		$this->tableLang = 'statistics_lang';
		$this->pageUrl = ADMIN_URL_CONTENTS . '/' . ADMIN_URL_STATISTIC;
		$this->StatisticsModel = new StatisticsModel();
		$this->DatatableModel = new DatatableModel();
	}

	public function index()
	{
		return $this->twig->render($this->BACKEND_TEMPLATE_PATH . '/' . $this->pageUrl . '.html', [
			'page_name' => 'index',
			'page_url' => $this->pageUrl,
			'datatable_url' => base_url(BACKEND_URL . '/' . $this->pageUrl . '/datatable')
		]);
	}

	public function datatable()
	{
		if ($this->request->isAJAX()) {
			$column = ['status', 'statistic_name', 'statistic_created_date', 'statistic_updated_date', NULL];
			$search = [];
			$orderBy = ['statistic_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = $row->statistic_name;
					$array[] = dateFormat($row->statistic_created_date, 'd-m-Y H:i:s');
					$array[] = $row->statistic_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->statistic_updated_date, 'd-m-Y H:i:s') : NULL;
					$array[] = action_links($row->statistic_id, ['edit', 'delete'], $this->pageUrl);
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

	public function add()
	{
		return $this->twig->render($this->BACKEND_TEMPLATE_PATH . '/' . $this->pageUrl . '.html', [
			'page_name' => 'add',
			'page_url' => $this->pageUrl
		]);
	}

	public function insert()
	{
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'form.status' => [
						'label' => lang('AdminContents.statistics.general.status'),
						'rules' => 'required'
					],
					'lang.' . $this->defaultLangId . '.statistic_name' => [
						'label' => lang('AdminContents.statistics.general.name'),
						'rules' => 'required'
					]
				];

				/*****************************************************/

				if ($this->validate($rules)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
					}
					$data['statistic_created_date'] = nowDate();

					$result = $this->general->insertModel($this->table, $data);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {
								$lang_data = [
									'statistic_id' => $result,
									'lang_id' => $lang_id,
									'statistic_name' => isNotNull($value['statistic_name']) ? $value['statistic_name'] : $this->request->getVar('lang[' . $this->defaultLangId . '][statistic_name]')
								];

								$this->general->insertModel($this->tableLang, $lang_data);
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.add.statistics', [$this->request->getVar('lang[' . $this->defaultLangId . '][statistic_name]')]));

						$ajax_message['success'] = TRUE;
						$ajax_message['url'] = base_url(BACKEND_URL . '/' . $this->pageUrl);
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

	public function edit(int $statistic_id)
	{
		$sql = $this->StatisticsModel->statisticsInfoModel($statistic_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->StatisticsModel->statisticsLangModel($statistic_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['statistic_name'] = $row->statistic_name;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH . '/' . $this->pageUrl . '.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'statistic_id' => $sql->statistic_id,
					'statistic_number' => $sql->statistic_number,
					'status' => $sql->status,
				]
			]);
		} else {
			return redirect()->to(BACKEND_URL . '/404');
		}
	}

	public function update(int $statistic_id)
	{
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->StatisticsModel->statisticsInfoModel($statistic_id);
				if (isNotNull($sql)) {

					$rules = [
						'form.status' => [
							'label' => lang('AdminContents.statistics.general.status'),
							'rules' => 'required'
						],
						'lang.' . $this->defaultLangId . '.statistic_name' => [
							'label' => lang('AdminContents.statistics.general.name'),
							'rules' => 'required'
						]
					];

					/*****************************************************/

					// Image Upload Validation
					$file = $this->request->getFile('statistic_image');
					if (isNotNull($file)) {
						$rulesImage = [
							'statistic_image' => [
								'label' => lang('AdminContents.statistics.general.image'),
								'rules' => [
									'uploaded[statistic_image]',
									'mime_in[statistic_image,' . IMAGE_UPLOAD_MIME . ']',
									'max_size[statistic_image,' . IMAGE_UPLOAD_SIZE . ']'
								]
							]
						];

						$rules = array_merge($rules, $rulesImage);
					}

					/*****************************************************/

					if ($this->validate($rules)) {
						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['statistic_updated_date'] = nowDate();
						}

						$result = $this->general->updateModel($this->table, $data, ['statistic_id' => $statistic_id]);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'statistic_id' => $statistic_id,
										'lang_id' => $lang_id,
										'statistic_name' => isNotNull($value['statistic_name']) ? $value['statistic_name'] : $this->request->getVar('lang[' . $this->defaultLangId . '][statistic_name]')
									];

									$langControlModel = $this->StatisticsModel->statisticsLangControlModel($statistic_id, $lang_id);
									if (isNotNull($langControlModel)) {
										$this->general->updateModel($this->tableLang, $lang_data, ['statistic_id' => $statistic_id, 'lang_id' => $lang_id]);
									} else {
										$this->general->insertModel($this->tableLang, $lang_data);
									}
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.edit.statistics', [$this->request->getVar('lang[' . $this->defaultLangId . '][statistic_name]')]));

							$ajax_message['success'] = TRUE;

							if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
								$ajax_message['url'] = base_url(BACKEND_URL . '/' . $this->pageUrl . '/edit/' . $statistic_id);
							} else {
								$ajax_message['url'] = base_url(BACKEND_URL . '/' . $this->pageUrl);
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

	public function delete(int $statistic_id)
	{
		$sql = $this->StatisticsModel->statisticsInfoModel($statistic_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['statistic_id' => $statistic_id]);
			if ($delete) {
				// Lang
				$lang = $this->StatisticsModel->statisticsLangModel($statistic_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['statistic_id' => $row->statistic_id]);
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
