<?php
namespace App\Controllers\Backend\Contents;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Contents\ContractsModel;
use App\Models\Backend\DatatableModel;

class Contracts extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $ContractsModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'contracts';
		$this->tableLang = 'contracts_lang';
		$this->pageUrl = ADMIN_URL_CONTENTS.'/'.ADMIN_URL_CONTRACTS;
		$this->ContractsModel = new ContractsModel();
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
			$column = ['status', 'contract_name', 'show_on_contact_page', 'show_on_president_contact_page', 'contract_created_date', 'contract_updated_date', NULL];
			$search = [];
			$orderBy = ['contract_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = $row->contract_name;
					$array[] = set_status($row->show_on_contact_page);
					$array[] = set_status($row->show_on_president_contact_page);
					$array[] = dateFormat($row->contract_created_date, 'd-m-Y H:i:s');
					$array[] = $row->contract_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->contract_updated_date, 'd-m-Y H:i:s') : NULL;
					$array[] = action_links($row->contract_id, ['edit', 'delete'], $this->pageUrl);
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
						'label' => lang('AdminContents.contracts.general.status'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.contract_name' => [
						'label' => lang('AdminContents.contracts.general.name'),
						'rules' => 'required'
					]
				];

				if ($this->validate($rules)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['show_on_contact_page'] = isNotNull($this->request->getVar('form[show_on_contact_page]')) ? $this->request->getVar('form[show_on_contact_page]') : FALSE;
						$data['show_on_president_contact_page'] = isNotNull($this->request->getVar('form[show_on_president_contact_page]')) ? $this->request->getVar('form[show_on_president_contact_page]') : FALSE;
						$data['contract_created_date'] = nowDate();
					}

					$result = $this->general->insertModel($this->table, $data);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {

								// Slug
								if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][contract_slug]'))) {
									$slug = slug($value['contract_slug']);
								} else {
									$slug = isNotNull($value['contract_name']) ? slug($value['contract_name']) : $this->request->getVar('lang['.$this->defaultLangId.'][contract_name]');
								}

								$lang_data = [
									'contract_id' => $result,
									'lang_id' => $lang_id,
									'contract_name' => isNotNull($value['contract_name']) ? $value['contract_name'] : $this->request->getVar('lang['.$this->defaultLangId.'][contract_name]'),
									'contract_description' => $value['contract_description'],
									'contract_meta_title' => $value['contract_meta_title'],
									'contract_meta_keywords' => $value['contract_meta_keywords'],
									'contract_meta_description' => $value['contract_meta_description'],
									'contract_slug' => $slug
								];

								$this->general->insertModel($this->tableLang, $lang_data);
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.add.contracts', [$this->request->getVar('lang['.$this->defaultLangId.'][contract_name]')]));

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

	public function edit(int $contract_id) {
		$sql = $this->ContractsModel->contractsInfoModel($contract_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->ContractsModel->contractsLangModel($contract_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['contract_name'] = $row->contract_name;
					$lang_array['data']['translations'][$row->lang_id]['contract_description'] = $row->contract_description;
					$lang_array['data']['translations'][$row->lang_id]['contract_meta_title'] = $row->contract_meta_title;
					$lang_array['data']['translations'][$row->lang_id]['contract_meta_keywords'] = $row->contract_meta_keywords;
					$lang_array['data']['translations'][$row->lang_id]['contract_meta_description'] = $row->contract_meta_description;
					$lang_array['data']['translations'][$row->lang_id]['contract_slug'] = $row->contract_slug;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'contract_id' => $sql->contract_id,
					'status' => $sql->status,
					'show_on_contact_page' => $sql->show_on_contact_page,
					'show_on_president_contact_page' => $sql->show_on_president_contact_page
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $contract_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'form.status' => [
						'label' => lang('AdminContents.contracts.general.status'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.contract_name' => [
						'label' => lang('AdminContents.contracts.general.name'),
						'rules' => 'required'
					]
				];

				if ($this->validate($rules)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['show_on_contact_page'] = $this->request->getVar('form[show_on_contact_page]');
						$data['show_on_president_contact_page'] = $this->request->getVar('form[show_on_president_contact_page]');
						$data['contract_updated_date'] = nowDate();
					}

					$result = $this->general->updateModel($this->table, $data, ['contract_id' => $contract_id]);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {

								// Slug
								if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][contract_slug]'))) {
									$slug = slug($value['contract_slug']);
								} else {
									$slug = isNotNull($value['contract_name']) ? slug($value['contract_name']) : $this->request->getVar('lang['.$this->defaultLangId.'][contract_name]');
								}

								$lang_data = [
									'contract_id' => $contract_id,
									'lang_id' => $lang_id,
									'contract_name' => isNotNull($value['contract_name']) ? $value['contract_name'] : $this->request->getVar('lang['.$this->defaultLangId.'][contract_name]'),
									'contract_description' => $value['contract_description'],
									'contract_meta_title' => $value['contract_meta_title'],
									'contract_meta_keywords' => $value['contract_meta_keywords'],
									'contract_meta_description' => $value['contract_meta_description'],
									'contract_slug' => $slug
								];

								$langControlModel = $this->ContractsModel->contractsLangControlModel($contract_id, $lang_id);
								if (isNotNull($langControlModel)) {
									$this->general->updateModel($this->tableLang, $lang_data, ['contract_id' => $contract_id, 'lang_id' => $lang_id]);
								} else {
									$this->general->insertModel($this->tableLang, $lang_data);
								}
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.edit.contracts', [$this->request->getVar('lang['.$this->defaultLangId.'][contract_name]')]));

						$ajax_message['success'] = TRUE;

						if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
							$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$contract_id);
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

	public function delete(int $contract_id) {
		$sql = $this->ContractsModel->contractsInfoModel($contract_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['contract_id' => $contract_id]);
			if ($delete) {

				// Lang
				$lang = $this->ContractsModel->contractsLangModel($contract_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['contract_id' => $row->contract_id]);
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
