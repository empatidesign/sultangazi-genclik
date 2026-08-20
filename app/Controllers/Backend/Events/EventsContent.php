<?php
namespace App\Controllers\Backend\Events;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Events\EventsContentModel;
use App\Models\Backend\DatatableModel;
// use App\Libraries\PushNotifications;

class EventsContent extends BaseController {

	protected $table;
	protected $tableLang;
	protected $tableParagraphs;
	protected $tableParagraphsLang;
	protected $pageUrl;
	protected $filePathThumb;
	protected $filePathBig;
	protected $EventsContentModel;
	protected $DatatableModel;
	// protected $PushNotifications;

	public function __construct() {
		$this->table = 'events';
		$this->tableLang = 'events_lang';
		$this->tableParagraphs = 'events_paragraphs';
		$this->tableParagraphsLang = 'events_paragraphs_lang';
		$this->pageUrl = ADMIN_URL_EVENTS.'/'.ADMIN_URL_EVENTS_CONTENT;
		$this->filePathThumb = FILE_PATH_EVENTS_THUMB;
		$this->filePathBig = FILE_PATH_EVENTS_BIG;
		$this->EventsContentModel = new EventsContentModel();
		$this->DatatableModel = new DatatableModel();
		// $this->PushNotifications = new PushNotifications();
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
			$column = ['status', 'status_mobile', 'push_notification', NULL, 'event_name', 'event_date', 'event_hour', 'event_created_date', NULL];
			$search = [];
			$orderBy = ['event_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_status($row->status_mobile);
					$array[] = set_status($row->push_notification);
					$array[] = set_image($this->filePathThumb.'/'.$row->event_image, 100);
					$array[] = character_limiter($row->event_name, 50);
					$array[] = $row->event_date != '0000-00-00' ? dateFormat($row->event_date, 'd/m/Y') : '--';
					$array[] = $row->event_hour != '00:00:00' ? deleteSeconds($row->event_hour) : '--';
					$array[] = dateFormat($row->event_created_date, 'd-m-Y H:i:s');
					$array[] = action_links($row->event_id, ['edit', 'delete'], $this->pageUrl);
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
		$last_id = $this->general->lastIDModel($this->table, 'event_id');

		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_name' => 'add',
			'page_url' => $this->pageUrl,
			'last_id' => $last_id,
			'paragraphs' => [
				'datatable_url' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/paragraphs/datatable/'.$last_id)
			],
			'list' => [
				'category' => $this->EventsContentModel->eventCategoryListModel($this->defaultLangId),
				'hours' => hoursList()
			]
		]);
	}

	public function insert() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules1 = [
					'form.status' => [
						'label' => lang('AdminEvents.contents.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					],
					'form.event_category_id' => [
						'label' => lang('AdminEvents.contents.general.category'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.event_name' => [
						'label' => lang('AdminEvents.contents.general.name'),
						'rules' => 'required'
					]
				];

				// Event Date
				$rules2 = [];
				$event_date = trim($this->request->getVar('form[event_date]'));
				if (isNotNull($event_date)) {
					$rules2 = [
						'form.event_date' => [
							'label' => lang('AdminEvents.contents.general.date'),
							'rules' => 'required|valid_date[d/m/Y]'
						]
					];
				}

				// Image Upload Validation
				$rules3 = [];
				$file = $this->request->getFile('event_image');
				if (isNotNull($file)) {
					$rules3 = [
						'event_image' => [
							'label' => lang('AdminEvents.contents.general.image'),
							'rules' => [
								'uploaded[event_image]',
								'mime_in[event_image,'.IMAGE_UPLOAD_MIME.']',
								'max_size[event_image,'.IMAGE_UPLOAD_SIZE.']'
							]
						]
					];
				}

				/*****************************************************/

				$rules = array_merge_recursive($rules1, $rules2, $rules3);

				if ($this->validate($rules)) {

					// // Api
					// if (isNotNull($this->request->getVar('form[push_notification]'))) {
					// 	$this->PushNotifications->index(trim($this->request->getVar('lang['.$this->defaultLangId.'][event_mobile_name]')), trim($this->request->getVar('lang['.$this->defaultLangId.'][event_mobile_description]')));
					// }

					/**************************************************/

					// Image Upload
					$fileName = '';
					$fileThumbResult = '';
					$fileBigResult = '';
					if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
						$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][event_name]')).'_'.$file->getRandomName();

						// Thumb
						$fileThumbResult = $this->uploadSingleFile($file,
												$this->filePathThumb,
												$fileName,
												$this->designSettings->events_thumb_image_width,
												$this->designSettings->events_thumb_image_height);

						// Big
						$fileBigResult = $this->uploadSingleFile($file,
												$this->filePathBig,
												$fileName,
												$this->designSettings->events_big_image_width,
												$this->designSettings->events_big_image_height);
					}

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['push_notification'] = isNotNull($this->request->getVar('form[push_notification]')) ? $this->request->getVar('form[push_notification]') : FALSE;
						$data['event_date'] = isNotNull($event_date) ? dateFormat($event_date, 'Y-m-d') : '0000-00-00';
						$data['event_image'] = $fileName;
						$data['event_location_address'] = trim($this->request->getVar('form[event_location_address]'));
						$data['event_location_telephone'] = trim($this->request->getVar('form[event_location_telephone]'));
						$data['event_location_map'] = trim($this->request->getVar('form[event_location_map]'));
						$data['event_lat_coordinate'] = trim($this->request->getVar('form[event_lat_coordinate]'));
						$data['event_long_coordinate'] = trim($this->request->getVar('form[event_long_coordinate]'));
						$data['event_created_date'] = nowDate();
					}

					if ($fileThumbResult == NULL) {
						if ($fileBigResult == NULL) {

							$result = $this->general->insertModel($this->table, $data);
							if ($result !== FALSE) {

								// Lang
								if (isNotNull($this->request->getVar('lang'))) {
									foreach ($this->request->getVar('lang') as $lang_id => $value) {

										// Slug
										if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][event_slug]'))) {
											$slug = slug($value['event_slug']);
										} else {
											$slug = isNotNull($value['event_name']) ? slug($value['event_name']) : $this->request->getVar('lang['.$this->defaultLangId.'][event_name]');
										}

										$lang_data = [
											'event_id' => $result,
											'lang_id' => $lang_id,
											'event_name' => isNotNull($value['event_name']) ? upper($value['event_name']) : upper($this->request->getVar('lang['.$this->defaultLangId.'][event_name]')),
											'event_age_group' => isNotNull($value['event_age_group']) ? $value['event_age_group'] : '',
											'event_quota' => isNotNull($value['event_quota']) ? $value['event_quota'] : '',
											'event_description' => $value['event_description'],
											'event_meta_title' => $value['event_meta_title'],
											'event_meta_keywords' => $value['event_meta_keywords'],
											'event_meta_description' => $value['event_meta_description'],
											'event_slug' => $slug,
											'event_mobile_name' => $value['event_mobile_name'],
											'event_mobile_description' => $value['event_mobile_description']
										];

										$this->general->insertModel($this->tableLang, $lang_data);
									}
								}

								// Flash Data
								session()->setFlashdata('flashDataMessageSuccess', lang('AdminEvents.result.add.contents', [$this->request->getVar('lang['.$this->defaultLangId.'][event_name]')]));

								$ajax_message['success'] = TRUE;
								$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);

							} else {
								$ajax_message['error'] = lang('Admin.error.insert');
							}

						} else {
							$ajax_message['error'] = $fileBigResult;
						}
					} else {
						$ajax_message['error'] = $fileThumbResult;
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

	public function edit(int $event_id) {
		$sql = $this->EventsContentModel->eventInfoModel($event_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->EventsContentModel->eventLangModel($event_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['event_name'] = $row->event_name;
					$lang_array['data']['translations'][$row->lang_id]['event_age_group'] = $row->event_age_group;
					$lang_array['data']['translations'][$row->lang_id]['event_quota'] = $row->event_quota;
					$lang_array['data']['translations'][$row->lang_id]['event_description'] = $row->event_description;
					$lang_array['data']['translations'][$row->lang_id]['event_meta_title'] = $row->event_meta_title;
					$lang_array['data']['translations'][$row->lang_id]['event_meta_keywords'] = $row->event_meta_keywords;
					$lang_array['data']['translations'][$row->lang_id]['event_meta_description'] = $row->event_meta_description;
					$lang_array['data']['translations'][$row->lang_id]['event_slug'] = $row->event_slug;
					$lang_array['data']['translations'][$row->lang_id]['event_mobile_name'] = $row->event_mobile_name;
					$lang_array['data']['translations'][$row->lang_id]['event_mobile_description'] = $row->event_mobile_description;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'paragraphs' => [
					'datatable_url' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/paragraphs/datatable/'.$sql->event_id)
				],
				'list' => [
					'category' => $this->EventsContentModel->eventCategoryListModel($this->defaultLangId),
					'hours' => hoursList()
				],
				'result' => [
					'event_id' => $sql->event_id,
					'status' => $sql->status,
					'status_mobile' => $sql->status_mobile,
					'push_notification' => $sql->push_notification,
					'event_category_id' => $sql->event_category_id,
					'event_date' => $sql->event_date != '0000-00-00' ? dateFormat($sql->event_date, 'd/m/Y') : NULL,
					'event_hour' => $sql->event_hour != '00:00:00' ? deleteSeconds($sql->event_hour) : NULL,
					'event_location' => $sql->event_location,
					'event_location_address' => $sql->event_location_address,
					'event_location_telephone' => $sql->event_location_telephone,
					'event_location_map' => $sql->event_location_map,
					'event_lat_coordinate' => $sql->event_lat_coordinate,
					'event_long_coordinate' => $sql->event_long_coordinate,
					'image' => $sql->event_image != NULL ? base_url($this->filePathThumb.'/'.$sql->event_image) : NULL,
					'image_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$sql->event_id)
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $event_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->EventsContentModel->eventInfoModel($event_id);
				if (isNotNull($sql)) {

					$rules1 = [
						'form.status' => [
							'label' => lang('AdminEvents.contents.general.status'),
							'rules' => 'required'
						],
						'form.status_mobile' => [
							'label' => lang('Admin.mobile'),
							'rules' => 'required'
						],
						'form.event_category_id' => [
							'label' => lang('AdminEvents.contents.general.category'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.event_name' => [
							'label' => lang('AdminEvents.contents.general.name'),
							'rules' => 'required'
						]
					];

					// Event Date
					$rules2 = [];
					$event_date = trim($this->request->getVar('form[event_date]'));
					if (isNotNull($event_date)) {
						$rules2 = [
							'form.event_date' => [
								'label' => lang('AdminEvents.contents.general.date'),
								'rules' => 'required|valid_date[d/m/Y]'
							]
						];
					}

					// Image Upload Validation
					$rules3 = [];
					$file = $this->request->getFile('event_image');
					if (isNotNull($file)) {
						$rules3 = [
							'event_image' => [
								'label' => lang('AdminEvents.contents.general.image'),
								'rules' => [
									'uploaded[event_image]',
									'mime_in[event_image,'.IMAGE_UPLOAD_MIME.']',
									'max_size[event_image,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];
					}

					/*****************************************************/

					$rules = array_merge_recursive($rules1, $rules2, $rules3);

					if ($this->validate($rules)) {

						// Api
						// if (isNotNull($thnis->request->getVar('form[push_notification]'))) {
						// 	$this->PushNotifications->index(trim($this->request->getVar('lang['.$this->defaultLangId.'][event_mobile_name]')), trim($this->request->getVar('lang['.$this->defaultLangId.'][event_mobile_description]')));
						// }

						/**************************************************/

						// Image Upload
						$fileName = $sql->event_image;
						$fileThumbResult = '';
						$fileBigResult = '';
						if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
							$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][event_name]')).'_'.$file->getRandomName();

							// Unlink
							unlinkFile($this->filePathThumb, $sql->event_image);
							unlinkFile($this->filePathBig, $sql->event_image);

							// Thumb
							$fileThumbResult = $this->uploadSingleFile($file,
													$this->filePathThumb,
													$fileName,
													$this->designSettings->events_thumb_image_width,
													$this->designSettings->events_thumb_image_height);

							// Big
							$fileBigResult = $this->uploadSingleFile($file,
													$this->filePathBig,
													$fileName,
													$this->designSettings->events_big_image_width,
													$this->designSettings->events_big_image_height);
						}

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['push_notification'] = isNotNull($this->request->getVar('form[push_notification]')) ? $this->request->getVar('form[push_notification]') : FALSE;
							$data['event_date'] = isNotNull($event_date) ? dateFormat($event_date, 'Y-m-d') : '0000-00-00';
							$data['event_image'] = $fileName;
							$data['event_location_address'] = trim($this->request->getVar('form[event_location_address]'));
							$data['event_location_telephone'] = trim($this->request->getVar('form[event_location_telephone]'));
							$data['event_location_map'] = trim($this->request->getVar('form[event_location_map]'));
							$data['event_lat_coordinate'] = trim($this->request->getVar('form[event_lat_coordinate]'));
							$data['event_long_coordinate'] = trim($this->request->getVar('form[event_long_coordinate]'));
							$data['event_updated_date'] = nowDate();
						}

						if ($fileThumbResult == NULL) {
							if ($fileBigResult == NULL) {

								$result = $this->general->updateModel($this->table, $data, ['event_id' => $event_id]);
								if ($result !== FALSE) {

									// Lang
									if (isNotNull($this->request->getVar('lang'))) {
										foreach ($this->request->getVar('lang') as $lang_id => $value) {

											// Slug
											if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][event_slug]'))) {
												$slug = slug($value['event_slug']);
											} else {
												$slug = slug($value['event_name']);
											}

											$lang_data = [
												'event_id' => $event_id,
												'lang_id' => $lang_id,
												'event_name' => upper($value['event_name']),
												'event_age_group' => isNotNull($value['event_age_group']) ? $value['event_age_group'] : '',
												'event_quota' => isNotNull($value['event_quota']) ? $value['event_quota'] : '',
												'event_description' => $value['event_description'],
												'event_meta_title' => $value['event_meta_title'],
												'event_meta_keywords' => $value['event_meta_keywords'],
												'event_meta_description' => $value['event_meta_description'],
												'event_slug' => $slug,
												'event_mobile_name' => $value['event_mobile_name'],
												'event_mobile_description' => $value['event_mobile_description']
											];

											$langControlModel = $this->EventsContentModel->eventLangControlModel($event_id, $lang_id);
											if (isNotNull($langControlModel)) {
												$this->general->updateModel($this->tableLang, $lang_data, ['event_id' => $event_id, 'lang_id' => $lang_id]);
											} else {
												$this->general->insertModel($this->tableLang, $lang_data);
											}
										}
									}

									// Flash Data
									session()->setFlashdata('flashDataMessageSuccess', lang('AdminEvents.result.edit.contents', [$this->request->getVar('lang['.$this->defaultLangId.'][event_name]')]));

									$ajax_message['success'] = TRUE;

									if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
										$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$event_id);
									} else {
										$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);
									}

								} else {
									$ajax_message['error'] = lang('Admin.error.update');
								}

							} else {
								$ajax_message['error'] = $fileBigResult;
							}
						} else {
							$ajax_message['error'] = $fileThumbResult;
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

	public function delete(int $event_id) {
		$sql = $this->EventsContentModel->eventInfoModel($event_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['event_id' => $event_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePathThumb, $sql->event_image);
				unlinkFile($this->filePathBig, $sql->event_image);

				// Lang
				$lang = $this->EventsContentModel->eventLangModel($event_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['event_id' => $row->event_id]);
					}
				}

				// Paragraphs
				$paragraphs = $this->EventsContentModel->eventParagraphListModel($row->event_id);
				if (isNotNull($paragraphs)) {
					foreach ($paragraphs as $row) {
						$paragraph_delete = $this->general->deleteModel($this->tableParagraphs, ['event_id' => $row->event_id]);
						if ($paragraph_delete) {

							// Unlink
							unlinkFile($this->filePathBig, $row->event_paragraph_image);

							// Paragraphs Lang
							$paragraphs_lang = $this->EventsContentModel->eventParagraphLangModel($row->event_paragraph_id);
							if (isNotNull($paragraphs_lang)) {
								foreach ($paragraphs_lang as $value) {
									$this->general->deleteModel($this->tableParagraphsLang, ['event_paragraph_lang_id' => $value->event_paragraph_lang_id]);
								}
							}

						}
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

	public function removeImage(int $event_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->EventsContentModel->eventInfoModel($event_id);
				if (isNotNull($sql)) {

					$result = $this->general->removeDropifyImageModel($this->table, ['event_image' => ''], ['event_id' => $event_id], $this->filePathThumb);
					if ($result == TRUE) {

						// Unlink
						unlinkFile($this->filePathBig, $sql->event_image);

						$ajax_message['success'] = TRUE;

					} else {
						$ajax_message['error'] = lang('Admin.error.description');
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

	/*****************************************************/

	public function paragraphDatatable(int $event_id) {
		if ($this->request->isAJAX()) {
			$column = ['event_paragraph_id', NULL, 'event_paragraph_name', 'event_paragraph_created_date', NULL];
			$search = [];
			$orderBy = ['event_paragraph_id' => 'ASC'];
			$where = ['event_id' => $event_id];

			$list = $this->DatatableModel->GetDatatables($this->tableParagraphs, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = $row->event_paragraph_id;
					$array[] = set_image($this->filePathBig.'/'.$row->event_paragraph_image, 100);
					$array[] = character_limiter($row->event_paragraph_name, 100);
					$array[] = dateFormat($row->event_paragraph_created_date, 'd/m/Y H:i:s');
					$array[] = action_links($row->event_paragraph_id, ['modal-edit', 'delete'], $this->pageUrl.'/paragraphs', NULL, NULL, ['modal', 'events_paragraph_edit']);
					$data[] = $array;
				}
			}

			$output = [
				'draw' => $this->request->getVar('draw'),
				'recordsTotal' => $this->DatatableModel->GetDatatables($this->tableParagraphs, $column, $search, $orderBy, $where, 'getNumRows'),
				'recordsFiltered' => $this->DatatableModel->GetDatatables($this->tableParagraphs, $column, $search, $orderBy, $where, 'countAllResults'),
				'data' => $data
			];

			return $this->response->setJSON($output);
		}
	}

	public function paragraphInsert(int $event_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'lang.'.$this->defaultLangId.'.event_paragraph_name' => [
						'label' => lang('AdminEvents.contents.paragraphs.name'),
						'rules' => 'required'
					]
				];

				if ($this->validate($rules)) {

					// Image Upload
					$fileName = '';
					$fileNameResult = '';
					$file = $this->request->getFile('event_paragraph_image');
					if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {

						if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][event_paragraph_name]'))) {
							$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][event_paragraph_name]')).'_'.$file->getRandomName();
						} else {
							$fileName = $file->getRandomName();
						}

						$fileNameResult = $this->uploadSingleFile($file, $this->filePathBig, $fileName);
					}

					$data = [
						'event_id' => $event_id,
						'event_paragraph_image' => $fileName,
						'event_paragraph_created_date' => nowDate()
					];

					if ($fileNameResult == NULL) {
						$result = $this->general->insertModel($this->tableParagraphs, $data);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'event_paragraph_id' => $result,
										'lang_id' => $lang_id,
										'event_paragraph_name' => $value['event_paragraph_name'],
										'event_paragraph_description' => $value['event_paragraph_description']
									];

									$this->general->insertModel($this->tableParagraphsLang, $lang_data);
								}
							}

							$ajax_message['success'] = TRUE;
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

	public function paragraphUpdate(int $event_paragraph_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->EventsContentModel->eventParagraphInfoModel($event_paragraph_id);
				if (isNotNull($sql)) {

					$rules = [
						'lang.'.$this->defaultLangId.'.event_paragraph_name' => [
							'label' => lang('AdminEvents.contents.paragraphs.name'),
							'rules' => 'required'
						]
					];

					if ($this->validate($rules)) {

						// Image Upload
						$fileName = $sql->event_paragraph_image;
						$fileNameResult = '';
						$file = $this->request->getFile('event_paragraph_image');
						if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
							// Unlink
							unlinkFile($this->filePathBig, $sql->event_paragraph_image);

							if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][event_paragraph_name]'))) {
								$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][event_paragraph_name]')).'_'.$file->getRandomName();
							} else {
								$fileName = $file->getRandomName();
							}

							$fileNameResult = $this->uploadSingleFile($file, $this->filePathBig, $fileName);
						}

						$data = [
							'event_paragraph_image' => $fileName,
							'event_paragraph_updated_date' => nowDate()
						];

						if ($fileNameResult == NULL) {
							$result = $this->general->updateModel($this->tableParagraphs, $data, ['event_paragraph_id' => $event_paragraph_id]);
							if ($result !== FALSE) {

								// Lang
								if (isNotNull($this->request->getVar('lang'))) {
									foreach ($this->request->getVar('lang') as $lang_id => $value) {
										$lang_data = [
											'event_paragraph_id' => $event_paragraph_id,
											'lang_id' => $lang_id,
											'event_paragraph_name' => $value['event_paragraph_name'],
											'event_paragraph_description' => $value['event_paragraph_description']
										];

										$langControlModel = $this->EventsContentModel->eventParagraphLangControlModel($event_paragraph_id, $lang_id);
										if (isNotNull($langControlModel)) {
											$this->general->updateModel($this->tableParagraphsLang, $lang_data, ['event_paragraph_id' => $event_paragraph_id, 'lang_id' => $lang_id]);
										} else {
											$this->general->insertModel($this->tableParagraphsLang, $lang_data);
										}
									}
								}

								$ajax_message['success'] = TRUE;

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

	public function paragraphDelete(int $event_paragraph_id) {
		$sql = $this->EventsContentModel->eventParagraphInfoModel($event_paragraph_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->tableParagraphs, ['event_paragraph_id' => $event_paragraph_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePathBig, $sql->event_paragraph_image);

				// Lang
				$lang = $this->EventsContentModel->eventParagraphLangModel($event_paragraph_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableParagraphsLang, ['event_paragraph_id' => $row->event_paragraph_id]);
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

	public function paragraphRemoveImage(int $event_paragraph_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->tableParagraphs, ['event_paragraph_image' => ''], ['event_paragraph_id' => $event_paragraph_id], $this->filePathBig);
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
