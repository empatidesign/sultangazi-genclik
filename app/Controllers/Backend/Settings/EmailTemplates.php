<?php
namespace App\Controllers\Backend\Settings;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\SettingModel;

class EmailTemplates extends BaseController {

	protected $pageUrl;
	protected $SettingModel;

	public function __construct() {
		$this->pageUrl = ADMIN_URL_SETTINGS.'/'.ADMIN_URL_EMAIL_TEMPLATES;
		$this->SettingModel = new SettingModel();
	}

	public function index() {
		$action = $this->request->getVar('action');

		$lang_array = [];
		$lang = $this->SettingModel->emailTemplatesLangModel($action);
		if (isNotNull($lang)) {
			foreach ($lang as $row) {
				$lang_array['data']['translations'][$row->lang_id]['email_template_name'] = $row->email_template_name;
				$lang_array['data']['translations'][$row->lang_id]['email_template_description'] = $row->email_template_description;
			}
		}

		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_url' => $this->pageUrl,
			'lang' => $lang_array,
			'result' => $this->SettingModel->emailTemplatesDetailModel($action, $this->defaultLangId),
			'action' => $action,
			'list' => [
				'email_templates' => $this->SettingModel->emailTemplatesListModel($this->defaultLangId)
			],
			'PARAMETER' => [
				'CONTACT_REQUESTS' => [
					'TMP_CONTACT_REQUESTS_NAME' => TMP_CONTACT_REQUESTS_NAME,
					'TMP_CONTACT_REQUESTS_SURNAME' => TMP_CONTACT_REQUESTS_SURNAME,
					'TMP_CONTACT_REQUESTS_TELEPHONE' => TMP_CONTACT_REQUESTS_TELEPHONE,
					'TMP_CONTACT_REQUESTS_EMAIL' => TMP_CONTACT_REQUESTS_EMAIL,
					'TMP_CONTACT_REQUESTS_MESSAGE' => TMP_CONTACT_REQUESTS_MESSAGE,
					'TMP_CONTACT_REQUESTS_DATE' => TMP_CONTACT_REQUESTS_DATE
				],
				'OFFERS' => [
					'TMP_OFFERS_NAME' => TMP_OFFERS_NAME,
					'TMP_OFFERS_SURNAME' => TMP_OFFERS_SURNAME,
					'TMP_OFFERS_TELEPHONE' => TMP_OFFERS_TELEPHONE,
					'TMP_OFFERS_EMAIL' => TMP_OFFERS_EMAIL,
					'TMP_OFFERS_MESSAGE' => TMP_OFFERS_MESSAGE,
					'TMP_OFFERS_DATE' => TMP_OFFERS_DATE
				],
				'GENERAL' => [
					'TMP_GENERAL_SITE_TELEPHONE' => TMP_GENERAL_SITE_TELEPHONE,
					'TMP_GENERAL_SITE_EMAIL' => TMP_GENERAL_SITE_EMAIL
				]
			]
		]);
	}

	public function update(int $email_template_id) {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules = [
					'form.status' => [
						'label' => lang('AdminSettings.emailTemplates.general.status'),
						'rules' => 'required'
					],
					'lang.'.$this->defaultLangId.'.email_template_name' => [
						'label' => lang('AdminSettings.emailTemplates.general.name'),
						'rules' => 'required'
					]
				];

				if ($this->validate($rules)) {
					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
					}

					$result = $this->general->updateModel('email_templates', $data, ['email_template_id' => $email_template_id]);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {
								$lang_data = [
									'email_template_id' => $email_template_id,
									'lang_id' => $lang_id,
									'email_template_name' => $value['email_template_name'],
									'email_template_description' => $value['email_template_description']
								];

								$langControlModel = $this->SettingModel->emailTemplatesLangControlModel($email_template_id, $lang_id);
								if (isNotNull($langControlModel)) {
									$this->general->updateModel('email_templates_lang', $lang_data, ['email_template_id' => $email_template_id, 'lang_id' => $lang_id]);
								} else {
									$this->general->insertModel('email_templates_lang', $lang_data);
								}
							}
						}

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminSettings.result.edit.emailTemplates', [$this->request->getVar('lang['.$this->defaultLangId.'][email_template_name]')]));

						$ajax_message['success'] = TRUE;
						$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl.'?action='.$email_template_id);

					} else {
						$ajax_message['error'] = lang('Admin.error.update');
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
