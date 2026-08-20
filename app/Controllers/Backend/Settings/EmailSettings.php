<?php
namespace App\Controllers\Backend\Settings;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\SettingModel;

class EmailSettings extends BaseController {

	protected $pageUrl;
	protected $SettingModel;

	public function __construct() {
		$this->pageUrl = ADMIN_URL_SETTINGS.'/'.ADMIN_URL_EMAIL_SETTINGS;
		$this->SettingModel = new SettingModel();
	}

	public function index() {
		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_url' => $this->pageUrl,
			'result' => [
				'smtp_email_title' => $this->settings->smtp_email_title,
				'smtp_host' => $this->settings->smtp_host,
				'smtp_email' => $this->settings->smtp_email,
				'smtp_password' => $this->settings->smtp_password,
				'smtp_port' => $this->settings->smtp_port,
				'smtp_crypto' => $this->settings->smtp_crypto
			]
		]);
	}

	public function update() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$data = [];
				foreach ($this->request->getVar('form') as $key => $value) {
					$data[$key] = $value;
					$data['smtp_host'] = removeWhiteSpaces($this->request->getVar('form[smtp_host]'));
					$data['smtp_email'] = removeWhiteSpaces($this->request->getVar('form[smtp_email]'));
					$data['smtp_password'] = removeWhiteSpaces($this->request->getVar('form[smtp_password]'));
					$data['smtp_port'] = removeWhiteSpaces($this->request->getVar('form[smtp_port]'));
				}

				$result = $this->SettingModel->update(SETTING_ID, $data);
				if ($result !== FALSE) {

					// Flash Data
					session()->setFlashdata('flashDataMessageSuccess', lang('AdminSettings.result.edit.emailSettings'));

					$ajax_message['success'] = TRUE;
					$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);

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
}
