<?php
namespace App\Libraries;
use App\Controllers\Backend\BaseController;

use App\Models\Backend\GeneralModel;
use App\Models\Backend\SettingModel;

use App\Libraries\Smtp;

class EmailTemplates extends BaseController {

	protected $general;
	protected $SettingModel;
	protected $defaultLangId;
	protected $settings;
	protected $designSettings;
	protected $Smtp;
	protected $Twig;

	public function __construct() {
		$this->general = new GeneralModel();
		$this->SettingModel = new SettingModel();
		$this->defaultLangId = $this->general->languagesDefaultModel()[0];
		if (isNotNull($this->defaultLangId)) {
			$this->settings = $this->general->getSettingsModel($this->defaultLangId);
		}

		$this->designSettings = $this->general->getDesignSettingsModel();
		$this->Smtp = new Smtp();
	}

	public function manualTemplate($email_title, $email_content, $email_address) {
		$this->Twig();

		$html = $this->twig->render($this->backendTemplatePath().'/layouts/email-template.html', [
			'result' => [
				'email_title' => $email_title,
				'email_content' => $email_content,
				'logo' => isNotNull($this->designSettings->logo) ? base_url(FILE_PATH_MAIN.'/'.$this->designSettings->logo) : NULL,
				'social_media' => $this->SettingModel->socialMediaModel(),
				'contact' => $this->general->contactDefaultModel($this->defaultLangId)
			]
		]);

		/*****************************************************/

		// Customer E-Mail
		if (isNotNull($email_address)) {
			$smtp_email = $this->Smtp->index($email_title, $html, $email_address);
			$smtp_status = $smtp_email[0];
			$smtp_error = $smtp_email[1];
		} else {
			$smtp_status = FALSE;
			$smtp_error = lang('Admin.noEmailAddress');
		}

		/*****************************************************/

		if ($smtp_status !== FALSE) {
			return [TRUE, NULL];
		} else {
			return [FALSE, $smtp_error];
		}
	}

	public function customTemplate(int $id, array $datas = [], string $attachment = NULL) {
		$this->Twig();

		$sql = $this->SettingModel->emailTemplatesInfoModel($id, $this->defaultLangId);
		if (isNotNull($sql)) {

			// Datas
			$tables = NULL;
			if (isNotNull($datas)) {
				foreach ($datas as $row) {
					$tables .= $row.',';
				}

				$tables = reduce_multiples($tables, ',', TRUE);
			}

			/*****************************************************/

			$email_content = NULL;
			$find = NULL;
			$change = NULL;
			$customer_email = NULL;
			$explode = explode(',', $tables);

			/*****************************************************/

			// Contact Requests
			if ($id == EMAIL_TEMPLATES_CONTACT_REQUESTS) {
				$find = [
					TMP_CONTACT_REQUESTS_NAME,
					TMP_CONTACT_REQUESTS_SURNAME,
					TMP_CONTACT_REQUESTS_TELEPHONE,
					TMP_CONTACT_REQUESTS_EMAIL,
					TMP_CONTACT_REQUESTS_MESSAGE,
					TMP_CONTACT_REQUESTS_DATE,
					TMP_GENERAL_SITE_TELEPHONE,
					TMP_GENERAL_SITE_EMAIL
				];
				$change = [$explode[0], $explode[1], $explode[2], $explode[3], $explode[4], $explode[5], $this->settings->site_telephone, $this->settings->site_email];

				$customer_email = $explode[3];
			}

			// President Contact Requests
			if ($id == EMAIL_TEMPLATES_PRESIDENT_CONTACT_REQUESTS) {
				$find = [
					TMP_OFFERS_NAME,
					TMP_OFFERS_SURNAME,
					TMP_OFFERS_TELEPHONE,
					TMP_OFFERS_EMAIL,
					TMP_OFFERS_MESSAGE,
					TMP_OFFERS_DATE,
					TMP_GENERAL_SITE_TELEPHONE,
					TMP_GENERAL_SITE_EMAIL
				];
				$change = [$explode[0], $explode[1], $explode[2], $explode[3], $explode[4], $explode[5], $this->settings->site_telephone, $this->settings->site_email];

				$customer_email = $explode[3];
			}

			/*****************************************************/

			$email_content = str_replace($find, $change, $sql->email_template_description);

			$html = $this->twig->render($this->backendTemplatePath().'/layouts/email-template.html', [
				'result' => [
					'email_title' => $sql->email_template_name,
					'email_content' => $email_content,
					'logo' => isNotNull($this->designSettings->logo) ? base_url(FILE_PATH_MAIN.'/'.$this->designSettings->logo) : NULL,
					'social_media' => $this->SettingModel->socialMediaModel(),
					'contact' => $this->general->contactDefaultModel($this->defaultLangId)
				]
			]);

			/*****************************************************/

			// Customer E-Mail
			$smtp_status = TRUE;
			if ($sql->send_to_customer == FORM_ACTIVE_NUMBER && isNotNull($customer_email)) {
				$smtp_email = $this->Smtp->index($sql->email_template_name, $html, $customer_email, $attachment);
				$smtp_status = $smtp_email[0];
				$smtp_error = $smtp_email[1];
			}

			// Admin E-Mail
			if ($sql->send_to_admin == FORM_ACTIVE_NUMBER && isNotNull($this->settings->site_email)) {
				$this->Smtp->index($sql->email_template_name, $html, $this->settings->site_email, $attachment);
			}

			/*****************************************************/

			if ($smtp_status == TRUE) {
				return [TRUE, NULL];
			} else {
				return [FALSE, $smtp_error];
			}

		} else {
			return [FALSE, lang('AdminSettings.templatesVariableList.alert.recordNotFoundOrInactive')];
		}
	}
}
