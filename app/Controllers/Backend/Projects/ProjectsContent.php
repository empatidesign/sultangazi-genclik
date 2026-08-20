<?php

namespace App\Controllers\Backend\Projects;

use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Projects\ProjectsContentModel;
use App\Models\Backend\DatatableModel;

class ProjectsContent extends BaseController
{

	protected $table;
	protected $tableLang;
	protected $tableImages;
	protected $pageUrl;
	protected $filePathThumb;
	protected $filePathMedium;
	protected $filePathBig;
	protected $ProjectsContentModel;
	protected $DatatableModel;

	public function __construct()
	{
		$this->table = 'projects';
		$this->tableLang = 'projects_lang';
		$this->tableImages = 'projects_image';
		$this->pageUrl = ADMIN_URL_PROJECTS . '/' . ADMIN_URL_PROJECTS_CONTENT;
		$this->filePathThumb = FILE_PATH_PROJECT_THUMB;
		$this->filePathMedium = FILE_PATH_PROJECT_MEDIUM;
		$this->filePathBig = FILE_PATH_PROJECT_BIG;
		$this->ProjectsContentModel = new ProjectsContentModel();
		$this->DatatableModel = new DatatableModel();
	}

	public function index()
	{
		return $this->twig->render($this->BACKEND_TEMPLATE_PATH . '/' . $this->pageUrl . '.html', [
			'page_name' => 'project-index',
			'page_url' => $this->pageUrl,
			'datatable_url' => base_url(BACKEND_URL . '/' . $this->pageUrl . '/datatable')
		]);
	}

