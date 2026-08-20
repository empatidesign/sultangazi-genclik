<?php
namespace App\Controllers\Backend\Settings;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\SettingModel;
use App\Models\Backend\DatatableModel;

class ManagerAccounts extends BaseController {

	protected $table;
	protected $pageUrl;
	protected $filePath;
	protected $SettingModel;
	protected $DatatableModel;

	public function __construct() {
		$this->table = 'users';
		$this->pageUrl = ADMIN_URL_SETTINGS.'/'.ADMIN_URL_MANAGER_ACCOUNTS;
		$this->filePath = FILE_PATH_MANAGER_ACCOUNTS;
		$this->SettingModel = new SettingModel();
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
			$column = ['user_status', 'user_type_name', 'user_name_surname', 'user_telephone', 'user_email_address', 'user_last_login_date', NULL];
			$search = [];
			$orderBy = ['user_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->user_status);
					$array[] = $row->user_type_name;
					$array[] = $row->user_name_surname;
					$array[] = $row->user_telephone;
					$array[] = $row->user_email_address;
					$array[] = $row->user_last_login_date != '0000-00-00 00:00:00' ? dateFormat($row->user_last_login_date, 'd-m-Y H:i:s') : lang('AdminSettings.managerAccounts.general.notSignedIn');
					$array[] = action_links($row->user_id, ['change-password', 'edit', 'delete'], $this->pageUrl);
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
			'user_types' => $this->SettingModel->managerAccountsUserTypesModel()
		]);
	}

	public function insert() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'form.user_status' => [
						'label' => lang('AdminSettings.managerAccounts.general.status'),
						'rules' => 'required'
					],
					'form.user_type_id' => [
						'label' => lang('AdminSettings.managerAccounts.general.type'),
						'rules' => 'required'
					],
					'form.user_name_surname' => [
						'label' => lang('AdminSettings.managerAccounts.general.nameSurname'),
						'rules' => 'required'
					],
					'form.user_email_address' => [
						'label' => lang('AdminSettings.managerAccounts.general.emailAddress'),
						'rules' => 'required|min_length['.FORM_EMAIL_MIN_LENGTH.']|max_length['.FORM_EMAIL_MAX_LENGTH.']|valid_email'
					],
					'new_password' => [
						'label' => lang('AdminSettings.managerAccounts.general.password.title'),
						'rules' => 'required|min_length['.FORM_PASSWORD_MIN_LENGTH.']|max_length['.FORM_PASSWORD_MAX_LENGTH.']'
					],
					'confirm_password' => [
						'label' => lang('AdminSettings.managerAccounts.general.password.again'),
						'rules' => 'required|min_length['.FORM_PASSWORD_MIN_LENGTH.']|max_length['.FORM_PASSWORD_MAX_LENGTH.']|matches[new_password]'
					]
				];

				/*****************************************************/

				// Image Upload Validation
				$file = $this->request->getFile('user_image');
				if (isNotNull($file)) {
					$rulesImage = [
						'user_image' => [
							'label' => lang('AdminSettings.managerAccounts.general.image'),
							'rules' => [
								'uploaded[user_image]',
								'mime_in[user_image,'.IMAGE_UPLOAD_MIME.']',
								'max_size[user_image,'.IMAGE_UPLOAD_SIZE.']'
							]
						]
					];

					$rules = array_merge($rules, $rulesImage);
				}

				/*****************************************************/

				if ($this->validate($rules)) {

					// Image Upload
					$fileName = '';
					$file = $this->request->getFile('user_image');
					if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
						$fileName = slug($this->request->getVar('form[user_name_surname]')).'_'.$file->getRandomName();
						$resize = \Config\Services::image()
							->withFile($file)
							->withResource(IMAGE_UPLOAD_QUALITY)
							->resize(120, 120, TRUE, 'height')
							->save($this->filePath.'/'.$fileName);
					}

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;

						if (isNotNull($this->request->getVar('form[user_telephone]'))) {
							$data['user_telephone'] = phoneFormat($this->request->getVar('form[user_telephone]'));
						}

						$data['user_password'] = password_hash($this->request->getVar('new_password'), PASSWORD_DEFAULT);
						$data['user_image'] = $fileName;
						$data['user_created_date'] = nowDate();
						$data['user_created_ip'] = $this->request->getIPAddress();
					}

					$result = $this->general->insertModel($this->table, $data);
					if ($result !== FALSE) {

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminSettings.result.add.managerAccounts', [$this->request->getVar('form[user_name_surname]')]));

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

	public function edit(int $user_id) {
		$sql = $this->SettingModel->managerAccountsInfoModel($user_id);
		if (isNotNull($sql)) {

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'edit',
				'page_url' => $this->pageUrl,
				'result' => [
					'user_id' => $sql->user_id,
					'user_type_id' => $sql->user_type_id,
					'user_status' => $sql->user_status,
					'user_name_surname' => $sql->user_name_surname,
					'user_telephone' => $sql->user_telephone,
					'user_email_address' => $sql->user_email_address,
					'user_image' => $sql->user_image != NULL ? base_url($this->filePath.'/'.$sql->user_image) : NULL,
					'image_remove' => base_url(BACKEND_URL.'/'.$this->pageUrl.'/remove-image/'.$sql->user_id)
				],
				'user_types' => $this->SettingModel->managerAccountsUserTypesModel()
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $user_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->SettingModel->managerAccountsInfoModel($user_id);
				if (isNotNull($sql)) {

					$rules = [
						'form.user_status' => [
							'label' => lang('AdminSettings.managerAccounts.general.status'),
							'rules' => 'required'
						],
						'form.user_type_id' => [
							'label' => lang('AdminSettings.managerAccounts.general.type'),
							'rules' => 'required'
						],
						'form.user_name_surname' => [
							'label' => lang('AdminSettings.managerAccounts.general.nameSurname'),
							'rules' => 'required'
						],
						'form.user_email_address' => [
							'label' => lang('AdminSettings.managerAccounts.general.emailAddress'),
							'rules' => 'required|min_length['.FORM_EMAIL_MIN_LENGTH.']|max_length['.FORM_EMAIL_MAX_LENGTH.']|valid_email'
						]
					];

					/*****************************************************/

					// Image Upload Validation
					$file = $this->request->getFile('user_image');
					if (isNotNull($file)) {
						$rulesImage = [
							'user_image' => [
								'label' => lang('AdminSettings.managerAccounts.general.image'),
								'rules' => [
									'uploaded[user_image]',
									'mime_in[user_image,'.IMAGE_UPLOAD_MIME.']',
									'max_size[user_image,'.IMAGE_UPLOAD_SIZE.']'
								]
							]
						];

						$rules = array_merge($rules, $rulesImage);
					}

					/*****************************************************/

					if ($this->validate($rules)) {

						// Image Upload
						$fileName = $sql->user_image;
						$file = $this->request->getFile('user_image');
						if (isNotNull($file) && $file->isValid() && !$file->hasMoved()) {
							// Unlink
							unlinkFile($this->filePath, $sql->user_image);

							$fileName = slug($this->request->getVar('form[user_name_surname]')).'_'.$file->getRandomName();
							$resize = \Config\Services::image()
								->withFile($file)
								->withResource(IMAGE_UPLOAD_QUALITY)
								->resize(120, 120, TRUE, 'height')
								->save($this->filePath.'/'.$fileName);
						}

						$data = [];
						foreach ($this->request->getVar('form') as $key => $value) {
							$data[$key] = $value;

							if (isNotNull($this->request->getVar('form[user_telephone]'))) {
								$data['user_telephone'] = phoneFormat($this->request->getVar('form[user_telephone]'));
							}

							$data['user_image'] = $fileName;
						}

						$result = $this->general->updateModel($this->table, $data, ['user_id' => $user_id]);
						if ($result !== FALSE) {

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminSettings.result.edit.managerAccounts', [$this->request->getVar('form[user_name_surname]')]));

							$ajax_message['success'] = TRUE;

							if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
								$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/edit/'.$user_id);
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

	public function delete(int $user_id) {
		$sql = $this->SettingModel->managerAccountsInfoModel($user_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['user_id' => $user_id]);
			if ($delete) {
				// Unlink
				unlinkFile($this->filePath, $sql->user_image);

				$ajax_message['success'] = TRUE;
			} else {
				$ajax_message['error'] = lang('Admin.error.delete');
			}

		} else {
			$ajax_message['error'] = lang('Admin.error.noRecord');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function removeImage(int $user_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$result = $this->general->removeDropifyImageModel($this->table, ['user_image' => ''], ['user_id' => $user_id], $this->filePath);
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

	public function changePassword(int $user_id) {
		$sql = $this->SettingModel->managerAccountsInfoModel($user_id);
		if (isNotNull($sql)) {

			$result = $this->SettingModel->managerAccountsInfoModel($user_id);

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'change-password',
				'page_url' => $this->pageUrl,
				'result' => [
					'user_id' => $result->user_id,
					'user_name_surname' => $result->user_name_surname
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function changePasswordUpdate(int $user_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'new_password' => [
						'label' => lang('AdminSettings.managerAccounts.changePassword.newPassword.title'),
						'rules' => 'required|min_length['.FORM_PASSWORD_MIN_LENGTH.']|max_length['.FORM_PASSWORD_MAX_LENGTH.']'
					],
					'confirm_password' => [
						'label' => lang('AdminSettings.managerAccounts.changePassword.newPassword.again'),
						'rules' => 'required|min_length['.FORM_PASSWORD_MIN_LENGTH.']|max_length['.FORM_PASSWORD_MAX_LENGTH.']|matches[new_password]'
					]
				];

				if ($this->validate($rules)) {

					$old_password = $this->request->getVar('old_password');
					$new_password = $this->request->getVar('new_password');

					$sql = $this->SettingModel->managerAccountsInfoModel($user_id);
					if (isNotNull($sql)) {

						$result = $this->general->updateModel($this->table, ['user_password' => password_hash($new_password, PASSWORD_DEFAULT)], ['user_id' => $user_id]);
						if ($result !== FALSE) {

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminSettings.result.edit.managerAccountsChangePassword', [$sql->user_name_surname]));

							$ajax_message['success'] = TRUE;
							$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);

						} else {
							$ajax_message['error'] = lang('Admin.error.insert');
						}

					} else {
						$ajax_message['error'] = lang('Admin.error.description');
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
}
