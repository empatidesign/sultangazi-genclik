<?php
namespace App\Controllers\Backend\Contents;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Contents\DirectoratesModel;
use App\Models\Backend\DatatableModel;

class Directorates extends BaseController {

	protected $table;
	protected $tableLang;
	protected $tableFiles;
	protected $tableFilesLang;
	protected $pageUrl;
	protected $filePath;
	protected $DirectoratesModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'directorates';
		$this->tableLang = 'directorates_lang';
		$this->tableFiles = 'directorates_file';
		$this->tableFilesLang = 'directorates_file_lang';
		$this->pageUrl = ADMIN_URL_CONTENTS.'/'.ADMIN_URL_DIRECTORATES;
		$this->filePath = FILE_PATH_DIRECTORATES;
		$this->DirectoratesModel = new DirectoratesModel();
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
			$column = ['status', 'status_mobile', NULL, 'directorates_name', 'directorates_person_name', 'directorates_person_surname', 'directorates_created_date', 'directorates_updated_date', NULL];
			$search = [];
			$orderBy = ['directorates_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_status($row->status_mobile);
					$array[] = isNotNull($row->directorates_icon) ? set_image($this->filePath.'/'.$row->directorates_icon, 100) : '--';
					$array[] = $row->directorates_name;
					$array[] = $row->directorates_person_name;
					$array[] = $row->directorates_person_surname;
					$array[] = dateFormat($row->directorates_created_date, 'd-m-Y H:i:s');
					$array[] = $row->directorates_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->directorates_updated_date, 'd-m-Y H:i:s') : NULL;
					$array[] = action_links($row->directorates_id, ['edit', 'delete'], $this->pageUrl);
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
		$last_id = $this->general->lastIDModel($this->table, 'directorates_id');

		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_name' => 'add',
			'page_url' => $this->pageUrl,
			'last_id' => $last_id,
			'files' => [
				'datatable_url' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/files/datatable/'.$last_id)
			]
		]);
	}

	public function insert() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules1 = [
					'form.status' => [
						'label' => lang('AdminContents.directorates.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.directorates_name' => [
						'label' => lang('AdminContents.directorates.general.name'),
						'rules' => 'required'
					],
					'form.directorates_person_name' => [
						'label' => lang('AdminContents.directorates.general.person.name'),
						'rules' => 'required'
					],
					'form.directorates_person_surname' => [
						'label' => lang('AdminContents.directorates.general.person.surname'),
						'rules' => 'required'
					]
				];

				$rules2 = [];
				if (isNotNull($this->request->getVar('form[directorates_email_address]'))) {
					$rules2 = [
						'form.directorates_email_address' => [
							'label' => lang('AdminContents.directorates.general.emailAddress'),
							'rules' => 'min_length['.FORM_EMAIL_MIN_LENGTH.']|max_length['.FORM_EMAIL_MAX_LENGTH.']|valid_email'
						]
					];
				}

				/*****************************************************/

				// Icon Upload Validation
				$rules3 = [];
				$fileIcon = $this->request->getFile('directorates_icon');
				if (isNotNull($fileIcon)) {
					$rules3 = [
						'directorates_icon' => [
							'label' => lang('AdminContents.directorates.general.icon.title'),
							'rules' => [
								'uploaded[directorates_icon]',
								'mime_in[directorates_icon,'.IMAGE_UPLOAD_MIME.']',
								'max_size[directorates_icon,'.IMAGE_UPLOAD_SIZE.']'
							]
						]
					];
				}

				// Person Image Upload Validation
				$rules4 = [];
				$filePersonImage = $this->request->getFile('directorates_person_image');
				if (isNotNull($filePersonImage)) {
					$rules4 = [
						'directorates_person_image' => [
							'label' => lang('AdminContents.directorates.general.person.image'),
							'rules' => [
								'uploaded[directorates_person_image]',
								'mime_in[directorates_person_image,'.IMAGE_UPLOAD_MIME.']',
								'max_size[directorates_person_image,'.IMAGE_UPLOAD_SIZE.']'
							]
						]
					];
				}

				/*****************************************************/

				$rules = array_merge_recursive($rules1, $rules2, $rules3, $rules4);

				if ($this->validate($rules)) {

					// Icon Upload
					$fileIconName = '';
					if (isNotNull($fileIcon) && $fileIcon->isValid() && !$fileIcon->hasMoved()) {
						$fileIconName = slug($this->request->getVar('lang['.$this->defaultLangId.'][directorates_name]')).'_'.$fileIcon->getRandomName();
						$fileIcon->move($this->filePath, $fileIconName);
					}

					// Person Image Upload
					$filePersonImageName = '';
					$filePersonImageResult = '';
					if (isNotNull($filePersonImage) && $filePersonImage->isValid() && !$filePersonImage->hasMoved()) {
						$filePersonImageName = slug($this->request->getVar('lang['.$this->defaultLangId.'][directorates_name]')).'_'.$filePersonImage->getRandomName();
						$filePersonImageResult = $this->uploadSingleFile($filePersonImage,
																		 $this->filePath,
																		 $filePersonImageName,
																		 $this->designSettings->directorates_person_image_width,
																		 $this->designSettings->directorates_person_image_height);
					}

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['directorates_telephone'] = trim($this->request->getVar('form[directorates_telephone]'));
						$data['directorates_fax'] = trim($this->request->getVar('form[directorates_fax]'));
						$data['directorates_email_address'] = trim($this->request->getVar('form[directorates_email_address]'));
						$data['directorates_person_name'] = trim($this->request->getVar('form[directorates_person_name]'));
						$data['directorates_person_surname'] = trim($this->request->getVar('form[directorates_person_surname]'));
						$data['directorates_person_image'] = $filePersonImageName;
						$data['directorates_icon'] = $fileIconName;
						$data['directorates_created_date'] = nowDate();
					}

					if ($filePersonImageResult == NULL) {
						$result = $this->general->insertModel($this->table, $data);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'directorates_id' => $result,
										'lang_id' => $lang_id,
										'directorates_name' => trim($value['directorates_name']),
										'directorates_person_sub_title' => trim($value['directorates_person_sub_title']),
										'directorates_slug' => slug($value['directorates_name'])
									];

									$this->general->insertModel($this->tableLang, $lang_data);
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.add.directorates', [$this->request->getVar('lang['.$this->defaultLangId.'][directorates_name]')]));

							$ajax_message['success'] = TRUE;
							$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);

						} else {
							$ajax_message['error'] = lang('Admin.error.insert');
						}
					} else {
						$ajax_message['error'] = $filePersonImageResult;
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

	public function edit(int $directorates_id) {
		$sql = $this->DirectoratesModel->directoratesInfoModel($directorates_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->DirectoratesModel->directoratesLangModel($directorates_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['directorates_name'] = $row->directorates_name;
					$lang_array['data']['translations'][$row->lang_id]['directorates_person_sub_title'] = $row->directorates_person_sub_title;
					$lang_array['data']['translations'][$row->lang_id]['directorates_slug'] = $row->directorates_slug;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'files' => [
					'datatable_url' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/files/datatable/'.$sql->directorates_id)
				],
				'result' => [
					'directorates_id' => $sql->directorates_id,
					'status' => $sql->status,
					'status_mobile' => $sql->status_mobile,
					'directorates_telephone' => $sql->directorates_telephone,
					'directorates_fax' => $sql->directorates_fax,
					'directorates_email_address' => $sql->directorates_email_address,
					'directorates_person_name' => $sql->directorates_person_name,
					'directorates_person_surname' => $sql->directorates_person_surname,
					'icon' => isNotNull($sql->directorates_icon) ? base_url($this->filePath.'/'.$sql->directorates_icon) : NULL,
					'person_image' => isNotNull($sql->directorates_person_image) ? base_url($this->filePath.'/'.$sql->directorates_person_image) : NULL,
					'image_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$sql->directorates_id)
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $directorates_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->DirectoratesModel->directoratesInfoModel($directorates_id);
				if (isNotNull($sql)) {

					$rules1 = [
						'form.status' => [
							'label' => lang('AdminContents.directorates.general.status'),
							'rules' => 'required'
						],
						'form.status_mobile' => [
							'label' => lang('Admin.mobile'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.directorates_name' => [
							'label' => lang('AdminContents.directorates.general.name'),
							'rules' => 'required'
						],
						'form.directorates_person_name' => [
							'label' => lang('AdminContents.directorates.general.person.name'),
							'rules' => 'required'
						],
						'form.directorates_person_surname' => [
							'label' => lang('AdminContents.directorates.general.person.surname'),
							'rules' => 'required'
						]
					];

					$rules2 = [];
					if (isNotNull($this->request->getVar('form[directorates_email_address]'))) {
						$rules2 = [
							'form.directorates_email_address' => [
								'label' => lang('AdminContents.directorates.general.emailAddress'),
								'rules' => 'min_length['.FORM_EMAIL_MIN_LENGTH.']|max_length['.FORM_EMAIL_MAX_LENGTH.']|valid_email'
							]
						];
					}

					/*****************************************************/

					// Icon Upload Validation
					$rules3 = [];
					$fileIcon = $this->request->getFile('directorates_icon');
					if (isNotNull($fileIcon)) {
						$rules3 = [
							'directorates_icon' => [
								'label' => lang('AdminContents.directorates.general.icon.title'),
								'rules' => [
									'uploaded[directorates_icon]',
									'mime_in[directorates_icon,'.IMAGE_UPLOAD_MIME.']',
									'max_size[directorates_icon,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];
					}

					// Person Image Upload Validation
					$rules4 = [];
					$filePersonImage = $this->request->getFile('directorates_person_image');
					if (isNotNull($filePersonImage)) {
						$rules4 = [
							'directorates_person_image' => [
								'label' => lang('AdminContents.directorates.general.person.image'),
								'rules' => [
									'uploaded[directorates_person_image]',
									'mime_in[directorates_person_image,'.IMAGE_UPLOAD_MIME.']',
									'max_size[directorates_person_image,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];
					}

					/*****************************************************/

					$rules = array_merge_recursive($rules1, $rules2, $rules3, $rules4);

					if ($this->validate($rules)) {

						// Icon Upload
						$fileIconName = $sql->directorates_icon;
						if (isNotNull($fileIcon) && $fileIcon->isValid() && !$fileIcon->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->directorates_icon);

							$fileIconName = slug($this->request->getVar('lang['.$this->defaultLangId.'][directorates_name]')).'_'.$fileIcon->getRandomName();
							$fileIcon->move($this->filePath, $fileIconName);
						}

						// Person Image Upload
						$filePersonImageName = $sql->directorates_person_image;
						$filePersonImageResult = '';
						if (isNotNull($filePersonImage) && $filePersonImage->isValid() && !$filePersonImage->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->directorates_person_image);

							$filePersonImageName = slug($this->request->getVar('lang['.$this->defaultLangId.'][directorates_name]')).'_'.$filePersonImage->getRandomName();
							$filePersonImageResult = $this->uploadSingleFile($filePersonImage,
																			 $this->filePath,
																			 $filePersonImageName,
																			 $this->designSettings->directorates_person_image_width,
																			 $this->designSettings->directorates_person_image_height);
						}

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['directorates_telephone'] = trim($this->request->getVar('form[directorates_telephone]'));
							$data['directorates_fax'] = trim($this->request->getVar('form[directorates_fax]'));
							$data['directorates_email_address'] = trim($this->request->getVar('form[directorates_email_address]'));
							$data['directorates_person_name'] = trim($this->request->getVar('form[directorates_person_name]'));
							$data['directorates_person_surname'] = trim($this->request->getVar('form[directorates_person_surname]'));
							$data['directorates_icon'] = $fileIconName;
							$data['directorates_person_image'] = $filePersonImageName;
							$data['directorates_updated_date'] = nowDate();
						}

						if ($filePersonImageResult == NULL) {
							$result = $this->general->updateModel($this->table, $data, ['directorates_id' => $directorates_id]);
							if ($result !== FALSE) {

								// Lang
								if (isNotNull($this->request->getVar('lang'))) {
									foreach ($this->request->getVar('lang') as $lang_id => $value) {
										$lang_data = [
											'directorates_id' => $directorates_id,
											'lang_id' => $lang_id,
											'directorates_name' => trim($value['directorates_name']),
											'directorates_person_sub_title' => trim($value['directorates_person_sub_title']),
											'directorates_slug' => slug($value['directorates_name'])
										];

										$langControlModel = $this->DirectoratesModel->directoratesLangControlModel($directorates_id, $lang_id);
										if (isNotNull($langControlModel)) {
											$this->general->updateModel($this->tableLang, $lang_data, ['directorates_id' => $directorates_id, 'lang_id' => $lang_id]);
										} else {
											$this->general->insertModel($this->tableLang, $lang_data);
										}
									}
								}

								// Flash Data
								session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.edit.directorates', [$this->request->getVar('lang['.$this->defaultLangId.'][directorates_name]')]));

								$ajax_message['success'] = TRUE;

								if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
									$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$directorates_id);
								} else {
									$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);
								}

							} else {
								$ajax_message['error'] = lang('Admin.error.update');
							}
						} else {
							$ajax_message['error'] = $filePersonImageResult;
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

	public function delete(int $directorates_id) {
		$sql = $this->DirectoratesModel->directoratesInfoModel($directorates_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['directorates_id' => $directorates_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePath, $sql->directorates_icon);
				unlinkFile($this->filePath, $sql->directorates_person_image);

				// Lang
				$lang = $this->DirectoratesModel->directoratesLangModel($directorates_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['directorates_id' => $row->directorates_id]);
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

	public function removeImage(int $directorates_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$data = [];
				if ($this->request->getVar('action') == 'icon') {
					$data = ['directorates_icon' => ''];
				} elseif ($this->request->getVar('action') == 'person_image') {
					$data = ['directorates_person_image' => ''];
				}

				$result = $this->general->removeDropifyImageModel($this->table, $data, ['directorates_id' => $directorates_id], $this->filePath);
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

	/*****************************************************/

	public function filesDatatable(int $directorates_id) {
		if ($this->request->isAJAX()) {
			$column = ['directorates_file_id', NULL, 'directorate_category_name', 'directorates_file_name', 'directorates_file_created_date', NULL];
			$search = [];
			$orderBy = ['directorates_file_id' => 'ASC'];
			$where = ['directorates_id' => $directorates_id];

			$list = $this->DatatableModel->GetDatatables($this->tableFiles, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = $row->directorates_file_id;
					$array[] = isNotNull($row->directorates_file) ? '<div class="icon-demo-content"><a target="_blank" href="'.base_url($this->filePath.'/'.$row->directorates_file).'"><i class="far fa-file-alt"></i></a></div>' : '--';
					$array[] = isNotNull($row->directorate_category_name) ? character_limiter($row->directorate_category_name, 50) : '--';
					$array[] = character_limiter($row->directorates_file_name, 50);
					$array[] = dateFormat($row->directorates_file_created_date, 'd/m/Y H:i:s');
					$array[] = action_links($row->directorates_file_id, ['modal-edit', 'delete'], $this->pageUrl.'/files', NULL, NULL, ['modal', 'directories_file_edit']);
					$data[] = $array;
				}
			}

			$output = [
				'draw' => $this->request->getVar('draw'),
				'recordsTotal' => $this->DatatableModel->GetDatatables($this->tableFiles, $column, $search, $orderBy, $where, 'getNumRows'),
				'recordsFiltered' => $this->DatatableModel->GetDatatables($this->tableFiles, $column, $search, $orderBy, $where, 'countAllResults'),
				'data' => $data
			];

			return $this->response->setJSON($output);
		}
	}

	public function filesInsert(int $directorates_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'form.directorate_category_id' => [
						'label' => lang('AdminContents.directorates.files.category'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.directorates_file_name' => [
						'label' => lang('AdminContents.directorates.files.name'),
						'rules' => 'required'
					]
				];

				/*****************************************************/

				// File Upload Validation
				$file = $this->request->getFile('directorates_file');
				if (isNotNull($file)) {
					$rulesFile = [
						'directorates_file' => [
							'label' => lang('AdminContents.directorates.files.file'),
							'rules' => [
								'uploaded[directorates_file]',
								'mime_in[directorates_file,'.FILE_UPLOAD_MIME.']',
								'max_size[directorates_file,'.FILE_UPLOAD_SIZE.']'
							]
						]
					];

					$rules = array_merge($rules, $rulesFile);
				}

				if ($this->validate($rules)) {

					// File Upload
					$fileName = '';
					if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {

						if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][directorates_file_name]'))) {
							$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][directorates_file_name]')).'_'.$file->getRandomName();
						} else {
							$fileName = $file->getRandomName();
						}

						$file->move($this->filePath, $fileName);
					}

					$data = [
						'directorates_id' => $directorates_id,
						'directorate_category_id' => trim($this->request->getVar('form[directorate_category_id]')),
						'directorates_file' => $fileName,
						'directorates_file_created_date' => nowDate()
					];

					$result = $this->general->insertModel($this->tableFiles, $data);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {
								$lang_data = [
									'directorates_file_id' => $result,
									'lang_id' => $lang_id,
									'directorates_file_name' => trim($value['directorates_file_name'])
								];

								$this->general->insertModel($this->tableFilesLang, $lang_data);
							}
						}

						$ajax_message['success'] = TRUE;
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

	public function filesUpdate(int $directorates_file_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->DirectoratesModel->directoratesFileInfoModel($directorates_file_id);
				if (isNotNull($sql)) {

					$rules = [
						'form.directorate_category_id' => [
							'label' => lang('AdminContents.directorates.files.category'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.directorates_file_name' => [
							'label' => lang('AdminContents.directorates.files.name'),
							'rules' => 'required'
						]
					];

					/*****************************************************/

					// File Upload Validation
					$file = $this->request->getFile('directorates_file');
					if (isNotNull($file) && isNull($sql->directorates_file)) {
						$rulesFile = [
							'directorates_file' => [
								'label' => lang('AdminContents.directorates.files.file'),
								'rules' => [
									'uploaded[directorates_file]',
									'mime_in[directorates_file,'.FILE_UPLOAD_MIME.']',
									'max_size[directorates_file,'.FILE_UPLOAD_SIZE.']'
								]
							]
						];

						$rules = array_merge($rules, $rulesFile);
					}

					if ($this->validate($rules)) {

						// File Upload
						$fileName = $sql->directorates_file;
						$fileNameResult = '';
						if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->directorates_file);

							if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][directorates_file_name]'))) {
								$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][directorates_file_name]')).'_'.$file->getRandomName();
							} else {
								$fileName = $file->getRandomName();
							}

							$file->move($this->filePath, $fileName);
						}

						$data = [
							'directorate_category_id' => trim($this->request->getVar('form[directorate_category_id]')),
							'directorates_file' => $fileName,
							'directorates_file_updated_date' => nowDate()
						];

						if ($fileNameResult == NULL) {
							$result = $this->general->updateModel($this->tableFiles, $data, ['directorates_file_id' => $directorates_file_id]);
							if ($result !== FALSE) {

								// Lang
								if (isNotNull($this->request->getVar('lang'))) {
									foreach ($this->request->getVar('lang') as $lang_id => $value) {
										$lang_data = [
											'directorates_file_id' => $directorates_file_id,
											'lang_id' => $lang_id,
											'directorates_file_name' => trim($value['directorates_file_name'])
										];

										$langControlModel = $this->DirectoratesModel->directoratesFileLangControlModel($directorates_file_id, $lang_id);
										if (isNotNull($langControlModel)) {
											$this->general->updateModel($this->tableFilesLang, $lang_data, ['directorates_file_id' => $directorates_file_id, 'lang_id' => $lang_id]);
										} else {
											$this->general->insertModel($this->tableFilesLang, $lang_data);
										}
									}
								}

								$ajax_message['success'] = TRUE;

							} else {
								$ajax_message['error'] = lang('Admin.error.update');
							}
						} else {
							$ajax_message['error'] = $fileNameResult;
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

	public function filesDelete(int $directorates_file_id) {
		$sql = $this->DirectoratesModel->directoratesFileInfoModel($directorates_file_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->tableFiles, ['directorates_file_id' => $directorates_file_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePath, $sql->directorates_file);

				// Lang
				$lang = $this->DirectoratesModel->directoratesFileLangModel($directorates_file_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableFilesLang, ['directorates_file_id' => $row->directorates_file_id]);
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

	public function filesRemoveFile(int $directorates_file_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->tableFiles, ['directorates_file' => ''], ['directorates_file_id' => $directorates_file_id], $this->filePath);
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
