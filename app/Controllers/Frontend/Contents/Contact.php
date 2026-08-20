<?php
namespace App\Controllers\Frontend\Contents;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\Contents\ContactModel;
use App\Libraries\EmailTemplates;
use App\Libraries\GoogleReCaptcha;

class Contact extends BaseController {

	protected $table;
	protected $ContactModel;
	protected $EmailTemplates;
	protected $GoogleReCaptcha;

	public function __construct() {
		$this->table = 'contact_requests';
		$this->ContactModel = new ContactModel();
		$this->EmailTemplates = new EmailTemplates();
		$this->GoogleReCaptcha = new GoogleReCaptcha();
	}

	public function index() {
		return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/contents/contact.html', [
			'head' => [
				'title' => lang('WebContact.title'),
				'keywords' => $this->settings->site_keywords,
				'description' => $this->settings->site_description
			],
			'list' => [
				'agreement' => $this->ContactModel->agreementModel($this->defaultLangId)
			],
			'PARAMETER' => [
				'WEB_URL_CONTACT_SEND' => WEB_URL_CONTACT_SEND,
				'FILE_PATH_CONTACT' => FILE_PATH_CONTACT
			]
		]);
	}

	public function send() {
		return;
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'email_address' => [
						'label' => lang('Web.general.form.emailAddress'),
						'rules' => 'min_length['.FORM_EMAIL_MIN_LENGTH.']|max_length['.FORM_EMAIL_MAX_LENGTH.']|valid_email'
					]
				];

				if ($this->validate($rules)) {

					// Google Recaptcha
					$recaptcha = TRUE;
					if ($this->settings->google_recaptcha_status == FORM_CHECKBOX_VALUE_NUMBER and $this->settings->google_recaptcha_page_contact == FORM_CHECKBOX_VALUE_NUMBER) {
						$recaptcha = $this->GoogleReCaptcha->index($this->request->getVar('g-recaptcha-response'));
					}

					if ($recaptcha == TRUE) {

						$name = trim(strip_tags(upper($this->request->getVar('name'))));
						$surname = trim(strip_tags(upper($this->request->getVar('surname'))));
						$telephone = strip_tags(removeWhiteSpaces($this->request->getVar('telephone')));
						$email_address = strip_tags(removeWhiteSpaces($this->request->getVar('email_address')));
						$message = trim(strip_tags($this->request->getVar('message')));
						$date = nowDate();

						$data = [
							'contact_form_name' => $name,
							'contact_form_surname' => $surname,
							'contact_form_telephone' => $telephone,
							'contact_form_email' => $email_address,
							'contact_form_message' => $message,
							'contact_form_created_date' => $date,
							'contact_form_created_ip' => $this->request->getIPAddress()
						];

						/*****************************************************/

						// E-Mail
						$email_status = TRUE;
						if (isNotNull($email_address)) {
							$email_template = $this->EmailTemplates->customTemplate(EMAIL_TEMPLATES_CONTACT_REQUESTS, [$name, $surname, $telephone, $email_address, $message, dateFormat($date, 'd-m-Y H:i:s')]);
							$email_status = $email_template[0];
							$email_error = $email_template[1];
						}

						/*****************************************************/

						//if ($email_status !== FALSE) {

							$result = $this->general->insertModel($this->table, $data);
							if ($result !== FALSE) {
								$ajax_message['success'] = lang('WebContact.form.alert.success');
							} else {
								$ajax_message['error'] = lang('Web.error.insert');
							}

						//} else {
							//$ajax_message['error'] = $email_error;
						//}

					} else {
						$ajax_message['error'] = lang('Web.error.captcha');
					}

				} else {
					$ajax_message['error'] = $this->validator->listErrors();
				}

			} else {
				$ajax_message['error'] = lang('Web.error.description');
			}
		} else {
			$ajax_message['error'] = lang('Web.error.ajax');
		}

		return $this->response->setJSON($ajax_message);
	}
}
