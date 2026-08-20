<?php
namespace App\Controllers\Backend\Settings;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\SettingModel;
use App\Models\Backend\Designs\MenuManagementModel;

class GeneralSettings extends BaseController {

	protected $pageUrl;
	protected $SettingModel;
	protected $MenuManagementModel;

	public function __construct() {
		$this->pageUrl = ADMIN_URL_SETTINGS.'/'.ADMIN_URL_GENERAL_SETTINGS;
		$this->SettingModel = new SettingModel();
		$this->MenuManagementModel = new MenuManagementModel();
	}

	public function index() {

		$lang_array = [];
		$lang = $this->SettingModel->settingsLangModel();
		if (isNotNull($lang)) {
			foreach ($lang as $row) {
				$lang_array['data']['translations'][$row->lang_id]['site_footer'] = $row->site_footer;
				$lang_array['data']['translations'][$row->lang_id]['cookies_description'] = $row->cookies_description;
			}
		}

		// Contracts
		$contracts_array = [];
		$contractsList = $this->MenuManagementModel->menuManagementContractsListModel($this->defaultLangId);
		if (isNotNull($contractsList)) {
			foreach ($contractsList as $row) {
				$contracts_array[] = [
					'id' => $row->contract_id,
					'name' => $row->contract_name
				];
			}
		}

		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_url' => $this->pageUrl,
			'lang' => $lang_array,
			'list' => [
				'contracts' => $contracts_array
			],
			'PARAMETER' => [
				'GENERAL_SETTINGS_DESCRIPTION_LIMITER' => GENERAL_SETTINGS_DESCRIPTION_LIMITER
			]
		]);
	}

	public function update() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$rules1 = [];
				$rules2 = [];

				if (isNotNull($this->request->getVar('form[site_email]'))) {
					$rules1 = [
						'form.site_email' => [
							'label' => lang('AdminSettings.generalSettings.siteEmailAddress'),
							'rules' => 'min_length['.FORM_EMAIL_MIN_LENGTH.']|max_length['.FORM_EMAIL_MAX_LENGTH.']|valid_email'
						]
					];
				}

				if (isNotNull($this->request->getVar('form[company_email]'))) {
					$rules2 = [
						'form.company_email' => [
							'label' => lang('AdminSettings.generalSettings.company.emailAddress'),
							'rules' => 'min_length['.FORM_EMAIL_MIN_LENGTH.']|max_length['.FORM_EMAIL_MAX_LENGTH.']|valid_email'
						]
					];
				}

				$rules = array_merge($rules1, $rules2);

				if ($this->validate($rules)) {

					$data = [];
					foreach ($this->request->getVar('form') as $key => $value) {
						$data[$key] = $value;
						$data['site_telephone'] = phoneFormat($this->request->getVar('form[site_telephone]'));
						$data['company_telephone'] = phoneFormat($this->request->getVar('form[company_telephone]'));
						$data['company_kep_address'] = trim($this->request->getVar('form[company_kep_address]'));
						$data['seo_google_meta'] = $this->request->getVar('form[seo_google_meta]');
						$data['seo_canonical_url'] = $this->request->getVar('form[seo_canonical_url]');
						$data['seo_facebook_open_graph_meta'] = $this->request->getVar('form[seo_facebook_open_graph_meta]');
						$data['seo_twitter_meta'] = $this->request->getVar('form[seo_twitter_meta]');
						$data['google_recaptcha_status'] = $this->request->getVar('form[google_recaptcha_status]');
						$data['google_recaptcha_key'] = removeWhiteSpaces($this->request->getVar('form[google_recaptcha_key]'));
						$data['google_recaptcha_secret'] = removeWhiteSpaces($this->request->getVar('form[google_recaptcha_secret]'));
						$data['google_recaptcha_page_contact'] = removeWhiteSpaces($this->request->getVar('form[google_recaptcha_page_contact]'));
						$data['google_recaptcha_page_president_contact'] = removeWhiteSpaces($this->request->getVar('form[google_recaptcha_page_president_contact]'));
						$data['google_map_api_key'] = removeWhiteSpaces($this->request->getVar('form[google_map_api_key]'));
						$data['cookies_status'] = $this->request->getVar('form[cookies_status]');

						// Remove Session Lang
						session()->remove(['lang']);
					}

					$result = $this->SettingModel->update(SETTING_ID, $data);
					if ($result !== FALSE) {

						// Lang
						if (isNotNull($this->request->getVar('lang'))) {
							foreach ($this->request->getVar('lang') as $lang_id => $value) {
								$lang_data = [
									'setting_id' => SETTING_ID,
									'lang_id' => $lang_id,
									'site_footer' => $value['site_footer'],
									'cookies_description' => $value['cookies_description']
								];

								$langControlModel = $this->SettingModel->settingsLangControlModel(SETTING_ID, $lang_id);
								if (isNotNull($langControlModel)) {
									$this->general->updateModel('settings_lang', $lang_data, ['setting_id' => SETTING_ID, 'lang_id' => $lang_id]);
								} else {
									$this->general->insertModel('settings_lang', $lang_data);
								}
							}
						}

						// Robots.txt
						file_put_contents(FCPATH.'robots.txt', $this->request->getVar('form[seo_robots]'));

						// Flash Data
						session()->setFlashdata('flashDataMessageSuccess', lang('AdminSettings.result.edit.generalSettings'));

						$ajax_message['success'] = TRUE;
						$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);

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
