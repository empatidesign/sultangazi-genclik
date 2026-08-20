<?php
namespace App\Controllers\Backend\Designs;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Designs\BannerManagementModel;

class BannerManagement extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $filePath;
	protected $BannerManagementModel;
	protected $types;

	public function __construct() {
		$this->table = 'banner';
		$this->tableLang = 'banner_lang';
		$this->pageUrl = ADMIN_URL_DESIGNS.'/'.ADMIN_URL_BANNER_MANAGEMENT;
		$this->filePath = FILE_PATH_BANNER_MANAGEMENT;
		$this->BannerManagementModel = new BannerManagementModel();
		helper('array');

		// Types
		$this->types = [
			BANNER_TYPE_1 => lang('AdminDesigns.bannerManagement.general.types.option1'),
			BANNER_TYPE_2 => lang('AdminDesigns.bannerManagement.general.types.option2')
		];
	}

	public function index() {

		// Get
		$banner_type = trim($this->request->getVar('banner_type'));
		$banner_name = trim($this->request->getVar('banner_name'));

		// List
		$array = [];
		$sql = $this->BannerManagementModel->bannerListModel($this->defaultLangId, $banner_type, $banner_name);
		if (isNotNull($sql)) {
			foreach ($sql as $row) {
				$array[] = [
					'banner_id' => $row->banner_id,
					'banner_type' => $row->banner_type ? dot_array_search($row->banner_type.'.name', $this->types) : NULL,
					'banner_name' => $row->banner_name,
					'banner_web_image' => $row->banner_web_image,
					'dates' => [
						'start' => $row->banner_start_date != '0000-00-00' ? TRUE : FALSE,
						'end' => $row->banner_end_date != '0000-00-00' ? TRUE : FALSE,
						'created' => dateFormat($row->banner_created_date, 'd-m-Y H:i:s'),
						'updated' => $row->banner_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->banner_updated_date, 'd-m-Y H:i:s') : lang('Admin.notUpdated')
					]
				];
			}
		}

		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_name' => 'index',
			'page_url' => $this->pageUrl,
			'file_path' => $this->filePath,
			'list' => [
				'types' => $this->types,
				'banners' => $array
			]
		]);
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

				$rules1 = [];
				$rules2 = [];
				$rules3 = [];

				$rules1 = [
					'form.status' => [
						'label' => lang('AdminDesigns.bannerManagement.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					],
					'form.banner_type' => [
						'label' => lang('AdminDesigns.bannerManagement.general.types.title'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.banner_name' => [
						'label' => lang('AdminDesigns.bannerManagement.general.name'),
						'rules' => 'required'
					]
				];

				/*****************************************************/

				// Image Upload Validation
				$fileWeb = $this->request->getFile('banner_web_image');
				if (isNotNull($fileWeb)) {
					$rules2 = [
						'banner_web_image' => [
							'label' => lang('AdminDesigns.bannerManagement.general.image.web'),
							'rules' => [
								'uploaded[banner_web_image]',
								'mime_in[banner_web_image,'.IMAGE_UPLOAD_MIME.']',
								'max_size[banner_web_image,'.IMAGE_UPLOAD_SIZE.']'
							]
						]
					];
				}

				$fileMobile = $this->request->getFile('banner_mobile_image');
				if (isNotNull($fileMobile)) {
					$rules3 = [
						'banner_mobile_image' => [
							'label' => lang('AdminDesigns.bannerManagement.general.image.mobile'),
							'rules' => [
								'uploaded[banner_mobile_image]',
								'mime_in[banner_mobile_image,'.IMAGE_UPLOAD_MIME.']',
								'max_size[banner_mobile_image,'.IMAGE_UPLOAD_SIZE.']'
							]
						]
					];
				}

				/*****************************************************/

				$rules = array_merge_recursive($rules1, $rules2, $rules3);

				if ($this->validate($rules)) {

					// Image Upload
					// Web
					$fileNameWeb = '';
					$fileNameWebResult = '';
					if (isNotNull($fileWeb) && $fileWeb->isValid() && !$fileWeb->hasMoved()) {

						// Banner Type 1
						if (trim($this->request->getVar('form[banner_type]')) == BANNER_TYPE_1) {
							$fileNameWeb = slug($this->request->getVar('lang['.$this->defaultLangId.'][banner_name]')).'_'.$fileWeb->getRandomName();
							$fileNameWebResult = $this->uploadSingleFile($fileWeb,
																	 $this->filePath,
																	 $fileNameWeb,
																	 $this->designSettings->home_page_banner_web_image_width,
																	 $this->designSettings->home_page_banner_web_image_height);
						}

						// Banner Type 2
						if (trim($this->request->getVar('form[banner_type]')) == BANNER_TYPE_2) {
							$fileNameWeb = slug($this->request->getVar('lang['.$this->defaultLangId.'][banner_name]')).'_'.$fileWeb->getRandomName();
							$fileNameWebResult = $this->uploadSingleFile($fileWeb,
																	 $this->filePath,
																	 $fileNameWeb,
																	 1920,
																	 1080);
						}

					}

					// Mobile
					$fileNameMobileResult = '';
					$fileNameMobile = '';
					if (isNotNull($fileMobile) && $fileMobile->isValid() && !$fileMobile->hasMoved()) {

						// Banner Type 1
						if (trim($this->request->getVar('form[banner_type]')) == BANNER_TYPE_1) {
							$fileNameMobile = slug($this->request->getVar('lang['.$this->defaultLangId.'][banner_name]')).'_'.$fileMobile->getRandomName();
							$fileNameMobileResult = $this->uploadSingleFile($fileMobile,
																			$this->filePath,
																			$fileNameMobile,
																			$this->designSettings->home_page_banner_mobile_image_width,
																			$this->designSettings->home_page_banner_mobile_image_height);
						}

						// Banner Type 2
						if (trim($this->request->getVar('form[banner_type]')) == BANNER_TYPE_2) {
							$fileNameMobile = slug($this->request->getVar('lang['.$this->defaultLangId.'][banner_name]')).'_'.$fileMobile->getRandomName();
							$fileNameMobileResult = $this->uploadSingleFile($fileMobile,
																			$this->filePath,
																			$fileNameMobile,
																			777,
																			697);
						}

					}

					// Showing Date
					$banner_start_date = '';
					$banner_end_date = '';
					$banner_showing_date = trim($this->request->getVar('banner_showing_date'));
					if (isNotNull($banner_showing_date)) {
						$banner_showing_date_explode = explode(' - ', $banner_showing_date);
						$banner_start_date = dateFormat($banner_showing_date_explode[0], 'Y-m-d');
						$banner_end_date = dateFormat($banner_showing_date_explode[1], 'Y-m-d');
					}

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['banner_web_image'] = $fileNameWeb;
						$data['banner_mobile_image'] = $fileNameMobile;
						$data['banner_link_target'] = $this->request->getVar('form[banner_link_target]') ?? 0;
						$data['banner_start_date'] = $banner_start_date;
						$data['banner_end_date'] = $banner_end_date;
						$data['banner_created_date'] = nowDate();
					}

					if ($fileNameWebResult == NULL) {
						if ($fileNameMobileResult == NULL) {
							$result = $this->general->insertModel($this->table, $data);
							if ($result !== FALSE) {

								// Lang
								if (isNotNull($this->request->getVar('lang'))) {
									foreach ($this->request->getVar('lang') as $lang_id => $value) {
										$lang_data = [
											'banner_id' => $result,
											'lang_id' => $lang_id,
											'banner_name' => trim($value['banner_name']),
											'banner_description' => $value['banner_description'],
											'banner_link' => $value['banner_link']
										];

										$this->general->insertModel($this->tableLang, $lang_data);
									}
								}

								// Flash Data
								session()->setFlashdata('flashDataMessageSuccess', lang('AdminDesigns.result.add.bannerManagement', [$this->request->getVar('lang['.$this->defaultLangId.'][banner_name]')]));

								$ajax_message['success'] = TRUE;
								$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);

							} else {
								$ajax_message['error'] = lang('Admin.error.insert');
							}
						} else {
							$ajax_message['error'] = $fileNameMobileResult;
						}
					} else {
						$ajax_message['error'] = $fileNameWebResult;
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

	public function edit(int $banner_id) {
		$sql = $this->BannerManagementModel->bannerInfoModel($banner_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->BannerManagementModel->bannerLangModel($banner_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['banner_name'] = $row->banner_name;
					$lang_array['data']['translations'][$row->lang_id]['banner_description'] = $row->banner_description;
					$lang_array['data']['translations'][$row->lang_id]['banner_link'] = $row->banner_link;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'banner_id' => $sql->banner_id,
					'status' => $sql->status,
					'status_mobile' => $sql->status_mobile,
					'banner_type' => $sql->banner_type,
					'banner_web_image' => isNotNull($sql->banner_web_image) ? base_url($this->filePath.'/'.$sql->banner_web_image) : NULL,
					'banner_mobile_image' => isNotNull($sql->banner_mobile_image) ? base_url($this->filePath.'/'.$sql->banner_mobile_image) : NULL,
					'dates' => [
						'start' => $sql->banner_start_date != '0000-00-00' ? dateFormat($sql->banner_start_date, 'd/m/Y').' - ' : NULL,
						'end' => $sql->banner_end_date != '0000-00-00' ? dateFormat($sql->banner_end_date, 'd/m/Y') : NULL
					],
					'banner_link_target' => $sql->banner_link_target,
					'image_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$sql->banner_id)
				],
				'list' => [
					'types' => $this->types
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $banner_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->BannerManagementModel->bannerInfoModel($banner_id);
				if (isNotNull($sql)) {

					$rules1 = [];
					$rules2 = [];
					$rules3 = [];

					$rules1 = [
						'form.status' => [
							'label' => lang('AdminDesigns.bannerManagement.general.status'),
							'rules' => 'required'
						],
						'form.status_mobile' => [
							'label' => lang('Admin.mobile'),
							'rules' => 'required'
						],
						'form.banner_type' => [
							'label' => lang('AdminDesigns.bannerManagement.general.types.title'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.banner_name' => [
							'label' => lang('AdminDesigns.bannerManagement.general.name'),
							'rules' => 'required'
						]
					];

					/*****************************************************/

					// Image Upload Validation
					$fileWeb = $this->request->getFile('banner_web_image');
					if (isNotNull($fileWeb)) {
						$rules2 = [
							'banner_web_image' => [
								'label' => lang('AdminDesigns.bannerManagement.general.image.web'),
								'rules' => [
									'uploaded[banner_web_image]',
									'mime_in[banner_web_image,'.IMAGE_UPLOAD_MIME.']',
									'max_size[banner_web_image,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];
					}

					$fileMobile = $this->request->getFile('banner_mobile_image');
					if (isNotNull($fileMobile)) {
						$rules3 = [
							'banner_mobile_image' => [
								'label' => lang('AdminDesigns.bannerManagement.general.image.mobile'),
								'rules' => [
									'uploaded[banner_mobile_image]',
									'mime_in[banner_mobile_image,'.IMAGE_UPLOAD_MIME.']',
									'max_size[banner_mobile_image,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];
					}

					/*****************************************************/

					$rules = array_merge_recursive($rules1, $rules2, $rules3);

					if ($this->validate($rules)) {

						// Image Upload
						$fileNameWeb = $sql->banner_web_image;
						$fileNameWebResult = '';
						if (isNotNull($fileWeb) && $fileWeb->isValid() && !$fileWeb->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->banner_web_image);

							// Banner Type 1
							if (trim($this->request->getVar('form[banner_type]')) == BANNER_TYPE_1) {
								$fileNameWeb = slug($this->request->getVar('lang['.$this->defaultLangId.'][banner_name]')).'_'.$fileWeb->getRandomName();
								$fileNameWebResult = $this->uploadSingleFile($fileWeb,
																		 $this->filePath,
																		 $fileNameWeb,
																		 $this->designSettings->home_page_banner_web_image_width,
																		 $this->designSettings->home_page_banner_web_image_height);
							}

							// Banner Type 2
							if (trim($this->request->getVar('form[banner_type]')) == BANNER_TYPE_2) {
								$fileNameWeb = slug($this->request->getVar('lang['.$this->defaultLangId.'][banner_name]')).'_'.$fileWeb->getRandomName();
								$fileNameWebResult = $this->uploadSingleFile($fileWeb,
																		 $this->filePath,
																		 $fileNameWeb,
																		 1920,
																		 1080);
							}
						}

						// Mobile
						$fileNameMobile = $sql->banner_mobile_image;
						$fileNameMobileResult = '';
						if (isNotNull($fileMobile) && $fileMobile->isValid() && !$fileMobile->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->banner_mobile_image);

							// Banner Type 1
							if (trim($this->request->getVar('form[banner_type]')) == BANNER_TYPE_1) {
								$fileNameMobile = slug($this->request->getVar('lang['.$this->defaultLangId.'][banner_name]')).'_'.$fileMobile->getRandomName();
								$fileNameMobileResult = $this->uploadSingleFile($fileMobile,
																				$this->filePath,
																				$fileNameMobile,
																				$this->designSettings->home_page_banner_mobile_image_width,
																				$this->designSettings->home_page_banner_mobile_image_height);
							}

							// Banner Type 2
							if (trim($this->request->getVar('form[banner_type]')) == BANNER_TYPE_2) {
								$fileNameMobile = slug($this->request->getVar('lang['.$this->defaultLangId.'][banner_name]')).'_'.$fileMobile->getRandomName();
								$fileNameMobileResult = $this->uploadSingleFile($fileMobile,
																				$this->filePath,
																				$fileNameMobile,
																				777,
																				697);
							}
						}

						// Showing Date
						$banner_start_date = '';
						$banner_end_date = '';
						$banner_showing_date = trim($this->request->getVar('banner_showing_date'));
						if (isNotNull($banner_showing_date)) {
							$banner_showing_date_explode = explode(' - ', $banner_showing_date);
							$banner_start_date = dateFormat($banner_showing_date_explode[0], 'Y-m-d');
							$banner_end_date = dateFormat($banner_showing_date_explode[1], 'Y-m-d');
						}

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['banner_web_image'] = $fileNameWeb;
							$data['banner_mobile_image'] = $fileNameMobile;
							$data['banner_link_target'] = $this->request->getVar('form[banner_link_target]') ?? 0;
							$data['banner_start_date'] = $banner_start_date;
							$data['banner_end_date'] = $banner_end_date;
							$data['banner_updated_date'] = nowDate();
						}

						if ($fileNameWebResult == NULL) {
							if ($fileNameMobileResult == NULL) {
								$result = $this->general->updateModel($this->table, $data, ['banner_id' => $banner_id]);
								if ($result !== FALSE) {

									// Lang
									if (isNotNull($this->request->getVar('lang'))) {
										foreach ($this->request->getVar('lang') as $lang_id => $value) {
											$lang_data = [
												'banner_id' => $banner_id,
												'lang_id' => $lang_id,
												'banner_name' => trim($value['banner_name']),
												'banner_description' => $value['banner_description'],
												'banner_link' => $value['banner_link']
											];

											$langControlModel = $this->BannerManagementModel->bannerLangControlModel($banner_id, $lang_id);
											if (isNotNull($langControlModel)) {
												$this->general->updateModel($this->tableLang, $lang_data, ['banner_id' => $banner_id, 'lang_id' => $lang_id]);
											} else {
												$this->general->insertModel($this->tableLang, $lang_data);
											}
										}
									}

									// Flash Data
									session()->setFlashdata('flashDataMessageSuccess', lang('AdminDesigns.result.edit.bannerManagement', [$this->request->getVar('lang['.$this->defaultLangId.'][banner_name]')]));

									$ajax_message['success'] = TRUE;

									if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
										$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$banner_id);
									} else {
										$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);
									}

								} else {
									$ajax_message['error'] = lang('Admin.error.update');
								}
							} else {
								$ajax_message['error'] = $fileNameMobileResult;
							}
						} else {
							$ajax_message['error'] = $fileNameWebResult;
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

	public function delete(int $banner_id) {
		$sql = $this->BannerManagementModel->bannerInfoModel($banner_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['banner_id' => $banner_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePath, $sql->banner_web_image);
				unlinkFile($this->filePath, $sql->banner_mobile_image);

				// Lang
				$lang = $this->BannerManagementModel->bannerLangModel($banner_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['banner_id' => $row->banner_id]);
					}
				}

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

	public function removeImage(int $banner_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$data = [];
				if ($this->request->getVar('action') == 'web') {
					$data = ['banner_web_image' => ''];
				} elseif ($this->request->getVar('action') == 'mobile') {
					$data = ['banner_mobile_image' => ''];
				}

				$result = $this->general->removeDropifyImageModel($this->table, $data, ['banner_id' => $banner_id], $this->filePath);
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

	public function sort() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				foreach ($this->request->getVar('item') as $key => $row) {
					$result = $this->general->updateModel($this->table, ['banner_order' => $key], ['banner_id' => $row]);
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
}
