<?php
namespace App\Controllers\Backend\Sultangazi;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Sultangazi\CityGuideContentsModel;
use App\Models\Backend\DatatableModel;

class CityGuideContents extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $filePath;
	protected $CityGuideContentsModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'city_guide_contents';
		$this->tableLang = 'city_guide_contents_lang';
		$this->pageUrl = ADMIN_URL_SULTANGAZI.'/'.ADMIN_URL_SULTANGAZI_CITY_GUIDE_CONTENTS;
		$this->filePath = FILE_PATH_CITY_GUIDE;
		$this->CityGuideContentsModel = new CityGuideContentsModel();
		$this->DatatableModel = new DatatableModel();
	}

	public function index() {
		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_name' => 'city-guide-content-index',
			'page_url' => $this->pageUrl,
			'datatable_url' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/datatable')
		]);
	}

	public function datatable() {
		if ($this->request->isAJAX()) {
			$column = ['status', NULL, 'city_guide_category_name', 'city_guide_content_name', 'city_guide_content_telephone', 'city_guide_content_email_address', 'city_guide_content_created_date', 'city_guide_content_updated_date', NULL];
			$search = [];
			$orderBy = ['city_guide_content_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_image($this->filePath.'/'.$row->city_guide_content_logo, 70);
					$array[] = $row->city_guide_category_name;
					$array[] = $row->city_guide_content_name;
					$array[] = isNotNull($row->city_guide_content_telephone) ? $row->city_guide_content_telephone : '--';
					$array[] = isNotNull($row->city_guide_content_email_address) ? $row->city_guide_content_email_address : '--';
					$array[] = dateFormat($row->city_guide_content_created_date, 'd-m-Y H:i:s');
					$array[] = $row->city_guide_content_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->city_guide_content_updated_date, 'd-m-Y H:i:s') : '--';
					$array[] = action_links($row->city_guide_content_id, ['edit', 'delete'], $this->pageUrl);
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

				$rules1 = [
					'form.status' => [
						'label' => lang('AdminSultangazi.cityGuideContents.general.status'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.city_guide_content_name' => [
						'label' => lang('AdminSultangazi.cityGuideContents.general.name'),
						'rules' => 'required'
					]
				];

				$rules2 = [];
				if (isNotNull($this->request->getVar('form[city_guide_content_email_address]'))) {
					$rules2 = [
						'form.city_guide_content_email_address' => [
							'label' => lang('AdminSultangazi.cityGuideContents.general.emailAddress'),
							'rules' => 'min_length['.FORM_EMAIL_MIN_LENGTH.']|max_length['.FORM_EMAIL_MAX_LENGTH.']|valid_email'
						]
					];
				}

				$rules3 = [];
				if (isNotNull($this->request->getVar('form[city_guide_content_web_address]'))) {
					$rules3 = [
						'form.city_guide_content_web_address' => [
							'label' => lang('AdminSultangazi.cityGuideContents.general.webAddress'),
							'rules' => 'valid_url_strict'
						]
					];
				}

				$rules4 = [];
				$logo = $this->request->getFile('city_guide_content_logo');
				if (isNotNull($logo)) {
					$rules4 = [
						'city_guide_content_logo' => [
							'label' => lang('AdminContents.pages.general.image'),
							'rules' => [
								'uploaded[city_guide_content_logo]',
								'mime_in[city_guide_content_logo,'.IMAGE_UPLOAD_MIME.']',
								'max_size[city_guide_content_logo,'.IMAGE_UPLOAD_SIZE.']'
							]
						]
					];
				}

				$rules = array_merge_recursive($rules1, $rules2, $rules3, $rules4);

				if ($this->validate($rules)) {

					// Logo Upload
					$fileName = '';
					$fileNameResult = '';
					if (isNotNull($logo) && $logo->isValid() && !$logo->hasMoved()) {
						$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][city_guide_content_name]')).'_'.$logo->getRandomName();
						$fileNameResult = $this->uploadSingleFile($logo, $this->filePath, $fileName);
					}

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['city_guide_content_person_name_surname'] = trim($this->request->getVar('form[city_guide_content_person_name_surname]'));
						$data['city_guide_content_telephone'] = trim($this->request->getVar('form[city_guide_content_telephone]'));
						$data['city_guide_content_fax'] = trim($this->request->getVar('form[city_guide_content_fax]'));
						$data['city_guide_content_email_address'] = trim($this->request->getVar('form[city_guide_content_email_address]'));
						$data['city_guide_content_web_address'] = trim($this->request->getVar('form[city_guide_content_web_address]'));
						$data['city_guide_content_lat_coordinate'] = trim($this->request->getVar('form[city_guide_content_lat_coordinate]'));
						$data['city_guide_content_long_coordinate'] = trim($this->request->getVar('form[city_guide_content_long_coordinate]'));
						$data['city_guide_content_address'] = trim($this->request->getVar('form[city_guide_content_address]'));
						$data['city_guide_content_logo'] = $fileName;
						$data['city_guide_content_created_date'] = nowDate();
					}

					if ($fileNameResult == NULL) {
						$result = $this->general->insertModel($this->table, $data);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'city_guide_content_id' => $result,
										'lang_id' => $lang_id,
										'city_guide_content_name' => trim($value['city_guide_content_name']),
										'city_guide_content_person_name_sub_title' => trim($value['city_guide_content_person_name_sub_title']),
										'city_guide_content_description' => trim($value['city_guide_content_description']),
										'city_guide_content_slug' => slug(trim($value['city_guide_content_name']))
									];

									$this->general->insertModel($this->tableLang, $lang_data);
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminSultangazi.result.add.cityGuideContents', [$this->request->getVar('lang['.$this->defaultLangId.'][city_guide_content_name]')]));

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

	public function edit(int $city_guide_content_id) {
		$sql = $this->CityGuideContentsModel->cityGuideContentsInfoModel($city_guide_content_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->CityGuideContentsModel->cityGuideContentsLangModel($city_guide_content_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['city_guide_content_name'] = $row->city_guide_content_name;
					$lang_array['data']['translations'][$row->lang_id]['city_guide_content_person_name_sub_title'] = $row->city_guide_content_person_name_sub_title;
					$lang_array['data']['translations'][$row->lang_id]['city_guide_content_description'] = $row->city_guide_content_description;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'city_guide_content_id' => $sql->city_guide_content_id,
					'status' => $sql->status,
					'city_guide_content_category_id' => $sql->city_guide_content_category_id,
					'city_guide_content_person_name_surname' => $sql->city_guide_content_person_name_surname,
					'city_guide_content_telephone' => $sql->city_guide_content_telephone,
					'city_guide_content_fax' => $sql->city_guide_content_fax,
					'city_guide_content_email_address' => $sql->city_guide_content_email_address,
					'city_guide_content_web_address' => $sql->city_guide_content_web_address,
					'city_guide_content_address' => $sql->city_guide_content_address,
					'city_guide_content_lat_coordinate' => $sql->city_guide_content_lat_coordinate,
					'city_guide_content_long_coordinate' => $sql->city_guide_content_long_coordinate,
					'logo' => isNotNull($sql->city_guide_content_logo) ? base_url($this->filePath.'/'.$sql->city_guide_content_logo) : NULL,
					'logo_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$sql->city_guide_content_id)
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $city_guide_content_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->CityGuideContentsModel->cityGuideContentsInfoModel($city_guide_content_id);
				if (isNotNull($sql)) {

					$rules1 = [
						'form.status' => [
							'label' => lang('AdminSultangazi.cityGuideContents.general.status'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.city_guide_content_name' => [
							'label' => lang('AdminSultangazi.cityGuideContents.general.name'),
							'rules' => 'required'
						]
					];

					$rules2 = [];
					if (isNotNull($this->request->getVar('form[city_guide_content_email_address]'))) {
						$rules2 = [
							'form.city_guide_content_email_address' => [
								'label' => lang('AdminSultangazi.cityGuideContents.general.emailAddress'),
								'rules' => 'min_length['.FORM_EMAIL_MIN_LENGTH.']|max_length['.FORM_EMAIL_MAX_LENGTH.']|valid_email'
							]
						];
					}

					$rules3 = [];
					if (isNotNull($this->request->getVar('form[city_guide_content_web_address]'))) {
						$rules3 = [
							'form.city_guide_content_web_address' => [
								'label' => lang('AdminSultangazi.cityGuideContents.general.webAddress'),
								'rules' => 'valid_url_strict'
							]
						];
					}

					$rules4 = [];
					$logo = $this->request->getFile('city_guide_content_logo');
					if (isNotNull($logo)) {
						$rules4 = [
							'city_guide_content_logo' => [
								'label' => lang('AdminContents.pages.general.image'),
								'rules' => [
									'uploaded[city_guide_content_logo]',
									'mime_in[city_guide_content_logo,'.IMAGE_UPLOAD_MIME.']',
									'max_size[city_guide_content_logo,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];
					}

					$rules = array_merge_recursive($rules1, $rules2, $rules3, $rules4);

					if ($this->validate($rules)) {

						// Logo Upload
						$fileName = $sql->city_guide_content_logo;
						$fileNameResult = '';
						if (isNotNull($logo) && $logo->isValid() && !$logo->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->city_guide_content_logo);

							$fileName = slug($this->request->getVar('lang['.$this->defaultLangId.'][city_guide_content_name]')).'_'.$logo->getRandomName();
							$fileNameResult = $this->uploadSingleFile($logo, $this->filePath, $fileName);
						}

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['city_guide_content_person_name_surname'] = trim($this->request->getVar('form[city_guide_content_person_name_surname]'));
							$data['city_guide_content_telephone'] = trim($this->request->getVar('form[city_guide_content_telephone]'));
							$data['city_guide_content_fax'] = trim($this->request->getVar('form[city_guide_content_fax]'));
							$data['city_guide_content_email_address'] = trim($this->request->getVar('form[city_guide_content_email_address]'));
							$data['city_guide_content_web_address'] = trim($this->request->getVar('form[city_guide_content_web_address]'));
							$data['city_guide_content_lat_coordinate'] = trim($this->request->getVar('form[city_guide_content_lat_coordinate]'));
							$data['city_guide_content_long_coordinate'] = trim($this->request->getVar('form[city_guide_content_long_coordinate]'));
							$data['city_guide_content_address'] = trim($this->request->getVar('form[city_guide_content_address]'));
							$data['city_guide_content_logo'] = $fileName;
							$data['city_guide_content_updated_date'] = nowDate();
						}

						if ($fileNameResult == NULL) {
							$result = $this->general->updateModel($this->table, $data, ['city_guide_content_id' => $city_guide_content_id]);
							if ($result !== FALSE) {

								// Lang
								if (isNotNull($this->request->getVar('lang'))) {
									foreach ($this->request->getVar('lang') as $lang_id => $value) {
										$lang_data = [
											'city_guide_content_id' => $city_guide_content_id,
											'lang_id' => $lang_id,
											'city_guide_content_name' => trim($value['city_guide_content_name']),
											'city_guide_content_person_name_sub_title' => trim($value['city_guide_content_person_name_sub_title']),
											'city_guide_content_description' => trim($value['city_guide_content_description']),
											'city_guide_content_slug' => slug(trim($value['city_guide_content_name']))
										];

										$langControlModel = $this->CityGuideContentsModel->cityGuideContentsLangControlModel($city_guide_content_id, $lang_id);
										if (isNotNull($langControlModel)) {
											$this->general->updateModel($this->tableLang, $lang_data, ['city_guide_content_id' => $city_guide_content_id, 'lang_id' => $lang_id]);
										} else {
											$this->general->insertModel($this->tableLang, $lang_data);
										}
									}
								}

								// Flash Data
								session()->setFlashdata('flashDataMessageSuccess', lang('AdminSultangazi.result.edit.cityGuideContents', [$this->request->getVar('lang['.$this->defaultLangId.'][city_guide_content_name]')]));

								$ajax_message['success'] = TRUE;

								if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
									$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$city_guide_content_id);
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

	public function delete(int $city_guide_content_id) {
		$sql = $this->CityGuideContentsModel->cityGuideContentsInfoModel($city_guide_content_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['city_guide_content_id' => $city_guide_content_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePath, $sql->city_guide_content_logo);

				// Lang
				$lang = $this->CityGuideContentsModel->cityGuideContentsLangModel($city_guide_content_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['city_guide_content_id' => $row->city_guide_content_id]);
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

	public function removeImage(int $city_guide_content_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->table, ['city_guide_content_logo' => ''], ['city_guide_content_id' => $city_guide_content_id], $this->filePath);
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
