<?php
namespace App\Controllers\Backend\Contents;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Contents\ServicesModel;
use App\Models\Backend\DatatableModel;

class Services extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $filePath;
	protected $ServicesModel;
	protected $DatatableModel;
	protected $types;

	public function __construct() {
		$this->table = 'services';
		$this->tableLang = 'services_lang';
		$this->pageUrl = ADMIN_URL_CONTENTS.'/'.ADMIN_URL_SERVICES;
		$this->filePath = FILE_PATH_SERVICES;
		$this->ServicesModel = new ServicesModel();
		$this->DatatableModel = new DatatableModel();
		helper('array');

		// Types
		$this->types = [
			SERVICES_TYPE_1 => lang('AdminContents.services.general.types.option1'),
			SERVICES_TYPE_2 => lang('AdminContents.services.general.types.option2'),
			SERVICES_TYPE_3 => lang('AdminContents.services.general.types.option3')
		];
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
			$column = ['status', 'status_mobile', 'service_type', NULL, NULL, 'service_name', 'service_order', 'service_created_date', 'service_updated_date', NULL];
			$search = [];
			$orderBy = ['service_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_status($row->status_mobile);
					$array[] = $row->service_type ? dot_array_search($row->service_type.'.name', $this->types) : NULL;
					$array[] = set_image($this->filePath.'/'.$row->service_image, 100);
					$array[] = set_image($this->filePath.'/'.$row->service_icon, 75);
					$array[] = character_limiter($row->service_name, 50);
					$array[] = $row->service_order;
					$array[] = dateFormat($row->service_created_date, 'd-m-Y H:i:s');
					$array[] = $row->service_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->service_updated_date, 'd-m-Y H:i:s') : NULL;
					$array[] = action_links($row->service_id, ['edit', 'delete'], $this->pageUrl);
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
			'page_url' => $this->pageUrl,
			'list' => [
				'types' => $this->types
			]
		]);
	}

	public function insert() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules1 = [
					'form.status' => [
						'label' => lang('AdminContents.services.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					],
					'form.service_type' => [
						'label' => lang('AdminContents.services.general.types.title'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.service_name' => [
						'label' => lang('AdminContents.services.general.name'),
						'rules' => 'required'
					]
				];

				/*****************************************************/

				// Image Upload Validation
				$rules2 = [];
				$file = $this->request->getFile('service_image');
				if (isNotNull($file)) {
					$rules2 = [
						'service_image' => [
							'label' => lang('AdminContents.services.general.image'),
							'rules' => [
								'uploaded[service_image]',
								'mime_in[service_image,'.IMAGE_UPLOAD_MIME.']',
								'max_size[service_image,'.IMAGE_UPLOAD_SIZE.']'
							]
						]
					];
				}

				// Icon Upload Validation
				$rules3 = [];
				$fileIcon = $this->request->getFile('service_icon');
				if (isNotNull($fileIcon)) {
					$rules3 = [
						'service_icon' => [
							'label' => lang('AdminContents.services.general.icon'),
							'rules' => [
								'uploaded[service_icon]',
								'mime_in[service_icon,'.IMAGE_UPLOAD_MIME.']',
								'max_size[service_icon,'.IMAGE_UPLOAD_SIZE.']'
							]
						]
					];
				}

				/*****************************************************/

				$rules = array_merge_recursive($rules1, $rules2, $rules3);

				if ($this->validate($rules)) {

					// Image Upload
					$fileImageName = '';
					$fileImageNameResult = '';
					if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
						$fileImageName = slug($this->request->getVar('lang['.$this->defaultLangId.'][service_name]')).'_'.$file->getRandomName();
						$fileImageNameResult = $this->uploadSingleFile($file,
																	 $this->filePath,
																	 $fileImageName,
																	 $this->designSettings->service_image_width,
																	 $this->designSettings->service_image_height);
					}

					// Icon
					$fileIconName = '';
					if (isNotNull($fileIcon) && $fileIcon->isValid() && !$fileIcon->hasMoved()) {
						$fileIconName = 'icon_'.$fileIcon->getRandomName();
						$fileIcon->move($this->filePath, $fileIconName);
					}

					/*****************************************************/

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['service_image'] = $fileImageName;
						$data['service_icon'] = $fileIconName;
						$data['service_created_date'] = nowDate();
					}

					if ($fileImageNameResult == NULL) {
						$result = $this->general->insertModel($this->table, $data);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {

									// Slug
									if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][service_slug]'))) {
										$slug = slug($value['service_slug']);
									} else {
										$slug = isNotNull($value['service_name']) ? slug($value['service_name']) : $this->request->getVar('lang['.$this->defaultLangId.'][service_name]');
									}

									$lang_data = [
										'service_id' => $result,
										'lang_id' => $lang_id,
										'service_name' => isNotNull($value['service_name']) ? $value['service_name'] : $this->request->getVar('lang['.$this->defaultLangId.'][service_name]'),
										'service_link' => $value['service_link'],
										'service_short_description' => $value['service_short_description'],
										'service_description' => $value['service_description'],
										'service_meta_title' => $value['service_meta_title'],
										'service_meta_keywords' => $value['service_meta_keywords'],
										'service_meta_description' => $value['service_meta_description'],
										'service_slug' => $slug
									];

									$this->general->insertModel($this->tableLang, $lang_data);
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.add.services', [$this->request->getVar('lang['.$this->defaultLangId.'][service_name]')]));

							$ajax_message['success'] = TRUE;
							$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);

						} else {
							$ajax_message['error'] = lang('Admin.error.insert');
						}
					} else {
						$ajax_message['error'] = $fileImageNameResult;
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

	public function edit(int $service_id) {
		$sql = $this->ServicesModel->servicesInfoModel($service_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->ServicesModel->servicesLangModel($service_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['service_name'] = $row->service_name;
					$lang_array['data']['translations'][$row->lang_id]['service_link'] = $row->service_link;
					$lang_array['data']['translations'][$row->lang_id]['service_short_description'] = $row->service_short_description;
					$lang_array['data']['translations'][$row->lang_id]['service_description'] = $row->service_description;
					$lang_array['data']['translations'][$row->lang_id]['service_meta_title'] = $row->service_meta_title;
					$lang_array['data']['translations'][$row->lang_id]['service_meta_keywords'] = $row->service_meta_keywords;
					$lang_array['data']['translations'][$row->lang_id]['service_meta_description'] = $row->service_meta_description;
					$lang_array['data']['translations'][$row->lang_id]['service_slug'] = $row->service_slug;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'service_id' => $sql->service_id,
					'status' => $sql->status,
					'status_mobile' => $sql->status_mobile,
					'service_type' => $sql->service_type,
					'image' => [
						'base' => isNotNull($sql->service_image) ? base_url($this->filePath.'/'.$sql->service_image) : NULL,
						'remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$sql->service_id.'?action=image')
					],
					'icon' => [
						'base' => isNotNull($sql->service_icon) ? base_url($this->filePath.'/'.$sql->service_icon) : NULL,
						'remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$sql->service_id.'?action=icon')
					],
					'service_order' => $sql->service_order
				],
				'list' => [
					'types' => $this->types
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $service_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->ServicesModel->servicesInfoModel($service_id);
				if (isNotNull($sql)) {

					$rules1 = [
						'form.status' => [
							'label' => lang('AdminContents.services.general.status'),
							'rules' => 'required'
						],
						'form.status_mobile' => [
							'label' => lang('Admin.mobile'),
							'rules' => 'required'
						],
						'form.service_type' => [
							'label' => lang('AdminContents.services.general.types.title'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.service_name' => [
							'label' => lang('AdminContents.services.general.name'),
							'rules' => 'required'
						]
					];

					/*****************************************************/

					// Image Upload Validation
					$rules2 = [];
					$file = $this->request->getFile('service_image');
					if (isNotNull($file)) {
						$rules2 = [
							'service_image' => [
								'label' => lang('AdminContents.services.general.image'),
								'rules' => [
									'uploaded[service_image]',
									'mime_in[service_image,'.IMAGE_UPLOAD_MIME.']',
									'max_size[service_image,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];
					}

					// Icon Upload Validation
					$rules3 = [];
					$fileIcon = $this->request->getFile('service_icon');
					if (isNotNull($fileIcon)) {
						$rules3 = [
							'service_icon' => [
								'label' => lang('AdminContents.services.general.icon'),
								'rules' => [
									'uploaded[service_icon]',
									'mime_in[service_icon,'.IMAGE_UPLOAD_MIME.']',
									'max_size[service_icon,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];
					}

					/*****************************************************/

					$rules = array_merge_recursive($rules1, $rules2, $rules3);

					if ($this->validate($rules)) {

						// Image Upload
						$fileImageName = $sql->service_image;
						$fileImageNameResult = '';
						if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->service_image);

							$fileImageName = slug($this->request->getVar('lang['.$this->defaultLangId.'][service_name]')).'_'.$file->getRandomName();
							$fileImageNameResult = $this->uploadSingleFile($file,
																		 $this->filePath,
																		 $fileImageName,
																		 $this->designSettings->service_image_width,
																		 $this->designSettings->service_image_height);
						}

						// Icon
						$fileIconName = $sql->service_icon;
						if (isNotNull($fileIcon) && $fileIcon->isValid() && !$fileIcon->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->service_icon);

							$fileIconName = 'icon_'.$fileIcon->getRandomName();
							$fileIcon->move($this->filePath, $fileIconName);
						}

						/*****************************************************/

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['service_image'] = $fileImageName;
							$data['service_icon'] = $fileIconName;
							$data['service_updated_date'] = nowDate();
						}

						if ($fileImageNameResult == NULL) {
							$result = $this->general->updateModel($this->table, $data, ['service_id' => $service_id]);
							if ($result !== FALSE) {

								// Lang
								if (isNotNull($this->request->getVar('lang'))) {
									foreach ($this->request->getVar('lang') as $lang_id => $value) {

										// Slug
										if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][service_slug]'))) {
											$slug = slug($value['service_slug']);
										} else {
											$slug = slug($value['service_name']);
										}

										$lang_data = [
											'service_id' => $service_id,
											'lang_id' => $lang_id,
											'service_name' => isNotNull($value['service_name']) ? $value['service_name'] : $this->request->getVar('lang['.$this->defaultLangId.'][service_name]'),
											'service_link' => $value['service_link'],
											'service_short_description' => $value['service_short_description'],
											'service_description' => $value['service_description'],
											'service_meta_title' => $value['service_meta_title'],
											'service_meta_keywords' => $value['service_meta_keywords'],
											'service_meta_description' => $value['service_meta_description'],
											'service_slug' => $slug
										];

										$langControlModel = $this->ServicesModel->servicesLangControlModel($service_id, $lang_id);
										if (isNotNull($langControlModel)) {
											$this->general->updateModel($this->tableLang, $lang_data, ['service_id' => $service_id, 'lang_id' => $lang_id]);
										} else {
											$this->general->insertModel($this->tableLang, $lang_data);
										}
									}
								}

								// Flash Data
								session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.edit.services', [$this->request->getVar('lang['.$this->defaultLangId.'][service_name]')]));

								$ajax_message['success'] = TRUE;

								if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
									$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$service_id);
								} else {
									$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);
								}

							} else {
								$ajax_message['error'] = lang('Admin.error.update');
							}
						} else {
							$ajax_message['error'] = $fileImageNameResult;
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

	public function delete(int $service_id) {
		$sql = $this->ServicesModel->servicesInfoModel($service_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['service_id' => $service_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePath, $sql->service_image);
				unlinkFile($this->filePath, $sql->service_icon);

				// Lang
				$lang = $this->ServicesModel->servicesLangModel($service_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['service_id' => $row->service_id]);
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

	public function removeImage(int $service_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$data = [];
				if ($this->request->getVar('action') == 'image') {
					$data = ['service_image' => ''];
				} elseif ($this->request->getVar('action') == 'icon') {
					$data = ['service_icon' => ''];
				}

				$result = $this->general->removeDropifyImageModel($this->table, $data, ['service_id' => $service_id], $this->filePath);
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
