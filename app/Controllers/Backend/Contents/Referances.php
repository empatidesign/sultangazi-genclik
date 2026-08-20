<?php
namespace App\Controllers\Backend\Contents;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Contents\ReferancesModel;
use App\Models\Backend\DatatableModel;

class Referances extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $filePath;
	protected $ReferancesModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'referances';
		$this->tableLang = 'referances_lang';
		$this->pageUrl = ADMIN_URL_CONTENTS.'/'.ADMIN_URL_REFERANCES;
		$this->filePath = FILE_PATH_REFERANCES;
		$this->ReferancesModel = new ReferancesModel();
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
			$column = ['status', 'status_mobile', NULL, 'referance_name', 'referance_created_date', 'referance_updated_date', NULL];
			$search = [];
			$orderBy = ['referance_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_status($row->status_mobile);
					$array[] = set_image($this->filePath.'/'.$row->referance_image, 100);
					$array[] = $row->referance_name;
					$array[] = dateFormat($row->referance_created_date, 'd-m-Y H:i:s');
					$array[] = $row->referance_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->referance_updated_date, 'd-m-Y H:i:s') : NULL;
					$array[] = action_links($row->referance_id, ['edit', 'delete'], $this->pageUrl);
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
						'label' => lang('AdminContents.referances.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.referance_name' => [
						'label' => lang('AdminContents.referances.general.name'),
						'rules' => 'required'
					]
				];

				/*****************************************************/

				// Image Upload Validation
				$file = $this->request->getFile('referance_image');
				if (isNotNull($file)) {
					$rulesImage = [
						'referance_image' => [
							'label' => lang('AdminContents.referances.general.image'),
							'rules' => [
								'uploaded[referance_image]',
								'mime_in[referance_image,'.IMAGE_UPLOAD_MIME.']',
								'max_size[referance_image,'.IMAGE_UPLOAD_SIZE.']'
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
						$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][referance_name]')).'_'.$file->getRandomName();
						$fileNameResult = $this->uploadSingleFile($file, $this->filePath, $fileName);
					}

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['referance_image'] = $fileName;
						$data['referance_created_date'] = nowDate();
					}

					if ($fileNameResult == NULL) {
						$result = $this->general->insertModel($this->table, $data);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'referance_id' => $result,
										'lang_id' => $lang_id,
										'referance_name' => isNotNull($value['referance_name']) ? $value['referance_name'] : $this->request->getVar('lang['.$this->defaultLangId.'][referance_name]'),
										'referance_link' => trim($value['referance_link'])
									];

									$this->general->insertModel($this->tableLang, $lang_data);
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.add.referances', [$this->request->getVar('lang['.$this->defaultLangId.'][referance_name]')]));

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

	public function edit(int $referance_id) {
		$sql = $this->ReferancesModel->referancesInfoModel($referance_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->ReferancesModel->referancesLangModel($referance_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['referance_name'] = $row->referance_name;
					$lang_array['data']['translations'][$row->lang_id]['referance_link'] = $row->referance_link;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'referance_id' => $sql->referance_id,
					'status' => $sql->status,
					'status_mobile' => $sql->status_mobile,
					'image' => isNotNull($sql->referance_image) ? base_url($this->filePath.'/'.$sql->referance_image) : NULL,
					'image_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$sql->referance_id)
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $referance_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->ReferancesModel->referancesInfoModel($referance_id);
				if (isNotNull($sql)) {

					$rules = [
						'form.status' => [
							'label' => lang('AdminContents.referances.general.status'),
							'rules' => 'required'
						],
						'form.status_mobile' => [
							'label' => lang('Admin.mobile'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.referance_name' => [
							'label' => lang('AdminContents.referances.general.name'),
							'rules' => 'required'
						]
					];

					/*****************************************************/

					// Image Upload Validation
					$file = $this->request->getFile('referance_image');
					if (isNotNull($file)) {
						$rulesImage = [
							'referance_image' => [
								'label' => lang('AdminContents.referances.general.image'),
								'rules' => [
									'uploaded[referance_image]',
									'mime_in[referance_image,'.IMAGE_UPLOAD_MIME.']',
									'max_size[referance_image,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];

						$rules = array_merge($rules, $rulesImage);
					}

					/*****************************************************/

					if ($this->validate($rules)) {

						// Image Upload
						$fileName = $sql->referance_image;
						$fileNameResult = '';
						if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->referance_image);

							$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][referance_name]')).'_'.$file->getRandomName();
							$fileNameResult = $this->uploadSingleFile($file,
																$this->filePath,
																$fileName,
																$this->designSettings->referance_image_width,
																$this->designSettings->referance_image_height);
						}

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['referance_image'] = $fileName;
							$data['referance_updated_date'] = nowDate();
						}

						if ($fileNameResult == NULL) {
							$result = $this->general->updateModel($this->table, $data, ['referance_id' => $referance_id]);
							if ($result !== FALSE) {

								// Lang
								if (isNotNull($this->request->getVar('lang'))) {
									foreach ($this->request->getVar('lang') as $lang_id => $value) {
										$lang_data = [
											'referance_id' => $referance_id,
											'lang_id' => $lang_id,
											'referance_name' => isNotNull($value['referance_name']) ? $value['referance_name'] : $this->request->getVar('lang['.$this->defaultLangId.'][referance_name]'),
											'referance_link' => trim($value['referance_link'])
										];

										$langControlModel = $this->ReferancesModel->referancesLangControlModel($referance_id, $lang_id);
										if (isNotNull($langControlModel)) {
											$this->general->updateModel($this->tableLang, $lang_data, ['referance_id' => $referance_id, 'lang_id' => $lang_id]);
										} else {
											$this->general->insertModel($this->tableLang, $lang_data);
										}
									}
								}

								// Flash Data
								session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.edit.referances', [$this->request->getVar('lang['.$this->defaultLangId.'][referance_name]')]));

								$ajax_message['success'] = TRUE;

								if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
									$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$referance_id);
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

	public function delete(int $referance_id) {
		$sql = $this->ReferancesModel->referancesInfoModel($referance_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['referance_id' => $referance_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePath, $sql->referance_image);

				// Lang
				$lang = $this->ReferancesModel->referancesLangModel($referance_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['referance_id' => $row->referance_id]);
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

	public function removeImage(int $referance_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->table, ['referance_image' => ''], ['referance_id' => $referance_id], $this->filePath);
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
