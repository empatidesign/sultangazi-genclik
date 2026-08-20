<?php
namespace App\Controllers\Backend\Contents;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Contents\InternalControlModel;
use App\Models\Backend\DatatableModel;

class InternalControl extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $filePath;
	protected $InternalControlModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'internal_control';
		$this->tableLang = 'internal_control_lang';
		$this->pageUrl = ADMIN_URL_CONTENTS.'/'.ADMIN_URL_INTERNAL_CONTROL;
		$this->filePath = FILE_PATH_INTERNAL_CONTROL;
		$this->InternalControlModel = new InternalControlModel();
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
			$column = ['status', NULL, NULL, 'strategic_plan_name', 'strategic_plan_year', 'strategic_plan_number', 'strategic_plan_created_date', 'strategic_plan_updated_date', NULL];
			$search = [];
			$orderBy = ['strategic_plan_year' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = isNotNull($row->strategic_plan_file) ? '<div class="icon-demo-content"><a target="_blank" href="'.base_url($this->filePath.'/'.$row->strategic_plan_file).'"><i class="far fa-file-alt"></i></a></div>' : '--';
					$array[] = isNotNull($row->strategic_plan_image) ? set_image($this->filePath.'/'.$row->strategic_plan_image, 100) : '--';
					$array[] = $row->strategic_plan_name;
					$array[] = $row->strategic_plan_year;
					$array[] = $row->strategic_plan_number;
					$array[] = dateFormat($row->strategic_plan_created_date, 'd-m-Y H:i:s');
					$array[] = $row->strategic_plan_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->strategic_plan_updated_date, 'd-m-Y H:i:s') : NULL;
					$array[] = action_links($row->strategic_plan_id, ['edit', 'delete'], $this->pageUrl);
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
						'label' => lang('AdminContents.internalControl.general.status'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.strategic_plan_name' => [
						'label' => lang('AdminContents.internalControl.general.name'),
						'rules' => 'required'
					],
					'form.strategic_plan_year' => [
						'label' => lang('AdminContents.internalControl.general.year'),
						'rules' => 'required'
					]
				];

				/*****************************************************/

				// File Upload Validation
				$rules2 = [];
				$file = $this->request->getFile('strategic_plan_file');
				if (isNotNull($file)) {
					$rules2 = [
						'strategic_plan_file' => [
							'label' => lang('AdminContents.internalControl.general.file.title'),
							'rules' => [
								'uploaded[strategic_plan_file]',
								'mime_in[strategic_plan_file,'.FILE_UPLOAD_MIME.']',
								'max_size[strategic_plan_file,'.FILE_UPLOAD_SIZE.']'
							]
						]
					];
				}

				// Image Upload Validation
				$rules3 = [];
				$image = $this->request->getFile('strategic_plan_image');
				if (isNotNull($image)) {
					$rules3 = [
						'strategic_plan_image' => [
							'label' => lang('AdminContents.internalControl.general.image.title'),
							'rules' => [
								'uploaded[strategic_plan_image]',
								'mime_in[strategic_plan_image,'.IMAGE_UPLOAD_MIME.']',
								'max_size[strategic_plan_image,'.IMAGE_UPLOAD_SIZE.']'
							]
						]
					];
				}

				/*****************************************************/

				$rules = array_merge_recursive($rules, $rules2, $rules3);

				if ($this->validate($rules)) {

					// File Upload
					$fileName = '';
					if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
						try {
							$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][strategic_plan_name]')).'_'.$file->getRandomName();
						    $file->move($this->filePath, $fileName);
						} catch (\Exception $e) {
							return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()]);
						}

					}

					// Image Upload
					$imageName = '';
					$imageNameResult = '';
					if (isNotNull($image) && $image->isValid() && !$image->hasMoved()) {
						$imageName = slug($this->request->getVar('lang['.$this->defaultLangId.'][strategic_plan_name]')).'_'.$image->getRandomName();
						$imageNameResult = $this->uploadSingleFile($image,
															 $this->filePath,
															 $imageName,
															 $this->designSettings->strategic_plan_and_performance_image_width,
															 $this->designSettings->strategic_plan_and_performance_image_height);
					}

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['strategic_plan_file'] = $fileName;
						$data['strategic_plan_image'] = $imageName;
						$data['strategic_plan_created_date'] = nowDate();
					}

					if ($imageNameResult == NULL) {
						$result = $this->general->insertModel($this->table, $data);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'strategic_plan_id' => $result,
										'lang_id' => $lang_id,
										'strategic_plan_name' => trim(upper($value['strategic_plan_name']))
									];

									$this->general->insertModel($this->tableLang, $lang_data);
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.add.internalControl', [$this->request->getVar('lang['.$this->defaultLangId.'][strategic_plan_name]')]));

							$ajax_message['success'] = TRUE;
							$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);

						} else {
							$ajax_message['error'] = lang('Admin.error.insert');
						}
					} else {
						$ajax_message['error'] = $imageNameResult;
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

	public function edit(int $strategic_plan_id) {
		$sql = $this->InternalControlModel->internalControlInfoModel($strategic_plan_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->InternalControlModel->internalControlLangModel($strategic_plan_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['strategic_plan_name'] = $row->strategic_plan_name;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'strategic_plan_id' => $sql->strategic_plan_id,
					'status' => $sql->status,
					'strategic_plan_number' => $sql->strategic_plan_number,
					'strategic_plan_year' => $sql->strategic_plan_year,
					'file' => isNotNull($sql->strategic_plan_file) ? base_url($this->filePath.'/'.$sql->strategic_plan_file) : NULL,
					'file_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-file/'.$sql->strategic_plan_id),
					'image' => isNotNull($sql->strategic_plan_image) ? base_url($this->filePath.'/'.$sql->strategic_plan_image) : NULL,
					'image_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$sql->strategic_plan_id)
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $strategic_plan_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->InternalControlModel->internalControlInfoModel($strategic_plan_id);
				if (isNotNull($sql)) {

					$rules = [
						'form.status' => [
							'label' => lang('AdminContents.internalControl.general.status'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.strategic_plan_name' => [
							'label' => lang('AdminContents.internalControl.general.name'),
							'rules' => 'required'
						],
						'form.strategic_plan_year' => [
							'label' => lang('AdminContents.internalControl.general.year'),
							'rules' => 'required'
						]
					];

					/*****************************************************/

					// File Upload Validation
					$rules2 = [];
					$file = $this->request->getFile('strategic_plan_file');
					if (isNotNull($file)) {
						$rules2 = [
							'strategic_plan_file' => [
								'label' => lang('AdminContents.internalControl.general.file.title'),
								'rules' => [
									'uploaded[strategic_plan_file]',
									'mime_in[strategic_plan_file,'.FILE_UPLOAD_MIME.']',
									'max_size[strategic_plan_file,'.FILE_UPLOAD_SIZE.']'
								]
							]
						];
					}

					// Image Upload Validation
					$rules3 = [];
					$image = $this->request->getFile('strategic_plan_image');
					if (isNotNull($image)) {
						$rules3 = [
							'strategic_plan_image' => [
								'label' => lang('AdminContents.internalControl.general.image.title'),
								'rules' => [
									'uploaded[strategic_plan_image]',
									'mime_in[strategic_plan_image,'.IMAGE_UPLOAD_MIME.']',
									'max_size[strategic_plan_image,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];
					}

					/*****************************************************/

					$rules = array_merge_recursive($rules, $rules2, $rules3);

					if ($this->validate($rules)) {

						// File Upload
						$fileName = $sql->strategic_plan_file;
						if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->strategic_plan_file);

							$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][strategic_plan_name]')).'_'.$file->getRandomName();
							$file->move($this->filePath, $fileName);
						}

						// Image Upload
						$imageName = $sql->strategic_plan_image;
						$imageNameResult = '';
						if (isNotNull($image) && $image->isValid() && !$image->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->strategic_plan_image);

							$imageName = slug($this->request->getVar('lang['.$this->defaultLangId.'][strategic_plan_name]')).'_'.$image->getRandomName();
							$imageNameResult = $this->uploadSingleFile($image,
																 $this->filePath,
																 $imageName,
																 $this->designSettings->strategic_plan_and_performance_image_width,
																 $this->designSettings->strategic_plan_and_performance_image_height);
						}

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['strategic_plan_file'] = $fileName;
							$data['strategic_plan_image'] = $imageName;
							$data['strategic_plan_updated_date'] = nowDate();
						}

						if ($imageNameResult == NULL) {
							$result = $this->general->updateModel($this->table, $data, ['strategic_plan_id' => $strategic_plan_id]);
							if ($result !== FALSE) {

								// Lang
								if (isNotNull($this->request->getVar('lang'))) {
									foreach ($this->request->getVar('lang') as $lang_id => $value) {
										$lang_data = [
											'strategic_plan_id' => $strategic_plan_id,
											'lang_id' => $lang_id,
											'strategic_plan_name' => trim(upper($value['strategic_plan_name']))
										];

										$langControlModel = $this->InternalControlModel->internalControlLangControlModel($strategic_plan_id, $lang_id);
										if (isNotNull($langControlModel)) {
											$this->general->updateModel($this->tableLang, $lang_data, ['strategic_plan_id' => $strategic_plan_id, 'lang_id' => $lang_id]);
										} else {
											$this->general->insertModel($this->tableLang, $lang_data);
										}
									}
								}

								// Flash Data
								session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.edit.internalControl', [$this->request->getVar('lang['.$this->defaultLangId.'][strategic_plan_name]')]));

								$ajax_message['success'] = TRUE;

								if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
									$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$strategic_plan_id);
								} else {
									$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);
								}

							} else {
								$ajax_message['error'] = lang('Admin.error.update');
							}
						} else {
							$ajax_message['error'] = $imageNameResult;
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

	public function delete(int $strategic_plan_id) {
		$sql = $this->InternalControlModel->internalControlInfoModel($strategic_plan_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['strategic_plan_id' => $strategic_plan_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePath, $sql->strategic_plan_file);
				unlinkFile($this->filePath, $sql->strategic_plan_image);

				// Lang
				$lang = $this->InternalControlModel->internalControlLangModel($strategic_plan_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['strategic_plan_id' => $row->strategic_plan_id]);
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

	public function removeFile(int $strategic_plan_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->table, ['strategic_plan_file' => ''], ['strategic_plan_id' => $strategic_plan_id], $this->filePath);
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

	public function removeImage(int $strategic_plan_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->table, ['strategic_plan_image' => ''], ['strategic_plan_id' => $strategic_plan_id], $this->filePath);
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
