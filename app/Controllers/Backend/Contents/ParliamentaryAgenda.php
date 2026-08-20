<?php
namespace App\Controllers\Backend\Contents;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Contents\ParliamentaryAgendaModel;
use App\Models\Backend\DatatableModel;

class ParliamentaryAgenda extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $filePath;
	protected $ParliamentaryAgendaModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'parliamentary_agenda';
		$this->tableLang = 'parliamentary_agenda_lang';
		$this->pageUrl = ADMIN_URL_CONTENTS.'/'.ADMIN_URL_PARLIAMENTARY_AGENDA;
		$this->filePath = FILE_PATH_PARLIAMENTARY_AGENDA;
		$this->ParliamentaryAgendaModel = new ParliamentaryAgendaModel();
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
			$column = ['status', NULL, 'parliamentary_agenda_name', 'parliamentary_agenda_month', 'parliamentary_agenda_year', 'parliamentary_agenda_created_date', 'parliamentary_agenda_updated_date', NULL];
			$search = [];
			$orderBy = ['parliamentary_agenda_year' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = isNotNull($row->parliamentary_agenda_file) ? '<div class="icon-demo-content"><a target="_blank" href="'.base_url($this->filePath.'/'.$row->parliamentary_agenda_file).'"><i class="far fa-file-alt"></i></a></div>' : '--';
					$array[] = $row->parliamentary_agenda_name;
					$array[] = monthName($row->parliamentary_agenda_month);
					$array[] = $row->parliamentary_agenda_year;
					$array[] = dateFormat($row->parliamentary_agenda_created_date, 'd-m-Y H:i:s');
					$array[] = $row->parliamentary_agenda_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->parliamentary_agenda_updated_date, 'd-m-Y H:i:s') : NULL;
					$array[] = action_links($row->parliamentary_agenda_id, ['edit', 'delete'], $this->pageUrl);
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
						'label' => lang('AdminContents.parliamentaryAgenda.general.status'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.parliamentary_agenda_name' => [
						'label' => lang('AdminContents.parliamentaryAgenda.general.name'),
						'rules' => 'required'
					],
					'form.parliamentary_agenda_month' => [
						'label' => lang('AdminContents.parliamentaryAgenda.general.month'),
						'rules' => 'required'
					],
					'form.parliamentary_agenda_year' => [
						'label' => lang('AdminContents.parliamentaryAgenda.general.year'),
						'rules' => 'required'
					]
				];

				/*****************************************************/

				// File Upload Validation
				$file = $this->request->getFile('parliamentary_agenda_file');
				if (isNotNull($file)) {
					$rulesFile = [
						'parliamentary_agenda_file' => [
							'label' => lang('AdminContents.parliamentaryAgenda.general.file.title'),
							'rules' => [
								'uploaded[parliamentary_agenda_file]',
								'mime_in[parliamentary_agenda_file,'.FILE_UPLOAD_MIME.']',
								'max_size[parliamentary_agenda_file,'.FILE_UPLOAD_SIZE.']'
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
						$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][parliamentary_agenda_name]')).'_'.$file->getRandomName();
						$file->move($this->filePath, $fileName);
					}

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['parliamentary_agenda_file'] = $fileName;
						$data['parliamentary_agenda_created_date'] = nowDate();
					}

					$result = $this->general->insertModel($this->table, $data);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {
								$lang_data = [
									'parliamentary_agenda_id' => $result,
									'lang_id' => $lang_id,
									'parliamentary_agenda_name' => trim(upper($value['parliamentary_agenda_name']))
								];

								$this->general->insertModel($this->tableLang, $lang_data);
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.add.parliamentaryAgenda', [$this->request->getVar('lang['.$this->defaultLangId.'][parliamentary_agenda_name]')]));

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

	public function edit(int $parliamentary_agenda_id) {
		$sql = $this->ParliamentaryAgendaModel->parliamentaryAgendaInfoModel($parliamentary_agenda_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->ParliamentaryAgendaModel->parliamentaryAgendaLangModel($parliamentary_agenda_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['parliamentary_agenda_name'] = $row->parliamentary_agenda_name;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'parliamentary_agenda_id' => $sql->parliamentary_agenda_id,
					'status' => $sql->status,
					'parliamentary_agenda_month' => $sql->parliamentary_agenda_month,
					'parliamentary_agenda_year' => $sql->parliamentary_agenda_year,
					'file' => isNotNull($sql->parliamentary_agenda_file) ? base_url($this->filePath.'/'.$sql->parliamentary_agenda_file) : NULL,
					'file_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-file/'.$sql->parliamentary_agenda_id)
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $parliamentary_agenda_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->ParliamentaryAgendaModel->parliamentaryAgendaInfoModel($parliamentary_agenda_id);
				if (isNotNull($sql)) {

					$rules = [
						'form.status' => [
							'label' => lang('AdminContents.parliamentaryAgenda.general.status'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.parliamentary_agenda_name' => [
							'label' => lang('AdminContents.parliamentaryAgenda.general.name'),
							'rules' => 'required'
						],
						'form.parliamentary_agenda_month' => [
							'label' => lang('AdminContents.parliamentaryAgenda.general.month'),
							'rules' => 'required'
						],
						'form.parliamentary_agenda_year' => [
							'label' => lang('AdminContents.parliamentaryAgenda.general.year'),
							'rules' => 'required'
						]
					];

					/*****************************************************/

					// File Upload Validation
					$file = $this->request->getFile('parliamentary_agenda_file');
					if (isNotNull($file)) {
						$rulesFile = [
							'parliamentary_agenda_file' => [
								'label' => lang('AdminContents.parliamentaryAgenda.general.file.title'),
								'rules' => [
									'uploaded[parliamentary_agenda_file]',
									'mime_in[parliamentary_agenda_file,'.FILE_UPLOAD_MIME.']',
									'max_size[parliamentary_agenda_file,'.FILE_UPLOAD_SIZE.']'
								]
							]
						];

						$rules = array_merge($rules, $rulesFile);
					}

					/*****************************************************/

					if ($this->validate($rules)) {

						// File Upload
						$fileName = $sql->parliamentary_agenda_file;
						if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->parliamentary_agenda_file);

							$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][parliamentary_agenda_name]')).'_'.$file->getRandomName();
							$file->move($this->filePath, $fileName);
						}

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['parliamentary_agenda_file'] = $fileName;
							$data['parliamentary_agenda_updated_date'] = nowDate();
						}

						$result = $this->general->updateModel($this->table, $data, ['parliamentary_agenda_id' => $parliamentary_agenda_id]);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'parliamentary_agenda_id' => $parliamentary_agenda_id,
										'lang_id' => $lang_id,
										'parliamentary_agenda_name' => trim(upper($value['parliamentary_agenda_name']))
									];

									$langControlModel = $this->ParliamentaryAgendaModel->parliamentaryAgendaLangControlModel($parliamentary_agenda_id, $lang_id);
									if (isNotNull($langControlModel)) {
										$this->general->updateModel($this->tableLang, $lang_data, ['parliamentary_agenda_id' => $parliamentary_agenda_id, 'lang_id' => $lang_id]);
									} else {
										$this->general->insertModel($this->tableLang, $lang_data);
									}
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.edit.parliamentaryAgenda', [$this->request->getVar('lang['.$this->defaultLangId.'][parliamentary_agenda_name]')]));

							$ajax_message['success'] = TRUE;

							if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
								$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$parliamentary_agenda_id);
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

	public function delete(int $parliamentary_agenda_id) {
		$sql = $this->ParliamentaryAgendaModel->parliamentaryAgendaInfoModel($parliamentary_agenda_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['parliamentary_agenda_id' => $parliamentary_agenda_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePath, $sql->parliamentary_agenda_file);

				// Lang
				$lang = $this->ParliamentaryAgendaModel->parliamentaryAgendaLangModel($parliamentary_agenda_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['parliamentary_agenda_id' => $row->parliamentary_agenda_id]);
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

	public function removeFile(int $parliamentary_agenda_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->table, ['parliamentary_agenda_file' => ''], ['parliamentary_agenda_id' => $parliamentary_agenda_id], $this->filePath);
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
