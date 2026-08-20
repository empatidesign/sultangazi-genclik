<?php
namespace App\Controllers\Backend\Projects;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Projects\ProjectsStatusModel;
use App\Models\Backend\DatatableModel;

class ProjectsStatus extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $ProjectsStatusModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'projects_status';
		$this->tableLang = 'projects_status_lang';
		$this->pageUrl = ADMIN_URL_PROJECTS.'/'.ADMIN_URL_PROJECTS_STATUS;
		$this->ProjectsStatusModel = new ProjectsStatusModel();
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
			$column = ['status', 'status_mobile', 'project_status_name', 'project_total', 'project_status_order', 'project_status_created_date', 'project_status_updated_date', NULL];
			$search = [];
			$orderBy = ['project_status_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_status($row->status_mobile);
					$array[] = $row->project_status_name;
					$array[] = $row->project_total;
					$array[] = $row->project_status_order;
					$array[] = dateFormat($row->project_status_created_date, 'd-m-Y H:i:s');
					$array[] = $row->project_status_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->project_status_updated_date, 'd-m-Y H:i:s') : '--';
					$array[] = action_links($row->project_status_id, ['edit', 'delete'], $this->pageUrl);
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
						'label' => lang('AdminProjects.status.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.project_status_name' => [
						'label' => lang('AdminProjects.status.general.name'),
						'rules' => 'required'
					]
				];

				if ($this->validate($rules)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['project_status_created_date'] = nowDate();
					}

					$result = $this->general->insertModel($this->table, $data);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {
								$lang_data = [
									'project_status_id' => $result,
									'lang_id' => $lang_id,
									'project_status_name' => $value['project_status_name']
								];

								$this->general->insertModel($this->tableLang, $lang_data);
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminProjects.result.add.status', [$this->request->getVar('lang['.$this->defaultLangId.'][project_status_name]')]));

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

	public function edit(int $project_status_id) {
		$sql = $this->ProjectsStatusModel->projectStatusInfoModel($project_status_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->ProjectsStatusModel->projectStatusLangModel($project_status_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['project_status_name'] = $row->project_status_name;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'project_status_id' => $sql->project_status_id,
					'status' => $sql->status,
					'status_mobile' => $sql->status_mobile,
					'project_status_order' => $sql->project_status_order
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $project_status_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'form.status' => [
						'label' => lang('AdminProjects.status.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.project_status_name' => [
						'label' => lang('AdminProjects.status.general.name'),
						'rules' => 'required'
					]
				];

				if ($this->validate($rules)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['project_status_updated_date'] = nowDate();
					}

					$result = $this->general->updateModel($this->table, $data, ['project_status_id' => $project_status_id]);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {
								$lang_data = [
									'project_status_id' => $project_status_id,
									'lang_id' => $lang_id,
									'project_status_name' => $value['project_status_name']
								];

								$langControlModel = $this->ProjectsStatusModel->projectStatusLangControlModel($project_status_id, $lang_id);
								if (isNotNull($langControlModel)) {
									$this->general->updateModel($this->tableLang, $lang_data, ['project_status_id' => $project_status_id, 'lang_id' => $lang_id]);
								} else {
									$this->general->insertModel($this->tableLang, $lang_data);
								}
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminProjects.result.edit.status', [$this->request->getVar('lang['.$this->defaultLangId.'][project_status_name]')]));

						$ajax_message['success'] = TRUE;

						if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
							$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$project_status_id);
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

	public function delete(int $project_status_id) {
		$sql = $this->ProjectsStatusModel->projectStatusInfoModel($project_status_id);
		if (isNotNull($sql)) {

			$control = $this->ProjectsStatusModel->projectsControlModel($project_status_id);
			if (isNull($control)) {

				$delete = $this->general->deleteModel($this->table, ['project_status_id' => $project_status_id]);
				if ($delete) {

					// Lang
					$lang = $this->ProjectsStatusModel->projectStatusLangModel($project_status_id);
					if (isNotNull($lang)) {
						foreach ($lang as $row) {
							$this->general->deleteModel($this->tableLang, ['project_status_id' => $row->project_status_id]);
						}
					}

					$ajax_message['success'] = TRUE;
				} else {
					$ajax_message['error'] = lang('Admin.deleteError');
				}

			} else {
				$ajax_message['error'] = lang('AdminProjects.status.alert.cannotBeDeleted');
			}

		} else {
			$ajax_message['error'] = lang('Admin.error.noRecord');
		}

		return $this->response->setJSON($ajax_message);
	}
}
