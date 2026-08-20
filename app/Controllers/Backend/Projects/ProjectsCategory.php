<?php
namespace App\Controllers\Backend\Projects;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Projects\ProjectsCategoryModel;
use App\Models\Backend\DatatableModel;

class ProjectsCategory extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $ProjectsCategoryModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'projects_category';
		$this->tableLang = 'projects_category_lang';
		$this->pageUrl = ADMIN_URL_PROJECTS.'/'.ADMIN_URL_PROJECTS_CATEGORY;
		$this->ProjectsCategoryModel = new ProjectsCategoryModel();
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
			$column = ['status', 'status_mobile', 'home_page', 'project_category_icon', 'project_category_name', 'project_total', 'project_category_order', 'project_category_created_date', 'project_category_updated_date', NULL];
			$search = [];
			$orderBy = ['project_category_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_status($row->status_mobile);
					$array[] = set_status($row->home_page);
					$array[] = isNotNull($row->project_category_icon) ? $row->project_category_icon : '--';
					$array[] = $row->project_category_name;
					$array[] = $row->project_total;
					$array[] = $row->project_category_order;
					$array[] = dateFormat($row->project_category_created_date, 'd-m-Y H:i:s');
					$array[] = $row->project_category_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->project_category_updated_date, 'd-m-Y H:i:s') : '--';
					$array[] = action_links($row->project_category_id, ['edit', 'delete'], $this->pageUrl);
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
						'label' => lang('AdminProjects.categories.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					],
					'form.home_page' => [
						'label' => lang('AdminProjects.categories.general.homePage'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.project_category_name' => [
						'label' => lang('AdminProjects.categories.general.name'),
						'rules' => 'required'
					]
				];

				if ($this->validate($rules)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['project_category_icon'] = trim($this->request->getVar('form[project_category_icon]'));
						$data['project_category_created_date'] = nowDate();
					}

					$result = $this->general->insertModel($this->table, $data);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {

								// Slug
								if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][project_category_slug]'))) {
									$slug = slug($value['project_category_slug']);
								} else {
									$slug = isNotNull($value['project_category_name']) ? slug($value['project_category_name']) : $this->request->getVar('lang['.$this->defaultLangId.'][project_category_name]');
								}

								$lang_data = [
									'project_category_id' => $result,
									'lang_id' => $lang_id,
									'project_category_name' => $value['project_category_name'],
									'project_category_slug' => $slug
								];

								$this->general->insertModel($this->tableLang, $lang_data);
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminProjects.result.add.categories', [$this->request->getVar('lang['.$this->defaultLangId.'][project_category_name]')]));

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

	public function edit(int $project_category_id) {
		$sql = $this->ProjectsCategoryModel->projectsCategoryInfoModel($project_category_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->ProjectsCategoryModel->projectsCategoryLangModel($project_category_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['project_category_name'] = $row->project_category_name;
					$lang_array['data']['translations'][$row->lang_id]['project_category_slug'] = $row->project_category_slug;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'project_category_id' => $sql->project_category_id,
					'status' => $sql->status,
					'status_mobile' => $sql->status_mobile,
					'home_page' => $sql->home_page,
					'project_category_icon' => $sql->project_category_icon,
					'project_category_order' => $sql->project_category_order
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $project_category_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'form.status' => [
						'label' => lang('AdminProjects.categories.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					],
					'form.home_page' => [
						'label' => lang('AdminProjects.categories.general.homePage'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.project_category_name' => [
						'label' => lang('AdminProjects.categories.general.name'),
						'rules' => 'required'
					]
				];

				if ($this->validate($rules)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['project_category_icon'] = trim($this->request->getVar('form[project_category_icon]'));
						$data['project_category_updated_date'] = nowDate();
					}

					$result = $this->general->updateModel($this->table, $data, ['project_category_id' => $project_category_id]);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {

								// Slug
								if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][project_category_slug]'))) {
									$slug = slug($value['project_category_slug']);
								} else {
									$slug = isNotNull($value['project_category_name']) ? slug($value['project_category_name']) : $this->request->getVar('lang['.$this->defaultLangId.'][project_category_name]');
								}

								$lang_data = [
									'project_category_id' => $project_category_id,
									'lang_id' => $lang_id,
									'project_category_name' => $value['project_category_name'],
									'project_category_slug' => $slug
								];

								$langControlModel = $this->ProjectsCategoryModel->projectsCategoryLangControlModel($project_category_id, $lang_id);
								if (isNotNull($langControlModel)) {
									$this->general->updateModel($this->tableLang, $lang_data, ['project_category_id' => $project_category_id, 'lang_id' => $lang_id]);
								} else {
									$this->general->insertModel($this->tableLang, $lang_data);
								}
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminProjects.result.edit.categories', [$this->request->getVar('lang['.$this->defaultLangId.'][project_category_name]')]));

						$ajax_message['success'] = TRUE;

						if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
							$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$project_category_id);
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

	public function delete(int $project_category_id) {
		$sql = $this->ProjectsCategoryModel->projectsCategoryInfoModel($project_category_id);
		if (isNotNull($sql)) {

			$control = $this->ProjectsCategoryModel->projectsControlModel($project_category_id);
			if (isNull($control)) {

				$delete = $this->general->deleteModel($this->table, ['project_category_id' => $project_category_id]);
				if ($delete) {

					// Lang
					$lang = $this->ProjectsCategoryModel->projectsCategoryLangModel($project_category_id);
					if (isNotNull($lang)) {
						foreach ($lang as $row) {
							$this->general->deleteModel($this->tableLang, ['project_category_id' => $row->project_category_id]);
						}
					}

					$ajax_message['success'] = TRUE;
				} else {
					$ajax_message['error'] = lang('Admin.deleteError');
				}

			} else {
				$ajax_message['error'] = lang('AdminProjects.categories.alert.cannotBeDeleted');
			}

		} else {
			$ajax_message['error'] = lang('Admin.error.noRecord');
		}

		return $this->response->setJSON($ajax_message);
	}
}
