<?php
namespace App\Controllers\Backend\Multimedia;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Multimedia\GalleryModel;
use App\Models\Backend\DatatableModel;

class Gallery extends BaseController {

	protected $table;
	protected $tableLang;
	protected $tablePictures;
	protected $pageUrl;
	protected $filePath;
	protected $GalleryModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'gallery';
		$this->tableLang = 'gallery_lang';
		$this->tablePictures = 'gallery_pictures';
		$this->pageUrl = ADMIN_URL_MULTIMEDIA.'/'.ADMIN_URL_MULTIMEDIA_GALLERY;
		$this->filePath = FILE_PATH_GALLERY;
		$this->GalleryModel = new GalleryModel();
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
			$column = ['status', 'gallery_category_name', 'gallery_name', 'pictures_total', 'gallery_created_date', 'gallery_updated_date', NULL];
			$search = [];
			$orderBy = ['gallery_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = $row->gallery_category_name;
					$array[] = $row->gallery_name;
					$array[] = $row->pictures_total;
					$array[] = dateFormat($row->gallery_created_date, 'd-m-Y H:i:s');
					$array[] = $row->gallery_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->gallery_updated_date, 'd-m-Y H:i:s') : NULL;
					$array[] = action_links($row->gallery_id, ['edit', 'delete'], $this->pageUrl);
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
		$last_id = $this->general->lastIDModel($this->table, 'gallery_id');

		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_name' => 'add',
			'page_url' => $this->pageUrl,
			'last_id' => $last_id,
			'list' =>[
				'categories' => $this->GalleryModel->galleryCategoriesListModel($this->defaultLangId)
			]
		]);
	}

	public function insert() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->GalleryModel->galleryCategoriesInfoModel(trim($this->request->getVar('form[gallery_category_id]')), $this->defaultLangId);
				if (isNotNull($sql)) {

					$rules = [
						'form.status' => [
							'label' => lang('AdminMultimedia.gallery.general.status'),
							'rules' => 'required'
						],
						'form.gallery_category_id' => [
							'label' => lang('AdminMultimedia.gallery.general.category'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.gallery_name' => [
							'label' => lang('AdminMultimedia.gallery.general.name'),
							'rules' => 'required'
						]
					];

					if ($this->validate($rules)) {

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['gallery_created_date'] = nowDate();
						}

						$result = $this->general->insertModel($this->table, $data);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'gallery_id' => $result,
										'lang_id' => $lang_id,
										'gallery_name' => trim($value['gallery_name'])
									];

									$this->general->insertModel($this->tableLang, $lang_data);
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminMultimedia.result.add.gallery', [$sql->gallery_category_name]));

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
				$ajax_message['error'] = lang('Admin.error.description');
			}
		} else {
			$ajax_message['error'] = lang('Admin.error.ajax');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function edit(int $gallery_id) {
		$sql = $this->GalleryModel->galleryInfoModel($gallery_id, $this->defaultLangId);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->GalleryModel->galleryLangModel($gallery_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['gallery_name'] = $row->gallery_name;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'gallery_id' => $sql->gallery_id,
					'status' => $sql->status,
					'gallery_category_id' => $sql->gallery_category_id
				],
				'list' =>[
					'categories' => $this->GalleryModel->galleryCategoriesListModel($this->defaultLangId)
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $gallery_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->GalleryModel->galleryInfoModel($gallery_id, $this->defaultLangId);
				if (isNotNull($sql)) {

					$rules = [
						'form.status' => [
							'label' => lang('AdminMultimedia.gallery.general.status'),
							'rules' => 'required'
						],
						'form.gallery_category_id' => [
							'label' => lang('AdminMultimedia.gallery.general.category'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.gallery_name' => [
							'label' => lang('AdminMultimedia.gallery.general.name'),
							'rules' => 'required'
						]
					];

					if ($this->validate($rules)) {

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['gallery_updated_date'] = nowDate();
						}

						$result = $this->general->updateModel($this->table, $data, ['gallery_id' => $gallery_id]);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'gallery_id' => $gallery_id,
										'lang_id' => $lang_id,
										'gallery_name' => trim($value['gallery_name'])
									];

									$langControlModel = $this->GalleryModel->galleryLangControlModel($gallery_id, $lang_id);
									if (isNotNull($langControlModel)) {
										$this->general->updateModel($this->tableLang, $lang_data, ['gallery_id' => $gallery_id, 'lang_id' => $lang_id]);
									} else {
										$this->general->insertModel($this->tableLang, $lang_data);
									}
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminMultimedia.result.edit.gallery', [$sql->gallery_category_name]));

							$ajax_message['success'] = TRUE;

							if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
								$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$gallery_id);
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
			$ajax_message['error'] = lang('Admin.ajaxError');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function delete(int $gallery_id) {
		$sql = $this->GalleryModel->galleryInfoModel($gallery_id, $this->defaultLangId);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['gallery_id' => $gallery_id]);
			if ($delete) {
				// Flash Data
				session()->setFlashdata('flashDataMessageSuccess', lang('Admin.success.recordDeleted'));

				// Lang
				$lang = $this->GalleryModel->galleryLangModel($gallery_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['gallery_id' => $row->gallery_id]);
					}
				}

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

	/*****************************************************/

	public function imageList(int $gallery_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$array = [];
				$sql = $this->GalleryModel->galleryPictureListModel($gallery_id);
				if (isNotNull($sql)) {
					foreach ($sql as $row) {
						$array[] = [
							'id' => $row->gallery_picture_id,
							'image' => base_url($this->filePath.'/'.$row->gallery_picture),
							'type' => 'gallery',
							'update' => TRUE
						];
					}
				}

				$ajax_message['image_list'] = $array;

			} else {
				$ajax_message['error'] = lang('Admin.error.description');
			}
		} else {
			$ajax_message['error'] = lang('Admin.error.ajax');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function imageUpload(int $gallery_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$validate = [
					'qqfile' => [
						'label' => lang('AdminMultimedia.gallery.general.image.title'),
						'rules' => [
							'uploaded[qqfile]',
							'mime_in[qqfile,'.IMAGE_UPLOAD_MIME.']',
							'max_size[qqfile,'.IMAGE_UPLOAD_SIZE.']'
						]
					]
				];

				if ($this->validate($validate)) {

					$files = $this->request->getFileMultiple('qqfile');
					foreach ($files as $file) {
						if ($file->isValid() && !$file->hasMoved()) {
							$fileName = $file->getRandomName();
							$file->move($this->filePath, $fileName);

							// Order
							$gallery_picture_order = 0;
							$gallery_picture_order_sql = $this->GalleryModel->galleryPictureOrderModel($gallery_id);
							if (isNotNull($gallery_picture_order_sql)) {
								$gallery_picture_order = $gallery_picture_order_sql->gallery_picture_order + 1;
							}

							$data = [
								'gallery_id' => $gallery_id,
								'gallery_picture' => $fileName,
								'gallery_picture_order' => $gallery_picture_order
							];
							$this->general->insertModel($this->tablePictures, $data);
						}
					}

					$ajax_message['success'] = lang('Admin.success.description');

				} else {
					$error = $this->validator->getErrors();
					$ajax_message['error'] = $error['qqfile'];
				}

			} else {
				$ajax_message['error'] = lang('Admin.error.description');
			}
		} else {
			$ajax_message['error'] = lang('Admin.error.ajax');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function imageSort() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'GET') {

				$id = $this->request->getVar('id');
				$index = $this->request->getVar('index');

				$result = $this->general->updateModel($this->tablePictures, ['gallery_picture_order' => $index], ['gallery_picture_id' => $id]);
				if ($result !== FALSE) {
					$ajax_message['success'] = TRUE;
				} else {
					$ajax_message['error'] = lang('Admin.update.error');
				}

			} else {
				$ajax_message['error'] = lang('Admin.error.description');
			}
		} else {
			$ajax_message['error'] = lang('Admin.error.ajax');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function imageDelete() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'GET') {

				$id = $this->request->getVar('id');

				$sql = $this->GalleryModel->galleryPictureInfoModel($id);
				if (isNotNull($sql)) {

					$delete = $this->general->deleteModel($this->tablePictures, ['gallery_picture_id' => $id]);
					if ($delete) {

						// Unlink
						unlinkFile($this->filePath, $sql->gallery_picture);

						$ajax_message['success'] = lang('Admin.success.description');

					} else {
						$ajax_message['error'] = lang('Admin.error.delete');
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
}
