<?php
namespace App\Controllers\Backend\Settings;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\SettingModel;
use App\Models\Backend\DatatableModel;

class LanguageManagement extends BaseController {

	protected $table;
	protected $pageUrl;
	protected $SettingModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'languages';
		$this->pageUrl = ADMIN_URL_SETTINGS.'/'.ADMIN_URL_LANGUAGE_MANAGEMENT;
		$this->SettingModel = new SettingModel();
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
			$column = ['lang_status', 'lang_code', 'lang_title', 'lang_set_locale', 'lang_icon', 'frontend_lang_default', 'backend_lang_default', NULL];
			$search = [];
			$orderBy = ['lang_id' => 'ASC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {

					$frontend_default = $row->lang_id == $row->frontend_lang_default ? set_danger(lang('Admin.default.title')) : NULL;
					$backend_default = $row->lang_id == $row->backend_lang_default ? set_danger(lang('Admin.default.title')) : NULL;

					$array = [];
					$array[] = set_status($row->lang_status);
					$array[] = $row->lang_code;
					$array[] = $row->lang_title;
					$array[] = $row->lang_set_locale;
					$array[] = set_image(FILE_PATH_FLAGS.$row->lang_icon);
					$array[] = $frontend_default;
					$array[] = $backend_default;
					$array[] = action_links($row->lang_id, ['edit'], $this->pageUrl);
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

	public function edit(int $lang_id) {
		$sql = $this->SettingModel->languageManagementInfoModel($lang_id);
		if (isNotNull($sql)) {

			$flags = [];
			$handle = opendir(FCPATH.FILE_PATH_FLAGS);
			if (isNotNull($handle)) {
				while ($file = readdir($handle)) {
					if ($file != '.' && $file != '..') {
						$flags[] = [
							'file' => $file
						];
					}
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'result' => $sql,
				'list' => [
					'setLocale' => $this->SettingModel->languageManagementSetLocaleModel(),
					'flags' => $flags
				],
				'PARAMETER' => [
					'FILE_PATH_FLAGS' => FILE_PATH_FLAGS,
					'LANGUAGES_PERCENTAGE_LOCATION_LEFT' => LANGUAGES_PERCENTAGE_LOCATION_LEFT,
					'LANGUAGES_PERCENTAGE_LOCATION_RIGHT' => LANGUAGES_PERCENTAGE_LOCATION_RIGHT
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $lang_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'form.lang_status' => [
						'label' => lang('AdminSettings.languageManagement.general.status'),
						'rules' => 'required'
					],
					'form.lang_code' => [
						'label' => lang('AdminSettings.languageManagement.general.code'),
						'rules' => 'required'
					],
					'form.lang_title' => [
						'label' => lang('AdminSettings.languageManagement.general.title'),
						'rules' => 'required'
					],
					'form.lang_set_locale' => [
						'label' => lang('AdminSettings.languageManagement.general.setLocale'),
						'rules' => 'required'
					]
				];

				if ($this->validate($rules)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
					}

					$result = $this->general->updateModel($this->table, $data, ['lang_id' => $lang_id]);
					if ($result !== FALSE) {

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminSettings.result.edit.languageManagement', [$this->request->getVar('form[lang_title]')]));

						$ajax_message['success'] = TRUE;

						if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
							$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$lang_id);
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
}
