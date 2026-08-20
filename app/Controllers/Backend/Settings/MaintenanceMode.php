<?php
namespace App\Controllers\Backend\Settings;
use App\Controllers\Backend\BaseController;
use CodeIgniter\Controller;

use App\Models\Backend\SettingModel;

class MaintenanceMode extends BaseController {

	protected $pageUrl;
	protected $SettingModel;

	public function __construct() {
		$this->pageUrl = ADMIN_URL_SETTINGS.'/'.ADMIN_URL_MAINTENANCE_MODE;
		$this->SettingModel = new SettingModel();
	}

	public function index() {
		$database_repair_date = $this->settings->database_repair_date != '0000-00-00 00:00:00' ? dateFormat($this->settings->database_repair_date, 'd-m-Y H:i:s') : lang('AdminSettings.maintenanceMode.databaseRepair.dateError');
		$cache_clear_date = $this->settings->cache_clear_date != '0000-00-00 00:00:00' ? dateFormat($this->settings->cache_clear_date, 'd-m-Y H:i:s') : lang('AdminSettings.maintenanceMode.cacheClearing.dateError');

		return $this->twig->render($this->BACKEND_TEMPLATE_PATH.'/'.$this->pageUrl.'.html', [
			'page_url' => $this->pageUrl,
			'database_repair_date' => $database_repair_date,
			'cache_clear_date' => $cache_clear_date,
			'FLASH_DATA' => [
				'MESSAGE_2' => session()->getFlashdata('FLASH_DATA_MESSAGE_SUCCESS_2'),
				'MESSAGE_3' => session()->getFlashdata('FLASH_DATA_MESSAGE_SUCCESS_3')
			]
		]);
	}

	public function update() {
		if ($this->request->isAJAX()) {
			if ($this->request->getMethod() == 'POST') {

				$data = [
					'maintenance_mode_status' => $this->request->getVar('maintenance_mode_status'),
					'maintenance_mode_title' => $this->request->getVar('maintenance_mode_title'),
					'maintenance_mode_text' => $this->request->getVar('maintenance_mode_text')
				];

				$result = $this->SettingModel->update(SETTING_ID, $data);
				if ($result !== FALSE) {
					if ($this->request->getVar('maintenance_mode_status') == 0) {
						session()->setFlashdata('FLASH_DATA_MESSAGE_SUCCESS_3', lang('AdminSettings.result.edit.maintenanceModeActive'));
					} else {
						session()->setFlashdata('FLASH_DATA_MESSAGE_SUCCESS_3', lang('AdminSettings.result.edit.maintenanceModePassive'));
					}

					$ajax_message['success'] = TRUE;
					$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);
				} else {
					$ajax_message['error'] = lang('Admin.updateError');
				}

			} else {
				$ajax_message['error'] = lang('Admin.errorDesc');
			}
		} else {
			$ajax_message['error'] = lang('Admin.ajaxError');
		}

		return $this->response->setJSON($ajax_message);
	}

	public function databaseRepair() {
		$db = \Config\Database::connect();
		$tables = $db->listTables();
		if (isNotNull($tables)) {
			foreach ($tables as $row) {
				$alter = $db->query("ALTER TABLE `$row` ROW_FORMAT=DYNAMIC");
				$collte = $db->query("ALTER TABLE `$row` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci");
				$optimize = $db->query("OPTIMIZE TABLE `$row`");
				$analyze = $db->query("ANALYZE TABLE `$row`");
				$repair = $db->query("REPAIR TABLE `$row`");
			}

			if ($alter && $collte && $optimize && $analyze && $repair) {

				$result = $this->SettingModel->update(SETTING_ID, ['database_repair_date' => nowDate()]);
				if ($result !== FALSE) {

					// Flash Data
					session()->setFlashdata('FLASH_DATA_MESSAGE_SUCCESS', lang('AdminSettings.result.edit.databaseRepair'));

					$ajax_message['success'] = TRUE;
					$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);

				} else {
					$ajax_message['error'] = lang('Admin.updateError');
				}

			} else {
				session()->setFlashdata('flashDataMessage', lang('Admin.errorDesc'));
			}
		} else {
			session()->setFlashdata('flashDataMessage', lang('Admin.errorDesc'));
		}

		return $this->response->setJSON($ajax_message);
	}

	public function cacheClearing() {
		$cache = service('cache');
		$cache_clean = $cache->clean();
		if ($cache_clean !== FALSE) {

			$result = $this->SettingModel->update(SETTING_ID, ['cache_clear_date' => nowDate()]);
			if ($result !== FALSE) {

				// Flash Data
				session()->setFlashdata('FLASH_DATA_MESSAGE_SUCCESS_2', lang('AdminSettings.result.edit.cacheClearing'));

				$ajax_message['success'] = TRUE;
				$ajax_message['url'] = base_url(BACKEND_URL.'/'.$this->pageUrl);

			} else {
				$ajax_message['error'] = lang('Admin.updateError');
			}

		} else {
			$ajax_message['error'] = lang('Admin.errorDesc');
		}

		return $this->response->setJSON($ajax_message);
	}
}
