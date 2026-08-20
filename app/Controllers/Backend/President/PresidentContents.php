<?php
namespace App\Controllers\Backend\President;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\President\PresidentContentsModel;
use App\Models\Backend\DatatableModel;

class PresidentContents extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $filePath;
	protected $PresidentContentsModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'president_contents';
		$this->tableLang = 'president_contents_lang';
		$this->pageUrl = ADMIN_URL_PRESIDENT.'/'.ADMIN_URL_PRESIDENT_CONTENTS;
		$this->filePath = FILE_PATH_PRESIDENT;
		$this->PresidentContentsModel = new PresidentContentsModel();
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
			$column = ['status', 'status_mobile', NULL, 'president_content_name', 'president_content_created_date', 'president_content_updated_date', NULL];
			$search = [];
			$orderBy = ['president_content_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_status($row->status_mobile);
					$array[] = set_image($this->filePath.'/'.$row->president_content_image, 100);
					$array[] = $row->president_content_name;
					$array[] = dateFormat($row->president_content_created_date, 'd-m-Y H:i:s');
					$array[] = $row->president_content_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->president_content_updated_date, 'd-m-Y H:i:s') : NULL;
					$array[] = action_links($row->president_content_id, ['edit', 'delete'], $this->pageUrl);
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
						'label' => lang('AdminPresident.contents.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.president_content_name' => [
						'label' => lang('AdminPresident.contents.general.name'),
						'rules' => 'required'
					]
				];

				/*****************************************************/

				// Image Upload Validation
				$file = $this->request->getFile('president_content_image');
				if (isNotNull($file)) {
					$rulesImage = [
						'president_content_image' => [
							'label' => lang('AdminPresident.contents.general.image'),
							'rules' => [
								'uploaded[president_content_image]',
								'mime_in[president_content_image,'.IMAGE_UPLOAD_MIME.']',
								'max_size[president_content_image,'.IMAGE_UPLOAD_SIZE.']'
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
						$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][president_content_name]')).'_'.$file->getRandomName();
						$fileNameResult = $this->uploadSingleFile($file, $this->filePath, $fileName);
					}

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['president_content_image'] = $fileName;
						$data['president_content_created_date'] = nowDate();
					}

					if ($fileNameResult == NULL) {
						$result = $this->general->insertModel($this->table, $data);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {

									// Slug
									if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][president_content_slug]'))) {
										$slug = slug($value['president_content_slug']);
									} else {
										$slug = isNotNull($value['president_content_name']) ? slug($value['president_content_name']) : $this->request->getVar('lang['.$this->defaultLangId.'][president_content_name]');
									}

									$lang_data = [
										'president_content_id' => $result,
										'lang_id' => $lang_id,
										'president_content_name' => isNotNull($value['president_content_name']) ? $value['president_content_name'] : $this->request->getVar('lang['.$this->defaultLangId.'][president_content_name]'),
										'president_content_description' => $value['president_content_description'],
										'president_content_meta_title' => $value['president_content_meta_title'],
										'president_content_meta_keywords' => $value['president_content_meta_keywords'],
										'president_content_meta_description' => $value['president_content_meta_description'],
										'president_content_slug' => $slug
									];

									$this->general->insertModel($this->tableLang, $lang_data);
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminPresident.result.add.contents', [$this->request->getVar('lang['.$this->defaultLangId.'][president_content_name]')]));

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

	public function edit(int $president_content_id) {
		$sql = $this->PresidentContentsModel->presidentContentInfoModel($president_content_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->PresidentContentsModel->presidentContentLangModel($president_content_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['president_content_name'] = $row->president_content_name;
					$lang_array['data']['translations'][$row->lang_id]['president_content_description'] = $row->president_content_description;
					$lang_array['data']['translations'][$row->lang_id]['president_content_meta_title'] = $row->president_content_meta_title;
					$lang_array['data']['translations'][$row->lang_id]['president_content_meta_keywords'] = $row->president_content_meta_keywords;
					$lang_array['data']['translations'][$row->lang_id]['president_content_meta_description'] = $row->president_content_meta_description;
					$lang_array['data']['translations'][$row->lang_id]['president_content_slug'] = $row->president_content_slug;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'president_content_id' => $sql->president_content_id,
					'status' => $sql->status,
					'status_mobile' => $sql->status_mobile,
					'image' => isNotNull($sql->president_content_image) ? base_url($this->filePath.'/'.$sql->president_content_image) : NULL,
					'image_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$sql->president_content_id)
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $president_content_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->PresidentContentsModel->presidentContentInfoModel($president_content_id);
				if (isNotNull($sql)) {

					$rules = [
						'form.status' => [
							'label' => lang('AdminPresident.contents.general.status'),
							'rules' => 'required'
						],
						'form.status_mobile' => [
							'label' => lang('Admin.mobile'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.president_content_name' => [
							'label' => lang('AdminPresident.contents.general.name'),
							'rules' => 'required'
						]
					];

					/*****************************************************/

					// Image Upload Validation
					$file = $this->request->getFile('president_content_image');
					if (isNotNull($file)) {
						$rulesImage = [
							'president_content_image' => [
								'label' => lang('AdminPresident.contents.general.image'),
								'rules' => [
									'uploaded[president_content_image]',
									'mime_in[president_content_image,'.IMAGE_UPLOAD_MIME.']',
									'max_size[president_content_image,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];

						$rules = array_merge($rules, $rulesImage);
					}

					/*****************************************************/

					if ($this->validate($rules)) {

						// Image Upload
						$fileName = $sql->president_content_image;
						$fileNameResult = '';
						if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->president_content_image);

							$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][president_content_name]')).'_'.$file->getRandomName();
							$fileNameResult = $this->uploadSingleFile($file, $this->filePath, $fileName);
						}

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['president_content_image'] = $fileName;
							$data['president_content_updated_date'] = nowDate();
						}

						if ($fileNameResult == NULL) {
							$result = $this->general->updateModel($this->table, $data, ['president_content_id' => $president_content_id]);
							if ($result !== FALSE) {

								// Lang
								if (isNotNull($this->request->getVar('lang'))) {
									foreach ($this->request->getVar('lang') as $lang_id => $value) {

										// Slug
										if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][president_content_slug]'))) {
											$slug = slug($value['president_content_slug']);
										} else {
											$slug = isNotNull($value['president_content_name']) ? slug($value['president_content_name']) : $this->request->getVar('lang['.$this->defaultLangId.'][president_content_name]');
										}

										$lang_data = [
											'president_content_id' => $president_content_id,
											'lang_id' => $lang_id,
											'president_content_name' => isNotNull($value['president_content_name']) ? $value['president_content_name'] : $this->request->getVar('lang['.$this->defaultLangId.'][president_content_name]'),
											'president_content_description' => $value['president_content_description'],
											'president_content_meta_title' => $value['president_content_meta_title'],
											'president_content_meta_keywords' => $value['president_content_meta_keywords'],
											'president_content_meta_description' => $value['president_content_meta_description'],
											'president_content_slug' => $slug
										];

										$langControlModel = $this->PresidentContentsModel->presidentContentLangControlModel($president_content_id, $lang_id);
										if (isNotNull($langControlModel)) {
											$this->general->updateModel($this->tableLang, $lang_data, ['president_content_id' => $president_content_id, 'lang_id' => $lang_id]);
										} else {
											$this->general->insertModel($this->tableLang, $lang_data);
										}
									}
								}

								// Flash Data
								session()->setFlashdata('flashDataMessageSuccess', lang('AdminPresident.result.edit.contents', [$this->request->getVar('lang['.$this->defaultLangId.'][president_content_name]')]));

								$ajax_message['success'] = TRUE;

								if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
									$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$president_content_id);
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

	public function delete(int $president_content_id) {
		$sql = $this->PresidentContentsModel->presidentContentInfoModel($president_content_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['president_content_id' => $president_content_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePath, $sql->president_content_image);

				// Lang
				$lang = $this->PresidentContentsModel->presidentContentLangModel($president_content_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['president_content_id' => $row->president_content_id]);
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

	public function removeImage(int $president_content_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->table, ['president_content_image' => ''], ['president_content_id' => $president_content_id], $this->filePath);
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
