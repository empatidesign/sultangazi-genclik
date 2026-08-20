<?php
namespace App\Controllers\Backend\Contents;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Contents\ContactInformationModel;
use App\Models\Backend\DatatableModel;

class ContactInformation extends BaseController {

	protected $table;
	protected $tableLang;
	protected $pageUrl;
	protected $filePath;
	protected $ContactInformationModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'contact_information';
		$this->tableLang = 'contact_information_lang';
		$this->pageUrl = ADMIN_URL_CONTENTS.'/'.ADMIN_URL_CONTACT_INFORMATION;
		$this->filePath = FILE_PATH_CONTACT;
		$this->ContactInformationModel = new ContactInformationModel();
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
			$column = ['status', 'status_mobile', 'contact_default', 'contact_title', 'contact_telephone', 'contact_email', 'contact_created_date', 'contact_updated_date', NULL];
			$search = [];
			$orderBy = ['contact_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = set_status($row->status_mobile);
					$array[] = set_status($row->contact_default);
					$array[] = $row->contact_title;
					$array[] = $row->contact_telephone;
					$array[] = $row->contact_email;
					$array[] = dateFormat($row->contact_created_date, 'd-m-Y H:i:s');
					$array[] = $row->contact_updated_date != '0000-00-00 00:00:00' ? dateFormat($row->contact_updated_date, 'd-m-Y H:i:s') : NULL;
					$array[] = action_links($row->contact_id, ['edit', 'delete'], $this->pageUrl);
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

				$total = $this->general->totalRecordModal($this->table, ['contact_default' => $this->request->getVar('form[contact_default]')]);
				if ($total == 0) {

					$rules1 = [];
					$rules2 = [];
					$rules3 = [];
					$rules4 = [];

					$rules1 = [
						'form.status' => [
							'label' => lang('AdminContents.contactInformation.general.status'),
							'rules' => 'required'
						],
						'form.status_mobile' => [
							'label' => lang('Admin.mobile'),
							'rules' => 'required'
						],
						'form.contact_default' => [
							'label' => lang('AdminContents.contactInformation.general.default'),
							'rules' => 'required'
						],
						'lang.'.$this->defaultLangId.'.contact_title' => [
							'label' => lang('AdminContents.contactInformation.general.name'),
							'rules' => 'required'
						]
					];

					if (isNotNull($this->request->getVar('form[contact_email]'))) {
						$rules2 = [
							'form.contact_email' => [
								'label' => lang('AdminContents.contactInformation.general.emailAddress'),
								'rules' => 'min_length['.FORM_EMAIL_MIN_LENGTH.']|max_length['.FORM_EMAIL_MAX_LENGTH.']|valid_email'
							]
						];
					}

					if (isNotNull($this->request->getVar('form[contact_email2]'))) {
						$rules3 = [
							'form.contact_email2' => [
								'label' => lang('AdminContents.contactInformation.general.emailAddress2'),
								'rules' => 'min_length['.FORM_EMAIL_MIN_LENGTH.']|max_length['.FORM_EMAIL_MAX_LENGTH.']|valid_email'
							]
						];
					}

					/*****************************************************/

					// Image Upload Validation
					$file = $this->request->getFile('contact_map_marker');
					if (isNotNull($file)) {
						$rules4 = [
							'contact_map_marker' => [
								'label' => lang('AdminContents.pages.general.map.icon'),
								'rules' => [
									'uploaded[contact_map_marker]',
									'mime_in[contact_map_marker,'.IMAGE_UPLOAD_MIME.']',
									'max_size[contact_map_marker,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];
					}

					/*****************************************************/

					$rules = array_merge_recursive($rules1, $rules2, $rules3, $rules4);

					if ($this->validate($rules)) {

						// Image Upload
						$fileName = '';
						if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
							$fileName = 'marker_'.$file->getRandomName();
							$file->move($this->filePath, $fileName);
						}

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;
							$data['contact_telephone'] = $this->request->getVar('form[contact_telephone]') ?? phoneFormat($this->request->getVar('form[contact_telephone]'));
							$data['contact_telephone2'] = $this->request->getVar('form[contact_telephone2]') ?? phoneFormat($this->request->getVar('form[contact_telephone2]'));
							$data['contact_mobile'] = $this->request->getVar('form[contact_mobile]') ?? phoneFormat($this->request->getVar('form[contact_mobile]'));
							$data['contact_whatsapp'] = $this->request->getVar('form[contact_whatsapp]') ?? phoneFormat($this->request->getVar('form[contact_whatsapp]'));
							$data['contact_fax'] = $this->request->getVar('form[contact_fax]') ?? phoneFormat($this->request->getVar('form[contact_fax]'));
							$data['contact_fax2'] = $this->request->getVar('form[contact_fax2]') ?? phoneFormat($this->request->getVar('form[contact_fax2]'));
							$data['contact_map_embed_url'] = $this->request->getVar('form[contact_map_embed_url]') ?? phoneFormat($this->request->getVar('form[contact_map_embed_url]'));
							$data['contact_map_marker'] = $fileName;
							$data['contact_created_date'] = nowDate();
						}

						$result = $this->general->insertModel($this->table, $data);
						if ($result !== FALSE) {

							// Lang
							if (isNotNull($this->request->getVar('lang'))) {
								foreach ($this->request->getVar('lang') as $lang_id => $value) {
									$lang_data = [
										'contact_id' => $result,
										'lang_id' => $lang_id,
										'contact_title' => $value['contact_title'],
										'contact_address' => $value['contact_address'],
										'contact_working_hours' => $value['contact_working_hours']
									];

									$this->general->insertModel($this->tableLang, $lang_data);
								}
							}

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.add.contactInformation', [$this->request->getVar('lang['.$this->defaultLangId.'][contact_title]')]));

							$ajax_message['success'] = TRUE;
							$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);

						} else {
							$ajax_message['error'] = lang('Admin.error.insert');
						}

					} else {
						$ajax_message['error'] = $this->validator->listErrors();
					}

				} else {
					$ajax_message['error'] = lang('AdminContents.contactInformation.error.default');
				}

			} else {
				$ajax_message['error'] = lang('Admin.error.description');
			}
		} else {
			$ajax_message['error'] = lang('Admin.error.ajax');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function edit(int $contact_id) {
		$sql = $this->ContactInformationModel->contactInfoModel($contact_id);
		if (isNotNull($sql)) {

			$lang_array = [];
			$lang = $this->ContactInformationModel->contactLangModel($contact_id);
			if (isNotNull($lang)) {
				foreach ($lang as $row) {
					$lang_array['data']['translations'][$row->lang_id]['contact_title'] = $row->contact_title;
					$lang_array['data']['translations'][$row->lang_id]['contact_address'] = $row->contact_address;
					$lang_array['data']['translations'][$row->lang_id]['contact_working_hours'] = $row->contact_working_hours;
				}
			}

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'lang' => $lang_array,
				'result' => [
					'contact_id' => $sql->contact_id,
					'status' => $sql->status,
					'status_mobile' => $sql->status_mobile,
					'contact_default' => $sql->contact_default,
					'contact_telephone' => $sql->contact_telephone,
					'contact_telephone2' => $sql->contact_telephone2,
					'contact_mobile' => $sql->contact_mobile,
					'contact_whatsapp' => $sql->contact_whatsapp,
					'contact_fax' => $sql->contact_fax,
					'contact_fax2' => $sql->contact_fax2,
					'contact_map_embed_url' => $sql->contact_map_embed_url,
					'contact_email' => $sql->contact_email,
					'contact_email2' => $sql->contact_email2,
					'contact_post_code' => $sql->contact_post_code,
					'contact_map_url' => $sql->contact_map_url,
					'map' => [
						'contact_map_lat_coordinate' => $sql->contact_map_lat_coordinate,
						'contact_map_long_coordinate' => $sql->contact_map_long_coordinate,
						'contact_map_zoom' => $sql->contact_map_zoom,
						'contact_map_marker' => $sql->contact_map_marker != NULL ? base_url($this->filePath.'/'.$sql->contact_map_marker) : NULL,
						'image_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$sql->contact_id)
					]
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $contact_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->ContactInformationModel->contactInfoModel($contact_id);
				if (isNotNull($sql)) {

					$total = $this->general->totalRecordModal($this->table, ['contact_default' => $this->request->getVar('form[contact_default]'), 'contact_id !=' => $contact_id]);
					if ($total == 0) {

						$rules1 = [];
						$rules2 = [];
						$rules3 = [];
						$rules4 = [];

						$rules1 = [
							'form.status' => [
								'label' => lang('AdminContents.contactInformation.general.status'),
								'rules' => 'required'
							],
							'form.status_mobile' => [
								'label' => lang('Admin.mobile'),
								'rules' => 'required'
							],
							'form.contact_default' => [
								'label' => lang('AdminContents.contactInformation.general.default'),
								'rules' => 'required'
							],
							'lang.'.$this->defaultLangId.'.contact_title' => [
								'label' => lang('AdminContents.contactInformation.general.name'),
								'rules' => 'required'
							]
						];

						if (isNotNull($this->request->getVar('form[contact_email]'))) {
							$rules2 = [
								'form.contact_email' => [
									'label' => lang('AdminContents.contactInformation.general.emailAddress'),
									'rules' => 'min_length['.FORM_EMAIL_MIN_LENGTH.']|max_length['.FORM_EMAIL_MAX_LENGTH.']|valid_email'
								]
							];
						}

						if (isNotNull($this->request->getVar('form[contact_email2]'))) {
							$rules3 = [
								'form.contact_email2' => [
									'label' => lang('AdminContents.contactInformation.general.emailAddress2'),
									'rules' => 'min_length['.FORM_EMAIL_MIN_LENGTH.']|max_length['.FORM_EMAIL_MAX_LENGTH.']|valid_email'
								]
							];
						}

						/*****************************************************/

						// Image Upload Validation
						$file = $this->request->getFile('contact_map_marker');
						if (isNotNull($file)) {
							$rules4 = [
								'contact_map_marker' => [
									'label' => lang('AdminContents.pages.general.map.icon'),
									'rules' => [
										'uploaded[contact_map_marker]',
										'mime_in[contact_map_marker,'.IMAGE_UPLOAD_MIME.']',
										'max_size[contact_map_marker,'.IMAGE_UPLOAD_SIZE.']'
									]
								]
							];
						}

						/*****************************************************/

						$rules = array_merge_recursive($rules1, $rules2, $rules3, $rules4);

						if ($this->validate($rules)) {

							// Image Upload
							$fileName = $sql->contact_map_marker;
							if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
								// Unlink
								unlinkFile($this->filePath, $sql->contact_map_marker);

								$fileName = 'marker_'.$file->getRandomName();
								$file->move($this->filePath, $fileName);
							}

							$data = [];
							foreach ($this->request->getVar('form') as $key => $value) {
								$data[$key] = $value;
								$data['contact_telephone'] = $this->request->getVar('form[contact_telephone]') ?? phoneFormat($this->request->getVar('form[contact_telephone]'));
								$data['contact_telephone2'] = $this->request->getVar('form[contact_telephone2]') ?? phoneFormat($this->request->getVar('form[contact_telephone2]'));
								$data['contact_mobile'] = $this->request->getVar('form[contact_mobile]') ?? phoneFormat($this->request->getVar('form[contact_mobile]'));
								$data['contact_whatsapp'] = $this->request->getVar('form[contact_whatsapp]') ?? phoneFormat($this->request->getVar('form[contact_whatsapp]'));
								$data['contact_fax'] = $this->request->getVar('form[contact_fax]') ?? phoneFormat($this->request->getVar('form[contact_fax]'));
								$data['contact_fax2'] = $this->request->getVar('form[contact_fax2]') ?? phoneFormat($this->request->getVar('form[contact_fax2]'));
								$data['contact_map_marker'] = $fileName;
								$data['contact_updated_date'] = nowDate();
							}

							$result = $this->general->updateModel($this->table, $data, ['contact_id' => $contact_id]);
							if ($result !== FALSE) {

								// Lang
								if (isNotNull($this->request->getVar('lang'))) {
									foreach ($this->request->getVar('lang') as $lang_id => $value) {

										$lang_data = [
											'contact_id' => $contact_id,
											'lang_id' => $lang_id,
											'contact_title' => $value['contact_title'],
											'contact_address' => $value['contact_address'],
											'contact_working_hours' => $value['contact_working_hours']
										];

										$langControlModel = $this->ContactInformationModel->contactLangControlModel($contact_id, $lang_id);
										if (isNotNull($langControlModel)) {
											$this->general->updateModel($this->tableLang, $lang_data, ['contact_id' => $contact_id, 'lang_id' => $lang_id]);
										} else {
											$this->general->insertModel($this->tableLang, $lang_data);
										}
									}
								}

								// Flash Data
								session()->setFlashdata('flashDataMessageSuccess', lang('AdminContents.result.edit.contactInformation', [$this->request->getVar('lang['.$this->defaultLangId.'][contact_title]')]));

								$ajax_message['success'] = TRUE;

								if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
									$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$contact_id);
								} else {
									$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);
								}

							} else {
								$ajax_message['error'] = lang('Admin.error.update');
							}

						} else {
							$ajax_message['error'] = $this->validator->listErrors();
						}

					} else {
						$ajax_message['error'] = lang('AdminContents.contactInformation.error.default');
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

	public function delete(int $contact_id) {
		$sql = $this->ContactInformationModel->contactInfoModel($contact_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['contact_id' => $contact_id]);
			if ($delete) {

				// Lang
				$lang = $this->ContactInformationModel->contactLangModel($contact_id);
				if (isNotNull($lang)) {
					foreach ($lang as $row) {
						$this->general->deleteModel($this->tableLang, ['contact_id' => $row->contact_id]);
					}
				}

				// Unlink
				unlinkFile($this->filePath, $sql->contact_map_marker);

				$ajax_message['success'] = TRUE;

			} else {
				$ajax_message['error'] = lang('Admin.error.delete');
			}

		} else {
			$ajax_message['error'] = lang('Admin.error.noRecord');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function removeImage(int $contact_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->table, ['contact_map_marker' => ''], ['contact_id' => $contact_id], $this->filePath);
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
