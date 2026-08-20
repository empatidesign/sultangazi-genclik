<?php
namespace App\Controllers\Backend\Contents;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Contents\CouncilMembersModel;
use App\Models\Backend\DatatableModel;

class CouncilMembers extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $filePath;
	protected $CouncilMembersModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'council_members';
		$this->tableLang = 'council_members_lang';
		$this->pageUrl = ADMIN_URL_CONTENTS.'/'.ADMIN_URL_COUNCIL_MEMBERS;
		$this->filePath = FILE_PATH_COUNCIL_MEMBERS;
		$this->CouncilMembersModel = new CouncilMembersModel();
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
			$column = ['status', 'status_mobile', NULL, 'council_member_name', 'council_member_surname', 'council_member_sub_title', 'council_member_order', 'council_member_created_date', 'council_member_updated_date', NULL];
			$search = [];
			$orderBy = ['council_member_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_status($row->status_mobile);
					$array[] = set_image($this->filePath.'/'.$row->council_member_image, 100);
					$array[] = $row->council_member_name;
					$array[] = $row->council_member_surname;
					$array[] = $row->council_member_sub_title;
					$array[] = $row->council_member_order;
					$array[] = dateFormat($row->council_member_created_date, 'd-m-Y H:i:s');
					$array[] = $row->council_member_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->council_member_updated_date, 'd-m-Y H:i:s') : NULL;
					$array[] = action_links($row->council_member_id, ['edit', 'delete'], $this->pageUrl);
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
						'label' => lang('AdminContents.councilMembers.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					],
					'form.council_member_name' => [
						'label' => lang('AdminContents.councilMembers.general.name'),
						'rules' => 'required'
					],
					'form.council_member_surname' => [
						'label' => lang('AdminContents.councilMembers.general.surname'),
						'rules' => 'required'
					]
				];

				/*****************************************************/

				// Image Upload Validation
				$file = $this->request->getFile('council_member_image');
				if (isNotNull($file)) {
					$rulesImage = [
						'council_member_image' => [
							'label' => lang('AdminContents.councilMembers.general.image'),
							'rules' => [
								'uploaded[council_member_image]',
								'mime_in[council_member_image,'.IMAGE_UPLOAD_MIME.']',
								'max_size[council_member_image,'.IMAGE_UPLOAD_SIZE.']'
							]
						]
					];

					$rules = array_merge($rules, $rulesImage);
				}

				/*****************************************************/

				if ($this->validate($rules)) {

					// Image Upload
					$fileName = '';
					$fileNameResult = '';
					if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
						$fileName = slug($this->request->getVar('form[council_member_name]')).'_'.slug($this->request->getVar('form[council_member_surname]')).'_'.$file->getRandomName();
						$fileNameResult = $this->uploadSingleFile($file,
															$this->filePath,
															$fileName,
															$this->designSettings->council_member_image_width,
															$this->designSettings->council_member_image_height);
					}

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['council_member_image'] = $fileName;
						$data['council_member_created_date'] = nowDate();
					}

					if ($fileNameResult == NULL) {
						$result = $this->general->insertModel($this->table, $data);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'council_member_id' => $result,
										'lang_id' => $lang_id,
										'council_member_sub_title' => $value['council_member_sub_title']
									];

									$this->general->insertModel($this->tableLang, $lang_data);
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.add.councilMembers', [$this->request->getVar('form[council_member_name]'), $this->request->getVar('form[council_member_surname]')]));

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

	public function edit(int $council_member_id) {
		$sql = $this->CouncilMembersModel->councilMembersInfoModel($council_member_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->CouncilMembersModel->councilMembersLangModel($council_member_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['council_member_sub_title'] = $row->council_member_sub_title;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'council_member_id' => $sql->council_member_id,
					'status' => $sql->status,
					'status_mobile' => $sql->status_mobile,
					'council_member_name' => $sql->council_member_name,
					'council_member_surname' => $sql->council_member_surname,
					'council_member_order' => $sql->council_member_order,
					'image' => isNotNull($sql->council_member_image) ? base_url($this->filePath.'/'.$sql->council_member_image) : NULL,
					'image_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$sql->council_member_id)
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $council_member_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->CouncilMembersModel->councilMembersInfoModel($council_member_id);
				if (isNotNull($sql)) {

					$rules = [
						'form.status' => [
							'label' => lang('AdminContents.councilMembers.general.status'),
							'rules' => 'required'
						],
						'form.status_mobile' => [
							'label' => lang('Admin.mobile'),
							'rules' => 'required'
						],
						'form.council_member_name' => [
							'label' => lang('AdminContents.councilMembers.general.name'),
							'rules' => 'required'
						],
						'form.council_member_surname' => [
							'label' => lang('AdminContents.councilMembers.general.surname'),
							'rules' => 'required'
						]
					];

					/*****************************************************/

					// Image Upload Validation
					$file = $this->request->getFile('council_member_image');
					if (isNotNull($file)) {
						$rulesImage = [
							'council_member_image' => [
								'label' => lang('AdminContents.councilMembers.general.image'),
								'rules' => [
									'uploaded[council_member_image]',
									'mime_in[council_member_image,'.IMAGE_UPLOAD_MIME.']',
									'max_size[council_member_image,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];

						$rules = array_merge($rules, $rulesImage);
					}

					/*****************************************************/

					if ($this->validate($rules)) {

						// Image Upload
						$fileName = $sql->council_member_image;
						$fileNameResult = '';
						if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->council_member_image);

							$fileName = slug($this->request->getVar('form[council_member_name]')).'_'.slug($this->request->getVar('form[council_member_surname]')).'_'.$file->getRandomName();
							$fileNameResult = $this->uploadSingleFile($file,
																$this->filePath,
																$fileName,
																$this->designSettings->council_member_image_width,
																$this->designSettings->council_member_image_height);
						}

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['council_member_image'] = $fileName;
							$data['council_member_updated_date'] = nowDate();
						}

						if ($fileNameResult == NULL) {
							$result = $this->general->updateModel($this->table, $data, ['council_member_id' => $council_member_id]);
							if ($result !== FALSE) {

								// Lang
								if (isNotNull($this->request->getVar('lang'))) {
									foreach ($this->request->getVar('lang') as $lang_id => $value) {
										$lang_data = [
											'council_member_id' => $council_member_id,
											'lang_id' => $lang_id,
											'council_member_sub_title' => $value['council_member_sub_title']
										];

										$langControlModel = $this->CouncilMembersModel->councilMembersLangControlModel($council_member_id, $lang_id);
										if (isNotNull($langControlModel)) {
											$this->general->updateModel($this->tableLang, $lang_data, ['council_member_id' => $council_member_id, 'lang_id' => $lang_id]);
										} else {
											$this->general->insertModel($this->tableLang, $lang_data);
										}
									}
								}

								// Flash Data
								session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.edit.councilMembers', [$this->request->getVar('form[council_member_name]'), $this->request->getVar('form[council_member_surname]')]));

								$ajax_message['success'] = TRUE;

								if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
									$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$council_member_id);
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

	public function delete(int $council_member_id) {
		$sql = $this->CouncilMembersModel->councilMembersInfoModel($council_member_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['council_member_id' => $council_member_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePath, $sql->council_member_image);

				// Lang
				$lang = $this->CouncilMembersModel->councilMembersLangModel($council_member_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['council_member_id' => $row->council_member_id]);
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

	public function removeImage(int $council_member_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->table, ['council_member_image' => ''], ['council_member_id' => $council_member_id], $this->filePath);
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
