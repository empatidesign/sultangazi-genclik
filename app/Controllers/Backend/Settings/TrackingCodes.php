<?php
namespace App\Controllers\Backend\Settings;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\SettingModel;

class TrackingCodes extends BaseController {

	protected $pageUrl;
	protected $SettingModel;

	public function __construct() {
		$this->pageUrl = ADMIN_URL_SETTINGS.'/'.ADMIN_URL_TRACKING_CODES;
		$this->SettingModel = new SettingModel();
	}

	public function index() {
		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_url' => $this->pageUrl,
			'result' => $this->SettingModel->trackingCodesModel()
		]);
	}

	public function update() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$data = [];
				foreach ($this->request->getVar('form') as $key => $value) {
					$data[$key] = $value;
				}

				$result = $this->general->updateModel('tracking_codes', $data, ['tracking_code_id' => TRACKING_CODE_ID]);
				if ($result !== FALSE) {

					// Flash Data
					session()->setFlashdata('flashDataMessageSuccess', lang('AdminSettings.result.edit.trackingCodes'));

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
