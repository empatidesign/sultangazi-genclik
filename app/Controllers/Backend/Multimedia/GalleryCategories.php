<?php
namespace App\Controllers\Backend\Multimedia;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Multimedia\GalleryCategoriesModel;
use App\Models\Backend\DatatableModel;

class GalleryCategories extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $filePath;
	protected $GalleryCategoriesModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'gallery_categories';
		$this->tableLang = 'gallery_categories_lang';
		$this->pageUrl = ADMIN_URL_MULTIMEDIA.'/'.ADMIN_URL_MULTIMEDIA_GALLERY_CATEGORIES;
		$this->filePath = FILE_PATH_GALLERY;
		$this->GalleryCategoriesModel = new GalleryCategoriesModel();
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
			$column = ['status', NULL, 'gallery_category_name', 'gallery_category_created_date', 'gallery_category_updated_date', NULL];
			$search = [];
			$orderBy = ['gallery_category_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_image($this->filePath.'/'.$row->gallery_category_image, 100);
					$array[] = $row->gallery_category_name;
					$array[] = dateFormat($row->gallery_category_created_date, 'd-m-Y H:i:s');
					$array[] = $row->gallery_category_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->gallery_category_updated_date, 'd-m-Y H:i:s') : NULL;
					$array[] = action_links($row->gallery_category_id, ['edit', 'delete'], $this->pageUrl);
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
						'label' => lang('AdminMultimedia.galleryCategories.general.status'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.gallery_category_name' => [
						'label' => lang('AdminMultimedia.galleryCategories.general.name'),
						'rules' => 'required'
					]
				];

				/*****************************************************/

				// Image Upload Validation
				$file = $this->request->getFile('gallery_category_image');
				if (isNotNull($file)) {
					$rulesFile = [
						'gallery_category_image' => [
							'label' => lang('AdminMultimedia.galleryCategories.general.image'),
							'rules' => [
								'uploaded[gallery_category_image]',
								'mime_in[gallery_category_image,'.IMAGE_UPLOAD_MIME.']',
								'max_size[gallery_category_image,'.IMAGE_UPLOAD_SIZE.']'
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
						$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][gallery_category_name]')).'_'.$file->getRandomName();
						$file->move($this->filePath, $fileName);
					}

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['gallery_category_image'] = $fileName;
						$data['gallery_category_created_date'] = nowDate();
					}

					$result = $this->general->insertModel($this->table, $data);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {
								$lang_data = [
									'gallery_category_id' => $result,
									'lang_id' => $lang_id,
									'gallery_category_name' => trim($value['gallery_category_name'])
								];

								$this->general->insertModel($this->tableLang, $lang_data);
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminMultimedia.result.add.galleryCategories', [$this->request->getVar('lang['.$this->defaultLangId.'][gallery_category_name]')]));

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

	public function edit(int $gallery_category_id) {
		$sql = $this->GalleryCategoriesModel->galleryCategoriesInfoModel($gallery_category_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->GalleryCategoriesModel->galleryCategoriesLangModel($gallery_category_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['gallery_category_name'] = $row->gallery_category_name;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'gallery_category_id' => $sql->gallery_category_id,
					'status' => $sql->status,
					'image' => isNotNull($sql->gallery_category_image) ? base_url($this->filePath.'/'.$sql->gallery_category_image) : NULL,
					'image_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$sql->gallery_category_id)
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $gallery_category_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->GalleryCategoriesModel->galleryCategoriesInfoModel($gallery_category_id);
				if (isNotNull($sql)) {

					$rules = [
						'form.status' => [
							'label' => lang('AdminMultimedia.galleryCategories.general.status'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.gallery_category_name' => [
							'label' => lang('AdminMultimedia.galleryCategories.general.name'),
							'rules' => 'required'
						]
					];

					/*****************************************************/

					// File Upload Validation
					$file = $this->request->getFile('gallery_category_image');
					if (isNotNull($file)) {
						$rulesFile = [
							'gallery_category_image' => [
								'label' => lang('AdminMultimedia.galleryCategories.general.image'),
								'rules' => [
									'uploaded[gallery_category_image]',
									'mime_in[gallery_category_image,'.IMAGE_UPLOAD_MIME.']',
									'max_size[gallery_category_image,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];

						$rules = array_merge($rules, $rulesFile);
					}

					/*****************************************************/

					if ($this->validate($rules)) {

						// File Upload
						$fileName = $sql->gallery_category_image;
						if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->gallery_category_image);

							$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][gallery_category_name]')).'_'.$file->getRandomName();
							$file->move($this->filePath, $fileName);
						}

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['gallery_category_image'] = $fileName;
							$data['gallery_category_updated_date'] = nowDate();
						}

						$result = $this->general->updateModel($this->table, $data, ['gallery_category_id' => $gallery_category_id]);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'gallery_category_id' => $gallery_category_id,
										'lang_id' => $lang_id,
										'gallery_category_name' => trim($value['gallery_category_name'])
									];

									$langControlModel = $this->GalleryCategoriesModel->galleryCategoriesLangControlModel($gallery_category_id, $lang_id);
									if (isNotNull($langControlModel)) {
										$this->general->updateModel($this->tableLang, $lang_data, ['gallery_category_id' => $gallery_category_id, 'lang_id' => $lang_id]);
									} else {
										$this->general->insertModel($this->tableLang, $lang_data);
									}
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminMultimedia.result.edit.galleryCategories', [$this->request->getVar('lang['.$this->defaultLangId.'][gallery_category_name]')]));

							$ajax_message['success'] = TRUE;

							if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
								$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$gallery_category_id);
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

	public function delete(int $gallery_category_id) {
		$sql = $this->GalleryCategoriesModel->galleryCategoriesInfoModel($gallery_category_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['gallery_category_id' => $gallery_category_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePath, $sql->gallery_category_image);

				// Lang
				$lang = $this->GalleryCategoriesModel->galleryCategoriesLangModel($gallery_category_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['gallery_category_id' => $row->gallery_category_id]);
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

	public function removeImage(int $gallery_category_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->table, ['gallery_category_image' => ''], ['gallery_category_id' => $gallery_category_id], $this->filePath);
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