	public function datatable()
	{
		if ($this->request->isAJAX()) {
			$column = ['status', 'status_mobile', 'home_page', NULL, 'project_name', 'project_start_date', 'project_end_date', 'project_created_date', 'project_updated_date', NULL];
			$search = [];
			$orderBy = ['project_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_status($row->status_mobile);
					$array[] = set_status($row->home_page);
					$array[] = isNotNull($row->project_image) ? set_image($this->filePathThumb . '/' . $row->project_image, 100) : NULL;
					$array[] = character_limiter($row->project_name, 50);
					$array[] = $row->project_start_date != '0000-00-00' ? dateFormat($row->project_start_date, 'd/m/Y') : '--';
					$array[] = $row->project_end_date != '0000-00-00' ? dateFormat($row->project_end_date, 'd/m/Y') : '--';
					$array[] = dateFormat($row->project_created_date, 'd-m-Y H:i:s');
					$array[] = $row->project_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->project_updated_date, 'd-m-Y H:i:s') : '--';
					$array[] = action_links($row->project_id, ['edit', 'delete'], $this->pageUrl);
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

	public function add()
	{
		$last_id = $this->general->lastIDModel($this->table, 'project_id');

		return $this->twig->render($this->BACKEND_TEMPLATE_PATH . '/' . $this->pageUrl . '.html', [
			'page_name' => 'add',
			'page_url' => $this->pageUrl,
			'last_id' => $last_id,
			'list' => [
				'category' => $this->ProjectsContentModel->projectCategoryListModel($this->defaultLangId),
				'status' => $this->ProjectsContentModel->projectStatusListModel($this->defaultLangId),
				'neighbourhoods' => $this->ProjectsContentModel->neighbourhoodListModel()
			]
		]);
	}

	public function insert()
	{
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules1 = [
					'form.status' => [
						'label' => lang('AdminProjects.contents.general.status'),
						'rules' => 'required'
					],
					'form.status_mobile' => [
						'label' => lang('Admin.mobile'),
						'rules' => 'required'
					],
					'form.home_page' => [
						'label' => lang('AdminProjects.contents.general.homePage'),
						'rules' => 'required'
					],
					'form.project_category_id' => [
						'label' => lang('AdminProjects.contents.general.category'),
						'rules' => 'required'
					],
					'form.project_status_id' => [
						'label' => lang('AdminProjects.contents.general.projectStatus'),
						'rules' => 'required'
					],
					'lang.' . $this->defaultLangId . '.project_name' => [
						'label' => lang('AdminProjects.contents.general.name'),
						'rules' => 'required'
					]
				];

				// Project Start Date
				$rules2 = [];
				$project_start_date = trim($this->request->getVar('form[project_start_date]'));
				if (isNotNull($project_start_date)) {
					$rules2 = [
						'form.project_start_date' => [
							'label' => lang('AdminProjects.contents.general.date.start'),
							'rules' => 'required|valid_date[d/m/Y]'
						]
					];
				}

				// Project End Date
				$rules3 = [];
				$project_end_date = trim($this->request->getVar('form[project_end_date]'));
				if (isNotNull($project_end_date)) {
					$rules3 = [
						'form.project_end_date' => [
							'label' => lang('AdminProjects.contents.general.date.end'),
							'rules' => 'required|valid_date[d/m/Y]'
						]
					];
				}

				/*****************************************************/

				$rules = array_merge_recursive($rules1, $rules2, $rules3);

				if ($this->validate($rules)) {
					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['project_start_date'] = isNotNull($project_start_date) ? dateFormat($project_start_date, 'Y-m-d') : '0000-00-00';
						$data['project_end_date'] = isNotNull($project_end_date) ? dateFormat($project_end_date, 'Y-m-d') : '0000-00-00';
						$data['project_location_address'] = trim($this->request->getVar('form[project_location_address]'));
						$data['project_location_telephone'] = isNotNull($this->request->getVar('form[project_location_telephone]')) ? $this->request->getVar('form[project_location_telephone]') : '';
						$data['project_location_map'] = trim($this->request->getVar('form[project_location_map]'));
						$data['project_lat_coordinate'] = trim(removeWhiteSpaces($this->request->getVar('form[project_lat_coordinate]')));
						$data['project_long_coordinate'] = trim(removeWhiteSpaces($this->request->getVar('form[project_long_coordinate]')));
						$data['project_created_date'] = nowDate();
					}

					$result = $this->general->insertModel($this->table, $data);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {

								// Slug
								if (isNotNull($this->request->getVar('lang[' . $this->defaultLangId . '][project_slug]'))) {
									$slug = slug($value['project_slug']);
								} else {
									$slug = isNotNull($value['project_name']) ? slug($value['project_name']) : $this->request->getVar('lang[' . $this->defaultLangId . '][project_name]');
								}

								$lang_data = [
									'project_id' => $result,
									'lang_id' => $lang_id,
									'project_name' => isNotNull($value['project_name']) ? upper($value['project_name']) : upper($this->request->getVar('lang[' . $this->defaultLangId . '][project_name]')),
									'project_description' => $value['project_description'],
									'project_meta_title' => $value['project_meta_title'],
									'project_meta_keywords' => $value['project_meta_keywords'],
									'project_meta_description' => $value['project_meta_description'],
									'project_slug' => $slug
								];

								$this->general->insertModel($this->tableLang, $lang_data);
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminProjects.result.add.contents', [$this->request->getVar('lang[' . $this->defaultLangId . '][project_name]')]));

						$ajax_message['success'] = TRUE;
						$ajax_message['url'] = base_url(BACKEND_URL . '/' . $this->pageUrl);
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

	public function edit(int $project_id)
	{
		$sql = $this->ProjectsContentModel->projectInfoModel($project_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->ProjectsContentModel->projectLangModel($project_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['project_name'] = $row->project_name;
					$lang_array['data']['translations'][$row->lang_id]['project_description'] = $row->project_description;
					$lang_array['data']['translations'][$row->lang_id]['project_meta_title'] = $row->project_meta_title;
					$lang_array['data']['translations'][$row->lang_id]['project_meta_keywords'] = $row->project_meta_keywords;
					$lang_array['data']['translations'][$row->lang_id]['project_meta_description'] = $row->project_meta_description;
					$lang_array['data']['translations'][$row->lang_id]['project_slug'] = $row->project_slug;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH . '/' . $this->pageUrl . '.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'list' => [
					'category' => $this->ProjectsContentModel->projectCategoryListModel($this->defaultLangId),
					'status' => $this->ProjectsContentModel->projectStatusListModel($this->defaultLangId),
					'neighbourhoods' => $this->ProjectsContentModel->neighbourhoodListModel()
				],
				'result' => [
					'project_id' => $sql->project_id,
					'status' => $sql->status,
					'status_mobile' => $sql->status_mobile,
					'home_page' => $sql->home_page,
					'project_category_id' => $sql->project_category_id,
					'project_status_id' => $sql->project_status_id,
					'project_date' => [
						'start' => $sql->project_start_date != '0000-00-00' ? dateFormat($sql->project_start_date, 'd/m/Y') : NULL,
						'end' => $sql->project_end_date != '0000-00-00' ? dateFormat($sql->project_end_date, 'd/m/Y') : NULL
					],
					'project_neighbourhood_id' => $sql->project_neighbourhood_id,
					'project_location_address' => $sql->project_location_address,
					'project_responsible' => $sql->project_responsible,
					'project_location_telephone' => $sql->project_location_telephone,
					'project_location_map' => $sql->project_location_map,
					'project_lat_coordinate' => $sql->project_lat_coordinate,
					'project_long_coordinate' => $sql->project_long_coordinate
				]
			]);
		} else {
			return redirect()->to(BACKEND_URL . '/404');
		}
	}

	public function update(int $project_id)
	{
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->ProjectsContentModel->projectInfoModel($project_id);
				if (isNotNull($sql)) {

					$rules1 = [
						'form.status' => [
							'label' => lang('AdminProjects.contents.general.status'),
							'rules' => 'required'
						],
						'form.status_mobile' => [
							'label' => lang('Admin.mobile'),
							'rules' => 'required'
						],
						'form.home_page' => [
							'label' => lang('AdminProjects.contents.general.homePage'),
							'rules' => 'required'
						],
						'form.project_category_id' => [
							'label' => lang('AdminProjects.contents.general.category'),
							'rules' => 'required'
						],
						'form.project_status_id' => [
							'label' => lang('AdminProjects.contents.general.projectStatus'),
							'rules' => 'required'
						],
						'lang.' . $this->defaultLangId . '.project_name' => [
							'label' => lang('AdminProjects.contents.general.name'),
							'rules' => 'required'
						]
					];

					// Project Start Date
					$rules2 = [];
					$project_start_date = trim($this->request->getVar('form[project_start_date]'));
					if (isNotNull($project_start_date)) {
						$rules2 = [
							'form.project_start_date' => [
								'label' => lang('AdminProjects.contents.general.date.start'),
								'rules' => 'required|valid_date[d/m/Y]'
							]
						];
					}

					// Project End Date
					$rules3 = [];
					$project_end_date = trim($this->request->getVar('form[project_end_date]'));
					if (isNotNull($project_end_date)) {
						$rules3 = [
							'form.project_end_date' => [
								'label' => lang('AdminProjects.contents.general.date.end'),
								'rules' => 'required|valid_date[d/m/Y]'
							]
						];
					}

					/*****************************************************/

					$rules = array_merge_recursive($rules1, $rules2, $rules3);

					if ($this->validate($rules)) {

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['project_start_date'] = isNotNull($project_start_date) ? dateFormat($project_start_date, 'Y-m-d') : '0000-00-00';
							$data['project_end_date'] = isNotNull($project_end_date) ? dateFormat($project_end_date, 'Y-m-d') : '0000-00-00';
							$data['project_location_address'] = trim($this->request->getVar('form[project_location_address]'));
							$data['project_location_telephone'] = isNotNull($this->request->getVar('form[project_location_telephone]')) ? $this->request->getVar('form[project_location_telephone]') : '';
							$data['project_lat_coordinate'] = trim(removeWhiteSpaces($this->request->getVar('form[project_lat_coordinate]')));
							$data['project_long_coordinate'] = trim(removeWhiteSpaces($this->request->getVar('form[project_long_coordinate]')));
							$data['project_location_map'] = trim($this->request->getVar('form[project_location_map]'));
							$data['project_updated_date'] = nowDate();
						}

						$result = $this->general->updateModel($this->table, $data, ['project_id' => $project_id]);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {

									// Slug
									if (isNotNull($this->request->getVar('lang[' . $this->defaultLangId . '][project_slug]'))) {
										$slug = slug($value['project_slug']);
									} else {
										$slug = slug($value['project_name']);
									}

									$lang_data = [
										'project_id' => $project_id,
										'lang_id' => $lang_id,
										'project_name' => upper($value['project_name']),
										'project_description' => $value['project_description'],
										'project_meta_title' => $value['project_meta_title'],
										'project_meta_keywords' => $value['project_meta_keywords'],
										'project_meta_description' => $value['project_meta_description'],
										'project_slug' => $slug
									];

									$langControlModel = $this->ProjectsContentModel->projectLangControlModel($project_id, $lang_id);
									if (isNotNull($langControlModel)) {
										$this->general->updateModel($this->tableLang, $lang_data, ['project_id' => $project_id, 'lang_id' => $lang_id]);
									} else {
										$this->general->insertModel($this->tableLang, $lang_data);
									}
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminProjects.result.edit.contents', [$this->request->getVar('lang[' . $this->defaultLangId . '][project_name]')]));

							$ajax_message['success'] = TRUE;

							if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
								$ajax_message['url'] = base_url(BACKEND_URL . '/' . $this->pageUrl . '/edit/' . $project_id);
							} else {
								$ajax_message['url'] = base_url(BACKEND_URL . '/' . $this->pageUrl);
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

	public function delete(int $project_id)
	{
		$sql = $this->ProjectsContentModel->projectInfoModel($project_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['project_id' => $project_id]);
			if ($delete) {

				// Lang
				$lang = $this->ProjectsContentModel->projectLangModel($project_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['project_id' => $row->project_id]);
					}
				}

				/*****************************************************/

				// Images
				$images = $this->ProjectsContentModel->projectImageListModel($project_id);
				if (isNotNull($images)) {
					foreach ($images as $row) {
						// Unlink
						unlinkFile($this->filePathThumb, $row->project_image);
						unlinkFile($this->filePathMedium, $row->project_image);
						unlinkFile($this->filePathBig, $row->project_image);

						$this->general->deleteModel($this->tableImages, ['project_id' => $row->project_id]);
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

	/*****************************************************/

	public function imageList(int $project_id)
	{
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$array = [];
				$sql = $this->ProjectsContentModel->projectImageListModel($project_id);
				if (isNotNull($sql)) {
					foreach ($sql as $row) {
						$array[] = [
							'id' => $row->project_image_id,
							'image' => base_url($this->filePathThumb . '/' . $row->project_image),
							'default' => $row->project_image_default,
							'defaultLang' => lang('AdminProjects.contents.general.image.defaultImage'),
							'type' => 'projects',
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

	public function imageUpload(int $project_id)
	{
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$validate = [
					'qqfile' => [
						'label' => lang('AdminProjects.contents.general.image.title'),
						'rules' => [
							'uploaded[qqfile]',
							'mime_in[qqfile,' . IMAGE_UPLOAD_MIME . ']',
							'max_size[qqfile,' . IMAGE_UPLOAD_SIZE . ']'
						]
					]
				];

				if ($this->validate($validate)) {

					$files = $this->request->getFileMultiple('qqfile');
					foreach ($files as $file) {
						if ($file->isValid() && !$file->hasMoved()) {
							$fileName = slug($this->request->getVar('slug')) . '_' . $file->getRandomName();

							// Thumb
							$this->uploadSingleFile(
								$file,
								$this->filePathThumb,
								$fileName,
								$this->designSettings->project_thumb_image_width,
								$this->designSettings->project_thumb_image_height
							);

							// Medium
							$this->uploadSingleFile(
								$file,
								$this->filePathMedium,
								$fileName,
								$this->designSettings->project_medium_image_width,
								$this->designSettings->project_medium_image_height
							);

							// Big
							$this->uploadSingleFile(
								$file,
								$this->filePathBig,
								$fileName,
								$this->designSettings->project_big_image_width,
								$this->designSettings->project_big_image_height
							);

							// Default Image Control
							$default_image = TRUE;
							$image_list = $this->ProjectsContentModel->projectImageListModel($project_id);
							if (isNotNull($image_list)) {
								foreach ($image_list as $row) {
									if ($row->project_image_default == FORM_CHECKBOX_VALUE_NUMBER) {
										$default_image = FALSE;
									}
								}
							}

							// Second Image & Order
							$project_image_order = 0;
							$project_image_order_sql = $this->ProjectsContentModel->projectImageOrderModel($project_id);
							if (isNotNull($project_image_order_sql)) {
								$project_image_order = $project_image_order_sql->project_image_order + 1;
							}

							$data = [
								'project_id' => $project_id,
								'project_image' => $fileName,
								'project_image_default' => $default_image == TRUE ? FORM_ACTIVE_NUMBER : FALSE,
								'project_image_order' => $project_image_order
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

	public function imageUpdate(int $project_id)
	{
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$data = [
					'project_image_default' => $this->request->getVar('project_image_default')
				];

				$result = $this->general->updateModel($this->tableImages, $data, ['project_image_id' => $project_id]);
				if ($result !== FALSE) {
					$ajax_message['success'] = TRUE;
				} else {
					$ajax_message['error'] = lang('Admin.error.update');
				}
			} else {
				$ajax_message['error'] = lang('Admin.error.description');
			}
		} else {
			$ajax_message['error'] = lang('Admin.error.ajax');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function imageSort()
	{
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'GET') {

				$id = $this->request->getVar('id');
				$index = $this->request->getVar('index');

				$result = $this->general->updateModel($this->tableImages, ['project_image_order' => $index], ['project_image_id' => $id]);
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

	public function imageDelete()
	{
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'GET') {

				$id = $this->request->getVar('id');

				$sql = $this->ProjectsContentModel->projectImageInfoModel($id);
				if (isNotNull($sql)) {

					$delete = $this->general->deleteModel($this->tableImages, ['project_image_id' => $id]);
					if ($delete) {

						// Unlink
						unlinkFile($this->filePathThumb, $sql->project_image);
						unlinkFile($this->filePathMedium, $sql->project_image);
						unlinkFile($this->filePathBig, $sql->project_image);

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
