<?php
namespace App\Controllers\Backend\Contents;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Contents\MunicipalCouncilsModel;
use App\Models\Backend\DatatableModel;

class MunicipalCouncils extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $filePath;
	protected $MunicipalCouncilsModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'municipal_councils';
		$this->tableLang = 'municipal_councils_lang';
		$this->pageUrl = ADMIN_URL_CONTENTS.'/'.ADMIN_URL_MUNICIPAL_COUNCILS;
		$this->filePath = FILE_PATH_MUNICIPAL_COUNCILS;
		$this->MunicipalCouncilsModel = new MunicipalCouncilsModel();
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
			$column = ['status', 'status_mobile', NULL, 'municipal_council_name', 'municipal_council_surname', 'municipal_council_sub_title', 'municipal_council_order', 'municipal_council_created_date', 'municipal_council_updated_date', NULL];
			$search = [];
			$orderBy = ['municipal_council_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_status($row->status_mobile);
					$array[] = set_image($this->filePath.'/'.$row->municipal_council_image, 100);
					$array[] = $row->municipal_council_name;
					$array[] = $row->municipal_council_surname;
					$array[] = $row->municipal_council_sub_title;
					$array[] = $row->municipal_council_order;
					$array[] = dateFormat($row->municipal_council_created_date, 'd-m-Y H:i:s');
					$array[] = $row->municipal_council_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->municipal_council_updated_date, 'd-m-Y H:i:s') : NULL;
					$array[] = action_links($row->municipal_council_id, ['edit', 'delete'], $this->pageUrl);
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
						'label' => lang('AdminContents.municipalCouncils.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					],
					'form.municipal_council_name' => [
						'label' => lang('AdminContents.municipalCouncils.general.name'),
						'rules' => 'required'
					],
					'form.municipal_council_surname' => [
						'label' => lang('AdminContents.municipalCouncils.general.surname'),
						'rules' => 'required'
					]
				];

				/*****************************************************/

				// Image Upload Validation
				$file = $this->request->getFile('municipal_council_image');
				if (isNotNull($file)) {
					$rulesImage = [
						'municipal_council_image' => [
							'label' => lang('AdminContents.municipalCouncils.general.image'),
							'rules' => [
								'uploaded[municipal_council_image]',
								'mime_in[municipal_council_image,'.IMAGE_UPLOAD_MIME.']',
								'max_size[municipal_council_image,'.IMAGE_UPLOAD_SIZE.']'
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
						$fileName = slug($this->request->getVar('form[municipal_council_name]')).'_'.slug($this->request->getVar('form[municipal_council_surname]')).'_'.$file->getRandomName();
						$fileNameResult = $this->uploadSingleFile($file,
															$this->filePath,
															$fileName,
															$this->designSettings->municipal_council_image_width,
															$this->designSettings->municipal_council_image_height);
					}

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['municipal_council_image'] = $fileName;
						$data['municipal_council_created_date'] = nowDate();
					}

					if ($fileNameResult == NULL) {
						$result = $this->general->insertModel($this->table, $data);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'municipal_council_id' => $result,
										'lang_id' => $lang_id,
										'municipal_council_sub_title' => $value['municipal_council_sub_title']
									];

									$this->general->insertModel($this->tableLang, $lang_data);
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.add.municipalCouncils', [$this->request->getVar('form[municipal_council_name]'), $this->request->getVar('form[municipal_council_surname]')]));

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

	public function edit(int $municipal_council_id) {
		$sql = $this->MunicipalCouncilsModel->municipalCouncilInfoModel($municipal_council_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->MunicipalCouncilsModel->municipalCouncilLangModel($municipal_council_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['municipal_council_sub_title'] = $row->municipal_council_sub_title;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'municipal_council_id' => $sql->municipal_council_id,
					'status' => $sql->status,
					'status_mobile' => $sql->status_mobile,
					'municipal_council_name' => $sql->municipal_council_name,
					'municipal_council_surname' => $sql->municipal_council_surname,
					'municipal_council_order' => $sql->municipal_council_order,
					'image' => isNotNull($sql->municipal_council_image) ? base_url($this->filePath.'/'.$sql->municipal_council_image) : NULL,
					'image_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$sql->municipal_council_id)
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $municipal_council_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->MunicipalCouncilsModel->municipalCouncilInfoModel($municipal_council_id);
				if (isNotNull($sql)) {

					$rules = [
						'form.status' => [
							'label' => lang('AdminContents.municipalCouncils.general.status'),
							'rules' => 'required'
						],
						'form.status_mobile' => [
							'label' => lang('Admin.mobile'),
							'rules' => 'required'
						],
						'form.municipal_council_name' => [
							'label' => lang('AdminContents.municipalCouncils.general.name'),
							'rules' => 'required'
						],
						'form.municipal_council_surname' => [
							'label' => lang('AdminContents.municipalCouncils.general.surname'),
							'rules' => 'required'
						]
					];

					/*****************************************************/

					// Image Upload Validation
					$file = $this->request->getFile('municipal_council_image');
					if (isNotNull($file)) {
						$rulesImage = [
							'municipal_council_image' => [
								'label' => lang('AdminContents.municipalCouncils.general.image'),
								'rules' => [
									'uploaded[municipal_council_image]',
									'mime_in[municipal_council_image,'.IMAGE_UPLOAD_MIME.']',
									'max_size[municipal_council_image,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];

						$rules = array_merge($rules, $rulesImage);
					}

					/*****************************************************/

					if ($this->validate($rules)) {

						// Image Upload
						$fileName = $sql->municipal_council_image;
						$fileNameResult = '';
						if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->municipal_council_image);

							$fileName = slug($this->request->getVar('form[municipal_council_name]')).'_'.slug($this->request->getVar('form[municipal_council_surname]')).'_'.$file->getRandomName();
							$fileNameResult = $this->uploadSingleFile($file,
																$this->filePath,
																$fileName,
																$this->designSettings->municipal_council_image_width,
																$this->designSettings->municipal_council_image_height);
						}

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['municipal_council_image'] = $fileName;
							$data['municipal_council_updated_date'] = nowDate();
						}

						if ($fileNameResult == NULL) {
							$result = $this->general->updateModel($this->table, $data, ['municipal_council_id' => $municipal_council_id]);
							if ($result !== FALSE) {

								// Lang
								if (isNotNull($this->request->getVar('lang'))) {
									foreach ($this->request->getVar('lang') as $lang_id => $value) {
										$lang_data = [
											'municipal_council_id' => $municipal_council_id,
											'lang_id' => $lang_id,
											'municipal_council_sub_title' => $value['municipal_council_sub_title']
										];

										$langControlModel = $this->MunicipalCouncilsModel->municipalCouncilLangControlModel($municipal_council_id, $lang_id);
										if (isNotNull($langControlModel)) {
											$this->general->updateModel($this->tableLang, $lang_data, ['municipal_council_id' => $municipal_council_id, 'lang_id' => $lang_id]);
										} else {
											$this->general->insertModel($this->tableLang, $lang_data);
										}
									}
								}

								// Flash Data
								session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.edit.municipalCouncils', [$this->request->getVar('form[municipal_council_name]'), $this->request->getVar('form[municipal_council_surname]')]));

								$ajax_message['success'] = TRUE;

								if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
									$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$municipal_council_id);
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

	public function delete(int $municipal_council_id) {
		$sql = $this->MunicipalCouncilsModel->municipalCouncilInfoModel($municipal_council_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['municipal_council_id' => $municipal_council_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePath, $sql->municipal_council_image);

				// Lang
				$lang = $this->MunicipalCouncilsModel->municipalCouncilLangModel($municipal_council_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['municipal_council_id' => $row->municipal_council_id]);
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

	public function removeImage(int $municipal_council_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->table, ['municipal_council_image' => ''], ['municipal_council_id' => $municipal_council_id], $this->filePath);
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
