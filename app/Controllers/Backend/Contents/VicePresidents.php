<?php
namespace App\Controllers\Backend\Contents;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Contents\VicePresidentsModel;
use App\Models\Backend\DatatableModel;

class VicePresidents extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $filePath;
	protected $VicePresidentsModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'vice_presidents';
		$this->tableLang = 'vice_presidents_lang';
		$this->pageUrl = ADMIN_URL_CONTENTS.'/'.ADMIN_URL_VICE_PRESIDENTS;
		$this->filePath = FILE_PATH_VICE_PRESIDENTS;
		$this->VicePresidentsModel = new VicePresidentsModel();
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
			$column = ['status', 'status_mobile', NULL, 'vice_president_name', 'vice_president_surname', 'vice_president_sub_title', 'vice_president_order', 'vice_president_created_date', 'vice_president_updated_date', NULL];
			$search = [];
			$orderBy = ['vice_president_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_status($row->status_mobile);
					$array[] = set_image($this->filePath.'/'.$row->vice_president_image, 100);
					$array[] = $row->vice_president_name;
					$array[] = $row->vice_president_surname;
					$array[] = $row->vice_president_sub_title;
					$array[] = $row->vice_president_order;
					$array[] = dateFormat($row->vice_president_created_date, 'd-m-Y H:i:s');
					$array[] = $row->vice_president_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->vice_president_updated_date, 'd-m-Y H:i:s') : NULL;
					$array[] = action_links($row->vice_president_id, ['edit', 'delete'], $this->pageUrl);
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
				'directorates' => $this->VicePresidentsModel->directoratesListModel($this->defaultLangId)
			]
		]);
	}

	public function insert() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules1 = [
					'form.status' => [
						'label' => lang('AdminContents.vicePresidents.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					],
					'form.vice_president_name' => [
						'label' => lang('AdminContents.vicePresidents.general.name'),
						'rules' => 'required'
					],
					'form.vice_president_surname' => [
						'label' => lang('AdminContents.vicePresidents.general.surname'),
						'rules' => 'required'
					]
				];

				$rules2 = [];
				if (isNotNull($this->request->getVar('form[vice_president_email_address]'))) {
					$rules2 = [
						'form.vice_president_email_address' => [
							'label' => lang('AdminContents.vicePresidents.general.emailAddress'),
							'rules' => 'min_length['.FORM_EMAIL_MIN_LENGTH.']|max_length['.FORM_EMAIL_MAX_LENGTH.']|valid_email'
						]
					];
				}

				/*****************************************************/

				// Image Upload Validation
				$rules3 = [];
				$file = $this->request->getFile('vice_president_image');
				if (isNotNull($file)) {
					$rules3 = [
						'vice_president_image' => [
							'label' => lang('AdminContents.vicePresidents.general.image'),
							'rules' => [
								'uploaded[vice_president_image]',
								'mime_in[vice_president_image,'.IMAGE_UPLOAD_MIME.']',
								'max_size[vice_president_image,'.IMAGE_UPLOAD_SIZE.']'
							]
						]
					];
				}

				/*****************************************************/

				$rules = array_merge_recursive($rules1, $rules2, $rules3);

				if ($this->validate($rules)) {

					// Image Upload
					$fileName = '';
					$fileNameResult = '';
					if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
						$fileName = slug($this->request->getVar('form[vice_president_name]')).'_'.slug($this->request->getVar('form[vice_president_surname]')).'_'.$file->getRandomName();
						$fileNameResult = $this->uploadSingleFile($file,
															$this->filePath,
															$fileName,
															$this->designSettings->vice_president_image_width,
															$this->designSettings->vice_president_image_height);
					}

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['vice_president_image'] = $fileName;
						$data['vice_president_telephone'] = trim($this->request->getVar('form[vice_president_telephone]'));
						$data['vice_president_email_address'] = trim($this->request->getVar('form[vice_president_email_address]'));

						// Directorates
						$data['directorates_id'] = '';
						if (isNotNull($this->request->getVar('form[directorates_id]'))) {
							$directorates = NULL;
							foreach ($this->request->getVar('form[directorates_id]') as $row) {
								$directorates .= $row.',';
							}

							$data['directorates_id'] = reduce_multiples($directorates, ',', TRUE);
						}

						$data['vice_president_created_date'] = nowDate();
					}

					if ($fileNameResult == NULL) {
						$result = $this->general->insertModel($this->table, $data);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'vice_president_id' => $result,
										'lang_id' => $lang_id,
										'vice_president_sub_title' => $value['vice_president_sub_title'],
										'vice_president_description' => $value['vice_president_description'],
										'vice_president_slug' => slug($this->request->getVar('form[vice_president_name]')).'-'.slug($this->request->getVar('form[vice_president_surname]'))
									];

									$this->general->insertModel($this->tableLang, $lang_data);
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.add.vicePresidents', [$this->request->getVar('form[vice_president_name]'), $this->request->getVar('form[vice_president_surname]')]));

							$ajax_message['success'] = TRUE;
							$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);

						} else {
							$ajax_message['error'] = lang('Admin.error.insert');
						}
					} else {
						$ajax_message['error'] = $fileNameResult;
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

	public function edit(int $vice_president_id) {
		$sql = $this->VicePresidentsModel->vicePresidentInfoModel($vice_president_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->VicePresidentsModel->vicePresidentLangModel($vice_president_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['vice_president_sub_title'] = $row->vice_president_sub_title;
					$lang_array['data']['translations'][$row->lang_id]['vice_president_description'] = $row->vice_president_description;
				}
			}

			// Directorates (Selected)
			$directorates_id = [];
			if (isNotNull($sql->directorates_id)) {
				$explode = explode(',', $sql->directorates_id);
				foreach ($explode as $row) {
					$directorates_id[] = $row;
				}
			}

			// Directorates
			$directorates = [];
			$directorates_sql = $this->VicePresidentsModel->directoratesListModel($this->defaultLangId);
			if (isNotNull($directorates_sql)) {
				foreach ($directorates_sql as $row) {

					// Selected
					$selected = NULL;
					if (in_array($row->directorates_id, $directorates_id)) {
						$selected = 'selected';
					}

					$directorates[] = [
						'directorates_id' => $row->directorates_id,
						'directorates_name' => $row->directorates_name,
						'selected' => $selected
					];
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'vice_president_id' => $sql->vice_president_id,
					'status' => $sql->status,
					'status_mobile' => $sql->status_mobile,
					'vice_president_name' => $sql->vice_president_name,
					'vice_president_surname' => $sql->vice_president_surname,
					'vice_president_order' => $sql->vice_president_order,
					'vice_president_telephone' => $sql->vice_president_telephone,
					'vice_president_email_address' => $sql->vice_president_email_address,
					'image' => isNotNull($sql->vice_president_image) ? base_url($this->filePath.'/'.$sql->vice_president_image) : NULL,
					'image_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$sql->vice_president_id)
				],
				'list' => [
					'directorates' => $directorates
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $vice_president_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->VicePresidentsModel->vicePresidentInfoModel($vice_president_id);
				if (isNotNull($sql)) {

					$rules1 = [
						'form.status' => [
							'label' => lang('AdminContents.vicePresidents.general.status'),
							'rules' => 'required'
						],
						'form.status_mobile' => [
							'label' => lang('Admin.mobile'),
							'rules' => 'required'
						],
						'form.vice_president_name' => [
							'label' => lang('AdminContents.vicePresidents.general.name'),
							'rules' => 'required'
						],
						'form.vice_president_surname' => [
							'label' => lang('AdminContents.vicePresidents.general.surname'),
							'rules' => 'required'
						]
					];

					$rules2 = [];
					if (isNotNull($this->request->getVar('form[vice_president_email_address]'))) {
						$rules2 = [
							'form.vice_president_email_address' => [
								'label' => lang('AdminContents.vicePresidents.general.emailAddress'),
								'rules' => 'min_length['.FORM_EMAIL_MIN_LENGTH.']|max_length['.FORM_EMAIL_MAX_LENGTH.']|valid_email'
							]
						];
					}

					/*****************************************************/

					// Image Upload Validation
					$rules3 = [];
					$file = $this->request->getFile('vice_president_image');
					if (isNotNull($file)) {
						$rules3 = [
							'vice_president_image' => [
								'label' => lang('AdminContents.vicePresidents.general.image'),
								'rules' => [
									'uploaded[vice_president_image]',
									'mime_in[vice_president_image,'.IMAGE_UPLOAD_MIME.']',
									'max_size[vice_president_image,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];
					}

					/*****************************************************/

					$rules = array_merge_recursive($rules1, $rules2, $rules3);

					if ($this->validate($rules)) {

						// Image Upload
						$fileName = $sql->vice_president_image;
						$fileNameResult = '';
						if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->vice_president_image);

							$fileName = slug($this->request->getVar('form[vice_president_name]')).'_'.slug($this->request->getVar('form[vice_president_surname]')).'_'.$file->getRandomName();
							$fileNameResult = $this->uploadSingleFile($file,
																$this->filePath,
																$fileName,
																$this->designSettings->vice_president_image_width,
																$this->designSettings->vice_president_image_height);
						}

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['vice_president_image'] = $fileName;
							$data['vice_president_telephone'] = trim($this->request->getVar('form[vice_president_telephone]'));
							$data['vice_president_email_address'] = trim($this->request->getVar('form[vice_president_email_address]'));

							// Directorates
							$data['directorates_id'] = '';
							if (isNotNull($this->request->getVar('form[directorates_id]'))) {
								$directorates = NULL;
								foreach ($this->request->getVar('form[directorates_id]') as $row) {
									$directorates .= $row.',';
								}

								$data['directorates_id'] = reduce_multiples($directorates, ',', TRUE);
							}

							$data['vice_president_updated_date'] = nowDate();
						}

						if ($fileNameResult == NULL) {
							$result = $this->general->updateModel($this->table, $data, ['vice_president_id' => $vice_president_id]);
							if ($result !== FALSE) {

								// Lang
								if (isNotNull($this->request->getVar('lang'))) {
									foreach ($this->request->getVar('lang') as $lang_id => $value) {
										$lang_data = [
											'vice_president_id' => $vice_president_id,
											'lang_id' => $lang_id,
											'vice_president_sub_title' => $value['vice_president_sub_title'],
											'vice_president_description' => $value['vice_president_description'],
											'vice_president_slug' => slug($this->request->getVar('form[vice_president_name]')).'-'.slug($this->request->getVar('form[vice_president_surname]'))
										];

										$langControlModel = $this->VicePresidentsModel->vicePresidentLangControlModel($vice_president_id, $lang_id);
										if (isNotNull($langControlModel)) {
											$this->general->updateModel($this->tableLang, $lang_data, ['vice_president_id' => $vice_president_id, 'lang_id' => $lang_id]);
										} else {
											$this->general->insertModel($this->tableLang, $lang_data);
										}
									}
								}

								// Flash Data
								session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.edit.vicePresidents', [$this->request->getVar('form[vice_president_name]'), $this->request->getVar('form[vice_president_surname]')]));

								$ajax_message['success'] = TRUE;

								if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
									$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$vice_president_id);
								} else {
									$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);
								}

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

	public function delete(int $vice_president_id) {
		$sql = $this->VicePresidentsModel->vicePresidentInfoModel($vice_president_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['vice_president_id' => $vice_president_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePath, $sql->vice_president_image);

				// Lang
				$lang = $this->VicePresidentsModel->vicePresidentLangModel($vice_president_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['vice_president_id' => $row->vice_president_id]);
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

	public function removeImage(int $vice_president_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->table, ['vice_president_image' => ''], ['vice_president_id' => $vice_president_id], $this->filePath);
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
