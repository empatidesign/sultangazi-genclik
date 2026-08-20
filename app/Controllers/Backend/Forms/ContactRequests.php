<?php
namespace App\Controllers\Backend\Forms;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Forms\ContactRequestsModel;
use App\Models\Backend\DatatableModel;
use App\Libraries\EmailTemplates;

class ContactRequests extends BaseController {

	protected $table;
	protected $pageUrl;
	protected $ContactRequestsModel;
	protected $DatatableModel;
	protected $EmailTemplates;

	public function __construct() {
		$this->table = 'contact_requests';
		$this->pageUrl = ADMIN_URL_FORMS.'/'.ADMIN_URL_CONTACT_REQUESTS;
		$this->ContactRequestsModel = new ContactRequestsModel();
		$this->DatatableModel = new DatatableModel();
		$this->EmailTemplates = new EmailTemplates();
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
			$column = ['status', 'contact_form_name', 'contact_form_surname', 'contact_form_telephone', 'contact_form_email', 'contact_form_created_date', 'contact_form_created_ip', NULL];
			$search = ['contact_form_name_surname', 'contact_form_telephone', 'contact_form_email', 'contact_form_created_date'];
			$orderBy = ['contact_form_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = $row->contact_form_name;
					$array[] = $row->contact_form_surname;
					$array[] = $row->contact_form_telephone;
					$array[] = $row->contact_form_email;
					$array[] = dateFormat($row->contact_form_created_date, 'd-m-Y H:i:s');
					$array[] = $row->contact_form_created_ip;
					$array[] = action_links($row->contact_form_id, ['detail', 'delete'], $this->pageUrl);
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

	public function detail(int $contact_form_id) {
		$sql = $this->ContactRequestsModel->contactInfoModel($contact_form_id);
		if (isNotNull($sql)) {

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'detail',
				'page_url' => $this->pageUrl,
				'result' => [
					'contact_form_id' => $sql->contact_form_id,
					'status' => $sql->status,
					'contact_form_name' => $sql->contact_form_name,
					'contact_form_surname' => $sql->contact_form_surname,
					'contact_form_company_name' => $sql->contact_form_company_name,
					'contact_form_telephone' => $sql->contact_form_telephone,
					'contact_form_email' => $sql->contact_form_email,
					'contact_form_message' => $sql->contact_form_message,
					'admin_notes' => $sql->admin_notes,
					'contact_form_answer' => $sql->contact_form_answer,
					'contact_form_answer_date' => $sql->contact_form_answer_date != '0000-00-00 00:00:00' ? dateFormat($sql->contact_form_answer_date, 'd/m/Y H:i:s') : NULL
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $contact_form_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->ContactRequestsModel->contactInfoModel($contact_form_id);
				if (isNotNull($sql)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;

						if (isNotNull($this->request->getVar('form[contact_form_answer]'))) {
							$data['status'] = FORM_EMAIL_NOTIFICATION;
							$data['contact_form_answer_date'] = nowDate();
						}
					}

					/*****************************************************/

					// E-Mail
					$email_status = TRUE;
					if (isNotNull($this->request->getVar('form[contact_form_answer]'))) {
						$email_template = $this->EmailTemplates->manualTemplate(lang('Admin.smtp.title.contactRequest'), $this->request->getVar('form[contact_form_answer]'), $sql->contact_form_email);
						$email_status = $email_template[0];
						$email_error = $email_template[1];
					}

					/*****************************************************/

					if ($email_status !== FALSE) {

						$result = $this->general->updateModel($this->table, $data, ['contact_form_id' => $contact_form_id]);
						if ($result !== FALSE) {

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminForms.result.edit.contactRequests', [$sql->contact_form_name, $sql->contact_form_surname]));

							$ajax_message['success'] = TRUE;

							if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
								$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/detail/'.$contact_form_id);
							} else {
								$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);
							}

						} else {
							$ajax_message['error'] = lang('Admin.error.update');
						}

					} else {
						$ajax_message['error'] = $email_error;
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

	public function delete(int $contact_form_id) {
		$sql = $this->ContactRequestsModel->contactInfoModel($contact_form_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['contact_form_id' => $contact_form_id]);
			if ($delete) {
				$ajax_message['success'] = TRUE;
			} else {
				$ajax_message['error'] = lang('Admin.error.delete');
			}

		} else {
			$ajax_message['error'] = lang('Admin.error.noRecord');
		}

		return $this->response->setJSON($ajax_message);
	}
}
