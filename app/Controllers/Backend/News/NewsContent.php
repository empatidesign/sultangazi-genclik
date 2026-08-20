<?php
namespace App\Controllers\Backend\News;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\News\NewsContentModel;
use App\Models\Backend\DatatableModel;

class NewsContent extends BaseController {

	protected $table;
	protected $tableLang;
	protected $tableParagraphs;
	protected $tableParagraphsLang;
	protected $tableImages;
	protected $pageUrl;
	protected $filePath;
	protected $filePathGallery;
	protected $NewsContentModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'news';
		$this->tableLang = 'news_lang';
		$this->tableParagraphs = 'news_paragraphs';
		$this->tableParagraphsLang = 'news_paragraphs_lang';
		$this->tableImages = 'news_image';
		$this->pageUrl = ADMIN_URL_NEWS.'/'.ADMIN_URL_NEWS_CONTENT;
		$this->filePath = FILE_PATH_NEWS;
		$this->filePathGallery = FILE_PATH_NEWS_GALLERY;
		$this->NewsContentModel = new NewsContentModel();
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
			$column = ['status', 'status_genclik', 'push_notification', NULL, 'news_name', 'news_created_date', NULL];
			$search = [];
			$orderBy = ['news_id' => 'DESC'];
			$where = [];

			$list1 = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$list2 = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult', appDb: true);
			$list = array_merge($list1, $list2);
			
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_status($row->status_genclik);
					$array[] = set_status($row->push_notification);
					$array[] = set_image($this->filePath.'/'.$row->news_image, 200, app: true);
					$array[] = character_limiter($row->news_name, 50);
					$array[] = dateFormat($row->news_created_date, 'd-m-Y H:i:s');
					$array[] = action_links($row->news_id, ['edit', 'delete'], $this->pageUrl);
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
		$last_id = $this->general->lastIDModel($this->table, 'news_id');

		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_name' => 'add',
			'page_url' => $this->pageUrl,
			'last_id' => $last_id,
			'list' => [
				'directorates' => $this->NewsContentModel->directoratesListModel($this->defaultLangId)
			],
			'paragraphs' => [
				'datatable_url' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/paragraphs/datatable/'.$last_id)
			]
		]);
	}

	public function insert() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'form.status' => [
						'label' => lang('AdminNews.news.general.status'),
						'rules' => 'required'
					],
					'form.status_genclik' => [
						'label' => lang('Admin.genclik'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.news_name' => [
						'label' => lang('AdminNews.news.general.name'),
						'rules' => 'required'
					]
				];

				/*****************************************************/

				// Image Upload Validation
				$file = $this->request->getFile('news_image');
				if (isNotNull($file)) {
					$rulesImage = [
						'news_image' => [
							'label' => lang('AdminNews.news.general.image.title'),
							'rules' => [
								'uploaded[news_image]',
								'mime_in[news_image,'.IMAGE_UPLOAD_MIME.']',
								'max_size[news_image,'.IMAGE_UPLOAD_SIZE.']'
							]
						]
					];

					$rules = array_merge($rules, $rulesImage);
				}

				/*****************************************************/

				if ($this->validate($rules)) {

					// Image Upload
					$fileName = '';
					if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
						$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][news_name]')).'_'.$file->getRandomName();
						$file->move($this->filePath, $fileName);
					}

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['push_notification'] = isNotNull($this->request->getVar('form[push_notification]')) ? $this->request->getVar('form[push_notification]') : FALSE;
						$data['news_image'] = $fileName;

						// Directorates
						$data['directorates_id'] = '';
						if (isNotNull($this->request->getVar('form[directorates_id]'))) {
							$directorates = NULL;
							foreach ($this->request->getVar('form[directorates_id]') as $row) {
								$directorates .= $row.',';
							}

							$data['directorates_id'] = reduce_multiples($directorates, ',', TRUE);
						}

						$data['news_created_date'] = nowDate();
					}

					$result = $this->general->insertModel($this->table, $data);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {

								// Slug
								if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][news_slug]'))) {
									$slug = slug($value['news_slug']);
								} else {
									$slug = isNotNull($value['news_name']) ? slug($value['news_name']) : $this->request->getVar('lang['.$this->defaultLangId.'][news_name]');
								}

								$lang_data = [
									'news_id' => $result,
									'lang_id' => $lang_id,
									'news_name' => isNotNull($value['news_name']) ? $value['news_name'] : $this->request->getVar('lang['.$this->defaultLangId.'][news_name]'),
									'news_description' => $value['news_description'],
									'news_meta_title' => $value['news_meta_title'],
									'news_meta_keywords' => $value['news_meta_keywords'],
									'news_meta_description' => $value['news_meta_description'],
									'news_slug' => $slug,
									'news_mobile_name' => $value['news_mobile_name'],
									'news_mobile_description' => $value['news_mobile_description']
								];

								$this->general->insertModel($this->tableLang, $lang_data);
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminNews.result.add.news', [$this->request->getVar('lang['.$this->defaultLangId.'][news_name]')]));

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

	public function edit(int $news_id) {
		$sql = $this->NewsContentModel->newsInfoModel($news_id);

		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->NewsContentModel->newsLangModel($news_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['news_name'] = $row->news_name;
					$lang_array['data']['translations'][$row->lang_id]['news_description'] = $row->news_description;
					$lang_array['data']['translations'][$row->lang_id]['news_meta_title'] = $row->news_meta_title;
					$lang_array['data']['translations'][$row->lang_id]['news_meta_keywords'] = $row->news_meta_keywords;
					$lang_array['data']['translations'][$row->lang_id]['news_meta_description'] = $row->news_meta_description;
					$lang_array['data']['translations'][$row->lang_id]['news_slug'] = $row->news_slug;
					$lang_array['data']['translations'][$row->lang_id]['news_mobile_name'] = $row->news_mobile_name;
					$lang_array['data']['translations'][$row->lang_id]['news_mobile_description'] = $row->news_mobile_description;
				}
			}

			// Directorates (Selected)
			$directorates_id = [];
			if (isNotNull($sql->directorates_id)) {
				$explode = explode(',', $sql->directorates_id);
				foreach ($explode as $row) {
					$directorates_id[] = $row;
				}
			}

			// Directorates
			$directorates = [];
			$directorates_sql = $this->NewsContentModel->directoratesListModel($this->defaultLangId);
			if (isNotNull($directorates_sql)) {
				foreach ($directorates_sql as $row) {

					// Selected
					$selected = NULL;
					if (in_array($row->directorates_id, $directorates_id)) {
						$selected = 'selected';
					}

					$directorates[] = [
						'directorates_id' => $row->directorates_id,
						'directorates_name' => $row->directorates_name,
						'selected' => $selected
					];
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'paragraphs' => [
					'datatable_url' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/paragraphs/datatable/'.$sql->news_id)
				],
				'result' => [
					'news_id' => $sql->news_id,
					'status' => $sql->status,
					'status_genclik' => $sql->status_genclik,
					'push_notification' => $sql->push_notification,
					'image' => $sql->news_image != NULL ? base_url($this->filePath.'/'.$sql->news_image) : NULL,
					'image_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$sql->news_id)
				],
				'list' => [
					'directorates' => $directorates
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $news_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->NewsContentModel->newsInfoModel($news_id);
				if (isNotNull($sql)) {

					$rules = [
						'form.status' => [
							'label' => lang('AdminNews.news.general.status'),
							'rules' => 'required'
						],
						'form.status_genclik' => [
							'label' => lang('Admin.mobile'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.news_name' => [
							'label' => lang('AdminNews.news.general.name'),
							'rules' => 'required'
						]
					];

					/*****************************************************/

					// Image Upload Validation
					$file = $this->request->getFile('news_image');
					if (isNotNull($file)) {
						$rulesImage = [
							'news_image' => [
								'label' => lang('AdminNews.news.general.image.title'),
								'rules' => [
									'uploaded[news_image]',
									'mime_in[news_image,'.IMAGE_UPLOAD_MIME.']',
									'max_size[news_image,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];

						$rules = array_merge($rules, $rulesImage);
					}

					/*****************************************************/

					if ($this->validate($rules)) {

						/**************************************************/

						// Image Upload
						$fileName = $sql->news_image;
						$fileNameResult = '';
						if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {

							// Unlink
							unlinkFile($this->filePath, $sql->news_image);

							$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][news_name]')).'_'.$file->getRandomName();
							$file->move($this->filePath, $fileName);
						}

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['push_notification'] = isNotNull($this->request->getVar('form[push_notification]')) ? $this->request->getVar('form[push_notification]') : FALSE;
							$data['news_image'] = $fileName;

							// Directorates
							$data['directorates_id'] = '';
							if (isNotNull($this->request->getVar('form[directorates_id]'))) {
								$directorates = NULL;
								foreach ($this->request->getVar('form[directorates_id]') as $row) {
									$directorates .= $row.',';
								}

								$data['directorates_id'] = reduce_multiples($directorates, ',', TRUE);
							}

							$data['news_updated_date'] = nowDate();
						}

						if ($fileNameResult == NULL) {

							$result = $this->general->updateModel($this->table, $data, ['news_id' => $news_id]);
							if ($result !== FALSE) {

								// Lang
								if (isNotNull($this->request->getVar('lang'))) {
									foreach ($this->request->getVar('lang') as $lang_id => $value) {

										// Slug
										if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][news_slug]'))) {
											$slug = slug($value['news_slug']);
										} else {
											$slug = slug($value['news_name']);
										}

										$lang_data = [
											'news_id' => $news_id,
											'lang_id' => $lang_id,
											'news_name' => $value['news_name'],
											'news_description' => $value['news_description'],
											'news_meta_title' => $value['news_meta_title'],
											'news_meta_keywords' => $value['news_meta_keywords'],
											'news_meta_description' => $value['news_meta_description'],
											'news_slug' => $slug,
											'news_mobile_name' => $value['news_mobile_name'],
											'news_mobile_description' => $value['news_mobile_description']
										];

										$langControlModel = $this->NewsContentModel->newsLangControlModel($news_id, $lang_id);
										if (isNotNull($langControlModel)) {
											$this->general->updateModel($this->tableLang, $lang_data, ['news_id' => $news_id, 'lang_id' => $lang_id]);
										} else {
											$this->general->insertModel($this->tableLang, $lang_data);
										}
									}
								}

								// Flash Data
								session()->setFlashdata('flashDataMessageSuccess', lang('AdminNews.result.edit.news', [$this->request->getVar('lang['.$this->defaultLangId.'][news_name]')]));

								$ajax_message['success'] = TRUE;

								if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
									$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$news_id);
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

	public function delete(int $news_id) {
		$sql = $this->NewsContentModel->newsInfoModel($news_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['news_id' => $news_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePath, $sql->news_image);

				// Lang
				$lang = $this->NewsContentModel->newsLangModel($news_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['news_id' => $row->news_id]);
					}
				}

				/*****************************************************/

				// Paragraphs
				$paragraphs = $this->NewsContentModel->newsParagraphListModel($row->news_id);
				if (isNotNull($paragraphs)) {
					foreach ($paragraphs as $row) {
						$paragraph_delete = $this->general->deleteModel($this->tableParagraphs, ['news_id' => $row->news_id]);
						if ($paragraph_delete) {

							// Unlink
							unlinkFile($this->filePathBig, $row->news_paragraph_image);

							// Paragraphs Lang
							$paragraphs_lang = $this->NewsContentModel->newsParagraphLangModel($row->news_paragraph_id);
							if (isNotNull($paragraphs_lang)) {
								foreach ($paragraphs_lang as $value) {
									$this->general->deleteModel($this->tableParagraphsLang, ['news_paragraph_lang_id' => $value->news_paragraph_lang_id]);
								}
							}

						}
					}
				}

				/*****************************************************/

				// Images
				$images = $this->NewsContentModel->newsImageListModel($row->news_id);
				if (isNotNull($images)) {
					foreach ($images as $row) {
						// Unlink
						unlinkFile($this->filePathGallery, $row->news_image);

						$this->general->deleteModel($this->tableImages, ['news_id' => $row->news_id]);
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

	public function removeImage(int $news_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->NewsContentModel->newsInfoModel($news_id);
				if (isNotNull($sql)) {

					$result = $this->general->removeDropifyImageModel($this->table, ['news_image' => ''], ['news_id' => $news_id], $this->filePath);
					if ($result == TRUE) {

						// Unlink
						unlinkFile($this->filePath, $sql->news_image);

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

	public function paragraphDatatable(int $news_id) {
		if ($this->request->isAJAX()) {
			$column = ['news_paragraph_id', NULL, 'news_paragraph_name', 'news_paragraph_created_date', NULL];
			$search = [];
			$orderBy = ['news_paragraph_id' => 'ASC'];
			$where = ['news_id' => $news_id];

			$list = $this->DatatableModel->GetDatatables($this->tableParagraphs, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = $row->news_paragraph_id;
					$array[] = set_image($this->filePathBig.'/'.$row->news_paragraph_image, 100);
					$array[] = character_limiter($row->news_paragraph_name, 100);
					$array[] = dateFormat($row->news_paragraph_created_date, 'd/m/Y H:i:s');
					$array[] = action_links($row->news_paragraph_id, ['modal-edit', 'delete'], $this->pageUrl.'/paragraphs', NULL, NULL, ['modal', 'news_paragraph_edit']);
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

	public function paragraphInsert(int $news_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'lang.'.$this->defaultLangId.'.news_paragraph_name' => [
						'label' => lang('AdminNews.news.paragraphs.name'),
						'rules' => 'required'
					]
				];

				if ($this->validate($rules)) {

					// Image Upload
					$fileName = '';
					$fileNameResult = '';
					$file = $this->request->getFile('news_paragraph_image');
					if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {

						if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][news_paragraph_name]'))) {
							$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][news_paragraph_name]')).'_'.$file->getRandomName();
						} else {
							$fileName = $file->getRandomName();
						}

						$fileNameResult = $this->uploadSingleFile($file, $this->filePathBig, $fileName);
					}

					$data = [
						'news_id' => $news_id,
						'news_paragraph_image' => $fileName,
						'news_paragraph_created_date' => nowDate()
					];

					if ($fileNameResult == NULL) {
						$result = $this->general->insertModel($this->tableParagraphs, $data);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'news_paragraph_id' => $result,
										'lang_id' => $lang_id,
										'news_paragraph_name' => $value['news_paragraph_name'],
										'news_paragraph_description' => $value['news_paragraph_description']
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

	public function paragraphUpdate(int $news_paragraph_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->NewsContentModel->newsParagraphInfoModel($news_paragraph_id);
				if (isNotNull($sql)) {

					$rules = [
						'lang.'.$this->defaultLangId.'.news_paragraph_name' => [
							'label' => lang('AdminNews.news.paragraphs.name'),
							'rules' => 'required'
						]
					];

					if ($this->validate($rules)) {

						// Image Upload
						$fileName = $sql->news_paragraph_image;
						$fileNameResult = '';
						$file = $this->request->getFile('news_paragraph_image');
						if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
							// Unlink
							unlinkFile($this->filePathBig, $sql->news_paragraph_image);

							if (isNotNull($this->request->getVar('lang['.$this->defaultLangId.'][news_paragraph_name]'))) {
								$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][news_paragraph_name]')).'_'.$file->getRandomName();
							} else {
								$fileName = $file->getRandomName();
							}

							$fileNameResult = $this->uploadSingleFile($file, $this->filePathBig, $fileName);
						}

						$data = [
							'news_paragraph_image' => $fileName,
							'news_paragraph_updated_date' => nowDate()
						];

						if ($fileNameResult == NULL) {
							$result = $this->general->updateModel($this->tableParagraphs, $data, ['news_paragraph_id' => $news_paragraph_id]);
							if ($result !== FALSE) {

								// Lang
								if (isNotNull($this->request->getVar('lang'))) {
									foreach ($this->request->getVar('lang') as $lang_id => $value) {
										$lang_data = [
											'news_paragraph_id' => $news_paragraph_id,
											'lang_id' => $lang_id,
											'news_paragraph_name' => $value['news_paragraph_name'],
											'news_paragraph_description' => $value['news_paragraph_description']
										];

										$langControlModel = $this->NewsContentModel->newsParagraphLangControlModel($news_paragraph_id, $lang_id);
										if (isNotNull($langControlModel)) {
											$this->general->updateModel($this->tableParagraphsLang, $lang_data, ['news_paragraph_id' => $news_paragraph_id, 'lang_id' => $lang_id]);
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

	public function paragraphDelete(int $news_paragraph_id) {
		$sql = $this->NewsContentModel->newsParagraphInfoModel($news_paragraph_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->tableParagraphs, ['news_paragraph_id' => $news_paragraph_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePathBig, $sql->news_paragraph_image);

				// Lang
				$lang = $this->NewsContentModel->newsParagraphLangModel($news_paragraph_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableParagraphsLang, ['news_paragraph_id' => $row->news_paragraph_id]);
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

	public function paragraphRemoveImage(int $news_paragraph_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->tableParagraphs, ['news_paragraph_image' => ''], ['news_paragraph_id' => $news_paragraph_id], $this->filePathBig);
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

	/*****************************************************/

	public function imageList(int $news_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$array = [];
				$sql = $this->NewsContentModel->newsImageListModel($news_id);
				if (isNotNull($sql)) {
					foreach ($sql as $row) {
						$array[] = [
							'id' => $row->news_image_id,
							'image' => base_url($this->filePathGallery.'/'.$row->news_image),
							'type' => 'news',
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

	public function imageUpload(int $news_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$validate = [
					'qqfile' => [
						'label' => lang('AdminNews.news.general.image.title'),
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
							$fileName = slug($this->request->getVar('slug')).'_'.$file->getRandomName();

							$this->uploadSingleFile($file,
																			$this->filePathGallery,
																			$fileName,
																			$this->designSettings->news_big_image_width,
																			$this->designSettings->news_big_image_height);

							// Second Image & Order
							$news_image_order = 0;
							$news_image_order_sql = $this->NewsContentModel->newsImageOrderModel($news_id);
							if (isNotNull($news_image_order_sql)) {
								$news_image_order = $news_image_order_sql->news_image_order + 1;
							}

							$data = [
								'news_id' => $news_id,
								'news_image' => $fileName,
								'news_image_order' => $news_image_order
							];
							$this->general->insertModel($this->tableImages, $data);
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

				$result = $this->general->updateModel($this->tableImages, ['news_image_order' => $index], ['news_image_id' => $id]);
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
			if ($this->request->getMethod() == 'get') {

				$id = $this->request->getVar('id');

				$sql = $this->NewsContentModel->newsImageInfoModel($id);
				if (isNotNull($sql)) {

					$delete = $this->general->deleteModel($this->tableImages, ['news_image_id' => $id]);
					if ($delete) {

						// Unlink
						unlinkFile($this->filePathGallery, $sql->news_image);

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
