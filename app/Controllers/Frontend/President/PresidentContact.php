<?php
namespace App\Controllers\Frontend\President;
use App\Controllers\Frontend\BaseController;
use CodeIgniter\Controller;

use App\Models\Frontend\President\PresidentContactModel;
use App\Models\Frontend\Contents\CorporateModel;
use App\Libraries\EmailTemplates;
use App\Libraries\GoogleReCaptcha;

class PresidentContact extends BaseController {

	protected $table;
	protected $PresidentContactModel;
	protected $CorporateModel;
	protected $EmailTemplates;
	protected $GoogleReCaptcha;
	protected $folder;

	public function __construct() {
		$this->table = 'president_contact_requests';
		$this->PresidentContactModel = new PresidentContactModel();
		$this->CorporateModel = new CorporateModel();
		$this->EmailTemplates = new EmailTemplates();
		$this->GoogleReCaptcha = new GoogleReCaptcha();
		$this->folder = 'contents';
	}

	public function index() {

		// Segment
		$segment = $this->request->getUri()->getTotalSegments() > 2 ? $this->request->getUri()->getSegment(2).'/'.$this->request->getUri()->getSegment(3) : $this->request->getUri()->getSegment(1).'/'.$this->request->getUri()->getSegment(2);

		return $this->twig->render($this->FRONTEND_TEMPLATE_PATH.'/president/president-contact.html', [
			'head' => [
				'title' => lang('WebPresident.form.title'),
				'keywords' => $this->settings->site_keywords,
				'description' => $this->settings->site_description
			],
			'result' => [
				'president' => [
					'informations' => $this->informations()
				]
			],
			'list' => [
				'agreement' => $this->PresidentContactModel->agreementModel($this->defaultLangId),
				'left_menu' => $this->CorporateModel->leftMenuSlugInfoModel($segment, $this->defaultLangId)
			],
			'PARAMETER' => [
				'WEB_URL_PRESIDENT_CONTACT_SEND' => WEB_URL_PRESIDENT_CONTACT_SEND
			],
			'folder' => $this->folder
		]);
	}

	public function send() {
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
					if ($this->settings->google_recaptcha_status == FORM_CHECKBOX_VALUE_NUMBER and $this->settings->google_recaptcha_page_president_contact == FORM_CHECKBOX_VALUE_NUMBER) {
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
							'president_contact_request_name' => $name,
							'president_contact_request_surname' => $surname,
							'president_contact_request_telephone' => $telephone,
							'president_contact_request_email' => $email_address,
							'president_contact_request_message' => $message,
							'president_contact_request_created_date' => $date,
							'president_contact_request_created_ip' => $this->request->getIPAddress()
						];

						/*****************************************************/

						// E-Mail
						$email_status = TRUE;
						if (isNotNull($email_address)) {
							$email_template = $this->EmailTemplates->customTemplate(EMAIL_TEMPLATES_PRESIDENT_CONTACT_REQUESTS, [$name, $surname, $telephone, $email_address, $message, dateFormat($date, 'd-m-Y H:i:s')]);
							$email_status = $email_template[0];
							$email_error = $email_template[1];
						}

						/*****************************************************/

						if ($email_status !== FALSE) {

							$result = $this->general->insertModel($this->table, $data);
							if ($result !== FALSE) {
								$ajax_message['success'] = lang('WebPresident.form.alert.success');
							} else {
								$ajax_message['error'] = lang('Web.error.insert');
							}

						} else {
							$ajax_message['error'] = $email_error;
						}

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

	public function informations() {
		$data = [];
		$sql = $this->general->getPresidentGeneralInformationsModel($this->defaultLangId);
		if (isNotNull($sql)) {
			$data = [
				'president_name_surname' => $sql->president_name_surname,
        'president_sub_title' => $sql->president_general_information_sub_title,
				'president_image' => [
					'base' => isNotNull($sql->president_image) ? $this->sultanImageControl(FILE_PATH_PRESIDENT, $sql->president_image) : NULL
				],
				'president_facebook' => $sql->president_facebook,
				'president_twitter' => $sql->president_twitter,
				'president_instagram' => $sql->president_instagram,
				'president_youtube' => $sql->president_youtube
			];
		}

		return $data;
	}
}
