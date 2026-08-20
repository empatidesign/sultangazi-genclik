<?php
namespace App\Controllers\Backend\President;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\President\PresidentGalleryModel;

class PresidentGallery extends BaseController {

	protected $table;
	protected $pageUrl;
	protected $filePath;
	protected $PresidentGalleryModel;

	public function __construct() {
		$this->table = 'president_gallery';
		$this->pageUrl = ADMIN_URL_PRESIDENT.'/'.ADMIN_URL_PRESIDENT_GALLERY;
		$this->filePath = FILE_PATH_GALLERY;
		$this->PresidentGalleryModel = new PresidentGalleryModel();
	}

	public function index() {
		$array = [];
		$sql = $this->PresidentGalleryModel->presidentGalleryListModel();
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'president_gallery_id' => $row->president_gallery_id,
					'president_gallery_image' => $row->president_gallery_image,
					'created_date' => dateFormat($row->president_gallery_created_date, 'd-m-Y H:i:s'),
					'updated_date' => $row->president_gallery_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->president_gallery_updated_date, 'd-m-Y H:i:s') : lang('Admin.notUpdated')
				];
			}
		}

		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_name' => 'index',
			'page_url' => $this->pageUrl,
			'file_path' => $this->filePath,
			'gallery_list' => $array
		]);
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

				$rules1 = [];
				$rules2 = [];

				$rules1 = [
					'form.status' => [
						'label' => lang('AdminPresident.gallery.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					]
				];

				/*****************************************************/

				// Image Upload Validation
				$file = $this->request->getFile('president_gallery_image');
				if (isNotNull($file)) {
					$rules2 = [
						'president_gallery_image' => [
							'label' => lang('AdminPresident.gallery.general.image'),
							'rules' => [
								'uploaded[president_gallery_image]',
								'mime_in[president_gallery_image,'.IMAGE_UPLOAD_MIME.']',
								'max_size[president_gallery_image,'.IMAGE_UPLOAD_SIZE.']'
							]
						]
					];
				}

				/*****************************************************/

				$rules = array_merge($rules1, $rules2);

				if ($this->validate($rules)) {

					// Image Upload
					$fileName = '';
					$fileNameResult = '';
					if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
						$fileName = $file->getRandomName();
						$fileNameResult = $this->uploadSingleFile($file, $this->filePath, $fileName);
					}

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['president_gallery_image'] = $fileName;
						$data['president_gallery_created_date'] = nowDate();
					}

					if ($fileNameResult == NULL) {
						$result = $this->general->insertModel($this->table, $data);
						if ($result !== FALSE) {

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminPresident.result.add.gallery'));

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

	public function edit(int $president_gallery_id) {
		$sql = $this->PresidentGalleryModel->presidentGalleryInfoModel($president_gallery_id);
		if (isNotNull($sql)) {

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'result' => [
					'president_gallery_id' => $sql->president_gallery_id,
					'status' => $sql->status,
					'status_mobile' => $sql->status_mobile,
					'president_gallery_image' => isNotNull($sql->president_gallery_image) ? base_url($this->filePath.'/'.$sql->president_gallery_image) : NULL,
					'image_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$sql->president_gallery_id)
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $president_gallery_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->PresidentGalleryModel->presidentGalleryInfoModel($president_gallery_id);
				if (isNotNull($sql)) {

					$rules1 = [];
					$rules2 = [];

					$rules1 = [
						'form.status' => [
							'label' => lang('AdminPresident.gallery.general.status'),
							'rules' => 'required'
						],
						'form.status_mobile' => [
							'label' => lang('Admin.mobile'),
							'rules' => 'required'
						]
					];

					/*****************************************************/

					// Image Upload Validation
					$file = $this->request->getFile('president_gallery_image');
					if (isNotNull($file)) {
						$rules2 = [
							'president_gallery_image' => [
								'label' => lang('AdminPresident.gallery.general.image'),
								'rules' => [
									'uploaded[president_gallery_image]',
									'mime_in[president_gallery_image,'.IMAGE_UPLOAD_MIME.']',
									'max_size[president_gallery_image,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];
					}

					/*****************************************************/

					$rules = array_merge($rules1, $rules2);

					if ($this->validate($rules)) {

						// Image Upload
						$fileName = $sql->president_gallery_image;
						$fileNameResult = '';
						if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->president_gallery_image);

							$fileName = $file->getRandomName();
							$fileNameResult = $this->uploadSingleFile($file, $this->filePath, $fileName);
						}

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['president_gallery_image'] = $fileName;
							$data['president_gallery_updated_date'] = nowDate();
						}

						if ($fileNameResult == NULL) {
							$result = $this->general->updateModel($this->table, $data, ['president_gallery_id' => $president_gallery_id]);
							if ($result !== FALSE) {

								$ajax_message['success'] = TRUE;

								if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
									$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$president_gallery_id);
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
			$ajax_message['error'] = lang('Admin.ajaxError');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function delete(int $president_gallery_id) {
		$sql = $this->PresidentGalleryModel->presidentGalleryInfoModel($president_gallery_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['president_gallery_id' => $president_gallery_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePath, $sql->president_gallery_image);

				// Flash Data
				session()->setFlashdata('flashDataMessageSuccess', lang('Admin.success.recordDeleted'));

				$ajax_message['success'] = TRUE;
				$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);
			} else {
				$ajax_message['error'] = lang('Admin.error.delete');
			}

		} else {
			$ajax_message['error'] = lang('Admin.error.noRecord');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function sort() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				foreach ($this->request->getVar('item') as $key => $row) {
					$result = $this->general->updateModel($this->table, ['president_gallery_order' => $key], ['president_gallery_id' => $row]);
					if ($result !== FALSE) {
						$ajax_message['success'] = TRUE;
					} else {
						$ajax_message['error'] = lang('Admin.error.update');
					}
				}

			} else {
				$ajax_message['error'] = lang('Admin.error.description');
			}
		} else {
			$ajax_message['error'] = lang('Admin.error.ajax');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function removeImage(int $president_gallery_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->table, ['president_gallery_image' => ''], ['president_gallery_id' => $president_gallery_id], $this->filePath);
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
