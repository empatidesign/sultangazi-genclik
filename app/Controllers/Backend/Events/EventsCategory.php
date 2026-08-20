<?php
namespace App\Controllers\Backend\Events;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Events\EventsCategoryModel;
use App\Models\Backend\DatatableModel;

class EventsCategory extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $EventsCategoryModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'events_category';
		$this->tableLang = 'events_category_lang';
		$this->pageUrl = ADMIN_URL_EVENTS.'/'.ADMIN_URL_EVENTS_CATEGORY;
		$this->EventsCategoryModel = new EventsCategoryModel();
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
			$column = ['status', 'event_category_name', 'event_total', 'event_category_order', 'event_category_created_date', 'event_category_updated_date', NULL];
			$search = [];
			$orderBy = ['event_category_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = $row->event_category_name;
					$array[] = $row->event_total;
					$array[] = $row->event_category_order;
					$array[] = dateFormat($row->event_category_created_date, 'd-m-Y H:i:s');
					$array[] = $row->event_category_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->event_category_updated_date, 'd-m-Y H:i:s') : '--';
					$array[] = action_links($row->event_category_id, ['edit', 'delete'], $this->pageUrl);
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
						'label' => lang('AdminEvents.categories.general.status'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.event_category_name' => [
						'label' => lang('AdminEvents.categories.general.name'),
						'rules' => 'required'
					]
				];

				if ($this->validate($rules)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['event_category_created_date'] = nowDate();
					}

					$result = $this->general->insertModel($this->table, $data);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {
								$lang_data = [
									'event_category_id' => $result,
									'lang_id' => $lang_id,
									'event_category_name' => $value['event_category_name']
								];

								$this->general->insertModel($this->tableLang, $lang_data);
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminEvents.result.add.categories', [$this->request->getVar('lang['.$this->defaultLangId.'][event_category_name]')]));

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

	public function edit(int $event_category_id) {
		$sql = $this->EventsCategoryModel->eventsCategoryInfoModel($event_category_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->EventsCategoryModel->eventsCategoryLangModel($event_category_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['event_category_name'] = $row->event_category_name;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'event_category_id' => $sql->event_category_id,
					'status' => $sql->status,
					'event_category_order' => $sql->event_category_order
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $event_category_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'form.status' => [
						'label' => lang('AdminEvents.categories.general.status'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.event_category_name' => [
						'label' => lang('AdminEvents.categories.general.name'),
						'rules' => 'required'
					]
				];

				if ($this->validate($rules)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['event_category_updated_date'] = nowDate();
					}

					$result = $this->general->updateModel($this->table, $data, ['event_category_id' => $event_category_id]);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {
								$lang_data = [
									'event_category_id' => $event_category_id,
									'lang_id' => $lang_id,
									'event_category_name' => $value['event_category_name']
								];

								$langControlModel = $this->EventsCategoryModel->eventsCategoryLangControlModel($event_category_id, $lang_id);
								if (isNotNull($langControlModel)) {
									$this->general->updateModel($this->tableLang, $lang_data, ['event_category_id' => $event_category_id, 'lang_id' => $lang_id]);
								} else {
									$this->general->insertModel($this->tableLang, $lang_data);
								}
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminEvents.result.edit.categories', [$this->request->getVar('lang['.$this->defaultLangId.'][event_category_name]')]));

						$ajax_message['success'] = TRUE;

						if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
							$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$event_category_id);
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

	public function delete(int $event_category_id) {
		$sql = $this->EventsCategoryModel->eventsCategoryInfoModel($event_category_id);
		if (isNotNull($sql)) {

			$control = $this->EventsCategoryModel->eventsControlModel($event_category_id);
			if (isNull($control)) {

				$delete = $this->general->deleteModel($this->table, ['event_category_id' => $event_category_id]);
				if ($delete) {

					// Lang
					$lang = $this->EventsCategoryModel->eventsCategoryLangModel($event_category_id);
					if (isNotNull($lang)) {
						foreach ($lang as $row) {
							$this->general->deleteModel($this->tableLang, ['event_category_id' => $row->event_category_id]);
						}
					}

					$ajax_message['success'] = TRUE;
				} else {
					$ajax_message['error'] = lang('Admin.deleteError');
				}

			} else {
				$ajax_message['error'] = lang('AdminEvents.categories.alert.cannotBeDeleted');
			}

		} else {
			$ajax_message['error'] = lang('Admin.error.noRecord');
		}

		return $this->response->setJSON($ajax_message);
	}
}
