<?php
namespace App\Controllers\Backend\Contents;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Contents\ActivityReportModel;
use App\Models\Backend\DatatableModel;

class ActivityReport extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $filePath;
	protected $ActivityReportModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'activity_report';
		$this->tableLang = 'activity_report_lang';
		$this->pageUrl = ADMIN_URL_CONTENTS.'/'.ADMIN_URL_ACTIVITY_REPORT;
		$this->filePath = FILE_PATH_ACTIVITY_REPORT;
		$this->ActivityReportModel = new ActivityReportModel();
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
			$column = ['status', NULL, 'activity_report_name', 'activity_report_year', 'activity_report_created_date', 'activity_report_updated_date', NULL];
			$search = [];
			$orderBy = ['activity_report_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = isNotNull($row->activity_report_file) ? '<div class="icon-demo-content"><a target="_blank" href="'.base_url($this->filePath.'/'.$row->activity_report_file).'"><i class="far fa-file-alt"></i></a></div>' : '--';
					$array[] = $row->activity_report_name;
					$array[] = $row->activity_report_year;
					$array[] = dateFormat($row->activity_report_created_date, 'd-m-Y H:i:s');
					$array[] = $row->activity_report_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->activity_report_updated_date, 'd-m-Y H:i:s') : NULL;
					$array[] = action_links($row->activity_report_id, ['edit', 'delete'], $this->pageUrl);
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
						'label' => lang('AdminContents.activityReport.general.status'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.activity_report_name' => [
						'label' => lang('AdminContents.activityReport.general.name'),
						'rules' => 'required'
					],
					'form.activity_report_year' => [
						'label' => lang('AdminContents.activityReport.general.year'),
						'rules' => 'required'
					]
				];

				/*****************************************************/

				// File Upload Validation
				$file = $this->request->getFile('activity_report_file');
				if (isNotNull($file)) {
					$rulesFile = [
						'activity_report_file' => [
							'label' => lang('AdminContents.activityReport.general.file.title'),
							'rules' => [
								'uploaded[activity_report_file]',
								'mime_in[activity_report_file,'.FILE_UPLOAD_MIME.']',
								'max_size[activity_report_file,'.FILE_UPLOAD_SIZE.']'
							]
						]
					];

					$rules = array_merge($rules, $rulesFile);
				}

				/*****************************************************/

				if ($this->validate($rules)) {

					// File Upload
					$fileName = '';
					if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
						$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][activity_report_name]')).'_'.$file->getRandomName();
						$file->move($this->filePath, $fileName);
					}

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['activity_report_file'] = $fileName;
						$data['activity_report_created_date'] = nowDate();
					}

					$result = $this->general->insertModel($this->table, $data);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {
								$lang_data = [
									'activity_report_id' => $result,
									'lang_id' => $lang_id,
									'activity_report_name' => trim(upper($value['activity_report_name']))
								];

								$this->general->insertModel($this->tableLang, $lang_data);
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.add.activityReport', [$this->request->getVar('lang['.$this->defaultLangId.'][activity_report_name]')]));

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

	public function edit(int $activity_report_id) {
		$sql = $this->ActivityReportModel->activityReportInfoModel($activity_report_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->ActivityReportModel->activityReportLangModel($activity_report_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['activity_report_name'] = $row->activity_report_name;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'activity_report_id' => $sql->activity_report_id,
					'status' => $sql->status,
					'activity_report_year' => $sql->activity_report_year,
					'file' => isNotNull($sql->activity_report_file) ? base_url($this->filePath.'/'.$sql->activity_report_file) : NULL,
					'file_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-file/'.$sql->activity_report_id)
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $activity_report_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->ActivityReportModel->activityReportInfoModel($activity_report_id);
				if (isNotNull($sql)) {

					$rules = [
						'form.status' => [
							'label' => lang('AdminContents.activityReport.general.status'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.activity_report_name' => [
							'label' => lang('AdminContents.activityReport.general.name'),
							'rules' => 'required'
						],
						'form.activity_report_year' => [
							'label' => lang('AdminContents.activityReport.general.year'),
							'rules' => 'required'
						]
					];

					/*****************************************************/

					// File Upload Validation
					$file = $this->request->getFile('activity_report_file');
					if (isNotNull($file)) {
						$rulesFile = [
							'activity_report_file' => [
								'label' => lang('AdminContents.activityReport.general.file.title'),
								'rules' => [
									'uploaded[activity_report_file]',
									'mime_in[activity_report_file,'.FILE_UPLOAD_MIME.']',
									'max_size[activity_report_file,'.FILE_UPLOAD_SIZE.']'
								]
							]
						];

						$rules = array_merge($rules, $rulesFile);
					}

					/*****************************************************/

					if ($this->validate($rules)) {

						// File Upload
						$fileName = $sql->activity_report_file;
						if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->activity_report_file);

							$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][activity_report_name]')).'_'.$file->getRandomName();
							$file->move($this->filePath, $fileName);
						}

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['activity_report_file'] = $fileName;
							$data['activity_report_updated_date'] = nowDate();
						}

						$result = $this->general->updateModel($this->table, $data, ['activity_report_id' => $activity_report_id]);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'activity_report_id' => $activity_report_id,
										'lang_id' => $lang_id,
										'activity_report_name' => trim(upper($value['activity_report_name']))
									];

									$langControlModel = $this->ActivityReportModel->activityReportLangControlModel($activity_report_id, $lang_id);
									if (isNotNull($langControlModel)) {
										$this->general->updateModel($this->tableLang, $lang_data, ['activity_report_id' => $activity_report_id, 'lang_id' => $lang_id]);
									} else {
										$this->general->insertModel($this->tableLang, $lang_data);
									}
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.edit.activityReport', [$this->request->getVar('lang['.$this->defaultLangId.'][activity_report_name]')]));

							$ajax_message['success'] = TRUE;

							if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
								$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$activity_report_id);
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

	public function delete(int $activity_report_id) {
		$sql = $this->ActivityReportModel->activityReportInfoModel($activity_report_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['activity_report_id' => $activity_report_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePath, $sql->activity_report_file);

				// Lang
				$lang = $this->ActivityReportModel->activityReportLangModel($activity_report_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['activity_report_id' => $row->activity_report_id]);
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

	public function removeFile(int $activity_report_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->table, ['activity_report_file' => ''], ['activity_report_id' => $activity_report_id], $this->filePath);
				if ($result == TRUE) {
					$ajax_message['success'] = TRUE;
				} else {
					$ajax_message['error'] = lang('Admin.error.description');
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
