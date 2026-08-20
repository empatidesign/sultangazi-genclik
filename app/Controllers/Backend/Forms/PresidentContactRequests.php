<?php
namespace App\Controllers\Backend\Forms;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\Forms\PresidentContactRequestsModel;
use App\Models\Backend\DatatableModel;
use App\Libraries\EmailTemplates;

class PresidentContactRequests extends BaseController {

	protected $table;
	protected $pageUrl;
	protected $PresidentContactRequestsModel;
	protected $DatatableModel;
	protected $EmailTemplates;

	public function __construct() {
		$this->table = 'president_contact_requests';
		$this->pageUrl = ADMIN_URL_FORMS.'/'.ADMIN_URL_PRESIDENT_CONTACT_REQUESTS;
		$this->PresidentContactRequestsModel = new PresidentContactRequestsModel();
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
			$column = ['status', 'president_contact_request_name', 'president_contact_request_surname', 'president_contact_request_telephone', 'president_contact_request_email', 'president_contact_request_created_date', 'president_contact_request_created_ip', NULL];
			$search = ['president_contact_request_surname', 'president_contact_request_telephone', 'president_contact_request_email', 'president_contact_request_created_date'];
			$orderBy = ['president_contact_request_id' => 'DESC'];
			$where = [];

			$list = $this->DatatableModel->GetDatatables($this->table, $column, $search, $orderBy, $where, 'getResult');
			$data = [];
			if (isNotNull($list)) {
				foreach ($list as $row) {
					$array = [];
					$array[] = set_status($row->status);
					$array[] = $row->president_contact_request_name;
					$array[] = $row->president_contact_request_surname;
					$array[] = $row->president_contact_request_telephone;
					$array[] = $row->president_contact_request_email;
					$array[] = dateFormat($row->president_contact_request_created_date, 'd-m-Y H:i:s');
					$array[] = $row->president_contact_request_created_ip;
					$array[] = action_links($row->president_contact_request_id, ['detail', 'delete'], $this->pageUrl);
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

	public function detail(int $president_contact_request_id) {
		$sql = $this->PresidentContactRequestsModel->presidentContactRequestInfoModel($president_contact_request_id);
		if (isNotNull($sql)) {

			return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
				'page_name' => 'detail',
				'page_url' => $this->pageUrl,
				'result' => [
					'president_contact_request_id' => $sql->president_contact_request_id,
					'status' => $sql->status,
					'president_contact_request_name' => $sql->president_contact_request_name,
					'president_contact_request_surname' => $sql->president_contact_request_surname,
					'president_contact_request_telephone' => $sql->president_contact_request_telephone,
					'president_contact_request_email' => $sql->president_contact_request_email,
					'president_contact_request_message' => $sql->president_contact_request_message,
					'admin_notes' => $sql->admin_notes,
					'president_contact_request_answer' => $sql->president_contact_request_answer,
					'president_contact_request_answer_date' => $sql->president_contact_request_answer_date != '0000-00-00 00:00:00' ? dateFormat($sql->president_contact_request_answer_date, 'd/m/Y H:i:s') : NULL
				]
			]);

		} else {
			return redirect()->to(BACKEND_URL.'/404');
		}
	}

	public function update(int $president_contact_request_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$sql = $this->PresidentContactRequestsModel->presidentContactRequestInfoModel($president_contact_request_id);
				if (isNotNull($sql)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;

						if (isNotNull($this->request->getVar('form[president_contact_request_answer]'))) {
							$data['status'] = FORM_EMAIL_NOTIFICATION;
							$data['president_contact_request_answer_date'] = nowDate();
						}
					}

					/*****************************************************/

					// E-Mail
					$email_status = TRUE;
					if (isNotNull($this->request->getVar('form[president_contact_request_answer]'))) {
						$email_template = $this->EmailTemplates->manualTemplate(lang('Admin.smtp.title.presidentContactRequests'), $this->request->getVar('form[president_contact_request_answer]'), $sql->president_contact_request_email);
						$email_status = $email_template[0];
						$email_error = $email_template[1];
					}

					/*****************************************************/

					if ($email_status !== FALSE) {

						$result = $this->general->updateModel($this->table, $data, ['president_contact_request_id' => $president_contact_request_id]);
						if ($result !== FALSE) {

							// Flash Data
							session()->setFlashdata('flashDataMessageSuccess', lang('AdminForms.result.edit.presidentContactRequests', [$sql->president_contact_request_name, $sql->president_contact_request_surname]));

							$ajax_message['success'] = TRUE;

							if ($this->request->getVar('redirect') == FORM_BUTTON_REDIRECT_PAGE1) {
								$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'/detail/'.$president_contact_request_id);
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

	public function delete(int $president_contact_request_id) {
		$sql = $this->PresidentContactRequestsModel->presidentContactRequestInfoModel($president_contact_request_id);
		if (isNotNull($sql)) {

			$delete = $this->general->deleteModel($this->table, ['president_contact_request_id' => $president_contact_request_id]);
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
