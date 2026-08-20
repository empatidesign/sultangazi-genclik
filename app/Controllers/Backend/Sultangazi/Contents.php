<?php
namespace App\Controllers\Backend\Sultangazi;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Sultangazi\ContentsModel;
use App\Models\Backend\DatatableModel;

class Contents extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $filePath;
	protected $ContentsModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'sultangazi_contents';
		$this->tableLang = 'sultangazi_contents_lang';
		$this->pageUrl = ADMIN_URL_SULTANGAZI.'/'.ADMIN_URL_SULTANGAZI_CONTENTS;
		$this->filePath = FILE_PATH_SULTANGAZI;
		$this->ContentsModel = new ContentsModel();
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
			$column = ['status', NULL, 'content_name', 'content_created_date', 'content_updated_date', NULL];
			$search = [];
			$orderBy = ['content_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_image($this->filePath.'/'.$row->content_image, 100);
					$array[] = $row->content_name;
					$array[] = dateFormat($row->content_created_date, 'd-m-Y H:i:s');
					$array[] = $row->content_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->content_updated_date, 'd-m-Y H:i:s') : NULL;
					$array[] = action_links($row->content_id, ['edit', 'delete'], $this->pageUrl);
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
						'label' => lang('AdminSultangazi.contents.general.status'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.content_name' => [
						'label' => lang('AdminSultangazi.contents.general.name'),
						'rules' => 'required'
					]
				];

				/*****************************************************/

				// Image Upload Validation
				$file = $this->request->getFile('content_image');
				if (isNotNull($file)) {
					$rulesImage = [
						'content_image' => [
							'label' => lang('AdminSultangazi.contents.general.image'),
							'rules' => [
								'uploaded[content_image]',
								'mime_in[content_image,'.IMAGE_UPLOAD_MIME.']',
								'max_size[content_image,'.IMAGE_UPLOAD_SIZE.']'
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
						$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][content_name]')).'_'.$file->getRandomName();
						$fileNameResult = $this->uploadSingleFile($file, $this->filePath, $fileName);
					}

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['content_image'] = $fileName;
						$data['content_created_date'] = nowDate();
					}

					if ($fileNameResult == NULL) {
						$result = $this->general->insertModel($this->table, $data);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {

									// Slug
									if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][content_slug]'))) {
										$slug = slug($value['content_slug']);
									} else {
										$slug = isNotNull($value['content_name']) ? slug($value['content_name']) : $this->request->getVar('lang['.$this->defaultLangId.'][content_name]');
									}

									$lang_data = [
										'content_id' => $result,
										'lang_id' => $lang_id,
										'content_name' => isNotNull($value['content_name']) ? $value['content_name'] : $this->request->getVar('lang['.$this->defaultLangId.'][content_name]'),
										'content_description' => $value['content_description'],
										'content_meta_title' => $value['content_meta_title'],
										'content_meta_keywords' => $value['content_meta_keywords'],
										'content_meta_description' => $value['content_meta_description'],
										'content_slug' => $slug
									];

									$this->general->insertModel($this->tableLang, $lang_data);
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminSultangazi.result.add.contents', [$this->request->getVar('lang['.$this->defaultLangId.'][content_name]')]));

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

	public function edit(int $content_id) {
		$sql = $this->ContentsModel->contentsInfoModel($content_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->ContentsModel->contentsLangModel($content_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['content_name'] = $row->content_name;
					$lang_array['data']['translations'][$row->lang_id]['content_description'] = $row->content_description;
					$lang_array['data']['translations'][$row->lang_id]['content_meta_title'] = $row->content_meta_title;
					$lang_array['data']['translations'][$row->lang_id]['content_meta_keywords'] = $row->content_meta_keywords;
					$lang_array['data']['translations'][$row->lang_id]['content_meta_description'] = $row->content_meta_description;
					$lang_array['data']['translations'][$row->lang_id]['content_slug'] = $row->content_slug;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'content_id' => $sql->content_id,
					'status' => $sql->status,
					'image' => isNotNull($sql->content_image) ? base_url($this->filePath.'/'.$sql->content_image) : NULL,
					'image_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$sql->content_id)
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $content_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->ContentsModel->contentsInfoModel($content_id);
				if (isNotNull($sql)) {

					$rules = [
						'form.status' => [
							'label' => lang('AdminSultangazi.contents.general.status'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.content_name' => [
							'label' => lang('AdminSultangazi.contents.general.name'),
							'rules' => 'required'
						]
					];

					/*****************************************************/

					// Image Upload Validation
					$file = $this->request->getFile('content_image');
					if (isNotNull($file)) {
						$rulesImage = [
							'content_image' => [
								'label' => lang('AdminSultangazi.contents.general.image'),
								'rules' => [
									'uploaded[content_image]',
									'mime_in[content_image,'.IMAGE_UPLOAD_MIME.']',
									'max_size[content_image,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];

						$rules = array_merge($rules, $rulesImage);
					}

					/*****************************************************/

					if ($this->validate($rules)) {

						// Image Upload
						$fileName = $sql->content_image;
						$fileNameResult = '';
						if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->content_image);

							$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][content_name]')).'_'.$file->getRandomName();
							$fileNameResult = $this->uploadSingleFile($file, $this->filePath, $fileName);
						}

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['content_image'] = $fileName;
							$data['content_updated_date'] = nowDate();
						}

						if ($fileNameResult == NULL) {
							$result = $this->general->updateModel($this->table, $data, ['content_id' => $content_id]);
							if ($result !== FALSE) {

								// Lang
								if (isNotNull($this->request->getVar('lang'))) {
									foreach ($this->request->getVar('lang') as $lang_id => $value) {

										// Slug
										if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][content_slug]'))) {
											$slug = slug($value['content_slug']);
										} else {
											$slug = isNotNull($value['content_name']) ? slug($value['content_name']) : $this->request->getVar('lang['.$this->defaultLangId.'][content_name]');
										}

										$lang_data = [
											'content_id' => $content_id,
											'lang_id' => $lang_id,
											'content_name' => isNotNull($value['content_name']) ? $value['content_name'] : $this->request->getVar('lang['.$this->defaultLangId.'][content_name]'),
											'content_description' => $value['content_description'],
											'content_meta_title' => $value['content_meta_title'],
											'content_meta_keywords' => $value['content_meta_keywords'],
											'content_meta_description' => $value['content_meta_description'],
											'content_slug' => $slug
										];

										$langControlModel = $this->ContentsModel->contentsLangControlModel($content_id, $lang_id);
										if (isNotNull($langControlModel)) {
											$this->general->updateModel($this->tableLang, $lang_data, ['content_id' => $content_id, 'lang_id' => $lang_id]);
										} else {
											$this->general->insertModel($this->tableLang, $lang_data);
										}
									}
								}

								// Flash Data
								session()->setFlashdata('flashDataMessageSuccess', lang('AdminSultangazi.result.edit.contents', [$this->request->getVar('lang['.$this->defaultLangId.'][content_name]')]));

								$ajax_message['success'] = TRUE;

								if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
									$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$content_id);
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

	public function delete(int $content_id) {
		$sql = $this->ContentsModel->contentsInfoModel($content_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['content_id' => $content_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePath, $sql->content_image);

				// Lang
				$lang = $this->ContentsModel->contentsLangModel($content_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['content_id' => $row->content_id]);
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

	public function removeImage(int $content_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->table, ['content_image' => ''], ['content_id' => $content_id], $this->filePath);
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
