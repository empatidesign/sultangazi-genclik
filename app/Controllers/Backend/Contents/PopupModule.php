<?php
namespace App\Controllers\Backend\Contents;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Contents\PopupModuleModel;
use App\Models\Backend\DatatableModel;

class PopupModule extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $filePath;
	protected $PopupModuleModel;
	protected $DatatableModel;
	protected $timing;
	protected $displayField;
	protected $type;

	public function __construct() {
		$this->table = 'popup_module';
		$this->tableLang = 'popup_module_lang';
		$this->pageUrl = 'contents/popup-module';
		$this->pageUrl = ADMIN_URL_CONTENTS.'/'.ADMIN_URL_POPUP_MODULE;
		$this->filePath = FILE_PATH_POPUP;
		$this->PopupModuleModel = new PopupModuleModel();
		$this->DatatableModel = new DatatableModel();
		helper('array');

		// Timing
		$this->timing = [
			POPUP_TIMING_24_HOURS => lang('AdminContents.popup.field.timing.24Hours'),
			POPUP_TIMING_1_HOUR => lang('AdminContents.popup.field.timing.1Hour'),
			POPUP_TIMING_1_WEEK => lang('AdminContents.popup.field.timing.1Week'),
			POPUP_TIMING_1_MONTH => lang('AdminContents.popup.field.timing.1Month'),
			POPUP_TIMING_AT_EVERY_ENTRY => lang('AdminContents.popup.field.timing.atEveryEntry')
		];

		// Display Field
		$this->displayField = [
			POPUP_DISPLAY_FIELD_DESKTOP => lang('AdminContents.popup.field.display.desktop'),
			POPUP_DISPLAY_FIELD_MOBILE => lang('AdminContents.popup.field.display.mobile')
		];

		// Type
		$this->type = [
			POPUP_TYPE_HTML => [
				'name' => lang('AdminContents.popup.field.type.html'),
				'type' => 'html'
			],
			POPUP_TYPE_IMAGE => [
				'name' => lang('AdminContents.popup.field.type.image'),
				'type' => 'image'
			]
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
			$column = ['status', 'status_mobile', 'popup_module_name', 'popup_module_start_date', 'popup_module_end_date', 'popup_module_timing', 'popup_module_display_field', 'popup_module_type', NULL];
			$search = [];
			$orderBy = ['popup_module_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {

					// Display Field
					$display_field = NULL;
					if (isNotNull($row->popup_module_display_field)) {
						$display_explode = explode(',', $row->popup_module_display_field);
						foreach ($display_explode as $value1) {
							if ($value1 == POPUP_DISPLAY_FIELD_DESKTOP) {
								$display_field .= lang('AdminContents.popup.field.display.desktop').', ';
							}elseif ($value1 == POPUP_DISPLAY_FIELD_MOBILE) {
								$display_field .= lang('AdminContents.popup.field.display.mobile').', ';
							}
						}
						$display_field = reduce_multiples($display_field, ', ', TRUE);
					}

					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_status($row->status_mobile);
					$array[] = $row->popup_module_name;
					$array[] = $row->popup_module_start_date != '0000-00-00 00:00:00' ? dateFormat($row->popup_module_start_date, 'd-m-Y H:i:s') : '--';
					$array[] = $row->popup_module_end_date != '0000-00-00 00:00:00' ? dateFormat($row->popup_module_end_date, 'd-m-Y H:i:s') : '--';
					$array[] = $row->popup_module_timing ? dot_array_search($row->popup_module_timing, $this->timing) : NULL;
					$array[] = $display_field;
					$array[] = $row->popup_module_type ? dot_array_search($row->popup_module_type.'.name', $this->type) : NULL;
					$array[] = action_links($row->popup_module_id, ['edit', 'delete'], $this->pageUrl);
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
			'result' => [
				'popup_module_timing' => $this->timing,
				'popup_module_display_field' => $this->displayField,
				'popup_module_type_array' => $this->type
			]
		]);
	}

	public function insert() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'form.status' => [
						'label' => lang('AdminContents.popup.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					],
					'form.popup_module_name' => [
						'label' => lang('AdminContents.popup.general.name'),
						'rules' => 'required'
					],
					'form.popup_module_timing' => [
						'label' => lang('AdminContents.popup.general.timing'),
						'rules' => 'required'
					],
					'form.popup_module_display_field' => [
						'label' => lang('AdminContents.popup.general.displayField'),
						'rules' => 'required'
					]
				];

				/*****************************************************/

				// Image Upload Validation
				$rulesImage = [];
				if (isNotNull($this->request->getVar('lang'))) {
					foreach ($this->request->getVar('lang') as $lang_id => $value) {

						$file = $this->request->getFile('lang.'.$lang_id.'.popup_module_image');
						if (isNotNull($file)) {
							$rulesImage = [
								'lang.'.$lang_id.'.popup_module_image' => [
									'label' => lang('AdminContents.popup.general.image.title'),
									'rules' => [
										'uploaded[lang.'.$lang_id.'.popup_module_image]',
										'mime_in[lang.'.$lang_id.'.popup_module_image,'.IMAGE_UPLOAD_MIME.']',
										'max_size[lang.'.$lang_id.'.popup_module_image,'.IMAGE_UPLOAD_SIZE.']'
									]
								]
							];
						}

					}
				}

				// Mobile Image Upload
				$rulesMobileImage = [];
				if (isNotNull($this->request->getVar('lang'))) {
					foreach ($this->request->getVar('lang') as $lang_id => $value) {

						$file = $this->request->getFile('lang.'.$lang_id.'.popup_module_mobile_image');
						if (isNotNull($file)) {
							$rulesImage = [
								'lang.'.$lang_id.'.popup_module_mobile_image' => [
									'label' => lang('AdminContents.popup.general.image.mobile'),
									'rules' => [
										'uploaded[lang.'.$lang_id.'.popup_module_mobile_image]',
										'mime_in[lang.'.$lang_id.'.popup_module_mobile_image,'.IMAGE_UPLOAD_MIME.']',
										'max_size[lang.'.$lang_id.'.popup_module_mobile_image,'.IMAGE_UPLOAD_SIZE.']'
									]
								]
							];
						}

					}
				}

				$rules = array_merge_recursive($rules, $rulesImage, $rulesMobileImage);

				/*****************************************************/

				if ($this->validate($rules)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['popup_module_start_date'] = isNotNull($this->request->getVar('form[popup_module_start_date]')) ? dateFormat($this->request->getVar('form[popup_module_start_date]'), 'Y-m-d H:i:s') : '0000-00-00 00:00:00';
						$data['popup_module_end_date'] = isNotNull($this->request->getVar('form[popup_module_end_date]')) ? dateFormat($this->request->getVar('form[popup_module_end_date]'), 'Y-m-d H:i:s') : '0000-00-00 00:00:00';

						// Display Field
						$data['popup_module_display_field'] = '';
						if (isNotNull($this->request->getVar('form[popup_module_display_field]'))) {
							$display_field = NULL;
							foreach ($this->request->getVar('form[popup_module_display_field]') as $row) {
								$display_field .= $row.',';
							}

							$data['popup_module_display_field'] = reduce_multiples($display_field, ',', TRUE);
						}
					}

					$result = $this->general->insertModel($this->table, $data);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {

								// Image Upload
								$fileName = '';
								$file = $this->request->getFile('lang.'.$lang_id.'.popup_module_image');
								if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
									$fileName = slug($this->request->getVar('form[popup_module_name]')).'_'.$file->getRandomName();
									$this->uploadSingleFile($file,
															$this->filePath,
															$fileName,
															$this->designSettings->popup_image_width,
															$this->designSettings->popup_image_height);
								}

								// Mobile Image Upload
								$fileMobileName = '';
								$fileMobile = $this->request->getFile('lang.'.$lang_id.'.popup_module_mobile_image');
								if (isNotNull($fileMobile) && $fileMobile->isValid() && !$fileMobile->hasMoved()) {
									$fileMobileName = slug($this->request->getVar('form[popup_module_name]')).'_'.$fileMobile->getRandomName();
									$this->uploadSingleFile($fileMobile,
															$this->filePath,
															$fileMobileName,
															$this->designSettings->popup_mobile_image_width,
															$this->designSettings->popup_mobile_image_height);
								}

								$lang_data = [
									'popup_module_id' => $result,
									'lang_id' => $lang_id,
									'popup_module_html' => $value['popup_module_html'],
									'popup_module_image' => $fileName,
									'popup_module_mobile_image' => $fileMobileName,
									'popup_module_image_link' => $value['popup_module_image_link']
								];

								$this->general->insertModel($this->tableLang, $lang_data);
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.add.popupModule', [$this->request->getVar('form[popup_module_name]')]));

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

	public function edit(int $popup_module_id) {
		$sql = $this->PopupModuleModel->popupInfoModel($popup_module_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->PopupModuleModel->popupLangModel($popup_module_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['popup_module_html'] = $row->popup_module_html;
					$lang_array['data']['translations'][$row->lang_id]['popup_module_image'] = $row->popup_module_image != NULL ? base_url($this->filePath.'/'.$row->popup_module_image) : NULL;
					$lang_array['data']['translations'][$row->lang_id]['popup_module_mobile_image'] = $row->popup_module_mobile_image != NULL ? base_url($this->filePath.'/'.$row->popup_module_mobile_image) : NULL;
					$lang_array['data']['translations'][$row->lang_id]['popup_module_image_link'] = $row->popup_module_image_link;
					$lang_array['data']['translations'][$row->lang_id]['image_remove'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$row->popup_module_lang_id);
					$lang_array['data']['translations'][$row->lang_id]['mobile_image_remove'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-mobile-image/'.$row->popup_module_lang_id);
				}
			}

			// Timing
			$timing = [];
			foreach ($this->timing as $key => $row) {
				$timing[] = [
					'id' => $key,
					'name' => $row,
					'selected' => $sql->popup_module_timing == $key ? 'selected' : NULL
				];
			}

			// Display Field
			$display_field = [];
			foreach ($this->displayField as $key => $row) {
				$selected = NULL;
				$explode = explode(',', $sql->popup_module_display_field);
				if (in_array($key, $explode)) {
					$selected = 'selected';
				}

				$display_field[] = [
					'id' => $key,
					'name' => $row,
					'selected' => $selected
				];
			}

			// Type
			$type = [];
			$type_selected = [];
			foreach ($this->type as $key => $row) {
				$type[] = [
					'id' => $key,
					'name' => $row['name'],
					'type' => $row['type'],
					'selected' => $sql->popup_module_type == $key ? 'selected' : NULL
				];

				$type_selected['data'][$row['type']]['id'] = $key;
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'popup_module_id' => $sql->popup_module_id,
					'status' => $sql->status,
					'status_mobile' => $sql->status_mobile,
					'popup_module_name' => $sql->popup_module_name,
					'popup_module_start_date' => $sql->popup_module_start_date != '0000-00-00 00:00:00' ? dateFormat($sql->popup_module_start_date, 'd/m/Y H:i') : NULL,
					'popup_module_end_date' => $sql->popup_module_end_date != '0000-00-00 00:00:00' ? dateFormat($sql->popup_module_end_date, 'd/m/Y H:i') : NULL,
					'popup_module_timing' => $timing,
					'popup_module_display_field' => $display_field,
					'popup_module_type' => $sql->popup_module_type,
					'popup_module_type_array' => $type,
					'popup_module_type_selected' => $type_selected
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $popup_module_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'form.status' => [
						'label' => lang('AdminContents.popup.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					],
					'form.popup_module_name' => [
						'label' => lang('AdminContents.popup.general.name'),
						'rules' => 'required'
					],
					'form.popup_module_timing' => [
						'label' => lang('AdminContents.popup.general.timing'),
						'rules' => 'required'
					],
					'form.popup_module_display_field' => [
						'label' => lang('AdminContents.popup.general.displayField'),
						'rules' => 'required'
					]
				];

				/*****************************************************/

				// Image Upload Validation
				$rulesImage = [];
				if (isNotNull($this->request->getVar('lang'))) {
					foreach ($this->request->getVar('lang') as $lang_id => $value) {

						$file = $this->request->getFile('lang.'.$lang_id.'.popup_module_image');
						if (isNotNull($file)) {
							$rulesImage = [
								'lang.'.$lang_id.'.popup_module_image' => [
									'label' => lang('AdminContents.popup.general.image.title'),
									'rules' => [
										'uploaded[lang.'.$lang_id.'.popup_module_image]',
										'mime_in[lang.'.$lang_id.'.popup_module_image,'.IMAGE_UPLOAD_MIME.']',
										'max_size[lang.'.$lang_id.'.popup_module_image,'.IMAGE_UPLOAD_SIZE.']'
									]
								]
							];
						}

					}
				}

				// Mobile Image Upload
				$rulesMobileImage = [];
				if (isNotNull($this->request->getVar('lang'))) {
					foreach ($this->request->getVar('lang') as $lang_id => $value) {

						$file = $this->request->getFile('lang.'.$lang_id.'.popup_module_mobile_image');
						if (isNotNull($file)) {
							$rulesImage = [
								'lang.'.$lang_id.'.popup_module_mobile_image' => [
									'label' => lang('AdminContents.popup.general.image.mobile'),
									'rules' => [
										'uploaded[lang.'.$lang_id.'.popup_module_mobile_image]',
										'mime_in[lang.'.$lang_id.'.popup_module_mobile_image,'.IMAGE_UPLOAD_MIME.']',
										'max_size[lang.'.$lang_id.'.popup_module_mobile_image,'.IMAGE_UPLOAD_SIZE.']'
									]
								]
							];
						}

					}
				}

				$rules = array_merge_recursive($rules, $rulesImage, $rulesMobileImage);

				/*****************************************************/

				if ($this->validate($rules)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['popup_module_start_date'] = isNotNull($this->request->getVar('form[popup_module_start_date]')) ? dateFormat($this->request->getVar('form[popup_module_start_date]'), 'Y-m-d H:i:s') : '0000-00-00 00:00:00';
						$data['popup_module_end_date'] = isNotNull($this->request->getVar('form[popup_module_end_date]')) ? dateFormat($this->request->getVar('form[popup_module_end_date]'), 'Y-m-d H:i:s') : '0000-00-00 00:00:00';

						// Display Field
						$data['popup_module_display_field'] = '';
						if (isNotNull($this->request->getVar('form[popup_module_display_field]'))) {
							$display_field = NULL;
							foreach ($this->request->getVar('form[popup_module_display_field]') as $row) {
								$display_field .= $row.',';
							}

							$data['popup_module_display_field'] = reduce_multiples($display_field, ',', TRUE);
						}
					}

					$result = $this->general->updateModel($this->table, $data, ['popup_module_id' => $popup_module_id]);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {

								// Image Upload
								$langControlModel = $this->PopupModuleModel->popupLangControlModel($popup_module_id, $lang_id);
								if (isNotNull($langControlModel)) {

									// Image Upload
									$fileName = $langControlModel->popup_module_image;
									$file = $this->request->getFile('lang.'.$lang_id.'.popup_module_image');
									if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
										// Unlink
										unlinkFile($this->filePath, $langControlModel->popup_module_image);

										$fileName = slug($this->request->getVar('form[popup_module_name]')).'_'.$file->getRandomName();
										$this->uploadSingleFile($file,
																$this->filePath,
																$fileName,
																$this->designSettings->popup_image_width,
																$this->designSettings->popup_image_height);
									}

									// Mobile Image Upload
									$fileMobileName = $langControlModel->popup_module_mobile_image;
									$fileMobile = $this->request->getFile('lang.'.$lang_id.'.popup_module_mobile_image');
									if (isNotNull($fileMobile) && $fileMobile->isValid() && !$fileMobile->hasMoved()) {
										// Unlink
										unlinkFile($this->filePath, $langControlModel->popup_module_mobile_image);

										$fileMobileName = slug($this->request->getVar('form[popup_module_name]')).'_'.$fileMobile->getRandomName();
										$this->uploadSingleFile($fileMobile,
																$this->filePath,
																$fileMobileName,
																$this->designSettings->popup_mobile_image_width,
																$this->designSettings->popup_mobile_image_height);
									}

								}

								$lang_data = [
									'popup_module_id' => $popup_module_id,
									'lang_id' => $lang_id,
									'popup_module_html' => $value['popup_module_html'],
									'popup_module_image' => $fileName,
									'popup_module_mobile_image' => $fileMobileName,
									'popup_module_image_link' => $value['popup_module_image_link']
								];

								$langControlModel = $this->PopupModuleModel->popupLangControlModel($popup_module_id, $lang_id);
								if (isNotNull($langControlModel)) {
									$this->general->updateModel($this->tableLang, $lang_data, ['popup_module_id' => $popup_module_id, 'lang_id' => $lang_id]);
								} else {
									$this->general->insertModel($this->tableLang, $lang_data);
								}
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.edit.popupModule', [$this->request->getVar('form[popup_module_name]')]));

						$ajax_message['success'] = TRUE;

						if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
							$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$popup_module_id);
						} else {
							$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);
						}

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

	public function delete(int $popup_module_id) {
		$sql = $this->PopupModuleModel->popupInfoModel($popup_module_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['popup_module_id' => $popup_module_id]);
			if ($delete) {

				// Lang
				$lang = $this->PopupModuleModel->popupLangModel($popup_module_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						// Unlink
						unlinkFile($this->filePath, $row->popup_module_image);

						$this->general->deleteModel($this->tableLang, ['popup_module_id' => $row->popup_module_id]);
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

	public function removeImage(int $popup_module_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->tableLang, ['popup_module_image' => ''], ['popup_module_lang_id' => $popup_module_id], $this->filePath);
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

	public function removeMobileImage(int $popup_module_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->tableLang, ['popup_module_mobile_image' => ''], ['popup_module_lang_id' => $popup_module_id], $this->filePath);
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
