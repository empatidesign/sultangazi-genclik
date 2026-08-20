<?php
namespace App\Controllers\Backend\Contents;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Contents\PagesModel;
use App\Models\Backend\DatatableModel;

class Pages extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $filePath;
	protected $PagesModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'pages';
		$this->tableLang = 'pages_lang';
		$this->pageUrl = ADMIN_URL_CONTENTS.'/'.ADMIN_URL_PAGES;
		$this->filePath = FILE_PATH_PAGES;
		$this->PagesModel = new PagesModel();
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
			$column = ['status', NULL, 'page_name', 'page_created_date', 'page_updated_date', NULL];
			$search = [];
			$orderBy = ['page_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_image($this->filePath.'/'.$row->page_image, 100);
					$array[] = $row->page_name;
					$array[] = dateFormat($row->page_created_date, 'd-m-Y H:i:s');
					$array[] = $row->page_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->page_updated_date, 'd-m-Y H:i:s') : NULL;
					$array[] = action_links($row->page_id, ['edit', 'delete'], $this->pageUrl);
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
						'label' => lang('AdminContents.pages.general.status'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.page_name' => [
						'label' => lang('AdminContents.pages.general.name'),
						'rules' => 'required'
					]
				];

				/*****************************************************/

				// Image Upload Validation
				$file = $this->request->getFile('page_image');
				if (isNotNull($file)) {
					$rulesImage = [
						'page_image' => [
							'label' => lang('AdminContents.pages.general.image'),
							'rules' => [
								'uploaded[page_image]',
								'mime_in[page_image,'.IMAGE_UPLOAD_MIME.']',
								'max_size[page_image,'.IMAGE_UPLOAD_SIZE.']'
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
						$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][page_name]')).'_'.$file->getRandomName();
						$fileNameResult = $this->uploadSingleFile($file, $this->filePath, $fileName);
					}

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['page_image'] = $fileName;
						$data['page_created_date'] = nowDate();
					}

					if ($fileNameResult == NULL) {
						$result = $this->general->insertModel($this->table, $data);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {

									// Slug
									if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][page_slug]'))) {
										$slug = slug($value['page_slug']);
									} else {
										$slug = isNotNull($value['page_name']) ? slug($value['page_name']) : $this->request->getVar('lang['.$this->defaultLangId.'][page_name]');
									}

									$lang_data = [
										'page_id' => $result,
										'lang_id' => $lang_id,
										'page_name' => isNotNull($value['page_name']) ? $value['page_name'] : $this->request->getVar('lang['.$this->defaultLangId.'][page_name]'),
										'page_description' => $value['page_description'],
										'page_meta_title' => $value['page_meta_title'],
										'page_meta_keywords' => $value['page_meta_keywords'],
										'page_meta_description' => $value['page_meta_description'],
										'page_slug' => $slug
									];

									$this->general->insertModel($this->tableLang, $lang_data);
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.add.pages', [$this->request->getVar('lang['.$this->defaultLangId.'][page_name]')]));

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

	public function edit(int $page_id) {
		$sql = $this->PagesModel->pagesInfoModel($page_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->PagesModel->pagesLangModel($page_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['page_name'] = $row->page_name;
					$lang_array['data']['translations'][$row->lang_id]['page_description'] = $row->page_description;
					$lang_array['data']['translations'][$row->lang_id]['page_meta_title'] = $row->page_meta_title;
					$lang_array['data']['translations'][$row->lang_id]['page_meta_keywords'] = $row->page_meta_keywords;
					$lang_array['data']['translations'][$row->lang_id]['page_meta_description'] = $row->page_meta_description;
					$lang_array['data']['translations'][$row->lang_id]['page_slug'] = $row->page_slug;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'page_id' => $sql->page_id,
					'status' => $sql->status,
					'image' => isNotNull($sql->page_image) ? base_url($this->filePath.'/'.$sql->page_image) : NULL,
					'image_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$sql->page_id)
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $page_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->PagesModel->pagesInfoModel($page_id);
				if (isNotNull($sql)) {

					$rules = [
						'form.status' => [
							'label' => lang('AdminContents.pages.general.status'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.page_name' => [
							'label' => lang('AdminContents.pages.general.name'),
							'rules' => 'required'
						]
					];

					/*****************************************************/

					// Image Upload Validation
					$file = $this->request->getFile('page_image');
					if (isNotNull($file)) {
						$rulesImage = [
							'page_image' => [
								'label' => lang('AdminContents.pages.general.image'),
								'rules' => [
									'uploaded[page_image]',
									'mime_in[page_image,'.IMAGE_UPLOAD_MIME.']',
									'max_size[page_image,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];

						$rules = array_merge($rules, $rulesImage);
					}

					/*****************************************************/

					if ($this->validate($rules)) {

						// Image Upload
						$fileName = $sql->page_image;
						$fileNameResult = '';
						if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->page_image);

							$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][page_name]')).'_'.$file->getRandomName();
							$fileNameResult = $this->uploadSingleFile($file, $this->filePath, $fileName);
						}

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['page_image'] = $fileName;
							$data['page_updated_date'] = nowDate();
						}

						if ($fileNameResult == NULL) {
							$result = $this->general->updateModel($this->table, $data, ['page_id' => $page_id]);
							if ($result !== FALSE) {

								// Lang
								if (isNotNull($this->request->getVar('lang'))) {
									foreach ($this->request->getVar('lang') as $lang_id => $value) {

										// Slug
										if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][page_slug]'))) {
											$slug = slug($value['page_slug']);
										} else {
											$slug = isNotNull($value['page_name']) ? slug($value['page_name']) : $this->request->getVar('lang['.$this->defaultLangId.'][page_name]');
										}

										$lang_data = [
											'page_id' => $page_id,
											'lang_id' => $lang_id,
											'page_name' => isNotNull($value['page_name']) ? $value['page_name'] : $this->request->getVar('lang['.$this->defaultLangId.'][page_name]'),
											'page_description' => $value['page_description'],
											'page_meta_title' => $value['page_meta_title'],
											'page_meta_keywords' => $value['page_meta_keywords'],
											'page_meta_description' => $value['page_meta_description'],
											'page_slug' => $slug
										];

										$langControlModel = $this->PagesModel->pagesLangControlModel($page_id, $lang_id);
										if (isNotNull($langControlModel)) {
											$this->general->updateModel($this->tableLang, $lang_data, ['page_id' => $page_id, 'lang_id' => $lang_id]);
										} else {
											$this->general->insertModel($this->tableLang, $lang_data);
										}
									}
								}

								// Flash Data
								session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.edit.pages', [$this->request->getVar('lang['.$this->defaultLangId.'][page_name]')]));

								$ajax_message['success'] = TRUE;

								if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
									$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$page_id);
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

	public function delete(int $page_id) {
		$sql = $this->PagesModel->pagesInfoModel($page_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['page_id' => $page_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePath, $sql->page_image);

				// Lang
				$lang = $this->PagesModel->pagesLangModel($page_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['page_id' => $row->page_id]);
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

	public function removeImage(int $page_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->table, ['page_image' => ''], ['page_id' => $page_id], $this->filePath);
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
